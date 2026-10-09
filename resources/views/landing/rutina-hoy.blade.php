@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])
@section('sin-flotantes', '1')

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

    QUE NO SE PIERDA SI SE CIERRA EL NAVEGADOR: el celular guarda la dirección
    del entrenamiento de hoy (y lo que va marcando como hecho) en su propio
    almacenamiento, sin mandar nada al servidor. Al volver a «Qué entrenar
    hoy» se ofrece seguir con él. Y se lo puede mandar por WhatsApp a sí mismo.
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

    // Para mandárselo por WhatsApp: la lista entera en el mensaje (se lee sin
    // abrir nada) y el enlace para volver a verlo con los dibujos.
    $lista = $movilidad
        ? collect($e['movilidad'])->pluck('nombre')->map(fn ($n, $i) => ($i + 1) . '. ' . $n)
        : collect($e['lineas'])->map(fn ($l, $i) => ($i + 1) . '. ' . $l['nombre'] . ' · ' . $l['corta']);
    $paraWhatsapp = 'https://wa.me/?text=' . rawurlencode(
        "Mi entrenamiento de hoy en {$gimnasio['nombre']}: {$e['titulo']}\n\n" . $lista->implode("\n") . "\n\n" . url()->full()
    );
@endphp

@section('content')
    <section class="bg-pg-negro pb-16 pt-20 lg:pt-32" data-entrenamiento data-titulo="{{ $e['titulo'] }}" data-dias="{{ $respuestas['dias'] }}" data-nivel="{{ $respuestas['nivel'] }}">
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
                            {{ count($e['lineas']) }} ejercicios<span data-avance hidden class="text-pg-rojo-claro"></span>
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
                            <li class="pg-sube group/hecho" style="animation-delay: {{ 120 + $i * 60 }}ms" data-linea="{{ $i }}">
                                <details class="group rounded-xl bg-pg-grafito/40 transition-colors hover:bg-pg-grafito/70 open:bg-pg-grafito/70">
                                    <summary class="flex cursor-pointer list-none items-center gap-3 p-2.5 sm:gap-4 sm:p-3 [&::-webkit-details-marker]:hidden">
                                        <div class="relative transition-opacity group-data-hecho/hecho:opacity-40">
                                            @include('landing.partes.imagen-ejercicio', ['e' => $l, 'tamano' => 'size-20 sm:size-24'])
                                            <span class="absolute -left-1.5 -top-1.5 grid size-6 place-items-center rounded-full bg-pg-rojo font-display text-sm text-white">{{ $i + 1 }}</span>
                                        </div>
                                        <div class="min-w-0 flex-1 font-modern transition-opacity group-data-hecho/hecho:opacity-40">
                                            <h3 class="text-base font-semibold leading-tight text-pg-tiza sm:text-lg">{{ $l['nombre'] }}</h3>
                                            <p class="mt-1 flex items-center gap-1 whitespace-nowrap text-xs text-pg-tiza/50 sm:text-sm">
                                                {{ ! empty($l['opciones']) ? 'Cómo y otras opciones' : 'Cómo se hace' }}
                                                <x-icono nombre="chevron-right" class="text-[10px] transition-transform group-open:rotate-90" />
                                            </p>
                                        </div>
                                        @php [$series, $porLado] = $partir($l['corta']); @endphp
                                        <div class="shrink-0 text-right transition-opacity group-data-hecho/hecho:opacity-40">
                                            <p class="whitespace-nowrap font-display text-2xl leading-none tabular-nums text-pg-rojo-claro sm:text-3xl">{{ $series }}</p>
                                            <p class="mt-1 font-modern text-xs tabular-nums text-pg-tiza/55 sm:text-sm">
                                                {{ $porLado }}{{ $porLado && $l['descanso'] ? ' · ' : '' }}@if($l['descanso'])<span class="sr-only">Descanso </span>{{ $l['descanso'] }} s @endif
                                            </p>
                                        </div>
                                        {{-- Marcarlo hecho: queda guardado en el celular. Sin JavaScript no sale. --}}
                                        <button type="button" data-marcar hidden aria-pressed="false" aria-label="Marcar {{ $l['nombre'] }} como hecho"
                                            class="-mr-0.5 grid size-8 shrink-0 place-items-center rounded-full border-2 border-pg-tiza/25 text-sm text-transparent transition-colors hover:border-pg-tiza/60 group-data-hecho/hecho:border-[#25D366] group-data-hecho/hecho:bg-[#25D366] group-data-hecho/hecho:text-pg-negro sm:size-10">
                                            <x-icono nombre="circle-check" />
                                        </button>
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
                <a href="{{ route('landing.rutina', ['nuevo' => 1]) }}"
                    class="flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-pg-grafito px-3 py-3 font-semibold text-pg-tiza transition-colors hover:bg-pg-gris">
                    <x-icono nombre="rotate-left" class="text-xs" /> Volver a empezar
                </a>
            </div>

            <a href="{{ $paraWhatsapp }}" target="_blank" rel="noopener" data-evento="rutina_whatsapp" data-detalle="{{ $e['titulo'] }}"
                class="mt-2 flex items-center justify-center gap-2 rounded-lg border border-[#25D366]/50 px-3 py-3 font-modern text-sm font-semibold text-pg-tiza transition-colors hover:bg-[#25D366] hover:text-pg-negro">
                <x-icono nombre="whatsapp" class="text-base" /> Mandármela por WhatsApp
            </a>

            <p class="mt-5 flex flex-wrap justify-center gap-x-6 gap-y-2 font-modern text-sm">
                <a href="{{ route('landing.rutina', ['dias' => $respuestas['dias'], 'nivel' => $respuestas['nivel'], 'hice' => $hice, 'hoy' => $respuestas['hoy'], 'v' => $respuestas['v'] ?? null, 'cambiar' => 1]) }}" class="text-pg-tiza/60 underline underline-offset-4 hover:text-pg-tiza">Elegir otra cosa para hoy</a>
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

    <script>
        (function () {
            const raiz = document.querySelector('[data-entrenamiento]');
            const clave = 'pg-rutina';
            const hoy = new Date().toLocaleDateString('sv');
            const direccion = location.pathname + location.search;
            const lineas = Array.from(document.querySelectorAll('[data-linea]'));
            const avance = document.querySelector('[data-avance]');
            let guardado = {};

            // Sin almacenamiento (incógnito, bloqueado) la página anda igual.
            try {
                guardado = JSON.parse(localStorage.getItem(clave) || '{}') || {};
            } catch (e) {}

            if (guardado.direccion !== direccion || guardado.fecha !== hoy) {
                guardado = { direccion: direccion, fecha: hoy, hechos: [] };
            }

            Object.assign(guardado, {
                titulo: raiz.dataset.titulo,
                dias: raiz.dataset.dias,
                nivel: raiz.dataset.nivel,
                total: lineas.length,
            });

            function guardar() {
                try {
                    localStorage.setItem(clave, JSON.stringify(guardado));
                } catch (e) {}
            }

            function pintar() {
                lineas.forEach((li) => {
                    const hecho = guardado.hechos.includes(Number(li.dataset.linea));
                    li.toggleAttribute('data-hecho', hecho);
                    li.querySelector('[data-marcar]').setAttribute('aria-pressed', hecho ? 'true' : 'false');
                });

                if (avance && guardado.hechos.length) {
                    avance.hidden = false;
                    avance.textContent = guardado.hechos.length === lineas.length
                        ? ' · ¡Listo por hoy!'
                        : ' · ' + guardado.hechos.length + ' hechos';
                } else if (avance) {
                    avance.hidden = true;
                }
            }

            lineas.forEach((li) => {
                const boton = li.querySelector('[data-marcar]');
                boton.hidden = false;
                // Dentro del resumen: sin esto, tocarlo también abriría la fila.
                boton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const i = Number(li.dataset.linea);
                    guardado.hechos = guardado.hechos.includes(i)
                        ? guardado.hechos.filter((x) => x !== i)
                        : [...guardado.hechos, i];
                    guardar();
                    pintar();
                });
            });

            guardar();
            pintar();
        })();
    </script>
@endsection
