<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\ContenidoWeb;
use App\Models\Especialista;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Support\Ajustes;
use App\Support\EntrenamientoDeHoy;
use App\Support\Especialidades;
use App\Support\MedidasDeImagen;
use App\Support\RutinaSugerida;
use App\Support\WebPublica;
use App\Services\CorreoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LandingController extends Controller
{
    /** Las categorias de convenio, en el orden en que se leen en la web. */
    private const CATEGORIAS_DE_CONVENIO = [
        'institucion_educativa' => 'Universidades e institutos',
        'organizacion' => 'Instituciones',
        'empresa' => 'Empresas',
        'otro' => 'Otros convenios',
    ];

    /**
     * Lo que puede escribir el cliente en «Interes», y como llega al correo.
     */
    private const INTERESES = [
        'informacion' => 'Información general',
        'inscripcion' => 'Quiero inscribirme',
        'convenio' => 'Convenio de empresa',
        'arriendo' => 'Arriendo de horas',
        'otro' => 'Otro',
    ];

    /** Los dias de la semana: la clave del ajuste, como se lee y como lo pide Google. */
    private const DIAS = [
        'lunes' => ['Lunes', 'Monday'],
        'martes' => ['Martes', 'Tuesday'],
        'miercoles' => ['Miércoles', 'Wednesday'],
        'jueves' => ['Jueves', 'Thursday'],
        'viernes' => ['Viernes', 'Friday'],
        'sabado' => ['Sábado', 'Saturday'],
        'domingo' => ['Domingo', 'Sunday'],
    ];

    /** El nombre corto de cada página, para las migas que muestra Google. */
    private const MIGAS = [
        'landing.gimnasio' => 'El gimnasio',
        'landing.planes' => 'Planes y precios',
        'landing.convenios' => 'Convenios',
        'landing.arriendo' => 'Arriendo de horas',
        'landing.clases' => 'Clases',
        'landing.especialistas' => 'Especialistas',
        'landing.contacto' => 'Contacto',
        'landing.membresia' => 'Mi membresía',
        'landing.privacidad' => 'Privacidad',
        'landing.terminos' => 'Términos y condiciones',
        'landing.rutina' => 'Qué entrenar hoy',
        'landing.rutinas' => 'Rutinas',
        'landing.ejercicios' => 'Ejercicios',
    ];

    /**
     * Inicio: quién es el gimnasio y el camino a cada página.
     *
     * UNA PÁGINA POR TEMA, no todo en una. La portada dice lo esencial y lleva
     * a planes, convenios, especialistas y la consulta; cada tema tiene su
     * página con su título, que es lo que Google muestra y lo que la gente
     * busca: «convenios estudiantes gimnasio Los Ángeles» cae directo en la de
     * convenios.
     *
     * TODO LO QUE MUESTRA SALE DEL SISTEMA: planes y precios del catálogo,
     * contacto y textos de Configuración, servicios, fotos y testimonios de
     * Página web. Nada escrito a mano en la vista.
     */
    public function index()
    {
        $comun = $this->comun();

        return $this->pagina('landing.inicio', 'landing', null, null, [
            'portada' => [
                'titulo_1' => Ajustes::obtener('portada.titulo_1'),
                'titulo_2' => Ajustes::obtener('portada.titulo_2'),
                'subtitulo' => Ajustes::obtener('portada.subtitulo'),
            ],
            'fotoPortada' => $this->contenidos('foto')->first(),
            'fondoPortada' => $this->fondoDePortada(),
            'destacados' => $this->destacados($comun),
            'logosConvenios' => collect($comun['convenios'])->flatMap(fn (array $g) => $g['convenios'])->values()->all(),
            'servicios' => $this->contenidos('servicio')->take(3)->values()->all(),
            'testimonios' => $this->contenidos('testimonio')->all(),
            // Solo en la portada: es el único sitio donde salen, y así no se
            // hace una consulta más en cada página.
            'embajadores' => $this->embajadoresEnLaWeb($comun['gimnasio']['nombre']),
            'json_ld' => $comun['web']['json_ld'],
        ], $comun);
    }

    public function gimnasio()
    {
        $comun = $this->comun();
        $ciudad = $comun['web']['ciudad'];

        return $this->pagina('landing.el-gimnasio', 'landing.gimnasio', 'El gimnasio',
            "Cómo es {$comun['gimnasio']['nombre']} por dentro: servicios, fotos y horario" . ($ciudad ? " de nuestro gimnasio en {$ciudad}." : '.'),
            [
                'servicios' => $this->contenidos('servicio')->all(),
                // Con su ancho y su alto: el navegador guarda el hueco de cada
                // foto y la galería no salta mientras cargan.
                'fotos' => $this->contenidos('foto')
                    ->map(fn (array $f) => $f + ['medidas' => MedidasDeImagen::de($f['imagen'])])
                    ->all(),
                // La misma portada que gira en el inicio: fotos apaisadas y vídeos.
                'fondoPortada' => $this->fondoDePortada(),
            ], $comun);
    }

    public function planes()
    {
        $comun = $this->comun();

        // Lo que Google enseña como «desde» tiene que ser una mensualidad: el
        // pase de un día no es el precio de ser socio.
        $mensualidades = $this->mensualidades($comun['planes']);
        $precios = array_column($mensualidades, 'precio');
        $nombres = implode(', ', array_column($mensualidades, 'nombre'));

        return $this->pagina('landing.planes', 'landing.planes', 'Planes y precios',
            "Planes de {$comun['gimnasio']['nombre']}" . ($comun['web']['ciudad'] ? " en {$comun['web']['ciudad']}" : '')
                . ($nombres ? ": {$nombres}." : '.')
                . ($precios ? ' Desde ' . $this->pesos(min($precios)) . '.' : ''),
            [
                // La misma ficha del gimnasio (el mismo @id), con lo que vende:
                // Google junta las dos y sabe que estos precios son de él.
                'json_ld' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'ExerciseGym',
                    '@id' => $comun['web']['id_gimnasio'],
                    'name' => $comun['gimnasio']['nombre'],
                    'url' => url('/'),
                    'makesOffer' => array_values(array_map(fn (array $plan) => array_filter([
                        '@type' => 'Offer',
                        'name' => 'Plan ' . $plan['nombre'],
                        'description' => $plan['duracion'] ?: null,
                        'price' => $plan['precio'],
                        'priceCurrency' => 'CLP',
                        'url' => route('landing.planes'),
                    ]), array_filter($comun['planes'], fn (array $plan) => $plan['precio'] > 0))),
                ]),
            ], $comun);
    }

    public function convenios()
    {
        $comun = $this->comun();
        $nombres = collect($comun['convenios'])
            ->flatMap(fn (array $g) => array_column($g['convenios'], 'nombre'))
            ->take(6)
            ->implode(', ');
        $conPrecio = collect($comun['planes'])->first(fn (array $p) => $p['precio_convenio']);

        return $this->pagina('landing.convenios', 'landing.convenios', 'Convenios para estudiantes, empresas e instituciones',
            ($nombres ? "Convenios con {$nombres}." : 'Convenios del gimnasio.')
                . ($conPrecio ? " Con convenio, el plan {$conPrecio['nombre']} queda en " . $this->pesos($conPrecio['precio_convenio']) . '.' : ''),
            [], $comun);
    }

    /**
     * Arriendo del gimnasio por horas para universidades e institutos.
     *
     * Su propia página, con título y descripción para quien busca «arriendo
     * de gimnasio» en la ciudad: metido en Convenios no lo encontraba nadie.
     */
    public function arriendo()
    {
        $comun = $this->comun();
        $ciudad = $comun['web']['ciudad'];
        $gimnasio = $comun['gimnasio']['nombre'];
        $instituciones = $this->contenidos('institucion')
            ->map(fn (array $c) => ['nombre' => $c['titulo'], 'logo' => $c['imagen']])
            ->all();
        $nombres = collect($instituciones)->pluck('nombre')->take(4)->implode(', ');

        return $this->pagina('landing.arriendo', 'landing.arriendo',
            'Arriendo de gimnasio por horas para tus clases',
            "Arrienda horas en {$gimnasio}" . ($ciudad ? ", {$ciudad}" : '') . ': para universidades, clubes y entrenadores que dan sus clases con sala de máquinas, peso libre y cardio, junto a los socios.'
                . ($nombres ? " Ya entrenan aquí {$nombres}." : '') . ' Marca las horas y te respondemos.',
            [
                'instituciones' => $instituciones,
                'fotosDelArriendo' => $this->contenidos('arriendo')->filter(fn (array $c) => $c['imagen'])->values()->all(),
                // La ficha para Google: un servicio del gimnasio, en su ciudad.
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'name' => 'Arriendo de gimnasio por horas',
                    'serviceType' => 'Arriendo de gimnasio por horas para clases',
                    'audience' => ['@type' => 'Audience', 'audienceType' => 'Universidades, institutos, clubes deportivos y entrenadores'],
                    'provider' => ['@type' => 'ExerciseGym', '@id' => $comun['web']['id_gimnasio'], 'name' => $gimnasio, 'url' => route('landing')],
                    'areaServed' => $ciudad ?: null,
                    'url' => route('landing.arriendo'),
                ],
            ], $comun);
    }

    /**
     * Las clases del gimnasio (judo, lucha olímpica…): el calendario de la
     * semana, una tarjeta por clase y el WhatsApp para inscribirse.
     *
     * Abiertas a cualquiera, socio o no, con mensualidad. Sin clases activas
     * la página no existe: un calendario vacío es peor que no tenerlo.
     */
    public function clases()
    {
        $filas = Clase::where('activo', true)->orderBy('orden')->orderBy('id')->get()
            ->filter(fn (Clase $c) => $c->horarioOrdenado() !== []);

        abort_if($filas->isEmpty(), 404);

        $comun = $this->comun();
        $ciudad = $comun['web']['ciudad'];
        $gimnasio = $comun['gimnasio']['nombre'];

        $clases = $filas->map(fn (Clase $c) => $this->fichaDeClase($c))->values();

        // «Judo y Lucha olímpica», o «Judo, Lucha olímpica y más»: el título
        // tiene que caber en el resultado de Google.
        $nombres = $clases->pluck('nombre');
        $enElTitulo = $nombres->count() <= 2
            ? $nombres->implode(' y ')
            : $nombres->take(2)->implode(', ') . ' y más';
        $todas = $nombres->count() > 1
            ? $nombres->slice(0, -1)->implode(', ') . ' y ' . $nombres->last()
            : $nombres->first();
        $precios = $clases->pluck('precio')->filter();
        $palabra = $nombres->count() === 1 ? 'Clase' : 'Clases';

        return $this->pagina('landing.clases', 'landing.clases',
            "{$palabra} de {$enElTitulo}",
            "{$palabra} de {$todas} en {$gimnasio}" . ($ciudad ? ", {$ciudad}" : '') . '. Abiertas a todos'
                . ($precios->isNotEmpty() ? ', mensualidad desde ' . $this->pesos($precios->min()) : '') . '.',
            [
                'clases' => $clases->all(),
                // Al compartir la página sale la foto de una clase, no la del gimnasio.
                'imagen_al_compartir' => $clases->pluck('imagen')->filter()->first(),
                'imagen_alt' => "{$palabra} de {$todas} en {$gimnasio}" . ($ciudad ? ", {$ciudad}" : ''),
                'tituloClases' => "{$palabra} de {$enElTitulo}" . ($ciudad ? " en {$ciudad}" : ''),
                'calendario' => $this->calendarioDeClases($clases->all()),
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'ItemList',
                    'name' => "Clases de {$gimnasio}",
                    'itemListElement' => $clases->values()->map(fn (array $c, int $i) => [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'item' => array_filter([
                            '@type' => 'Service',
                            'name' => $c['nombre'],
                            'description' => $c['descripcion'] ?: ($c['para_quien'] ?: null),
                            'provider' => ['@type' => 'ExerciseGym', '@id' => $comun['web']['id_gimnasio'], 'name' => $gimnasio, 'url' => route('landing')],
                            'areaServed' => $ciudad ?: null,
                            'offers' => $c['precio'] ? [
                                '@type' => 'Offer',
                                'price' => $c['precio'],
                                'priceCurrency' => 'CLP',
                                'priceSpecification' => [
                                    '@type' => 'UnitPriceSpecification',
                                    'price' => $c['precio'],
                                    'priceCurrency' => 'CLP',
                                    'unitText' => 'MONTH',
                                ],
                            ] : null,
                        ]),
                    ])->all(),
                ],
            ], $comun);
    }

    /**
     * La página de una clase: /clases/judo.
     *
     * Para quien busca «clases de judo en Los Ángeles»: en la página de todas
     * las clases, judo es una tarjeta entre cuatro; aquí es el título. Lleva
     * su foto, su horario, el precio, el WhatsApp para inscribirse y, abajo,
     * las otras clases.
     */
    public function clase(string $slug)
    {
        $activas = Clase::where('activo', true)->orderBy('orden')->orderBy('id')->get()
            ->filter(fn (Clase $c) => $c->horarioOrdenado() !== [])
            ->values();
        $clase = $activas->firstWhere('slug', $slug);

        if (! $clase) {
            // Una dirección vieja, de antes de corregirle el nombre: a la nueva.
            $actual = $activas->first(fn (Clase $c) => in_array($slug, $c->slugs_anteriores ?? [], true));

            abort_if(! $actual, 404);

            return redirect()->route('landing.clase', $actual->slug, 301);
        }

        $comun = $this->comun();
        $ciudad = $comun['web']['ciudad'];
        $gimnasio = $comun['gimnasio']['nombre'];
        $c = $this->fichaDeClase($clase);
        $titulo = "Clases de {$c['nombre']}" . ($ciudad ? " en {$ciudad}" : '');

        return $this->pagina('landing.clase', 'landing.clase', $titulo,
            // «Clases de Judo en PRO GYM, Los Ángeles: Lun y Mié · 19:00 a
            // 20:30. Niños desde 8 años. $25.000 al mes.»
            implode(' ', array_filter([
                "Clases de {$c['nombre']} en {$gimnasio}" . ($ciudad ? ", {$ciudad}" : '') . ": {$c['horario_texto']}.",
                $c['para_quien'] ? rtrim($c['para_quien'], '. ') . '.' : null,
                $c['precio_texto'] ? "{$c['precio_texto']} al mes." : null,
            ])),
            [
                'clase' => $c,
                'tituloClase' => $titulo,
                'otras' => $activas->where('id', '!=', $clase->id)
                    ->map(fn (Clase $o) => $this->fichaDeClase($o))
                    ->values()
                    ->all(),
                'imagen_al_compartir' => $c['imagen'],
                'imagen_alt' => "Clase de {$c['nombre']} en {$gimnasio}" . ($ciudad ? ", {$ciudad}" : ''),
                'json_ld' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'name' => "Clases de {$c['nombre']}",
                    'serviceType' => $c['nombre'],
                    'description' => $c['descripcion'] ?: ($c['para_quien'] ?: null),
                    'image' => $c['imagen'] ? url($c['imagen']) : null,
                    'url' => $c['url'],
                    'provider' => ['@type' => 'ExerciseGym', '@id' => $comun['web']['id_gimnasio'], 'name' => $gimnasio, 'url' => route('landing')],
                    'areaServed' => $ciudad ?: null,
                    'offers' => $c['precio'] ? [
                        '@type' => 'Offer',
                        'price' => $c['precio'],
                        'priceCurrency' => 'CLP',
                        'url' => $c['url'],
                        'priceSpecification' => [
                            '@type' => 'UnitPriceSpecification',
                            'price' => $c['precio'],
                            'priceCurrency' => 'CLP',
                            'unitText' => 'MONTH',
                        ],
                    ] : null,
                ]),
                'migas' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => $gimnasio, 'item' => route('landing')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Clases', 'item' => route('landing.clases')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $c['nombre'], 'item' => $c['url']],
                    ],
                ],
            ],
            $comun, ['slug' => $clase->slug]);
    }

    /**
     * Lo que se muestra de una clase, en la lista y en su página.
     *
     * @return array<string,mixed>
     */
    private function fichaDeClase(Clase $c): array
    {
        return [
            'nombre' => $c->nombre,
            'slug' => $c->slug,
            'url' => $c->slug ? route('landing.clase', $c->slug) : route('landing.clases'),
            'descripcion' => $c->descripcion,
            'profesor' => $c->profesor,
            'para_quien' => $c->para_quien,
            'precio' => $c->precio_mensual,
            'precio_texto' => $c->precio_mensual ? $this->pesos($c->precio_mensual) : null,
            'imagen' => $c->urlDeImagen(),
            'color' => $c->hex(),
            'horario' => $c->horarioOrdenado(),
            'horario_texto' => $c->horarioEnUnaLinea(),
            'whatsapp' => $this->whatsappConMensaje("Hola, quiero inscribirme en la clase de {$c->nombre}"),
        ];
    }

    /**
     * El calendario de la semana, ya armado para pintarlo.
     *
     *  · Lunes a sábado siempre; el domingo solo si alguna clase cae ese día.
     *  · De la primera hora con clases a la última: un calendario de 7 a 23
     *    con dos clases en la tarde es casi todo vacío.
     *  · Cada bloque sabe dónde va (arriba y alto en %) y en qué carril: dos
     *    clases a la misma hora van lado a lado, no una encima de la otra.
     *
     * @param  list<array<string,mixed>>  $clases
     * @return array{dias: array<string,string>, horas: list<int>, desde: int, hasta: int, bloques: array<string,list<array<string,mixed>>>, carriles: array<string,int>, porDia: array<string,list<array<string,mixed>>>}
     */
    private function calendarioDeClases(array $clases): array
    {
        $minutos = fn (string $hora) => (int) substr($hora, 0, 2) * 60 + (int) substr($hora, 3, 2);

        $todos = [];
        foreach ($clases as $c) {
            foreach ($c['horario'] as $b) {
                $todos[] = $b + ['nombre' => $c['nombre'], 'color' => $c['color'], 'url' => $c['url'] ?? null, 'inicio' => $minutos($b['desde']), 'fin' => $minutos($b['hasta'])];
            }
        }

        $hayDomingo = collect($todos)->contains('dia', 'domingo');
        $dias = collect(Clase::DIAS)->when(! $hayDomingo, fn ($d) => $d->except('domingo'))->all();

        $desde = intdiv(min(array_column($todos, 'inicio')), 60);
        $hasta = (int) ceil(max(array_column($todos, 'fin')) / 60);
        $total = max(60, ($hasta - $desde) * 60);

        $bloques = [];
        $carriles = [];
        foreach (array_keys($dias) as $dia) {
            $delDia = collect($todos)->where('dia', $dia)->sortBy('inicio')->values()->all();
            $finDeCarril = [];

            foreach ($delDia as &$b) {
                // El primer carril que ya quedó libre a esta hora.
                $carril = 0;
                while (isset($finDeCarril[$carril]) && $finDeCarril[$carril] > $b['inicio']) {
                    $carril++;
                }
                $finDeCarril[$carril] = $b['fin'];
                $b['carril'] = $carril;
                $b['arriba'] = round(($b['inicio'] - $desde * 60) / $total * 100, 3);
                $b['alto'] = round(($b['fin'] - $b['inicio']) / $total * 100, 3);
            }
            unset($b);

            $bloques[$dia] = $delDia;
            $carriles[$dia] = max(1, count($finDeCarril));
        }

        return [
            'dias' => $dias,
            'horas' => range($desde, $hasta - 1),
            'desde' => $desde,
            'hasta' => $hasta,
            'bloques' => $bloques,
            'carriles' => $carriles,
            // Para el celular: solo los días que tienen algo.
            'porDia' => array_filter($bloques),
        ];
    }

    public function especialistas()
    {
        $comun = $this->comun();

        // Sin especialistas la página no existe: una lista vacía no le sirve a
        // nadie, y Google la tomaría por una página pobre del sitio.
        abort_if($comun['especialistas'] === [], 404);

        $especialidades = collect($comun['especialistas'])->pluck('especialidad')->unique()->implode(', ');

        return $this->pagina('landing.especialistas', 'landing.especialistas', 'Especialistas',
            ($especialidades
                ? "{$especialidades} que trabajan con {$comun['gimnasio']['nombre']}."
                : "Los profesionales que trabajan con {$comun['gimnasio']['nombre']}.")
                . ' Escríbeles directo por WhatsApp o Instagram.',
            ['especialidades' => array_values(array_map(
                fn (array $g) => ['nombre' => $g['nombre'], 'url' => route('landing.especialidad', $g['slug'])],
                Especialidades::agrupar($comun['especialistas'])
            ))], $comun);
    }

    /**
     * Una página por especialidad: /especialidades/kinesiologo.
     *
     * Para quien busca «kinesiólogo en Los Ángeles»: junta a todos los que
     * hacen eso, aunque lo hayan escrito distinto (ver Especialidades). Sin
     * nadie, no existe.
     */
    public function especialidad(string $slug)
    {
        $comun = $this->comun();
        $grupos = Especialidades::agrupar($comun['especialistas']);
        $grupo = $grupos[$slug] ?? null;

        abort_if(! $grupo, 404);

        $ciudad = $comun['web']['ciudad'];
        $gimnasio = $comun['gimnasio']['nombre'];
        $titulo = $grupo['nombre'] . ($ciudad ? " en {$ciudad}" : '');
        $nombres = collect($grupo['especialistas'])->pluck('nombre');
        $url = route('landing.especialidad', $slug);

        return $this->pagina('landing.especialidad', 'landing.especialidad', $titulo,
            "{$grupo['nombre']} en {$gimnasio}" . ($ciudad ? ", {$ciudad}" : '') . ': '
                . ($nombres->count() > 1 ? $nombres->slice(0, -1)->implode(', ') . ' y ' . $nombres->last() : $nombres->first())
                . '. ' . ($nombres->count() > 1 ? 'Escríbeles' : 'Escríbele') . ' directo por WhatsApp.',
            [
                'grupo' => $grupo,
                'tituloEspecialidad' => $titulo,
                'otrasEspecialidades' => array_values(array_map(
                    fn (array $g) => ['nombre' => $g['nombre'], 'url' => route('landing.especialidad', $g['slug'])],
                    array_filter($grupos, fn (array $g) => $g['slug'] !== $slug)
                )),
                'migas' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => $gimnasio, 'item' => route('landing')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Especialistas', 'item' => route('landing.especialistas')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $grupo['nombre'], 'item' => $url],
                    ],
                ],
            ],
            $comun, ['slug' => $slug]);
    }

    /**
     * El perfil de un especialista: su foto grande, su presentación, en qué
     * se enfoca, cómo atiende y cómo escribirle.
     *
     * En la lista iba todo encima de la foto y la tapaba; allá queda lo justo
     * para elegir, y aquí lo necesario para decidirse a escribirle.
     */
    public function especialista(string $slug)
    {
        $comun = $this->comun();
        $especialista = collect($comun['especialistas'])->firstWhere('slug', $slug);

        if (! $especialista) {
            // Una dirección vieja, de antes de corregirle el nombre: a la nueva.
            $actual = Especialista::where('activo', true)->where('tipo', 'especialista')->get()
                ->first(fn (Especialista $e) => in_array($slug, $e->slugs_anteriores ?? [], true));

            abort_if(! $actual, 404);

            return redirect()->route('landing.especialista', $actual->slug, 301);
        }

        $otros = collect($comun['especialistas'])->where('slug', '!=', $slug)->take(3)->values()->all();
        $nombreGimnasio = $comun['gimnasio']['nombre'];
        $ciudad = $comun['web']['ciudad'];
        $perfil = route('landing.especialista', $slug);

        // «Camila Rojas, nutricionista en Los Ángeles»: lo que alguien escribe
        // en Google. pagina() ve la ciudad en el título y no la repite.
        $titulo = "{$especialista['nombre']}, {$especialista['especialidad']}" . ($ciudad ? " en {$ciudad}" : '');

        return $this->pagina('landing.especialista', 'landing.especialista',
            $titulo,
            $especialista['descripcion']
                ? Str::limit(preg_replace('/\s+/', ' ', $especialista['descripcion']), 155)
                : "{$especialista['nombre']}, {$especialista['especialidad']} en {$nombreGimnasio}" . ($ciudad ? ", {$ciudad}" : '') . '.'
                    . ($especialista['whatsapp'] ? ' Agenda por WhatsApp.' : ' Escríbele directo.'),
            [
                'especialista' => $especialista,
                'otros' => $otros,
                // «Más Nutricionista en PRO GYM»: solo si hay alguien más ahí.
                'susEspecialidades' => array_values(array_map(
                    fn (array $g) => ['nombre' => $g['nombre'], 'url' => route('landing.especialidad', $g['slug'])],
                    array_filter(
                        Especialidades::agrupar($comun['especialistas']),
                        fn (array $g) => isset(Especialidades::de($especialista['especialidad'])[$g['slug']]) && count($g['especialistas']) > 1
                    )
                )),
                // Al compartir su perfil sale su foto, no la del gimnasio.
                'imagen_al_compartir' => $especialista['foto'],
                'imagen_alt' => "{$especialista['nombre']}, {$especialista['especialidad']}",
                // Quién es, para Google: una persona que trabaja en el gimnasio.
                'json_ld' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Person',
                    'name' => $especialista['nombre'],
                    'jobTitle' => $especialista['especialidad'] ?: null,
                    'image' => $especialista['foto'] ? url($especialista['foto']) : null,
                    'url' => $perfil,
                    'sameAs' => $especialista['instagram'] ? [$especialista['instagram']] : null,
                    'worksFor' => ['@type' => 'ExerciseGym', '@id' => $comun['web']['id_gimnasio'], 'name' => $nombreGimnasio],
                ]),
                'migas' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => $nombreGimnasio, 'item' => route('landing')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Especialistas', 'item' => route('landing.especialistas')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $especialista['nombre'], 'item' => $perfil],
                    ],
                ],
            ],
            $comun, ['slug' => $slug]);
    }

    public function paginaContacto()
    {
        $comun = $this->comun();

        // Sin preguntas frecuentes: la lista de acordeones se quito de la pagina
        // (2026-09-17), y la ficha FAQPage se fue con ella, porque Google no
        // acepta preguntas que no estan a la vista.
        return $this->pagina('landing.contacto', 'landing.contacto', 'Contacto y horario',
            "Dónde está {$comun['gimnasio']['nombre']}, cómo llegar y el horario. Escríbenos.",
            // La ficha del gimnasio también aquí: es la página de la dirección,
            // el teléfono y el horario, justo lo que esa ficha le dice a Google.
            ['json_ld' => $comun['web']['json_ld']], $comun);
    }

    public function miMembresia()
    {
        return $this->pagina('landing.mi-membresia', 'landing.membresia', 'Consulta tu membresía',
            'Revisa cuándo vence tu membresía y si tienes algo pendiente, con tu RUT y los últimos 4 dígitos de tu celular.',
            // Es una consulta para socios, no algo que alguien busque en
            // Google: fuera del índice y fuera del mapa del sitio.
            ['robots' => 'noindex, follow'], $this->comun());
    }

    public function privacidad()
    {
        return $this->pagina('landing.privacidad', 'landing.privacidad', 'Privacidad y cookies',
            'Qué datos guarda el gimnasio, para qué, y cómo pedir que se corrijan o se borren.',
            ['legal' => \App\Support\TextosLegales::publicado('privacidad')], $this->comun());
    }

    /**
     * Los términos y condiciones. Los escribe el gimnasio en Configuración, y
     * son los mismos que acepta cada socio al firmar su contrato.
     */
    /**
     * Qué entrenar hoy: cuatro preguntas (cuántos días, cómo va, qué entrenó
     * estos últimos días y qué quiere hoy) y el día armado para eso. Las respuestas van en
     * la dirección, así se puede volver al resultado. Sin JavaScript es un
     * formulario común con las cuatro preguntas seguidas.
     *
     * LOS QR IMPRESOS llevan otras respuestas (?objetivo=…&nivel=…&dias=…):
     * con ellas se busca la rutina de siempre y se abre su página, para no
     * romperlos. Con solo ?objetivo=, la lista de ese objetivo.
     * No pide ni guarda nada de quien la mira.
     */
    public function rutina(Request $request)
    {
        $objetivo = (string) $request->query('objetivo', '');
        $nivel = (string) $request->query('nivel', '');
        $dias = (int) $request->query('dias', 0);
        // Lo de estos días: ?hice[]=… (varios) o el ?ayer=… de los enlaces viejos.
        $hechos = EntrenamientoDeHoy::hechos($request->query('hice'), $request->query('ayer'));

        if ($objetivo !== '') {
            if (RutinaSugerida::respondido($objetivo, $nivel, $dias) && ($rutina = RutinaSugerida::buscar($objetivo, $nivel, $dias))) {
                return redirect()->route('landing.rutina.ver', $rutina->slug);
            }

            return redirect()->route('landing.rutinas', isset(\App\Models\Rutina::OBJETIVOS[$objetivo]) ? ['objetivo' => $objetivo] : []);
        }

        if ($request->boolean('todas')) {
            return redirect()->route('landing.rutinas');
        }

        $comun = $this->comun();

        // ?cambiar=1: volver a la última pregunta con las otras ya marcadas.
        if (EntrenamientoDeHoy::respondido($dias, $nivel, $hechos) && ! $request->boolean('cambiar')) {
            $hoy = (string) $request->query('hoy', '');
            $hoy = isset(EntrenamientoDeHoy::GRUPOS[$hoy]) ? $hoy : EntrenamientoDeHoy::sugerencia($hechos, $dias);
            $respuestas = ['dias' => $dias, 'nivel' => $nivel, 'hice' => $hechos, 'hoy' => $hoy];

            return $this->pagina('landing.rutina-hoy', 'landing.rutina', 'Tu entrenamiento de hoy',
                'Un día de entrenamiento armado para lo que quieres entrenar hoy, con los ejercicios de la sala.',
                [
                    'entrenamiento' => EntrenamientoDeHoy::armar($dias, $nivel, $hechos, $hoy),
                    'respuestas' => $respuestas,
                    'nivelNombre' => EntrenamientoDeHoy::NIVELES[$nivel],
                    'robots' => 'noindex, follow',
                ],
                $comun);
        }

        // Lo que ya contestó (si volvió atrás o le faltó algo) queda marcado.
        $respuestas = [
            'dias' => in_array($dias, EntrenamientoDeHoy::DIAS, true) ? $dias : null,
            'nivel' => isset(EntrenamientoDeHoy::NIVELES[$nivel]) ? $nivel : null,
            'hice' => $hechos,
            'hoy' => isset(EntrenamientoDeHoy::GRUPOS[(string) $request->query('hoy')]) ? (string) $request->query('hoy') : null,
        ];

        return $this->pagina('landing.rutina', 'landing.rutina', 'Qué entrenar hoy',
            'Contesta cuatro preguntas y te armamos el entrenamiento de hoy con los ejercicios de la sala: series, repeticiones y descanso.',
            [
                'respuestas' => $respuestas,
                'reglas' => EntrenamientoDeHoy::reglas(),
                'robots' => $request->query() !== [] ? 'noindex, follow' : null,
            ],
            $comun);
    }

    /**
     * Todas las rutinas de la sala, por objetivo, cada una hacia su página.
     * Con ?objetivo=, solo las de ese objetivo.
     */
    public function rutinas(Request $request)
    {
        $objetivo = (string) $request->query('objetivo', '');
        $filtro = isset(\App\Models\Rutina::OBJETIVOS[$objetivo]) ? $objetivo : null;

        return $this->pagina('landing.rutinas', 'landing.rutinas', 'Rutinas de gimnasio',
            'Rutinas para empezar, bajar de peso, ganar fuerza o mantenerse, de 2 a 6 días, con los ejercicios, series y repeticiones de cada día.',
            [
                'objetivos' => \App\Models\Rutina::OBJETIVOS,
                'filtro' => $filtro,
                'grupos' => RutinaSugerida::porObjetivo($filtro),
                // Filtrada es la misma lista: para Google cuenta la entera.
                'robots' => $request->query() !== [] ? 'noindex, follow' : null,
            ],
            $this->comun());
    }

    /**
     * Una rutina en su página: para quién es, cómo avanzar y cada día con sus
     * ejercicios en orden, cada uno con su foto o su mapa muscular.
     */
    public function rutinaVer(string $slug)
    {
        $rutina = \App\Models\Rutina::where('activa', true)->where('slug', $slug)
            ->with(['dias.ejercicios.ejercicio', 'dias.ejercicios.alternativa'])->first();

        if (! $rutina) {
            // Una dirección vieja, de antes de corregirle el nombre: a la nueva.
            $actual = \App\Models\Rutina::where('activa', true)->get()
                ->first(fn ($r) => in_array($slug, $r->slugs_anteriores ?? [], true));

            abort_if(! $actual, 404);

            return redirect()->route('landing.rutina.ver', $actual->slug, 301);
        }

        $comun = $this->comun();
        $objetivo = \App\Models\Rutina::OBJETIVOS[$rutina->objetivo] ?? $rutina->objetivo;
        $nivel = \App\Models\Rutina::NIVELES[$rutina->nivel] ?? $rutina->nivel;
        // «Primeros pasos · 2 días» → «Primeros pasos»: los días van aparte.
        $nombre = trim(preg_replace('/\s*·\s*\d+\s*d[ií]as?\s*$/u', '', $rutina->nombre)) ?: $rutina->nombre;

        return $this->pagina('landing.rutina-ver', 'landing.rutina.ver',
            "Rutina {$nombre}: {$rutina->dias_por_semana} días",
            \Illuminate\Support\Str::limit("Rutina de {$rutina->dias_por_semana} días para {$objetivo}. {$nivel}. " . ($rutina->descripcion ?? ''), 155),
            [
                'rutina' => $rutina,
                'nombre' => $nombre,
                'objetivo' => $objetivo,
                'nivel' => $nivel,
                'dias' => RutinaSugerida::dias($rutina),
                'progresar' => RutinaSugerida::comoProgresar($rutina),
                'variantes' => RutinaSugerida::variantes($rutina),
                'migas' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => $comun['gimnasio']['nombre'], 'item' => route('landing')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Rutinas', 'item' => route('landing.rutinas')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $rutina->nombre, 'item' => route('landing.rutina.ver', $rutina->slug)],
                    ],
                ],
            ],
            $comun, ['slug' => $rutina->slug]);
    }

    /**
     * Los ejercicios de la sala, por grupo muscular, cada uno con su foto o
     * su mapa muscular: para ver qué se puede hacer en el gimnasio.
     */
    public function ejercicios()
    {
        $comun = $this->comun();
        $ciudad = $comun['web']['ciudad'] ?? null;

        return $this->pagina('landing.ejercicios', 'landing.ejercicios',
            $ciudad ? "Ejercicios del gimnasio en {$ciudad}" : 'Ejercicios del gimnasio',
            "Las máquinas y ejercicios de {$comun['gimnasio']['nombre']}" . ($ciudad ? " en {$ciudad}" : '')
                . ': pecho, espalda, piernas, hombros, brazos, abdomen y cardio, con cómo se hace cada uno.',
            [
                'grupos' => RutinaSugerida::ejerciciosPorGrupo(),
                'migas' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => $comun['gimnasio']['nombre'], 'item' => route('landing')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Ejercicios', 'item' => route('landing.ejercicios')],
                    ],
                ],
            ],
            $comun);
    }

    public function terminos()
    {
        return $this->pagina('landing.terminos', 'landing.terminos', 'Términos y condiciones',
            'Las reglas del gimnasio: membresías, pagos, pausas, devoluciones y uso de las instalaciones.',
            ['legal' => \App\Support\TextosLegales::publicado('terminos')], $this->comun());
    }

    /**
     * Lo que usan todas las páginas: el gimnasio, los planes, el menú, el
     * aviso, el horario y el WhatsApp flotante.
     *
     * @return array<string,mixed>
     */
    private function comun(): array
    {
        $gimnasio = [
            'nombre' => Ajustes::obtener('gimnasio.nombre') ?: 'PRO GYM',
            'direccion' => Ajustes::obtener('gimnasio.direccion'),
            'telefono' => Ajustes::obtener('gimnasio.telefono'),
            'email' => Ajustes::obtener('gimnasio.email'),
        ];

        $planes = $this->planesALaVenta();
        $convenios = $this->conveniosEnLaWeb();
        $especialistas = $this->especialistasEnLaWeb($gimnasio['nombre']);
        // Solo si hay alguna: sin clases no hay enlace en el menú ni en el pie.
        $hayClases = Clase::where('activo', true)->exists();
        $web = $this->datosParaGoogle($gimnasio, $planes);

        return [
            'gimnasio' => $gimnasio,
            'planes' => $planes,
            'convenios' => $convenios,
            'especialistas' => $especialistas,
            'web' => $web,
            // Las redes que tienen enlace, para el pie y para Contacto.
            'redes' => array_values(array_filter([
                $web['instagram'] ? ['nombre' => 'Instagram', 'url' => $web['instagram'], 'icono' => 'instagram'] : null,
                $web['facebook'] ? ['nombre' => 'Facebook', 'url' => $web['facebook'], 'icono' => 'facebook-f'] : null,
                $web['tiktok'] ? ['nombre' => 'TikTok', 'url' => $web['tiktok'], 'icono' => 'tiktok'] : null,
                $web['youtube'] ? ['nombre' => 'YouTube', 'url' => $web['youtube'], 'icono' => 'youtube'] : null,
            ])),
            // El menú solo enlaza lo que tiene algo que mostrar.
            'navegacion' => ['convenios' => $convenios !== [], 'especialistas' => $especialistas !== [], 'clases' => $hayClases],
            'tienda' => $this->tiendaDeSuplementos(),
            'aviso' => $this->avisoVigente(),
            'horario' => $this->horario(),
            'whatsapp' => $this->whatsappDelGimnasio($gimnasio['nombre']),
        ];
    }

    /**
     * Arma una página con su título, su descripción y su dirección canónica.
     *
     * @param array<string,mixed> $datos
     * @param array<string,mixed> $comun
     */
    private function pagina(string $vista, string $ruta, ?string $titulo, ?string $descripcion, array $datos, array $comun, array $parametros = [])
    {
        $web = $comun['web'];

        if ($titulo !== null) {
            // La ciudad una sola vez: si el título ya la dice, no se repite.
            $conCiudad = $web['ciudad'] && ! str_contains($titulo, $web['ciudad']);
            $web['titulo'] = "{$titulo} | {$comun['gimnasio']['nombre']}" . ($conCiudad ? " {$web['ciudad']}" : '');
        }

        if ($descripcion !== null) {
            $web['descripcion'] = $descripcion;
        }

        $web['canonical'] = route($ruta, $parametros);
        $web['json_ld'] = $datos['json_ld'] ?? null;
        $web['robots'] = $datos['robots'] ?? null;
        $migas = $datos['migas'] ?? null;

        // La imagen al compartir: la de la página si tiene una propia (la
        // foto del especialista, la de una clase), si no la del gimnasio.
        if (! empty($datos['imagen_al_compartir'])) {
            $web['imagen'] = url($datos['imagen_al_compartir']);
            $web['imagen_alt'] = $datos['imagen_alt'] ?? null;
        }
        $web['imagen_medidas'] = MedidasDeImagen::de($web['imagen']);

        unset($datos['json_ld'], $datos['migas'], $datos['robots'], $datos['imagen_al_compartir'], $datos['imagen_alt']);

        // Las migas: Google las muestra en vez de la dirección —«PRO GYM ›
        // Planes y precios»— y dicen de qué parte del sitio es cada página.
        $web['migas'] = isset(self::MIGAS[$ruta]) ? [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => $comun['gimnasio']['nombre'], 'item' => route('landing')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => self::MIGAS[$ruta], 'item' => route($ruta)],
            ],
        ] : $migas;

        return view($vista, ['web' => $web] + $datos + $comun);
    }

    /** Lo que se escribió en Página web para un tipo, en su orden. */
    private function contenidos(string $tipo): \Illuminate\Support\Collection
    {
        return ContenidoWeb::where('tipo', $tipo)
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(fn (ContenidoWeb $c) => [
                'titulo' => $c->titulo,
                'texto' => $c->texto,
                // Las tarjetas de servicio ya usaban «descripcion».
                'descripcion' => $c->texto,
                'icono' => $c->icono,
                'imagen' => $c->urlDeImagen(),
            ]);
    }

    /**
     * La tienda de suplementos, para el apartado que enlaza con ella.
     *
     * Es OTRO negocio con su propia web: aquí solo se enlaza. Sale de
     * Configuración → Página web, y sin dirección no hay apartado, porque
     * enlazar a ninguna parte es peor que no enlazar.
     *
     * La foto se lee de la carpeta de archivos subidos, como los vídeos de la
     * portada: si no hay, el apartado sale a una sola columna.
     *
     * @return array<string,?string>|null
     */
    private function tiendaDeSuplementos(): ?array
    {
        $url = trim((string) Ajustes::obtener('tienda.url'));

        if ($url === '') {
            return null;
        }

        $foto = $this->laMasLiviana('tienda');
        $icono = $this->laMasLiviana('tienda-icono');

        return [
            'url' => $url,
            'titulo' => trim((string) Ajustes::obtener('tienda.titulo')) ?: 'Suplementos',
            'texto' => trim((string) Ajustes::obtener('tienda.texto')),
            'imagen' => $foto ? asset('storage/web/' . basename($foto)) : null,
            'medidas' => $foto ? MedidasDeImagen::de(asset('storage/web/' . basename($foto))) : null,
            // La marca sola —la columna y el laurel— y el logotipo entero. El
            // botón flotante usa el logotipo donde cabe, y la marca en el móvil.
            'icono' => $icono ? asset('storage/web/' . basename($icono)) : null,
            'logo' => ($l = $this->laMasLiviana('tienda-logo'))
                ? asset('storage/web/' . basename($l))
                : null,
        ];
    }

    /**
     * La foto con ese nombre, prefiriendo la versión WebP si la hay.
     *
     * `web:aligerar-fotos` deja la liviana al lado de la original, y por orden
     * alfabético el .jpg salía primero: la web seguía mandando la pesada.
     */
    private function laMasLiviana(string $nombre): ?string
    {
        return collect(glob(storage_path("app/public/web/{$nombre}.*")) ?: [])
            ->sortBy(fn (string $ruta) => str_ends_with(strtolower($ruta), '.webp') ? 0 : 1)
            ->first();
    }

    /**
     * El fondo de la portada: los vídeos y las fotos, uno detrás de otro.
     *
     * Van INTERCALADOS a propósito —vídeo, foto, vídeo, foto— y la portada los
     * pasa en bucle. Un vídeo detrás de otro no deja mirar nada, y las fotos
     * solas no enseñan el gimnasio en marcha.
     *
     * Los vídeos se leen de la carpeta de archivos subidos, por nombre: los que
     * haya. Sin vídeos queda el pase de fotos, y sin nada de nada la portada cae
     * al degradado, así que nunca se ve rota.
     *
     * @return list<array{tipo:string, src:string}>
     */
    private function fondoDePortada(): array
    {
        /*
         * SOLO LAS FOTOS APAISADAS. La portada es ancha y baja; una foto vertical
         * (la mayoría de las del gimnasio: 1600 x 2000) tiene que agrandarse más
         * del doble para taparla, y en la rotación se veía una franja borrosa y
         * enorme entre fotos que sí calzaban. Las verticales siguen en la galería
         * de «El gimnasio», que las muestra enteras. Si no hay ninguna apaisada,
         * van todas: mejor una portada que una portada vacía.
         */
        $todas = $this->contenidos('foto')->pluck('imagen')->filter()->values();
        $apaisadas = $todas->filter(function (string $src) {
            $ruta = storage_path('app/public/web/' . basename(parse_url($src, PHP_URL_PATH) ?? ''));
            $medidas = is_file($ruta) ? @getimagesize($ruta) : false;

            return $medidas && $medidas[0] >= $medidas[1];
        })->values();
        $fotos = ($apaisadas->isNotEmpty() ? $apaisadas : $todas)->all();

        $videos = collect(glob(storage_path('app/public/web/portada*.mp4')) ?: [])
            ->map(fn (string $ruta) => asset('storage/web/' . basename($ruta)))
            ->values()
            ->all();

        if ($videos === []) {
            return array_map(fn (string $src) => ['tipo' => 'foto', 'src' => $src], $fotos);
        }

        $escenas = [];

        // Se recorre lo más largo de los dos: si hay un vídeo y cuatro fotos, el
        // vídeo vuelve a salir entre foto y foto.
        for ($i = 0; $i < max(count($videos), count($fotos)); $i++) {
            $escenas[] = ['tipo' => 'video', 'src' => $videos[$i % count($videos)]];

            if (isset($fotos[$i])) {
                $escenas[] = ['tipo' => 'foto', 'src' => $fotos[$i]];
            }
        }

        return $escenas;
    }

    /**
     * Las tarjetas de la portada que llevan a cada página.
     *
     * @param array<string,mixed> $comun
     * @return list<array<string,string>>
     */
    private function destacados(array $comun): array
    {
        // Sin los pases sueltos: el de un día no es un plan, y ponerlo el
        // primero hacía que la portada anunciara «desde $5.000».
        $mensualidades = $this->mensualidades($comun['planes']);
        $precios = array_column($mensualidades, 'precio');
        $conPrecio = collect($mensualidades)->first(fn (array $p) => $p['precio_convenio']);

        $destacados = [[
            'href' => route('landing.planes'),
            'icono' => 'tags',
            'titulo' => 'Planes',
            'texto' => $precios
                ? 'Desde ' . $this->pesos(min($precios)) . ': ' . mb_strtolower(implode(', ', array_column($mensualidades, 'nombre'))) . '.'
                : 'Pregunta por los planes en el mesón.',
            'accion' => 'Ver planes',
        ]];

        if ($comun['navegacion']['clases']) {
            $nombres = \App\Models\Clase::where('activo', true)->orderBy('orden')->limit(3)->pluck('nombre')->all();
            $destacados[] = [
                'href' => route('landing.clases'),
                'icono' => 'fist-raised',
                'titulo' => 'Clases',
                'texto' => implode(', ', $nombres) . '. Abiertas a todos.',
                'accion' => 'Ver horarios',
            ];
        }

        if ($comun['navegacion']['especialistas']) {
            $destacados[] = [
                'href' => route('landing.especialistas'),
                'icono' => 'user-friends',
                'titulo' => 'Especialistas',
                'texto' => collect($comun['especialistas'])->pluck('especialidad')->unique()->take(3)->implode(', ') . '.',
                'accion' => 'Conócelos',
            ];
        }

        if ($comun['navegacion']['convenios']) {
            $destacados[] = [
                'href' => route('landing.convenios'),
                'icono' => 'graduation-cap',
                'titulo' => 'Convenios',
                'texto' => $conPrecio
                    ? "Plan {$conPrecio['nombre']} a " . $this->pesos($conPrecio['precio_convenio']) . ' para estudiantes e instituciones con convenio.'
                    : 'Precios especiales para estudiantes, empresas e instituciones.',
                'accion' => 'Ver convenios',
            ];
        }

        $destacados[] = [
            'href' => route('landing.membresia'),
            'icono' => 'id-card',
            'titulo' => 'Mi membresía',
            'texto' => 'Revisa cuándo vence y si tienes algo pendiente.',
            'accion' => 'Consultar',
        ];

        return $destacados;
    }

    private function pesos(int $monto): string
    {
        return '$' . number_format($monto, 0, ',', '.');
    }

    /**
     * El horario de la semana, día por día, desde Configuración -> Horario.
     *
     * @return array{dias: list<array<string,mixed>>, configurado: bool, nota: ?string, hoy: string}
     */
    private function horario(): array
    {
        $dias = [];

        foreach (self::DIAS as $clave => [$nombre, $ingles]) {
            $tramos = [];

            foreach (array_filter(array_map('trim', explode(',', (string) Ajustes::obtener("horario.{$clave}")))) as $tramo) {
                $partes = array_map('trim', explode('-', $tramo));

                if (count($partes) === 2) {
                    // «7:00» se lee y se entrega como «07:00».
                    $tramos[] = array_map(
                        fn (string $hora) => vsprintf('%02d:%02d', array_map('intval', array_pad(explode(':', $hora), 2, 0))),
                        $partes
                    );
                }
            }

            $dias[] = ['clave' => $clave, 'nombre' => $nombre, 'ingles' => $ingles, 'tramos' => $tramos];
        }

        return [
            'dias' => $dias,
            'configurado' => collect($dias)->contains(fn (array $d) => $d['tramos'] !== []),
            'nota' => Ajustes::obtener('horario.nota') ?: null,
            'hoy' => array_keys(self::DIAS)[now()->dayOfWeekIso - 1],
        ];
    }

    /** El horario en el formato que lee Google, para su ficha del gimnasio. */
    private function horarioParaGoogle(): array
    {
        $especificacion = [];

        foreach ($this->horario()['dias'] as $dia) {
            foreach ($dia['tramos'] as [$abre, $cierra]) {
                $especificacion[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => 'https://schema.org/' . $dia['ingles'],
                    'opens' => $abre,
                    'closes' => $cierra,
                ];
            }
        }

        return $especificacion;
    }

    /**
     * El aviso destacado, si hoy está dentro de sus fechas.
     *
     * Se va solo al pasar la fecha de término: un «cerramos el sábado» que
     * sigue ahí el lunes es peor que no avisar.
     */
    private function avisoVigente(): ?string
    {
        $texto = trim((string) Ajustes::obtener('portada.aviso'));

        if ($texto === '') {
            return null;
        }

        $hoy = today()->toDateString();
        $desde = Ajustes::obtener('portada.aviso_desde');
        $hasta = Ajustes::obtener('portada.aviso_hasta');

        if (($desde && $hoy < $desde) || ($hasta && $hoy > $hasta)) {
            return null;
        }

        return $texto;
    }

    /** El enlace del WhatsApp flotante, con un saludo ya escrito. */
    private function whatsappDelGimnasio(string $gimnasio): ?string
    {
        return $this->whatsappConMensaje("Hola, quiero información sobre {$gimnasio}.");
    }

    /**
     * El mismo WhatsApp del gimnasio, con otro saludo ya escrito: el de
     * inscribirse en una clase llega diciendo cuál.
     */
    private function whatsappConMensaje(string $mensaje): ?string
    {
        $numero = preg_replace('/[^0-9]/', '', (string) Ajustes::obtener('web.whatsapp'));

        if (strlen($numero) === 9) {
            $numero = '56' . $numero;
        }

        return preg_match('/^569[0-9]{8}$/', $numero)
            ? 'https://wa.me/' . $numero . '?text=' . rawurlencode($mensaje)
            : null;
    }

    /**
     * Los planes que se pueden comprar hoy, del mas barato al mas caro.
     *
     * Solo los activos y con precio vigente: un plan sin precio no se puede
     * vender, y ensenarlo seria prometer algo que el meson no puede cobrar.
     */
    private function planesALaVenta(): array
    {
        // «El mas elegido» se CUENTA, no se decide: el plan con mas membresias
        // activas. Sin datos, ninguno lleva la marca.
        $masElegido = Inscripcion::where('id_estado', 100)
            ->selectRaw('id_membresia, count(*) as total')
            ->groupBy('id_membresia')
            ->orderByDesc('total')
            ->value('id_membresia');

        // Solo lo que se anuncia: hay planes que se venden en el mesón a un
        // precio arreglado con cada persona, y en la web serían una promesa.
        return Membresia::where('activo', true)
            ->where('en_la_web', true)
            ->with(['precios' => fn ($q) => $q->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now())
                ->orderByDesc('fecha_vigencia_desde')])
            ->get()
            ->filter(fn (Membresia $m) => $m->precios->isNotEmpty())
            ->map(function (Membresia $m) use ($masElegido) {
                $precio = $m->precios->first();

                return [
                    'nombre' => $m->nombre,
                    'descripcion' => $m->descripcion,
                    'duracion' => $this->duracion($m),
                    // Para sacar cuanto sale al mes y cuanto se ahorra frente al mensual.
                    'meses' => (int) $m->duracion_meses,
                    // UN PASE SUELTO NO ES UNA MENSUALIDAD. Dura días, no meses,
                    // y no se compara con los planes: puesto en la misma fila, el
                    // precio más barato de la web pasaba a ser el del pase de un
                    // día, y «desde $5.000» daba a entender que eso es lo que
                    // cuesta ser socio.
                    'es_pase' => (int) $m->duracion_meses < 1,
                    'precio' => (int) $precio->precio_normal,
                    'precio_convenio' => $precio->precio_convenio ? (int) $precio->precio_convenio : null,
                    'destacado' => $masElegido !== null && $m->id === (int) $masElegido,
                ];
            })
            ->sortBy('precio')
            ->values()
            ->all();
    }

    /**
     * Los planes que son membresía, sin los pases sueltos.
     *
     * El criterio se escribe UNA vez: si cada pantalla decidiera por su cuenta
     * qué cuenta como plan, la portada diría un precio y la página de planes
     * otro.
     *
     * @param list<array<string,mixed>> $planes
     * @return list<array<string,mixed>>
     */
    private function mensualidades(array $planes): array
    {
        return array_values(array_filter($planes, fn (array $p) => ! $p['es_pase']));
    }

    /** «1 mes», «3 meses», «1 año», «1 día». */
    private function duracion(Membresia $m): string
    {
        $meses = (int) $m->duracion_meses;
        $dias = (int) $m->duracion_dias;

        return match (true) {
            $meses === 12 => '1 año',
            $meses === 1 => '1 mes',
            $meses > 1 => "{$meses} meses",
            $dias === 1 => '1 día',
            $dias > 1 => "{$dias} días",
            default => '',
        };
    }

    /**
     * Lo que lee Google: el titulo, la descripcion y la ficha estructurada.
     *
     * PARA SALIR EN «GIMNASIO EN LOS ANGELES» lo que mas pesa no esta aqui:
     * es la ficha del gimnasio en Google Maps (Perfil de Empresa) y que la
     * direccion y el telefono sean los mismos en todas partes. Esto hace que
     * la pagina diga lo mismo, con las palabras que la gente escribe, y en el
     * formato que Google entiende —schema.org ExerciseGym—, con los planes y
     * los precios de verdad.
     *
     * @param array<string,mixed> $gimnasio
     * @param list<array<string,mixed>> $planes
     * @return array<string,mixed>
     */
    private function datosParaGoogle(array $gimnasio, array $planes): array
    {
        $ciudad = trim((string) Ajustes::obtener('web.ciudad'));
        $region = trim((string) Ajustes::obtener('web.region'));
        $codigoPostal = trim((string) Ajustes::obtener('web.codigo_postal'));
        $maps = Ajustes::obtener('web.google_maps') ?: null;
        $instagram = Ajustes::obtener('web.instagram') ?: null;
        $facebook = Ajustes::obtener('web.facebook') ?: null;
        $tiktok = Ajustes::obtener('web.tiktok') ?: null;
        $youtube = Ajustes::obtener('web.youtube') ?: null;

        $precios = array_column($planes, 'precio');
        // El «desde» que lee Google tiene que ser una mensualidad. El rango de
        // precios sí los incluye todos, pases incluidos, porque es lo que de
        // verdad se vende en el mesón.
        $desdeMensual = array_column($this->mensualidades($planes), 'precio');
        $inicio = url('/');
        $logo = asset('images/progym-logo.png');
        // El nombre fijo de la ficha: las demás páginas (planes, clases, el
        // perfil de un especialista) apuntan a él en vez de repetirla.
        $idGimnasio = $inicio . '#gimnasio';

        // La ciudad y las comunas vecinas: quien vive en Nacimiento también
        // busca un gimnasio, y el de Los Ángeles le queda a veinte minutos.
        $comunas = array_values(array_unique(array_filter(array_map(
            'trim',
            explode(',', $ciudad . ',' . Ajustes::obtener('web.comunas'))
        ))));

        // «-37.46973, -72.35366», tal como la entrega Google Maps.
        $geo = null;
        if (preg_match('/^(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)$/', trim((string) Ajustes::obtener('web.coordenadas')), $m)) {
            $geo = ['@type' => 'GeoCoordinates', 'latitude' => (float) $m[1], 'longitude' => (float) $m[2]];
        }

        // Las fotos del gimnasio, con dirección completa: la primera es la que
        // sale al compartir la página por WhatsApp o Facebook.
        $fotos = $this->contenidos('foto')
            ->take(5)
            ->pluck('imagen')
            ->filter()
            ->map(fn (string $foto) => url($foto))
            ->values()
            ->all();

        $vacio = fn ($valor) => $valor !== null && $valor !== '' && $valor !== [];

        $ficha = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'ExerciseGym',
            '@id' => $idGimnasio,
            'name' => $gimnasio['nombre'],
            'slogan' => 'Profesionales del deporte',
            'url' => $inicio,
            'logo' => $logo,
            'image' => $fotos ?: [$logo],
            'telephone' => $gimnasio['telefono'] ?: null,
            'email' => $gimnasio['email'] ?: null,
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $gimnasio['direccion'] ?: null,
                'addressLocality' => $ciudad ?: null,
                'addressRegion' => $region ?: null,
                'postalCode' => $codigoPostal ?: null,
                'addressCountry' => 'CL',
            ], $vacio),
            'geo' => $geo,
            'areaServed' => array_map(fn (string $comuna) => ['@type' => 'City', 'name' => $comuna], $comunas),
            'hasMap' => $maps,
            'sameAs' => array_values(array_filter([$instagram, $facebook, $tiktok, $youtube])),
            'priceRange' => $precios ? $this->pesos(min($precios)) . ' - ' . $this->pesos(max($precios)) : null,
            'makesOffer' => array_map(fn (array $plan) => [
                '@type' => 'Offer',
                'name' => 'Plan ' . $plan['nombre'],
                'price' => $plan['precio'],
                'priceCurrency' => 'CLP',
            ], $planes),
            'openingHoursSpecification' => $this->horarioParaGoogle(),
        ], $vacio);

        return [
            'ciudad' => $ciudad,
            'region' => $region,
            // Los mismos que enseña la vista previa de Configuración.
            'titulo' => WebPublica::tituloDeInicio(),
            'descripcion' => WebPublica::descripcion($desdeMensual ? min($desdeMensual) : null),
            'canonical' => $inicio,
            'json_ld' => $ficha,
            'id_gimnasio' => $idGimnasio,
            // Las comunas vecinas, sin la ciudad: «Cerca de: Nacimiento, Mulchén».
            'comunas' => array_values(array_filter($comunas, fn (string $c) => $c !== $ciudad)),
            'imagen' => $fotos[0] ?? $logo,
            'google_analytics' => Ajustes::obtener('web.google_analytics') ?: null,
            'search_console' => Ajustes::obtener('web.search_console') ?: null,
            'bing' => Ajustes::obtener('web.bing') ?: null,
            'google_maps' => $maps,
            'resenas' => Ajustes::obtener('web.resenas') ?: null,
            'instagram' => $instagram,
            'facebook' => $facebook,
            'tiktok' => $tiktok,
            'youtube' => $youtube,
        ];
    }

    /**
     * Los convenios que se muestran, agrupados por categoria.
     *
     * Solo los activos y MARCADOS para la web: el catalogo trae de ejemplo
     * instituciones con las que el gimnasio no tiene acuerdo, y hay convenios
     * que no se anuncian. Una categoria sin convenios no aparece.
     *
     * @return list<array{titulo:string, convenios:list<array<string,?string>>}>
     */
    private function conveniosEnLaWeb(): array
    {
        $porTipo = Convenio::where('activo', true)
            ->where('mostrar_en_web', true)
            ->orderBy('nombre')
            ->get()
            ->groupBy('tipo');

        $grupos = [];

        foreach (self::CATEGORIAS_DE_CONVENIO as $tipo => $titulo) {
            if (! isset($porTipo[$tipo])) {
                continue;
            }

            $grupos[] = [
                'titulo' => $titulo,
                'convenios' => $porTipo[$tipo]->map(fn (Convenio $c) => [
                    'nombre' => $c->nombre,
                    'logo' => $c->urlDeLogo(),
                    'requisito' => $c->requisito_web,
                ])->values()->all(),
            ];
        }

        return $grupos;
    }

    /**
     * Los especialistas que se muestran, en el orden que se les dio.
     *
     * @return list<array<string,?string>>
     */
    private function especialistasEnLaWeb(string $gimnasio): array
    {
        // Solo los especialistas: los embajadores comparten tabla pero salen en
        // la portada, no en esta página.
        return Especialista::where('activo', true)
            ->where('tipo', 'especialista')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Especialista $e) => [
                'nombre' => $e->nombre,
                'slug' => $e->slug,
                'perfil' => $e->slug ? route('landing.especialista', $e->slug) : null,
                'especialidad' => $e->especialidad,
                'descripcion' => $e->descripcion,
                'temas' => $e->temas ?? [],
                'modalidad' => Especialista::MODALIDADES[$e->modalidad] ?? null,
                'foto' => $e->urlDeFoto(),
                'whatsapp' => $e->enlaceWhatsapp($gimnasio),
                'instagram' => $e->enlaceInstagram(),
                'usuario' => $e->instagram,
                'email' => $e->email,
            ])
            ->all();
    }

    /**
     * Los embajadores, para la portada.
     *
     * Son socios que representan al gimnasio: se enseñan con su foto, su
     * disciplina y su Instagram, que es donde se les sigue. Sin ninguno cargado
     * la sección no sale.
     *
     * @return list<array<string,?string>>
     */
    private function embajadoresEnLaWeb(string $gimnasio): array
    {
        return Especialista::where('activo', true)
            ->where('tipo', 'embajador')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Especialista $e) => [
                'nombre' => $e->nombre,
                'disciplina' => $e->especialidad,
                'descripcion' => $e->descripcion,
                'foto' => $e->urlDeFoto(),
                'instagram' => $e->enlaceInstagram(),
                'usuario' => $e->instagram,
                'whatsapp' => $e->enlaceWhatsapp($gimnasio),
            ])
            ->all();
    }

    /**
     * Para los buscadores: todo se puede leer, y aqui esta el mapa del sitio.
     *
     * EL PANEL NO SE NOMBRA a proposito. robots.txt lo puede abrir cualquiera,
     * y escribir «Disallow: /login» seria senalarle a todo el mundo donde esta
     * la puerta. El panel ya queda fuera de Google por su cuenta: pide sesion,
     * y la pantalla de acceso lleva «noindex».
     */
    public function robots()
    {
        $texto = "User-agent: *\nAllow: /\n\nSitemap: " . route('landing.sitemap') . "\n";

        return response($texto, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * El mapa del sitio, con la fecha del último cambio DE CADA PÁGINA.
     *
     * Antes todas llevaban la del último cambio de precios, y Google aprende
     * a no creerle a un mapa cuyas fechas no dicen nada. Ahora cada página
     * lleva la de lo que muestra: los planes, la de los precios; las clases,
     * la de las clases; el contacto, la de los datos del gimnasio. Si no hay
     * fecha, no se inventa: la línea va sin ella.
     *
     * La consulta de membresía no va: es para socios y no se indexa.
     */
    public function sitemap()
    {
        $ajustes = fn (string ...$prefijos) => \Illuminate\Support\Facades\DB::table('ajustes')
            ->where(function ($q) use ($prefijos) {
                foreach ($prefijos as $prefijo) {
                    $q->orWhere('clave', 'like', $prefijo . '%');
                }
            })
            ->max('updated_at');
        $contenidos = fn (string ...$tipos) => ContenidoWeb::whereIn('tipo', $tipos)->max('updated_at');
        $precios = fn () => $this->laMasNueva(\App\Models\PrecioMembresia::max('updated_at'), Membresia::max('updated_at'));
        $especialistas = Especialista::where('activo', true)->where('tipo', 'especialista');

        $paginas = array_filter([
            ['landing', '1.0', $this->laMasNueva($ajustes('portada.'), $contenidos('foto', 'servicio', 'testimonio'), $precios())],
            ['landing.planes', '0.9', $precios()],
            $this->conveniosEnLaWeb() ? ['landing.convenios', '0.8', $this->laMasNueva(Convenio::max('updated_at'), $precios())] : null,
            ['landing.arriendo', '0.8', $contenidos('institucion', 'arriendo')],
            Clase::where('activo', true)->exists() ? ['landing.clases', '0.8', Clase::max('updated_at')] : null,
            ['landing.gimnasio', '0.8', $contenidos('foto', 'servicio')],
            (clone $especialistas)->exists() ? ['landing.especialistas', '0.7', (clone $especialistas)->max('updated_at')] : null,
            ['landing.contacto', '0.7', $ajustes('gimnasio.', 'horario.', 'web.')],
            // Qué entrenar hoy: solo la de sin respuestas (las demás son noindex).
            ['landing.rutina', '0.6', \App\Models\Rutina::max('updated_at')],
            ['landing.rutinas', '0.5', \App\Models\Rutina::max('updated_at')],
            ['landing.ejercicios', '0.5', \App\Models\Ejercicio::max('updated_at')],
            ['landing.privacidad', '0.2', \App\Models\TextoLegal::where('tipo', 'privacidad')->max('updated_at')],
            ['landing.terminos', '0.2', \App\Models\TextoLegal::where('tipo', 'terminos')->max('updated_at')],
        ]);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $linea = fn (string $direccion, $fecha, string $frecuencia, string $prioridad) => '  <url><loc>' . e($direccion) . '</loc>'
            . ($fecha ? '<lastmod>' . Carbon::parse($fecha)->toDateString() . '</lastmod>' : '')
            . '<changefreq>' . $frecuencia . '</changefreq><priority>' . $prioridad . '</priority></url>' . "\n";

        foreach ($paginas as [$ruta, $prioridad, $fecha]) {
            $xml .= $linea(route($ruta), $fecha, 'weekly', $prioridad);
        }

        // Cada clase en su página, con la fecha de su último cambio.
        Clase::where('activo', true)->whereNotNull('slug')->orderBy('orden')->orderBy('id')->get()
            ->filter(fn (Clase $c) => $c->horarioOrdenado() !== [])
            ->each(function (Clase $c) use (&$xml, $linea) {
                $xml .= $linea(route('landing.clase', $c->slug), $c->updated_at, 'monthly', '0.7');
            });

        // Cada rutina en su página.
        \App\Models\Rutina::where('activa', true)->whereNotNull('slug')->orderBy('orden')->get()
            ->each(function (\App\Models\Rutina $r) use (&$xml, $linea) {
                $xml .= $linea(route('landing.rutina.ver', $r->slug), $r->updated_at, 'monthly', '0.5');
            });

        // El perfil de cada especialista, con la fecha de su último cambio.
        $perfiles = (clone $especialistas)->whereNotNull('slug')->orderBy('orden')->get();
        $perfiles->each(function (Especialista $e) use (&$xml, $linea) {
            $xml .= $linea(route('landing.especialista', $e->slug), $e->updated_at, 'monthly', '0.6');
        });

        // Cada especialidad, con la fecha del último cambio de quienes la hacen.
        $porEspecialidad = Especialidades::agrupar($perfiles->map(fn (Especialista $e) => [
            'especialidad' => $e->especialidad,
            'cambio' => $e->updated_at,
        ])->all());
        foreach ($porEspecialidad as $g) {
            $xml .= $linea(route('landing.especialidad', $g['slug']), $this->laMasNueva(...array_column($g['especialistas'], 'cambio')), 'monthly', '0.6');
        }

        $xml .= '</urlset>' . "\n";

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /** La fecha más reciente de las que haya, o null si no hay ninguna. */
    private function laMasNueva(...$fechas): ?string
    {
        $fechas = array_filter($fechas);

        return $fechas ? (string) max(array_map(fn ($f) => Carbon::parse($f)->toDateTimeString(), $fechas)) : null;
    }

    /**
     * Procesar formulario de contacto con seguridad
     */
    public function contacto(Request $request)
    {
        // 1. Rate Limiting - Máximo 5 envíos por IP cada 10 minutos
        $key = 'contacto:' . \App\Support\IpDelCliente::paraLimitar($request);
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withInput()
                ->with('error', "Demasiados intentos. Por favor espera {$seconds} segundos.");
        }
        
        RateLimiter::hit($key, 600); // 10 minutos

        // 2. Honeypot - Campo oculto anti-bot
        if ($request->filled('website')) {
            // Bot detectado - simular éxito pero no procesar
            Log::warning('Honeypot triggered', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->with('success', '¡Mensaje enviado correctamente!');
        }

        // El formulario de arriendo para instituciones (pagina de Convenios)
        // entra por aqui mismo. Sus casillas propias se pliegan dentro del
        // mensaje: asi el correo, el registro y la validacion son los de
        // siempre, y no hay un segundo camino que mantener.
        $esArriendo = $request->servicio === 'arriendo' && $request->has('institucion');
        if ($esArriendo) {
            // El calendario manda una casilla por hora: «martes-10» es martes de
            // 10:00 a 11:00. Aqui se juntan las horas seguidas de cada dia para
            // que el correo diga «martes: 10:00 a 12:00» y no una lista de casillas.
            $porDia = [];
            foreach ((array) $request->bloques as $bloque) {
                if (preg_match('/^(lunes|martes|miércoles|jueves|viernes|sábado)-(\d{1,2})$/u', (string) $bloque, $m) && (int) $m[2] <= 23) {
                    $porDia[$m[1]][] = (int) $m[2];
                }
            }
            if (! $porDia) {
                return redirect()->to(url()->previous() . '#instituciones')
                    ->withErrors(['bloques' => 'Marca en el calendario al menos una hora.'])
                    ->withInput();
            }
            $horario = [];
            foreach (['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'] as $dia) {
                $horas = array_values(array_unique($porDia[$dia] ?? []));
                sort($horas);
                $tramos = [];
                foreach ($horas as $i => $h) {
                    if ($i === 0 || $h !== $horas[$i - 1] + 1) {
                        $tramos[] = [$h, $h + 1];
                    } else {
                        $tramos[count($tramos) - 1][1] = $h + 1;
                    }
                }
                if ($tramos) {
                    $horario[] = $dia . ': ' . implode(' y ', array_map(fn ($t) => sprintf('%02d:00 a %02d:00', $t[0], $t[1]), $tramos));
                }
            }

            $lineas = collect([
                'Quién' => $request->institucion,
                'Qué clases' => $request->area,
                'Personas por clase' => $request->alumnos,
            ])->map(fn ($v) => Str::limit(trim(strip_tags((string) $v)), 150, ''))
                ->filter()
                ->map(fn ($v, $k) => "{$k}: {$v}")
                ->implode("\n");
            $request->merge(['mensaje' => Str::limit(trim($lineas . "\nHorario que buscan:\n" . implode("\n", $horario)), 1000, '')]);
        }
        // Vuelve al apartado, no al principio de la pagina: si no, el aviso de
        // «enviado» queda abajo y parece que no paso nada.
        $volver = fn () => $esArriendo ? redirect()->to(url()->previous() . '#instituciones') : back();

        // 3. Validación estricta con sanitización
        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\-\']+$/u'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'mensaje' => ['required', 'string', 'min:10', 'max:1000'],
            'servicio' => ['nullable', 'string', 'in:' . implode(',', array_keys(self::INTERESES))],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex' => 'El nombre solo puede contener letras.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'telefono.regex' => 'El teléfono tiene un formato inválido.',
            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.min' => 'El mensaje debe tener al menos 10 caracteres.',
        ]);

        if ($validator->fails()) {
            return $volver()
                ->withErrors($validator)
                ->withInput();
        }

        // 4. Sanitizar datos
        $datos = [
            'nombre' => strip_tags(trim($request->nombre)),
            'email' => filter_var(trim($request->email), FILTER_SANITIZE_EMAIL),
            'telefono' => $request->telefono ? preg_replace('/[^\d\+\-\s]/', '', $request->telefono) : null,
            'mensaje' => strip_tags(trim($request->mensaje)),
            'servicio' => self::INTERESES[$request->servicio ?? 'informacion'] ?? self::INTERESES['informacion'],
            'ip' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent(), 255),
            'fecha' => now()->format('Y-m-d H:i:s'),
        ];

        // 5. Log del contacto (en producción: guardar en BD o enviar email)
        Log::channel('daily')->info('Nuevo contacto desde landing', $datos);

        /*
         * EL MENSAJE SE ENVIA DE VERDAD.
         *
         * Aqui habia un TODO con el envio comentado, asi que al visitante se le
         * decia «te responderemos pronto» y el mensaje no llegaba a nadie: moria
         * en el fichero de registro. Cada persona que escribia desde el sitio
         * publico se perdia.
         *
         * El registro de arriba se queda igual: es lo unico que guarda el
         * mensaje si el envio falla, porque no hay tabla de contactos.
         */
        // Primero el correo de Configuracion: es el que el gimnasio dice que lee.
        $destino = Ajustes::obtener('gimnasio.email') ?: config('correo.contacto') ?: config('mail.from.address');

        /*
         * TREINTA CORREOS DE CONTACTO AL DÍA, NO MÁS. Cada uno sale del mismo
         * cupo diario que los contratos y los avisos: desde varias direcciones
         * se podía gastar el cupo entero y ese día no salía nada más. Pasado el
         * tope, el mensaje queda en el registro de arriba y no se manda.
         */
        if (RateLimiter::tooManyAttempts('contacto:del-dia', 30)) {
            Log::warning('Contacto de la web sin mandar: se pasó el tope del día.', ['destino' => $destino]);

            return $volver()->with('success', '¡Gracias por contactarnos! Te responderemos pronto.');
        }

        RateLimiter::hit('contacto:del-dia', 86400);

        try {
            app(CorreoService::class)->enviar(
                $destino,
                "Contacto web · {$datos['nombre']}",
                view('emails.contacto', ['datos' => $datos])->render(),
            );
        } catch (\Throwable $e) {
            // Al visitante no se le dice que fallo: el hizo su parte y el
            // mensaje sigue en el registro para recuperarlo a mano.
            Log::error('No se pudo enviar el contacto de la web: ' . $e->getMessage(), [
                'destino' => $destino,
                'de' => $datos['email'],
            ]);
        }

        // 6. Respuesta exitosa
        return $volver()->with('success', '¡Gracias por contactarnos! Te responderemos pronto.');
    }

    /**
     * «Mi membresía»: el socio mira cómo está su membresía desde su casa.
     *
     * PIDE DOS DATOS, NO UNO. Con el RUT solo, cualquiera que lo supiera —y
     * un RUT sale en cualquier boleta— veía el nombre completo de la persona,
     * si era socia, su plan y cuándo pagó. Ahora se pide además lo que sabe el
     * socio y no un desconocido: los últimos 4 dígitos de su celular.
     *
     * RESPONDE LO JUSTO: el nombre de pila, el plan, el vencimiento y si debe
     * algo. Ni apellido ni historial de pagos: eso se ve en el mesón.
     *
     * «RUT que no es socio» y «RUT con los dígitos equivocados» responden
     * EXACTAMENTE lo mismo. Si respondieran distinto, la pregunta «¿esta
     * persona es socia?» se contestaría sin saber los dígitos.
     *
     * Los frenos van en capas: por IP —3 consultas cada 5 minutos y bloqueo
     * tras 5 fallos— y por RUT o celular —bloqueo tras 5 fallos, venga de
     * donde venga—, para que cambiar de conexión no sirva para probar dígitos.
     */
    public function consultarMembresia(Request $request)
    {
        $ip = \App\Support\IpDelCliente::paraLimitar($request);

        // 1. Trampa para bots: se les responde como a alguien que no existe.
        if ($request->filled('website') || $request->filled('url')) {
            Log::warning('Consulta membresía: Honeypot activado', ['ip' => $ip]);

            return $this->noEncontrada();
        }

        // 2. Frenos por IP.
        $keyBloqueo = 'consulta_bloqueado:' . $ip;
        $keyConsultas = 'consulta_membresia:' . $ip;

        if (RateLimiter::tooManyAttempts($keyBloqueo, 1)) {
            $minutos = (int) ceil(RateLimiter::availableIn($keyBloqueo) / 60);
            Log::warning('Consulta membresía: IP bloqueada intentando acceder', ['ip' => $ip]);

            return response()->json([
                'success' => false,
                'message' => "Tu acceso está temporalmente bloqueado. Intenta en {$minutos} minutos.",
                'blocked' => true,
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($keyConsultas, 3)) {
            $segundos = RateLimiter::availableIn($keyConsultas);
            Log::info('Consulta membresía: Rate limit alcanzado', ['ip' => $ip]);

            return response()->json([
                'success' => false,
                'message' => "Has realizado muchas consultas. Espera {$segundos} segundos.",
            ], 429);
        }

        RateLimiter::hit($keyConsultas, 300);

        /*
         * DOSCIENTOS FALLOS AL DÍA ENTRE TODOS. Los frenos por dirección y por
         * RUT no alcanzan contra quien recorre una lista de celulares desde
         * muchas conexiones. Pasado el tope, la consulta se hace en el mesón.
         */
        if (RateLimiter::tooManyAttempts('consulta_fallidos:del-dia', 200)) {
            return response()->json([
                'success' => false,
                'message' => 'Hoy la consulta en línea no está disponible. Pregunta en el mesón.',
                'blocked' => true,
            ], 429);
        }

        // 3. Quién es.
        [$cliente, $llave, $respuesta] = $request->input('tipo', 'rut') === 'celular'
            ? $this->buscarPorCelular($request, $ip)
            : $this->buscarPorRut($request, $ip);

        if ($respuesta) {
            return $respuesta;
        }

        if (! $cliente) {
            $this->contarFallo($ip, $llave);

            return $this->noEncontrada();
        }

        // 4. Encontrado: se olvidan los fallos de esta conexión y de esta llave.
        RateLimiter::clear('consulta_fallidos:' . $ip);
        RateLimiter::clear($llave);

        // Antes se registraba aquí «rut_parcial» con una variable que solo
        // existe en la búsqueda por RUT: la búsqueda por celular reventaba
        // con un error 500 justo al encontrar a la persona.
        Log::info('Consulta membresía: Exitosa', ['ip' => $ip, 'cliente_id' => $cliente->id]);

        return response()->json([
            'success' => true,
            'data' => $this->loQueSeMuestra($cliente),
        ]);
    }

    /**
     * Por RUT y los últimos 4 dígitos del celular.
     *
     * @return array{0: ?Cliente, 1: ?string, 2: ?\Illuminate\Http\JsonResponse}
     */
    private function buscarPorRut(Request $request, string $ip): array
    {
        $rutInput = preg_replace('/[^0-9kK.-]/', '', strip_tags(trim((string) $request->input('rut', ''))));
        $digitos = preg_replace('/[^0-9]/', '', (string) $request->input('digitos', ''));

        if (strlen($rutInput) < 7 || strlen($rutInput) > 12) {
            return [null, null, $this->invalido('Formato de RUT inválido.')];
        }

        if (strlen($digitos) !== 4) {
            return [null, null, $this->invalido('Ingresa los últimos 4 dígitos de tu celular.')];
        }

        $rutLimpio = strtoupper(preg_replace('/[^0-9kK]/', '', $rutInput));
        $llave = 'consulta_fallidos_llave:' . hash('sha256', 'rut:' . $rutLimpio);

        if ($bloqueada = $this->llaveBloqueada($llave)) {
            return [null, $llave, $bloqueada];
        }

        // Un RUT mal escrito se dice —el dígito verificador lo calcula
        // cualquiera, no revela nada—, pero cuenta como fallo: probar RUTs al
        // azar es justo lo que hace quien busca a alguien.
        if (! $this->validarRutChileno($rutLimpio)) {
            $this->contarFallo($ip, null);

            return [null, $llave, $this->invalido('El RUT ingresado no es válido.')];
        }

        $cliente = Cliente::where('activo', true)
            ->where(function ($q) use ($rutInput, $rutLimpio) {
                $q->where('run_pasaporte', $rutInput)
                    ->orWhereRaw("UPPER(REPLACE(REPLACE(REPLACE(run_pasaporte, '.', ''), '-', ''), ' ', '')) = ?", [$rutLimpio]);
            })
            ->first();

        // El mismo camino exista o no: así la respuesta no distingue «no es
        // socio» de «es socio pero los dígitos no son».
        $celular = $cliente ? preg_replace('/[^0-9]/', '', (string) $cliente->celular) : '';
        $coincide = $celular !== '' && hash_equals(substr($celular, -4), $digitos);

        return [$coincide ? $cliente : null, $llave, null];
    }

    /**
     * Por celular y primer nombre, para quien no tiene RUT.
     *
     * @return array{0: ?Cliente, 1: ?string, 2: ?\Illuminate\Http\JsonResponse}
     */
    private function buscarPorCelular(Request $request, string $ip): array
    {
        $celularInput = preg_replace('/[^0-9]/', '', (string) $request->input('celular', ''));
        $nombreInput = trim(strip_tags((string) $request->input('nombre', '')));

        if (strlen($celularInput) < 8 || strlen($celularInput) > 12) {
            return [null, null, $this->invalido('Formato de celular inválido.')];
        }

        if (mb_strlen($nombreInput) < 2 || mb_strlen($nombreInput) > 50) {
            return [null, null, $this->invalido('Nombre inválido.')];
        }

        $llave = 'consulta_fallidos_llave:' . hash('sha256', 'cel:' . substr($celularInput, -8));

        if ($bloqueada = $this->llaveBloqueada($llave)) {
            return [null, $llave, $bloqueada];
        }

        /*
         * El celular se guarda normalizado —nueve dígitos, sin +56—, así que se
         * compara tal cual. Antes se usaba RIGHT() de MySQL, que otras bases no
         * tienen.
         *
         * Y el nombre tiene que ser EL PRIMER NOMBRE, entero. Antes bastaba con
         * que las letras escritas estuvieran dentro del nombre: «an» abría la
         * ficha de cualquier Juan, Ana o Daniela con ese celular.
         */
        $primero = fn (string $texto) => explode(' ', trim($this->normalizarTexto($texto)))[0] ?? '';
        $buscado = $primero($nombreInput);

        $cliente = Cliente::where('activo', true)
            ->where(fn ($q) => $q
                ->where('celular', substr($celularInput, -9))
                ->orWhere('celular', 'like', '%' . substr($celularInput, -8)))
            ->get()
            ->first(fn (Cliente $c) => $buscado !== '' && $primero((string) $c->nombres) === $buscado);

        return [$cliente, $llave, null];
    }

    /** Un fallo cuenta para la conexión y, si la hay, para la llave (el RUT o el celular). */
    private function contarFallo(string $ip, ?string $llave): void
    {
        $keyFallidos = 'consulta_fallidos:' . $ip;

        RateLimiter::hit($keyFallidos, 900);
        RateLimiter::hit('consulta_fallidos:del-dia', 86400);

        if (RateLimiter::attempts($keyFallidos) >= 5) {
            RateLimiter::hit('consulta_bloqueado:' . $ip, 1800);
            Log::warning('Consulta membresía: IP bloqueada por muchos fallos', ['ip' => $ip]);
        }

        if ($llave) {
            RateLimiter::hit($llave, 1800);
        }
    }

    /** Cinco fallos para el mismo RUT o celular lo cierran 30 minutos, cambie o no la IP. */
    private function llaveBloqueada(string $llave): ?\Illuminate\Http\JsonResponse
    {
        if (! RateLimiter::tooManyAttempts($llave, 5)) {
            return null;
        }

        $minutos = (int) ceil(RateLimiter::availableIn($llave) / 60);

        return response()->json([
            'success' => false,
            'message' => "Por seguridad, esta consulta quedó bloqueada. Intenta en {$minutos} minutos o pregunta en el mesón.",
            'blocked' => true,
        ], 429);
    }

    private function invalido(string $mensaje): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => false, 'message' => $mensaje], 422);
    }

    /** Siempre la misma respuesta: no existe, no coincide o es un bot. */
    private function noEncontrada(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'No encontramos tu membresía. Revisa los datos e intenta de nuevo.',
        ], 404);
    }

    /**
     * Lo que ve el socio: su nombre de pila, su plan y si debe algo.
     *
     * @return array<string,mixed>
     */
    private function loQueSeMuestra(Cliente $cliente): array
    {
        // La activa, y si no hay, la pausada. Solo se miraba la activa: el
        // socio en pausa leía «Sin membresía activa» —y «Estás al día» aunque
        // debiera— como si se hubiera ido del gimnasio.
        $activa = $cliente->inscripciones()
            ->with('membresia')
            ->whereIn('id_estado', [100, 101])
            ->orderByRaw('id_estado = 100 desc')
            ->orderByDesc('fecha_vencimiento')
            ->first();

        $datos = [
            'nombre' => explode(' ', trim((string) $cliente->nombres))[0] ?: 'Socio',
            'membresia' => null,
            'estado' => 'Sin membresía activa',
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'dias_restantes' => null,
            'saldo' => 0,
        ];

        if (! $activa) {
            return $datos;
        }

        // En pausa: los días guardados, el día en que vuelve y hasta cuándo le
        // alcanzará. La fecha de vencimiento de una pausada es la vieja y ya
        // no dice nada.
        if ($activa->estaEnPausa()) {
            return [
                ...$datos,
                'membresia' => $activa->membresia?->nombre ?? 'Membresía',
                'estado' => $activa->fecha_pausa_fin
                    ? 'Pausada hasta el ' . $activa->fecha_pausa_fin->format('d/m/Y')
                    : 'Pausada',
                'fecha_inicio' => $activa->fecha_inicio?->format('d/m/Y'),
                'fecha_fin' => $activa->vencimientoAlReanudar()?->format('d/m/Y'),
                'dias_restantes' => $activa->dias_restantes,
                'saldo' => (int) $activa->obtenerEstadoPago()['pendiente'],
            ];
        }

        // Por fechas de calendario: contando horas, el domingo del cambio de
        // hora quedaba un día menos.
        $dias = $activa->fecha_vencimiento
            ? Inscripcion::diasEntre(today(), $activa->fecha_vencimiento)
            : null;

        return [
            ...$datos,
            'membresia' => $activa->membresia?->nombre ?? 'Membresía',
            'estado' => match (true) {
                $dias === null, $dias > 0 => 'Activa',
                $dias === 0 => 'Vence hoy',
                default => 'Vencida',
            },
            'fecha_inicio' => $activa->fecha_inicio?->format('d/m/Y'),
            'fecha_fin' => $activa->fecha_vencimiento?->format('d/m/Y'),
            'dias_restantes' => $dias === null ? null : max(0, $dias),
            'saldo' => (int) $activa->obtenerEstadoPago()['pendiente'],
        ];
    }

    /**
     * Validar RUT chileno con dígito verificador
     * Algoritmo Módulo 11
     * 
     * @param string $rut RUT sin puntos ni guión (ej: 12345678K)
     * @return bool
     */
    private function validarRutChileno(string $rut): bool
    {
        // Debe tener al menos 2 caracteres (1 dígito + DV)
        if (strlen($rut) < 2) {
            return false;
        }
        
        // Separar cuerpo y dígito verificador
        $dv = substr($rut, -1);
        $cuerpo = substr($rut, 0, -1);
        
        // Cuerpo debe ser numérico
        if (!ctype_digit($cuerpo)) {
            return false;
        }
        
        // Calcular dígito verificador esperado
        $suma = 0;
        $multiplicador = 2;
        
        // Recorrer de derecha a izquierda
        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += (int)$cuerpo[$i] * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }
        
        $resto = $suma % 11;
        $dvCalculado = 11 - $resto;
        
        // Convertir a caracter
        if ($dvCalculado === 11) {
            $dvCalculado = '0';
        } elseif ($dvCalculado === 10) {
            $dvCalculado = 'K';
        } else {
            $dvCalculado = (string)$dvCalculado;
        }
        
        // Comparar (case insensitive para K)
        return strtoupper($dv) === $dvCalculado;
    }

    /**
     * Normalizar texto para comparación
     * Quita tildes, convierte a minúsculas
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        
        // Quitar tildes
        $tildes = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 
                   'ü' => 'u', 'ñ' => 'n', 'Á' => 'a', 'É' => 'e', 'Í' => 'i', 
                   'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n'];
        
        return strtr($texto, $tildes);
    }
}
