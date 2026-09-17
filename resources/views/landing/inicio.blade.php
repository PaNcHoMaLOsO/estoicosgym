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
    <section id="inicio" class="relative min-h-[70vh] flex items-end overflow-hidden">
        {{-- El fondo va pasando solo: vídeo, foto, vídeo, foto. Sale de «Página
             web» y de los vídeos que haya subidos. --}}
        @include('landing.partes.portada-fondo')

        <div class="relative z-10 w-full max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-14 fade-in">
            <p class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">
                {{ $web['ciudad'] ? 'Gimnasio en ' . $web['ciudad'] : 'Profesionales del deporte' }}
            </p>
            <h1 class="font-display text-4xl sm:text-5xl md:text-5xl lg:text-6xl text-pg-tiza mt-4 uppercase leading-[0.95]">
                <span class="block">{{ $portada['titulo_1'] }}</span>
                <span class="block">{{ $portada['titulo_2'] }}</span>
            </h1>
            <p class="text-base text-pg-tiza/75 max-w-xl mt-6 font-modern">{{ $portada['subtitulo'] }}</p>
            <div class="mt-9 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('landing.planes') }}" class="inline-flex items-center justify-center bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold px-8 py-3 rounded-lg text-base transition-colors font-modern">
                    Ver planes
                </a>
                <a href="{{ route('landing.gimnasio') }}" class="inline-flex items-center justify-center border-2 border-pg-tiza/30 hover:border-pg-rojo text-pg-tiza hover:text-pg-rojo-claro px-8 py-3 rounded-lg text-base transition-colors font-modern">
                    Conoce el gimnasio
                </a>
            </div>
        </div>
    </section>

    {{-- ===== ACCESOS: una tarjeta por pagina ===== --}}
    <section id="accesos" class="py-14 bg-pg-negro">
        @php($columnas = [2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'][min(4, max(2, count($destacados)))])
        {{-- Las clases van escritas enteras: el CSS compilado solo trae las que encuentra tal cual. --}}
        {{-- Sin tarjetas: una línea separa un acceso del siguiente. Tres cajas
             iguales en fila es lo que hace que la página parezca de plantilla. --}}
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 divide-y {{ $columnas }} lg:divide-y-0 lg:divide-x divide-pg-tiza/10">
            @foreach($destacados as $i => $d)
                {{-- El icono suelto y pequeño, al lado del título. Metido en un
                     cuadrado de color es el adorno que traen todas las plantillas
                     y le quitaba sitio a lo que hay que leer. --}}
                <a href="{{ $d['href'] }}" class="animate-on-scroll group flex h-full flex-col py-7 lg:px-8 transition-colors hover:bg-pg-carbon/50" style="animation-delay: {{ $i * 0.1 }}s">
                    <h2 class="flex items-center gap-3 font-display text-xl uppercase text-pg-tiza">
                        <i class="fas fa-{{ $d['icono'] }} text-pg-rojo-claro text-base" aria-hidden="true"></i>
                        <span>{{ $d['titulo'] }}</span>
                    </h2>
                    <p class="text-pg-tiza/60 font-modern text-sm mt-3 leading-relaxed">{{ $d['texto'] }}</p>
                    <span class="inline-flex items-center gap-2 mt-auto pt-6 text-pg-rojo-claro font-modern text-sm font-semibold">
                        {{ $d['accion'] }} <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ===== CINTA DE CONVENIOS ===== --}}
    @if(count($logosConvenios))
        <section class="py-10 bg-pg-carbon border-y border-pg-tiza/5">
            <p class="text-center text-pg-tiza/60 font-modern text-sm uppercase tracking-widest mb-8">Convenios con</p>
            @include('landing.partes.cinta', ['logos' => $logosConvenios])
            <p class="text-center mt-8">
                <a href="{{ route('landing.convenios') }}" class="text-pg-rojo-claro hover:underline font-modern text-sm">Ver los convenios y sus precios</a>
            </p>
        </section>
    @endif

    {{-- ===== SERVICIOS ===== --}}
    @if(count($servicios))
        <section id="servicios" class="py-16 bg-pg-negro">
            <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Lo que ofrecemos</span>
                    <h2 class="font-display text-3xl md:text-4xl mt-4 text-pg-tiza">NUESTROS SERVICIOS</h2>
                </div>
                @include('landing.partes.servicios')
                <p class="text-center mt-8">
                    <a href="{{ route('landing.gimnasio') }}" class="inline-flex items-center gap-2 text-pg-rojo-claro hover:underline font-modern font-semibold">
                        Conoce el gimnasio completo <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    </a>
                </p>
            </div>
        </section>
    @endif

    {{-- ===== TESTIMONIOS: reales y con permiso ===== --}}
    @if(count($testimonios))
        <section class="py-16 bg-pg-carbon">
            <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Nuestros socios</span>
                    <h2 class="font-display text-3xl md:text-4xl mt-4 text-pg-tiza">LO QUE DICEN</h2>
                </div>
                {{-- Separados por una línea, no metidos cada uno en su recuadro. --}}
                <div class="grid grid-cols-1 divide-y lg:grid-cols-3 lg:divide-y-0 lg:divide-x divide-pg-tiza/10">
                    @foreach($testimonios as $i => $t)
                        <figure class="animate-on-scroll py-7 lg:px-8" style="animation-delay: {{ $i * 0.1 }}s">
                            <i class="fas fa-quote-left text-pg-rojo/60 text-2xl" aria-hidden="true"></i>
                            <blockquote class="mt-4 text-pg-tiza/85 font-modern leading-relaxed">{{ $t['texto'] }}</blockquote>
                            <figcaption class="mt-6 font-semibold text-pg-tiza font-modern">— {{ $t['titulo'] }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('landing.partes.tienda')
    @include('landing.partes.llamado')
@endsection
