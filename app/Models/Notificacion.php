<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_cliente
 * @property int|null $id_inscripcion Si es notificación de vencimiento
 * @property string $tipo
 * @property string $canal
 * @property string $destinatario Email o teléfono
 * @property string $asunto
 * @property string $mensaje
 * @property string $estado
 * @property \Illuminate\Support\Carbon|null $fecha_envio
 * @property string|null $error_mensaje Detalle del error si falló
 * @property string $created_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Inscripcion|null $inscripcion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereAsunto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereCanal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereDestinatario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereErrorMensaje($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereFechaEnvio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereIdCliente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereIdInscripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereMensaje($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereTipo($value)
 * @mixin \Eloquent
 */
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
