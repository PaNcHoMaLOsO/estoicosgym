@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])
@section('sin-flotantes', '1')

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

    <section class="bg-pg-negro pt-24 pb-24 lg:pt-32 lg:pb-20">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <a href="{{ route('landing.especialistas') }}" class="inline-flex items-center gap-2 font-modern text-sm text-pg-tiza/60 transition-colors hover:text-pg-tiza">
                <x-icono nombre="arrow-left" class="text-xs" /> Profesionales
            </a>

            <div class="mt-6 grid grid-cols-[minmax(0,1fr)] gap-8 lg:mt-8 lg:grid-cols-[minmax(0,22rem)_minmax(0,1fr)] lg:gap-14 xl:grid-cols-[minmax(0,24rem)_minmax(0,1fr)]">
                {{-- La foto, a color: en la lista va en gris hasta pasar por encima. --}}
                {{-- La foto, más chica: era casi la mitad de la pantalla y dejaba los datos apretados al lado. --}}
                <div class="relative aspect-[4/3] w-full overflow-hidden bg-pg-carbon sm:max-w-md lg:aspect-[4/5] lg:max-w-none lg:sticky lg:top-28 lg:self-start">
                    @if($e['foto'])
                        <img src="{{ $e['foto'] }}" alt="{{ $e['nombre'] }}, {{ $e['especialidad'] }} en {{ $gimnasio['nombre'] }}{{ $web['ciudad'] ? ', ' . $web['ciudad'] : '' }}"
                             loading="eager" fetchpriority="high"
                             class="absolute inset-0 h-full w-full object-cover object-top">
                    @else
                        <span class="absolute -right-8 -top-12 select-none font-display text-[26rem] leading-none text-transparent [-webkit-text-stroke:1.5px_rgba(221,42,50,0.35)]" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($e['nombre'], 0, 1)) }}
                        </span>
                    @endif
                    <span class="absolute bottom-0 left-0 h-1 w-16 bg-pg-rojo" aria-hidden="true"></span>
                </div>

                {{-- LA COLUMNA DE LOS DATOS. Sin presentación ni temas cargados
                     quedaba casi vacía: un nombre, tres botones y negro. Ahora
                     siempre lleva las fichas (cuándo, dónde, qué hace), cómo
                     agendar y, detrás, su nombre gigante en hueco, como el resto
                     de la web. --}}
                <div class="relative min-w-0">
                    <span class="pointer-events-none absolute inset-x-0 -top-10 hidden select-none overflow-hidden whitespace-nowrap text-right font-display text-[11rem] uppercase leading-none text-transparent [-webkit-text-stroke:1.5px_rgba(221,42,50,0.16)] xl:block" aria-hidden="true">{{ \Illuminate\Support\Str::before($e['nombre'], ' ') }}</span>

                    <div class="relative">
                        <p class="flex items-center gap-3 font-modern text-xs uppercase tracking-[0.2em] text-pg-rojo-claro sm:text-sm">
                            <span class="h-0.5 w-8 shrink-0 bg-pg-rojo" aria-hidden="true"></span>{{ $e['especialidad'] }}
                        </p>
                        <h1 class="mt-3 font-display text-[2.6rem] uppercase leading-[0.92] text-pg-tiza [overflow-wrap:anywhere] sm:text-5xl md:text-7xl">{{ $e['nombre'] }}</h1>

                        <p class="mt-4 flex flex-wrap items-center gap-2 font-modern text-xs uppercase tracking-[0.15em]">
                            <span class="inline-flex items-center gap-1.5 bg-pg-rojo/15 px-2.5 py-1 text-pg-rojo-claro ring-1 ring-pg-rojo/40">
                                <x-icono nombre="user-check" class="text-[10px]" />
                                {{ $e['recomendado'] ? 'Recomendado por ' . $gimnasio['nombre'] : 'Equipo ' . $gimnasio['nombre'] }}
                            </span>
                            @if($e['modalidad'])
                                <span class="bg-pg-carbon px-2.5 py-1 text-pg-tiza/70">{{ $e['modalidad'] }}</span>
                            @endif
                        </p>

                        {{-- CUÁNDO, DÓNDE, QUÉ: lo que se pregunta antes de escribirle. --}}
                        <dl class="mt-8 grid gap-px overflow-hidden bg-pg-tiza/10 sm:grid-cols-3">
                            @foreach([
                                ['clock', 'Cuándo atiende', collect([$e['dias'], $e['horario']])->filter()->implode(' · ') ?: ($e['whatsapp'] ? 'A coordinar por WhatsApp' : 'A coordinar')],
                                ['location-dot', 'Dónde', $e['lugar'] ?: ($e['modalidad'] === 'Online' ? 'Online' : ($web['ciudad'] ?: $gimnasio['nombre']))],
                                ['medal', 'Especialidad', $e['especialidad']],
                            ] as [$icono, $etiqueta, $valor])
                                <div class="bg-pg-carbon px-5 py-4">
                                    <dt class="flex items-center gap-2 font-modern text-[11px] uppercase tracking-[0.18em] text-pg-tiza/45">
                                        <x-icono :nombre="$icono" class="text-pg-rojo-claro" /> {{ $etiqueta }}
                                    </dt>
                                    <dd class="mt-1.5 font-modern text-base font-medium leading-snug text-pg-tiza">{{ $valor }}</dd>
                                    @if($icono === 'location-dot' && $e['mapa'])
                                        <a href="{{ $e['mapa'] }}" target="_blank" rel="noopener" data-evento="como_llegar_especialista" data-detalle="{{ $e['nombre'] }}"
                                           class="mt-1.5 inline-flex items-center gap-1.5 font-modern text-sm text-pg-rojo-claro transition-colors hover:text-pg-tiza">
                                            Cómo llegar <x-icono nombre="arrow-up-right-from-square" class="text-[10px]" />
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </dl>

                        {{-- ESCRIBIRLE, ARRIBA. Es a lo que se viene: no tiene que
                             quedar debajo de la presentación. --}}
                        @if($e['whatsapp'] || $e['instagram'] || $e['tiktok'] || $e['sitio'] || $e['email'])
                            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                @if($e['whatsapp'])
                                    <a href="{{ $e['whatsapp'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                       class="inline-flex items-center justify-center gap-2.5 bg-[#25D366] px-6 py-3.5 font-modern text-sm font-semibold text-pg-negro transition-opacity hover:opacity-90">
                                        <x-icono nombre="whatsapp" class="text-lg" /> Agendar por WhatsApp
                                    </a>
                                @endif
                                @if($e['instagram'])
                                    <a href="{{ $e['instagram'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                       class="inline-flex items-center justify-center gap-2.5 border border-pg-tiza/30 px-6 py-3.5 font-modern text-sm font-semibold text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                                        <x-icono nombre="instagram" class="text-lg" /> {{ '@' . $e['usuario'] }}
                                    </a>
                                @endif
                                @if($e['tiktok'])
                                    <a href="{{ $e['tiktok'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                       class="inline-flex items-center justify-center gap-2.5 border border-pg-tiza/30 px-6 py-3.5 font-modern text-sm font-semibold text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                                        <x-icono nombre="tiktok" class="text-lg" /> {{ '@' . $e['usuario_tiktok'] }}
                                    </a>
                                @endif
                                @if($e['sitio'])
                                    <a href="{{ $e['sitio'] }}" target="_blank" rel="noopener" data-evento="sitio_especialista" data-detalle="{{ $e['nombre'] }}"
                                       class="inline-flex items-center justify-center gap-2.5 border border-pg-tiza/30 px-6 py-3.5 font-modern text-sm font-semibold text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                                        <x-icono nombre="arrow-up-right-from-square" class="text-sm" /> {{ $e['sitio_legible'] }}
                                    </a>
                                @endif
                                @if($e['email'])
                                    <a href="mailto:{{ $e['email'] }}" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                       class="inline-flex items-center justify-center gap-2.5 border border-pg-tiza/30 px-6 py-3.5 font-modern text-sm font-semibold text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                                        <x-icono nombre="envelope" class="text-base" /> Enviar correo
                                    </a>
                                @endif
                            </div>
                        @endif

                        {{-- La presentación: la primera parte, grande y con la raya
                             roja, como cita; lo demás, en letra normal. --}}
                        @if($e['descripcion'])
                            @php($parrafos = preg_split('/\n\s*\n/', trim($e['descripcion'])))
                            <div class="mt-10 max-w-2xl">
                                <p class="border-l-2 border-pg-rojo pl-5 font-modern text-lg leading-relaxed text-pg-tiza sm:text-xl">{!! nl2br(e($parrafos[0])) !!}</p>
                                @if(count($parrafos) > 1)
                                    <div class="mt-5 space-y-4 pl-5 font-modern text-base leading-relaxed text-pg-tiza/70">
                                        @foreach(array_slice($parrafos, 1) as $parrafo)
                                            <p>{!! nl2br(e($parrafo)) !!}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($e['temas'])
                            <div class="mt-10">
                                <h2 class="font-display text-2xl uppercase text-pg-tiza">En qué te ayuda</h2>
                                <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                                    @foreach($e['temas'] as $tema)
                                        <li class="flex items-center gap-3 bg-pg-carbon px-4 py-3 font-modern text-sm text-pg-tiza/85">
                                            <x-icono nombre="circle-check" class="shrink-0 text-pg-rojo-claro" /> {{ $tema }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- CÓMO AGENDAR, en tres pasos: quien llega por Google no
                             sabe si escribirle, si hay que pagar antes o dónde. --}}
                        <div class="mt-10">
                            <h2 class="font-display text-2xl uppercase text-pg-tiza">Cómo agendar</h2>
                            <ol class="mt-4 grid gap-px overflow-hidden bg-pg-tiza/10 sm:grid-cols-3">
                                @foreach([
                                    ['whatsapp', $e['whatsapp'] ? 'Escríbele por WhatsApp' : 'Escríbele', 'Cuéntale qué buscas.'],
                                    ['clock', 'Coordinan el día', collect([$e['dias'], $e['horario']])->filter()->implode(' · ') ?: 'El que les acomode a los dos.'],
                                    ['person-running', 'Primera sesión', $e['lugar'] ?: ($e['modalidad'] ?: 'Y a partir de ahí, a entrenar.')],
                                ] as $i => [$icono, $paso, $detalle])
                                    <li class="relative bg-pg-carbon px-5 py-5">
                                        <span class="absolute right-4 top-3 font-display text-4xl leading-none text-pg-tiza/10" aria-hidden="true">{{ $i + 1 }}</span>
                                        <span class="grid size-10 place-items-center rounded-full bg-pg-rojo/15 text-pg-rojo-claro"><x-icono :nombre="$icono" /></span>
                                        <p class="mt-3 font-modern text-sm font-semibold text-pg-tiza">{{ $paso }}</p>
                                        <p class="mt-1 font-modern text-sm text-pg-tiza/55">{{ $detalle }}</p>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if($susEspecialidades)
                <p class="mt-10 font-modern text-sm">
                    @foreach($susEspecialidades as $esp)
                        <a href="{{ $esp['url'] }}" class="mr-5 inline-flex items-center gap-2 text-pg-rojo-claro transition-colors hover:text-pg-tiza">
                            Ver más de {{ \Illuminate\Support\Str::lower($esp['nombre']) }} <x-icono nombre="arrow-right" class="text-xs" />
                        </a>
                    @endforeach
                </p>
            @endif

            @if($otros)
                <div class="mt-16 border-t border-pg-tiza/10 pt-8 lg:mt-24">
                    <h2 class="font-display text-2xl uppercase text-pg-tiza">Otros profesionales</h2>
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

            {{-- EL GIMNASIO, AL FINAL DEL PERFIL: quien vino por el profesional
                 se entera de que puede entrenar ahí mismo. --}}
            @php($desde = collect($planes)->pluck('precio')->filter()->min())
            <a href="{{ route('landing.planes') }}" class="group mt-12 flex flex-col gap-4 overflow-hidden border-l-4 border-pg-rojo bg-pg-carbon px-6 py-6 transition-colors hover:bg-pg-grafito sm:flex-row sm:items-center sm:justify-between lg:px-10">
                <span>
                    <span class="block font-modern text-xs uppercase tracking-[0.2em] text-pg-rojo-claro">{{ $web['ciudad'] ? 'En el centro de ' . $web['ciudad'] : 'Entrena con nosotros' }}</span>
                    <span class="mt-1 block font-display text-3xl uppercase leading-none text-pg-tiza md:text-4xl">Entrena en {{ $gimnasio['nombre'] }}</span>
                    @if($desde)
                        <span class="mt-2 block font-modern text-sm text-pg-tiza/65">Planes desde ${{ number_format($desde, 0, ',', '.') }} al mes</span>
                    @endif
                </span>
                <span class="inline-flex shrink-0 items-center gap-2 self-start bg-pg-rojo px-5 py-3 font-modern text-sm font-semibold text-white transition-transform group-hover:translate-x-1 sm:self-auto">
                    Ver planes <x-icono nombre="arrow-right" class="text-xs" />
                </span>
            </a>
        </div>
    </section>

    {{-- EN EL TELÉFONO, EL BOTÓN DE ESCRIBIRLE SIEMPRE A MANO: al bajar a
         leer, el de arriba quedaba fuera de la pantalla. --}}
    @if($e['whatsapp'])
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-pg-tiza/10 bg-pg-negro/95 p-3 backdrop-blur-sm lg:hidden">
            <a href="{{ $e['whatsapp'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
               class="flex items-center justify-center gap-2.5 bg-[#25D366] py-3.5 font-modern text-sm font-semibold text-pg-negro">
                <x-icono nombre="whatsapp" class="text-lg" /> Escribirle a {{ \Illuminate\Support\Str::before($e['nombre'], ' ') }}
            </a>
        </div>
    @endif

    @include('landing.partes.llamado')
@endsection
