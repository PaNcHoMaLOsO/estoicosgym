<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    protected $table = 'metodos_pago';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
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
