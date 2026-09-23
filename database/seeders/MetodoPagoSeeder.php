<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
            [
                'nombre' => 'Efectivo',
                'descripcion' => 'Pago en efectivo en el gimnasio',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Transferencia',
                'descripcion' => 'Transferencia bancaria',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Tarjeta',
                'descripcion' => 'Tarjeta de débito o crédito',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
            /*
             * PARA LO QUE VINO DE LAS PLANILLAS VIEJAS.
             *
             * Ahí no quedó anotado si el socio pagó en efectivo o transfirió, y
             * darlo por efectivo hacía que el informe por medio de pago dijera
             * una cifra falsa con toda seguridad —y una cifra falsa es peor que
             * una que falta, porque nadie la revisa—.
             *
             * Va apagado: no se ofrece al cobrar en el mesón, donde el medio sí
             * se sabe. Pero se sigue nombrando en los informes.
             */
            [
                'nombre' => 'Sin registrar',
                'descripcion' => 'Pagos traídos de las planillas antiguas: no quedó anotado con qué se pagaron.',
                'requiere_comprobante' => false,
                'activo' => false,
            ],
        ];

        DB::table('metodos_pago')->insert(array_map(
            fn ($metodo) => $metodo + ['created_at' => now(), 'updated_at' => now()],
            $metodos
        ));
    }
}
