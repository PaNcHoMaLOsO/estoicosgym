<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============ SCHEDULER ============
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
