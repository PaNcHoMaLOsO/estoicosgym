{{--
    Los embajadores: socios que representan al gimnasio. Salen de
    Configuración → Especialistas y embajadores; sin ninguno, no se pinta.

    COMO UN PLANTEL, no como una fila de circulitos. Cada uno es un panel alto
    —la proporción de una persona de pie— con el nombre grande abajo, como en
    la presentación de un equipo. Los círculos con la inicial se veían a
    plantilla hecha en serie, y el dueño lo notó. Con foto, la foto llena el
    panel y toma color al pasar el mouse; sin foto, la inicial gigante en hueco
    ocupa el fondo y el panel no queda vacío.

    En el celular se deslizan de lado: cuatro paneles altos uno debajo del otro
    serían cuatro pantallas de scroll para ver cuatro nombres.

    Van justo antes del bloque rojo final: ver a gente real que entrena aquí es
    lo que más ayuda a decidirse.
--}}
@if(count($embajadores ?? []))
    <section id="embajadores" class="py-10 lg:py-20 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <div class="mb-7 lg:mb-12 flex flex-wrap items-end justify-between gap-4 animate-on-scroll">
                <div>
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Entrenan con nosotros</span>
                    <h2 class="font-display text-4xl md:text-6xl mt-2 uppercase leading-none text-pg-tiza">NUESTROS EMBAJADORES</h2>
                </div>
                <p class="font-display text-lg tracking-wider text-pg-tiza/40">{{ str_pad(count($embajadores), 2, '0', STR_PAD_LEFT) }} atletas</p>
            </div>

            <div class="-mx-5 flex snap-x snap-mandatory gap-3 overflow-x-auto px-5 pb-2 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-4 lg:gap-4">
                @foreach($embajadores as $i => $e)
                    <figure class="animate-on-scroll group relative aspect-[3/4] w-[72%] shrink-0 snap-start overflow-hidden bg-pg-carbon sm:w-auto" style="animation-delay: {{ ($i % 4) * 0.08 }}s">
                        @if($e['foto'])
                            <img src="{{ $e['foto'] }}" alt="{{ $e['nombre'] }}" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover grayscale transition duration-700 group-hover:scale-105 group-hover:grayscale-0">
                        @else
                            {{-- Sin foto, la inicial en hueco llena el panel: un hueco negro se ve roto. --}}
                            <span class="absolute -right-4 -top-6 select-none font-display text-[14rem] leading-none text-transparent [-webkit-text-stroke:1.5px_rgba(221,42,50,0.35)] transition-colors duration-700 group-hover:text-pg-rojo/10" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr($e['nombre'], 0, 1)) }}
                            </span>
                        @endif

                        {{-- El fundido de abajo: el nombre se lee sobre cualquier foto. --}}
                        <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-pg-negro via-pg-negro/70 to-transparent" aria-hidden="true"></div>

                        <figcaption class="absolute inset-x-0 bottom-0 p-4 lg:p-5">
                            <span class="block h-0.5 w-8 bg-pg-rojo transition-all duration-500 group-hover:w-14" aria-hidden="true"></span>
                            @if($e['disciplina'])
                                <p class="mt-3 font-modern text-xs uppercase tracking-[0.2em] text-pg-rojo-claro">{{ $e['disciplina'] }}</p>
                            @endif
                            <p class="mt-1 font-display text-2xl lg:text-3xl uppercase leading-none text-pg-tiza">{{ $e['nombre'] }}</p>
                            @if($e['instagram'])
                                <a href="{{ $e['instagram'] }}" target="_blank" rel="noopener" data-evento="instagram_embajador"
                                   class="mt-2 inline-flex items-center gap-1.5 font-modern text-sm text-pg-tiza/70 hover:text-pg-rojo-claro">
                                    <i class="fab fa-instagram" aria-hidden="true"></i>{{ '@' . $e['usuario'] }}
                                </a>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
