<?php

namespace App\Support;

use App\Models\Rutina;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * «Encuentra tu rutina», arriba de /rutinas: las preguntas y la rutina que sale.
 *
 * Qué buscas, cuánto llevas y cuántos días son las de siempre. Después vienen
 * cuánto tiempo tienes, cómo prefieres entrenar y si quieres enfocarte en
 * glúteo y pierna, y al final si tienes alguna lesión o molestia.
 *
 * SOLO SE PREGUNTA LO QUE CAMBIA EL RESULTADO. Si para tu objetivo, tu nivel
 * y tus días no hay ninguna funcional, «¿cómo prefieres entrenar?» no aparece:
 * una pregunta cuya respuesta no cambia nada engaña a quien la contesta.
 *
 * Cada respuesta va en la dirección (?objetivo=…&nivel=…), así funciona sin
 * JavaScript y el resultado se puede mandar por WhatsApp.
 *
 * LA QUE SALE es la que menos se aleja de lo contestado: el nivel y los días
 * pesan más que el estilo, y una rutina con más días de los que puedes venir
 * pesa más que una con menos. Si no calza en todo, se dice en qué no.
 */
class EligeTuRutina
{
    public const TIEMPOS = [
        '30' => ['Media hora', 'Entro, entreno y salgo', 'stopwatch'],
        '45' => ['Unos 45 minutos', 'Lo normal de una sesión', 'clock'],
        '60' => ['Una hora o más', 'Sin apuro', 'route'],
    ];

    public const ESTILOS = [
        'maquinas' => ['Con máquinas', 'Guiado y seguro: la máquina marca el movimiento', 'user-check'],
        'mixto' => ['Máquinas y pesas', 'Un poco de todo', 'dumbbell'],
        'funcional' => ['Funcional', 'Peso corporal, pesa rusa y cajón, sin esperar máquinas', 'person-running'],
    ];

    public const ENFOQUES = [
        'parejo' => ['Todo el cuerpo parejo', 'Lo mismo para arriba y para abajo', 'child-reaching'],
        'gluteo' => ['Glúteo y pierna', 'Más días y más trabajo para la pierna', 'fire'],
    ];

    public const MOLESTIAS = [
        'no' => ['No, ninguna', 'Puedo entrenar sin problemas', 'circle-check'],
        'si' => ['Sí, tengo una', 'Rodilla, espalda, hombro u otra', 'circle-exclamation'],
    ];

    /** El orden de las preguntas. */
    private const ORDEN = ['objetivo', 'nivel', 'dias', 'tiempo', 'estilo', 'enfoque', 'molestia'];

    /** @var array<string, string|int|null> */
    public array $respuestas = [];

    /** @var Collection<int, Rutina> */
    private Collection $rutinas;

    /** @param  Collection<int, Rutina>|null  $rutinas  las activas, si ya se tienen */
    public static function desde(Request $request, ?Collection $rutinas = null): self
    {
        $e = new self;
        $e->rutinas = $rutinas ?? Rutina::where('activa', true)->with('dias.ejercicios')->orderBy('orden')->get();

        // Cada respuesta vale solo si la anterior está y si esa pregunta
        // corresponde: la dirección se puede escribir a mano o venir de un
        // enlace viejo.
        foreach (self::ORDEN as $clave) {
            $valor = $request->query($clave);
            $valor = is_scalar($valor) ? trim((string) $valor) : null;

            if ($valor === null || $valor === '' || ! $e->valida($clave, $valor) || ! $e->corresponde($clave)) {
                if ($e->corresponde($clave)) {
                    break;
                }

                continue;
            }

            $e->respuestas[$clave] = $clave === 'dias' ? (int) $valor : $valor;
        }

        return $e;
    }

    /** La pregunta que toca, o null si ya está todo. */
    public function actual(): ?string
    {
        foreach (self::ORDEN as $clave) {
            if (! isset($this->respuestas[$clave]) && $this->corresponde($clave)) {
                return $clave;
            }
        }

        return null;
    }

    /** Las preguntas que se le hacen a esta persona, en orden. @return list<string> */
    public function preguntas(): array
    {
        return array_values(array_filter(self::ORDEN, fn ($c) => isset($this->respuestas[$c]) || $this->corresponde($c)));
    }

