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
--}}
@if(count($testimonios ?? []))
    <section id="testimonios" class="relative overflow-hidden py-12 lg:py-24 bg-pg-carbon" aria-roledescription="carrusel" aria-label="Lo que dicen nuestros socios">
        {{-- La comilla gigante de fondo: marca la sección sin otra caja más. --}}
        <span class="pointer-events-none absolute -top-10 left-2 lg:left-10 select-none font-display text-[16rem] lg:text-[26rem] leading-none text-pg-rojo/10" aria-hidden="true">“</span>

        <div class="relative max-w-5xl mx-auto px-5 sm:px-8 lg:px-12">
            <p class="font-modern text-sm uppercase tracking-widest text-pg-rojo-claro">Lo que dicen los socios</p>

            <div class="mt-6 lg:mt-10" data-testimonios>
                @foreach($testimonios as $i => $t)
                    <figure data-testimonio class="{{ $i > 0 ? 'mt-10' : '' }}" id="testimonio-{{ $i + 1 }}">
                        <blockquote class="font-display text-2xl sm:text-4xl lg:text-5xl uppercase leading-tight text-pg-tiza">
                            {{ $t['texto'] }}
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-4 font-modern text-pg-tiza/70">
                            <span class="h-px w-10 bg-pg-rojo" aria-hidden="true"></span>
                            {{ $t['titulo'] }}
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            @if(count($testimonios) > 1)
                {{-- Los controles solo existen con JavaScript: sin él se ven todas. --}}
                <div class="mt-10 hidden items-center gap-6" data-testimonios-controles>
                    <button type="button" data-anterior aria-label="Opinión anterior"
                            class="flex h-11 w-11 items-center justify-center rounded-full border border-pg-tiza/25 text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                        <i class="fas fa-arrow-left text-sm" aria-hidden="true"></i>
                    </button>
                    <button type="button" data-siguiente aria-label="Opinión siguiente"
                            class="flex h-11 w-11 items-center justify-center rounded-full border border-pg-tiza/25 text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro">
                        <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i>
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
        </div>
    </section>
@endif
