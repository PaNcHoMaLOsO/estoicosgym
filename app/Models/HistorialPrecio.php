<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialPrecio extends Model
{
    protected $table = 'historial_precios';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_precio_membresia',
        'precio_anterior_normal',
        'precio_anterior_convenio',
        'precio_nuevo_normal',
        'precio_nuevo_convenio',
        'fecha_cambio',
        'motivo_cambio',
        'usuario_modificador',
    ];

    protected $casts = [
        'fecha_cambio' => 'date',
    ];

    public function precioMembresia()
    {
        return $this->belongsTo(PrecioMembresia::class, 'id_precio_membresia');
    }
}
