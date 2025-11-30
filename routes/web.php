<?php

use Illuminate\Support\Facades\Route;
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

Route::get('/', function () {
    return redirect()->route('dashboard');
});

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
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::get('/{notificacion}', [NotificacionController::class, 'show'])->name('show');
        Route::post('/{notificacion}/reenviar', [NotificacionController::class, 'reenviar'])->name('reenviar');
        Route::post('/{notificacion}/cancelar', [NotificacionController::class, 'cancelar'])->name('cancelar');
        Route::post('/ejecutar', [NotificacionController::class, 'ejecutar'])->name('ejecutar');
        Route::get('/{notificacion}/logs', [NotificacionController::class, 'logs'])->name('logs');
        
        // Plantillas
        Route::get('/config/plantillas', [NotificacionController::class, 'plantillas'])->name('plantillas');
        Route::get('/config/plantillas/{tipoNotificacion}/editar', [NotificacionController::class, 'editarPlantilla'])->name('plantillas.editar');
        Route::put('/config/plantillas/{tipoNotificacion}', [NotificacionController::class, 'actualizarPlantilla'])->name('plantillas.actualizar');
    });
});

// API Routes - Grupo con prefijo 'api'
Route::prefix('api')->group(function () {
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
