<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'run_pasaporte',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'celular',
        'email',
        'direccion',
        'fecha_nacimiento',
        'contacto_emergencia',
        'telefono_emergencia',
        'id_convenio',
        'observaciones',
        'activo',
    ];

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'id_convenio');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_cliente');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_cliente');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_cliente');
    }

    public function getNombreCompletoAttribute()
    {
        $nombre = $this->nombres . ' ' . $this->apellido_paterno;
        if ($this->apellido_materno) {
            $nombre .= ' ' . $this->apellido_materno;
        }
        return $nombre;
    }
}
