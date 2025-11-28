<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RutValido implements Rule
{
    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value)
    {
        // Limpiar el RUT
        $rut = preg_replace('/[^0-9K]/', '', strtoupper($value));

        if (strlen($rut) < 8) {
            return false;
        }

        // Separar el dígito verificador
        $dvExpected = substr($rut, -1);
        $rutNumber = substr($rut, 0, -1);

        // Calcular dígito verificador
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($rutNumber) - 1; $i >= 0; $i--) {
            $sum += intval($rutNumber[$i]) * $multiplier;
            $multiplier++;

            if ($multiplier > 7) {
                $multiplier = 2;
            }
        }

        $dv = 11 - ($sum % 11);

        if ($dv == 11) {
            $dvCalculated = '0';
        } elseif ($dv == 10) {
            $dvCalculated = 'K';
        } else {
            $dvCalculated = strval($dv);
        }

        return $dvExpected === $dvCalculated;
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return 'El RUT/Pasaporte ingresado no es válido.';
    }
}
