<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivoDescuento extends Model
{
    protected $table = 'motivos_descuento';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_motivo_descuento');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_motivo_descuento');
    }
}
