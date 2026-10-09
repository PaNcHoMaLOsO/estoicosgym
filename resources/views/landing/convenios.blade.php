@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    {{-- El encabezado dice lo mismo que el título que ve Google: «gimnasio
         para estudiantes» es lo que se busca, y «Convenios» solo no le decía
         a Google de qué era la página. --}}
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Convenios con instituciones',
        'titulo' => 'Gimnasio para estudiantes',
        'bajada' => null,
    ])

    @php($conConvenio = collect($planes)->filter(fn ($p) => $p['precio_convenio'])->values())
    <section id="convenios" class="pb-10 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            @foreach($conConvenio as $p)
                {{-- Centrado, como el título de la página: pegado a la izquierda
                     quedaba suelto en medio de la nada. --}}
                <div class="animate-on-scroll mx-auto w-fit flex flex-wrap items-baseline justify-center gap-x-3 gap-y-1 bg-pg-carbon border border-pg-rojo/30 rounded-2xl px-6 py-3 mb-4 text-center">
                    <p class="text-pg-tiza/75 font-modern text-base">Con convenio, el plan {{ $p['nombre'] }} queda en <span class="font-display text-2xl text-pg-tiza">${{ number_format($p['precio_convenio'], 0, ',', '.') }}</span> <span class="text-pg-tiza/40 line-through">${{ number_format($p['precio'], 0, ',', '.') }}</span></p>
                </div>
            @endforeach

        </div>
    </section>

    {{--
        TODA LA SECCIÓN EN BLANCO, no una franja blanca dentro del negro.

        El blanco no es decoración: estos logos son de otros y están hechos para
        fondo claro (el azul del AIEP o el de Virginio Gómez sobre negro no se
        ven). Antes cada grupo llevaba su franja blanca y los nombres iban
        debajo, ya sobre negro: el logo y su nombre quedaban en dos mundos
        distintos. Ahora la página entera cambia a blanco aquí y cada institución
        es una sola celda con su logo, su nombre y su requisito.

        Las líneas entre celdas son el hueco de 1 px de la rejilla, que deja ver
        el gris de atrás: así salen bien también cuando una fila queda a medias.
    --}}
    <section class="bg-white text-gray-900 py-9 lg:py-20">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            {{-- Arriba pasan todos los logos; abajo, cada categoría con sus fichas. --}}
            {{-- Solo los que tienen logo, y sin los de «Por su servicio»: la cinta
                 dice «Instituciones con convenio», y con ellos no hay uno firmado. --}}
            @php($todos = collect($convenios)->reject(fn ($g) => $g['titulo'] === 'Por su servicio')->flatMap(fn ($g) => $g['convenios'])->filter(fn ($c) => $c['logo'])->values()->all())
            @if(count($todos) >= 3)
                <p class="text-center font-modern text-xs uppercase tracking-[0.3em] text-gray-400 mb-6">Instituciones con convenio</p>
                @include('landing.partes.cinta', ['logos' => $todos])
            @endif

            @forelse($convenios as $grupo)
                <div class="{{ $loop->first && count($todos) < 3 ? '' : 'mt-9 lg:mt-20' }}">
                    <div class="animate-on-scroll mb-7 flex items-center gap-4">
                        <span class="h-0.5 w-10 bg-pg-rojo" aria-hidden="true"></span>
                        <h2 class="font-display text-2xl md:text-3xl uppercase tracking-wide text-gray-900">{{ $grupo['titulo'] }}</h2>
                        <span class="ml-auto hidden sm:block font-modern text-sm text-gray-400">{{ count($grupo['convenios']) }} {{ count($grupo['convenios']) === 1 ? 'convenio' : 'convenios' }}</span>
                    </div>
                    @if($grupo['bajada'])
                        <p class="animate-on-scroll -mt-3 mb-6 font-modern text-sm text-gray-500 lg:text-base">{{ $grupo['bajada'] }}</p>
                    @endif

                    <div class="animate-on-scroll overflow-hidden rounded-2xl border border-gray-200 bg-gray-200">
                        {{-- Tantas columnas como convenios, hasta cuatro: con tres quedaba una celda gris vacía. --}}
                        <div class="grid {{ count($grupo['convenios']) === 1 ? 'grid-cols-1' : 'grid-cols-2' }} {{ [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][count($grupo['convenios'])] ?? 'lg:grid-cols-4' }} gap-px">
                            @foreach($grupo['convenios'] as $c)
                                <div class="group flex flex-col items-center bg-white px-3 py-5 lg:px-8 lg:py-9 text-center transition-colors duration-300 hover:bg-gray-50">
                                    <div class="flex h-12 lg:h-20 w-full items-center justify-center transition-transform duration-500 group-hover:scale-[1.04]">
                                        @if($c['logo'])
                                            <img src="{{ $c['logo'] }}" alt="{{ $c['nombre'] }}" loading="lazy" class="max-h-full max-w-full object-contain">
                                        @else
                                            {{-- Sin logo, un ícono en un círculo rojo: el nombre va justo abajo. --}}
                                            <span class="grid size-12 place-items-center rounded-full bg-pg-rojo/10 text-xl text-pg-rojo lg:size-16 lg:text-2xl">
                                                <x-icono :nombre="$c['icono']" />
                                            </span>
                                        @endif
                                    </div>
                                    <span class="mt-3 lg:mt-6 h-px w-8 bg-pg-rojo/70 transition-all duration-500 group-hover:w-14" aria-hidden="true"></span>
                                    <p class="mt-2.5 lg:mt-4 font-modern font-semibold text-gray-900 text-sm lg:text-base">{{ $c['nombre'] }}</p>
                                    @if($c['requisito'])
                                        <p class="mt-1 font-modern text-xs lg:text-sm text-gray-500">{{ $c['requisito'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 font-modern py-12">Todavía no publicamos convenios. Pregunta en el mesón si tu empresa o institución tiene uno.</p>
            @endforelse

            <p class="mt-7 lg:mt-12 text-center text-gray-500 font-modern text-sm lg:text-base">
                Presenta tu credencial vigente en el mesón al inscribirte.
                ¿Tu empresa o institución quiere un convenio? <a href="{{ route('landing.contacto') }}" class="font-semibold text-pg-rojo hover:underline">Escríbenos</a>.
            </p>
        </div>
    </section>

    {{-- El arriendo por horas tiene su propia página: aquí solo se enlaza. --}}
    @if($navegacion['arriendo'])
    <section class="py-8 bg-pg-carbon border-t border-pg-tiza/10">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <a href="{{ route('landing.arriendo') }}" class="animate-on-scroll mx-auto flex max-w-3xl flex-wrap items-center justify-center gap-x-4 gap-y-2 rounded-2xl border border-pg-tiza/10 bg-pg-negro px-6 py-4 text-center font-modern transition-colors hover:border-pg-rojo/50">
                <span class="text-pg-tiza/75 text-sm lg:text-base">¿Das clases? Arrienda horas del gimnasio para tu institución, club o alumnos.</span>
                <span class="text-pg-rojo-claro text-sm font-semibold">Ver más <x-icono nombre="arrow-right" class="ml-1" /></span>
            </a>
        </div>
    </section>
    @endif

    @include('landing.partes.llamado')
@endsection
