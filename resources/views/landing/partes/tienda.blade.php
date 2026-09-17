{{--
    El apartado de la tienda de suplementos.

    Es OTRO negocio con su propia web, así que el botón se va fuera y por eso
    abre en una pestaña nueva: quien entra al gimnasio no tiene por qué perder
    la página en la que estaba. Sale de Configuración → Página web; sin
    dirección, el controlador no lo manda y esto no se pinta.
--}}
@if($tienda ?? null)
    <section id="tienda" class="py-16 bg-pg-carbon border-y border-pg-tiza/10">
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 items-center {{ $tienda['imagen'] ? 'lg:grid-cols-2' : '' }}">
                @if($tienda['imagen'])
                    <div class="animate-on-scroll overflow-hidden rounded-2xl border border-pg-tiza/10">
                        {{-- Con su proporción, sin recortar: el recuadro fijo a 4:3
                             le comía un trozo arriba y abajo. --}}
                        <img src="{{ $tienda['imagen'] }}" alt="{{ $tienda['titulo'] }}" loading="lazy"
                             class="w-full h-auto">
                    </div>
                @endif

                <div class="animate-on-scroll {{ $tienda['imagen'] ? '' : 'max-w-3xl' }}">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">También nuestro</span>
                    <h2 class="font-display text-3xl md:text-4xl mt-4 text-pg-tiza uppercase">{{ $tienda['titulo'] }}</h2>

                    @if($tienda['texto'])
                        <p class="mt-5 text-pg-tiza/70 font-modern text-base leading-relaxed">{{ $tienda['texto'] }}</p>
                    @endif

                    <a href="{{ $tienda['url'] }}" target="_blank" rel="noopener"
                       data-evento="tienda_suplementos"
                       class="mt-8 inline-flex items-center gap-3 bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold px-8 py-3 rounded-lg text-base transition-colors font-modern">
                        Ver la tienda
                        <i class="fas fa-arrow-up-right-from-square text-sm" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endif
