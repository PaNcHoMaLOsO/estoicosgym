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

// ===== LANDING PAGE PÚBLICA (con headers de seguridad) =====
Route::middleware('security.headers')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    // Para Google: que hay y donde esta el mapa del sitio.
    Route::get('/robots.txt', [LandingController::class, 'robots'])->name('landing.robots');
    Route::get('/sitemap.xml', [LandingController::class, 'sitemap'])->name('landing.sitemap');

    /*
     * Un tope general encima de los que ya lleva cada metodo. Aquellos cuentan
     * con criterio —fallos, bloqueos progresivos, por RUT—; este es el respaldo
     * tonto por si un dia fallan: nadie de verdad manda diez formularios en un
     * minuto.
     */
    Route::post('/contacto', [LandingController::class, 'contacto'])->middleware('throttle:10,1')->name('landing.contacto');
    Route::post('/consultar-membresia', [LandingController::class, 'consultarMembresia'])->middleware('throttle:10,1')->name('landing.consultar-membresia');
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
                }

                /*
                 * SI EL CODIGO NO SALE, NO SE ENTRA.
                 *
                 * Aqui antes se hacia Auth::login() «para no dejar fuera al
                 * usuario», y eso convertia el segundo factor en un adorno:
                 * bastaba con que el canal fallara para que la clave sola
                 * abriera la puerta. Y no era un caso raro. Sin ningun canal
                 * configurado —que es como esta hoy— el envio SIEMPRE falla,
                 * asi que todo el que activaba 2FA entraba sin el creyendose
                 * protegido, y lo unico que quedaba era un aviso en el log.
                 *
                 * Un control de seguridad que se apaga solo es peor que uno que
                 * estorba: el que estorba se ve. Si el canal esta caido, el
                 * administrador puede desactivarle el 2FA a esa cuenta.
                 */
                session()->forget(['2fa_user_id', '2fa_remember']);

                \Illuminate\Support\Facades\Log::error('No se pudo enviar el código 2FA; se bloqueó el acceso.', [
                    'usuario' => $user->id,
                    'canal' => $user->two_factor_channel,
                    'motivo' => $result['message'] ?? null,
                ]);

                return back()->withErrors([
                    'email' => 'No pudimos enviarte el código de verificación. Inténtalo de nuevo en un momento; si sigue fallando, avisa al administrador.',
                ])->onlyInput('email');
            }
            
            request()->session()->regenerate();
            return redirect()->intended('dashboard');
        }
        
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    })->middleware('throttle:8,1'); // 8 intentos por minuto por IP: frena el probar claves en masa sin estorbar a quien se equivoca un par de veces.
    
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
    })->middleware('throttle:6,1')->name('2fa.verify');
    
    Route::post('/resend-2fa', function () {
        // El usuario SOLO sale de la sesion del login a medias, nunca del
        // request. Antes se aceptaba `user_id` del formulario, asi que un
        // visitante anonimo probaba ids y el sistema respondia distinto segun
        // existieran o no: enumeracion de usuarios sin estar autenticado.
        $userId = session('2fa_user_id');

        $neutro = response()->json([
            'success' => true,
            'message' => 'Si tu sesión sigue activa, reenviamos el código.',
        ]);

        if (! $userId) {
            return $neutro;
        }

        $user = \App\Models\User::find($userId);

        if ($user) {
            (new \App\Services\TwoFactorService())->sendVerificationCode($user, 'login');
        }

        // Misma respuesta pase lo que pase: no se revela si el codigo salio.
        return $neutro;
    })->middleware('throttle:4,1')->name('2fa.resend');
    
    // Recuperar contraseña - Solicitar enlace
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    Route::post('/forgot-password', function () {
        request()->validate(['email' => 'required|email']);

        // MISMA respuesta exista o no el correo. Antes, cuando no existia se
        // devolvia «No encontramos un usuario con ese correo», y eso le confirma
        // a cualquiera —sin sesion— que direcciones estan registradas.
        $neutro = 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña.';

        $user = \App\Models\User::where('email', request('email'))->first();

        if ($user) {
            // El token viaja EN CLARO en el enlace y se guarda hasheado: quien
            // lea la tabla no puede armar el enlace con lo que hay ahi.
            $token = \Illuminate\Support\Str::random(64);

            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['email' => $user->email, 'token' => bcrypt($token), 'created_at' => now()]
            );

            $enlace = route('password.reset', ['token' => $token, 'email' => $user->email]);

            // EL TOKEN SE MANDA POR CORREO, no se pinta en pantalla. Antes se
            // devolvia en el mensaje, asi que cualquiera que supiera el correo
            // del admin reseteaba su clave SIN entrar a su buzon: toma de cuenta
            // completa. Si el correo no esta configurado el envio falla y queda
            // en el log, pero el token JAMAS vuelve al navegador.
            try {
                app(\App\Services\CorreoService::class)->enviar(
                    $user->email,
                    'Restablece tu contraseña · PRO GYM',
                    view('emails.reset-password', ['enlace' => $enlace, 'nombre' => $user->name])->render(),
                    $user->name,
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('No se pudo enviar el correo de recuperación: ' . $e->getMessage());
            }

            // Solo en desarrollo, y siguiendo el mismo criterio que el dev_code
            // del 2FA: en local el enlace se muestra para poder probar sin correo
            // configurado. En produccion esta rama no existe.
            if (app()->environment('local', 'development')) {
                return back()->with('status', $neutro . ' [dev] ' . $enlace);
            }
        }

        return back()->with('status', $neutro);
    })->middleware('throttle:4,1')->name('password.email');
    
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
        
        /*
         * EL ENLACE CADUCA A LA HORA.
         *
         * Aqui se comparaba `now()->diffInMinutes($record->created_at) > 60`, y
         * eso NUNCA se cumplia: Carbon devuelve la diferencia CON SIGNO, asi que
         * para una fecha pasada da un numero negativo —hace dos horas son −120—
         * y ningun negativo es mayor que 60. El enlace de recuperacion no
         * caducaba jamas.
         *
         * Comprobado: un token de hace SIETE DIAS cambiaba la contraseña sin
         * chistar. Un correo reenviado, olvidado en un buzon viejo o leido en un
         * equipo compartido seguia abriendo la cuenta meses despues.
         *
         * Se compara sumando la hora al momento de creacion, que no depende del
         * signo y se lee como lo que es.
         */
        $caduca = \Illuminate\Support\Carbon::parse($record->created_at)->addHour();

        if ($caduca->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', request('email'))->delete();

            return back()->withErrors(['email' => 'El enlace de recuperación caducó. Pide uno nuevo.']);
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
    })->middleware('throttle:6,1')->name('password.update');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

