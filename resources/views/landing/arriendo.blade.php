@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    ARRIENDO PARA INSTITUCIONES. No es un convenio (ahí el alumno se inscribe
    por su cuenta con descuento): la universidad o el instituto arrienda el
    gimnasio unas horas fijas a la semana para sus clases y talleres, en los
    bloques donde hay menos socios.

    Página propia para que Google la encuentre: quien busca «arriendo de
    gimnasio» no escribe «convenios». Nada interno: ni horarios de otros, ni
    precios, ni alumnos. Solo quiénes ya arriendan, cómo es el espacio y el
    formulario.

    SE ARRIENDAN HORAS, PERO COMPARTIDAS: los socios siguen entrenando. Por
    eso la frase lo dice de entrada; si no, «arriendo» suena a que ese rato el
    gimnasio queda solo para ellos. Y no es solo para universidades: también
    clubes y entrenadores que quieren dar sus clases aquí.
--}}
@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Instituciones, clubes y entrenadores',
        'titulo' => 'Arrienda horas de gimnasio para tus clases' . ($web['ciudad'] ? ' en ' . $web['ciudad'] : ''),
        'bajada' => 'Universidades, clubes deportivos y entrenadores arriendan horas fijas a la semana para hacer sus clases en ' . $gimnasio['nombre'] . '. La sala se comparte con los socios, en las horas de menos gente.',
        'compacta' => true,
    ])

    {{-- Quiénes ya arriendan: solo el logo y el nombre. Sin ninguno, no hay franja. --}}
    @if(count($instituciones))
        <section class="bg-white text-gray-900 pt-6 pb-2">
            <h2 class="text-center font-modern text-xs uppercase tracking-widest text-gray-500">Ya entrenan aquí</h2>
            <div class="mt-2">
                @include('landing.partes.cinta', ['logos' => $instituciones, 'etiqueta' => 'Quienes ya hacen sus clases en el gimnasio'])
            </div>
        </section>
    @endif

    {{-- El espacio que se arrienda, en collage. Sin fotos, no hay collage. --}}
    @if(count($fotosDelArriendo))
        <section class="bg-pg-negro py-8 lg:py-12">
            <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
                <h2 class="sr-only">El espacio</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 auto-rows-[9rem] sm:auto-rows-[12rem] lg:auto-rows-[14rem] gap-2 sm:gap-3">
                    @foreach($fotosDelArriendo as $i => $foto)
                        {{-- La primera, grande: el collage tiene un centro. --}}
                        <figure class="animate-on-scroll relative overflow-hidden rounded-xl bg-pg-carbon {{ $i === 0 ? 'col-span-2 row-span-2' : '' }}">
                            <img src="{{ $foto['imagen'] }}" alt="{{ $foto['titulo'] }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}" decoding="async" class="absolute inset-0 h-full w-full object-cover">
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- El formulario: se marcan los bloques y se manda. Sin precios: se
         cotiza según las horas y el grupo. --}}
    <section id="instituciones" class="py-9 lg:py-14 bg-pg-carbon border-t border-pg-tiza/10 scroll-mt-24">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            {{--
                UNA SOLA COLUMNA, centrada: el título y la frase arriba, el
                formulario debajo y lo demás en una línea al final. Con el texto a
                un lado y el formulario al otro se veía cargado y las dos mitades
                competían entre sí.
            --}}
            <div class="mx-auto max-w-3xl">
                {{-- Solo el formulario: nada de WhatsApp ni teléfono aquí. Una
                     institución cotiza por escrito, y así llega todo lo que hace
                     falta para responderle de una vez. --}}
                <div class="animate-on-scroll">
                    <h3 class="sr-only">Pide tus horarios</h3>

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
                                <label for="arr-institucion" class="sr-only">Institución, club o tu nombre</label>
                                <i class="fas fa-building-columns pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-institucion" name="institucion" type="text" required maxlength="150" value="{{ old('institucion') }}" placeholder="Institución, club o tu nombre *" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
                            </div>
                            <div class="col-span-4 sm:col-span-2 relative">
                                <label for="arr-area" class="sr-only">Qué clases harían</label>
                                <i class="fas fa-graduation-cap pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-area" name="area" type="text" maxlength="150" value="{{ old('area') }}" placeholder="Qué clases harían" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
                            </div>
                            <div class="col-span-2 sm:col-span-1 relative">
                                <label for="arr-alumnos" class="sr-only">Personas por clase</label>
                                <i class="fas fa-user-group pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.7rem] text-pg-tiza/35" aria-hidden="true"></i>
                                <input id="arr-alumnos" name="alumnos" type="number" min="1" max="500" inputmode="numeric" value="{{ old('alumnos') }}" placeholder="Personas" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
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
                                <input id="arr-email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email') }}" placeholder="Correo *" class="w-full bg-pg-negro border border-pg-tiza/15 hover:border-pg-tiza/30 focus:border-pg-rojo rounded-lg py-2 text-pg-tiza placeholder-pg-tiza/40 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-sm pl-8 pr-3">
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
                    Musculación, peso libre y cardio · Clases prácticas de carreras deportivas, entrenamiento de clubes y selecciones, clases de entrenadores.
                </p>
            </div>
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
