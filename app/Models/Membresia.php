<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membresia extends Model
{
    protected $table = 'membresias';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'duracion_meses',
        'duracion_dias',
        'descripcion',
        'activo',
    ];

    public function precios()
    {
        return $this->hasMany(PrecioMembresia::class, 'id_membresia');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_membresia');
    }
}
