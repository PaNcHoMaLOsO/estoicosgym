@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    Los especialistas, lado a lado y de borde a borde.

    PRIMERO FUERON TRES TARJETAS con un círculo arriba —la maqueta de siempre,
    se notaba hecha en serie— y después una lista hacia abajo que dejaba media
    pantalla vacía a la derecha. El dueño pidió horizontal y sin espacio
    perdido: cada especialista es un panel alto que ocupa su parte del ancho,
    como los embajadores de la portada, con la foto de fondo y lo que hace y
    cómo escribirle abajo. Sin foto, su inicial gigante en hueco llena el
    panel. En el celular se deslizan de lado.
--}}

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Con quién entrenas',
        'titulo' => 'Especialistas',
        'bajada' => 'Profesionales que trabajan con ' . $gimnasio['nombre'] . '. Escríbeles directo.',
    ])

    <section id="especialistas" class="pb-10 lg:pb-20 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            @if(count($especialistas))
                @php
                    // Tantas columnas como especialistas, hasta cuatro: con dos, dos
                    // paneles anchos; nunca un tercio de pantalla vacío.
                    $columnas = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][count($especialistas)] ?? 'lg:grid-cols-4';
                @endphp

                <div class="-mx-5 flex snap-x snap-mandatory gap-3 overflow-x-auto px-5 pb-2 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 {{ $columnas }} lg:gap-4">
                    @foreach($especialistas as $index => $e)
                        <article class="animate-on-scroll group relative flex min-h-[22rem] w-[82%] shrink-0 snap-start flex-col justify-end overflow-hidden bg-pg-carbon sm:w-auto lg:min-h-[26rem]" style="animation-delay: {{ ($index % 4) * 0.08 }}s">
                            @if($e['foto'])
                                <img src="{{ $e['foto'] }}" alt="{{ $e['nombre'] }}, {{ $e['especialidad'] }} en {{ $gimnasio['nombre'] }}" loading="lazy"
                                     class="absolute inset-0 h-full w-full object-cover object-top grayscale transition duration-700 group-hover:scale-105 group-hover:grayscale-0">
                            @else
                                {{-- Sin foto, la inicial llena el panel: un fondo liso se ve vacío. --}}
                                <span class="absolute -right-6 -top-10 select-none font-display text-[18rem] leading-none text-transparent [-webkit-text-stroke:1.5px_rgba(221,42,50,0.35)] transition-colors duration-700 group-hover:text-pg-rojo/10" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($e['nombre'], 0, 1)) }}
                                </span>
                            @endif

                            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-pg-negro via-pg-negro/70 to-transparent" aria-hidden="true"></div>

                            {{-- LO JUSTO PARA ELEGIR. La presentación iba aquí y con más de
                                 dos líneas tapaba la foto; ahora va en su perfil, al que lleva
                                 todo el panel. El WhatsApp queda a mano, para quien ya sabe. --}}
                            <div class="relative p-5 lg:p-7">
                                <span class="block h-0.5 w-8 bg-pg-rojo transition-all duration-500 group-hover:w-16" aria-hidden="true"></span>
                                <p class="mt-4 line-clamp-2 font-modern text-xs uppercase tracking-[0.2em] text-pg-rojo-claro">{{ $e['especialidad'] }}</p>
                                <h2 class="mt-1 font-display text-3xl lg:text-4xl uppercase leading-none text-pg-tiza">
                                    <a href="{{ $e['perfil'] }}" class="after:absolute after:inset-0">{{ $e['nombre'] }}</a>
                                </h2>
                                @if($e['modalidad'])
                                    <p class="mt-2 font-modern text-xs text-pg-tiza/60">{{ $e['modalidad'] }}</p>
                                @endif

                                <div class="mt-5 flex items-center justify-between gap-4 font-modern text-sm">
                                    <span class="inline-flex items-center gap-2 border-b border-pg-tiza/30 pb-1 text-pg-tiza transition-colors group-hover:border-pg-rojo group-hover:text-pg-rojo-claro">
                                        Ver perfil <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                                    </span>
                                    @if($e['whatsapp'])
                                        <a href="{{ $e['whatsapp'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                           aria-label="Escribirle a {{ $e['nombre'] }} por WhatsApp"
                                           class="relative z-10 flex size-10 items-center justify-center rounded-full border border-pg-tiza/25 text-pg-tiza transition-colors hover:border-[#25D366] hover:bg-[#25D366]">
                                            <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-center text-pg-tiza/60 font-modern py-12">Pronto vas a encontrar aquí a los profesionales que trabajan con nosotros.</p>
            @endif
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
