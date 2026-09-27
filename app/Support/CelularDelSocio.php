<?php

namespace App\Support;

use App\Models\Cliente;
use App\Services\RegistroClienteService;
use Illuminate\Http\Request;

/**
 * El celular del socio, pedido cuando vuelve.
 *
 * DE 1.704 SOCIOS, 23 TENÍAN CELULAR: las planillas casi nunca lo traían, y
 * sin él no le llegan los avisos de vencimiento ni sale en la lista de a quién
 * llamar. El momento de pedirlo es cuando el socio está delante: renovando o
 * inscribiéndose. Si no tiene, esas pantallas muestran el campo; si lo
 * escriben, se anota con la misma membresía. Si ya tenía, no se toca.
 */
class CelularDelSocio
{
    /** Valida el celular que vino con el formulario; null si no vino. */
    public static function validar(Request $request): ?string
    {
        $celular = trim((string) $request->input('celular_socio', ''));

        // Vacío o solo el prefijo («+56 9 »): no lo dio, y se sigue igual.
        if ($celular === '' || preg_replace('/\D/', '', $celular) === '569') {
            return null;
        }

        $request->validate([
            'celular_socio' => ['string', 'regex:' . RegistroClienteService::TELEFONO],
        ], [
            'celular_socio.regex' => 'Celular no válido. Uno chileno es +56 9 1234 5678.',
        ]);

        return $celular;
    }

    /** Lo anota si el socio no tenía. */
    public static function anotar(?Cliente $cliente, ?string $celular): void
    {
        if ($cliente && $celular && blank($cliente->celular)) {
            $cliente->update(['celular' => $celular]);
        }
    }
}
