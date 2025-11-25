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
use App\Http\Controllers\Api\InscripcionApiController;
use App\Http\Controllers\Api\SearchApiController;
use App\Http\Controllers\Api\MembresiaApiController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Rutas Admin - Grupo con prefijo 'admin'
Route::prefix('admin')->name('admin.')->group(function () {
    // CRUD Clientes
    Route::resource('clientes', ClienteController::class);

    // CRUD Convenios
    Route::resource('convenios', ConvenioController::class);

    // CRUD Membresias
    Route::resource('membresias', MembresiaController::class);

    // CRUD Inscripciones
    Route::resource('inscripciones', InscripcionController::class);

    // CRUD Pagos
    Route::resource('pagos', PagoController::class);

    // CRUD Métodos de Pago
    Route::resource('metodos-pago', MetodoPagoController::class);

    // CRUD Motivos de Descuento
    Route::resource('motivos-descuento', MotivoDescuentoController::class);
});

// API Routes - Grupo con prefijo 'api'
Route::prefix('api')->group(function () {
    // Búsqueda
    Route::get('/clientes/search', [SearchApiController::class, 'searchClientes']);
    Route::get('/inscripciones/search', [SearchApiController::class, 'searchInscripciones']);
    
    // Membresias
    Route::get('/membresias', [MembresiaApiController::class, 'index']);
    Route::get('/membresias/search', [MembresiaApiController::class, 'search']);
    Route::get('/membresias/{id}', [MembresiaApiController::class, 'show']);
    
    // Obtener descuento de convenio
    Route::get('/convenios/{id}/descuento', [InscripcionApiController::class, 'getConvenioDescuento']);
    
    // Calcular precio final y fecha vencimiento
    Route::post('/inscripciones/calcular', [InscripcionApiController::class, 'calcular']);
});
