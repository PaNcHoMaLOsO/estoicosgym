<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    use SoftDeletes;

    protected $table = 'motivos_descuento';
    // La clave es `id` y la pone la base sola, como en todas las demas:
    // aqui decia `$incrementing = false`, que le dice a Eloquent que NO
    // recoja el id despues de insertar. El modelo que devolvia create()
    // salia sin id, asi que ->fresh(), ->refresh() y cualquier relacion
    // sobre esa instancia no encontraban nada. La fila se escribia bien:
    // fallaba solo el objeto que quedaba en la mano.
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
