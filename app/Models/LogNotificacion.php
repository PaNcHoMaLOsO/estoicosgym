<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogNotificacion extends Model
{
    protected $table = 'log_notificaciones';

    public $timestamps = false;

    protected $fillable = [
        'id_notificacion',
        'accion',
        'detalle',
        'ip_servidor',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Acciones posibles
    const ACCION_PROGRAMADA = 'programada';
    const ACCION_ENVIANDO = 'enviando';
    const ACCION_ENVIADA = 'enviada';
    const ACCION_FALLIDA = 'fallida';
    const ACCION_REINTENTANDO = 'reintentando';
    const ACCION_CANCELADA = 'cancelada';

    public function notificacion()
    {
        return $this->belongsTo(Notificacion::class, 'id_notificacion');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }
}
