<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Una clase del gimnasio (judo, lucha olímpica…), abierta a cualquiera y con
 * mensualidad. No confundir con los talleres, que son arriendos.
 *
 * @property array<int,array{dia:string,desde:string,hasta:string}> $horario
 */
class Clase extends Model
{
    protected $table = 'clases';

    protected $fillable = [
        'uuid',
        'nombre',
        'slug',
        'slugs_anteriores',
        'descripcion',
        'profesor',
        'para_quien',
        'precio_mensual',
        'imagen',
        'horario',
        'color',
        'activo',
        'orden',
    ];

    protected $casts = [
        'horario' => 'array',
        'activo' => 'boolean',
        'orden' => 'integer',
        'precio_mensual' => 'integer',
        'slugs_anteriores' => 'array',
    ];

    /** Los días, en el orden de la semana: la clave y cómo se lee. */
    public const DIAS = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    /**
     * Los colores que se pueden elegir, con su valor.
     *
     * UNA LISTA FIJA y no un selector libre: la web está compilada con
     * Tailwind y el color va en línea, así que cualquiera serviría, pero un
     * amarillo claro con letra blanca no se lee. Estos van todos con letra
     * blanca encima.
     */
    public const COLORES = [
        'rojo' => ['nombre' => 'Rojo', 'hex' => '#c81e26'],
        'azul' => ['nombre' => 'Azul', 'hex' => '#1d5fbf'],
        'verde' => ['nombre' => 'Verde', 'hex' => '#1f8a4c'],
        'naranjo' => ['nombre' => 'Naranjo', 'hex' => '#c2570c'],
        'morado' => ['nombre' => 'Morado', 'hex' => '#6d3fc0'],
        'turquesa' => ['nombre' => 'Turquesa', 'hex' => '#0e7c86'],
        'rosado' => ['nombre' => 'Rosado', 'hex' => '#b8326e'],
        'gris' => ['nombre' => 'Gris', 'hex' => '#4b5563'],
    ];

    protected static function booted(): void
    {
        static::creating(function (Clase $clase) {
            if (empty($clase->uuid)) {
                $clase->uuid = (string) Str::uuid();
            }
        });

        // Su página, /clases/judo, sale del nombre. Si se corrige el nombre se
        // rehace, y la vieja queda guardada para redirigir: ya puede estar
        // compartida.
        static::saving(function (Clase $clase) {
            if (! $clase->slug || $clase->isDirty('nombre')) {
                $anterior = $clase->getOriginal('slug');
                $clase->slug = $clase->slugLibre();

                if ($anterior && $anterior !== $clase->slug) {
                    $clase->slugs_anteriores = array_values(array_unique([
                        ...($clase->slugs_anteriores ?? []),
                        $anterior,
                    ]));
                }
            }
        });
    }

    /** «Lucha olímpica» → «lucha-olimpica»; si ya existe, «lucha-olimpica-2». */
    private function slugLibre(): string
    {
        $base = Str::slug($this->nombre) ?: 'clase';
        $slug = $base;

        for ($n = 2; static::where('slug', $slug)->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))->exists(); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function urlDeImagen(): ?string
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : null;
    }

    /** El valor del color para pintarlo en línea. */
    public function hex(): string
    {
        return self::COLORES[$this->color]['hex'] ?? self::COLORES['rojo']['hex'];
    }

    /**
     * El horario ordenado por día y hora: se guarda como se escribió, y
     * «sábado, lunes» se lee raro.
     *
     * @return list<array{dia:string,desde:string,hasta:string}>
     */
    public function horarioOrdenado(): array
    {
        $puesto = array_flip(array_keys(self::DIAS));

        return collect($this->horario ?? [])
            ->filter(fn ($b) => isset($b['dia'], $b['desde'], $b['hasta'], $puesto[$b['dia']]))
            ->sortBy(fn (array $b) => sprintf('%d-%s', $puesto[$b['dia']], $b['desde']))
            ->values()
            ->all();
    }

    /**
     * Los días y horas en una línea: «Lun y Mié · 19:00 a 20:30».
     *
     * Los días con la misma hora se juntan: tres veces «19:00 a 20:30»
     * seguidas es ruido.
     */
    public function horarioEnUnaLinea(): string
    {
        $cortos = ['lunes' => 'Lun', 'martes' => 'Mar', 'miercoles' => 'Mié', 'jueves' => 'Jue', 'viernes' => 'Vie', 'sabado' => 'Sáb', 'domingo' => 'Dom'];

        return collect($this->horarioOrdenado())
            ->groupBy(fn (array $b) => "{$b['desde']} a {$b['hasta']}")
            ->map(function ($bloques, string $horas) use ($cortos) {
                $dias = $bloques->pluck('dia')->unique()->map(fn (string $d) => $cortos[$d])->values();
                $lista = $dias->count() > 1
                    ? $dias->slice(0, -1)->implode(', ') . ' y ' . $dias->last()
                    : $dias->first();

                return "{$lista} · {$horas}";
            })
            ->implode(' / ');
    }
}
