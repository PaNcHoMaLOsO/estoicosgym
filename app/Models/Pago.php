<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\Cliente;

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
    use HasFactory,SoftDeletes;

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
        'fecha_pago',
        'id_metodo_pago',
        'id_metodo_pago2',
        'monto_metodo1',
        'monto_metodo2',
        'referencia_pago',
        'cantidad_cuotas',
        'numero_cuota',
        'monto_cuota',
        'periodo_inicio',
        'periodo_fin',
        'id_estado',
        'tipo_pago',
        'observaciones',
        'id_usuario',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
        'monto_total' => 'decimal:2',
        'monto_abonado' => 'decimal:2',
        'monto_pendiente' => 'decimal:2',
        'monto_cuota' => 'decimal:2',
        'monto_metodo1' => 'decimal:2',
        'monto_metodo2' => 'decimal:2',
        'cantidad_cuotas' => 'integer',
        'numero_cuota' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }

            /*
             * QUIÉN LO COBRÓ, desde el pago mismo y no desde cada servicio:
             * se cobra al inscribir, en el alta rápida, al renovar, en el
             * cobro suelto y al traspasar, y el camino que se sumara mañana
             * sin anotarlo dejaría pagos sin autor que nadie podría corregir
             * con «sus cobros de hoy». Desde la consola o una tarea no hay
             * sesión y queda vacío, que es la verdad.
             */
            if (empty($model->id_usuario)) {
                $model->id_usuario = auth()->id();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Estados en los que el dinero ENTRO de verdad a la caja.
     *
     * 201 es el pago saldado y 202 el abono: en los dos hay plata recibida y
     * las dos cuentan como ingreso. Quedan fuera el pendiente (200), que no ha
     * pagado nada, el cancelado (204) y el traspasado (205), que ya se contó en
     * la inscripción a la que se mudó.
     */
    public const ESTADOS_CON_INGRESO = [201, 202];

    /**
     * Lo que se cobró de verdad.
     *
     * EXISTE PARA QUE DOS PANTALLAS NO DIGAN COSAS DISTINTAS. El panel de inicio
     * sumaba 201 y 202, y los reportes solo 201: para el mismo mes uno decía
     * $2.000.034 y el otro $1.295.000, porque los abonos parciales —la mitad
     * del dinero— no aparecían en los informes. Con el criterio escrito en un
     * solo sitio no pueden volver a separarse.
     *
     * Se usa como `Pago::ingresos()->sum('monto_abonado')`.
     */
    public function scopeIngresos($query)
    {
        // La columna va CALIFICADA con su tabla: los informes cruzan pagos con
        // inscripciones, que tiene su propio id_estado, y sin el prefijo MySQL
        // rechaza la consulta por ambigua.
        return $query->whereIn('pagos.id_estado', self::ESTADOS_CON_INGRESO);
    }

    /**
     * Los pagos que todavía se le pueden cobrar al socio.
     *
     * No basta con el estado del pago: el de una membresía cancelada, cambiada
     * de plan o traspasada se queda «pendiente» para siempre —el pago no se
     * toca, es la historia de lo que se cobró—, pero esa membresía ya no tiene
     * deuda (Inscripcion::ESTADOS_CON_DEUDA). Contarlo bloqueaba la baja y el
     * borrado de datos de quien no debía nada, y no habia forma de salir.
     * Un pago suelto, sin membresía, sí se sigue cobrando.
     */
    public function scopePendientesDeCobro($query)
    {
        return $query->whereIn('pagos.id_estado', \App\Enums\EstadosCodigo::PAGO_PENDIENTES_COBRO)
            ->where(fn ($q) => $q->whereNull('pagos.id_inscripcion')
                ->orWhereHas('inscripcion', fn ($i) => $i->whereIn('id_estado', Inscripcion::ESTADOS_CON_DEUDA)));
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /** Quién lo registró. Vacío en los pagos de antes de anotarlo. */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function metodoPago()
    {
        // Con los de la papelera: un método que se retira no le borra el
        // nombre a los pagos que ya se hicieron con él.
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago')->withTrashed();
    }

    public function metodoPago2()
    {
        // Con los de la papelera: un método que se retira no le borra el
        // nombre a los pagos que ya se hicieron con él.
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago2')->withTrashed();
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'codigo');
    }

    public function motivoDescuento()
    {
        return $this->belongsTo(MotivoDescuento::class, 'id_motivo_descuento');
    }

    /**
     * Obtener saldo pendiente de la inscripción
     */
    public function getSaldoPendiente()
    {
        if (!$this->inscripcion) {
            return 0;
        }

        $totalAbonado = $this->inscripcion->pagos()
            ->whereIn('id_estado', [201, 202]) // Pagado o Parcial
            ->sum('monto_abonado');

        return max(0, $this->inscripcion->precio_final - $totalAbonado);
    }

    /**
     * Obtener total abonado hasta ahora
     */
    public function getTotalAbonado()
    {
        if (!$this->inscripcion) {
            return 0;
        }

        return $this->inscripcion->pagos()
            ->whereIn('id_estado', [201, 202])
            ->sum('monto_abonado');
    }
}
