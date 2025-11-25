<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\InscripcionController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Api\InscripcionApiController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Rutas Admin - Grupo con prefijo 'admin'
Route::prefix('admin')->name('admin.')->group(function () {
    // CRUD Clientes
    Route::resource('clientes', ClienteController::class);

    // CRUD Inscripciones
    Route::resource('inscripciones', InscripcionController::class);

    // CRUD Pagos
    Route::resource('pagos', PagoController::class);
});

// API Routes - Grupo con prefijo 'api'
Route::prefix('api')->group(function () {
    // Obtener datos de membresía
    Route::get('/membresias/{id}', [InscripcionApiController::class, 'showMembresia']);
    
    // Obtener descuento de convenio
    Route::get('/convenios/{id}/descuento', [InscripcionApiController::class, 'getConvenioDescuento']);
    
    // Calcular precio final y fecha vencimiento
    Route::post('/inscripciones/calcular', [InscripcionApiController::class, 'calcular']);
});
