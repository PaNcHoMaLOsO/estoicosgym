<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tabla_afectada
 * @property int $id_registro_afectado
 * @property string $accion
 * @property array<array-key, mixed>|null $datos_anteriores Estado previo (solo UPDATE/DELETE)
 * @property array<array-key, mixed>|null $datos_nuevos Estado nuevo (solo INSERT/UPDATE)
 * @property int|null $usuario_id Futuro: ID del usuario que hizo la acción
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $fecha_hora
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereAccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereDatosAnteriores($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereDatosNuevos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereFechaHora($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereIdRegistroAfectado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereTablaAfectada($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auditoria whereUsuarioId($value)
 * @mixin \Eloquent
 */
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
