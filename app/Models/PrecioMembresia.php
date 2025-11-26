<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_membresia
 * @property string $precio_normal
 * @property string|null $precio_convenio NULL si no aplica convenio
 * @property \Illuminate\Support\Carbon $fecha_vigencia_desde
 * @property \Illuminate\Support\Carbon|null $fecha_vigencia_hasta NULL = vigente actualmente
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HistorialPrecio> $historialPrecios
 * @property-read int|null $historial_precios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \App\Models\Membresia $membresia
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereFechaVigenciaDesde($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereFechaVigenciaHasta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereIdMembresia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia wherePrecioConvenio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia wherePrecioNormal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrecioMembresia whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PrecioMembresia extends Model
{
    protected $table = 'precios_membresias';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'id_membresia',
        'precio_normal',
        'precio_convenio',
        'fecha_vigencia_desde',
        'fecha_vigencia_hasta',
        'activo',
    ];

    protected $casts = [
        'fecha_vigencia_desde' => 'date',
        'fecha_vigencia_hasta' => 'date',
    ];

    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'id_membresia');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_precio_acordado');
    }

    public function historialPrecios()
    {
        return $this->hasMany(HistorialPrecio::class, 'id_precio_membresia');
    }
}
