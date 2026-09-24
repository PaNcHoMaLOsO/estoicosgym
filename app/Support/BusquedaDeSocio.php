<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Buscar a un socio por su nombre o por su RUT, escrito como sea.
 *
 * EL RUT SE ESCRIBE DE CINCO FORMAS y en el mesón se teclea siempre la más
 * corta. En la ficha está guardado «21.410.708-2», y quien atiende escribe
 * «21410708» —sin puntos, sin guion y sin dígito verificador, que es lo que se
 * lee de un carnet apurado—. Comparando el texto tal cual, esa búsqueda no
 * encontraba nada y el socio parecía no existir: se le creaba otra ficha.
 *
 * Por eso el RUT se compara SIN PUNTOS NI GUION por los dos lados. `REPLACE`
 * anidado es feo, pero funciona igual en MySQL y en SQLite —donde corren las
 * pruebas— y no obliga a mantener una columna más al día.
 *
 * El nombre se parte en palabras: «juan hernandez» encuentra a Juan Francisco
 * Hernández Iturra, que con la cadena entera no aparecía.
 */
class BusquedaDeSocio
{
    /** Cómo queda un RUT para compararlo: solo dígitos y la K. */
    public static function soloDigitos(string $texto): string
    {
        return strtoupper(preg_replace('/[^0-9kK]/', '', $texto));
    }

    /** ¿Lo que se escribió se parece a un RUT? Tres cifras ya bastan. */
    public static function pareceRut(string $texto): bool
    {
        return (bool) preg_match('/^\d[\d.\-]*[\dkK]?$/', trim($texto))
            && strlen(self::soloDigitos($texto)) >= 3;
    }

    /**
     * Aplica la búsqueda sobre una consulta de clientes.
     *
     * @param  Builder  $consulta  Sobre la tabla `clientes` (o dentro de un whereHas).
     */
    public static function aplicar(Builder $consulta, string $texto): Builder
    {
        $texto = trim($texto);

        if ($texto === '') {
            return $consulta;
        }

        return $consulta->where(function (Builder $q) use ($texto) {
            if (self::pareceRut($texto)) {
                $digitos = self::soloDigitos($texto);

                $q->orWhereRaw(
                    "REPLACE(REPLACE(UPPER(run_pasaporte), '.', ''), '-', '') LIKE ?",
                    ["%{$digitos}%"]
                );
            }

            // Cada palabra tiene que estar en alguna parte del nombre: así
            // «hernandez juan» encuentra lo mismo que «juan hernandez».
            foreach (preg_split('/\s+/', $texto) as $palabra) {
                if ($palabra === '') {
                    continue;
                }

                // Solo si tiene números: sin esto, buscar «juan» acababa
                // comparando el celular contra «%%», que es todo el mundo.
                $numeros = preg_replace('/\D/', '', $palabra);

                $q->orWhere(function (Builder $q) use ($palabra, $numeros) {
                    // «Parecido» y no «like»: en PostgreSQL LIKE distingue
                    // mayúsculas y tildes, y «hernandez» no encontraba a
                    // «Hernández». Ver App\Support\Parecido.
                    $q->whereParecido('nombres', "%{$palabra}%")
                        ->orWhereParecido('apellido_paterno', "%{$palabra}%")
                        ->orWhereParecido('apellido_materno', "%{$palabra}%")
                        ->orWhereParecido('run_pasaporte', "%{$palabra}%")
                        ->orWhereParecido('email', "%{$palabra}%");

                    // El celular, igual que el RUT: guardado con espacios o con
                    // +56, y tecleado a secas.
                    if (strlen($numeros) >= 4) {
                        $q->orWhereRaw(
                            "REPLACE(REPLACE(REPLACE(celular, ' ', ''), '+', ''), '-', '') LIKE ?",
                            ["%{$numeros}%"]
                        );
                    }
                });
            }
        });
    }
}
