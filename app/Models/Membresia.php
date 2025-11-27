<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
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
}
