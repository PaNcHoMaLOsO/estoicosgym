@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Estudiantes, empresas e instituciones',
        'titulo' => 'Convenios',
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
            @php($todos = collect($convenios)->flatMap(fn ($g) => $g['convenios'])->values()->all())
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

                    <div class="animate-on-scroll overflow-hidden rounded-2xl border border-gray-200 bg-gray-200">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-px">
                            @foreach($grupo['convenios'] as $c)
                                <div class="group flex flex-col items-center bg-white px-3 py-5 lg:px-8 lg:py-9 text-center transition-colors duration-300 hover:bg-gray-50">
                                    <div class="flex h-12 lg:h-20 w-full items-center justify-center transition-transform duration-500 group-hover:scale-[1.04]">
                                        @if($c['logo'])
                                            <img src="{{ $c['logo'] }}" alt="{{ $c['nombre'] }}" loading="lazy" class="max-h-full max-w-full object-contain">
                                        @else
                                            {{-- Sin logo subido, el nombre hace de logo. --}}
                                            <span class="font-display text-2xl lg:text-3xl uppercase tracking-wide leading-none text-gray-800">{{ $c['nombre'] }}</span>
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

    {{--
        ARRIENDO PARA INSTITUCIONES. No es un convenio (ahí el alumno se inscribe
        por su cuenta con descuento): aquí la universidad o el instituto arrienda
        el gimnasio por horas a la semana para sus clases y talleres, en los
        bloques donde hay menos socios. Va en esta página porque quien la lee es
        el mismo público. Sin precios: se cotiza según las horas y el grupo.
    --}}
    <section id="instituciones" class="py-9 lg:py-14 bg-pg-carbon border-t border-pg-tiza/10">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            {{--
                UNA SOLA COLUMNA, centrada: el título y la frase arriba, el
                formulario debajo y lo demás en una línea al final. Con el texto a
                un lado y el formulario al otro se veía cargado y las dos mitades
                competían entre sí.
            --}}
            <div class="mx-auto max-w-3xl">
                <div class="animate-on-scroll text-center">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Para universidades e institutos</span>
                    <h2 class="font-display text-2xl md:text-3xl mt-3 text-pg-tiza uppercase leading-tight">Arriendo de gimnasio por horas{{ $web['ciudad'] ? ' en ' . $web['ciudad'] : '' }}</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-pg-tiza/70 font-modern text-sm lg:text-base leading-relaxed">
                        Tu institución arrienda {{ $gimnasio['nombre'] }} unas horas fijas a la semana y tus alumnos
                        hacen aquí sus clases prácticas o talleres, con la sala de máquinas para el grupo.
                    </p>
                </div>

                {{-- Solo el formulario: nada de WhatsApp ni teléfono aquí. Una
                     institución cotiza por escrito, y así llega todo lo que hace
                     falta para responderle de una vez. --}}
                <div class="animate-on-scroll mt-6 lg:mt-8">
                    <h3 class="sr-only">Solicita el arriendo</h3>

                    @if(session('success'))
                        <div class="mt-3 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 font-modern text-sm text-emerald-300" role="status">
                            <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>Solicitud enviada. Te responderemos pronto.
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mt-3 rounded-lg border border-pg-rojo/40 bg-pg-rojo/10 px-4 py-3 font-modern text-sm text-pg-rojo-claro" role="alert">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="mt-3 rounded-lg border border-pg-rojo/40 bg-pg-rojo/10 px-4 py-3 font-modern text-sm text-pg-rojo-claro" role="alert">
                            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                        </div>
                    @endif

                    {{--
                        UN CALENDARIO DE LA SEMANA, no botones con letras. «L M X J V S»
                        y dos desplegables no le decían a nadie qué hacer: había que
                        adivinar que eran los días y que las horas valían para todos.
                        Aquí se ve la semana entera (días arriba, horas al lado) y se
                        pinta encima lo que se necesita, igual que en cualquier agenda.
                        Se puede arrastrar para marcar varias horas de una vez, y debajo
                        se va escribiendo lo marcado, para que no quede duda.

                        Cada casilla es una hora: «martes-10» es martes de 10:00 a 11:00.
                        Son casillas de verificación de verdad, así que funciona con
                        teclado y sin JavaScript; el arrastre y el resumen son un extra.
                    --}}
                    {{-- Con etiqueta PHP a secas y no con la directiva de Blade: en esta vista
                         ya hay directivas de PHP en linea, y Blade las empareja con el cierre
                         del bloque y se come todo lo que queda en medio. --}}
                    <?php
                        $diasArriendo = ['lunes' => 'Lun', 'martes' => 'Mar', 'miércoles' => 'Mié', 'jueves' => 'Jue', 'viernes' => 'Vie', 'sábado' => 'Sáb'];
                        $horasArriendo = range(8, 21);
                        $marcados = (array) old('bloques', []);
                    ?>
                    <form action="{{ route('landing.contacto.enviar') }}" method="POST" class="space-y-5">
                        @csrf
                        <input type="hidden" name="servicio" value="arriendo">
                        <div class="hp-field" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="space-y-2.5">
                        <p class="flex flex-wrap items-baseline gap-x-2.5 font-modern text-sm font-semibold text-pg-tiza">
                            <span class="flex h-5 w-5 items-center justify-center self-center rounded-full bg-pg-rojo text-[0.7rem] text-white">1</span>
                            Marca en el calendario las horas que necesitan
                            <span class="font-normal text-xs text-pg-tiza/45">Toca o arrastra. Cada casilla es una hora.</span>
                        </p>

                            <div data-calendario class="select-none touch-pan-y overflow-hidden rounded-xl border border-pg-tiza/10 bg-pg-negro/50">
                                <div class="grid grid-cols-[2.6rem_repeat(6,minmax(0,1fr))] border-b border-pg-tiza/10 bg-pg-negro/60">
                                    <span></span>
                                    @foreach($diasArriendo as $dia => $corto)
                                        <span class="py-1.5 text-center font-display text-xs uppercase tracking-wide text-pg-tiza/80" title="{{ ucfirst($dia) }}">{{ $corto }}</span>
                                    @endforeach
                                </div>
                                @foreach($horasArriendo as $h)
                                    <div class="grid grid-cols-[2.6rem_repeat(6,minmax(0,1fr))] {{ $loop->last ? '' : 'border-b border-pg-tiza/5' }}">
                                        <span class="flex items-center justify-end pr-2 font-modern text-[0.65rem] tabular-nums text-pg-tiza/45">{{ sprintf('%02d:00', $h) }}</span>
                                        @foreach($diasArriendo as $dia => $corto)
                                            <label class="block cursor-pointer border-l border-pg-tiza/5">
                                                <input type="checkbox" name="bloques[]" value="{{ $dia }}-{{ $h }}" class="peer sr-only" @checked(in_array($dia . '-' . $h, $marcados))>
                                                <span class="block h-6 sm:h-[1.6rem] transition-colors hover:bg-pg-tiza/10 peer-checked:bg-pg-rojo peer-checked:hover:bg-pg-rojo-oscuro peer-focus-visible:ring-2 peer-focus-visible:ring-inset peer-focus-visible:ring-white/60">
                                                    <span class="sr-only">{{ $dia }} de {{ sprintf('%02d:00', $h) }} a {{ sprintf('%02d:00', $h + 1) }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <p data-resumen class="min-h-5 font-modern text-xs sm:text-sm text-pg-tiza/60" aria-live="polite">Todavía no marcas ninguna hora.</p>
                        </div>

                        <div class="space-y-2.5">
                        <p class="flex flex-wrap items-baseline gap-x-2.5 font-modern text-sm font-semibold text-pg-tiza">
                            <span class="flex h-5 w-5 items-center justify-center self-center rounded-full bg-pg-rojo text-[0.7rem] text-white">2</span>
                            Cuéntanos quiénes son
                            
                        </p>
                            <div class="grid grid-cols-6 gap-2">
                            <div class="col-span-6 sm:col-span-3 relative">
                                <label for="arr-institucion" class="sr-only">Institución</label>
                                <i class="fas fa-building-columns pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-institucion" name="institucion" type="text" required maxlength="150" value="{{ old('institucion') }}" placeholder="Institución *" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
                            </div>
                            <div class="col-span-4 sm:col-span-2 relative">
                                <label for="arr-area" class="sr-only">Carrera o área</label>
                                <i class="fas fa-graduation-cap pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-area" name="area" type="text" maxlength="150" value="{{ old('area') }}" placeholder="Carrera o área" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
                            </div>
                            <div class="col-span-2 sm:col-span-1 relative">
                                <label for="arr-alumnos" class="sr-only">Alumnos</label>
                                <i class="fas fa-user-group pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-alumnos" name="alumnos" type="number" min="1" max="500" inputmode="numeric" value="{{ old('alumnos') }}" placeholder="Alumnos" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
                            </div>
                            </div>
                        </div>

                        <div class="space-y-2.5">
                        <p class="flex flex-wrap items-baseline gap-x-2.5 font-modern text-sm font-semibold text-pg-tiza">
                            <span class="flex h-5 w-5 items-center justify-center self-center rounded-full bg-pg-rojo text-[0.7rem] text-white">3</span>
                            A quién le respondemos
                            
                        </p>
                            <div class="grid grid-cols-6 gap-2">
                            <div class="col-span-3 sm:col-span-2 relative">
                                <label for="arr-nombre" class="sr-only">Tu nombre</label>
                                <i class="fas fa-user pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-nombre" name="nombre" type="text" required maxlength="100" autocomplete="name" value="{{ old('nombre') }}" placeholder="Tu nombre *" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
                            </div>
                            <div class="col-span-3 sm:col-span-2 relative">
                                <label for="arr-telefono" class="sr-only">Teléfono</label>
                                <i class="fas fa-phone pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-telefono" name="telefono" type="tel" maxlength="20" autocomplete="tel" value="{{ old('telefono') }}" placeholder="Teléfono" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
                            </div>
                            <div class="col-span-6 sm:col-span-2 relative">
                                <label for="arr-email" class="sr-only">Correo institucional</label>
                                <i class="fas fa-envelope pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email') }}" placeholder="Correo institucional *" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
                            </div>
                            </div>
                        </div>

                        <button type="submit" data-evento="arriendo_instituciones" class="group flex w-full items-center justify-center gap-2 bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold px-4 py-2.5 rounded-lg text-sm transition-colors font-modern">
                            Enviar solicitud
                            <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                        </button>
                    </form>

                    <script>
                        (function () {
                            const cal = document.querySelector('[data-calendario]');
                            const resumen = document.querySelector('[data-resumen]');
                            if (!cal || !resumen) { return; }
                            const casillas = Array.from(cal.querySelectorAll('input[type=checkbox]'));
                            const dosCifras = (n) => String(n).padStart(2, '0') + ':00';

                            // Lo marcado, dicho en palabras: «Martes 10:00 a 12:00 · Jueves 10:00 a 12:00».
                            const escribir = () => {
                                const porDia = new Map();
                                casillas.filter(c => c.checked).forEach(c => {
                                    const corte = c.value.lastIndexOf('-');
                                    const dia = c.value.slice(0, corte);
                                    if (!porDia.has(dia)) { porDia.set(dia, []); }
                                    porDia.get(dia).push(Number(c.value.slice(corte + 1)));
                                });
                                const partes = [];
                                porDia.forEach((horas, dia) => {
                                    horas.sort((a, b) => a - b);
                                    const tramos = [];
                                    let desde = horas[0], hasta = horas[0];
                                    for (let i = 1; i <= horas.length; i++) {
                                        if (horas[i] === hasta + 1) { hasta = horas[i]; continue; }
                                        tramos.push(dosCifras(desde) + ' a ' + dosCifras(hasta + 1));
                                        desde = hasta = horas[i];
                                    }
                                    partes.push(dia.charAt(0).toUpperCase() + dia.slice(1) + ' ' + tramos.join(' y '));
                                });
                                resumen.textContent = partes.length ? partes.join(' · ') : 'Todavía no marcas ninguna hora.';
                                resumen.classList.toggle('text-pg-tiza', partes.length > 0);
                            };

                            // Arrastrar pinta (o borra) todo lo que se cruza, segun lo que
                            // hizo la primera casilla. Solo con mouse: con el dedo se toca
                            // casilla a casilla, para no pelear con el scroll de la pagina.
                            let pintando = null;
                            cal.addEventListener('pointerdown', (e) => {
                                const etiqueta = e.target.closest('label');
                                if (e.pointerType !== 'mouse' || !etiqueta) { return; }
                                e.preventDefault();
                                const caja = etiqueta.querySelector('input');
                                pintando = !caja.checked;
                                caja.checked = pintando;
                                escribir();
                            });
                            cal.addEventListener('pointerover', (e) => {
                                const etiqueta = e.target.closest('label');
                                if (pintando === null || !etiqueta) { return; }
                                etiqueta.querySelector('input').checked = pintando;
                                escribir();
                            });
                            cal.addEventListener('click', (e) => {
                                // El clic que cierra un arrastre con mouse ya se aplico arriba.
                                if (e.detail > 0 && e.target.closest('label') && cal.dataset.mouse === '1') { e.preventDefault(); }
                            });
                            cal.addEventListener('pointerdown', (e) => { cal.dataset.mouse = e.pointerType === 'mouse' ? '1' : '0'; }, true);
                            window.addEventListener('pointerup', () => { pintando = null; });
                            cal.addEventListener('change', escribir);
                            escribir();
                        })();
                    </script>
                </div>

                <p class="animate-on-scroll mt-5 text-center font-modern text-xs sm:text-sm text-pg-tiza/45 leading-relaxed">
                    Musculación, peso libre y cardio · Para Preparador Físico, Técnico en Deportes, Kinesiología, Educación Física, talleres y selecciones deportivas.
                </p>
            </div>
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
