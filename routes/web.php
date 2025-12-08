<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\InscripcionController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\MembresiaController;
use App\Http\Controllers\Admin\ConvenioController;
use App\Http\Controllers\Admin\MetodoPagoController;
use App\Http\Controllers\Admin\MotivoDescuentoController;
use App\Http\Controllers\Admin\NotificacionController;
use App\Http\Controllers\Api\InscripcionApiController;
use App\Http\Controllers\Api\SearchApiController;
use App\Http\Controllers\Api\MembresiaApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\PausaApiController;
use App\Http\Controllers\Api\PagoApiController;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\Convenio;
use Illuminate\Support\Facades\Storage;

// Model Route Bindings - Buscar por UUID o ID
Route::model('inscripcion', Inscripcion::class);
Route::model('pago', Pago::class);

// ==== RUTA DE PRUEBA PARA PREVIEW DE PLANTILLAS ====
Route::get('/test-preview-directo/{id}', function($id) {
    $archivos = [
        1 => '01_bienvenida.html', 2 => '02_pago_completado.html',
        3 => '03_membresia_por_vencer.html', 4 => '04_membresia_vencida.html',
        5 => '05_pausa_inscripcion.html', 6 => '06_activacion_inscripcion.html',
        7 => '07_pago_pendiente.html', 8 => '08_renovacion.html',
        9 => '09_confirmacion_tutor_legal.html',
    ];
    
    $archivo = $archivos[$id] ?? null;
    if (!$archivo || !Storage::disk('local')->exists("test_emails/{$archivo}")) {
        return response('<h1>Plantilla no encontrada</h1>', 404);
    }
    
    $contenido = Storage::disk('local')->get("test_emails/{$archivo}");
    $datos = [
        'nombre' => 'Juan Pérez González', 'nombre_cliente' => 'Juan Pérez González',
        'email_cliente' => 'juan.perez@ejemplo.cl', 'run_cliente' => '12.345.678-9',
        'nombre_membresia' => 'Trimestral', 'precio_membresia' => '$65.000',
        'fecha_inicio' => now()->format('d/m/Y'), 'fecha_vencimiento' => now()->addMonths(3)->format('d/m/Y'),
        'tipo_pago' => 'Completo', 'monto_pagado' => '$65.000', 'monto_pendiente' => '$0',
        'monto_total' => '$65.000', 'metodo_pago' => 'Transferencia Bancaria', 'dias_restantes' => '7',
    ];
    
    foreach ($datos as $variable => $valor) {
        $contenido = str_replace("{{$variable}}", $valor, $contenido);
    }
    
    return response($contenido)->header('Content-Type', 'text/html');
});
Route::model('cliente', Cliente::class);
Route::model('membresia', Membresia::class);
Route::model('convenio', Convenio::class);

// ===== LANDING PAGE PÚBLICA (con headers de seguridad) =====
Route::middleware('security.headers')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::post('/contacto', [LandingController::class, 'contacto'])->name('landing.contacto');
    Route::post('/consultar-membresia', [LandingController::class, 'consultarMembresia'])->name('landing.consultar-membresia');
});

