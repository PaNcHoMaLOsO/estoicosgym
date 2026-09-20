<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $nombre
 * @property int $duracion_meses Meses de duración (0 para pase diario)
 * @property int $duracion_dias 0 para mensuales, 1 para pase diario, 365 para anual
 * @property string|null $descripcion
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PrecioMembresia> $precios
 * @property-read int|null $precios_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereDuracionDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereDuracionMeses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membresia whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Membresia extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'membresias';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'nombre',
        'duracion_meses',
        'duracion_dias',
        'max_pausas',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'max_pausas' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function precios()
    {
        return $this->hasMany(PrecioMembresia::class, 'id_membresia');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_membresia');
    }

    /**
     * Los planes que son un PASE y no una mensualidad: sin meses y de un día.
     *
     * Quien compra un pase está de paso, como el huésped que entra por canje.
     * Sus pases vencen al día siguiente y, mezclados con las mensualidades,
     * llenaban «vencidos» y «se fueron sin renovar» de gente que nunca pensó
     * quedarse. Las listas los muestran aparte con esta misma regla.
     */
    public function scopePases($consulta)
    {
        return $consulta->where('duracion_meses', 0)->where('duracion_dias', '<=', 1);
    }

    public function esPase(): bool
    {
        return (int) $this->duracion_meses === 0 && (int) $this->duracion_dias <= 1;
    }

    /**
     * El último día que sirve una membresía de este plan que empieza en $inicio.
     *
     * UNA sola regla para todo lo que crea una membresía —inscribir, renovar,
     * cambiar de plan y registrar a un socio nuevo—. El registro de socio nuevo
     * llevaba la suya (inicio + días, sin descontar el primero y sin mirar los
     * meses): daba un día de más, un pase diario de dos días, y un plan cargado
     * solo en meses vencía el mismo día en que empezaba.
     *
     * Con días mandan los días: inicio + días − 1, porque el primero cuenta.
     * Con meses: inicio + meses − 1 día.
     */
    public function vencimientoDesde(CarbonInterface $inicio): CarbonInterface
    {
        $dia = $inicio->copy()->startOfDay();

        if ((int) $this->duracion_dias > 0) {
            return $dia->addDays((int) $this->duracion_dias)->subDay();
        }

        return $dia->addMonths(max(1, (int) $this->duracion_meses))->subDay();
    }
}
