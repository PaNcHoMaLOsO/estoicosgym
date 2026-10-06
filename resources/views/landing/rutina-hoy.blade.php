@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    TU ENTRENAMIENTO DE HOY: lo que sale de las cuatro preguntas, compacto
    para que en el celular se vea casi todo sin deslizar. Arriba el día con
    su mapa chico; el calentamiento con banda y los estiramientos en una fila
    cada uno (los nombres a la vista, el cómo dentro de un <details>); y los
    ejercicios en una lista de filas, cada una con su cómo y sus otras
    opciones dentro de un <details> cerrado. Sin JavaScript.

    El día es uno de la rutina que le corresponde (o uno armado con el
    catálogo). «Estirar y movilidad» es solo movilidad y estiramientos. Las
    respuestas van en la dirección: se puede volver y compartir.
--}}
@php
    $e = $entrenamiento;
    $rutina = $e['rutina'];
    $movilidad = $e['grupo'] === 'movilidad';
    $conBanda = count(array_filter($e['calentamiento'], fn ($x) => $x['banda']));
    $hice = $respuestas['hice'] ?: ['nada'];
    $fila = 'flex cursor-pointer list-none items-start gap-3 [&::-webkit-details-marker]:hidden';
    $titulo = 'font-display text-xl uppercase leading-none text-pg-tiza sm:text-2xl';
    $detalle = 'mt-1 block font-modern text-xs font-normal normal-case text-pg-tiza/55 sm:text-sm';
    $chip = 'rounded-full bg-pg-grafito px-2.5 py-1 text-xs text-pg-tiza/80';
    // En pantalla grande, los ejercicios a la izquierda y lo demás a la derecha.
    $lado = 'lg:col-start-2';
    // «4 × 6-8 por pierna»: lo de «por pierna» va abajo, chico, para no apretar la fila.
    $partir = fn (string $corta) => preg_match('/^(.+?) (por (?:pierna|brazo|lado))$/u', $corta, $m) ? [$m[1], $m[2]] : [$corta, null];
    $flecha = 'mt-0.5 shrink-0 text-xs text-pg-tiza/40 transition-transform group-open:rotate-90';
@endphp

