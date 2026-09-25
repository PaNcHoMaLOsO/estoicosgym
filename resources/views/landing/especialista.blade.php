@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    El perfil de un especialista.

    En la lista, la presentación iba encima de la foto y con más de dos líneas
    la tapaba. Aquí cada uno tiene su página: la foto grande a un lado y al
    otro quién es, cómo atiende, cómo escribirle —arriba, sin tener que bajar—
    y después su presentación y en qué se enfoca. Mismo negro, mismo rojo y
    misma letra que el resto de la web.
--}}

@section('content')
    @php($e = $especialista)

    <section class="bg-pg-negro pt-24 pb-12 lg:pt-32 lg:pb-20">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <a href="{{ route('landing.especialistas') }}" class="inline-flex items-center gap-2 font-modern text-sm text-pg-tiza/60 transition-colors hover:text-pg-tiza">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i> Especialistas
            </a>

            <div class="mt-6 grid gap-8 lg:mt-8 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-14">
                {{-- La foto, a color: en la lista va en gris hasta pasar por encima. --}}
                <div class="relative aspect-[5/4] overflow-hidden bg-pg-carbon sm:aspect-[4/3] lg:aspect-[4/5] lg:sticky lg:top-28 lg:self-start">
                    @if($e['foto'])
                        <img src="{{ $e['foto'] }}" alt="{{ $e['nombre'] }}, {{ $e['especialidad'] }} en {{ $gimnasio['nombre'] }}"
                             class="absolute inset-0 h-full w-full object-cover object-top">
                    @else
                        <span class="absolute -right-8 -top-12 select-none font-display text-[26rem] leading-none text-transparent [-webkit-text-stroke:1.5px_rgba(221,42,50,0.35)]" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($e['nombre'], 0, 1)) }}
                        </span>
                    @endif
                    <span class="absolute bottom-0 left-0 h-1 w-16 bg-pg-rojo" aria-hidden="true"></span>
                </div>

                <div class="min-w-0">
                    <p class="font-modern text-xs uppercase tracking-[0.2em] text-pg-rojo-claro sm:text-sm">{{ $e['especialidad'] }}</p>
                    <h1 class="mt-2 font-display text-4xl uppercase leading-[0.95] text-pg-tiza md:text-6xl">{{ $e['nombre'] }}</h1>

                    @if($e['modalidad'])
                        <p class="mt-5 inline-flex items-center gap-2 border border-pg-tiza/20 px-3 py-1.5 font-modern text-sm text-pg-tiza/80">
                            <i class="fas fa-location-dot text-pg-rojo-claro text-xs" aria-hidden="true"></i>
                            {{ $e['modalidad'] }}
                        </p>
                    @endif

                    {{-- ESCRIBIRLE, ARRIBA. Es a lo que se viene: no tiene que
                         quedar debajo de la presentación. --}}
                    @if($e['whatsapp'] || $e['instagram'] || $e['email'])
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            @if($e['whatsapp'])
                                <a href="{{ $e['whatsapp'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                   class="inline-flex items-center justify-center gap-2.5 bg-[#25D366] px-6 py-3.5 font-modern text-sm font-semibold text-pg-negro transition-opacity hover:opacity-90">
                                    <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i> Agendar por WhatsApp
                                </a>
                            @endif
                            @if($e['instagram'])
                                <a href="{{ $e['instagram'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                   class="inline-flex items-center justify-center gap-2.5 border border-pg-tiza/30 px-6 py-3.5 font-modern text-sm font-semibold text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                                    <i class="fab fa-instagram text-lg" aria-hidden="true"></i> {{ '@' . $e['usuario'] }}
                                </a>
                            @endif
                            @if($e['email'])
                                <a href="mailto:{{ $e['email'] }}" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                   class="inline-flex items-center justify-center gap-2.5 border border-pg-tiza/30 px-6 py-3.5 font-modern text-sm font-semibold text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                                    <i class="fas fa-envelope text-base" aria-hidden="true"></i> Enviar correo
                                </a>
                            @endif
                        </div>
                    @endif

                    @if($e['descripcion'])
                        <div class="mt-10 border-t border-pg-tiza/10 pt-8">
                            <h2 class="font-display text-2xl uppercase text-pg-tiza">Presentación</h2>
                            <div class="mt-4 max-w-2xl space-y-4 font-modern text-base leading-relaxed text-pg-tiza/75">
                                @foreach(preg_split('/\n\s*\n/', trim($e['descripcion'])) as $parrafo)
                                    <p>{!! nl2br(e($parrafo)) !!}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($e['temas'])
                        <div class="mt-10 border-t border-pg-tiza/10 pt-8">
                            <h2 class="font-display text-2xl uppercase text-pg-tiza">En qué te ayuda</h2>
                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach($e['temas'] as $tema)
                                    <li class="bg-pg-carbon px-3 py-1.5 font-modern text-sm text-pg-tiza/85">{{ $tema }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            @if($otros)
                <div class="mt-16 border-t border-pg-tiza/10 pt-8 lg:mt-24">
                    <h2 class="font-display text-2xl uppercase text-pg-tiza">Otros especialistas</h2>
                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        @foreach($otros as $otro)
                            <a href="{{ $otro['perfil'] }}" class="group flex items-center gap-4 bg-pg-carbon p-3 transition-colors hover:bg-pg-grafito">
                                <span class="relative size-16 shrink-0 overflow-hidden bg-pg-negro">
                                    @if($otro['foto'])
                                        <img src="{{ $otro['foto'] }}" alt="" loading="lazy" class="h-full w-full object-cover object-top grayscale transition duration-500 group-hover:grayscale-0">
                                    @else
                                        <span class="flex h-full items-center justify-center font-display text-3xl text-pg-rojo/60">{{ mb_strtoupper(mb_substr($otro['nombre'], 0, 1)) }}</span>
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate font-modern text-xs uppercase tracking-[0.15em] text-pg-rojo-claro">{{ $otro['especialidad'] }}</span>
                                    <span class="block truncate font-display text-xl uppercase text-pg-tiza">{{ $otro['nombre'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
