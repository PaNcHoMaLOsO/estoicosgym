<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


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