    /** La dirección con las respuestas hasta antes de $clave, más las que se agreguen. */
    public function enlace(array $mas = [], ?string $hasta = null): string
    {
        $respuestas = [];

        foreach (self::ORDEN as $clave) {
            if ($clave === $hasta) {
                break;
            }

            if (isset($this->respuestas[$clave])) {
                $respuestas[$clave] = $this->respuestas[$clave];
            }
        }

        return route('landing.rutinas', $respuestas + $mas) . '#elige';
    }

    /**
     * Las opciones de una pregunta: [valor, etiqueta, bajada, icono].
     *
     * @return list<array{0:string|int,1:string,2:string,3:string}>
     */
    public function opciones(string $clave): array
    {
        return match ($clave) {
            'objetivo' => [
                ['empezar', Rutina::OBJETIVOS['empezar'], 'Aprender las máquinas y tomar el ritmo', 'seedling'],
                ['bajar_grasa', Rutina::OBJETIVOS['bajar_grasa'], 'Quemar grasa y tonificar', 'fire'],
                ['fuerza', Rutina::OBJETIVOS['fuerza'], 'Subir pesos y ganar músculo', 'dumbbell'],
                ['mantener', Rutina::OBJETIVOS['mantener'], 'Sentirme bien y no perder lo ganado', 'heart-pulse'],
            ],
            // Para quien está empezando, «entreno hace tiempo» no calza: ahí
            // la tercera es la de quien entrenaba y lo dejó.
            'nivel' => [
                ['nunca', Rutina::NIVELES['nunca'], 'Es mi primera vez en un gimnasio', 'seedling'],
                ['algo', Rutina::NIVELES['algo'], 'Algunos meses, ya conozco las máquinas', 'dumbbell'],
                ($this->respuestas['objetivo'] ?? null) === 'empezar'
                    ? ['hace_tiempo', 'Entrenaba y lo dejé', 'Vuelvo después de un tiempo', 'rotate-left']
                    : ['hace_tiempo', Rutina::NIVELES['hace_tiempo'], 'Más de un año, con constancia', 'medal'],
            ],
            'dias' => array_map(fn (int $n) => [$n, (string) $n, 'días', ''], $this->diasPosibles()),
            'tiempo' => self::comoOpciones(self::TIEMPOS),
            'estilo' => self::comoOpciones(array_intersect_key(self::ESTILOS, array_flip($this->valoresEnJuego('estilo')))),
            'enfoque' => self::comoOpciones(self::ENFOQUES),
            'molestia' => self::comoOpciones(self::MOLESTIAS),
        };
    }

    /** La etiqueta corta de lo contestado, para las fichas de arriba. */
    public function etiqueta(string $clave): string
    {
        $valor = $this->respuestas[$clave] ?? null;

        return match ($clave) {
            'dias' => "{$valor} días",
            'molestia' => $valor === 'si' ? 'Con molestia' : 'Sin molestias',
            default => collect($this->opciones($clave))->firstWhere(0, $valor)[1] ?? (string) $valor,
        };
    }

    /**
     * LA QUE SALE: la que menos se aleja de lo contestado. Si otra queda igual
     * de cerca, van las dos: elegir entre dos es fácil.
     *
     * @return array{exacta:bool, rutinas: list<array<string,mixed>>, diferencias: list<string>}|null
     */
    public function resultado(): ?array
    {
        if ($this->actual() !== null || ! isset($this->respuestas['objetivo'])) {
            return null;
        }

        $puntuadas = $this->rutinas
            ->where('objetivo', $this->respuestas['objetivo'])
            ->map(fn (Rutina $r) => ['rutina' => $r, 'puntos' => $this->distancia($r)])
            ->sortBy([['puntos', 'asc'], [fn ($a, $b) => $a['rutina']->orden <=> $b['rutina']->orden]])
            ->values();

        if ($puntuadas->isEmpty()) {
            return null;
        }

        $mejor = $puntuadas->first()['puntos'];
        $elegidas = $puntuadas->filter(fn ($p) => $p['puntos'] <= $mejor + 0.5)->take(2)->pluck('rutina');

        return [
            'exacta' => $mejor < 1,
            'rutinas' => $elegidas->map(fn (Rutina $r) => [
                'nombre' => RutinaSugerida::sinDias($r->nombre),
                'dias' => (int) $r->dias_por_semana,
                'nivel' => Rutina::NIVELES[$r->nivel] ?? $r->nivel,
                'para' => $r->paraQue(),
                'minutos' => $r->minutos(),
                'estilo' => self::ESTILOS[$r->estilo][0] ?? null,
                'descripcion' => $r->descripcion,
                'jornadas' => $r->dias->sortBy('numero')->map(fn ($d) => $d->titulo)->filter()->values()->all(),
                'url' => route('landing.rutina.ver', $r->slug),
            ])->values()->all(),
            'diferencias' => $this->diferencias($elegidas->first()),
        ];
    }

