<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'id_cliente',
        'id_membresia',
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
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
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

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_inscripcion');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_inscripcion');
    }
}
