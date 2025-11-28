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
        // Si está vacío, es válido (porque es optional)
        if (empty($value)) {
            return true;
        }

        // Limpiar el RUT - eliminar espacios, puntos y guiones, convertir a mayúsculas
        $rut = preg_replace('/[\s\.\-]/', '', strtoupper($value));
        $rut = preg_replace('/[^0-9K]/', '', $rut);

        // Debe tener mínimo 8 caracteres (7 dígitos + 1 verificador)
        if (strlen($rut) < 8 || strlen($rut) > 9) {
            return false;
        }

        // Separar el dígito verificador
        $dvExpected = substr($rut, -1);
        $rutNumber = substr($rut, 0, -1);

        // Calcular dígito verificador usando algoritmo módulo 11
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
        return 'El RUT ingresado no es válido. Formato correcto: 7.882.382-4 o 78823824';
    }
}