// ===== AUTENTICACIÓN =====
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', function () {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        if (Auth::attempt($credentials, request()->boolean('remember'))) {
            $user = Auth::user();
            
            // Verificar si tiene 2FA habilitado
            if ($user->two_factor_enabled && $user->phone) {
                // Cerrar sesión temporalmente
                Auth::logout();
                
                // Guardar usuario en sesión temporal
                session(['2fa_user_id' => $user->id]);
                session(['2fa_remember' => request()->boolean('remember')]);
                
                // Enviar código de verificación
                $twoFactorService = new \App\Services\TwoFactorService();
                $result = $twoFactorService->sendVerificationCode($user, 'login');
                
                if ($result['success']) {
                    return redirect()->route('2fa.show')->with('status', $result['message']);
                } else {
                    // Si falla el envío, permitir login sin 2FA
                    Auth::login($user, request()->boolean('remember'));
                    request()->session()->regenerate();
                    return redirect()->intended('dashboard');
                }
            }
            
            request()->session()->regenerate();
            return redirect()->intended('dashboard');
        }
        
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    });
    
    // ===== 2FA - Verificación de dos factores =====
    Route::get('/verify-2fa', function () {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }
        
        $twoFactorService = new \App\Services\TwoFactorService();
        
        return view('auth.verify-2fa', [
            'userId' => $user->id,
            'maskedPhone' => substr($user->phone, 0, 3) . '****' . substr($user->phone, -3),
            'channel' => $user->two_factor_channel ?? 'whatsapp',
            'type' => 'login',
            'expiresIn' => 600, // 10 minutos
        ]);
    })->name('2fa.show');
    
    Route::post('/verify-2fa', function () {
        $userId = session('2fa_user_id');
        $remember = session('2fa_remember', false);
        
        if (!$userId) {
            return redirect()->route('login')->withErrors(['code' => 'Sesión expirada. Inicia sesión nuevamente.']);
        }
        
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }
        
        request()->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $twoFactorService = new \App\Services\TwoFactorService();
        $result = $twoFactorService->verifyCode($user, request('code'), 'login');
        
        if ($result['success']) {
            // Limpiar sesión temporal
            session()->forget(['2fa_user_id', '2fa_remember']);
            
            // Login exitoso
            Auth::login($user, $remember);
            request()->session()->regenerate();
            
            return redirect()->intended('dashboard');
        }
        
        return back()->withErrors(['code' => $result['message']]);
    })->name('2fa.verify');
    
    Route::post('/resend-2fa', function () {
        $userId = request('user_id') ?: session('2fa_user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Sesión expirada']);
        }
        
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        
        $twoFactorService = new \App\Services\TwoFactorService();
        $result = $twoFactorService->sendVerificationCode($user, request('type', 'login'));
        
        return response()->json($result);
    })->name('2fa.resend');
    
    // Recuperar contraseña - Solicitar enlace
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    Route::post('/forgot-password', function () {
        request()->validate(['email' => 'required|email']);
        
        $user = \App\Models\User::where('email', request('email'))->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'No encontramos un usuario con ese correo.']);
        }
        
        // Generar token
        $token = \Illuminate\Support\Str::random(64);
        
        // Guardar en password_reset_tokens
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => request('email')],
            [
                'email' => request('email'),
                'token' => bcrypt($token),
                'created_at' => now()
            ]
        );
        
        // Por ahora, mostrar el token (en producción enviar por email)
        // En un entorno real, usarías Mail::to($user)->send(new ResetPasswordMail($token));
        return back()->with('status', 'Si el correo existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña. (Token de prueba: ' . $token . ')');
    })->name('password.email');
    
    // Restablecer contraseña - Formulario
    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');
    
    Route::post('/reset-password', function () {
        request()->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        // Verificar token
        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', request('email'))
            ->first();
        
        if (!$record || !\Illuminate\Support\Facades\Hash::check(request('token'), $record->token)) {
            return back()->withErrors(['email' => 'El token de recuperación es inválido o ha expirado.']);
        }
        
        // Verificar que no haya expirado (1 hora)
        if (now()->diffInMinutes($record->created_at) > 60) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', request('email'))->delete();
            return back()->withErrors(['email' => 'El token de recuperación ha expirado.']);
        }
        
        // Actualizar contraseña
        $user = \App\Models\User::where('email', request('email'))->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No encontramos un usuario con ese correo.']);
        }
        
        $user->password = \Illuminate\Support\Facades\Hash::make(request('password'));
        $user->save();
        
        // Eliminar token usado
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', request('email'))->delete();
        
        return redirect()->route('login')->with('status', '¡Contraseña actualizada! Ya puedes iniciar sesión.');
    })->name('password.update');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

