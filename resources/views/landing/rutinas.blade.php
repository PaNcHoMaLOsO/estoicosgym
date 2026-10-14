@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])
{{-- Sin los botones flotantes: tapaban las opciones en el teléfono. --}}
@section('sin-flotantes', '1')

{{--
    LAS RUTINAS: primero tres preguntas, después la lista.

    Eran cincuenta filas seguidas y había que adivinar cuál era la de uno.
    Ahora arriba se contesta qué buscas, cuánto llevas y cuántos días puedes
    venir, y sale la tuya con lo que se hace cada día. Cada respuesta es un
    enlace común: va en la dirección, funciona sin JavaScript y el resultado
    se puede compartir. La lista entera sigue abajo, con cada rutina en una
    fila y un botón por cantidad de días.
--}}
@php
    $paso = match (true) {
        ! $filtro => 0,
        ! $nivel => 1,
        ! $dias => 2,
        default => 3,
    };
    $ir = fn (array $respuestas) => route('landing.rutinas', $respuestas) . '#elige';

    $queBuscas = [
        'empezar' => ['seedling', 'Aprender las máquinas y tomar el ritmo'],
        'bajar_grasa' => ['fire', 'Quemar grasa y tonificar'],
        'fuerza' => ['dumbbell', 'Subir pesos y ganar músculo'],
        'mantener' => ['heart-pulse', 'Sentirme bien y no perder lo ganado'],
    ];
    // Para quien está empezando, «entreno hace tiempo» no calza: ahí la
    // tercera opción es la de quien entrenaba y lo dejó.
    $cuantoLlevas = [
        'nunca' => ['seedling', $niveles['nunca'], 'Es mi primera vez en un gimnasio'],
        'algo' => ['dumbbell', $niveles['algo'], 'Algunos meses, ya conozco las máquinas'],
        'hace_tiempo' => $filtro === 'empezar'
            ? ['rotate-left', 'Entrenaba y lo dejé', 'Vuelvo después de un tiempo']
            : ['medal', $niveles['hace_tiempo'], 'Más de un año, con constancia'],
    ];

    $opcion = 'group flex items-center gap-4 rounded-xl bg-pg-grafito px-4 py-4 text-pg-tiza transition duration-200 hover:bg-pg-gris/70 active:scale-[0.98] sm:px-5 sm:py-5';
    $circulo = 'grid size-12 shrink-0 place-items-center rounded-full bg-pg-negro/60 text-xl text-pg-rojo-claro';
    $pregunta = 'font-display text-[1.7rem] uppercase leading-tight text-pg-tiza sm:text-4xl';
    $ficha = 'inline-flex items-center gap-1.5 rounded-full bg-pg-negro/50 px-3 py-1.5 font-modern text-xs text-pg-tiza/75 transition-colors hover:text-pg-tiza sm:text-sm';
