<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_precio_membresia
 * @property \Illuminate\Support\Carbon $created_at
 * @property string $precio_anterior Precio anterior
 * @property string $precio_nuevo Precio nuevo
 * @property string|null $razon_cambio Razón del cambio
 * @property string|null $usuario_cambio Usuario que realizó el cambio
 * @property-read \App\Models\PrecioMembresia $precioMembresia
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereIdPrecioMembresia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio wherePrecioAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio wherePrecioNuevo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereRazonCambio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistorialPrecio whereUsuarioCambio($value)
 * @mixin \Eloquent
 */
class HistorialPrecio extends Model
{
    protected $table = 'historial_precios';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'id_precio_membresia',
        'precio_anterior',
        'precio_nuevo',
        'razon_cambio',
        'usuario_cambio',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function precioMembresia()
    {
        return $this->belongsTo(PrecioMembresia::class, 'id_precio_membresia');
    }
}
