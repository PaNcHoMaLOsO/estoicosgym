{{--
    Los embajadores: socios que representan al gimnasio. Salen de
    Configuración → Especialistas y embajadores; sin ninguno, no se pinta.

    Sin tarjetas: la foto, el nombre, la disciplina y su Instagram, sueltos. Van
    justo antes del bloque rojo final, porque ver a gente real que entrena aquí
    es lo que más ayuda a decidirse.
--}}
@if(count($embajadores ?? []))
    <section id="embajadores" class="py-9 lg:py-16 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <div class="text-center mb-7 lg:mb-12 animate-on-scroll">
                <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Entrenan con nosotros</span>
                <h2 class="font-display text-3xl md:text-4xl mt-3 text-pg-tiza">NUESTROS EMBAJADORES</h2>
            </div>

            <div class="flex flex-wrap justify-center gap-x-8 gap-y-10 lg:gap-x-14">
                @foreach($embajadores as $i => $e)
                    <figure class="animate-on-scroll w-36 lg:w-44 text-center" style="animation-delay: {{ ($i % 4) * 0.1 }}s">
                        @if($e['foto'])
                            <img src="{{ $e['foto'] }}" alt="{{ $e['nombre'] }}" loading="lazy"
                                 class="mx-auto h-28 w-28 lg:h-36 lg:w-36 rounded-full object-cover border border-pg-tiza/10">
                        @else
                            {{-- Sin foto, su inicial: un hueco vacío se ve roto. --}}
                            <span class="mx-auto flex h-28 w-28 lg:h-36 lg:w-36 items-center justify-center rounded-full border border-pg-tiza/10 bg-pg-negro font-display text-4xl text-pg-tiza" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr($e['nombre'], 0, 1)) }}
                            </span>
                        @endif

                        <figcaption class="mt-4">
                            <p class="font-display text-lg uppercase tracking-wide text-pg-tiza">{{ $e['nombre'] }}</p>
                            @if($e['disciplina'])
                                <p class="mt-0.5 font-modern text-sm text-pg-tiza/55">{{ $e['disciplina'] }}</p>
                            @endif
                            @if($e['instagram'])
                                <a href="{{ $e['instagram'] }}" target="_blank" rel="noopener" data-evento="instagram_embajador"
                                   class="mt-2 inline-flex items-center gap-1.5 font-modern text-sm text-pg-rojo-claro hover:underline">
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