@endphp

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Sala de máquinas',
        'titulo' => 'Rutinas de gimnasio',
        'bajada' => 'Contesta tres preguntas y te decimos cuál seguir, día a día, con las máquinas de la sala.',
        'compacta' => true,
    ])

    <section class="bg-pg-negro pb-14">
        <div class="mx-auto max-w-6xl px-4 sm:px-8">

            {{-- ===== Encuentra tu rutina ===== --}}
            <div id="elige" class="mx-auto max-w-3xl scroll-mt-24 rounded-2xl border border-pg-tiza/10 bg-pg-carbon/60 p-4 sm:p-8">
                <div class="flex gap-1.5" aria-hidden="true">
                    @for($i = 0; $i < 3; $i++)
                        <span class="h-1.5 flex-1 rounded-full {{ $i < $paso || ($i === $paso && $paso < 3) ? 'bg-pg-rojo' : 'bg-pg-tiza/15' }}"></span>
                    @endfor
                </div>

                {{-- Lo ya contestado: se toca para cambiarlo. --}}
                @if($paso > 0)
                    <p class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ $ir([]) }}" class="{{ $ficha }}" aria-label="Cambiar lo que buscas: {{ $objetivos[$filtro] }}">{{ $objetivos[$filtro] }} <x-icono nombre="xmark" class="text-[11px] text-pg-tiza/45" /></a>
                        @if($nivel)
                            <a href="{{ $ir(['objetivo' => $filtro]) }}" class="{{ $ficha }}" aria-label="Cambiar cuánto llevas: {{ $cuantoLlevas[$nivel][1] }}">{{ $cuantoLlevas[$nivel][1] }} <x-icono nombre="xmark" class="text-[11px] text-pg-tiza/45" /></a>
                        @endif
                        @if($dias)
                            <a href="{{ $ir(['objetivo' => $filtro, 'nivel' => $nivel]) }}" class="{{ $ficha }}" aria-label="Cambiar los días: {{ $dias }}">{{ $dias }} días <x-icono nombre="xmark" class="text-[11px] text-pg-tiza/45" /></a>
                        @endif
                    </p>
                @endif

                @if($paso === 0)
                    <p class="mt-5 font-modern text-xs uppercase tracking-widest text-pg-tiza/45">1 de 3</p>
                    <h2 class="{{ $pregunta }}">¿Qué buscas?</h2>
                    <div class="mt-5 grid gap-2.5 sm:grid-cols-2 sm:gap-3">
                        @foreach($objetivos as $clave => $etiqueta)
                            <a href="{{ $ir(['objetivo' => $clave]) }}" class="{{ $opcion }}">
                                <span class="{{ $circulo }}"><x-icono :nombre="$queBuscas[$clave][0] ?? 'dumbbell'" /></span>
                                <span class="min-w-0 flex-1 font-modern">
                                    <span class="block font-semibold sm:text-lg">{{ $etiqueta }}</span>
                                    <span class="block text-sm text-pg-tiza/55">{{ $queBuscas[$clave][1] ?? '' }}</span>
                                </span>
                                <x-icono nombre="arrow-right" class="text-sm text-pg-tiza/30 transition-transform group-hover:translate-x-0.5" />
                            </a>
                        @endforeach
                    </div>
                @elseif($paso === 1)
                    <p class="mt-5 font-modern text-xs uppercase tracking-widest text-pg-tiza/45">2 de 3</p>
                    <h2 class="{{ $pregunta }}">¿Cuánto llevas entrenando?</h2>
                    <div class="mt-5 grid gap-2.5 sm:gap-3">
                        @foreach($cuantoLlevas as $clave => [$icono, $etiqueta, $bajada])
                            <a href="{{ $ir(['objetivo' => $filtro, 'nivel' => $clave]) }}" class="{{ $opcion }}">
                                <span class="{{ $circulo }}"><x-icono :nombre="$icono" /></span>
                                <span class="min-w-0 flex-1 font-modern">
                                    <span class="block font-semibold sm:text-lg">{{ $etiqueta }}</span>
                                    <span class="block text-sm text-pg-tiza/55">{{ $bajada }}</span>
                                </span>
                                <x-icono nombre="arrow-right" class="text-sm text-pg-tiza/30 transition-transform group-hover:translate-x-0.5" />
                            </a>
                        @endforeach
                    </div>
                @elseif($paso === 2)
                    <p class="mt-5 font-modern text-xs uppercase tracking-widest text-pg-tiza/45">3 de 3</p>
                    <h2 class="{{ $pregunta }}">¿Cuántos días puedes venir a la semana?</h2>
                    <p class="mt-1 font-modern text-sm text-pg-tiza/60">Elige los que de verdad vas a cumplir: sirve más venir 3 siempre que 5 a veces.</p>
                    <div class="mt-5 flex gap-2 sm:gap-3">
                        @foreach($diasPosibles as $n)
                            <a href="{{ $ir(['objetivo' => $filtro, 'nivel' => $nivel, 'dias' => $n]) }}"
                               class="flex h-24 flex-1 flex-col items-center justify-center rounded-xl bg-pg-grafito text-pg-tiza transition duration-200 hover:bg-pg-gris/70 active:scale-[0.97] sm:h-32">
                                <span class="font-display text-4xl leading-none sm:text-6xl">{{ $n }}</span>
                                <span class="mt-1 font-modern text-xs text-pg-tiza/55 sm:text-sm">días</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    {{-- ===== La tuya ===== --}}
                    <p class="mt-5 font-modern text-xs uppercase tracking-widest text-pg-rojo-claro">
                        @if(! $paraTi['exacta'])
                            La más cercana a lo que buscas
                        @elseif(count($paraTi['rutinas']) > 1)
                            Hay {{ count($paraTi['rutinas']) }} que te sirven
                        @else
                            Tu rutina
                        @endif
                    </p>

                    <div class="mt-2 space-y-4">
                        @foreach($paraTi['rutinas'] as $r)
                            <article class="{{ $loop->first ? '' : 'border-t border-pg-tiza/10 pt-4' }}">
                                <h2 class="font-display text-3xl uppercase leading-tight text-pg-tiza sm:text-5xl">{{ $r['nombre'] }}</h2>
                                <p class="mt-1 font-modern text-sm text-pg-tiza/60 sm:text-base">
                                    {{ $r['dias'] }} días por semana · {{ $r['para'] }}@if(! $paraTi['exacta']) · {{ mb_strtolower($r['nivel']) }}@endif
                                </p>
                                @if($r['descripcion'])
                                    <p class="mt-3 font-modern text-sm text-pg-tiza/80 sm:text-base">{{ $r['descripcion'] }}</p>
                                @endif
                                @if(count($r['jornadas']))
                                    <ol class="mt-4 grid gap-1.5 sm:grid-cols-2">
                                        @foreach($r['jornadas'] as $i => $titulo)
                                            <li class="flex items-center gap-3 rounded-lg bg-pg-negro/40 px-3 py-2.5 font-modern text-sm text-pg-tiza/85">
                                                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-pg-rojo/20 font-display text-sm text-pg-rojo-claro">{{ $i + 1 }}</span>
                                                <span class="min-w-0 truncate">{{ $titulo }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif
                                <a href="{{ $r['url'] }}" data-evento="rutina_elegida" data-detalle="{{ $r['nombre'] }} · {{ $r['dias'] }} días"
                                   class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-pg-rojo px-5 py-4 font-modern font-bold text-white transition-colors hover:bg-pg-rojo-oscuro">
                                    Ver la rutina completa <x-icono nombre="arrow-right" class="text-sm" />
                                </a>
                            </article>
                        @endforeach
                    </div>

                    {{-- Un día más o uno menos, sin volver a contestar. --}}
                    @php $otros = array_values(array_filter($diasPosibles, fn ($n) => $n !== ($paraTi['rutinas'][0]['dias'] ?? $dias))); @endphp
                    @if(count($otros))
                        <p class="mt-6 flex flex-wrap items-center gap-2 font-modern text-sm text-pg-tiza/60">
                            <span class="w-full sm:w-auto">¿Puedes venir más o menos días?</span>
                            @foreach($otros as $n)
                                <a href="{{ $ir(['objetivo' => $filtro, 'nivel' => $nivel, 'dias' => $n]) }}" class="rounded-lg bg-pg-grafito px-4 py-2.5 font-semibold text-pg-tiza transition-colors hover:bg-pg-gris/70">{{ $n }} días</a>
                            @endforeach
                        </p>
                    @endif
                @endif
            </div>

            {{-- ===== Todas ===== --}}
            <div class="mt-12 lg:mt-16">
                <h2 class="font-display text-2xl uppercase text-pg-tiza sm:text-3xl">{{ $filtro ? 'Rutinas para: ' . mb_strtolower($objetivos[$filtro]) : 'Todas las rutinas' }}</h2>

                {{-- Filtro por objetivo: enlaces comunes, lo arma el servidor. --}}
                <nav aria-label="Filtrar por objetivo" class="-mx-4 mb-6 mt-3 flex gap-2 overflow-x-auto px-4 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0 [&::-webkit-scrollbar]:hidden">
                    <a href="{{ route('landing.rutinas') }}" @if(! $filtro) aria-current="page" @endif
                        class="shrink-0 rounded-lg px-4 py-2.5 font-modern text-sm font-semibold transition-colors {{ $filtro ? 'bg-pg-grafito text-pg-tiza/70 hover:text-pg-tiza' : 'bg-pg-rojo text-white' }}">
                        Todas
                    </a>
                    @foreach($objetivos as $clave => $etiqueta)
                        <a href="{{ route('landing.rutinas', ['objetivo' => $clave]) }}" @if($filtro === $clave) aria-current="page" @endif
                            class="shrink-0 rounded-lg px-4 py-2.5 font-modern text-sm font-semibold transition-colors {{ $filtro === $clave ? 'bg-pg-rojo text-white' : 'bg-pg-grafito text-pg-tiza/70 hover:text-pg-tiza' }}">
                            {{ $etiqueta }}
                        </a>
                    @endforeach
                </nav>

                @forelse($grupos as $clave => $grupo)
                    <div id="{{ $clave }}" class="mb-8 scroll-mt-24">
                        @if(! $filtro)
                            <h3 class="font-display text-xl uppercase text-pg-tiza/80 sm:text-2xl">{{ $grupo['nombre'] }}</h3>
                        @endif
                        <ul class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($grupo['familias'] as $f)
                                <li class="rounded-xl bg-pg-grafito/40 px-4 py-3.5 font-modern sm:px-5 sm:py-4">
                                    <span class="block font-semibold text-pg-tiza/90 sm:text-lg">{{ $f['nombre'] }}</span>
                                    <span class="block text-sm text-pg-tiza/50">{{ $f['nivel'] }}</span>
                                    <span class="mt-2.5 flex flex-wrap gap-2">
                                        @foreach($f['variantes'] as $v)
                                            <a href="{{ $v['url'] }}" aria-label="{{ $f['nombre'] }}, {{ $v['dias'] }} días por semana"
                                               class="rounded-lg bg-pg-negro/50 px-3.5 py-2.5 text-sm font-semibold text-pg-tiza/85 ring-1 ring-pg-tiza/10 transition-colors hover:bg-pg-rojo hover:text-white hover:ring-pg-rojo">{{ $v['dias'] }} días</a>
                                        @endforeach
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="font-modern text-sm text-pg-tiza/70">Todavía no hay rutinas cargadas. Pregunta en el mesón y te orientamos.</p>
                @endforelse
            </div>

            <p class="flex flex-wrap gap-x-6 gap-y-2 font-modern text-sm sm:text-base">
                <a href="{{ route('landing.rutina') }}" class="text-pg-tiza underline underline-offset-4 hover:text-white">Qué entrenar hoy</a>
                <a href="{{ route('landing.ejercicios') }}" class="text-pg-tiza underline underline-offset-4 hover:text-white">Mira los ejercicios del gimnasio</a>
            </p>
        </div>
    </section>
@endsection
