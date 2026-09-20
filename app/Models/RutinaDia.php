<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Un día de una rutina: «Día 1 · Tren superior», con sus ejercicios. */
class RutinaDia extends Model
{
    protected $table = 'rutina_dias';

    protected $fillable = ['id_rutina', 'numero', 'titulo', 'foco'];

    protected $casts = ['numero' => 'integer'];

    public function rutina(): BelongsTo
    {
        return $this->belongsTo(Rutina::class, 'id_rutina');
    }

    public function ejercicios(): HasMany
    {
        return $this->hasMany(RutinaEjercicio::class, 'id_dia')->orderBy('orden');
    }
}
