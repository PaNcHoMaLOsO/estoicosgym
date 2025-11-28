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
        'precio_base',
        'descuento_aplicado',
        'precio_final',
        'id_motivo_descuento',
        'id_estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_vencimiento' => 'datetime',
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
     * Obtener el estado actual de pago de la inscripción
     */
    public function obtenerEstadoPago()
    {
        $montoTotal = ($this->precio_base ?? 0) - ($this->descuento_aplicado ?? 0);
        $allPagos = $this->pagos()->get();
        $totalAbonado = $allPagos->sum('monto_abonado');
        
        $pendiente = $montoTotal - $totalAbonado;
        $porcentajePagado = $montoTotal > 0 ? ($totalAbonado / $montoTotal) * 100 : 0;

        return [
            'monto_total' => $montoTotal,
            'total_abonado' => $totalAbonado,
            'pendiente' => $pendiente,
            'porcentaje_pagado' => $porcentajePagado,
            'estado' => $pendiente <= 0 ? 'pagado' : ($totalAbonado > 0 ? 'parcial' : 'pendiente'),
        ];
    }
}