// ===== RUTAS PROTEGIDAS (Requieren autenticación) =====
Route::middleware(['auth', 'verify.session'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas Admin - Grupo con prefijo 'admin'
    Route::prefix('admin')->name('admin.')->group(function () {
    // Rutas personalizadas de clientes (deben ir antes del resource)
    Route::get('clientes-desactivados/ver', [ClienteController::class, 'showInactive'])->name('clientes.inactive');
    Route::patch('clientes/{cliente}/reactivar', [ClienteController::class, 'reactivate'])->name('clientes.reactivate');
    Route::patch('clientes/{cliente}/desactivar', [ClienteController::class, 'deactivate'])->name('clientes.deactivate');
    
    // Papelera de clientes (SoftDeletes)
    Route::get('clientes/papelera', [ClienteController::class, 'trashed'])->name('clientes.trashed');
    Route::patch('clientes/{id}/restaurar', [ClienteController::class, 'restore'])->name('clientes.restore');
    Route::delete('clientes/{id}/eliminar-permanente', [ClienteController::class, 'forceDelete'])->name('clientes.force-delete');
    
    // RUTA SIMPLE DE DEBUG
    Route::get('clientes/create-simple', function() {
        $convenios = \App\Models\Convenio::where('activo', true)->get();
        $membresias = \App\Models\Membresia::where('activo', true)->get();
        $metodos_pago = \App\Models\MetodoPago::all();
        return view('admin.clientes.create_simple', compact('convenios', 'membresias', 'metodos_pago'));
    })->name('clientes.create-simple');
    
    // CRUD Clientes
    Route::resource('clientes', ClienteController::class);

    // CRUD Inscripciones
    Route::resource('inscripciones', InscripcionController::class)->parameters(['inscripciones' => 'inscripcion']);
    
    // Papelera de inscripciones (SoftDeletes)
    Route::get('inscripciones-papelera', [InscripcionController::class, 'trashed'])->name('inscripciones.trashed');
    Route::patch('inscripciones/{id}/restaurar', [InscripcionController::class, 'restore'])->name('inscripciones.restore');
    Route::delete('inscripciones/{id}/eliminar-permanente', [InscripcionController::class, 'forceDelete'])->name('inscripciones.force-delete');
    
    // Pausar y Reanudar inscripciones
    Route::post('inscripciones/{inscripcion}/pausar', [InscripcionController::class, 'pausar'])->name('inscripciones.pausar');
    Route::post('inscripciones/{inscripcion}/reanudar', [InscripcionController::class, 'reanudar'])->name('inscripciones.reanudar');
    
    // Mejora de Plan (Upgrade)
    Route::get('inscripciones/{inscripcion}/info-cambio-plan', [InscripcionController::class, 'infoCambioPlan'])->name('inscripciones.info-cambio-plan');
    Route::post('inscripciones/{inscripcion}/cambiar-plan', [InscripcionController::class, 'cambiarPlan'])->name('inscripciones.cambiar-plan');
    
    // Traspaso de Membresía
    Route::get('inscripciones/{inscripcion}/buscar-clientes-traspaso', [InscripcionController::class, 'buscarClientesTraspaso'])->name('inscripciones.buscar-clientes-traspaso');
    Route::post('inscripciones/{inscripcion}/traspasar', [InscripcionController::class, 'traspasar'])->name('inscripciones.traspasar');
    
    // Renovación de Membresía
    Route::get('inscripciones/{inscripcion}/renovar', [InscripcionController::class, 'showRenovar'])->name('inscripciones.renovar');
    Route::post('inscripciones/{inscripcion}/renovar', [InscripcionController::class, 'renovar'])->name('inscripciones.renovar.store');
    
    // Módulo Historial (traspasos, cambios, etc.)
    Route::get('historial', [\App\Http\Controllers\Admin\HistorialController::class, 'index'])->name('historial.index');
    Route::get('historial/traspaso/{traspaso}', [\App\Http\Controllers\Admin\HistorialController::class, 'showTraspaso'])->name('historial.traspaso.show');

    // Módulo Reportes
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReporteController::class, 'index'])->name('index');
        Route::get('/builder', [\App\Http\Controllers\Admin\ReporteController::class, 'builder'])->name('builder');
        Route::match(['get', 'post'], '/generar', [\App\Http\Controllers\Admin\ReporteController::class, 'generar'])->name('generar');
        Route::get('/predefinido/{tipo}', [\App\Http\Controllers\Admin\ReporteController::class, 'predefinido'])->name('predefinido');
        Route::get('/campos/{modulo}', [\App\Http\Controllers\Admin\ReporteController::class, 'getCamposModulo'])->name('campos');
    });

    // CRUD Pagos
    Route::get('pagos/json', [PagoController::class, 'getPagosJson'])->name('pagos.json');
    Route::resource('pagos', PagoController::class)->parameters(['pagos' => 'pago']);
    Route::get('pagos/historial/{id}', [PagoController::class, 'historial'])->name('pagos.historial');
    
    // Papelera de pagos (SoftDeletes)
    Route::get('pagos-papelera', [PagoController::class, 'trashed'])->name('pagos.trashed');
    Route::patch('pagos/{id}/restaurar', [PagoController::class, 'restore'])->name('pagos.restore');
    Route::delete('pagos/{id}/eliminar-permanente', [PagoController::class, 'forceDelete'])->name('pagos.force-delete');

    // ===== CONFIGURACIÓN (Sección inferior) =====
    
    // CRUD Convenios
    Route::resource('convenios', ConvenioController::class);
    Route::patch('convenios/{convenio}/desactivar', [ConvenioController::class, 'deactivate'])->name('convenios.deactivate');
    Route::patch('convenios/{convenio}/activar', [ConvenioController::class, 'activate'])->name('convenios.activate');
    
    // Papelera de convenios (SoftDeletes)
    Route::get('convenios-papelera', [ConvenioController::class, 'trashed'])->name('convenios.trashed');
    Route::patch('convenios/{id}/restaurar', [ConvenioController::class, 'restore'])->name('convenios.restore');
    Route::delete('convenios/{id}/eliminar-permanente', [ConvenioController::class, 'forceDelete'])->name('convenios.force-delete');

    // CRUD Membresias (configuración)
    Route::resource('membresias', MembresiaController::class);
    Route::patch('membresias/{membresia}/activar', [MembresiaController::class, 'activate'])->name('membresias.activate');
    
    // Papelera de membresías (SoftDeletes) - Nota: Las membresías solo se desactivan, no van a papelera
    Route::get('membresias-papelera', [MembresiaController::class, 'trashed'])->name('membresias.trashed');
    Route::patch('membresias/{id}/restaurar', [MembresiaController::class, 'restore'])->name('membresias.restore');
    Route::delete('membresias/{id}/eliminar-permanente', [MembresiaController::class, 'forceDelete'])->name('membresias.force-delete');

    // CRUD Métodos de Pago
    Route::resource('metodos-pago', MetodoPagoController::class);

    // CRUD Motivos de Descuento
    Route::resource('motivos-descuento', MotivoDescuentoController::class);

    // ===== NOTIFICACIONES =====
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        // Rutas específicas PRIMERO (antes de las rutas con parámetros)
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::get('/historial', [NotificacionController::class, 'historial'])->name('historial');
        
        // Programar notificaciones masivas (NUEVO)
        Route::get('/programar', [NotificacionController::class, 'programar'])->name('programar');
        Route::post('/guardar-programada', [NotificacionController::class, 'guardarProgramada'])->name('guardar-programada');
        Route::get('/buscar-cliente', [NotificacionController::class, 'buscarCliente'])->name('buscar-cliente');
        Route::get('/contar-destinatarios', [NotificacionController::class, 'contarDestinatarios'])->name('contar-destinatarios');
        
        // Enviar a cliente individual
        Route::get('/enviar-cliente', [NotificacionController::class, 'enviarCliente'])->name('enviar-cliente');
        Route::post('/buscar-cliente-individual', [NotificacionController::class, 'buscarClienteIndividual'])->name('buscar-cliente-individual');
        Route::post('/preview', [NotificacionController::class, 'preview'])->name('preview');
        Route::post('/enviar-individual', [NotificacionController::class, 'enviarIndividual'])->name('enviar-individual');
        
        // Crear notificación masiva
        Route::get('/crear', [NotificacionController::class, 'crear'])->name('crear');
        Route::get('/obtener-destinatarios', [NotificacionController::class, 'obtenerDestinatarios'])->name('obtener-destinatarios');
        Route::post('/enviar-masivo', [NotificacionController::class, 'enviarMasivo'])->name('enviar-masivo');
        
        // Plantillas (rutas específicas)
        Route::get('/plantillas', [NotificacionController::class, 'plantillas'])->name('plantillas');
        Route::get('/plantillas/{tipoNotificacion}/editar', [NotificacionController::class, 'editarPlantilla'])->name('plantillas.editar');
        Route::put('/plantillas/{tipoNotificacion}', [NotificacionController::class, 'actualizarPlantilla'])->name('plantillas.actualizar');
        
        // Rutas con parámetros AL FINAL
        Route::get('/{notificacion}', [NotificacionController::class, 'show'])->name('show');
        Route::post('/{notificacion}/reenviar', [NotificacionController::class, 'reenviar'])->name('reenviar');
        Route::post('/{notificacion}/cancelar', [NotificacionController::class, 'cancelar'])->name('cancelar');
        Route::get('/{notificacion}/logs', [NotificacionController::class, 'logs'])->name('logs');
    });
}); // Fin rutas admin

}); // Fin middleware('auth')

