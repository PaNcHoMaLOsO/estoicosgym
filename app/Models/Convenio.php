<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convenio extends Model
{
    protected $table = 'convenios';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'contacto_nombre',
        'contacto_telefono',
        'contacto_email',
        'activo',
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_convenio');
    }
}
