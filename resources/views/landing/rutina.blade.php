@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    QUÉ ENTRENAR HOY: cuatro preguntas, una a la vez.

    Es un formulario GET común con las cuatro preguntas seguidas: sin
    JavaScript se contestan de arriba abajo y se envía. Con JavaScript se
    muestra una por vez, pasa sola a la siguiente al tocar una opción (en la
    tercera, que es de marcar varias, con «Seguir») y en la última marca lo
    que conviene según lo de estos días. No guarda nada.
--}}
@php
    use App\Support\EntrenamientoDeHoy as Hoy;

    $tarjeta = 'flex rounded-xl bg-pg-grafito text-pg-tiza transition duration-200 hover:bg-pg-gris/70 peer-checked:bg-pg-rojo/15 peer-checked:ring-2 peer-checked:ring-pg-rojo peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-pg-rojo-claro active:scale-[0.97]';
    $pregunta = 'mt-1 block font-display text-[1.7rem] uppercase leading-tight text-pg-tiza sm:text-5xl';
    $paso = 'block font-modern text-xs sm:text-sm uppercase tracking-widest text-pg-tiza/45';
    $seccion = 'col-span-4 mt-1 font-modern text-[10px] sm:mt-3 sm:text-xs uppercase tracking-widest text-pg-tiza/45 first:mt-0';
    // Las opciones de grupos, bajas y de a cuatro (una fila por sección):
    // cada pregunta cabe entera en la pantalla del celular.
    $baja = 'h-full flex-col items-center gap-1 px-1 py-2 text-center sm:gap-2 sm:px-2 sm:py-4';
    $nombreBajo = 'font-modern text-[11px] font-semibold leading-tight sm:text-sm';
    $atras = 'mb-3 inline-flex items-center gap-2 font-modern text-sm text-pg-tiza/60 transition-colors hover:text-pg-tiza';
    $iconos = ['nunca' => 'seedling', 'algo' => 'dumbbell', 'hace_tiempo' => 'medal'];
    $bajadas = ['nunca' => 'Primeras semanas', 'algo' => 'Algunos meses', 'hace_tiempo' => 'Varios años'];

    // null: todavía no contesta lo de estos días. Sin respuesta, «nada» queda
    // marcado: así, sin JavaScript, no marcar nada es haber descansado.
    $hice = $respuestas['hice'];
    $hechos = $hice ?? [];
    $sugerida = $hice !== null ? Hoy::sugerencia($hechos, $respuestas['dias'] ?? 3) : null;
    $hoyMarcado = $respuestas['hoy'] ?? $sugerida;
    $inicio = match (true) {
        $respuestas['dias'] === null => 0,
        $respuestas['nivel'] === null => 1,
        $hice === null => 2,
        default => 3,
    };
@endphp

