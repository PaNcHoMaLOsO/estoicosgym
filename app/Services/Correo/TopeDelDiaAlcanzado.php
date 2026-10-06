<?php

namespace App\Services\Correo;

use RuntimeException;

/**
 * El correo no salió porque hoy ya se llegó al tope, no porque fallara.
 *
 * Tiene su propia clase para que quien manda en tanda lo distinga de un fallo
 * de verdad: el mensaje promete «los que faltan salen mañana», y eso solo es
 * cierto si la fila se deja pendiente para mañana sin gastarle un intento.
 * Contarlo como fallo la dejaba «fallida», y tres días de tope la mataban.
 *
 * Hereda de RuntimeException para que lo que ya lo atrapaba así siga igual.
 */
class TopeDelDiaAlcanzado extends RuntimeException
{
}
