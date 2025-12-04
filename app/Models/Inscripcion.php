<?php

namespace App\Models;

use App\Enums\EstadosCodigo;
use App\Models\HistorialCambio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $id_cliente
 * @property int $id_membresia
 * @property int|null $id_convenio Convenio aplicado al momento de la inscripción
 * @property int $id_precio_acordado Precio vigente al momento de la inscripción
 * @property \Illuminate\Support\Carbon $fecha_inscripcion Fecha en que se registra
 * @property \Illuminate\Support\Carbon $fecha_inicio Fecha en que inicia la membresía (puede ser futura)
 * @property \Illuminate\Support\Carbon $fecha_vencimiento Fecha de expiración
 * @property string $precio_base Precio oficial de la membresía
 * @property string $descuento_aplicado Descuento en pesos
 * @property string $precio_final precio_base - descuento_aplicado
 * @property int|null $id_motivo_descuento Justificación del descuento
 * @property int $id_estado Activa, Vencida, Pausada, Cancelada, Pendiente (referencia estados.codigo)
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Convenio|null $convenio
 * @property-read \App\Models\Estado $estado
 * @property-read \App\Models\Membresia $membresia
 * @property-read \App\Models\MotivoDescuento|null $motivoDescuento
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $pagos
 * @property-read int|null $pagos_count
 * @property-read \App\Models\PrecioMembresia $precioAcordado
 * @property-read int $dias_restantes
 * @property-read bool $esta_vencida
 * @property-read bool $esta_activa
 * @mixin \Eloquent
 */
class Inscripcion extends Model
{
    use SoftDeletes;

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
        'precio_base',
        'descuento_aplicado',
        'precio_final',
        'id_motivo_descuento',
        'id_estado',
        'observaciones',
        // Campos de sistema de pausas
        'pausada',
        'dias_pausa',
        'dias_restantes_al_pausar',
        'fecha_pausa_inicio',
        'fecha_pausa_fin',
        'razon_pausa',
        'pausa_indefinida',
        'pausas_realizadas',
        'max_pausas_permitidas',
        'dias_compensacion',
        // Campos de cambio de plan (upgrade/downgrade)
        'id_inscripcion_anterior',
        'es_cambio_plan',
        'tipo_cambio',
        'credito_plan_anterior',
        'precio_nuevo_plan',
        'diferencia_a_pagar',
        'fecha_cambio_plan',
        'motivo_cambio_plan',
        // Campos de traspaso de membresía
        'es_traspaso',
        'id_inscripcion_origen',
        'id_cliente_original',
        'fecha_traspaso',
        'motivo_traspaso',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'fecha_pausa_inicio' => 'date',
        'fecha_pausa_fin' => 'date',
        'fecha_cambio_plan' => 'datetime',
        'fecha_traspaso' => 'datetime',
        'precio_base' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2',
        'precio_final' => 'decimal:2',
        'credito_plan_anterior' => 'decimal:2',
        'precio_nuevo_plan' => 'decimal:2',
        'diferencia_a_pagar' => 'decimal:2',
        'pausada' => 'boolean',
        'pausa_indefinida' => 'boolean',
        'es_cambio_plan' => 'boolean',
        'es_traspaso' => 'boolean',
        'dias_pausa' => 'integer',
        'dias_restantes_al_pausar' => 'integer',
        'pausas_realizadas' => 'integer',
        'max_pausas_permitidas' => 'integer',
        'dias_compensacion' => 'integer',
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
        return $this->belongsTo(Estado::class, 'id_estado', 'codigo');
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
     * Obtener el estado actual de pago de la inscripción
     */
    public function obtenerEstadoPago()
    {
        $montoTotal = $this->precio_final ?? (($this->precio_base ?? 0) - ($this->descuento_aplicado ?? 0));
        $allPagos = $this->pagos()->get();
        $totalAbonado = $allPagos->sum('monto_abonado');
        
        $pendiente = max(0, $montoTotal - $totalAbonado);
        $porcentajePagado = $montoTotal > 0 ? ($totalAbonado / $montoTotal) * 100 : 0;

        return [
            'monto_total' => $montoTotal,
            'total_abonado' => $totalAbonado,
            'pendiente' => $pendiente,
            'porcentaje_pagado' => min(100, $porcentajePagado),
            'estado' => $pendiente <= 0 ? 'pagado' : ($totalAbonado > 0 ? 'parcial' : 'pendiente'),
        ];
    }

    /**
     * Obtener días restantes de la membresía
     */
    public function getDiasRestantesAttribute()
    {
        if (!$this->fecha_vencimiento) {
            return 0;
        }
        return (int) now()->diffInDays($this->fecha_vencimiento, false);
    }

    /**
     * Verificar si la inscripción está vencida
     */
    public function getEstaVencidaAttribute()
    {
        return $this->dias_restantes < 0;
    }

