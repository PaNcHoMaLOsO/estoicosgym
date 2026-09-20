<?php

namespace App\Http\Controllers\Traits;

use App\Models\Cliente;
use Illuminate\Http\Request;

/**
 * Volver a la ficha del socio desde donde se vino.
 *
 * Cobrar, renovar e inscribir se abren en una ventana SOBRE la ficha del socio.
 * Al guardar, el sitio natural al que volver es esa ficha: quien atiende tiene
 * a la persona delante y sigue con ella. Sin esto se terminaba en la pantalla
 * del pago o de la membresía recién creada, y había que buscar al socio otra
 * vez.
 *
 * El destino NO se recibe como dirección, solo como el uuid del socio: una
 * dirección venida del navegador se puede cambiar, y redirigir a donde diga un
 * parámetro es abrir la puerta a mandar a la gente a cualquier sitio.
 */
trait VuelveAlSocio
{
    /**
     * A dónde volver: la ficha del socio si se pidió y existe, o `$otro`.
     *
     * @param string $otro la ruta de siempre, la que se usa cuando no se vino
     *                     desde una ficha
     */
    protected function volverA(Request $request, string $otro, mixed ...$parametros): string
    {
        $uuid = (string) $request->input('volver', '');

        if ($uuid !== '' && Cliente::where('uuid', $uuid)->exists()) {
            return route('panel.clientes.show', $uuid);
        }

        return route($otro, ...$parametros);
    }
}
