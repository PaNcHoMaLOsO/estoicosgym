<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============ SCHEDULER ============

// Actualizar estados de inscripciones vencidas (ejecutar primero)
Schedule::command('inscripciones:actualizar-estados')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->name('actualizar-estados-inscripciones')
    ->onSuccess(function () {
        Log::info('✅ Estados de inscripciones actualizados automáticamente');
    })
    ->onFailure(function () {
        Log::error('❌ Error al actualizar estados de inscripciones');
    });

// Sincronizar estados de pagos
Schedule::command('pagos:sincronizar-estados')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->name('sincronizar-estados-pagos')
    ->onSuccess(function () {
        Log::info('✅ Estados de pagos sincronizados');
    })
    ->onFailure(function () {
        Log::error('❌ Error al sincronizar estados de pagos');
    });

// Desactivar clientes con membresías vencidas
Schedule::command('clientes:desactivar-vencidos')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->name('desactivar-clientes-vencidos')
    ->onSuccess(function () {
        Log::info('✅ Clientes vencidos desactivados automáticamente');
    })
    ->onFailure(function () {
        Log::error('❌ Error al desactivar clientes vencidos');
    });
