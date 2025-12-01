<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
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

// Model Route Bindings - Buscar por UUID o ID
Route::model('inscripcion', Inscripcion::class);
Route::model('pago', Pago::class);
Route::model('cliente', Cliente::class);
Route::model('membresia', Membresia::class);
Route::model('convenio', Convenio::class);

// ===== RUTAS PÚBLICAS =====
Route::get('/', function () {
    return redirect()->route('login');
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
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas Admin - Grupo con prefijo 'admin'
    Route::prefix('admin')->name('admin.')->group(function () {
    // Rutas personalizadas de clientes (deben ir antes del resource)
    Route::get('clientes-desactivados/ver', [ClienteController::class, 'showInactive'])->name('clientes.inactive');
    Route::patch('clientes/{cliente}/reactivar', [ClienteController::class, 'reactivate'])->name('clientes.reactivate');
    Route::patch('clientes/{cliente}/desactivar', [ClienteController::class, 'deactivate'])->name('clientes.deactivate');
    
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
    
    // Pausar y Reanudar inscripciones
    Route::post('inscripciones/{inscripcion}/pausar', [InscripcionController::class, 'pausar'])->name('inscripciones.pausar');
    Route::post('inscripciones/{inscripcion}/reanudar', [InscripcionController::class, 'reanudar'])->name('inscripciones.reanudar');
    
    // Mejora de Plan (Upgrade)
    Route::get('inscripciones/{inscripcion}/info-cambio-plan', [InscripcionController::class, 'infoCambioPlan'])->name('inscripciones.info-cambio-plan');
    Route::post('inscripciones/{inscripcion}/cambiar-plan', [InscripcionController::class, 'cambiarPlan'])->name('inscripciones.cambiar-plan');
    
    // Traspaso de Membresía
    Route::get('inscripciones/{inscripcion}/buscar-clientes-traspaso', [InscripcionController::class, 'buscarClientesTraspaso'])->name('inscripciones.buscar-clientes-traspaso');
    Route::post('inscripciones/{inscripcion}/traspasar', [InscripcionController::class, 'traspasar'])->name('inscripciones.traspasar');
    
    // Módulo Historial (traspasos, cambios, etc.)
    Route::get('historial', [\App\Http\Controllers\Admin\HistorialController::class, 'index'])->name('historial.index');
    Route::get('historial/traspaso/{traspaso}', [\App\Http\Controllers\Admin\HistorialController::class, 'showTraspaso'])->name('historial.traspaso.show');

    // CRUD Pagos
    Route::resource('pagos', PagoController::class)->parameters(['pagos' => 'pago']);
    Route::get('pagos/historial/{id}', [PagoController::class, 'historial'])->name('pagos.historial');

    // ===== CONFIGURACIÓN (Sección inferior) =====
    
    // CRUD Convenios
    Route::resource('convenios', ConvenioController::class);

    // CRUD Membresias (configuración)
    Route::resource('membresias', MembresiaController::class);

    // CRUD Métodos de Pago
    Route::resource('metodos-pago', MetodoPagoController::class);

    // CRUD Motivos de Descuento
    Route::resource('motivos-descuento', MotivoDescuentoController::class);

    // ===== NOTIFICACIONES =====
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        // Rutas específicas PRIMERO (antes de las rutas con parámetros)
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::post('/ejecutar', [NotificacionController::class, 'ejecutar'])->name('ejecutar');
        
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
