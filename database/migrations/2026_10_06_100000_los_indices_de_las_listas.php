<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los índices que pedían las listas y las cifras del panel.
 *
 * Las listas de Socios, Membresías y Pagos se ordenan por fecha de alta
 * (`created_at`), la Caja y el informe de ingresos suman lo cobrado del mesón
 * y de los talleres por `pagado_en`, y los filtros de Pagos cuentan por medio
 * de pago. Ninguna de esas columnas tenía índice: con unos pocos miles de filas
 * la base las recorría enteras en cada pantalla. En PostgreSQL una clave
 * foránea no se indexa sola, por eso van también los dos medios de pago.
 *
 * Solo agrega índices: no cambia ningún dato ni lo que se ve.
 */
return new class extends Migration
{
    /** tabla => [nombre del índice => columnas] */
    private const INDICES = [
        'clientes' => [
            'clientes_created_at_index' => ['created_at'],
            'clientes_apellido_paterno_nombres_index' => ['apellido_paterno', 'nombres'],
        ],
        'inscripciones' => [
            'inscripciones_created_at_index' => ['created_at'],
            'inscripciones_id_estado_fecha_vencimiento_index' => ['id_estado', 'fecha_vencimiento'],
        ],
        'pagos' => [
            'pagos_created_at_index' => ['created_at'],
            'pagos_id_metodo_pago_index' => ['id_metodo_pago'],
            'pagos_id_metodo_pago2_index' => ['id_metodo_pago2'],
        ],
        'fiados' => [
            'fiados_pagado_en_index' => ['pagado_en'],
        ],
        'cobros_taller' => [
            'cobros_taller_pagado_en_index' => ['pagado_en'],
        ],
        'notificaciones' => [
            'notificaciones_created_at_index' => ['created_at'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDICES as $tabla => $indices) {
            foreach ($indices as $nombre => $columnas) {
                // Por si alguna base ya lo tiene (creado a mano en el servidor).
                if (Schema::hasIndex($tabla, $nombre)) {
                    continue;
                }

                Schema::table($tabla, fn (Blueprint $t) => $t->index($columnas, $nombre));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDICES as $tabla => $indices) {
            foreach (array_keys($indices) as $nombre) {
                if (Schema::hasIndex($tabla, $nombre)) {
                    Schema::table($tabla, fn (Blueprint $t) => $t->dropIndex($nombre));
                }
            }
        }
    }
};
