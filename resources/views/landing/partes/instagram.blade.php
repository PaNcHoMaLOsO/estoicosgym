{{--
    Lo último de Instagram. Sale de Configuración → Página web → Instagram;
    sin ninguna publicación, no se pinta.

    Con la llave de la cuenta puesta, las publicaciones nuevas entran solas
    (ver App\Services\InstagramDelGimnasio); sin ella se cargan en el panel.
    La foto vive aquí, así que la portada no depende de que Instagram responda
    ni carga nada de sus servidores.

    UNA FILA QUE SE DESLIZA, en el teléfono y en el computador: doce fotos en
    cuadrícula serían tres pantallas de scroll en un celular. La tarjeta
    siguiente asoma por el borde, que es lo que dice «hay más».

    SOBRIO, con el negro y rojo del gimnasio: los colores de Instagram en el
    título y en una tarjeta entera se comían la portada, y el dueño lo pidió
    más sutil. Las publicaciones ya traen su propio color; el marco se calla.

    La foto va entera y el texto debajo: son afiches con letras hasta el
    borde, y un texto encima les tapaba la dirección o el precio.
--}}
@if(count($publicaciones ?? []))
    @php($usuarioInstagram = $web['instagram'] ? \Illuminate\Support\Str::of($web['instagram'])->before('?')->rtrim('/')->afterLast('/')->toString() : null)
    <section id="instagram" class="py-10 lg:py-20 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <div class="mb-6 lg:mb-10 flex flex-wrap items-end justify-between gap-4 animate-on-scroll">
                <div>
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Síguenos</span>
                    <h2 class="font-display text-4xl md:text-6xl mt-2 uppercase leading-none text-pg-tiza">LO ÚLTIMO EN INSTAGRAM</h2>
                </div>
                @if($web['instagram'])
                    <a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" data-evento="instagram_gimnasio" data-detalle="portada"
                       class="inline-flex items-center gap-2 font-modern text-sm text-pg-tiza/70 transition-colors hover:text-pg-rojo-claro">
                        <x-icono nombre="instagram" /> {{ $usuarioInstagram ? '@' . $usuarioInstagram : 'Ver el perfil' }}
                    </a>
                @endif
            </div>

            <div class="relative" data-fila-instagram>
                <ul data-pista class="-mx-5 flex snap-x gap-3 overflow-x-auto scroll-smooth px-5 pb-2 scroll-px-5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden sm:mx-0 sm:px-0 sm:scroll-px-0 lg:gap-4">
                    @foreach($publicaciones as $p)
                        <li class="w-[64%] shrink-0 snap-start sm:w-56 lg:w-64">
                            <a href="{{ $p['enlace'] }}" target="_blank" rel="noopener" data-evento="instagram_publicacion" data-detalle="{{ $p['titulo'] }}"
                               class="group block">
                                <span class="relative block aspect-[4/5] overflow-hidden rounded-lg bg-pg-carbon">
                                    <img src="{{ $p['imagen'] }}" {{ \App\Support\MedidasDeImagen::atributos($p['imagen']) }} alt="" loading="lazy" decoding="async"
                                         class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.03] group-hover:opacity-90">
                                    @if($p['reel'])
                                        {{-- Un reel: quien lo abre sabe que es un video. --}}
                                        <span class="absolute right-2.5 top-2.5 grid size-7 place-items-center rounded-full bg-pg-negro/65 text-pg-tiza" aria-label="Reel">
                                            <svg viewBox="0 0 12 12" class="ml-0.5 size-2.5" fill="currentColor" aria-hidden="true"><path d="M2.5 1.2v9.6L10.5 6z"/></svg>
                                        </span>
                                    @endif
                                </span>
                                <span class="mt-3 block line-clamp-2 font-modern text-sm leading-snug text-pg-tiza/70 transition-colors group-hover:text-pg-tiza">{{ $p['titulo'] }}</span>
                            </a>
                        </li>
                    @endforeach

                    @if($web['instagram'])
                        {{-- La fila termina en la puerta al perfil, no en un borde vacío. --}}
                        <li class="w-[64%] shrink-0 snap-start sm:w-56 lg:w-64">
                            <a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" data-evento="instagram_gimnasio" data-detalle="fin de la fila"
                               class="group flex aspect-[4/5] flex-col items-center justify-center gap-3 rounded-lg border border-pg-tiza/15 p-5 text-center transition-colors hover:border-pg-rojo">
                                <x-icono nombre="instagram" class="text-2xl text-pg-tiza/60 transition-colors group-hover:text-pg-rojo-claro" />
                                <span class="font-modern text-sm text-pg-tiza/70">Ver más en el perfil</span>
                                <span class="inline-flex items-center gap-1.5 font-modern text-xs uppercase tracking-wider text-pg-tiza/45 transition-colors group-hover:text-pg-rojo-claro">
                                    Abrir Instagram <x-icono nombre="arrow-right" class="text-[0.625rem]" />
                                </span>
                            </a>
                        </li>
                    @endif
                </ul>

                {{-- Las flechas, donde hay mouse: en el teléfono se desliza con el dedo. --}}
                @foreach([[-1, 'Anteriores', 'arrow-left', 'left-0 -translate-x-1/2'], [1, 'Siguientes', 'arrow-right', 'right-0 translate-x-1/2']] as [$paso, $rotulo, $icono, $lado])
                    <button type="button" data-mover="{{ $paso }}" aria-label="{{ $rotulo }}" hidden
                            class="absolute top-[40%] z-10 hidden size-10 -translate-y-1/2 place-items-center rounded-full border border-pg-tiza/20 bg-pg-negro/85 text-pg-tiza transition-colors hover:border-pg-rojo hover:text-pg-rojo-claro lg:[&:not([hidden])]:grid {{ $lado }}">
                        <x-icono :nombre="$icono" class="text-xs" />
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <script>
        // Las flechas avanzan casi un ancho y cada una se esconde en su extremo.
        (function () {
            var fila = document.querySelector('[data-fila-instagram]');
            if (!fila) return;
            var pista = fila.querySelector('[data-pista]');
            var flechas = fila.querySelectorAll('[data-mover]');
            function medir() {
                var resto = pista.scrollWidth - pista.clientWidth - pista.scrollLeft;
                flechas.forEach(function (f) {
                    f.hidden = f.dataset.mover === '-1' ? pista.scrollLeft < 4 : resto < 4;
                });
            }
            flechas.forEach(function (f) {
                f.addEventListener('click', function () {
                    pista.scrollBy({ left: Number(f.dataset.mover) * pista.clientWidth * 0.8, behavior: 'smooth' });
                });
            });
            pista.addEventListener('scroll', medir, { passive: true });
            window.addEventListener('resize', medir);
            window.addEventListener('load', medir);
            medir();
        })();
    </script>
@endif