// ===== RUTAS PROTEGIDAS (Requieren autenticación) =====
/*
 * 'puede' sin argumento deduce el permiso del NOMBRE de la ruta
 * (App\Support\Permisos) y deja pasar las que no son de un modulo protegido.
 * Va en el grupo entero para que ninguna ruta nueva nazca sin proteger, que es
 * como estaba TODO hasta ahora: la tabla `roles` guardaba permisos y no habia
 * nada que los leyera.
 */
Route::middleware(['auth', 'verify.session', 'puede'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
     * PANEL NUEVO (Inertia + React).
     *
     * Vive bajo /panel y no reemplaza a /admin todavia: se migra pantalla por
     * pantalla y las dos conviven mientras tanto. Cuando /panel cubra todos los
     * modulos, /admin pasa a redirigir aqui.
     */
    Route::prefix('panel')->name('panel.')->group(function () {
        Route::get('/', \App\Http\Controllers\Panel\ResumenController::class)->name('resumen');

        /*
         * El bloc de notas del meson, en la portada.
         *
         * Cae en el permiso de clientes —lo mismo que `resumen`— porque es del
         * trabajo de meson: quien atiende apunta lo que hay que hacer hoy.
         */
        Route::post('/notas', [\App\Http\Controllers\Panel\NotaController::class, 'store'])->name('notas.store');
        Route::patch('/notas/{nota}', [\App\Http\Controllers\Panel\NotaController::class, 'alternar'])->name('notas.alternar');
        Route::delete('/notas/{nota}', [\App\Http\Controllers\Panel\NotaController::class, 'destroy'])->name('notas.destroy');

        /*
         * Lo fiado en el meson: la barra de proteina que alguien se lleva y
         * paga despues. Va con el trabajo de meson, como las notas.
         */
        Route::get('/fiados', [\App\Http\Controllers\Panel\FiadoController::class, 'index'])->name('fiados.index');
        Route::get('/fiados/buscar-socio', [\App\Http\Controllers\Panel\FiadoController::class, 'buscar'])->name('fiados.buscar');
        Route::post('/fiados', [\App\Http\Controllers\Panel\FiadoController::class, 'store'])->name('fiados.store');
        Route::post('/fiados/saldar', [\App\Http\Controllers\Panel\FiadoController::class, 'saldar'])->name('fiados.saldar');
        // Deshacer un «Pago» mal dado: sin esto, la deuda desaparece y hay que
        // volver a apuntarla a mano inventando conceptos y montos.
        Route::patch('/fiados/{fiado}/reabrir', [\App\Http\Controllers\Panel\FiadoController::class, 'reabrir'])->name('fiados.reabrir');
        Route::delete('/fiados/{fiado}', [\App\Http\Controllers\Panel\FiadoController::class, 'destroy'])->name('fiados.destroy');
        Route::get('/clientes', [\App\Http\Controllers\Panel\ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/crear', [\App\Http\Controllers\Panel\ClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes', [\App\Http\Controllers\Panel\ClienteController::class, 'store'])->name('clientes.store');
        // La ficha va DESPUES del alta: si fuera antes, /clientes/crear
        // entraria por {cliente} y buscaria un socio con uuid «crear».
        Route::get('/clientes/{cliente}', \App\Http\Controllers\Panel\ClienteFichaController::class)->name('clientes.show');
        Route::get('/clientes/{cliente}/editar', [\App\Http\Controllers\Panel\ClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/{cliente}', [\App\Http\Controllers\Panel\ClienteController::class, 'update'])->name('clientes.update');
        // La foto va aparte de la edicion: un archivo obliga a multipart, e
        // Inertia no puede mandar eso con un PUT.
        Route::post('/clientes/{cliente}/foto', [\App\Http\Controllers\Panel\ClienteController::class, 'foto'])->name('clientes.foto');
        // Constancia del contrato en papel y de los permisos que dio el socio.
        Route::post('/clientes/{cliente}/contrato', [\App\Http\Controllers\Panel\ClienteController::class, 'contrato'])->name('clientes.contrato');
        Route::patch('/clientes/{cliente}/desactivar', [\App\Http\Controllers\Panel\ClienteController::class, 'desactivar'])->name('clientes.deactivate');
        Route::patch('/clientes/{cliente}/reactivar', [\App\Http\Controllers\Panel\ClienteController::class, 'reactivar'])->name('clientes.reactivate');
        // A la papelera, no al vacio: se recupera desde /panel/papelera.
        Route::delete('/clientes/{cliente}', [\App\Http\Controllers\Panel\ClienteController::class, 'eliminar'])->name('clientes.destroy');
        Route::get('/inscripciones', [\App\Http\Controllers\Panel\InscripcionController::class, 'index'])->name('inscripciones.index');
        Route::get('/inscripciones/crear', [\App\Http\Controllers\Panel\InscripcionCrearController::class, 'create'])->name('inscripciones.create');
        Route::get('/inscripciones/buscar-socio', [\App\Http\Controllers\Panel\InscripcionCrearController::class, 'buscar'])->name('inscripciones.buscar-socio');
        Route::post('/inscripciones', [\App\Http\Controllers\Panel\InscripcionCrearController::class, 'store'])->name('inscripciones.store');
        Route::get('/inscripciones/{inscripcion}/renovar', [\App\Http\Controllers\Panel\InscripcionRenovarController::class, 'create'])->name('inscripciones.renovar');
        Route::post('/inscripciones/{inscripcion}/renovar', [\App\Http\Controllers\Panel\InscripcionRenovarController::class, 'store'])->name('inscripciones.renovar.store');
        // La ficha va DESPUES del alta, igual que en clientes: si fuera antes,
        // /inscripciones/crear entraria por {inscripcion} y buscaria una
        // membresia con uuid «crear».
        Route::get('/inscripciones/{inscripcion}', \App\Http\Controllers\Panel\InscripcionFichaController::class)->name('inscripciones.show');
        Route::get('/inscripciones/{inscripcion}/editar', [\App\Http\Controllers\Panel\InscripcionEditarController::class, 'edit'])->name('inscripciones.edit');
        Route::put('/inscripciones/{inscripcion}', [\App\Http\Controllers\Panel\InscripcionEditarController::class, 'update'])->name('inscripciones.update');
        // A la papelera, que ya la listaba sin que nada pudiera llegar ahi.
        Route::delete('/inscripciones/{inscripcion}', [\App\Http\Controllers\Panel\InscripcionEditarController::class, 'eliminar'])->name('inscripciones.destroy');

        /*
         * Acciones sobre una membresia ya vendida.
         *
         * Apuntan a los MISMOS metodos del controlador de Blade: la logica de
         * pausas, saldos y traspasos son cientos de lineas y duplicarlas para el
         * panel dejaria dos versiones que se separan a la primera correccion.
         * Los cuatro ya devolvian JSON, asi que React los llama tal cual.
         *
         * El nombre de la ruta importa: `panel.inscripciones.pausar` cae en
         * `inscripciones.gestionar` por App\Support\Permisos, que es el permiso
         * que tiene recepcion.
         */
        Route::post('/inscripciones/{inscripcion}/pausar', [\App\Http\Controllers\Admin\InscripcionController::class, 'pausar'])->name('inscripciones.pausar');
        Route::post('/inscripciones/{inscripcion}/reanudar', [\App\Http\Controllers\Admin\InscripcionController::class, 'reanudar'])->name('inscripciones.reanudar');
        Route::post('/inscripciones/{inscripcion}/cambiar-plan', [\App\Http\Controllers\Admin\InscripcionController::class, 'cambiarPlan'])->name('inscripciones.cambiar-plan');
        Route::post('/inscripciones/{inscripcion}/traspasar', [\App\Http\Controllers\Admin\InscripcionController::class, 'traspasar'])->name('inscripciones.traspasar');
        Route::get('/inscripciones/{inscripcion}/buscar-clientes-traspaso', [\App\Http\Controllers\Admin\InscripcionController::class, 'buscarClientesTraspaso'])->name('inscripciones.buscar-clientes-traspaso');
        Route::get('/inscripciones/{inscripcion}/info-cambio-plan', [\App\Http\Controllers\Admin\InscripcionController::class, 'infoCambioPlan'])->name('inscripciones.info-cambio-plan');
        Route::get('/pagos', [\App\Http\Controllers\Panel\PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/cobrar', [\App\Http\Controllers\Panel\PagoCrearController::class, 'create'])->name('pagos.create');
        // Buscador del socio al que se le cobra. Cuelga de /pagos/buscar y no
        // de {pago} porque va antes en el fichero: si no, «buscar» entraria
        // por la ficha como si fuera un uuid.
        Route::get('/pagos/buscar', [\App\Http\Controllers\Panel\PagoCrearController::class, 'buscar'])->name('pagos.buscar');
        // El alta no cuelga de POST /pagos porque ese nombre ya lo ocupa el
        // listado y `Permisos` deduce la accion del nombre de la ruta.
        Route::post('/pagos/registrar', [\App\Http\Controllers\Panel\PagoCrearController::class, 'store'])->name('pagos.store');
        // Va al final del bloque de pagos: si {pago} se declarara antes,
        // /panel/pagos/cobrar entraria por ahi buscando un pago con uuid «cobrar».
        Route::get('/pagos/{pago}', \App\Http\Controllers\Panel\PagoFichaController::class)->name('pagos.show');
        Route::get('/pagos/{pago}/editar', [\App\Http\Controllers\Panel\PagoEditarController::class, 'edit'])->name('pagos.edit');
        Route::put('/pagos/{pago}', [\App\Http\Controllers\Panel\PagoEditarController::class, 'update'])->name('pagos.update');
        // A la papelera: un cobro anulado por error se recupera desde ahi.
        Route::delete('/pagos/{pago}', [\App\Http\Controllers\Panel\PagoEditarController::class, 'eliminar'])->name('pagos.destroy');
        Route::get('/historial', [\App\Http\Controllers\Panel\HistorialController::class, 'index'])->name('historial.index');

        /*
         * Lo borrado que todavia se puede recuperar, todo en una pantalla.
         *
         * NO hay «eliminar del todo»: un socio con inscripciones, un plan con
         * membresias vendidas o un pago de una caja de hace tres años estan
         * referenciados por otras filas, y quitarlos deja huecos en sitios que
         * nadie mira hasta que cuadran mal las cuentas.
         */
        Route::get('/papelera', [\App\Http\Controllers\Panel\PapeleraController::class, 'index'])->name('papelera.index');
        Route::patch('/papelera/{tipo}/{id}/restaurar', [\App\Http\Controllers\Panel\PapeleraController::class, 'restaurar'])->name('papelera.restore');

        // Informes. El constructor dinamico sigue en /admin: arma consultas a
        // medida y no se parece a estas cuatro pantallas.
        Route::get('/reportes', [\App\Http\Controllers\Panel\ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/ingresos', [\App\Http\Controllers\Panel\ReporteController::class, 'ingresos'])->name('reportes.ingresos');
        Route::get('/reportes/membresias', [\App\Http\Controllers\Panel\ReporteController::class, 'membresias'])->name('reportes.membresias');
        Route::get('/reportes/por-vencer', [\App\Http\Controllers\Panel\ReporteController::class, 'porVencer'])->name('reportes.por-vencer');
        Route::get('/reportes/pendientes', [\App\Http\Controllers\Panel\ReporteController::class, 'pendientes'])->name('reportes.pendientes');

        /*
         * Constructor de informes a medida.
         *
         * `generar` responde JSON en vez de una pagina de Inertia: se pulsa
         * «Ver» muchas veces seguidas cambiando columnas, y recargar entera la
         * pagina en cada intento perderia lo que se acaba de armar.
         */
        Route::get('/reportes/constructor', [\App\Http\Controllers\Panel\ConstructorController::class, 'index'])->name('reportes.constructor');
        Route::get('/reportes/constructor/{modulo}/ver', [\App\Http\Controllers\Panel\ConstructorController::class, 'generar'])->name('reportes.constructor.ver');
        Route::get('/reportes/constructor/{modulo}/csv', [\App\Http\Controllers\Panel\ConstructorController::class, 'exportar'])->name('reportes.constructor.csv');
        Route::get('/notificaciones', [\App\Http\Controllers\Panel\NotificacionController::class, 'index'])->name('notificaciones.index');
        // El envio va ANTES de la ficha: si fuera despues, /notificaciones/enviar
        // entraria por {notificacion} y buscaria una con uuid «enviar».
        Route::get('/notificaciones/enviar', [\App\Http\Controllers\Panel\NotificacionEnviarController::class, 'create'])->name('notificaciones.crear');
        Route::get('/notificaciones/buscar-socio', [\App\Http\Controllers\Panel\NotificacionEnviarController::class, 'buscar'])->name('notificaciones.buscar-cliente');
        Route::post('/notificaciones/vista-previa', [\App\Http\Controllers\Panel\NotificacionEnviarController::class, 'vistaPrevia'])->name('notificaciones.preview');
        Route::post('/notificaciones/enviar', [\App\Http\Controllers\Panel\NotificacionEnviarController::class, 'store'])->name('notificaciones.enviar-individual');
        Route::post('/notificaciones/{notificacion}/reenviar', [\App\Http\Controllers\Panel\NotificacionEnviarController::class, 'reenviar'])->name('notificaciones.reenviar');
        Route::post('/notificaciones/{notificacion}/cancelar', [\App\Http\Controllers\Panel\NotificacionEnviarController::class, 'cancelar'])->name('notificaciones.cancelar');
        Route::get('/notificaciones/masivo', [\App\Http\Controllers\Panel\NotificacionMasivaController::class, 'create'])->name('notificaciones.crear-masivo');
        Route::post('/notificaciones/masivo/destinatarios', [\App\Http\Controllers\Panel\NotificacionMasivaController::class, 'destinatarios'])->name('notificaciones.obtener-destinatarios');
        Route::post('/notificaciones/masivo/vista-previa', [\App\Http\Controllers\Panel\NotificacionMasivaController::class, 'vistaPrevia'])->name('notificaciones.preview-masivo');
        Route::post('/notificaciones/masivo', [\App\Http\Controllers\Panel\NotificacionMasivaController::class, 'store'])->name('notificaciones.enviar-masivo');
        Route::get('/notificaciones/plantillas', [\App\Http\Controllers\Panel\PlantillaController::class, 'index'])->name('notificaciones.plantillas');
        Route::put('/notificaciones/plantillas/{tipoNotificacion}', [\App\Http\Controllers\Panel\PlantillaController::class, 'update'])->name('notificaciones.plantillas.actualizar');
        Route::get('/notificaciones/plantillas/{tipoNotificacion}/vista-previa', [\App\Http\Controllers\Panel\PlantillaController::class, 'vistaPrevia'])->name('notificaciones.plantillas.preview');
        Route::get('/notificaciones/{notificacion}', [\App\Http\Controllers\Panel\FichasConfiguracionController::class, 'notificacion'])->name('notificaciones.show');

        // Catalogos de configuracion.
        /*
         * Configuracion: UNA sola puerta.
         *
         * Antes eran cinco entradas sueltas en el menu sin nada que dijera que
         * van juntas. Los cuatro catalogos siguen teniendo su pantalla —cada
         * uno es una tabla con su alta y su edicion— pero se entra por aqui.
         */
        Route::get('/configuracion', [\App\Http\Controllers\Panel\AjustesController::class, 'index'])->name('configuracion.index');
        Route::put('/configuracion', [\App\Http\Controllers\Panel\AjustesController::class, 'update'])->name('configuracion.update');

        Route::get('/membresias', [\App\Http\Controllers\Panel\ConfiguracionController::class, 'membresias'])->name('membresias.index');
        Route::get('/membresias/{membresia}', [\App\Http\Controllers\Panel\FichasConfiguracionController::class, 'membresia'])->name('membresias.show');
        Route::get('/convenios', [\App\Http\Controllers\Panel\ConfiguracionController::class, 'convenios'])->name('convenios.index');
        Route::get('/convenios/{convenio}', [\App\Http\Controllers\Panel\FichasConfiguracionController::class, 'convenio'])->name('convenios.show');
        Route::get('/metodos-pago', [\App\Http\Controllers\Panel\ConfiguracionController::class, 'metodosPago'])->name('metodos-pago.index');
        Route::get('/motivos-descuento', [\App\Http\Controllers\Panel\ConfiguracionController::class, 'motivosDescuento'])->name('motivos-descuento.index');

        /*
         * Alta y edicion de los cuatro catalogos.
         *
         * No hay `destroy`: un plan o un metodo que ya se uso esta referenciado
         * por inscripciones y pagos, y borrarlo dejaria fichas apuntando al
         * vacio. `alternar` lo desactiva, que es lo que de verdad se quiere.
         */
        Route::post('/membresias', [\App\Http\Controllers\Panel\CatalogoController::class, 'guardarMembresia'])->name('membresias.store');
        Route::put('/membresias/{membresia}', [\App\Http\Controllers\Panel\CatalogoController::class, 'actualizarMembresia'])->name('membresias.update');
        Route::post('/convenios', [\App\Http\Controllers\Panel\CatalogoController::class, 'guardarConvenio'])->name('convenios.store');
        Route::put('/convenios/{convenio}', [\App\Http\Controllers\Panel\CatalogoController::class, 'actualizarConvenio'])->name('convenios.update');
        // Los especialistas que aparecen en la web. Se ocultan con catalogos.alternar.
        Route::get('/especialistas', [\App\Http\Controllers\Panel\EspecialistaController::class, 'index'])->name('especialistas.index');
        Route::post('/especialistas', [\App\Http\Controllers\Panel\EspecialistaController::class, 'store'])->name('especialistas.store');
        Route::put('/especialistas/{especialista}', [\App\Http\Controllers\Panel\EspecialistaController::class, 'update'])->name('especialistas.update');
        Route::post('/metodos-pago', [\App\Http\Controllers\Panel\CatalogoController::class, 'guardarMetodoPago'])->name('metodos-pago.store');
        Route::put('/metodos-pago/{metodoPago}', [\App\Http\Controllers\Panel\CatalogoController::class, 'actualizarMetodoPago'])->name('metodos-pago.update');
        Route::post('/motivos-descuento', [\App\Http\Controllers\Panel\CatalogoController::class, 'guardarMotivo'])->name('motivos-descuento.store');
        Route::put('/motivos-descuento/{motivoDescuento}', [\App\Http\Controllers\Panel\CatalogoController::class, 'actualizarMotivo'])->name('motivos-descuento.update');
        Route::patch('/catalogos/{catalogo}/{id}/alternar', [\App\Http\Controllers\Panel\CatalogoController::class, 'alternar'])->name('catalogos.alternar');
    });

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
    // Nota: el parámetro se renombra a {metodoPago} para que coincida con el
    // type-hint del controlador (MetodoPago $metodoPago) y funcione el binding.
    Route::resource('metodos-pago', MetodoPagoController::class)
        ->parameters(['metodos-pago' => 'metodoPago']);

    // CRUD Motivos de Descuento
    // Nota: el parámetro se renombra a {motivoDescuento} para que coincida con el
    // type-hint del controlador (MotivoDescuento $motivoDescuento).
    Route::resource('motivos-descuento', MotivoDescuentoController::class)
        ->parameters(['motivos-descuento' => 'motivoDescuento']);

    // ===== NOTIFICACIONES =====
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        // Rutas específicas PRIMERO (antes de las rutas con parámetros)
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::get('/historial', [NotificacionController::class, 'historial'])->name('historial');
        
        // Programar notificaciones masivas (NUEVO)
        Route::get('/programar', [NotificacionController::class, 'programar'])->name('programar');
        Route::post('/guardar-programada', [NotificacionController::class, 'guardarProgramada'])->name('guardar-programada');
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

