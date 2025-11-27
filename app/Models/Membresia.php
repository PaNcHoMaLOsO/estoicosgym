<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Membresia extends Model
{
    protected $table = 'membresias';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'nombre',
        'duracion_meses',
        'duracion_dias',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
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

    public function precios()
    {
        return $this->hasMany(PrecioMembresia::class, 'id_membresia');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_membresia');
    }
}