@section('content')
    <section class="bg-pg-negro pb-16 pt-20 lg:pt-32">
        <div class="mx-auto max-w-6xl px-4 sm:px-8">

            {{-- El día, en una franja baja con su mapa chico. --}}
            <header class="pg-sube flex items-center gap-4">
                <div class="min-w-0 flex-1">
                    <p class="font-modern text-xs uppercase tracking-widest text-pg-rojo-claro">Tu entrenamiento de hoy</p>
                    <h1 class="mt-1 font-display text-4xl uppercase leading-none text-pg-tiza sm:text-5xl lg:text-6xl">{{ $e['titulo'] }}</h1>
                    <p class="mt-2 font-modern text-sm text-pg-tiza/60 sm:text-base">
                        @if($movilidad)
                            Unos {{ $e['minutos'] }} minutos
                        @else
                            {{ count($e['lineas']) }} ejercicios
                        @endif
                    </p>
                </div>
                <x-mapa-muscular :principal="$e['principales']" :secundarios="$e['secundarios']" class="h-24 w-28 shrink-0 sm:h-32 sm:w-36 lg:h-40 lg:w-44" />
            </header>

            <div class="mt-6 lg:mt-10 lg:grid lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start lg:gap-x-12">

            {{-- Calentamiento: una fila con los nombres; el cómo, al tocar. --}}
            <details class="group mt-5 lg:mt-0 lg:rounded-xl lg:bg-pg-grafito/40 lg:p-5 {{ $lado }} lg:row-start-1" aria-labelledby="calentamiento">
                <summary class="{{ $fila }}">
                    <div class="min-w-0 flex-1">
                        <h2 id="calentamiento" class="{{ $titulo }}">
                            Calentamiento
                            <span class="{{ $detalle }}">5 min cardio{{ $conBanda ? " + {$conBanda} con banda" : ' suave' }}</span>
                        </h2>
                        @if($conBanda)
                            <ul class="mt-1.5 flex flex-wrap gap-1 font-modern">
                                @foreach($e['calentamiento'] as $x)
                                    @continue(! $x['banda'])
                                    <li class="{{ $chip }}">{{ $x['nombre'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <x-icono nombre="chevron-right" class="{{ $flecha }}" />
                </summary>
                <ul class="mt-4 space-y-4">
                    @foreach($e['calentamiento'] as $x)
                        @include('landing.partes.ejercicio-liviano', ['x' => $x])
                    @endforeach
                </ul>
            </details>

            @if($movilidad)
                <details class="group mt-5 lg:col-start-1 lg:row-span-3 lg:row-start-1 lg:mt-0" open>
                    <summary class="{{ $fila }}">
                        <h2 id="movilidad" class="min-w-0 flex-1 {{ $titulo }}">
                            Movilidad <span class="{{ $detalle }}">lento y sin dolor</span>
                        </h2>
                        <x-icono nombre="chevron-right" class="{{ $flecha }}" />
                    </summary>
                    <ul class="mt-4 space-y-4">
                        @foreach($e['movilidad'] as $x)
                            @include('landing.partes.ejercicio-liviano', ['x' => $x])
                        @endforeach
                    </ul>
                </details>
            @else
                {{-- Los ejercicios: una fila cada uno; el cómo y las otras opciones, al tocar. --}}
                <section class="mt-6 lg:col-start-1 lg:row-span-3 lg:row-start-1 lg:mt-0" aria-labelledby="entrenamiento">
                    <h2 id="entrenamiento" class="{{ $titulo }}">Entrenamiento</h2>

                    <ol class="mt-3 grid grid-cols-1 gap-2">
                        @foreach($e['lineas'] as $i => $l)
                            <li class="pg-sube" style="animation-delay: {{ 120 + $i * 60 }}ms">
                                <details class="group rounded-xl bg-pg-grafito/40 transition-colors hover:bg-pg-grafito/70 open:bg-pg-grafito/70">
                                    <summary class="flex cursor-pointer list-none items-center gap-3 p-2.5 sm:gap-4 sm:p-3 [&::-webkit-details-marker]:hidden">
                                        <div class="relative">
                                            @include('landing.partes.imagen-ejercicio', ['e' => $l, 'tamano' => 'size-20 sm:size-24'])
                                            <span class="absolute -left-1.5 -top-1.5 grid size-6 place-items-center rounded-full bg-pg-rojo font-display text-sm text-white">{{ $i + 1 }}</span>
                                        </div>
                                        <div class="min-w-0 flex-1 font-modern">
                                            <h3 class="text-base font-semibold leading-tight text-pg-tiza sm:text-lg">{{ $l['nombre'] }}</h3>
                                            <p class="mt-1 flex items-center gap-1 whitespace-nowrap text-xs text-pg-tiza/50 sm:text-sm">
                                                {{ ! empty($l['opciones']) ? 'Cómo y otras opciones' : 'Cómo se hace' }}
                                                <x-icono nombre="chevron-right" class="text-[10px] transition-transform group-open:rotate-90" />
                                            </p>
                                        </div>
                                        @php [$series, $porLado] = $partir($l['corta']); @endphp
                                        <div class="shrink-0 text-right">
                                            <p class="whitespace-nowrap font-display text-2xl leading-none tabular-nums text-pg-rojo-claro sm:text-3xl">{{ $series }}</p>
                                            <p class="mt-1 font-modern text-xs tabular-nums text-pg-tiza/55 sm:text-sm">
                                                {{ $porLado }}{{ $porLado && $l['descanso'] ? ' · ' : '' }}@if($l['descanso'])<span class="sr-only">Descanso </span>{{ $l['descanso'] }} s @endif
                                            </p>
                                        </div>
                                    </summary>

                                    <div class="px-3 pb-4 font-modern sm:px-4">
                                        <p class="text-sm leading-snug text-pg-tiza/75 sm:text-base">{{ $l['nota'] ?: $l['dosis'] }}</p>
                                        {{-- Otras opciones del mismo músculo, con su dibujo: para
                                             cambiarlo si la máquina está ocupada o no le acomoda. --}}
                                        @if(! empty($l['opciones']))
                                            <p class="mt-3 text-xs uppercase tracking-wider text-pg-tiza/45">Otras opciones</p>
                                            <ul class="mt-2 grid grid-cols-3 gap-2">
                                                @foreach($l['opciones'] as $o)
                                                    <li class="flex flex-col items-center gap-1.5 text-center text-xs text-pg-tiza/75 sm:text-sm">
                                                        @include('landing.partes.imagen-ejercicio', ['e' => $o, 'tamano' => 'size-16 sm:size-20'])
                                                        <span class="leading-tight">{{ $o['nombre'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </details>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- Para terminar: los músculos de hoy (o todo el cuerpo, de los pies a la cabeza). --}}
            <details class="group mt-5 lg:rounded-xl lg:bg-pg-grafito/40 lg:p-5 {{ $lado }} lg:row-start-2" @if($movilidad) open @endif>
                <summary class="{{ $fila }}">
                    <div class="min-w-0 flex-1">
                        <h2 id="estira" class="{{ $titulo }}">
                            {{ $movilidad ? 'Estira, de pies a cabeza' : 'Para terminar, estira' }}
                            <span class="{{ $detalle }}">{{ count($e['estiramientos']) }} de 30 s</span>
                        </h2>
                        <ul class="mt-1.5 flex flex-wrap gap-1 font-modern">
                            @foreach($e['estiramientos'] as $x)
                                <li class="{{ $chip }}">{{ $x['nombre'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <x-icono nombre="chevron-right" class="{{ $flecha }}" />
                </summary>
                <ul class="mt-4 space-y-4">
                    @foreach($e['estiramientos'] as $x)
                        @include('landing.partes.ejercicio-liviano', ['x' => $x])
                    @endforeach
                </ul>
            </details>

            <div class="mt-8 lg:mt-5 {{ $lado }} lg:row-start-3">
            <div class="flex gap-2 font-modern text-sm lg:flex-col">
                @if($rutina)
                    <a href="{{ route('landing.rutina.ver', $rutina->slug) }}" title="{{ $rutina->nombre }}"
                        class="flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-pg-rojo px-3 py-3 font-bold text-white transition-colors hover:bg-pg-rojo-oscuro">
                        Ver la semana <x-icono nombre="arrow-right" class="text-xs" />
                    </a>
                @endif
                <a href="{{ route('landing.rutina') }}"
                    class="flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-pg-grafito px-3 py-3 font-semibold text-pg-tiza transition-colors hover:bg-pg-gris">
                    <x-icono nombre="rotate-left" class="text-xs" /> Volver a empezar
                </a>
            </div>

            <p class="mt-5 flex flex-wrap justify-center gap-x-6 gap-y-2 font-modern text-sm">
                <a href="{{ route('landing.rutina', ['dias' => $respuestas['dias'], 'nivel' => $respuestas['nivel'], 'hice' => $hice, 'hoy' => $respuestas['hoy'], 'cambiar' => 1]) }}" class="text-pg-tiza/60 underline underline-offset-4 hover:text-pg-tiza">Elegir otra cosa para hoy</a>
                <a href="{{ route('landing.rutinas') }}" class="text-pg-tiza/60 underline underline-offset-4 hover:text-pg-tiza">Ver todas las rutinas</a>
            </p>
            </div>
            </div>

            <p class="mt-10 max-w-3xl font-modern text-xs leading-relaxed text-pg-tiza/45">
                Es una guía general de la sala, igual para todos, y no reemplaza a un profesional.
                Si tienes una lesión, estás embarazada, tomas medicamentos o tienes alguna condición de
                salud, consulta antes con tu médico. Usa un peso con el que puedas terminar todas las
                repeticiones con buena técnica y, si algo te duele, detente y pregunta en el mesón.
            </p>
        </div>
    </section>
@endsection
