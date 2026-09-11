@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Por dentro',
        'titulo' => 'El gimnasio',
        'bajada' => $gimnasio['nombre'] . ' es un gimnasio' . ($web['ciudad'] ? ' en ' . $web['ciudad'] : '') . ($web['region'] ? ', región del ' . $web['region'] : '') . '. Esto es lo que encuentras al entrar.',
    ])

    @if(count($servicios))
        <section id="servicios" class="pb-24 bg-pg-negro">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @include('landing.partes.servicios')
            </div>
        </section>
    @endif

    {{-- ===== GALERIA: sale de Pagina web -> Fotos ===== --}}
    @if(count($fotos))
        <section id="fotos" class="py-24 bg-pg-carbon">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Así se ve</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-pg-tiza">EL GIMNASIO EN FOTOS</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 auto-rows-[12rem] md:auto-rows-[15rem]">
                    @foreach($fotos as $i => $foto)
                        <figure class="animate-on-scroll group relative overflow-hidden rounded-2xl bg-pg-negro {{ $i === 0 ? 'col-span-2 row-span-2' : '' }}" style="animation-delay: {{ ($i % 3) * 0.1 }}s">
                            <img src="{{ $foto['imagen'] }}" alt="{{ $foto['titulo'] }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 to-transparent p-4 text-sm text-pg-tiza font-modern opacity-0 translate-y-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0">{{ $foto['titulo'] }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('landing.partes.horario')
    @include('landing.partes.llamado')
@endsection
