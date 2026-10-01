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

    protected $fillable = ['nombre', 'slug', 'objetivo', 'nivel', 'dias_por_semana', 'descripcion', 'activa', 'orden'];

    protected $casts = [
        'activa' => 'boolean',
        'dias_por_semana' => 'integer',
        'orden' => 'integer',
        'slugs_anteriores' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $r) {
            $r->uuid ??= (string) Str::uuid();
            // Una copia (Duplicar) no hereda las direcciones viejas de la
            // original; su slug nuevo lo pone `saving`, que corre antes.
            $r->slugs_anteriores = null;
        });

        // Su página, /rutinas/primeros-pasos-2-dias, sale del nombre. Si se
        // corrige el nombre se rehace, y la vieja queda guardada para
        // redirigir: ya puede estar compartida o impresa.
        static::saving(function (self $r) {
            if (! $r->slug || $r->isDirty('nombre')) {
                $anterior = $r->getOriginal('slug');
                $r->slug = $r->slugLibre();

                if ($anterior && $anterior !== $r->slug) {
                    $r->slugs_anteriores = array_values(array_unique([...($r->slugs_anteriores ?? []), $anterior]));
                }
            }
        });
    }

    /** «Primeros pasos · 2 días» → «primeros-pasos-2-dias»; si ya existe, con -2. */
    private function slugLibre(): string
    {
        $base = Str::slug($this->nombre) ?: 'rutina';
        $slug = $base;

        for ($n = 2; static::where('slug', $slug)->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))->exists(); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
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
