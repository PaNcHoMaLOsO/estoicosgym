<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotivoDescuento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MotivoDescuento extends Model
{
    protected $table = 'motivos_descuento';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_motivo_descuento');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_motivo_descuento');
    }
}
