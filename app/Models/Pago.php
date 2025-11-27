<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


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
        'monto_abonado',
        'monto_pendiente',
        'id_motivo_descuento',
        'fecha_pago',
        'id_metodo_pago_principal',
        'metodos_pago_json',
        'referencia_pago',
        'es_plan_cuotas',
        'numero_cuota',
        'cantidad_cuotas',
        'monto_cuota',
        'fecha_vencimiento_cuota',
        'grupo_pago',
        'id_estado',
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

    public function metodoPagoPrincipal()
    {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago_principal');
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
            return collect([]);
        }
        return self::where('grupo_pago', $this->grupo_pago)
            ->orderBy('numero_cuota')
            ->get();
    }

    /**
     * ¿Este pago es parte de un plan de cuotas?
     */
    public function esParteDeCuotas()
    {
        return $this->es_plan_cuotas ?? false;
    }

    /**
     * ¿Es la última cuota?
     */
    public function esUltimaCuota()
    {
        if (!$this->esParteDeCuotas()) {
            return false;
        }
        return $this->numero_cuota >= $this->cantidad_cuotas;
    }

    /**
     * ¿Es una cuota válida?
     */
    public function esNumeroCuotaValido()
    {
        if (!$this->esParteDeCuotas()) {
            return true;
        }
        return $this->numero_cuota > 0 && $this->numero_cuota <= $this->cantidad_cuotas;
    }

    /**
     * ¿Es pago mixto (múltiples métodos)?
     */
    public function esPagoMixto()
    {
        return $this->metodos_pago_json && count($this->metodos_pago_json) > 1;
    }

    /**
     * Obtener desglose de métodos de pago
     */
    public function obtenerDesglose()
    {
        if (!$this->metodos_pago_json) {
            return [
                $this->metodoPagoPrincipal->codigo ?? 'desconocido' => $this->monto_abonado,
            ];
        }
        return $this->metodos_pago_json;
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
            ->whereIn('id_estado', [102, 103]) // Pagado o Parcial
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
            ->whereIn('id_estado', [102, 103])
            ->sum('monto_abonado');
    }

    /**
     * Calcular el estado dinámico basado en montos y fechas
     * 101: PENDIENTE
     * 102: PAGADO
     * 103: PARCIAL
     * 104: VENCIDO
     */
    public function calculateEstadoDinamico()
    {
        if (!$this->inscripcion) {
            return 101;
        }

        $saldoPendiente = $this->getSaldoPendiente();
        $totalAbonado = $this->getTotalAbonado();

        // Si todo está pagado
        if ($saldoPendiente <= 0) {
            return 102; // PAGADO
        }

        // Si es cuota vencida
        if ($this->esParteDeCuotas() &&
            $this->fecha_vencimiento_cuota &&
            now()->isAfter($this->fecha_vencimiento_cuota)) {
            return 104; // VENCIDO
        }

        // Si hay algo abonado (parcial)
        if ($totalAbonado > 0 || $this->monto_abonado > 0) {
            return 103; // PARCIAL
        }

        return 101; // PENDIENTE
    }
}