    public function conMolestia(): bool
    {
        return ($this->respuestas['molestia'] ?? null) === 'si';
    }

    /** Los días que tienen rutina para ese objetivo y ese nivel. @return list<int> */
    public function diasPosibles(): array
    {
        return RutinaSugerida::diasPara((string) ($this->respuestas['objetivo'] ?? ''), (string) ($this->respuestas['nivel'] ?? ''));
    }

    // ------------------------------------------------------------------

    /** Si esa pregunta se le hace a esta persona. */
    private function corresponde(string $clave): bool
    {
        $antes = array_slice(self::ORDEN, 0, (int) array_search($clave, self::ORDEN, true));

        // Sin las tres primeras contestadas no se sabe qué preguntar después.
        foreach (array_intersect($antes, ['objetivo', 'nivel', 'dias']) as $previa) {
            if (! isset($this->respuestas[$previa])) {
                // La de la lesión se hace siempre, al final.
                return in_array($clave, ['objetivo', 'nivel', 'dias', 'molestia'], true);
            }
        }

        return match ($clave) {
            'molestia' => true,
            'tiempo' => $this->cercanas()->contains(fn (Rutina $r) => $r->minutos() <= 30)
                && $this->cercanas()->contains(fn (Rutina $r) => $r->minutos() > 35),
            'estilo' => count($this->valoresEnJuego('estilo')) > 1,
            'enfoque' => count($this->valoresEnJuego('enfoque')) > 1,
            default => true,
        };
    }

    private function valida(string $clave, string $valor): bool
    {
        return match ($clave) {
            'objetivo' => isset(Rutina::OBJETIVOS[$valor]),
            'nivel' => isset(Rutina::NIVELES[$valor]),
            'dias' => ctype_digit($valor) && (int) $valor >= 2 && (int) $valor <= 6,
            'tiempo' => isset(self::TIEMPOS[$valor]),
            'estilo' => isset(self::ESTILOS[$valor]),
            'enfoque' => isset(self::ENFOQUES[$valor]),
            'molestia' => isset(self::MOLESTIAS[$valor]),
            default => false,
        };
    }

    /**
     * Las rutinas entre las que se elige: su objetivo y su nivel, con un día
     * más o uno menos. Sin ninguna de su nivel, las de su objetivo.
     *
     * @return Collection<int, Rutina>
     */
    private function cercanas(): Collection
    {
        $dias = (int) ($this->respuestas['dias'] ?? 3);
        $delObjetivo = $this->rutinas->where('objetivo', $this->respuestas['objetivo'] ?? null);
        $delNivel = $delObjetivo->where('nivel', $this->respuestas['nivel'] ?? null);

        $base = $delNivel->isNotEmpty() ? $delNivel : $delObjetivo;
        // CON SUS DÍAS EXACTOS: si se ofrece «con máquinas» es porque hay una
        // con máquinas de esos días. Solo si no hay ninguna, un día más o menos.
        $mismos = $base->where('dias_por_semana', $dias);

        return ($mismos->isNotEmpty() ? $mismos : $base->filter(fn (Rutina $r) => abs($r->dias_por_semana - $dias) <= 1))
            // Lo ya contestado acota: quien pidió media hora no elige entre
            // estilos que solo existen en rutinas de una hora.
            ->filter(fn (Rutina $r) => ! isset($this->respuestas['tiempo']) || $this->respuestas['tiempo'] === '60' || $r->minutos() <= (int) $this->respuestas['tiempo'] + 5)
            ->filter(fn (Rutina $r) => ! isset($this->respuestas['estilo']) || $r->estilo === $this->respuestas['estilo'])
            ->values();
    }

