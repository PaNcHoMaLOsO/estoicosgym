@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    Los especialistas, como una lista y no como tarjetas.

    TRES TARJETAS IGUALES CON UN CÍRCULO ARRIBA es la plantilla que sale en
    todas las webs hechas en serie, y se nota: el dueño lo dijo tal cual. Aquí
    va como la plantilla de un equipo en una revista deportiva: un número, el
    nombre en grande, qué hace y cómo escribirle, en filas separadas por una
    línea. Con foto, la foto va al lado y en vertical, que es como sale una
    persona entera; sin foto, el número hace de ancla y no queda un hueco.
--}}

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Con quién entrenas',
        'titulo' => 'Especialistas',
        'bajada' => 'Profesionales que trabajan con ' . $gimnasio['nombre'] . '. Escríbeles directo.',
    ])

    <section id="especialistas" class="pb-9 lg:pb-20 bg-pg-negro">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 lg:px-12">
            @if(count($especialistas))
                <ol class="border-t border-pg-tiza/15">
                    @foreach($especialistas as $index => $e)
                        <li class="animate-on-scroll group border-b border-pg-tiza/15 py-8 lg:py-12" style="animation-delay: {{ ($index % 3) * 0.08 }}s">
                            <div class="grid grid-cols-[auto_1fr] gap-x-5 gap-y-5 sm:gap-x-8 lg:grid-cols-[auto_1fr_auto] lg:items-end">

                                @if($e['foto'])
                                    <img src="{{ $e['foto'] }}" alt="{{ $e['nombre'] }}, {{ $e['especialidad'] }} en {{ $gimnasio['nombre'] }}" loading="lazy" width="160" height="200"
                                         class="row-span-2 lg:row-span-1 h-32 w-24 sm:h-44 sm:w-36 lg:h-52 lg:w-40 object-cover grayscale transition duration-500 group-hover:grayscale-0">
                                @else
                                    {{-- El número en hueco hace de ancla: una fila sin foto no queda coja. --}}
                                    <span class="row-span-2 lg:row-span-1 self-start w-16 sm:w-24 lg:w-28 font-display text-6xl sm:text-7xl lg:text-8xl leading-none text-transparent [-webkit-text-stroke:1.5px_var(--color-pg-rojo)] transition-colors duration-500 group-hover:text-pg-rojo/20" aria-hidden="true">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <p class="font-modern text-xs sm:text-sm uppercase tracking-[0.25em] text-pg-rojo-claro">{{ $e['especialidad'] }}</p>
                                    <h2 class="mt-1 font-display text-3xl sm:text-5xl lg:text-6xl uppercase leading-[0.95] text-pg-tiza">{{ $e['nombre'] }}</h2>
                                    @if($e['descripcion'])
                                        <p class="mt-3 lg:mt-4 max-w-xl font-modern text-sm sm:text-base leading-relaxed text-pg-tiza/60">{{ $e['descripcion'] }}</p>
                                    @endif
                                </div>

                                @if($e['whatsapp'] || $e['instagram'])
                                    <div class="col-start-2 lg:col-start-3 flex flex-wrap gap-x-6 gap-y-2 font-modern text-sm">
                                        @if($e['whatsapp'])
                                            <a href="{{ $e['whatsapp'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                               class="inline-flex items-center gap-2 border-b border-pg-tiza/30 pb-1 text-pg-tiza transition-colors hover:border-[#25D366] hover:text-[#25D366]">
                                                <i class="fab fa-whatsapp" aria-hidden="true"></i> Escribirle
                                            </a>
                                        @endif
                                        @if($e['instagram'])
                                            <a href="{{ $e['instagram'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                               class="inline-flex items-center gap-2 border-b border-pg-tiza/30 pb-1 text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                                                <i class="fab fa-instagram" aria-hidden="true"></i> Instagram
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="text-center text-pg-tiza/60 font-modern py-12">Pronto vas a encontrar aquí a los profesionales que trabajan con nosotros.</p>
            @endif
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