    /**
     * Verificar si la inscripción está activa
     */
    public function getEstaActivaAttribute()
    {
        return $this->id_estado == 100 && !$this->esta_vencida;
    }

    /**
     * Verificar si la inscripción está pausada
     */
    public function estaPausada()
    {
        // Estado 101 = Pausada (NO confundir con 102 = Vencida)
        return $this->pausada === true || $this->id_estado == 101;
    }

    /**
     * Verificar si puede realizar más pausas
     */
    public function puedeRealizarPausa()
    {
        return $this->pausas_realizadas < $this->max_pausas_permitidas 
            && !$this->pausada 
            && $this->id_estado == 100;
    }

    /**
     * Obtener pausas disponibles
     */
    public function getPausasDisponiblesAttribute()
    {
        return max(0, $this->max_pausas_permitidas - $this->pausas_realizadas);
    }

    /**
     * Pausar la membresía
     * 
     * @param int|null $dias Días de pausa (null para indefinida)
     * @param string $razon Razón de la pausa
     * @param bool $indefinida Si es pausa indefinida
     * @return bool
     */
    public function pausar($dias = null, $razon = '', $indefinida = false)
    {
        if (!$this->puedeRealizarPausa()) {
            return false;
        }

        // Calcular los días restantes REALES de la membresía al momento de pausar
        // Esto es lo único que importa: cuántos días le quedaban
        $diasRestantes = 0;
        if ($this->fecha_vencimiento) {
            $diasRestantes = max(0, (int) now()->startOfDay()->diffInDays($this->fecha_vencimiento->startOfDay(), false));
        }
        
        $this->pausada = true;
        $this->dias_pausa = $indefinida ? null : $dias; // Solo informativo: cuántos días pidió pausar
        $this->dias_restantes_al_pausar = $diasRestantes; // CRÍTICO: los días que le quedaban
        $this->fecha_pausa_inicio = now()->startOfDay();
        $this->fecha_pausa_fin = $indefinida ? null : now()->addDays($dias)->startOfDay();
        $this->razon_pausa = $razon;
        $this->pausa_indefinida = $indefinida;
        $this->pausas_realizadas = $this->pausas_realizadas + 1;
        // Estado 101 = Pausada
        $this->id_estado = 101;

        $resultado = $this->save();

        // Registrar en historial
        if ($resultado) {
            HistorialCambio::registrarPausa($this, [
                'dias_solicitados' => $dias,
                'dias_restantes_guardados' => $diasRestantes,
                'razon' => $razon,
                'indefinida' => $indefinida,
                'fecha_fin_pausa' => $this->fecha_pausa_fin?->format('Y-m-d'),
            ]);
        }

        return $resultado;
    }

    /**
     * Reanudar la membresía pausada
     * 
     * LÓGICA CORRECTA:
     * - Al pausar se guardaron los días que le quedaban (dias_restantes_al_pausar)
     * - Al reanudar: nueva_fecha_vencimiento = HOY + dias_restantes_al_pausar
     * - NO importa cuántos días estuvo pausado, solo importa cuántos días le quedaban
     * 
     * @return bool
     */
    public function reanudar()
    {
        if (!$this->pausada) {
            return false;
        }

        // Calcular días que estuvo pausado (solo para información/historial)
        $diasEnPausa = 0;
        if ($this->fecha_pausa_inicio) {
            $diasEnPausa = (int) $this->fecha_pausa_inicio->startOfDay()->diffInDays(now()->startOfDay());
        }

        // Los días que tenía guardados al momento de pausar
        $diasRestantesGuardados = $this->dias_restantes_al_pausar ?? 0;
        
        // NUEVA FECHA DE VENCIMIENTO = HOY + días que le quedaban
        // Esto es todo lo que importa. No sumamos días de pausa ni nada extra.
        if ($diasRestantesGuardados > 0) {
            $this->fecha_vencimiento = now()->startOfDay()->addDays($diasRestantesGuardados);
        }

        // Limpiar todos los campos de pausa
        $this->pausada = false;
        $this->dias_pausa = null;
        $this->dias_restantes_al_pausar = null;
        $this->fecha_pausa_inicio = null;
        $this->fecha_pausa_fin = null;
        $this->razon_pausa = null;
        $this->pausa_indefinida = false;
        $this->id_estado = 100; // Estado Activa

        $resultado = $this->save();

        // Registrar en historial
        if ($resultado) {
            HistorialCambio::registrarReanudacion($this, $diasEnPausa, $diasRestantesGuardados);
        }

        return $resultado;
    }

    /**
     * Obtener descripción del estado de pausa
     */
    public function getEstadoPausaDescripcionAttribute()
    {
        if (!$this->pausada) {
            return null;
        }

        if ($this->pausa_indefinida) {
            return 'Pausada hasta nuevo aviso';
        }

        return $this->fecha_pausa_fin 
            ? 'Pausada hasta ' . $this->fecha_pausa_fin->format('d/m/Y')
            : 'Pausada';
    }

