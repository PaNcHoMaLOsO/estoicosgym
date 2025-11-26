<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $id_inscripcion
 * @property int $id_cliente Redundante pero útil para queries
 * @property string $monto_total Total a pagar
 * @property string $monto_abonado Lo que se pagó en este registro
 * @property string $monto_pendiente Saldo restante
 * @property string $descuento_aplicado
 * @property int|null $id_motivo_descuento
 * @property \Illuminate\Support\Carbon $fecha_pago
 * @property \Illuminate\Support\Carbon $periodo_inicio Inicio del período cubierto
 * @property \Illuminate\Support\Carbon $periodo_fin Fin del período cubierto
 * @property int $id_metodo_pago
 * @property string|null $referencia_pago Futuro: N° de transferencia, comprobante
 * @property int $id_estado Pendiente, Pagado, Parcial, Vencido
 * @property int $cantidad_cuotas Total de cuotas (default: 1)
 * @property int $numero_cuota Número de cuota actual (ej: 1 de 3)
 * @property string|null $monto_cuota Monto de cada cuota individual
 * @property \Illuminate\Support\Carbon|null $fecha_vencimiento_cuota Fecha de vencimiento de esta cuota
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Estado $estado
 * @property-read \App\Models\Inscripcion $inscripcion
 * @property-read \App\Models\MetodoPago $metodoPago
 * @property-read \App\Models\MotivoDescuento|null $motivoDescuento
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
        'id_inscripcion',
        'id_cliente',
        'monto_total',
        'monto_abonado',
        'monto_pendiente',
        'descuento_aplicado',
        'id_motivo_descuento',
        'fecha_pago',
        'periodo_inicio',
        'periodo_fin',
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
}
