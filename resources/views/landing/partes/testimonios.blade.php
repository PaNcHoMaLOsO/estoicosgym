{{--
    Lo que dicen los socios: UNA cita grande a la vez, no tres columnas de
    cajitas con comillas.

    Tres columnas iguales es la maqueta de siempre y se lee como relleno. Una
    frase en letra grande, con el nombre debajo, se lee como lo que es: alguien
    que habla del gimnasio. Pasan solas cada ocho segundos, se paran al poner el
    mouse encima y se cambian con las flechas o los números.

    TODAS ESTÁN EN LA PÁGINA, solo que una visible. Google y los lectores de
    pantalla las leen todas; y sin JavaScript se ven todas, una debajo de otra.
    Quien pidió menos movimiento no las ve pasar solas.

    RESEÑAS DE GOOGLE (15-oct-2026): con la llave de Google conectada, las de
    4 y 5 estrellas de la ficha de Maps van primero (ResenasDeGoogle), con sus
    estrellas y el nombre de quien la escribió; arriba, la nota y cuántas hay.
    Las largas, en letra más chica. Abajo, ver todas y dejar una.
--}}
@php
    $google = $google ?? null;
    $paraResenar = $web['resenas'] ?? null;
@endphp
@if(count($testimonios ?? []))
    <section id="testimonios" class="relative overflow-hidden py-12 lg:py-24 bg-pg-carbon" aria-roledescription="carrusel" aria-label="Lo que dicen nuestros socios">
        {{-- La comilla gigante de fondo: marca la sección sin otra caja más. --}}
        <span class="pointer-events-none absolute -top-10 left-2 lg:left-10 select-none font-display text-[16rem] lg:text-[26rem] leading-none text-pg-rojo/10" aria-hidden="true">“</span>

        <div class="relative max-w-5xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
                <p class="font-modern text-sm uppercase tracking-widest text-pg-rojo-claro">Lo que dicen los socios</p>
                {{-- La nota de Google: se toca y abre la ficha. --}}
                @if($google && $google['nota'])
                    <a href="{{ $google['enlace'] ?: '#testimonios' }}" @if($google['enlace']) target="_blank" rel="noopener" data-evento="resenas_google" @endif
                        class="flex items-center gap-2 font-modern text-sm text-pg-tiza/80 transition-colors hover:text-pg-tiza">
                        <span class="font-display text-2xl leading-none text-pg-tiza">{{ number_format($google['nota'], 1, ',', '') }}</span>
                        <span class="flex gap-0.5 text-amber-400" aria-label="{{ number_format($google['nota'], 1, ',', '') }} de 5 estrellas">
                            @for($i = 1; $i <= 5; $i++)
                                <x-icono nombre="star" class="text-sm {{ $google['nota'] >= $i - 0.25 ? '' : 'opacity-25' }}" />
                            @endfor
                        </span>
                        <span>{{ $google['total'] }} {{ $google['total'] === 1 ? 'reseña' : 'reseñas' }} en Google</span>
                    </a>
                @endif
            </div>

            <div class="mt-6 lg:mt-10" data-testimonios>
                @foreach($testimonios as $i => $t)
                    @php $larga = mb_strlen($t['texto']) > 120; @endphp
                    <figure data-testimonio class="{{ $i > 0 ? 'mt-10' : '' }}" id="testimonio-{{ $i + 1 }}">
                        @if(! empty($t['google']))
                            <p class="mb-4 flex gap-1 text-amber-400" aria-label="{{ $t['nota'] }} de 5 estrellas">
                                @for($k = 0; $k < $t['nota']; $k++)
                                    <x-icono nombre="star" class="text-base" />
                                @endfor
                            </p>
                        @endif
                        <blockquote class="{{ $larga ? 'font-modern text-lg sm:text-2xl lg:text-3xl leading-snug' : 'font-display text-2xl sm:text-4xl lg:text-5xl uppercase leading-tight' }} text-pg-tiza">
                            {{ $t['texto'] }}
                        </blockquote>
                        <figcaption class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-1 font-modern text-pg-tiza/70">
                            <span class="h-px w-10 bg-pg-rojo" aria-hidden="true"></span>
                            @if(! empty($t['google']) && ! empty($t['enlace']))
                                <a href="{{ $t['enlace'] }}" target="_blank" rel="noopener" class="hover:text-pg-tiza">{{ $t['titulo'] }}</a>
                            @else
                                {{ $t['titulo'] }}
                            @endif
                            @if(! empty($t['google']))
                                <span class="text-sm text-pg-tiza/45">en Google{{ $t['cuando'] ? ' · ' . $t['cuando'] : '' }}</span>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            @if(count($testimonios) > 1)
                {{-- Los controles solo existen con JavaScript: sin él se ven todas. --}}
                <div class="mt-10 hidden items-center gap-6" data-testimonios-controles>
                    <button type="button" data-anterior aria-label="Opinión anterior"
                            class="flex h-11 w-11 items-center justify-center rounded-full border border-pg-tiza/25 text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                        <x-icono nombre="arrow-left" class="text-sm" />
                    </button>
                    <button type="button" data-siguiente aria-label="Opinión siguiente"
                            class="flex h-11 w-11 items-center justify-center rounded-full border border-pg-tiza/25 text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                        <x-icono nombre="arrow-right" class="text-sm" />
                    </button>
                    <span class="font-display text-lg tracking-wider text-pg-tiza/50" aria-live="polite">
                        <span data-actual class="text-pg-tiza">01</span> / {{ str_pad(count($testimonios), 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <script>
                    (function () {
                        var caja = document.querySelector('[data-testimonios]');
                        var controles = document.querySelector('[data-testimonios-controles]');

                        if (! caja || ! controles) {
                            return;
                        }

                        var todas = Array.prototype.slice.call(caja.querySelectorAll('[data-testimonio]'));
                        var actual = 0;
                        var reloj = null;
                        var quieto = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                        controles.classList.remove('hidden');
                        controles.classList.add('flex');

                        function mostrar(n) {
                            actual = (n + todas.length) % todas.length;

                            todas.forEach(function (t, i) {
                                t.classList.remove('mt-10');
                                t.hidden = i !== actual;
                                if (i === actual && ! quieto) {
                                    t.animate(
                                        [{ opacity: 0, transform: 'translateY(12px)' }, { opacity: 1, transform: 'none' }],
                                        { duration: 600, easing: 'cubic-bezier(0.16, 1, 0.3, 1)' }
                                    );
                                }
                            });

                            controles.querySelector('[data-actual]').textContent = String(actual + 1).padStart(2, '0');
                        }

                        function andar() {
                            if (quieto) {
                                return;
                            }
                            parar();
                            reloj = window.setInterval(function () { mostrar(actual + 1); }, 8000);
                        }

                        function parar() {
                            if (reloj) {
                                window.clearInterval(reloj);
                                reloj = null;
                            }
                        }

                        controles.querySelector('[data-anterior]').addEventListener('click', function () { mostrar(actual - 1); andar(); });
                        controles.querySelector('[data-siguiente]').addEventListener('click', function () { mostrar(actual + 1); andar(); });

                        // Quien está leyendo no quiere que la frase se le vaya.
                        caja.addEventListener('mouseenter', parar);
                        caja.addEventListener('mouseleave', andar);

                        mostrar(0);
                        andar();
                    })();
                </script>
            @endif

            @if(($google && $google['enlace']) || $paraResenar)
                <div class="mt-8 flex flex-wrap gap-3 font-modern text-sm">
                    @if($google && $google['enlace'])
                        <a href="{{ $google['enlace'] }}" target="_blank" rel="noopener" data-evento="resenas_google"
                            class="inline-flex items-center gap-2 rounded-lg border border-pg-tiza/25 px-4 py-2.5 font-semibold text-pg-tiza transition-colors hover:border-pg-tiza/60">
                            Ver todas en Google <x-icono nombre="arrow-up-right-from-square" class="text-xs" />
                        </a>
                    @endif
                    @if($paraResenar)
                        <a href="{{ $paraResenar }}" target="_blank" rel="noopener" data-evento="dejar_resena"
                            class="inline-flex items-center gap-2 rounded-lg bg-pg-rojo px-4 py-2.5 font-bold text-white transition-colors hover:bg-pg-rojo-oscuro">
                            <x-icono nombre="star" class="text-xs" /> Deja tu reseña
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endif
