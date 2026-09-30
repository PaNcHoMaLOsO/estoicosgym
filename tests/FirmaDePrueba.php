<?php

namespace Tests;

use App\Models\Contrato;
use App\Services\ContratoDigitalService;

/**
 * Lo que manda el formulario de firma, armado para las pruebas.
 *
 * La firma es un PNG hecho a mano, sin la extensión GD: fondo blanco con una
 * raya en diagonal, porque un recuadro en blanco ya no se acepta como firma.
 */
trait FirmaDePrueba
{
    protected function pngDeFirma(int $ancho = 300, int $alto = 100, bool $conTrazo = true): string
    {
        $filas = '';

        for ($y = 0; $y < $alto; $y++) {
            $fila = str_repeat("\xff\xff\xff", $ancho);

            if ($conTrazo) {
                // Una raya de tres píxeles que baja de izquierda a derecha.
                $x = (int) floor($y * ($ancho - 3) / max(1, $alto - 1));
                $fila = substr_replace($fila, str_repeat("\x11\x11\x14", 3), $x * 3, 9);
            }

            $filas .= "\0" . $fila;
        }

        $trozo = fn (string $tipo, string $datos) => pack('N', strlen($datos)) . $tipo . $datos . pack('N', crc32($tipo . $datos));

        $png = "\x89PNG\r\n\x1a\n"
            . $trozo('IHDR', pack('NNCCCCC', $ancho, $alto, 8, 2, 0, 0, 0))
            . $trozo('IDAT', gzcompress($filas))
            . $trozo('IEND', '');

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * La huella del texto que se estaría leyendo ahora en el enlace pendiente
     * más reciente: la misma que lleva el campo oculto de la página.
     */
    protected function lecturaDeHoy(): string
    {
        $contrato = Contrato::whereNull('firmado_en')->latest('id')->first();

        return $contrato
            ? app(ContratoDigitalService::class)->documento($contrato, now())['lectura']
            : str_repeat('0', 64);
    }
}
