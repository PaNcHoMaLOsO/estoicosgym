<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_inscripcion',
        'tipo',
        'canal',
        'destinatario',
        'asunto',
        'mensaje',
        'estado',
        'fecha_envio',
        'error_mensaje',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }
}