    // ============================================
    // MÉTODOS DE CAMBIO DE PLAN (UPGRADE/DOWNGRADE)
    // ============================================

    /**
     * Relación con la inscripción anterior (si es upgrade/downgrade)
     */
    public function inscripcionAnterior()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion_anterior');
    }

    /**
     * Relación con inscripciones que la reemplazaron
     */
    public function inscripcionesPosteriores()
    {
        return $this->hasMany(Inscripcion::class, 'id_inscripcion_anterior');
    }

    /**
     * Verificar si esta inscripción puede cambiar de plan
     * Solo inscripciones activas pueden cambiar
     */
    public function puedeCambiarPlan()
    {
        return $this->id_estado == 100 && !$this->pausada;
    }

    /**
     * Obtener el monto total pagado de esta inscripción
     */
    public function getMontoPagadoAttribute()
    {
        return $this->pagos()->sum('monto_abonado');
    }

    /**
     * Obtener el monto pendiente de esta inscripción
     */
    public function getMontoPendienteAttribute()
    {
        return max(0, $this->precio_final - $this->monto_pagado);
    }

    /**
     * Verificar si la inscripción está completamente pagada
     */
    public function getEstaPagadaAttribute()
    {
        return $this->monto_pagado >= $this->precio_final;
    }

    /**
     * Calcular el crédito disponible para cambio de plan
     * Es el monto que ya pagó el cliente
     */
    public function getCreditoDisponibleAttribute()
    {
        return $this->monto_pagado;
    }

    /**
     * Obtener días restantes de la membresía actual
     */
    public function getDiasConsumidosAttribute()
    {
        if (!$this->fecha_inicio) return 0;
        return max(0, $this->fecha_inicio->diffInDays(now()));
    }

    /**
     * Verificar si es un upgrade o downgrade
     */
    public function getTipoCambioDescripcionAttribute()
    {
        if (!$this->es_cambio_plan) {
            return null;
        }

        return $this->tipo_cambio === 'upgrade' ? 'Mejora de Plan' : 'Cambio a Plan Menor';
    }

    // ============================================
    // MÉTODOS DE TRASPASO DE MEMBRESÍA
    // ============================================

    /**
     * Relación con la inscripción origen (de donde viene el traspaso)
     */
    public function inscripcionOrigen()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion_origen');
    }

    /**
     * Relación con el cliente original que cedió la membresía
     */
    public function clienteOriginal()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente_original');
    }

    /**
     * Inscripciones que fueron traspasadas desde esta
     */
    public function inscripcionesTraspasadas()
    {
        return $this->hasMany(Inscripcion::class, 'id_inscripcion_origen');
    }

    /**
     * Verificar si esta inscripción puede ser traspasada
     * Solo inscripciones activas o pausadas pueden traspasarse
     * @param bool $ignorarDeuda Si es true, ignora la validación de deuda pendiente
     */
    public function puedeTraspasarse($ignorarDeuda = false)
    {
        // Debe estar activa o pausada
        if (!in_array($this->id_estado, [100, 101])) {
            return false;
        }
        
        // Debe tener días restantes
        if ($this->dias_restantes <= 0) {
            return false;
        }
        
        // Si no se ignora la deuda, verificar que esté completamente pagada
        if (!$ignorarDeuda && $this->monto_pendiente > 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Obtener información detallada de traspaso
     * Incluye validaciones y montos de deuda si existen
     */
    public function getInfoTraspaso()
    {
        $estadoPago = $this->obtenerEstadoPago();
        
        return [
            'puede_traspasar' => $this->puedeTraspasarse(false), // Sin ignorar deuda
            'puede_traspasar_con_deuda' => $this->puedeTraspasarse(true), // Ignorando deuda
            'tiene_deuda' => $estadoPago['pendiente'] > 0,
            'monto_total' => $estadoPago['monto_total'],
            'monto_pagado' => $estadoPago['total_abonado'],
            'monto_pendiente' => $estadoPago['pendiente'],
            'porcentaje_pagado' => $estadoPago['porcentaje_pagado'],
            'estado_pago' => $estadoPago['estado'],
            'dias_restantes' => $this->dias_restantes,
            'membresia' => $this->membresia->nombre ?? 'N/A',
            'fecha_vencimiento' => $this->fecha_vencimiento->format('d/m/Y'),
        ];
    }

    /**
     * Verificar si un cliente puede recibir un traspaso
     * No debe tener membresía activa
     */
    public static function clientePuedeRecibirTraspaso($clienteId)
    {
        return !self::where('id_cliente', $clienteId)
            ->whereIn('id_estado', [100, 101]) // Activa o Pausada
            ->where('fecha_vencimiento', '>=', now())
            ->exists();
    }
}
