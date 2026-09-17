@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Estudiantes, empresas e instituciones',
        'titulo' => 'Convenios',
        'bajada' => null,
    ])

    @php($conConvenio = collect($planes)->filter(fn ($p) => $p['precio_convenio'])->values())
    <section id="convenios" class="pb-16 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
            @foreach($conConvenio as $p)
                {{-- Centrado, como el título de la página: pegado a la izquierda
                     quedaba suelto en medio de la nada. --}}
                <div class="animate-on-scroll mx-auto w-fit flex flex-wrap items-baseline justify-center gap-x-3 gap-y-1 bg-pg-carbon border border-pg-rojo/30 rounded-2xl px-6 py-3 mb-4 text-center">
                    <p class="text-pg-tiza/75 font-modern text-base">Con convenio, el plan {{ $p['nombre'] }} queda en <span class="font-display text-2xl text-pg-tiza">${{ number_format($p['precio_convenio'], 0, ',', '.') }}</span> <span class="text-pg-tiza/40 line-through">${{ number_format($p['precio'], 0, ',', '.') }}</span></p>
                </div>
            @endforeach

            {{-- Arriba pasan todos los logos; abajo, cada categoría con sus fichas grandes. --}}
            @php($todos = collect($convenios)->flatMap(fn ($g) => $g['convenios'])->values()->all())
            @if(count($todos) >= 3)
                <div class="mt-10">
                    @include('landing.partes.cinta', ['logos' => $todos])
                </div>
            @endif

            @forelse($convenios as $grupo)
                <div class="mt-16">
                    <h2 class="animate-on-scroll font-display text-2xl uppercase tracking-wide text-pg-tiza mb-8">{{ $grupo['titulo'] }}</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($grupo['convenios'] as $i => $c)
                            <div class="animate-on-scroll" style="animation-delay: {{ $i * 0.1 }}s">
                                {{-- Flotan cada una a su tiempo; al pasar el mouse crecen. --}}
                                <div class="group flotar" style="animation-delay: -{{ ($i % 4) * 1.5 }}s">
                                    {{-- Más bajas y sin crecer al pasar por encima: eran
                                         unos cuadros blancos enormes que se comían la
                                         pantalla y daban el salto de plantilla. --}}
                                    <div class="h-28 sm:h-32 bg-white rounded-2xl p-5 flex items-center justify-center shadow-lg shadow-black/30 ring-1 ring-white/10 transition-colors duration-300 group-hover:ring-2 group-hover:ring-pg-rojo/60">
                                        @if($c['logo'])
                                            <img src="{{ $c['logo'] }}" alt="{{ $c['nombre'] }}" loading="lazy" class="max-h-full max-w-full object-contain">
                                        @else
                                            <span class="text-gray-800 font-semibold font-modern text-base sm:text-xl text-center leading-tight">{{ $c['nombre'] }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-4 text-center font-modern font-semibold text-pg-tiza">{{ $c['nombre'] }}</p>
                                    @if($c['requisito'])
                                        <p class="text-center font-modern text-sm text-pg-tiza/50">{{ $c['requisito'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-pg-tiza/60 font-modern py-12">Todavía no publicamos convenios. Pregunta en el mesón si tu empresa o institución tiene uno.</p>
            @endforelse

            <p class="mt-14 text-center text-pg-tiza/60 font-modern">
                Presenta tu credencial vigente en el mesón al inscribirte.
                ¿Tu empresa o institución quiere un convenio? <a href="{{ route('landing.contacto') }}" class="text-pg-rojo-claro hover:underline">Escríbenos</a>.
            </p>
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
