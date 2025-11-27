<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Rol extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'permisos',
        'activo',
    ];

    protected $casts = [
        'permisos' => 'json',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_rol');
    }
}
