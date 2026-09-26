<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Una falla del sistema, con cuántas veces pasó. Ver App\Support\RegistroDeFallas. */
class Falla extends Model
{
    protected $table = 'fallas';

    protected $fillable = [
        'huella', 'origen', 'nivel', 'tipo', 'mensaje', 'lugar', 'metodo', 'url',
        'id_usuario', 'traza', 'contexto', 'veces', 'primera_vez', 'ultima_vez', 'resuelta_en',
    ];

    protected $casts = [
        'contexto' => 'array',
        'veces' => 'integer',
        'primera_vez' => 'datetime',
        'ultima_vez' => 'datetime',
        'resuelta_en' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function scopeAbiertas(Builder $consulta): Builder
    {
        return $consulta->whereNull('resuelta_en');
    }
}
