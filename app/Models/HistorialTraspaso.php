<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Modelo para el historial de traspasos de membresías
 * 
 * @property int $id
 * @property string $uuid
 * @property int $inscripcion_origen_id
 * @property int $inscripcion_destino_id
 * @property int $cliente_origen_id
 * @property int $cliente_destino_id
 * @property int $membresia_id
 * @property \Carbon\Carbon $fecha_traspaso
 * @property string $motivo
 * @property int $dias_restantes_traspasados
 * @property \Carbon\Carbon $fecha_vencimiento_original
 * @property float $monto_pagado
 * @property float $deuda_transferida
 * @property bool $se_transfirio_deuda
 * @property int|null $usuario_id
 */
class HistorialTraspaso extends Model
{
    protected $table = 'historial_traspasos';

    protected $fillable = [
        'uuid',
        'inscripcion_origen_id',
        'inscripcion_destino_id',
        'cliente_origen_id',
        'cliente_destino_id',
        'membresia_id',
        'fecha_traspaso',
        'motivo',
        'dias_restantes_traspasados',
        'fecha_vencimiento_original',
        'monto_pagado',
        'deuda_transferida',
        'se_transfirio_deuda',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_traspaso' => 'date',
        'fecha_vencimiento_original' => 'date',
        'monto_pagado' => 'decimal:0',
        'deuda_transferida' => 'decimal:0',
        'se_transfirio_deuda' => 'boolean',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Obtener el modelo por UUID
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('uuid', $value)->orWhere('id', $value)->firstOrFail();
    }

    // ============================================
    // RELACIONES
    // ============================================

    /**
     * Inscripción origen (la que se traspasó)
     */
    public function inscripcionOrigen()
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_origen_id');
    }

    /**
     * Inscripción destino (la nueva creada)
     */
    public function inscripcionDestino()
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_destino_id');
    }

    /**
     * Cliente que cedió la membresía
     */
    public function clienteOrigen()
    {
        return $this->belongsTo(Cliente::class, 'cliente_origen_id');
    }

    /**
     * Cliente que recibió la membresía
     */
    public function clienteDestino()
    {
        return $this->belongsTo(Cliente::class, 'cliente_destino_id');
    }

    /**
     * Membresía traspasada
     */
    public function membresia()
    {
        return $this->belongsTo(Membresia::class, 'membresia_id');
    }

    /**
     * Usuario que realizó el traspaso
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Traspasos de un cliente específico (como origen o destino)
     */
    public function scopeDeCliente($query, $clienteId)
    {
        return $query->where('cliente_origen_id', $clienteId)
                     ->orWhere('cliente_destino_id', $clienteId);
    }

    /**
     * Traspasos en un rango de fechas
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_traspaso', [$desde, $hasta]);
    }

    /**
     * Traspasos con deuda transferida
     */
    public function scopeConDeudaTransferida($query)
    {
        return $query->where('se_transfirio_deuda', true);
    }

    /**
     * Traspasos recientes (últimos N días)
     */
    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_traspaso', '>=', now()->subDays($dias));
    }

    // ============================================
    // HELPERS
    // ============================================

    /**
     * Obtener resumen del traspaso
     */
    public function getResumen(): string
    {
        $origen = $this->clienteOrigen;
        $destino = $this->clienteDestino;
        
        return sprintf(
            "%s → %s (%s, %d días)",
            $origen ? "{$origen->nombres} {$origen->apellido_paterno}" : 'N/A',
            $destino ? "{$destino->nombres} {$destino->apellido_paterno}" : 'N/A',
            $this->membresia->nombre ?? 'N/A',
            $this->dias_restantes_traspasados
        );
    }

    /**
     * Formatear monto pagado
     */
    public function getMontoPagadoFormateadoAttribute(): string
    {
        return '$' . number_format($this->monto_pagado, 0, ',', '.');
    }

    /**
     * Formatear deuda transferida
     */
    public function getDeudaTransferidaFormateadaAttribute(): string
    {
        return '$' . number_format($this->deuda_transferida, 0, ',', '.');
    }
}
