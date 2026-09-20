<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Una rutina de la sala: la que se ve al leer el QR.
 *
 * ES UNA GUÍA GENERAL, no un plan hecho para una persona. Por eso se elige con
 * tres respuestas —qué busca, cuánto lleva entrenando y cuántos días puede
 * venir— y no se guarda nada de quien la mira.
 */
class Rutina extends Model
{
    protected $table = 'rutinas';

    public const OBJETIVOS = [
        'empezar' => 'Estoy empezando',
        'bajar_grasa' => 'Bajar de peso',
        'fuerza' => 'Ganar fuerza y músculo',
        'mantener' => 'Mantenerme',
    ];

    public const NIVELES = [
        'nunca' => 'Nunca he entrenado',
        'algo' => 'He entrenado algo',
        'hace_tiempo' => 'Entreno hace tiempo',
    ];

    protected $fillable = ['nombre', 'objetivo', 'nivel', 'dias_por_semana', 'descripcion', 'activa', 'orden'];

    protected $casts = [
        'activa' => 'boolean',
        'dias_por_semana' => 'integer',
        'orden' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $r) => $r->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function dias(): HasMany
    {
        return $this->hasMany(RutinaDia::class, 'id_rutina')->orderBy('numero');
    }
}
