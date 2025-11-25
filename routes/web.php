<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\PagoController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Clientes
Route::resource('clientes', ClienteController::class);

// Inscripciones
Route::resource('inscripciones', InscripcionController::class);

// Pagos
Route::resource('pagos', PagoController::class);