@section('content')
    <section class="bg-pg-negro pb-16 pt-20 lg:pt-32">
        <div class="mx-auto max-w-3xl px-4 sm:px-8">
            {{-- El título de la página (para Google y los lectores de pantalla), con el aspecto de antes. --}}
            <h1 class="text-center font-modern text-sm uppercase tracking-widest text-pg-rojo-claro">Qué entrenar hoy</h1>

            {{-- La barra de los cuatro pasos: solo cuando van de a uno. --}}
            <div data-barra hidden class="mt-4 flex gap-1.5" aria-hidden="true">
                @for($i = 0; $i < 4; $i++)
                    <span class="h-1.5 flex-1 rounded-full bg-pg-tiza/15 transition-colors duration-500 data-lleno:bg-pg-rojo"></span>
                @endfor
            </div>

            <form method="GET" action="{{ route('landing.rutina') }}" data-preguntas data-inicio="{{ $inicio }}" data-reglas="{{ json_encode($reglas) }}" class="mt-6">

                {{-- 1. Días --}}
                <fieldset data-paso class="mb-14">
                    <legend class="w-full">
                        <span class="{{ $paso }}">1 de 4</span>
                        <span class="{{ $pregunta }}">¿Cuántos días entrenas a la semana?</span>
                    </legend>
                    <div class="mt-5 grid grid-cols-5 gap-2 sm:gap-3">
                        @foreach(Hoy::DIAS as $n)
                            <label data-opcion class="block cursor-pointer">
                                <input type="radio" name="dias" value="{{ $n }}" class="peer sr-only" @checked($respuestas['dias'] === $n) @if($loop->first) required @endif>
                                <span class="{{ $tarjeta }} h-24 flex-col items-center justify-center sm:h-36">
                                    <span class="font-display text-4xl leading-none sm:text-6xl">{{ $n }}</span>
                                    <span class="mt-1 font-modern text-xs text-pg-tiza/55 sm:text-sm">días</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                {{-- 2. Cómo va --}}
                <fieldset data-paso class="mb-14">
                    <legend class="w-full">
                        <button type="button" data-atras hidden class="{{ $atras }}"><x-icono nombre="arrow-left" class="text-xs" /> Volver</button>
                        <span class="{{ $paso }}">2 de 4</span>
                        <span class="{{ $pregunta }}">¿Cómo vas?</span>
                    </legend>
                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        @foreach(Hoy::NIVELES as $clave => $nombre)
                            <label data-opcion class="block cursor-pointer">
                                <input type="radio" name="nivel" value="{{ $clave }}" class="peer sr-only" @checked($respuestas['nivel'] === $clave) @if($loop->first) required @endif>
                                <span class="{{ $tarjeta }} h-full items-center gap-4 px-4 py-4 sm:flex-col sm:justify-center sm:gap-3 sm:py-8 sm:text-center">
                                    <span class="grid size-12 shrink-0 place-items-center rounded-full bg-pg-negro/60 text-xl text-pg-rojo-claro sm:size-16 sm:text-2xl">
                                        <x-icono :nombre="$iconos[$clave]" />
                                    </span>
                                    <span class="font-modern">
                                        <span class="block font-semibold sm:text-lg">{{ $nombre }}</span>
                                        <span class="block text-sm text-pg-tiza/55">{{ $bajadas[$clave] }}</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                {{-- 3. Estos días: se marca todo lo que entrenó. --}}
                <fieldset data-paso class="mb-14">
                    <legend class="w-full">
                        <button type="button" data-atras hidden class="{{ $atras }}"><x-icono nombre="arrow-left" class="text-xs" /> Volver</button>
                        <span class="{{ $paso }}">3 de 4</span>
                        <span class="{{ $pregunta }}">¿Qué entrenaste estos últimos días?</span>
                        <span class="mt-1 block font-modern text-sm text-pg-tiza/60">Marca todo lo de los últimos 2 o 3 días.</span>
                    </legend>
                    <div class="mt-4 grid grid-cols-4 gap-1.5 sm:gap-3">
                        @foreach(Hoy::SECCIONES as $titulo => $claves)
                            @php $claves = array_values(array_filter($claves, fn ($c) => isset(Hoy::HICE[$c]))); @endphp
                            <p class="{{ $seccion }}">{{ $titulo }}</p>
                            @foreach($claves as $clave)
                                <label data-opcion class="block cursor-pointer">
                                    <input type="checkbox" name="hice[]" value="{{ $clave }}" class="peer sr-only" @checked(in_array($clave, $hechos, true))>
                                    <span class="{{ $tarjeta }} {{ $baja }}">
                                        @include('landing.partes.icono-grupo', ['grupo' => $clave])
                                        <span class="{{ $nombreBajo }}">{{ Hoy::HICE[$clave] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        @endforeach
                        {{-- Junto al cardio, en la misma fila. --}}
                        <label data-opcion class="col-span-3 block cursor-pointer">
                            <input type="checkbox" name="hice[]" value="nada" class="peer sr-only" @checked($hechos === [])>
                            <span class="{{ $tarjeta }} h-full items-center justify-center gap-2 px-3 py-2">
                                <x-icono nombre="bed" class="text-lg text-pg-tiza/60" />
                                <span class="font-modern text-sm font-semibold sm:text-base">{{ Hoy::HICE['nada'] }}</span>
                            </span>
                        </label>
                    </div>

                    <button type="button" data-seguir hidden class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-pg-rojo py-4 font-modern font-bold text-white transition-colors hover:bg-pg-rojo-oscuro">
                        Seguir <x-icono nombre="arrow-right" class="text-sm" />
                    </button>
                </fieldset>

                {{-- 4. Hoy: lo que choca con lo de estos días queda como «mejor no» y se sugiere otra cosa. --}}
                <fieldset data-paso>
                    <legend class="w-full">
                        <button type="button" data-atras hidden class="{{ $atras }}"><x-icono nombre="arrow-left" class="text-xs" /> Volver</button>
                        <span class="{{ $paso }}">4 de 4</span>
                        <span class="{{ $pregunta }}">¿Qué te gustaría entrenar hoy?</span>
                    </legend>
                    <div class="mt-4 grid grid-cols-4 gap-1.5 sm:gap-3">
                        @foreach(Hoy::SECCIONES as $titulo => $claves)
                            <p class="{{ $seccion }}">{{ $titulo }}</p>
                            @foreach($claves as $clave)
                                @php $descansa = Hoy::choca($clave, $hechos); @endphp
                                <label data-opcion class="block cursor-pointer">
                                    <input type="radio" name="hoy" value="{{ $clave }}" class="peer sr-only" @checked($hoyMarcado === $clave)>
                                    <span class="{{ $tarjeta }} {{ $baja }} data-descansa:opacity-55" @if($descansa) data-descansa @endif>
                                        @include('landing.partes.icono-grupo', ['grupo' => $clave])
                                        <span class="min-w-0">
                                            <span class="block {{ $nombreBajo }}">{{ Hoy::GRUPOS[$clave] }}</span>
                                            <span data-mejor-no @if(! $descansa) hidden @endif class="mt-0.5 block font-modern text-[10px] text-pg-tiza/60 sm:text-xs">Descansa</span>
                                            <span data-sugerido @if($sugerida !== $clave) hidden @endif class="mt-0.5 block font-modern text-[10px] font-semibold text-pg-rojo-claro sm:text-xs">Sugerido</span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>

                    <button type="submit" class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-pg-rojo py-4 font-modern font-bold text-white transition-colors hover:bg-pg-rojo-oscuro">
                        Ver mi entrenamiento <x-icono nombre="arrow-right" class="text-sm" />
                    </button>
                </fieldset>
            </form>

            <p class="mt-12 text-center font-modern text-sm">
                <a href="{{ route('landing.rutinas') }}" class="text-pg-tiza/50 underline underline-offset-4 transition-colors hover:text-pg-tiza">Ver todas las rutinas</a>
            </p>
        </div>
    </section>

    <script>
        (function () {
            const form = document.querySelector('[data-preguntas]');

            if (! form) {
                return;
            }

            const pasos = Array.from(form.querySelectorAll('[data-paso]'));
            const nombres = ['dias', 'nivel', 'hice[]', 'hoy'];
            const barra = document.querySelector('[data-barra]');
            const seguir = form.querySelector('[data-seguir]');
            const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let reglas = { vueltas: {}, respaldo: [], carga: {} };
            let actual = 0;

            try {
                reglas = JSON.parse(form.dataset.reglas || '{}');
            } catch (e) {}

            const valor = (nombre) => {
                const marcado = form.querySelector('input[name="' + nombre + '"]:checked');

                return marcado ? marcado.value : null;
            };

            const casillas = () => Array.from(form.querySelectorAll('input[name="hice[]"]'));
            const hechos = () => casillas().filter((c) => c.checked && c.value !== 'nada').map((c) => c.value);

            // La misma cuenta que EntrenamientoDeHoy::choca() y sugerencia().
            const carga = (g) => reglas.carga[g] || [];
            const choca = (g, hechos) => hechos.includes(g)
                || carga(g).some((m) => hechos.some((h) => carga(h).includes(m)));

            function sugerencia(hechos, dias) {
                const vuelta = reglas.vueltas[dias] || reglas.vueltas[3] || [];
                let ultimo = -1;

                vuelta.forEach((g, i) => { if (hechos.includes(g)) { ultimo = i; } });

                for (let i = 1; i <= vuelta.length; i++) {
                    const g = vuelta[(ultimo + i) % vuelta.length];

                    if (! choca(g, hechos)) {
                        return g;
                    }
                }

                return reglas.respaldo.find((g) => ! choca(g, hechos)) || 'movilidad';
            }

            function mostrar(i, animar) {
                const vuelve = i < actual;
                actual = i;
                pasos.forEach((p, j) => { p.hidden = j !== i; });
                barra.querySelectorAll('span').forEach((s, j) => s.toggleAttribute('data-lleno', j <= i));

                if (! animar) {
                    return;
                }

                if (! reducir) {
                    pasos[i].classList.remove('pg-paso-entra', 'pg-paso-vuelve');
                    void pasos[i].offsetWidth;
                    pasos[i].classList.add(vuelve ? 'pg-paso-vuelve' : 'pg-paso-entra');
                }

                if (barra.getBoundingClientRect().top < 0) {
                    barra.scrollIntoView({ behavior: reducir ? 'auto' : 'smooth', block: 'center' });
                }

                const foco = pasos[i].querySelector('input:checked') || pasos[i].querySelector('input');
                foco.focus({ preventScroll: true });
            }

            // Lo que choca con lo de estos días queda como «mejor no» y se marca lo que conviene.
            function marcarHoy(elegir) {
                const hecho = hechos();
                const sugerida = sugerencia(hecho, valor('dias') || '3');

                form.querySelectorAll('input[name="hoy"]').forEach((input) => {
                    const tarjeta = input.nextElementSibling;
                    const descansa = choca(input.value, hecho);

                    tarjeta.toggleAttribute('data-descansa', descansa);
                    tarjeta.querySelector('[data-mejor-no]').hidden = ! descansa;
                    tarjeta.querySelector('[data-sugerido]').hidden = input.value !== sugerida;

                    if (elegir && input.value === sugerida) {
                        input.checked = true;
                    }
                });
            }

            function siguiente() {
                if (actual >= pasos.length - 1) {
                    return;
                }

                // Sin nada marcado en lo de estos días, es que descansó.
                if (nombres[actual] === 'hice[]' && ! valor('hice[]')) {
                    form.querySelector('input[name="hice[]"][value="nada"]').checked = true;
                    marcarHoy(true);
                }

                if (valor(nombres[actual])) {
                    mostrar(actual + 1, true);
                }
            }

            // Tocar una opción pasa a la pregunta siguiente, salvo en la de
            // marcar varias (ahí solo «Nada, descansé» pasa sola). Las flechas
            // del teclado solo cambian la marca; Enter avanza.
            form.addEventListener('click', (e) => {
                const opcion = e.target.closest('[data-opcion]');

                if (! opcion || e.target.matches('input') || pasos.indexOf(opcion.closest('[data-paso]')) !== actual) {
                    return;
                }

                const input = opcion.querySelector('input');

                if (input.type === 'checkbox' && input.value !== 'nada') {
                    return;
                }

                window.setTimeout(() => {
                    if (input.checked) {
                        siguiente();
                    }
                }, reducir ? 0 : 260);
            });

            form.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && e.target.matches('input') && actual < pasos.length - 1) {
                    e.preventDefault();
                    siguiente();
                }
            });

            form.addEventListener('change', (e) => {
                // «Nada» y lo demás no van juntos.
                if (e.target.name === 'hice[]' && e.target.checked) {
                    casillas().forEach((c) => {
                        if (c !== e.target && (c.value === 'nada') !== (e.target.value === 'nada')) {
                            c.checked = false;
                        }
                    });
                }

                // Sin nada marcado, «Nada, descansé»: tocarla de nuevo no la desmarca.
                if (e.target.name === 'hice[]' && ! valor('hice[]')) {
                    form.querySelector('input[name="hice[]"][value="nada"]').checked = true;
                }

                if (e.target.name === 'hice[]' || e.target.name === 'dias') {
                    marcarHoy(true);
                }
            });

            seguir.hidden = false;
            seguir.addEventListener('click', siguiente);

            form.querySelectorAll('[data-atras]').forEach((boton) => {
                boton.hidden = false;
                boton.addEventListener('click', () => mostrar(Math.max(0, actual - 1), true));
            });

            barra.hidden = false;

            if (Number(form.dataset.inicio) >= 3) {
                marcarHoy(! valor('hoy'));
            }

            pasos.forEach((p) => p.classList.remove('mb-14'));
            mostrar(Number(form.dataset.inicio) || 0, false);
        })();
    </script>
@endsection
