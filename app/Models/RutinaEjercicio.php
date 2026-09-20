<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un ejercicio dentro de un día: cuántas series, cuántas veces y qué cuidar. */
class RutinaEjercicio extends Model
{
    protected $table = 'rutina_ejercicios';

    protected $fillable = ['id_dia', 'id_ejercicio', 'series', 'repeticiones', 'descanso_seg', 'nota', 'orden'];

    protected $casts = ['series' => 'integer', 'descanso_seg' => 'integer', 'orden' => 'integer'];

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class, 'id_ejercicio');
    }

    public function dia(): BelongsTo
    {
        return $this->belongsTo(RutinaDia::class, 'id_dia');
    }
}
