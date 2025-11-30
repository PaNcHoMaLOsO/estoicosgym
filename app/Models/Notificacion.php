<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'uuid',
        'id_tipo_notificacion',
        'id_cliente',
        'id_inscripcion',
        'id_pago',
        'email_destino',
        'asunto',
        'contenido',
        'id_estado',
        'fecha_programada',
        'fecha_envio',
        'intentos',
        'max_intentos',
        'error_mensaje',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_envio' => 'datetime',
        'intentos' => 'integer',
        'max_intentos' => 'integer',
    ];

    // Estados de notificación (rango 600)
    const ESTADO_PENDIENTE = 600;
    const ESTADO_ENVIADO = 601;
    const ESTADO_FALLIDO = 602;
    const ESTADO_CANCELADO = 603;

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

    // Relaciones
    public function tipoNotificacion()
    {
        return $this->belongsTo(TipoNotificacion::class, 'id_tipo_notificacion');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'codigo');
    }

    public function logs()
    {
        return $this->hasMany(LogNotificacion::class, 'id_notificacion');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('id_estado', self::ESTADO_PENDIENTE);
    }

    public function scopeEnviadas($query)
    {
        return $query->where('id_estado', self::ESTADO_ENVIADO);
    }

    public function scopeFallidas($query)
    {
        return $query->where('id_estado', self::ESTADO_FALLIDO);
    }

    public function scopeParaEnviarHoy($query)
    {
        return $query->where('id_estado', self::ESTADO_PENDIENTE)
                     ->where('fecha_programada', '<=', now()->toDateString())
                     ->where('intentos', '<', \DB::raw('max_intentos'));
    }

    // Helpers
    public function estaPendiente(): bool
    {
        return $this->id_estado === self::ESTADO_PENDIENTE;
    }

    public function fueEnviada(): bool
    {
        return $this->id_estado === self::ESTADO_ENVIADO;
    }

    public function puedeReintentar(): bool
    {
        return $this->id_estado === self::ESTADO_FALLIDO 
            && $this->intentos < $this->max_intentos;
    }

    public function marcarComoEnviada()
    {
        $this->update([
            'id_estado' => self::ESTADO_ENVIADO,
            'fecha_envio' => now(),
        ]);

        $this->registrarLog('enviada', 'Notificación enviada exitosamente');
    }

    public function marcarComoFallida(string $error)
    {
        $this->increment('intentos');
        $this->update([
            'id_estado' => self::ESTADO_FALLIDO,
            'error_mensaje' => $error,
        ]);

        $this->registrarLog('fallida', $error);
    }

    public function cancelar(string $motivo = null)
    {
        $this->update([
            'id_estado' => self::ESTADO_CANCELADO,
            'error_mensaje' => $motivo,
        ]);

        $this->registrarLog('cancelada', $motivo ?? 'Cancelada manualmente');
    }

    public function registrarLog(string $accion, string $detalle = null)
    {
        LogNotificacion::create([
            'id_notificacion' => $this->id,
            'accion' => $accion,
            'detalle' => $detalle,
            'ip_servidor' => request()->server('SERVER_ADDR'),
        ]);
    }
}
