<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RutValido implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Remover puntos y guiones
        $rut = preg_replace('/[.\-]/', '', $value);

        // Verificar formato: números + letra (ej: 12345678k)
        if (!preg_match('/^(\d{7,8})[k0-9]$/i', $rut)) {
            return false;
        }

        // Separar número y dígito verificador
        $numero = substr($rut, 0, -1);
        $dv = strtoupper(substr($rut, -1));

        // Calcular dígito verificador
        $suma = 0;
        $multiplicador = 2;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += intval($numero[$i]) * $multiplicador;
            $multiplicador++;
            if ($multiplicador > 7) {
                $multiplicador = 2;
            }
        }

        $dvCalculado = 11 - ($suma % 11);

        if ($dvCalculado == 11) {
            $dvCalculado = 0;
        } elseif ($dvCalculado == 10) {
            $dvCalculado = 'K';
        }

        return $dv === strval($dvCalculado);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El RUT ingresado no es válido. Formato: XX.XXX.XXX-X o XXXXXXXX-X';
    }
}
