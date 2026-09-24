<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los pagos que vinieron de las planillas no eran en efectivo.
 *
 * AL IMPORTAR SE LES PUSO «EFECTIVO» porque había que ponerles algo: un pago
 * sin medio no se podía guardar. Pero la planilla nunca dijo cómo pagó cada
 * socio, así que la base terminó afirmando que entraron $73.953.000 en efectivo
 * —1.770 pagos—, y eso NO ES VERDAD: buena parte fueron transferencias.
 *
 * Y una cifra falsa es peor que una que falta. El informe de ingresos por medio
 * de pago sale mal, la comparación con el banco sale mal, y nadie se entera
 * porque el dato parece completo.
 *
 * Así que estos pagos pasan a un medio que dice justo lo que se sabe de ellos:
 * «Sin registrar». Queda apagado, para que no aparezca al cobrar en el mesón
 * —de hoy en adelante sí se sabe si fue efectivo o transferencia—, pero sigue
 * nombrándose en los informes, que es donde importa distinguir «esto fue
 * efectivo» de «esto no lo sabemos».
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sin pagos importados no hay nada que arreglar, y el medio ya lo
        // deja puesto el catálogo: crearlo aquí también lo colaría ANTES que
        // el efectivo en una base recién hecha.
        if (! DB::table('pagos')->where('observaciones', 'like', 'Importado de %')->exists()) {
            return;
        }

        $id = DB::table('metodos_pago')->where('nombre', 'Sin registrar')->value('id');

        if (! $id) {
            $id = DB::table('metodos_pago')->insertGetId([
                'nombre' => 'Sin registrar',
                'descripcion' => 'Pagos traídos de las planillas antiguas: no quedó anotado si fueron en efectivo o por transferencia.',
                'requiere_comprobante' => false,
                // Apagado: no se ofrece al cobrar. De hoy en adelante el medio
                // se elige de verdad.
                'activo' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Los reconoce su propia observación, que la importación escribió en
        // cada uno: «Importado de Planilla Socios definitva 1.xlsx…».
        DB::table('pagos')
            ->where('observaciones', 'like', 'Importado de %')
            ->update(['id_metodo_pago' => $id]);
    }

    /**
     * No se deshace: volver a decir que fueron en efectivo sería inventarse el
     * dato otra vez, que es justo lo que esto vino a arreglar.
     */
    public function down(): void
    {
        //
    }
};
