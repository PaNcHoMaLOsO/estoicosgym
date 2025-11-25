<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    protected $table = 'metodos_pago';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'requiere_comprobante',
        'activo',
    ];

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_metodo_pago');
    }
}
