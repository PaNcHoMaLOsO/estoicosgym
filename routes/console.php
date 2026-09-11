<?php

use App\Support\Programador;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * TODO ESTO CORRE SOLO SI WINDOWS LLAMA A `php artisan schedule:run` CADA
 * MINUTO. Si nadie registró esa tarea, nada de lo de abajo pasa y nadie se
 * entera. La orden para registrarla, con las rutas de este equipo, está en
 * Configuración → Correos y tareas automáticas, que también dice cuándo corrió
 * por última vez.
 *
 * Las horas salen de ese mismo sitio: si el computador del mesón se apaga de
 * noche, una tarea de la 01:00 no corre nunca, y Laravel no recupera la hora
 * perdida.
 */

// ============ EL LATIDO ============

// Cada vuelta deja la hora: así sabe Configuración si esto está corriendo.
Schedule::call(fn () => Programador::latir())
    ->everyMinute()
    ->name('latido-del-programador');

// ============ LA REVISIÓN DEL DÍA ============
// Tres pasos en orden, separados por diez minutos: primero se marcan las
// membresías vencidas, después los pagos, y al final se desactiva a quien
// quedó sin ninguna.

Schedule::command('inscripciones:actualizar-estados')
    ->dailyAt(Programador::hora('tareas.hora_revision'))
    ->withoutOverlapping()
    ->name('actualizar-estados-inscripciones')
    ->onSuccess(function () {
        Log::info('✅ Estados de inscripciones actualizados automáticamente');
    })
    ->onFailure(function () {
        Log::error('❌ Error al actualizar estados de inscripciones');
    });

Schedule::command('pagos:sincronizar-estados')
    ->dailyAt(Programador::hora('tareas.hora_revision', 10))
    ->withoutOverlapping()
    ->name('sincronizar-estados-pagos')
    ->onSuccess(function () {
        Log::info('✅ Estados de pagos sincronizados');
    })
    ->onFailure(function () {
        Log::error('❌ Error al sincronizar estados de pagos');
    });

Schedule::command('clientes:desactivar-vencidos')
    ->dailyAt(Programador::hora('tareas.hora_revision', 20))
    ->withoutOverlapping()
    ->name('desactivar-clientes-vencidos')
    ->onSuccess(function () {
        // El último paso: si llegó aquí, la revisión del día terminó.
        Programador::registrar('revision');
        Log::info('✅ Clientes vencidos desactivados automáticamente');
    })
    ->onFailure(function () {
        Log::error('❌ Error al desactivar clientes vencidos');
    });

// ============ LOS CORREOS ============

// Programar y enviar los avisos de membresías por vencer y vencidas, y los
// envíos programados para hoy.
Schedule::command('notificaciones:enviar --todo')
    ->dailyAt(Programador::hora('tareas.hora_avisos'))
    ->withoutOverlapping()
    ->name('enviar-notificaciones-diarias')
    ->onSuccess(function () {
        Programador::registrar('avisos');
        Log::info('✅ Notificaciones enviadas exitosamente');
    })
    ->onFailure(function () {
        Log::error('❌ Error al enviar notificaciones');
    });

// Segunda oportunidad para los que fallaron en la mañana.
Schedule::command('notificaciones:enviar --reintentar')
    ->dailyAt(Programador::hora('tareas.hora_reintento'))
    ->withoutOverlapping()
    ->name('reintentar-notificaciones')
    ->onSuccess(function () {
        Programador::registrar('reintento');
        Log::info('✅ Reintento de notificaciones completado');
    })
    ->onFailure(function () {
        Log::error('❌ Error al reintentar notificaciones');
    });
