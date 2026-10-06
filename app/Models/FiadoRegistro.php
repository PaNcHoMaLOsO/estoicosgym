<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lo que cambió una cuenta del mesón sin cobrarla: una línea quitada, un cobro
 * deshecho o una cuenta pasada a un socio. Es lo que se mira cuando al final
 * del mes algo no cuadra.
 */
class FiadoRegistro extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'fiado_registros';

    protected $fillable = ['accion', 'id_cliente', 'nombre', 'detalle', 'monto', 'id_usuario'];

    protected $casts = ['monto' => 'integer'];

    public const ACCIONES = [
        'quitado' => 'Quitó',
        'reabierto' => 'Deshizo un cobro',
        'asignado' => 'Pasó a un socio',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente')->withTrashed();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /** Lo deja anotado, de quien sea la línea. */
    public static function anotar(string $accion, Fiado $linea, string $detalle, int $monto, ?int $idUsuario): void
    {
        self::create([
            'accion' => $accion,
            'id_cliente' => $linea->id_cliente,
            // El nombre escrito solo si no era socio: el del socio sale de su
            // ficha, y si un día se borran sus datos aquí no queda copiado.
            'nombre' => $linea->id_cliente ? null : $linea->nombre,
            'detalle' => mb_substr($detalle, 0, 255),
            'monto' => $monto,
            'id_usuario' => $idUsuario,
        ]);
    }

    public function aNombreDe(): string
    {
        return $this->cliente
            ? trim("{$this->cliente->nombres} {$this->cliente->apellido_paterno}")
            : ($this->nombre ?: 'Sin nombre');
    }
}
