<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Evita que un envio duplicado cree el registro dos veces.
 *
 * POR QUE SE DUPLICABAN LOS PAGOS. Antes esto preguntaba `Cache::has()` y solo
 * marcaba el token DESPUES de terminar. Eso es comprobar-y-actuar, y no es
 * atomico: con dos peticiones a la vez —un doble clic— las dos preguntan antes
 * de que ninguna haya marcado nada, las dos ven la via libre y las dos cobran.
 * No fallaba siempre, solo cuando las dos coincidian en el aire, que es
 * exactamente lo que se veia: «a veces se duplicaban».
 *
 * Ahora se RESERVA el turno con Cache::add(), que escribe solo si la clave no
 * existe y resuelve el empate en una sola operacion: la segunda peticion se
 * encuentra la clave puesta y se va.
 *
 * CUANDO LLAMARLO: lo mas tarde posible, justo antes de escribir en la base. Si
 * se reserva antes de validar, un formulario rechazado deja el turno pillado y
 * quien corrige el error no puede reenviar.
 */
trait ValidatesFormToken
{
    /** Ventana de la reserva. Cubre de sobra un doble clic y un reintento. */
    private const VENTANA_ENVIO = 120;

    /**
     * Reserva el turno de este envio.
     *
     * Devuelve false si ya estaba reservado, o sea: esto es un duplicado.
     */
    protected function validateFormToken(Request $request, string $action): bool
    {
        return Cache::add($this->claveDeEnvio($request, $action), true, self::VENTANA_ENVIO);
    }

    /**
     * Libera el turno para que se pueda reintentar.
     *
     * Se llama cuando el envio NO llego a crear nada: un error de validacion,
     * una excepcion. Si no se libera, quien arregla el dato se queda sin poder
     * reenviar hasta que caduque la ventana.
     */
    protected function releaseFormToken(Request $request, string $action): void
    {
        Cache::forget($this->claveDeEnvio($request, $action));
    }

    /**
     * Se mantiene por las llamadas que ya existen. Con la reserva hecha antes
     * de escribir, marcar el exito ya no hace falta: el turno sigue tomado y es
     * lo que bloquea al duplicado que venga detras.
     */
    protected function invalidateFormToken(Request $request, string $action): void
    {
        // Intencionadamente sin efecto.
    }

    /**
     * Clave del envio.
     *
     * Con token de formulario se usa ese. SIN token se usa la huella de lo
     * enviado, porque seis formularios (convenios, metodos de pago y motivos de
     * descuento) nunca lo incluyeron y el codigo anterior los dejaba pasar sin
     * comprobar nada: se quedaban por completo sin proteccion. Dos envios
     * IDENTICOS seguidos son un doble clic; dos registros distintos difieren en
     * algo y no se estorban.
     */
    private function claveDeEnvio(Request $request, string $action): string
    {
        $usuario = auth('web')->id() ?? session()->getId();
        $token = (string) $request->input('form_submit_token');

        $huella = $token !== ''
            ? substr($token, 0, 40)
            : 'sin-token-' . md5((string) json_encode($request->except([
                '_token', '_method', 'form_submit_token',
            ])));

        return "envio:{$usuario}:{$action}:{$huella}";
    }
}
