<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid UUID único para identificación externa
 * @property string|null $grupo_pago UUID para agrupar cuotas del mismo plan
 * @property int $id_inscripcion
 * @property string $monto_abonado Lo que se pagó en este registro
 * @property string $monto_pendiente Saldo restante
 * @property int|null $id_motivo_descuento Motivo del descuento (si aplica)
 * @property \Illuminate\Support\Carbon $fecha_pago
 * @property int $id_metodo_pago
 * @property string|null $referencia_pago N° de transferencia, comprobante, referencia
 * @property int $id_estado Pendiente, Pagado, Parcial, Vencido (calculado dinámicamente)
 * @property int $cantidad_cuotas Total de cuotas en el plan (default: 1)
 * @property int $numero_cuota Número de cuota actual (ej: 1 de 3)
 * @property string|null $monto_cuota Monto de cada cuota individual
 * @property \Illuminate\Support\Carbon|null $fecha_vencimiento_cuota Fecha de vencimiento de esta cuota
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Estado $estado
 * @property-read \App\Models\Inscripcion $inscripcion
 * @property-read \App\Models\MetodoPago $metodoPago
 * @property-read \App\Models\MotivoDescuento|null $motivoDescuento
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pago> $cuotasRelacionadas
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereDescuentoAplicado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereFechaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdCliente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdInscripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdMetodoPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereIdMotivoDescuento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereMontoAbonado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereMontoPendiente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereMontoTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago wherePeriodoFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago wherePeriodoInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereReferenciaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pago whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'grupo_pago',
        'id_inscripcion',
        'monto_abonado',
        'monto_pendiente',
        'id_motivo_descuento',
        'fecha_pago',
        'id_metodo_pago',
        'referencia_pago',
        'id_estado',
        'cantidad_cuotas',
        'numero_cuota',
        'monto_cuota',
        'fecha_vencimiento_cuota',
        'observaciones',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
        'fecha_vencimiento_cuota' => 'date',
        'monto_cuota' => 'decimal:2',
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

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado');
    }

    public function motivoDescuento()
    {
        return $this->belongsTo(MotivoDescuento::class, 'id_motivo_descuento');
    }

    /**
     * Obtener todas las cuotas relacionadas al mismo plan (mismo grupo_pago)
     */
    public function cuotasRelacionadas()
    {
        if (!$this->grupo_pago) {
            return [];
        }
        return self::where('grupo_pago', $this->grupo_pago)
            ->orderBy('numero_cuota')
            ->get();
    }

    /**
     * Obtener el monto total que debe pagar por la inscripción
     * (precio_final = precio_base - descuento)
     */
    public function getMontoTotalAttribute()
    {
        return $this->inscripcion->precio_final ?? $this->inscripcion->precio_base;
    }

    /**
     * Obtener el descuento aplicado en la inscripción
     */
    public function getDescuentoAplicadoAttribute()
    {
        return $this->inscripcion->descuento_aplicado;
    }

    /**
     * Obtener la fecha de inicio del período (fecha_inicio de inscripción)
     */
    public function getPeriodoInicioAttribute()
    {
        return $this->inscripcion->fecha_inicio;
    }

    /**
     * Obtener la fecha de fin del período (fecha_vencimiento de inscripción)
     */
    public function getPeriodoFinAttribute()
    {
        return $this->inscripcion->fecha_vencimiento;
    }

    /**
     * Obtener el cliente de la inscripción
     */
    public function getClienteAttribute()
    {
        return $this->inscripcion->cliente;
    }

    /**
     * Calcular el estado dinámico basado en montos y fechas
     */
    public function calculateEstadoDinamico()
    {
        $montoTotal = $this->getMontoTotalAttribute();
        $montoPendiente = $this->monto_pendiente;
        $hoy = now();

        // Si todo está pagado
        if ($montoPendiente <= 0) {
            return 102; // Pagado
        }

        // Si hay monto pendiente pero aún está dentro de plazo
        if ($this->fecha_vencimiento_cuota && $this->fecha_vencimiento_cuota->isBefore($hoy)) {
            return 104; // Vencido
        }

        // Si hay monto abonado pero no todo
        if ($this->monto_abonado > 0) {
            return 103; // Parcial
        }

        // Si no hay nada pagado
        return 101; // Pendiente
    }

    /**
     * Determinar si esta cuota es la última del plan
     */
    public function esUltimaCuota()
    {
        return $this->numero_cuota >= $this->cantidad_cuotas;
    }

    /**
     * Validar que el número de cuota sea válido
     */
    public function esNumeroCuotaValido()
    {
        return $this->numero_cuota > 0 && $this->numero_cuota <= $this->cantidad_cuotas;
    }

    /**
     * Obtener el saldo pendiente total por la inscripción (sumando todos los pagos)
     */
    public function getSaldoPendienteTotal()
    {
        $montoTotal = $this->getMontoTotalAttribute();
        $totalAbonado = $this->inscripcion->pagos()
            ->whereIn('id_estado', [102, 103]) // Pagado o Parcial
            ->sum('monto_abonado');
        
        return max(0, $montoTotal - $totalAbonado);
    }
}
