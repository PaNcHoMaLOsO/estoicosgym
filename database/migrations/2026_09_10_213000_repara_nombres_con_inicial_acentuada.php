<?php

use App\Models\Cliente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Vuelve a escribir los nombres que se guardaron en minúscula.
 *
 * El mutador del modelo usaba ucwords(), que trabaja por bytes y no puede poner
 * en mayúscula una letra acentuada: todo socio cuyo nombre o apellido empezara
 * por vocal con tilde —los Álvarez, las Ángela, los Óscar— quedó guardado en
 * minúscula, y así salía en la ficha, en los informes y en los correos.
 *
 * El mutador ya está arreglado; esto arregla lo que quedó escrito. Se guarda
 * con save() para que pase por él y no haya que repetir aquí la misma regla.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Los borrados tambien: si alguien restaura una ficha, no tiene por
        // que volver con el nombre mal escrito.
        Cliente::withTrashed()
            ->select(['id', 'nombres', 'apellido_paterno', 'apellido_materno'])
            ->chunkById(200, function ($clientes) {
                foreach ($clientes as $cliente) {
                    $antes = [
                        $cliente->nombres,
                        $cliente->apellido_paterno,
                        $cliente->apellido_materno,
                    ];

                    // Reasignar dispara el mutador, que ya escribe bien.
                    $cliente->nombres = $cliente->nombres;
                    $cliente->apellido_paterno = $cliente->apellido_paterno;
                    $cliente->apellido_materno = $cliente->apellido_materno;

                    $despues = [
                        $cliente->nombres,
                        $cliente->apellido_paterno,
                        $cliente->apellido_materno,
                    ];

                    // Solo se toca lo que de verdad cambia: así no se remueve
                    // updated_at de todas las fichas del gimnasio.
                    if ($antes !== $despues) {
                        $cliente->saveQuietly();
                    }
                }
            });
    }

    public function down(): void
    {
        // No se deshace: volver a escribir «álvarez» en minúscula no arregla
        // nada y perderia la correccion.
    }
};