    /** @return list<string> */
    private function valoresEnJuego(string $atributo): array
    {
        $valores = $this->cercanas()->pluck($atributo)->unique()->values()->all();
        $orden = array_keys($atributo === 'estilo' ? self::ESTILOS : self::ENFOQUES);

        return array_values(array_intersect($orden, $valores));
    }

    /** Cuánto se aleja una rutina de lo contestado. 0 es exacta. */
    private function distancia(Rutina $r): float
    {
        $niveles = array_keys(Rutina::NIVELES);
        $puntos = 3 * abs(array_search($r->nivel, $niveles, true) - array_search($this->respuestas['nivel'], $niveles, true));

        $dias = (int) $this->respuestas['dias'];
        // Más días de los que puede venir es peor que menos: no la va a cumplir.
        $puntos += $r->dias_por_semana > $dias ? 4 * ($r->dias_por_semana - $dias) : 2 * ($dias - $r->dias_por_semana);

        if (isset($this->respuestas['tiempo'])) {
            $tiempo = (int) $this->respuestas['tiempo'];
            $minutos = $r->minutos();
            $puntos += match (true) {
                $minutos > $tiempo + 5 && $tiempo < 60 => 3 + ($minutos - $tiempo) / 10,
                // Con una hora, mejor una que la aproveche; pero una corta no
                // es «otra» rutina: solo desempata.
                $tiempo === 60 && $minutos <= 25 => 0.4,
                default => 0,
            };
        }

        if (isset($this->respuestas['estilo']) && $r->estilo !== $this->respuestas['estilo']) {
            $puntos += $this->respuestas['estilo'] === 'funcional' || $r->estilo === 'funcional' ? 4 : 1.5;
        }

        if (isset($this->respuestas['enfoque']) && $r->enfoque !== $this->respuestas['enfoque']) {
            $puntos += 3;
        }

        return $puntos;
    }

    /** En qué no calza la que salió, dicho corto. @return list<string> */
    private function diferencias(?Rutina $r): array
    {
        if (! $r) {
            return [];
        }

        $notas = [];

        if ($r->nivel !== $this->respuestas['nivel']) {
            $notas[] = 'Es para «' . mb_strtolower(Rutina::NIVELES[$r->nivel]) . '»: no hay una así para tu nivel.';
        }

        if ($r->dias_por_semana !== (int) $this->respuestas['dias']) {
            $notas[] = "Es de {$r->dias_por_semana} días: la más cercana a los {$this->respuestas['dias']} que puedes venir.";
        }

        if (isset($this->respuestas['tiempo']) && $this->respuestas['tiempo'] !== '60' && $r->minutos() > (int) $this->respuestas['tiempo'] + 5) {
            $notas[] = "Dura unos {$r->minutos()} minutos: no hay una más corta con lo demás que pediste.";
        }

        if (isset($this->respuestas['estilo']) && $r->estilo !== $this->respuestas['estilo']) {
            $notas[] = 'Es ' . mb_strtolower(self::ESTILOS[$r->estilo][0] ?? $r->estilo) . ': no hay una ' . mb_strtolower(self::ESTILOS[$this->respuestas['estilo']][0]) . ' con lo demás que pediste.';
        }

        if (isset($this->respuestas['enfoque']) && $r->enfoque !== $this->respuestas['enfoque']) {
            $notas[] = $this->respuestas['enfoque'] === 'gluteo'
                ? 'Trabaja todo el cuerpo parejo: no hay una de glúteo con lo demás que pediste.'
                : 'Se enfoca en glúteo y pierna.';
        }

        return $notas;
    }

    /** @return list<array{0:string,1:string,2:string,3:string}> */
    private static function comoOpciones(array $lista): array
    {
        return array_map(fn ($valor, $o) => [(string) $valor, $o[0], $o[1], $o[2]], array_keys($lista), $lista);
    }
}
