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
    <section id="inicio" class="relative min-h-[82vh] flex items-end overflow-hidden">
        {{-- De fondo, la primera foto de la galeria con un acercamiento lento. Sin fotos, el degradado. --}}
        @if($fotoPortada)
            <div class="absolute inset-0" aria-hidden="true">
                <img src="{{ $fotoPortada['imagen'] }}" alt="" class="portada-foto w-full h-full object-cover">
                <div class="absolute inset-0 bg-linear-to-t from-pg-negro via-pg-negro/75 to-pg-negro/30"></div>
            </div>
        @else
            <div class="absolute inset-0 bg-linear-to-br from-pg-negro via-pg-carbon to-pg-grafito" aria-hidden="true"></div>
        @endif

        <div class="relative z-10 w-full max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8 pt-40 pb-20 fade-in">
            <p class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">
                {{ $web['ciudad'] ? 'Gimnasio en ' . $web['ciudad'] : 'Profesionales del deporte' }}
            </p>
            <h1 class="font-display text-5xl sm:text-6xl md:text-7xl lg:text-8xl text-pg-tiza mt-4 uppercase leading-[0.95]">
                <span class="block">{{ $portada['titulo_1'] }}</span>
                <span class="block">{{ $portada['titulo_2'] }}</span>
            </h1>
            <p class="text-lg text-pg-tiza/75 max-w-xl mt-6 font-modern">{{ $portada['subtitulo'] }}</p>
            <div class="mt-9 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('landing.planes') }}" class="inline-flex items-center justify-center bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold px-8 py-4 rounded-lg text-lg transition-colors font-modern">
                    Ver planes
                </a>
                <a href="{{ route('landing.gimnasio') }}" class="inline-flex items-center justify-center border-2 border-pg-tiza/30 hover:border-pg-rojo text-pg-tiza hover:text-pg-rojo-claro px-8 py-4 rounded-lg text-lg transition-colors font-modern">
                    Conoce el gimnasio
                </a>
            </div>
        </div>
    </section>

    {{-- ===== ACCESOS: una tarjeta por pagina ===== --}}
    <section id="accesos" class="py-20 bg-pg-negro">
        @php($columnas = [2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'][min(4, max(2, count($destacados)))])
        {{-- Las clases van escritas enteras: el CSS compilado solo trae las que encuentra tal cual. --}}
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8 grid gap-5 sm:grid-cols-2 {{ $columnas }}">
            @foreach($destacados as $i => $d)
                {{-- El icono suelto y pequeño, al lado del título. Metido en un
                     cuadrado de color es el adorno que traen todas las plantillas
                     y le quitaba sitio a lo que hay que leer. --}}
                <a href="{{ $d['href'] }}" class="animate-on-scroll group card-hover flex h-full flex-col bg-pg-carbon border border-pg-tiza/10 hover:border-pg-rojo/40 rounded-2xl p-8 transition-colors" style="animation-delay: {{ $i * 0.1 }}s">
                    <h2 class="flex items-center gap-3 font-display text-2xl uppercase text-pg-tiza">
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
        <section class="py-14 bg-pg-carbon border-y border-pg-tiza/5">
            <p class="text-center text-pg-tiza/60 font-modern text-sm uppercase tracking-widest mb-8">Convenios con</p>
            @include('landing.partes.cinta', ['logos' => $logosConvenios])
            <p class="text-center mt-8">
                <a href="{{ route('landing.convenios') }}" class="text-pg-rojo-claro hover:underline font-modern text-sm">Ver los convenios y sus precios</a>
            </p>
        </section>
    @endif

    {{-- ===== SERVICIOS ===== --}}
    @if(count($servicios))
        <section id="servicios" class="py-24 bg-pg-negro">
            <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Lo que ofrecemos</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-pg-tiza">NUESTROS SERVICIOS</h2>
                </div>
                @include('landing.partes.servicios')
                <p class="text-center mt-12">
                    <a href="{{ route('landing.gimnasio') }}" class="inline-flex items-center gap-2 text-pg-rojo-claro hover:underline font-modern font-semibold">
                        Conoce el gimnasio completo <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    </a>
                </p>
            </div>
        </section>
    @endif

    {{-- ===== TESTIMONIOS: reales y con permiso ===== --}}
    @if(count($testimonios))
        <section class="py-24 bg-pg-carbon">
            <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Nuestros socios</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-pg-tiza">LO QUE DICEN</h2>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($testimonios as $i => $t)
                        <figure class="animate-on-scroll card-hover bg-pg-negro/70 border border-pg-tiza/10 rounded-2xl p-8" style="animation-delay: {{ $i * 0.1 }}s">
                            <i class="fas fa-quote-left text-pg-rojo/60 text-3xl" aria-hidden="true"></i>
                            <blockquote class="mt-4 text-pg-tiza/85 font-modern leading-relaxed">{{ $t['texto'] }}</blockquote>
                            <figcaption class="mt-6 font-semibold text-pg-tiza font-modern">— {{ $t['titulo'] }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('landing.partes.llamado')
@endsection
