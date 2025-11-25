<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
