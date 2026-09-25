<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Junta dos fichas de la misma persona en una.
 *
 * Todo lo de la ficha que se va pasa a la que se queda: membresías, pagos,
 * fiado, contratos, correos e historial. Los datos que le faltan a la que se
 * queda —el RUT, el celular, el correo— se toman de la otra; lo que ya tiene
 * no se pisa. La que se va queda en la papelera, sin nada colgando, con una
 * nota de adónde se fue.
 *
 * En una transacción: si algo falla a mitad, no queda la mitad de los pagos
 * en una ficha y la otra mitad en la otra.
 */
class JuntarFichas
{
    /** Datos que se completan desde la ficha que se va, si faltan. */
    private const COMPLETAR = [
        'run_pasaporte', 'celular', 'email', 'direccion', 'fecha_nacimiento',
        'contacto_emergencia', 'telefono_emergencia', 'foto_perfil',
        'apoderado_nombre', 'apoderado_rut', 'apoderado_email', 'apoderado_telefono', 'apoderado_parentesco',
        'apellido_materno', 'id_convenio',
    ];

    /** Dónde cuelga algo de un socio: tabla → columnas. */
    private const TABLAS = [
        'inscripciones' => ['id_cliente', 'id_cliente_original'],
        'pagos' => ['id_cliente'],
        'fiados' => ['id_cliente'],
        'contratos' => ['id_cliente'],
        'notificaciones' => ['id_cliente'],
        'historial_cambios' => ['cliente_id'],
        'historial_traspasos' => ['cliente_origen_id', 'cliente_destino_id'],
    ];

    public function juntar(Cliente $queda, Cliente $sale): Cliente
    {
        if ($queda->id === $sale->id) {
            throw ValidationException::withMessages(['sale' => 'Es la misma ficha.']);
        }

        if ($queda->datos_borrados_en || $sale->datos_borrados_en) {
            throw ValidationException::withMessages(['sale' => 'A una de las dos se le borraron los datos: no se puede juntar.']);
        }

        return DB::transaction(function () use ($queda, $sale) {
            foreach (self::TABLAS as $tabla => $columnas) {
                foreach ($columnas as $columna) {
                    DB::table($tabla)->where($columna, $sale->id)->update([$columna => $queda->id]);
                }
            }

            // Si las dos estaban marcadas como distintas con una tercera, la
            // marca se queda con la que sigue.
            DB::table('socios_distintos')->where('id_a', $sale->id)->orWhere('id_b', $sale->id)->delete();

            $completados = [];

            foreach (self::COMPLETAR as $campo) {
                if (blank($queda->{$campo}) && filled($sale->{$campo})) {
                    $completados[$campo] = $sale->{$campo};
                }
            }

            // El RUT es único, también en la papelera: primero se le quita a
            // la que se va.
            $rutQueSeVa = $sale->run_pasaporte;

            if ($rutQueSeVa !== null) {
                $sale->forceFill(['run_pasaporte' => null])->saveQuietly();
            }

            $nota = trim(implode("\n", array_filter([
                $queda->observaciones,
                $sale->observaciones ? "De la ficha #{$sale->id}: {$sale->observaciones}" : null,
                'Se juntó con la ficha #' . $sale->id . ' el ' . now()->format('d/m/Y')
                    . ($rutQueSeVa && ! isset($completados['run_pasaporte']) ? " (esa tenía el RUT {$rutQueSeVa})" : '') . '.',
            ])));

            $queda->forceFill($completados + [
                'observaciones' => $nota,
                // Si alguna estaba activa, la persona lo está.
                'activo' => $queda->activo || $sale->activo,
            ])->save();

            $sale->forceFill([
                'activo' => false,
                'observaciones' => trim(($sale->observaciones ? $sale->observaciones . "\n" : '')
                    . "Juntada con la ficha #{$queda->id} ({$queda->nombres} {$queda->apellido_paterno}) el " . now()->format('d/m/Y') . '.'),
            ])->saveQuietly();
            $sale->delete();

            return $queda->refresh();
        });
    }
}
