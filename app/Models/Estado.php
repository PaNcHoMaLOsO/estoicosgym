<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'estados';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'categoria',
        'activo',
    ];

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_estado');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_estado');
    }
}
