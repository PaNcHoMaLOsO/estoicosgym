@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Sala de máquinas',
        'titulo' => 'Qué entrenar hoy',
        'bajada' => 'Responde tres cosas y te decimos qué hacer esta semana.',
        'compacta' => true,
    ])

    <section class="bg-pg-negro pb-14">
        <div class="mx-auto max-w-3xl px-5 sm:px-8">

            {{--
                LAS PREGUNTAS VAN EN LA DIRECCIÓN, no en un formulario que se
                manda: así la persona puede volver atrás, recargar o guardar el
                enlace de su rutina, y el gimnasio no recibe ni guarda nada de
                lo que respondió.
            --}}
            @if(! $respondido || ! $rutina)
                @if(! $rutina && $respondido)
                    <p class="mb-6 rounded-lg border border-pg-tiza/15 px-4 py-3 font-modern text-sm text-pg-tiza/70">
                        Todavía no hay rutinas cargadas. Pregunta en el mesón y te orientamos.
                    </p>
                @endif

                <form method="get" action="{{ route('landing.rutina') }}" class="space-y-8">
                    <fieldset>
                        <legend class="font-modern text-sm uppercase tracking-wide text-pg-tiza/50">1 · ¿Qué buscas?</legend>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach($objetivos as $valor => $etiqueta)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-pg-tiza/15 px-4 py-3 font-modern text-pg-tiza transition-colors has-[:checked]:border-pg-rojo has-[:checked]:bg-pg-rojo/10">
                                    <input type="radio" name="objetivo" value="{{ $valor }}" class="accent-[#E11D2E]"
                                        @checked($elegido['objetivo'] === $valor || (! $elegido['objetivo'] && $loop->first))>
                                    {{ $etiqueta }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="font-modern text-sm uppercase tracking-wide text-pg-tiza/50">2 · ¿Cuánto llevas entrenando?</legend>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                            @foreach($niveles as $valor => $etiqueta)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-pg-tiza/15 px-4 py-3 font-modern text-pg-tiza transition-colors has-[:checked]:border-pg-rojo has-[:checked]:bg-pg-rojo/10">
                                    <input type="radio" name="nivel" value="{{ $valor }}" class="accent-[#E11D2E]"
                                        @checked($elegido['nivel'] === $valor || (! $elegido['nivel'] && $loop->first))>
                                    {{ $etiqueta }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="font-modern text-sm uppercase tracking-wide text-pg-tiza/50">3 · ¿Cuántos días puedes venir?</legend>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($diasPosibles as $dias)
                                <label class="flex cursor-pointer items-center gap-2 rounded-full border border-pg-tiza/15 px-5 py-2.5 font-modern text-pg-tiza transition-colors has-[:checked]:border-pg-rojo has-[:checked]:bg-pg-rojo/10">
                                    <input type="radio" name="dias" value="{{ $dias }}" class="accent-[#E11D2E]"
                                        @checked((int) $elegido['dias'] === (int) $dias || (! $elegido['dias'] && $loop->first))>
                                    {{ $dias }} días
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <button type="submit" class="w-full rounded-lg bg-pg-rojo px-6 py-4 font-display text-lg uppercase tracking-wide text-white transition-opacity hover:opacity-90 sm:w-auto">
                        Ver mi rutina
                    </button>
                </form>
            @else
                <div class="mb-6 flex flex-wrap items-baseline justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl uppercase text-pg-tiza">{{ $rutina->nombre }}</h2>
                        <p class="font-modern text-sm text-pg-tiza/60">
                            {{ $objetivos[$rutina->objetivo] ?? '' }} · {{ $niveles[$rutina->nivel] ?? '' }} · {{ $rutina->dias_por_semana }} días por semana
                        </p>
                    </div>
                    <a href="{{ route('landing.rutina') }}" class="font-modern text-sm text-pg-tiza/60 underline-offset-4 hover:text-pg-tiza hover:underline">
                        Cambiar respuestas
                    </a>
                </div>

                @if($rutina->descripcion)
                    <p class="mb-6 font-modern text-pg-tiza/75">{{ $rutina->descripcion }}</p>
                @endif

                {{-- EN QUÉ DÍA VA lo recuerda SU teléfono, no el gimnasio. Si
                     borra los datos del navegador se pierde, y está bien: es una
                     comodidad, no un registro de nadie. --}}
                <div id="siguiente" hidden class="mb-6 rounded-lg border border-pg-rojo/40 bg-pg-rojo/10 px-4 py-3 font-modern text-pg-tiza">
                    <span id="siguiente-texto"></span>
                    <button type="button" id="reiniciar" class="ml-2 text-sm text-pg-tiza/60 underline underline-offset-4 hover:text-pg-tiza">
                        empezar de nuevo
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach($rutina->dias as $dia)
                        <article data-dia="{{ $dia->numero }}" class="rutina-dia rounded-lg border border-pg-tiza/15 p-5">
                            <header class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="font-display text-lg uppercase text-pg-tiza">
                                    Día {{ $dia->numero }} · {{ $dia->titulo }}
                                </h3>
                                @if($dia->foco)
                                    <p class="font-modern text-sm text-pg-tiza/55">{{ $dia->foco }}</p>
                                @endif
                            </header>

                            <ul class="divide-y divide-pg-tiza/10">
                                @foreach($dia->ejercicios as $linea)
                                    <li class="py-3">
                                        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                            <p class="font-modern font-semibold text-pg-tiza">
                                                {{ $linea->ejercicio->nombre ?? 'Ejercicio' }}
                                            </p>
                                            <p class="font-modern text-sm tabular-nums text-pg-tiza/70">
                                                {{ $linea->series }} × {{ $linea->repeticiones }}
                                                @if($linea->descanso_seg)
                                                    · descansa {{ $linea->descanso_seg }} s
                                                @endif
                                            </p>
                                        </div>
                                        @if($linea->nota || ($linea->ejercicio->indicacion ?? null))
                                            <p class="mt-1 font-modern text-sm text-pg-tiza/55">
                                                {{ $linea->nota ?: $linea->ejercicio->indicacion }}
                                            </p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            <button type="button" data-hecho="{{ $dia->numero }}"
                                class="mt-4 rounded-lg border border-pg-tiza/20 px-4 py-2 font-modern text-sm text-pg-tiza/80 transition-colors hover:border-pg-tiza/40 hover:text-pg-tiza">
                                Listo, hice este día
                            </button>
                        </article>
                    @endforeach
                </div>

                {{-- EL AVISO VA SIEMPRE Y ABAJO DE LA RUTINA, que es donde se
                     termina de leer. Esto es una guía general de sala: no la
                     revisó nadie para esta persona en particular. --}}
                <p class="mt-8 rounded-lg border border-pg-tiza/15 px-4 py-3 font-modern text-sm text-pg-tiza/60">
                    Esto es una guía general de la sala, igual para todos. No reemplaza a un profesional.
                    Si tienes una lesión, estás embarazada, tomas medicamentos o tienes alguna condición de
                    salud, consulta antes con tu médico. Calienta 5 minutos antes de empezar, usa un peso con
                    el que puedas terminar todas las repeticiones con buena técnica, y si algo te duele,
                    detente y pregunta en el mesón.
                </p>

                <script>
                    // La memoria es del teléfono de quien mira: el gimnasio no
                    // guarda nada. Si el navegador no deja (modo privado, datos
                    // bloqueados), la página funciona igual sin esta ayuda.
                    (function () {
                        var clave = 'progym.rutina.' + @json($rutina->uuid);
                        var total = {{ $rutina->dias->count() }};
                        var aviso = document.getElementById('siguiente');
                        var texto = document.getElementById('siguiente-texto');

                        function leer() {
                            try { return parseInt(localStorage.getItem(clave) || '0', 10) || 0; } catch (e) { return 0; }
                        }

                        function guardar(n) {
                            try { localStorage.setItem(clave, String(n)); } catch (e) {}
                        }

                        function pintar() {
                            var hechos = leer();
                            var toca = (hechos % total) + 1;

                            document.querySelectorAll('.rutina-dia').forEach(function (dia) {
                                var esElSuyo = Number(dia.dataset.dia) === toca;
                                dia.classList.toggle('border-pg-rojo/50', esElSuyo);
                                dia.classList.toggle('border-pg-tiza/15', ! esElSuyo);
                            });

                            if (hechos > 0) {
                                texto.textContent = 'Te toca el día ' + toca + '.';
                                aviso.hidden = false;
                            }
                        }

                        document.querySelectorAll('[data-hecho]').forEach(function (boton) {
                            boton.addEventListener('click', function () {
                                guardar(leer() + 1);
                                pintar();
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            });
                        });

                        document.getElementById('reiniciar').addEventListener('click', function () {
                            guardar(0);
                            aviso.hidden = true;
                            pintar();
                        });

                        pintar();
                    })();
                </script>
            @endif
        </div>
    </section>
@endsection
