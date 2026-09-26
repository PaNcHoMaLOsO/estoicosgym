<?php

namespace App\Http\Middleware;

use App\Support\Programador;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * La revisión del día, aunque nadie haya programado nada.
 *
 * MARCAR LAS VENCIDAS, CUADRAR LOS PAGOS Y DAR DE BAJA a quien quedó sin plan
 * dependía de que Windows llamara al programador cada minuto. Esa tarea quedó
 * apagada —abría ventanas negras en el mesón— y la revisión no corría nunca:
 * membresías que figuraban activas con la fecha vencida, trescientos socios
 * «activos» sin nada vigente, y la caja contando como vigente lo que no lo era.
 * Nadie se enteraba, porque nada fallaba: simplemente no pasaba.
 *
 * Ahora la corre la primera persona que abre el panel cada día. Va DESPUÉS de
 * mandar la pantalla —no la hace esperar— y una sola vez al día: si ya la hizo
 * el programador, o ya la hizo otra pestaña, no se repite.
 *
 * Los CORREOS no van aquí: mandar cien avisos cabe en una tarea de fondo, no al
 * final de la visita de alguien. Esos siguen en el programador.
 */
class RevisaElDia
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        // En las pruebas no: cada visita cambiaría los datos que la prueba está
        // mirando. La suya propia la enciende a mano.
        $enPruebas = app()->runningUnitTests() && ! config('app.revision_en_pruebas');

        if ($request->user() && ! $enPruebas) {
            $this->siHaceFalta();
        }

        return $respuesta;
    }

    private function siHaceFalta(): void
    {
        $hoy = now()->toDateString();

        // Ya la hizo hoy el programador de verdad.
        if (Programador::ultimaVez('revision')?->toDateString() === $hoy) {
            return;
        }

        // Una sola vez al día aunque entren diez a la vez: el primero se la
        // queda y los demás siguen de largo.
        if (! Cache::add("revision-del-dia:{$hoy}", true, now()->endOfDay())) {
            return;
        }

        app()->terminating(function () use ($hoy) {
            // Lo que se deja «para después» se queda anotado en la aplicación:
            // donde un proceso atiende varias visitas, volvería a correr en
            // cada una. Se mira otra vez si ya se hizo.
            if (Programador::ultimaVez('revision')?->toDateString() === $hoy) {
                return;
            }

            try {
                Artisan::call('inscripciones:actualizar-estados');
                Artisan::call('pagos:sincronizar-estados');
                Artisan::call('clientes:desactivar-vencidos');
                // Las fallas que no se repiten hace tres meses ya no dicen nada.
                \App\Support\RegistroDeFallas::limpiar();
                Programador::registrar('revision');
            } catch (\Throwable $e) {
                // Que falle la revisión no puede tumbar el panel: se anota y
                // mañana se intenta otra vez.
                Log::error('La revisión del día falló: ' . $e->getMessage());
            }
        });
    }
}
