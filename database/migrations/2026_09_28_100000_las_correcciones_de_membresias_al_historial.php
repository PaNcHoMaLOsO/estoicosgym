<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corregir una membresía (fechas, precio, descuento) queda en el historial.
 *
 * Antes no dejaba rastro: se podía bajar el precio y alargar el vencimiento
 * y nadie sabía quién ni cuándo. Mismo camino que la migración de la
 * renovación (2026_09_10_210000) para agrandar la lista de tipos.
 */
return new class extends Migration
{
    private const TIPOS = [
        'pausa',
        'reanudacion',
        'cambio_plan',
        'cambio_estado_inscripcion',
        'cambio_estado_cliente',
        'cancelacion_inscripcion',
        'suspension',
        'vencimiento',
        'renovacion',
        'traspaso',
        'inscripcion',
    ];

    public function up(): void
    {
        $this->admitir([...self::TIPOS, 'correccion']);
    }

    public function down(): void
    {
        DB::table('historial_cambios')
            ->where('tipo_cambio', 'correccion')
            ->update(['tipo_cambio' => 'cambio_estado_inscripcion']);

        $this->admitir(self::TIPOS);
    }

    /** @param list<string> $valores */
    private function admitir(array $valores): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $lista = implode(', ', array_map(fn (string $v) => "'" . $v . "'", $valores));

            DB::statement('ALTER TABLE historial_cambios DROP CONSTRAINT IF EXISTS historial_cambios_tipo_cambio_check');
            DB::statement("ALTER TABLE historial_cambios ADD CONSTRAINT historial_cambios_tipo_cambio_check CHECK (tipo_cambio IN ({$lista}))");

            return;
        }

        Schema::table('historial_cambios', function (Blueprint $tabla) use ($valores) {
            $tabla->enum('tipo_cambio', $valores)->change();
        });
    }
};
