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

    /**
     * «para bajar de peso», «para principiantes»: como se busca en Google.
     *
     * Las opciones del cuestionario están dichas en primera persona («Estoy
     * empezando», «Nunca he entrenado») y pegadas tal cual en lo que lee
     * Google daban «Rutina de 3 días para Estoy empezando. Nunca he entrenado.»
     */
    public function paraQue(): string
    {
        return match ($this->objetivo) {
            'empezar' => $this->nivel === 'hace_tiempo' ? 'para volver al gimnasio' : 'para principiantes',
            'bajar_grasa' => 'para bajar de peso',
            'fuerza' => 'para ganar fuerza y músculo',
            'mantener' => 'para mantenerse en forma',
            default => 'de gimnasio',
        };
    }

    /** «si nunca has entrenado»: para quién es, dicho al que lee. */
    public function paraQuien(): string
    {
        if ($this->objetivo === 'empezar') {
            return match ($this->nivel) {
                'nunca' => 'que nunca han entrenado',
                'algo' => 'que ya conocen las máquinas',
                default => 'después de un tiempo sin entrenar',
            };
        }

        return match ($this->nivel) {
            'nunca' => 'si nunca has entrenado',
            'algo' => 'si ya has entrenado algo',
            'hace_tiempo' => 'si entrenas hace tiempo',
            default => '',
        };
    }

    /** Cómo se entrena: lo pregunta la web a quien busca su rutina. */
    public const ESTILOS = [
        'maquinas' => 'Con máquinas',
        'mixto' => 'Máquinas y pesas',
        'funcional' => 'Funcional',
    ];

    /** En qué se enfoca. */
    public const ENFOQUES = [
        'parejo' => 'Todo el cuerpo',
        'gluteo' => 'Glúteo y pierna',
    ];

    /**
     * CUÁNTO DURA UNA SESIÓN, en minutos, contada de lo que tiene: cada serie
     * con su descanso, lo que va en tiempo («30 segundos», «15 minutos») tal
     * cual, y cinco minutos para calentar y cambiar de máquina. Es una
     * estimación para saber si cabe en la hora de almuerzo, no un cronómetro.
     */
    public function minutos(): int
    {
        return $this->minutosCalculados ??= $this->contarMinutos();
    }

    /** Se cuenta una vez por petición: las preguntas de la web lo piden muchas veces. */
    private ?int $minutosCalculados = null;

    private function contarMinutos(): int
    {
        $dias = $this->relationLoaded('dias') ? $this->dias : $this->dias()->with('ejercicios')->get();
        $porDia = $dias->map(function ($dia) {
            $segundos = 0;

            foreach ($dia->ejercicios as $l) {
                $segundos += self::segundosDeLinea((int) $l->series, (string) $l->repeticiones, (int) $l->descanso_seg);
            }

            return $segundos / 60 + 5;
        });

        return $porDia->isEmpty() ? 0 : (int) (round($porDia->avg() / 5) * 5);
    }

    /**
     * Lo que toma un ejercicio: cada serie, lo que dura (40 s, o lo que diga:
     * «30 segundos», «10 minutos»; el doble si es por lado) más su descanso,
     * y 20 s para acomodar la máquina.
     */
    public static function segundosDeLinea(int $series, string $repeticiones, int $descanso): int
    {
        $reps = mb_strtolower($repeticiones);
        $series = max(1, $series);
        $doble = str_contains($reps, 'por pierna') || str_contains($reps, 'por brazo') || str_contains($reps, 'por lado') ? 2 : 1;
        $trabajo = match (true) {
            (bool) preg_match('/(\d+)\s*minutos?/u', $reps, $m) => (int) $m[1] * 60,
            (bool) preg_match('/(\d+)(?:\s*a\s*(\d+))?\s*segundos?/u', $reps, $m) => (int) ($m[2] ?? $m[1]) * $doble,
            default => 40 * $doble,
        };

        return $series * ($trabajo + $descanso) + 20;
    }

    protected $fillable = ['nombre', 'slug', 'objetivo', 'nivel', 'dias_por_semana', 'estilo', 'enfoque', 'descripcion', 'activa', 'orden'];

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
