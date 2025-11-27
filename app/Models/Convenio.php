<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Convenio extends Model
{
    protected $table = 'convenios';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'nombre',
        'tipo',
        'descripcion',
        'contacto_nombre',
        'contacto_telefono',
        'contacto_email',
        'activo',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_convenio');
    }
}
