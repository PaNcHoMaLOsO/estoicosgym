@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    {{-- ===== PORTADA ===== --}}
    {{--
        La portada apoyada abajo sobre la foto, igual que la de El gimnasio.

        Antes ocupaba la pantalla entera con el titular centrado, una píldora con
        un pin, dos manchas de color borrosas detrás y una flecha que rebotaba.
        Es el molde de cualquier plantilla: se reconoce antes de leer nada, y no
        decía de este gimnasio más de lo que dice la foto.
    --}}
    <section id="inicio" class="relative lg:min-h-[80svh] flex items-end overflow-hidden">
        {{-- El fondo va pasando solo: vídeo, foto, vídeo, foto. Sale de «Página
             web» y de los vídeos que haya subidos. --}}
        @include('landing.partes.portada-fondo')

        <div class="relative z-10 w-full max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20 pt-24 lg:pt-32 pb-7 lg:pb-14 entrada">
            {{-- EL H1 ES EL RÓTULO PEQUEÑO, no el eslogan: «Gimnasio en Los
                 Ángeles» es lo que busca la gente y lo que Google lee primero.
                 El eslogan sigue siendo lo grande, pero como párrafo. Se ven
                 igual que antes: los estilos de la portada miran a los dos. --}}
            <h1 class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">
                {{ $web['ciudad'] ? 'Gimnasio en ' . $web['ciudad'] : 'Gimnasio ' . $gimnasio['nombre'] }}
            </h1>
            <p class="font-display text-4xl sm:text-5xl md:text-5xl lg:text-6xl text-pg-tiza mt-4 uppercase leading-[0.95]">
                <span class="block">{{ $portada['titulo_1'] }}</span>
                <span class="block">{{ $portada['titulo_2'] }}</span>
            </p>
            <p class="text-base text-pg-tiza/75 max-w-xl mt-4 lg:mt-6 font-modern">{{ $portada['subtitulo'] }}</p>
            <div class="mt-6 lg:mt-9 flex flex-row gap-3 lg:gap-4">
                @if($navegacion['planes'])
                    <a href="{{ route('landing.planes') }}" class="inline-flex items-center justify-center flex-1 sm:flex-none bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold px-4 sm:px-8 py-3 rounded-lg text-sm sm:text-base transition-colors font-modern">
                        Ver planes
                    </a>
                @endif
                @if($navegacion['gimnasio'])
                    <a href="{{ route('landing.gimnasio') }}" class="inline-flex items-center justify-center border-2 border-pg-tiza/30 hover:border-pg-rojo flex-1 sm:flex-none whitespace-nowrap text-pg-tiza hover:text-pg-rojo-claro px-4 sm:px-8 py-3 rounded-lg text-sm sm:text-base transition-colors font-modern">
                        Conoce el gimnasio
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- ===== ACCESOS: una tarjeta por pagina ===== --}}
    <section id="accesos" class="py-1 lg:py-14 bg-pg-negro">
        @php($columnas = [2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5'][min(5, max(2, count($destacados)))])
        {{-- Las clases van escritas enteras: el CSS compilado solo trae las que encuentra tal cual. --}}
        {{-- Sin tarjetas: una línea separa un acceso del siguiente. Tres cajas
             iguales en fila es lo que hace que la página parezca de plantilla. --}}
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20 grid grid-cols-1 divide-y {{ $columnas }} lg:divide-y-0 lg:divide-x divide-pg-tiza/10">
            @foreach($destacados as $i => $d)
                {{-- El icono suelto y pequeño, al lado del título. Metido en un
                     cuadrado de color es el adorno que traen todas las plantillas
                     y le quitaba sitio a lo que hay que leer. --}}
                <a href="{{ $d['href'] }}" class="animate-on-scroll group flex h-full flex-col py-4 lg:py-7 lg:px-8 transition-colors hover:bg-pg-carbon/50" style="animation-delay: {{ $i * 0.1 }}s">
                    <h2 class="flex items-center gap-3 font-display text-xl uppercase text-pg-tiza">
                        <x-icono :nombre="$d['icono']" class="text-pg-rojo-claro text-base" />
                        <span>{{ $d['titulo'] }}</span>
                        <x-icono nombre="chevron-right" class="ml-auto text-xs text-pg-tiza/35 lg:hidden" />
                    </h2>
                    <p class="text-pg-tiza/60 font-modern text-sm mt-1.5 lg:mt-3 leading-relaxed">{{ $d['texto'] }}</p>
                    <span class="hidden lg:inline-flex items-center gap-2 mt-auto pt-6 text-pg-rojo-claro font-modern text-sm font-semibold">
                        {{ $d['accion'] }} <x-icono nombre="arrow-right" class="text-xs transition-transform group-hover:translate-x-1" />
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ===== RUTINAS: su propia franja, la fila de accesos ya va llena ===== --}}
    @if($navegacion['rutinas'])
    <section class="bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <div class="animate-on-scroll flex flex-col gap-4 border-t border-pg-tiza/10 py-6 sm:flex-row sm:items-center sm:justify-between lg:py-8">
                <div>
                    <h2 class="font-display text-xl uppercase text-pg-tiza">Rutinas para entrenar</h2>
                    <p class="mt-1 font-modern text-sm text-pg-tiza/60">Ejercicios, series y repeticiones según tu objetivo.</p>
                </div>
                <a href="{{ route('landing.rutina') }}" class="inline-flex shrink-0 items-center justify-center gap-2 bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold px-6 py-3 rounded-lg text-sm transition-colors font-modern">
                    Ver rutinas <x-icono nombre="arrow-right" class="text-xs" />
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- ===== CINTA DE CONVENIOS ===== --}}
    @if(count($logosConvenios))
        {{-- Toda la sección en blanco, como en Convenios: los logos son de otros y
             están hechos para fondo claro. Una franja blanca dentro del negro
             parecía un parche. --}}
        <section class="py-7 lg:py-12 bg-white">
            <p class="text-center text-gray-400 font-modern text-sm uppercase tracking-widest mb-4 lg:mb-8">Convenios con</p>
            @include('landing.partes.cinta', ['logos' => $logosConvenios])
            <p class="text-center mt-4 lg:mt-8">
                <a href="{{ route('landing.convenios') }}" class="inline-flex items-center gap-2 text-pg-rojo hover:underline font-modern text-sm font-semibold">Ver los convenios y sus precios <x-icono nombre="arrow-right" class="text-xs" /></a>
            </p>
        </section>
    @endif

    {{-- ===== SERVICIOS ===== --}}
    @if(count($servicios))
        <section id="servicios" class="py-9 lg:py-16 bg-pg-negro">
            <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
                <div class="text-center mb-6 lg:mb-10 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Lo que ofrecemos</span>
                    <h2 class="font-display text-3xl md:text-4xl mt-4 text-pg-tiza">NUESTROS SERVICIOS</h2>
                    {{-- QUÉ ES Y DÓNDE, en una frase: lo que Google y los asistentes
                         citan cuando alguien pregunta por un gimnasio en la ciudad.
                         Armada con lo que hay encendido, nunca con lo que no. --}}
                    @if($web['ciudad'])
                        <p class="mx-auto mt-4 max-w-2xl font-modern text-sm text-pg-tiza/60 lg:text-base">
                            {{ $gimnasio['nombre'] }} es un gimnasio en el centro de {{ $web['ciudad'] }}{{ $web['region'] ? ', ' . $web['region'] : '' }}{{ $gimnasio['direccion'] ? ' (' . $gimnasio['direccion'] . ')' : '' }},
                            con {{ \Illuminate\Support\Str::lower(collect($servicios)->pluck('titulo')->join(', ', ' y ')) }}{{ $navegacion['especialistas'] ? ', especialistas en el mismo lugar' : '' }}{{ $navegacion['convenios'] ? ' y precio especial para estudiantes con convenio' : '' }}.
                        </p>
                    @endif
                </div>
                @include('landing.partes.servicios')
                @if($navegacion['gimnasio'])
                    <p class="text-center mt-4 lg:mt-8">
                        <a href="{{ route('landing.gimnasio') }}" class="inline-flex items-center gap-2 text-pg-rojo-claro hover:underline font-modern font-semibold">
                            Conoce el gimnasio completo <x-icono nombre="arrow-right" class="text-xs" />
                        </a>
                    </p>
                @endif
            </div>
        </section>
    @endif

    {{-- ===== TESTIMONIOS: reales y con permiso ===== --}}
    @include('landing.partes.testimonios')

    @include('landing.partes.tienda')
    {{-- Los embajadores justo antes del bloque rojo: gente real que entrena
         aquí es lo último que se ve antes de «¿Listo para empezar?». --}}
    @include('landing.partes.embajadores')
    @include('landing.partes.llamado')
@endsection
