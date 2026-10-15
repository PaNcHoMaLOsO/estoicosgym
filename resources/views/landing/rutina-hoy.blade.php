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

    PARA USARLO EN LA SALA (15-oct-2026), con el celular en la mano:
    - el círculo de cada ejercicio cuenta las series: al tocarlo suma una y
      corre el descanso en una barra abajo (vibra al terminar, se le pueden
      sumar 15 s, saltar o deshacer el toque);
    - el peso que usó queda anotado en el celular y se ve la próxima vez;
    - si la máquina está ocupada, «Usar este» cambia el ejercicio por una de
      sus otras opciones;
    - mientras entrena, la pantalla no se apaga.
    Todo eso es JavaScript sobre la misma página: sin él se ve como antes.

    QUE NO SE PIERDA SI SE CIERRA EL NAVEGADOR: el celular guarda la dirección
    del entrenamiento de hoy (y lo que va marcando como hecho) en su propio
    almacenamiento, sin mandar nada al servidor. Al volver a «Qué entrenar
    hoy» se ofrece seguir con él. Y se lo puede mandar por WhatsApp a sí mismo.
--}}
@php
    $e = $entrenamiento;
    $rutina = $e['rutina'];
    $movilidad = $e['grupo'] === 'movilidad';
    // El calentamiento: la movilidad articular (igual siempre) y los dinámicos de hoy.
    $articular = array_values(array_filter($e['calentamiento'], fn ($x) => ($x['tipo'] ?? null) === 'articular'));
    $dinamicos = array_values(array_filter($e['calentamiento'], fn ($x) => ($x['tipo'] ?? null) === 'dinamico'));
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
    <section class="bg-pg-negro pb-16 pt-20 lg:pt-32" data-entrenamiento data-titulo="{{ $e['titulo'] }}" data-dias="{{ $respuestas['dias'] }}" data-nivel="{{ $respuestas['nivel'] }}" data-busco="{{ $respuestas['busco'] ?? '' }}">
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
                            {{ count($e['lineas']) }} ejercicios @if($e['minutos'])· ≈ {{ $e['minutos'] }} min @endif<span data-avance hidden class="text-pg-rojo-claro"></span>
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
                            <span class="{{ $detalle }}">Movilidad articular{{ count($dinamicos) ? ' y ' . count($dinamicos) . ' dinámicos' : '' }} · unos {{ count($dinamicos) ? 6 : 3 }} min</span>
                        </h2>
                        @if(count($dinamicos))
                            <ul class="mt-1.5 flex flex-wrap gap-1 font-modern">
                                @foreach($dinamicos as $x)
                                    <li class="{{ $chip }}">{{ $x['nombre'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <x-icono nombre="chevron-right" class="{{ $flecha }}" />
                </summary>
                {{-- MOVILIDAD ARTICULAR: cada articulación en círculos, de
                     arriba abajo. Corta, en una lista: se hace de memoria. --}}
                <h3 class="mt-4 font-modern text-xs uppercase tracking-widest text-pg-tiza/45">Movilidad articular · de arriba abajo</h3>
                <ol class="mt-2 divide-y divide-pg-tiza/10 font-modern">
                    @foreach($articular as $x)
                        <li class="flex gap-3 py-2">
                            <span class="w-24 shrink-0 font-semibold text-pg-tiza sm:w-32">{{ $x['nombre'] }}</span>
                            <span class="min-w-0 flex-1 text-sm leading-snug text-pg-tiza/65">{{ $x['como'] }} <span class="whitespace-nowrap font-semibold text-pg-rojo-claro">{{ $x['dosis'] }}</span></span>
                        </li>
                    @endforeach
                </ol>
                @if(count($dinamicos))
                    <h3 class="mt-5 font-modern text-xs uppercase tracking-widest text-pg-tiza/45">Dinámicos · para lo de hoy</h3>
                    <ul class="mt-3 space-y-4">
                        @foreach($dinamicos as $x)
                            @include('landing.partes.ejercicio-liviano', ['x' => $x])
                        @endforeach
                    </ul>
                @endif
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
                    <p data-pista hidden class="mt-1.5 flex items-center gap-2 font-modern text-xs text-pg-tiza/60 sm:text-sm">
                        <x-icono nombre="stopwatch" class="text-pg-rojo-claro" /> Toca el círculo al terminar cada serie y corre el descanso.
                    </p>

                    <ol class="mt-3 grid grid-cols-1 gap-2">
                        @foreach($e['lineas'] as $i => $l)
                            <li class="pg-sube group/hecho" style="animation-delay: {{ 120 + $i * 60 }}ms" data-linea="{{ $i }}">
                                <details class="group rounded-xl bg-pg-grafito/40 transition-colors hover:bg-pg-grafito/70 open:bg-pg-grafito/70">
                                    <summary class="flex cursor-pointer list-none items-center gap-3 p-2.5 sm:gap-4 sm:p-3 [&::-webkit-details-marker]:hidden">
                                        <div class="relative transition-opacity group-data-hecho/hecho:opacity-40">
                                            @include('landing.partes.imagen-ejercicio', ['e' => $l, 'tamano' => 'size-20 sm:size-24', 'marca' => true])
                                            <span class="absolute -left-1.5 -top-1.5 grid size-6 place-items-center rounded-full bg-pg-rojo font-display text-sm text-white">{{ $i + 1 }}</span>
                                        </div>
                                        <div class="min-w-0 flex-1 font-modern transition-opacity group-data-hecho/hecho:opacity-40">
                                            <h3 data-nombre class="text-base font-semibold leading-tight text-pg-tiza sm:text-lg">{{ $l['nombre'] }}</h3>
                                            <p class="mt-1 flex items-center gap-1 whitespace-nowrap text-xs text-pg-tiza/50 sm:text-sm">
                                                {{ ! empty($l['opciones']) ? 'Cómo y otras opciones' : 'Cómo se hace' }}
                                                <x-icono nombre="chevron-right" class="text-[11px] transition-transform group-open:rotate-90" />
                                            </p>
                                        </div>
                                        @php [$series, $porLado] = $partir($l['corta']); @endphp
                                        <div class="shrink-0 text-right transition-opacity group-data-hecho/hecho:opacity-40">
                                            <p class="whitespace-nowrap font-display text-2xl leading-none tabular-nums text-pg-rojo-claro sm:text-3xl">{{ $series }}</p>
                                            <p class="mt-1 font-modern text-xs tabular-nums text-pg-tiza/55 sm:text-sm">
                                                {{ $porLado }}{{ $porLado && $l['descanso'] ? ' · ' : '' }}@if($l['descanso'])<span class="sr-only">Descanso </span>{{ $l['descanso'] }} s @endif
                                            </p>
                                            <p data-peso-fila hidden class="mt-0.5 font-modern text-xs font-semibold tabular-nums text-pg-tiza/80 sm:text-sm"></p>
                                        </div>
                                        {{-- Las series: cada toque suma una y corre el descanso;
                                             queda guardado en el celular. Sin JavaScript no sale. --}}
                                        <button type="button" data-marcar hidden data-series="{{ max(1, (int) $l['series']) }}" data-descanso="{{ (int) $l['descanso'] }}" aria-label="Serie hecha de {{ $l['nombre'] }}"
                                            class="relative -mr-1 grid size-12 shrink-0 place-items-center rounded-full text-pg-tiza transition-transform active:scale-90">
                                            <svg viewBox="0 0 36 36" class="absolute inset-1 -rotate-90" aria-hidden="true">
                                                <circle cx="18" cy="18" r="16" fill="none" stroke="currentColor" stroke-width="2.5" class="text-pg-tiza/20" />
                                                <circle data-arco cx="18" cy="18" r="16" fill="none" stroke="#25D366" stroke-width="3" stroke-linecap="round" pathLength="100" stroke-dasharray="0 100" />
                                            </svg>
                                            <span data-cuenta class="relative font-display text-sm tabular-nums" aria-hidden="true"></span>
                                            <span class="absolute inset-1.5 hidden place-items-center rounded-full bg-[#25D366] text-lg text-pg-negro group-data-hecho/hecho:grid" aria-hidden="true"><x-icono nombre="circle-check" /></span>
                                        </button>
                                    </summary>

                                    <div class="px-3 pb-4 font-modern sm:px-4">
                                        <p data-nota class="text-sm leading-snug text-pg-tiza/75 sm:text-base">{{ $l['nota'] ?: $l['dosis'] }}</p>
                                        @if(! in_array($l['zona'] ?? null, ['cardio'], true))
                                            <div data-peso hidden class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1">
                                                <label class="flex items-center gap-2 text-sm text-pg-tiza/75">
                                                    Peso que usaste
                                                    <input type="number" inputmode="decimal" min="0" max="500" step="0.5" data-peso-kg
                                                        class="w-20 rounded-lg border border-pg-tiza/20 bg-pg-negro px-2.5 py-1.5 text-center text-base tabular-nums text-pg-tiza focus:border-pg-rojo focus:outline-none">
                                                    kg
                                                </label>
                                                <span data-peso-antes class="text-xs text-pg-tiza/50"></span>
                                            </div>
                                        @endif
                                        {{-- Otras opciones del mismo músculo, con su dibujo: para
                                             cambiarlo si la máquina está ocupada o no le acomoda. --}}
                                        @if(! empty($l['opciones']))
                                            <p class="mt-3 text-xs uppercase tracking-wider text-pg-tiza/45">Otras opciones</p>
                                            <ul class="mt-2 grid grid-cols-3 gap-2">
                                                @foreach($l['opciones'] as $o)
                                                    <li data-opcion="{{ $loop->index }}" class="flex flex-col items-center gap-1.5 text-center text-xs text-pg-tiza/75 sm:text-sm">
                                                        @include('landing.partes.imagen-ejercicio', ['e' => $o, 'tamano' => 'size-16 sm:size-20', 'marca' => true])
                                                        <span data-nombre class="leading-tight">{{ $o['nombre'] }}</span>
                                                        <span data-nota hidden>{{ $o['nota'] ?? '' }}</span>
                                                        {{-- Si la máquina está ocupada: cambiarlo ahí mismo. --}}
                                                        <button type="button" data-usar hidden class="rounded-full border border-pg-tiza/25 px-3 py-1.5 font-semibold text-pg-tiza transition-colors hover:border-pg-tiza/60 active:bg-pg-grafito">Usar este</button>
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
                <a href="{{ route('landing.rutina', ['dias' => $respuestas['dias'], 'nivel' => $respuestas['nivel'], 'busco' => $respuestas['busco'] ?? null, 'hice' => $hice, 'hoy' => $respuestas['hoy'], 'v' => $respuestas['v'] ?? null, 'cambiar' => 1]) }}" class="text-pg-tiza/60 underline underline-offset-4 hover:text-pg-tiza">Elegir otra cosa para hoy</a>
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

    {{-- El descanso: abajo, encima de todo, mientras corre. --}}
    <div data-reloj hidden role="status" aria-live="polite"
        class="fixed inset-x-0 bottom-0 z-40 border-t border-pg-tiza/10 bg-pg-negro/95 px-4 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] pt-3 backdrop-blur">
        <div data-reloj-barrita class="absolute left-0 top-0 h-1 bg-pg-rojo" style="width: 100%"></div>
        <div class="mx-auto flex max-w-xl items-center gap-3 font-modern">
            <div class="min-w-0 flex-1">
                <p data-reloj-que class="truncate text-xs text-pg-tiza/60"></p>
                <p class="flex items-baseline gap-3">
                    <span data-reloj-tiempo class="font-display text-4xl leading-none tabular-nums text-pg-tiza"></span>
                    <button type="button" data-reloj-deshacer class="text-xs text-pg-tiza/50 underline underline-offset-4">Deshacer</button>
                </p>
            </div>
            <button type="button" data-reloj-mas class="rounded-lg bg-pg-grafito px-3 py-3 text-sm font-semibold text-pg-tiza active:bg-pg-gris">+15 s</button>
            <button type="button" data-reloj-saltar class="rounded-lg bg-pg-rojo px-4 py-3 text-sm font-bold text-white active:bg-pg-rojo-oscuro">Saltar</button>
        </div>
    </div>

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
            // Las series de cada ejercicio y los ejercicios cambiados. Lo
            // guardado antes de las series: un ejercicio hecho, todas hechas.
            guardado.series = guardado.series || {};
            guardado.cambios = guardado.cambios || [];
            (guardado.hechos || []).forEach((i) => {
                const li = lineas[i];
                if (li && guardado.series[i] === undefined) {
                    guardado.series[i] = Number(li.querySelector('[data-marcar]').dataset.series);
                }
            });

            Object.assign(guardado, {
                titulo: raiz.dataset.titulo,
                dias: raiz.dataset.dias,
                nivel: raiz.dataset.nivel,
                busco: raiz.dataset.busco,
                total: lineas.length,
            });

            function guardar() {
                guardado.hechos = lineas.map((li, i) => i).filter((i) => hechas(i) >= totales(i));
                try {
                    localStorage.setItem(clave, JSON.stringify(guardado));
                } catch (e) {}
            }

            const boton = (i) => lineas[i].querySelector('[data-marcar]');
            const totales = (i) => Number(boton(i).dataset.series);
            const hechas = (i) => Math.min(guardado.series[i] || 0, totales(i));
            const nombre = (i) => lineas[i].querySelector('summary [data-nombre]').textContent.trim();

            function pintar() {
                lineas.forEach((li, i) => {
                    const n = hechas(i);
                    const total = totales(i);
                    const hecho = n >= total;
                    li.toggleAttribute('data-hecho', hecho);
                    const arco = li.querySelector('[data-arco]');
                    arco.setAttribute('stroke-dasharray', (n / total * 100) + ' 100');
                    // En cero, ni el punto de la punta redonda.
                    arco.style.opacity = n ? 1 : 0;
                    li.querySelector('[data-cuenta]').textContent = n && ! hecho ? n + '/' + total : '';
                    boton(i).setAttribute('aria-label', hecho
                        ? nombre(i) + ': hecho. Tocar para empezar de nuevo'
                        : nombre(i) + ': serie ' + (n + 1) + ' de ' + total + ' hecha');
                });

                const listos = guardado.hechos.length;
                if (avance && listos) {
                    avance.hidden = false;
                    avance.textContent = listos === lineas.length ? ' · ¡Listo por hoy!' : ' · ' + listos + ' de ' + lineas.length;
                } else if (avance) {
                    avance.hidden = true;
                }
            }

            // ---- La pantalla encendida mientras entrena.
            let pantalla = null;
            async function noApagar() {
                if (! ('wakeLock' in navigator) || pantalla || document.visibilityState !== 'visible') {
                    return;
                }
                try {
                    pantalla = await navigator.wakeLock.request('screen');
                    pantalla.addEventListener('release', () => { pantalla = null; });
                } catch (e) {}
            }
            document.addEventListener('visibilitychange', () => {
                if (Object.keys(guardado.series).length && guardado.hechos.length < lineas.length) {
                    noApagar();
                }
            });

            // ---- El descanso.
            const reloj = document.querySelector('[data-reloj]');
            const parte = (x) => reloj.querySelector('[data-reloj-' + x + ']');
            let tic = null;
            let deshacer = null;

            const mmss = (seg) => Math.floor(seg / 60) + ':' + String(seg % 60).padStart(2, '0');

            function mostrarReloj(si) {
                reloj.hidden = ! si;
                document.body.style.paddingBottom = si ? reloj.offsetHeight + 'px' : '';
            }

            function correr() {
                clearInterval(tic);
                const d = guardado.descanso;
                if (! d) {
                    mostrarReloj(false);
                    return;
                }
                parte('que').textContent = d.que;
                parte('deshacer').hidden = deshacer === null;
                parte('mas').hidden = false;
                parte('saltar').textContent = 'Saltar';
                mostrarReloj(true);

                const paso = () => {
                    const quedan = Math.max(0, Math.ceil((d.fin - Date.now()) / 1000));
                    parte('tiempo').textContent = mmss(quedan);
                    parte('barrita').style.width = (quedan / d.largo * 100) + '%';
                    if (quedan === 0) {
                        terminar(true);
                    }
                };
                paso();
                tic = setInterval(paso, 250);
            }

            function terminar(sonar) {
                clearInterval(tic);
                const d = guardado.descanso;
                guardado.descanso = null;
                guardar();
                if (! sonar || ! d) {
                    mostrarReloj(false);
                    return;
                }
                if (navigator.vibrate) {
                    navigator.vibrate([250, 120, 250]);
                }
                parte('que').textContent = 'Se acabó el descanso';
                parte('tiempo').textContent = d.luego;
                parte('barrita').style.width = '0%';
                parte('mas').hidden = true;
                parte('deshacer').hidden = true;
                parte('saltar').textContent = 'Listo';
                mostrarReloj(true);
                tic = setTimeout(() => mostrarReloj(false), 6000);
            }

            parte('mas').addEventListener('click', () => {
                if (guardado.descanso) {
                    guardado.descanso.fin = Math.max(guardado.descanso.fin, Date.now()) + 15000;
                    guardado.descanso.largo += 15;
                    guardar();
                    correr();
                }
            });
            parte('saltar').addEventListener('click', () => terminar(false));
            parte('deshacer').addEventListener('click', () => {
                if (deshacer !== null) {
                    guardado.series[deshacer] = Math.max(0, hechas(deshacer) - 1);
                    deshacer = null;
                    terminar(false);
                    pintar();
                }
            });

            // ---- Las series: cada toque suma una y corre el descanso.
            lineas.forEach((li, i) => {
                const b = boton(i);
                b.hidden = false;
                // Dentro del resumen: sin esto, tocarlo también abriría la fila.
                b.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const total = totales(i);
                    const n = hechas(i);

                    if (n >= total) {
                        // Ya estaba hecho: se empieza de nuevo.
                        guardado.series[i] = 0;
                        if (guardado.descanso && guardado.descanso.i === i) {
                            guardado.descanso = null;
                        }
                        deshacer = null;
                        guardar();
                        pintar();
                        correr();
                        return;
                    }

                    guardado.series[i] = n + 1;
                    pista.hidden = true;
                    try {
                        localStorage.setItem('pg-pista', '1');
                    } catch (e) {}
                    deshacer = i;
                    guardar();
                    pintar();
                    noApagar();

                    if (guardado.hechos.length === lineas.length) {
                        // El último: nada de descanso.
                        guardado.descanso = null;
                        deshacer = null;
                        guardar();
                        terminarElDia();
                        return;
                    }

                    const largo = Number(b.dataset.descanso) || 60;
                    const siguiente = lineas.findIndex((x, k) => k > i && hechas(k) < totales(k));
                    const otro = siguiente >= 0 ? siguiente : lineas.findIndex((x, k) => hechas(k) < totales(k));
                    guardado.descanso = {
                        i: i,
                        fin: Date.now() + largo * 1000,
                        largo: largo,
                        que: nombre(i) + ' · serie ' + (n + 1) + ' de ' + total,
                        luego: n + 1 < total ? 'Serie ' + (n + 2) + ' de ' + total : (otro >= 0 ? 'Sigue: ' + nombre(otro) : '¡Listo!'),
                    };
                    guardar();
                    correr();
                });
            });

            function terminarElDia() {
                clearInterval(tic);
                if (navigator.vibrate) {
                    navigator.vibrate([150, 80, 150, 80, 300]);
                }
                if (pantalla) {
                    pantalla.release().catch(() => {});
                }
                parte('que').textContent = '¡Listo por hoy!';
                parte('tiempo').textContent = 'Ahora, a estirar';
                parte('barrita').style.width = '0%';
                parte('mas').hidden = true;
                parte('deshacer').hidden = true;
                parte('saltar').textContent = 'Ver';
                mostrarReloj(true);
                parte('saltar').addEventListener('click', () => {
                    const estira = document.getElementById('estira');
                    if (estira) {
                        estira.closest('details').open = true;
                        estira.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, { once: true });
            }

            // ---- La pista, hasta el primer toque.
            const pista = document.querySelector('[data-pista]');
            if (pista) {
                try {
                    pista.hidden = localStorage.getItem('pg-pista') === '1';
                } catch (e) {
                    pista.hidden = false;
                }
            }

            // ---- El peso: queda en el celular por nombre de ejercicio, de un día al otro.
            let pesos = {};
            try {
                pesos = JSON.parse(localStorage.getItem('pg-pesos') || '{}') || {};
            } catch (e) {}
            const guardarPesos = () => {
                try {
                    localStorage.setItem('pg-pesos', JSON.stringify(pesos));
                } catch (e) {}
            };
            const fecha = (f) => new Date(f + 'T12:00').toLocaleDateString('es-CL', { day: 'numeric', month: 'short' });

            function pintarPeso(li) {
                const caja = li.querySelector('[data-peso]');
                const fila = li.querySelector('[data-peso-fila]');
                if (! caja) {
                    return;
                }
                const p = pesos[nombre(lineas.indexOf(li))];
                const deHoy = p && p.fecha === hoy ? p : null;
                const antes = p ? (p.fecha === hoy ? p.antes : p) : null;
                const input = caja.querySelector('[data-peso-kg]');
                caja.hidden = false;
                if (document.activeElement !== input) {
                    input.value = deHoy ? deHoy.kg : '';
                }
                input.placeholder = antes ? antes.kg : '';
                caja.querySelector('[data-peso-antes]').textContent = antes ? 'La vez pasada: ' + antes.kg + ' kg (' + fecha(antes.fecha) + ')' : '';
                const kg = deHoy ? deHoy.kg : (antes ? antes.kg : null);
                fila.hidden = kg === null;
                fila.textContent = kg === null ? '' : kg + ' kg';
            }

            lineas.forEach((li) => {
                const input = li.querySelector('[data-peso-kg]');
                if (! input) {
                    return;
                }
                input.addEventListener('change', () => {
                    const n = nombre(lineas.indexOf(li));
                    const kg = Math.round(parseFloat(String(input.value).replace(',', '.')) * 2) / 2;
                    const p = pesos[n];
                    const antes = p ? (p.fecha === hoy ? p.antes : { kg: p.kg, fecha: p.fecha }) : null;
                    if (! (kg > 0)) {
                        // Borrado: queda lo de la vez pasada.
                        if (antes) {
                            pesos[n] = antes;
                        } else {
                            delete pesos[n];
                        }
                    } else {
                        pesos[n] = { kg: kg, fecha: hoy, antes: antes };
                    }
                    guardarPesos();
                    pintarPeso(li);
                });
            });

            // ---- Cambiar el ejercicio por una de sus opciones: se intercambian
            // la foto, el nombre y el cómo. Se guarda como una lista de cambios
            // y al volver se repiten en orden.
            function cambiar(i, j) {
                const li = lineas[i];
                const op = li.querySelector('[data-opcion="' + j + '"]');
                const fila = li.querySelector('summary');
                [['[data-foto]'], ['[data-nombre]'], ['[data-nota]']].forEach(([sel]) => {
                    const a = fila.querySelector(sel) || li.querySelector('details > div > ' + sel);
                    const b = op.querySelector(sel);
                    const x = a.innerHTML;
                    a.innerHTML = b.innerHTML;
                    b.innerHTML = x;
                });
                // Si la opción no tiene cómo, que no quede vacío.
                const nota = li.querySelector('details > div > [data-nota]');
                if (! nota.textContent.trim()) {
                    nota.textContent = 'Mismo músculo, mismas series y repeticiones.';
                }
                pintarPeso(li);
            }

            lineas.forEach((li, i) => {
                li.querySelectorAll('[data-opcion]').forEach((op) => {
                    const usar = op.querySelector('[data-usar]');
                    usar.hidden = false;
                    usar.addEventListener('click', () => {
                        cambiar(i, op.dataset.opcion);
                        guardado.cambios.push([i, op.dataset.opcion]);
                        guardar();
                        pintar();
                        li.querySelector('summary').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    });
                });
            });

            guardado.cambios = guardado.cambios.filter(([i, j]) => lineas[i] && lineas[i].querySelector('[data-opcion="' + j + '"]'));
            guardado.cambios.forEach(([i, j]) => cambiar(i, j));
            lineas.forEach(pintarPeso);

            guardar();
            pintar();
            if (guardado.descanso && guardado.descanso.fin > Date.now()) {
                correr();
            } else {
                guardado.descanso = null;
            }
        })();
    </script>
@endsection
