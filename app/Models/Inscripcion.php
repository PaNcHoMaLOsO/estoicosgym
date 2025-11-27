<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $id_cliente
 * @property int $id_membresia
 * @property int|null $id_convenio Convenio aplicado al momento de la inscripción
 * @property int $id_precio_acordado Precio vigente al momento de la inscripción
 * @property \Illuminate\Support\Carbon $fecha_inscripcion Fecha en que se registra
 * @property \Illuminate\Support\Carbon $fecha_inicio Fecha en que inicia la membresía (puede ser futura)
 * @property \Illuminate\Support\Carbon $fecha_vencimiento Fecha de expiración
 * @property int|null $dia_pago 1-31: Día del mes elegido para pagar
 * @property string $precio_base Precio oficial de la membresía
 * @property string $descuento_aplicado Descuento en pesos
 * @property string $precio_final precio_base - descuento_aplicado
 * @property int|null $id_motivo_descuento Justificación del descuento
 * @property int $id_estado Activa, Vencida, Pausada, Cancelada, Pendiente
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $pausada Si está en pausa
 * @property int $dias_pausa Días que durará la pausa
 * @property \Illuminate\Support\Carbon|null $fecha_pausa_inicio Cuándo inicia la pausa
 * @property \Illuminate\Support\Carbon|null $fecha_pausa_fin Cuándo termina la pausa
 * @property string|null $razon_pausa Motivo de la pausa
 * @property int $pausas_realizadas Cantidad de pausas hechas
 * @property int $max_pausas_permitidas Máximo de pausas permitidas por año
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Convenio|null $convenio
 * @property-read \App\Models\Estado $estado
 * @property-read \App\Models\Membresia $membresia
 * @property-read \App\Models\MotivoDescuento|null $motivoDescuento
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @property-read \App\Models\PrecioMembresia $precioAcordado
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereDescuentoAplicado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereDiaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereDiasPausa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaInscripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaPausaFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaPausaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaVencimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdCliente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdConvenio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdMembresia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdMotivoDescuento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdPrecioAcordado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereMaxPausasPermitidas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion wherePausada($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion wherePausasRealizadas($value)
    @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion wherePrecioBase($value)
    @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereRazonPausa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Inscripcion extends Model
{
    protected $table = 'inscripciones';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'id_cliente',
        'id_membresia',
        'id_convenio',
        'id_precio_acordado',
        'fecha_inscripcion',
        'fecha_inicio',
        'fecha_vencimiento',
        'dia_pago',
        'precio_base',
        'descuento_aplicado',
        'precio_final',
        'id_motivo_descuento',
        'id_estado',
        'observaciones',
        'pausada',
        'dias_pausa',
        'fecha_pausa_inicio',
        'fecha_pausa_fin',
        'razon_pausa',
        'pausas_realizadas',
        'max_pausas_permitidas',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'fecha_pausa_inicio' => 'datetime',
        'fecha_pausa_fin' => 'datetime',
        'pausada' => 'boolean',
        'precio_base' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2',
        'precio_final' => 'decimal:2',
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

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'id_membresia');
    }

    public function precioAcordado()
    {
        return $this->belongsTo(PrecioMembresia::class, 'id_precio_acordado');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado');
    }

    public function motivoDescuento()
    {
        return $this->belongsTo(MotivoDescuento::class, 'id_motivo_descuento');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'id_convenio');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_inscripcion');
    }

    /**
     * Pausar la membresía por un período especificado
     * 
     * @param int $dias Período de pausa: 7, 14 o 30 días
     * @param string $razon Motivo de la pausa
     * @return bool True si se pausó exitosamente
     * @throws \Exception Si se alcanza máximo de pausas o días inválidos
     */
    public function pausar($dias = 7, $razon = '')
    {
        // Validar que no exceda el máximo de pausas permitidas
        if ($this->pausas_realizadas >= $this->max_pausas_permitidas) {
            throw new \Exception('Se ha alcanzado el máximo de pausas permitidas para esta membresía');
        }

        // Validar días válidos
        if (!in_array($dias, [7, 14, 30])) {
            throw new \Exception('Días de pausa válidos: 7, 14 o 30 días');
        }

        $fechaInicio = now();
        $fechaFin = now()->addDays($dias);

        // Determinar el estado según días
        $idEstado = match($dias) {
            7 => 2,  // Pausada - 7 días
            14 => 3, // Pausada - 14 días
            30 => 4, // Pausada - 30 días
        };

        $this->update([
            'pausada' => true,
            'dias_pausa' => $dias,
            'fecha_pausa_inicio' => $fechaInicio,
            'fecha_pausa_fin' => $fechaFin,
            'razon_pausa' => $razon,
            'pausas_realizadas' => $this->pausas_realizadas + 1,
            'id_estado' => $idEstado,
        ]);

        return true;
    }

    /**
     * Reanudar la membresía si la pausa ha finalizado o manualmente
     * Extiende automáticamente la fecha de vencimiento por los días pausados
     * 
     * @return bool True si se reanudó exitosamente
     * @throws \Exception Si la membresía no está pausada
     */
    public function reanudar()
    {
        // Validar que la membresía esté pausada
        if (!$this->pausada) {
            throw new \Exception('Esta membresía no está pausada');
        }

        // Extender la fecha de vencimiento por los días que estuvo pausada
        if ($this->fecha_pausa_fin) {
            $diasPausa = now()->diffInDays($this->fecha_pausa_fin);
            $nuevaFechaVencimiento = Carbon::parse($this->fecha_vencimiento)->addDays($diasPausa);
            
            $estadoActiva = Estado::where('codigo', 100)->first(); // Activa
            $this->update([
                'pausada' => false,
                'fecha_vencimiento' => $nuevaFechaVencimiento,
                'id_estado' => $estadoActiva->id, // Activa
            ]);
        }

        return true;
    }

    /**
     * Verificar si la pausa ha expirado automáticamente
     * Si es así, intenta reanudar automáticamente
     * 
     * @return bool True si se reanudó automáticamente, False si no estaba pausada o no expiró
     */
    public function verificarPausaExpirada()
    {
        if ($this->pausada && $this->fecha_pausa_fin && now()->isAfter($this->fecha_pausa_fin)) {
            return $this->reanudar();
        }

        return false;
    }

    /**
     * Obtener información detallada de la pausa actual
     * 
     * @return array|null Array con información de pausa o null si no está pausada
     */
    public function obtenerInfoPausa()
    {
        if (!$this->pausada) {
            return null;
        }

        return [
            'activa' => $this->pausada,
            'dias' => $this->dias_pausa,
            'inicio' => $this->fecha_pausa_inicio?->format('d/m/Y'),
            'fin' => $this->fecha_pausa_fin?->format('d/m/Y'),
            'razon' => $this->razon_pausa,
            'dias_restantes' => now()->diffInDays($this->fecha_pausa_fin, false),
            'pausas_usadas' => $this->pausas_realizadas,
            'pausas_disponibles' => $this->max_pausas_permitidas - $this->pausas_realizadas,
        ];
    }

    /**
     * Verificar si puede pausarse esta membresía
     * Condiciones: No estar pausada, no exceder pausas máximas
     * 
     * @return bool True si puede pausarse
     */
    public function puedePausarse()
    {
        return !$this->pausada 
            && $this->pausas_realizadas < $this->max_pausas_permitidas
            && !$this->estaPausada();
    }

    /**
     * Obtener el estado actual de pago de la inscripción
     * Calcula montos totales, abonados, pendientes y porcentaje pagado
     * 
     * @return array Estado de pago con claves: monto_total, total_abonado, pendiente, 
     *              porcentaje_pagado, pagos_completados, pagos_otros_estados, total_pagos, estado
     */
    public function obtenerEstadoPago()
    {
        // Calcular precio final: precio_base - descuento_aplicado
        $montoTotal = ($this->precio_base ?? 0) - ($this->descuento_aplicado ?? 0);
        
        // Obtener todos los pagos
        $allPagos = $this->pagos()->get();
        
        // Sumar TODOS los montos abonados (sin filtrar por estado)
        $totalAbonado = $allPagos->sum('monto_abonado');
        
        // También contar pagos por estado para debugging
        $estadoPagado = Estado::where('codigo', 201)->first();
        $pagosCompletados = $allPagos->where('id_estado', $estadoPagado->id)->sum('monto_abonado');
        $pagosOtrosEstados = $allPagos->where('id_estado', '!=', $estadoPagado->id)->sum('monto_abonado');
        
        // Calcular pendiente
        $pendiente = $montoTotal - $totalAbonado;
        $porcentajePagado = $montoTotal > 0 ? ($totalAbonado / $montoTotal) * 100 : 0;

        return [
            'monto_total' => $montoTotal,
            'total_abonado' => $totalAbonado,
            'pendiente' => $pendiente,
            'porcentaje_pagado' => $porcentajePagado,
            'pagos_completados' => $pagosCompletados,
            'pagos_otros_estados' => $pagosOtrosEstados,
            'total_pagos' => $allPagos->count(),
            'estado' => $pendiente <= 0 ? 'pagado' : ($totalAbonado > 0 ? 'parcial' : 'pendiente'),
        ];
    }

    /**
     * Verificar si está efectivamente pausada
     * Retorna true si:
     * 1. El campo pausada es true O el estado es uno de pausa (2, 3, 4)
     * 2. Y la pausa NO ha expirado
     * 
     * @return bool True si está pausada actualmente
     */
    public function estaPausada()
    {
        // Estados de pausa (2, 3, 4 corresponden a Pausada - 7d, 14d, 30d)
        $estadosPausa = [2, 3, 4];
        
        // Verificar por estado O por campo pausada
        $tienePausa = in_array($this->id_estado, $estadosPausa) || ($this->pausada === true || $this->pausada === 1);
        
        if (!$tienePausa) {
            return false;
        }

        // Si tiene fecha fin y ya pasó, no está pausada (auto-reanudación)
        if ($this->fecha_pausa_fin && now()->greaterThan($this->fecha_pausa_fin)) {
            return false;
        }

        return true;
    }
}
