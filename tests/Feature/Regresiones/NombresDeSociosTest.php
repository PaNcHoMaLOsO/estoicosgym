<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\CasoConCatalogos;

/**
 * Regresión de cómo se escriben los nombres al guardarlos.
 *
 * El mutador usaba ucwords(), que trabaja por BYTES: pasaba el nombre a
 * minúsculas con mb_strtolower y luego no era capaz de volver a poner en
 * mayúscula una letra acentuada, porque ocupa dos bytes y no es una letra a-z.
 * Todos los Álvarez, las Ángela y los Óscar quedaron guardados en minúscula, y
 * así salían en la ficha, en los informes y en los correos.
 */
class NombresDeSociosTest extends CasoConCatalogos
{
    /** @return array<string,array{string,string}> */
    public static function nombres(): array
    {
        return [
            'inicial acentuada' => ['álvarez', 'Álvarez'],
            'todo en mayúsculas' => ['ÓSCAR', 'Óscar'],
            'eñe' => ['íñigo', 'Íñigo'],
            'dos palabras' => ['ángela maría', 'Ángela María'],
            'espacios de sobra' => ['  ana   sofía  ', 'Ana Sofía'],
            'con guion' => ['jean-luc', 'Jean-Luc'],
            'con apóstrofo' => ["o'brien", "O'Brien"],
            'ya bien escrito' => ['Pérez', 'Pérez'],
        ];
    }

    #[DataProvider('nombres')]
    public function test_el_nombre_se_guarda_escrito_como_un_nombre(string $entra, string $esperado): void
    {
        $cliente = Cliente::factory()->create(['nombres' => $entra]);

        $this->assertSame($esperado, $cliente->fresh()->nombres);
    }

    public function test_los_apellidos_siguen_la_misma_regla(): void
    {
        $cliente = Cliente::factory()->create([
            'apellido_paterno' => 'álvarez',
            'apellido_materno' => 'ÑUÑEZ',
        ]);

        $cliente = $cliente->fresh();

        $this->assertSame('Álvarez', $cliente->apellido_paterno);
        $this->assertSame('Ñuñez', $cliente->apellido_materno);
    }
}
