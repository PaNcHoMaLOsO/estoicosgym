<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
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
    ];

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

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_inscripcion');
    }

    /**
     * Pausar la membresía por un período especificado
     * @param int $dias 7, 14 o 30 días
     * @param string $razon Motivo de la pausa
     * @return bool
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
     * @return bool
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
            
            $this->update([
                'pausada' => false,
                'fecha_vencimiento' => $nuevaFechaVencimiento,
                'id_estado' => 1, // Activa
            ]);
        }

        return true;
    }

    /**
     * Verificar si la pausa ha expirado automáticamente
     * @return bool
     */
    public function verificarPausaExpirada()
    {
        if ($this->pausada && $this->fecha_pausa_fin && now()->isAfter($this->fecha_pausa_fin)) {
            return $this->reanudar();
        }

        return false;
    }

    /**
     * Obtener información de la pausa actual
     * @return array|null
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
     * Puede pausarse esta membresía?
     * @return bool
     */
    public function puedepausarse()
    {
        return !$this->pausada 
            && $this->pausas_realizadas < $this->max_pausas_permitidas
            && $this->id_estado == 1; // Solo si está activa
    }
}
