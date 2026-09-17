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
                    {{--
                        UNA SOLA FRANJA BLANCA, no una tarjeta por institución.

                        El blanco no es decoración y por eso no se puede quitar del
                        todo: estos logos son de otros y están hechos para fondo
                        claro —el azul del AIEP o el de Virginio Gómez sobre negro
                        no se ven—. Así que en vez de un recuadro blanco por cada
                        uno, hay una franja para todos y una línea fina los separa,
                        igual que la cinta de la portada.

                        El nombre va debajo, ya sobre el fondo de la página, en las
                        mismas columnas para que quede bajo su logo.

                        Tampoco flotan ya cada una por su cuenta: con las tarjetas
                        fuera, ese vaivén no tenía de dónde agarrarse.
                    --}}
                    <div class="animate-on-scroll overflow-hidden rounded-lg bg-white">
                        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-black/10">
                            @foreach($grupo['convenios'] as $c)
                                <div class="flex h-24 items-center justify-center px-6 py-4">
                                    @if($c['logo'])
                                        <img src="{{ $c['logo'] }}" alt="{{ $c['nombre'] }}" loading="lazy" class="max-h-full max-w-full object-contain">
                                    @else
                                        <span class="text-center font-modern font-semibold leading-tight text-gray-800">{{ $c['nombre'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-pg-tiza/10">
                        @foreach($grupo['convenios'] as $c)
                            <div class="px-4 py-4 text-center">
                                <p class="font-modern font-semibold text-pg-tiza">{{ $c['nombre'] }}</p>
                                @if($c['requisito'])
                                    <p class="font-modern text-sm text-pg-tiza/50">{{ $c['requisito'] }}</p>
                                @endif
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
