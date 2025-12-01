<?php

namespace App\Models;

use App\Enums\EstadosCodigo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Historial de Cambios General
 * 
 * Registra todos los cambios de estado importantes del sistema
 * 
 * @property int $id
 * @property string $uuid
 * @property string $tipo_cambio (pausa, reanudacion, cambio_plan, etc.)
 * @property string $entidad (inscripcion, cliente, pago)
 * @property int $entidad_id
 * @property int $cliente_id
 * @property int|null $inscripcion_id
 * @property int|null $estado_anterior
 * @property int $estado_nuevo
 * @property array|null $detalles
 * @property string|null $motivo
 * @property int|null $usuario_id
 * @property \Carbon\Carbon $fecha_cambio
 */
class HistorialCambio extends Model
{
    protected $table = 'historial_cambios';

    protected $fillable = [
        'uuid',
        'tipo_cambio',
        'entidad',
        'entidad_id',
        'cliente_id',
        'inscripcion_id',
        'estado_anterior',
        'estado_nuevo',
        'detalles',
        'motivo',
        'usuario_id',
        'fecha_cambio',
    ];

    protected $casts = [
        'detalles' => 'array',
        'fecha_cambio' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
            if (empty($model->fecha_cambio)) {
                $model->fecha_cambio = now();
            }
        });
    }

    // ========================================
    // RELACIONES
    // ========================================

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function estadoAnterior()
    {
        return $this->belongsTo(Estado::class, 'estado_anterior', 'codigo');
    }

    public function estadoNuevo()
    {
        return $this->belongsTo(Estado::class, 'estado_nuevo', 'codigo');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopePorInscripcion($query, $inscripcionId)
    {
        return $query->where('inscripcion_id', $inscripcionId);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_cambio', $tipo);
    }

    public function scopeUltimos($query, $dias = 30)
    {
        return $query->where('fecha_cambio', '>=', now()->subDays($dias));
    }

    public function scopePausas($query)
    {
        return $query->whereIn('tipo_cambio', ['pausa', 'reanudacion']);
    }

    public function scopeCambiosPlan($query)
    {
        return $query->where('tipo_cambio', 'cambio_plan');
    }

    // ========================================
    // MÉTODOS ESTÁTICOS DE REGISTRO
    // ========================================

    /**
     * Registrar una pausa de inscripción
     */
    public static function registrarPausa(Inscripcion $inscripcion, array $datosPausa, $usuarioId = null)
    {
        return self::create([
            'tipo_cambio' => 'pausa',
            'entidad' => 'inscripcion',
            'entidad_id' => $inscripcion->id,
            'cliente_id' => $inscripcion->id_cliente,
            'inscripcion_id' => $inscripcion->id,
            'estado_anterior' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'estado_nuevo' => EstadosCodigo::INSCRIPCION_PAUSADA,
            'detalles' => [
                'dias_pausa' => $datosPausa['dias'] ?? null,
                'indefinida' => $datosPausa['indefinida'] ?? false,
                'fecha_fin_prevista' => $datosPausa['fecha_fin'] ?? null,
            ],
            'motivo' => $datosPausa['razon'] ?? null,
            'usuario_id' => $usuarioId ?? auth()->id(),
        ]);
    }

    /**
     * Registrar una reanudación de inscripción
     */
    public static function registrarReanudacion(Inscripcion $inscripcion, int $diasEnPausa, int $diasCompensados, $usuarioId = null)
    {
        return self::create([
            'tipo_cambio' => 'reanudacion',
            'entidad' => 'inscripcion',
            'entidad_id' => $inscripcion->id,
            'cliente_id' => $inscripcion->id_cliente,
            'inscripcion_id' => $inscripcion->id,
            'estado_anterior' => EstadosCodigo::INSCRIPCION_PAUSADA,
            'estado_nuevo' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'detalles' => [
                'dias_en_pausa' => $diasEnPausa,
                'dias_compensados' => $diasCompensados,
                'nueva_fecha_vencimiento' => $inscripcion->fecha_vencimiento?->format('Y-m-d'),
            ],
            'usuario_id' => $usuarioId ?? auth()->id(),
        ]);
    }

    /**
     * Registrar un cambio de plan
     */
    public static function registrarCambioPlan(
        Inscripcion $inscripcionAnterior, 
        Inscripcion $inscripcionNueva, 
        string $tipoCambio,
        float $diferencia,
        ?string $motivo = null,
        $usuarioId = null
    ) {
        return self::create([
            'tipo_cambio' => 'cambio_plan',
            'entidad' => 'inscripcion',
            'entidad_id' => $inscripcionAnterior->id,
            'cliente_id' => $inscripcionAnterior->id_cliente,
            'inscripcion_id' => $inscripcionNueva->id,
            'estado_anterior' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'estado_nuevo' => EstadosCodigo::INSCRIPCION_CAMBIADA,
            'detalles' => [
                'tipo' => $tipoCambio, // 'upgrade' o 'downgrade'
                'inscripcion_anterior_id' => $inscripcionAnterior->id,
                'inscripcion_nueva_id' => $inscripcionNueva->id,
                'membresia_anterior' => $inscripcionAnterior->membresia?->nombre,
                'membresia_nueva' => $inscripcionNueva->membresia?->nombre,
                'precio_anterior' => $inscripcionAnterior->precio_final,
                'precio_nuevo' => $inscripcionNueva->precio_final,
                'diferencia' => $diferencia,
            ],
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id(),
        ]);
    }

    /**
     * Registrar cambio de estado de inscripción
     */
    public static function registrarCambioEstadoInscripcion(
        Inscripcion $inscripcion, 
        int $estadoAnterior, 
        int $estadoNuevo, 
        ?string $motivo = null,
        $usuarioId = null
    ) {
        $tipoCambio = match($estadoNuevo) {
            EstadosCodigo::INSCRIPCION_CANCELADA => 'cancelacion_inscripcion',
            EstadosCodigo::INSCRIPCION_SUSPENDIDA => 'suspension',
            EstadosCodigo::INSCRIPCION_VENCIDA => 'vencimiento',
            default => 'cambio_estado_inscripcion',
        };

        return self::create([
            'tipo_cambio' => $tipoCambio,
            'entidad' => 'inscripcion',
            'entidad_id' => $inscripcion->id,
            'cliente_id' => $inscripcion->id_cliente,
            'inscripcion_id' => $inscripcion->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id(),
        ]);
    }

    /**
     * Registrar cambio de estado de cliente
     */
    public static function registrarCambioEstadoCliente(
        Cliente $cliente, 
        int $estadoAnterior, 
        int $estadoNuevo, 
        ?string $motivo = null,
        $usuarioId = null
    ) {
        return self::create([
            'tipo_cambio' => 'cambio_estado_cliente',
            'entidad' => 'cliente',
            'entidad_id' => $cliente->id,
            'cliente_id' => $cliente->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id(),
        ]);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Obtener descripción legible del tipo de cambio
     */
    public function getTipoCambioDescripcionAttribute()
    {
        return match($this->tipo_cambio) {
            'pausa' => 'Pausa de membresía',
            'reanudacion' => 'Reanudación de membresía',
            'cambio_plan' => 'Cambio de plan',
            'cambio_estado_inscripcion' => 'Cambio de estado',
            'cambio_estado_cliente' => 'Cambio estado cliente',
            'cancelacion_inscripcion' => 'Cancelación',
            'suspension' => 'Suspensión',
            'vencimiento' => 'Vencimiento',
            default => ucfirst(str_replace('_', ' ', $this->tipo_cambio)),
        };
    }

    /**
     * Obtener icono para el tipo de cambio
     */
    public function getIconoAttribute()
    {
        return match($this->tipo_cambio) {
            'pausa' => 'fas fa-pause-circle',
            'reanudacion' => 'fas fa-play-circle',
            'cambio_plan' => 'fas fa-exchange-alt',
            'cambio_estado_inscripcion' => 'fas fa-edit',
            'cambio_estado_cliente' => 'fas fa-user-edit',
            'cancelacion_inscripcion' => 'fas fa-times-circle',
            'suspension' => 'fas fa-ban',
            'vencimiento' => 'fas fa-calendar-times',
            default => 'fas fa-history',
        };
    }

    /**
     * Obtener color para el tipo de cambio
     */
    public function getColorAttribute()
    {
        return match($this->tipo_cambio) {
            'pausa' => 'warning',
            'reanudacion' => 'success',
            'cambio_plan' => 'info',
            'cambio_estado_inscripcion' => 'primary',
            'cambio_estado_cliente' => 'secondary',
            'cancelacion_inscripcion' => 'danger',
            'suspension' => 'danger',
            'vencimiento' => 'secondary',
            default => 'secondary',
        };
    }
}
