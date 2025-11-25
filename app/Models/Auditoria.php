<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'tabla_afectada',
        'id_registro_afectado',
        'accion',
        'datos_anteriores',
        'datos_nuevos',
        'usuario_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'datos_anteriores' => 'json',
        'datos_nuevos' => 'json',
    ];
}
