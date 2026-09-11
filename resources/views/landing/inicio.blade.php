@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    {{-- ===== PORTADA ===== --}}
    <section id="inicio" class="relative min-h-screen flex items-center justify-center overflow-hidden">
        {{-- De fondo, la primera foto de la galeria con un acercamiento lento. Sin fotos, el degradado. --}}
        @if($fotoPortada)
            <div class="absolute inset-0" aria-hidden="true">
                <img src="{{ $fotoPortada['imagen'] }}" alt="" class="portada-foto w-full h-full object-cover">
                <div class="absolute inset-0 bg-linear-to-b from-pg-negro/85 via-pg-negro/70 to-pg-negro"></div>
            </div>
        @else
            <div class="absolute inset-0 bg-linear-to-br from-pg-negro via-pg-carbon to-pg-grafito" aria-hidden="true"></div>
        @endif
        <div class="absolute top-1/4 left-10 w-72 h-72 bg-pg-rojo/25 rounded-full blur-3xl brillo" aria-hidden="true"></div>
        <div class="absolute bottom-1/4 right-10 w-96 h-96 bg-pg-plata/10 rounded-full blur-3xl brillo" style="animation-delay: -4s" aria-hidden="true"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-28">
            <div class="fade-in">
                <span class="inline-block px-4 py-2 bg-pg-rojo/20 border border-pg-rojo/40 rounded-full text-pg-rojo-claro text-sm font-modern mb-6">
                    <i class="fas fa-map-marker-alt mr-2" aria-hidden="true"></i>
                    {{ $web['ciudad'] ? 'Gimnasio en ' . $web['ciudad'] : 'Profesionales del deporte' }}
                </span>
                <h1 class="font-display text-6xl sm:text-7xl md:text-8xl lg:text-9xl tracking-wider mb-6 leading-[0.95]">
                    <span class="block text-pg-tiza">{{ $portada['titulo_1'] }}</span>
                    <span class="block gradient-text brillo-texto">{{ $portada['titulo_2'] }}</span>
                </h1>
                <p class="text-lg sm:text-xl text-pg-tiza/75 max-w-3xl mx-auto mb-10 font-modern">{{ $portada['subtitulo'] }}</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('landing.planes') }}" class="w-full sm:w-auto bg-linear-to-r from-pg-rojo to-pg-rojo-oscuro text-white font-bold px-10 py-4 rounded-lg text-lg transition-all btn-glow font-modern">
                        <i class="fas fa-bolt mr-2" aria-hidden="true"></i>Ver planes
                    </a>
                    <a href="{{ route('landing.gimnasio') }}" class="w-full sm:w-auto border-2 border-pg-tiza/30 hover:border-pg-rojo text-pg-tiza hover:text-pg-rojo-claro px-10 py-4 rounded-lg text-lg transition-all font-modern">
                        Conoce el gimnasio
                    </a>
                </div>
            </div>
        </div>

        <a href="#accesos" class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce text-pg-rojo/60 hover:text-pg-rojo-claro transition-colors" aria-label="Seguir bajando">
            <i class="fas fa-chevron-down text-2xl" aria-hidden="true"></i>
        </a>
    </section>

    {{-- ===== ACCESOS: una tarjeta por pagina ===== --}}
    <section id="accesos" class="py-20 bg-pg-negro">
        @php($columnas = [2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'][min(4, max(2, count($destacados)))])
        {{-- Las clases van escritas enteras: el CSS compilado solo trae las que encuentra tal cual. --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-5 sm:grid-cols-2 {{ $columnas }}">
            @foreach($destacados as $i => $d)
                <a href="{{ $d['href'] }}" class="animate-on-scroll group card-hover block bg-pg-carbon border border-pg-tiza/10 hover:border-pg-rojo/40 rounded-2xl p-7 transition-colors" style="animation-delay: {{ $i * 0.1 }}s">
                    <div class="w-14 h-14 rounded-xl bg-pg-rojo/10 flex items-center justify-center mb-5 transition-all duration-300 group-hover:bg-pg-rojo/25 group-hover:scale-110">
                        <i class="fas fa-{{ $d['icono'] }} text-pg-rojo-claro text-2xl" aria-hidden="true"></i>
                    </div>
                    <h2 class="font-display text-2xl uppercase text-pg-tiza">{{ $d['titulo'] }}</h2>
                    <p class="text-pg-tiza/60 font-modern text-sm mt-2">{{ $d['texto'] }}</p>
                    <span class="inline-flex items-center gap-2 mt-5 text-pg-rojo-claro font-modern text-sm font-semibold">
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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
