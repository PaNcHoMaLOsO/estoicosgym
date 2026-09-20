<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * El precio que UN convenio negoció para UN plan.
 *
 * El club de básquetbol paga 15.000 la mensualidad y el de fútbol 10.000: eso
 * no cabe en el «precio con convenio» del plan, que es uno solo para todos.
 * Cuando no hay fila aquí, manda ese precio general (ver PrecioAcordado).
 */
class ConvenioPrecio extends Model
{
    protected $table = 'convenio_precios';

    protected $fillable = ['id_convenio', 'id_membresia', 'precio', 'condicion'];

    protected $casts = ['precio' => 'integer'];

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class, 'id_convenio');
    }

    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class, 'id_membresia');
    }
}