// API Routes - Grupo con prefijo 'api'
Route::prefix('api')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
    Route::get('/dashboard/ingresos-mes', [DashboardApiController::class, 'ingresosPorMes']);
    Route::get('/dashboard/inscripciones-estado', [DashboardApiController::class, 'inscripcionesPorEstado']);
    Route::get('/dashboard/membresias-populares', [DashboardApiController::class, 'membresiasPopulares']);
    Route::get('/dashboard/metodos-pago', [DashboardApiController::class, 'metodosPagoPopulares']);
    Route::get('/dashboard/ultimos-pagos', [DashboardApiController::class, 'ultimosPagos']);
    Route::get('/dashboard/proximas-vencer', [DashboardApiController::class, 'proximasAVencer']);
    Route::get('/dashboard/resumen-clientes', [DashboardApiController::class, 'resumenClientes']);
    
    // Búsqueda
    Route::get('/clientes/search', [SearchApiController::class, 'searchClientes']);
    Route::get('/inscripciones/search', [SearchApiController::class, 'searchInscripciones']);
    
    // Clientes
    Route::get('/clientes', [ClienteApiController::class, 'index']);
    Route::get('/clientes/{id}', [ClienteApiController::class, 'show']);
    Route::get('/clientes/{id}/stats', [ClienteApiController::class, 'stats']);
    Route::post('/clientes/validar-rut', [ClienteApiController::class, 'validarRut']);
    
    // Precio de membresía
    Route::get('/precio-membresia/{membresia_id}', [ClienteController::class, 'getPrecioMembresia']);
    
    // Membresias
    Route::get('/membresias', [MembresiaApiController::class, 'index']);
    Route::get('/membresias/search', [MembresiaApiController::class, 'search']);
    Route::get('/membresias/{id}', [MembresiaApiController::class, 'show']);
    
    // Obtener descuento de convenio
    Route::get('/convenios/{id}/descuento', [InscripcionApiController::class, 'getConvenioDescuento']);
    
    // Calcular precio final y fecha vencimiento
    Route::post('/inscripciones/calcular', [InscripcionApiController::class, 'calcular']);
    
    // Pausas - Manejo de pausas en membresías
    Route::post('/pausas/{id}/pausar', [PausaApiController::class, 'pausar']);
    Route::post('/pausas/{id}/reanudar', [PausaApiController::class, 'reanudar']);
    Route::get('/pausas/{id}/info', [PausaApiController::class, 'info']);
    Route::post('/pausas/verificar-expiradas', [PausaApiController::class, 'verificarExpiradas']);
    
    // Pagos - API REST para pagos
    Route::post('/pagos', [PagoApiController::class, 'store']);
    Route::get('/pagos/{id}', [PagoApiController::class, 'show']);
    Route::put('/pagos/{id}', [PagoApiController::class, 'update']);
    Route::delete('/pagos/{id}', [PagoApiController::class, 'destroy']);
    Route::get('/inscripciones/{id}/saldo', [PagoApiController::class, 'getSaldo']);
    Route::post('/pagos/calcular-cuotas', [PagoApiController::class, 'calcularCuotas']);
});
