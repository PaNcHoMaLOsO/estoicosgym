<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `renovacion` faltaba en los tipos de cambio que admite el historial.
 *
 * Renovar intentaba anotarlo con ese nombre y la columna solo admite una lista
 * cerrada que no lo incluía, así que la fila se rechazaba. Como la escritura
 * del historial iba dentro de la misma transacción que la renovación, se caía
 * TODA la renovación con ella: renovar no ha funcionado nunca.
 *
 * De paso entran los otros dos que el sistema ya provoca de hecho —el traspaso
 * y el alta— para que el historial pueda contar la vida entera de una
 * membresía sin volver a chocar con esto.
 *
 * La lista se redefine con `change()` y no con SQL a mano porque cada motor la
 * guarda a su manera: MySQL con un ENUM y SQLite —el de las pruebas— con un
 * CHECK. Tocar solo uno deja el otro rechazando las filas nuevas, que es
 * exactamente lo que pasaba.
 */
return new class extends Migration
{
    private const NUEVOS = ['renovacion', 'traspaso', 'inscripcion'];

    private const EXISTENTES = [
        'pausa',
        'reanudacion',
        'cambio_plan',
        'cambio_estado_inscripcion',
        'cambio_estado_cliente',
        'cancelacion_inscripcion',
        'suspension',
        'vencimiento',
    ];

    public function up(): void
    {
        $this->admitir([...self::EXISTENTES, ...self::NUEVOS]);
    }

    public function down(): void
    {
        // Las filas que ya usen un tipo nuevo dejarían de caber en la lista
        // estrecha: se pasan al genérico antes de estrecharla.
        DB::table('historial_cambios')
            ->whereIn('tipo_cambio', self::NUEVOS)
            ->update(['tipo_cambio' => 'cambio_estado_inscripcion']);

        $this->admitir(self::EXISTENTES);
    }

    /** @param list<string> $valores */
    private function admitir(array $valores): void
    {
        // En PostgreSQL un enum es un texto con una restricción CHECK, y
        // ->change() arma una sentencia que PostgreSQL no acepta. Se cambia
        // la restricción a mano: la vieja fuera, la nueva con la lista ancha.
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
