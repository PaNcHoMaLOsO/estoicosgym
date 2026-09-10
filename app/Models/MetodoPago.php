<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $requiere_comprobante Para futuro: pago online
 * @property int $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereRequiereComprobante($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MetodoPago whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MetodoPago extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'metodos_pago';
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
        'requiere_comprobante',
        'activo',
    ];

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_metodo_pago');
    }
}
