<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Lo que se le presta a una institución, y a cuánto la hora.
 *
 * EL PRECIO VA CON IVA INCLUIDO porque así se acuerda y así se habla: «la hora
 * sale treinta mil». El neto y el IVA se sacan al cerrar el mes, que es cuando
 * hacen falta para escribir la factura; guardado al revés, habría que hacer la
 * cuenta de cabeza cada vez que alguien pregunta cuánto se cobra.
 */
class Taller extends Model
{
    use SoftDeletes;

    protected $table = 'talleres';

    protected $fillable = [
        'uuid', 'id_institucion', 'nombre', 'descripcion_factura',
        'precio_hora', 'horario', 'activo',
    ];

    protected $casts = [
        'precio_hora' => 'integer',
        'horario' => 'array',
        'activo' => 'boolean',
    ];

    /** Los días de la semana como los escribe el horario, de lunes a domingo. */
    public const DIAS = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    protected static function booted(): void
    {
        static::creating(fn (self $taller) => $taller->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function horas(): HasMany
    {
        return $this->hasMany(HoraTaller::class, 'id_taller');
    }

    public function cobros(): HasMany
    {
        return $this->hasMany(CobroTaller::class, 'id_taller');
    }

    /**
     * Las clases que TOCARÍAN en un mes, según el horario semanal.
     *
     * SE PROPONEN, NO SE DAN POR HECHAS: hubo feriado, se suspendió, el colegio
     * estaba de vacaciones. Por eso se ofrecen para revisarlas antes de
     * anotarlas. Pero contarlas a mano con el calendario abierto al lado —que
     * es como se hacía— es donde se pierde una hora, y una hora son $30.000.
     *
     * @return list<array{fecha:string, horas:float, detalle:string}>
     */
    public function clasesDe(Carbon $mes): array
    {
        $horario = $this->horario ?? [];
        $dia = $mes->copy()->startOfMonth();
        $fin = $mes->copy()->endOfMonth();
        $clases = [];

        while ($dia->lte($fin)) {
            // `dayOfWeek` va de 0 (domingo) a 6, y DIAS empieza en lunes.
            $nombre = self::DIAS[($dia->dayOfWeek + 6) % 7];

            foreach ($horario[$nombre] ?? [] as $tramo) {
                $desde = $tramo[0] ?? null;
                $hasta = $tramo[1] ?? null;

                if (! $desde || ! $hasta) {
                    continue;
                }

                $clases[] = [
                    'fecha' => $dia->toDateString(),
                    'horas' => round(Carbon::parse($desde)->floatDiffInHours(Carbon::parse($hasta)), 2),
                    'detalle' => "{$desde} a {$hasta}",
                ];
            }

            $dia->addDay();
        }

        return $clases;
    }
}
