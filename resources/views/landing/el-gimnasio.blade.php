@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    {{--
        OJO con el orden: este bloque de PHP va PRIMERO, antes de cualquier
        bloque de una sola linea. Blade toma desde la primera apertura hasta
        el primer cierre, incluso si aparecen dentro de un comentario como
        este, asi que una apertura mas arriba se traga la vista entera y la
        pagina queda en blanco. Por eso aqui no se nombran las directivas.

        Todo lo de aqui sale del sistema, nada escrito a mano: si un dato
        falta, la tarjeta correspondiente simplemente no aparece.
    --}}
    @php
        $fotoPortada = $fotos[0] ?? null;

        // El horario de hoy y el plan mas barato: los planes ya vienen
        // ordenados por precio desde el controlador.
        $hoy = $horario['configurado'] ? collect($horario['dias'])->firstWhere('clave', $horario['hoy']) : null;
        $masBarato = $planes[0] ?? null;

        $datos = array_values(array_filter([
            $hoy ? [
                'icono' => 'clock',
                'rotulo' => 'Hoy ' . mb_strtolower($hoy['nombre']),
                'valor' => $hoy['tramos'] ? implode(' · ', array_map(fn ($t) => $t[0] . ' – ' . $t[1], $hoy['tramos'])) : 'Cerrado',
            ] : null,
            $masBarato ? [
                'icono' => 'ticket',
                'rotulo' => 'Planes desde',
                'valor' => '$' . number_format($masBarato['precio'], 0, ',', '.'),
                'apoyo' => $masBarato['nombre'] . ($masBarato['duracion'] ? ' · ' . $masBarato['duracion'] : ''),
                'href' => route('landing.planes'),
            ] : null,
            $navegacion['convenios'] ? [
                'icono' => 'graduation-cap',
                'rotulo' => 'Convenios',
                'valor' => 'Estudiantes y empresas',
                'apoyo' => 'Con credencial vigente',
                'href' => route('landing.convenios'),
            ] : null,
            $gimnasio['direccion'] || $web['ciudad'] ? [
                'icono' => 'map-marker-alt',
                'rotulo' => 'Dónde estamos',
                'valor' => $gimnasio['direccion'] ?: $web['ciudad'],
                'apoyo' => $gimnasio['direccion'] ? $web['ciudad'] : $web['region'],
                'href' => $web['google_maps'] ?: null,
            ] : null,
        ]));

        // Las clases van escritas enteras: el CSS compilado solo trae las que
        // encuentra tal cual, una armada al vuelo no existiria.
        $columnas = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'][count($datos)] ?? 'lg:grid-cols-4';
    @endphp

    {{--
        La portada, con la primera foto del gimnasio.

        Antes era el titulo sobre negro: en una pantalla ancha se veia una
        franja vacia justo donde hay que mostrar el local. Sin fotos cargadas
        cae al degradado de siempre, asi que la pagina nunca queda rota.
    --}}
    <section class="relative flex min-h-[62vh] items-end overflow-hidden">
        @if($fotoPortada)
            <div class="absolute inset-0" aria-hidden="true">
                <img src="{{ $fotoPortada['imagen'] }}" alt="" class="portada-foto h-full w-full object-cover">
                <div class="absolute inset-0 bg-linear-to-t from-pg-negro via-pg-negro/80 to-pg-negro/40"></div>
            </div>
        @else
            <div class="absolute inset-0 bg-linear-to-br from-pg-negro via-pg-carbon to-pg-grafito" aria-hidden="true"></div>
        @endif

        <div class="relative z-10 w-full max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-12 fade-in">
            <p class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Por dentro</p>
            <h1 class="font-display text-4xl md:text-5xl lg:text-6xl text-pg-tiza mt-3 uppercase leading-[0.95]">El gimnasio</h1>
            <p class="text-pg-tiza/75 mt-5 max-w-2xl font-modern text-base">
                {{ $gimnasio['nombre'] }} es un gimnasio{{ $web['ciudad'] ? ' en ' . $web['ciudad'] : '' }}. Esto es lo que encuentras al entrar.
            </p>

            <div class="mt-9 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('landing.planes') }}"
                   class="inline-flex items-center justify-center bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold px-8 py-3 rounded-lg text-base transition-colors font-modern">
                    Ver planes
                </a>
                @if($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio"
                       class="inline-flex items-center justify-center gap-2 border-2 border-pg-tiza/30 hover:border-pg-rojo text-pg-tiza hover:text-pg-rojo-claro px-8 py-3 rounded-lg text-base transition-colors font-modern">
                        <i class="fab fa-whatsapp text-xl" aria-hidden="true"></i> Preguntar por WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Lo que alguien quiere saber antes de venir, de una sola mirada. --}}
    @if($datos)
        <section class="bg-pg-carbon border-y border-pg-tiza/10">
            <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 {{ $columnas }} lg:divide-x divide-pg-tiza/10">
                @foreach($datos as $dato)
                    @php($etiqueta = ($dato['href'] ?? null) ? 'a' : 'div')
                    <{{ $etiqueta }} @if($dato['href'] ?? null) href="{{ $dato['href'] }}" @endif
                        class="flex items-center gap-4 px-2 lg:px-8 py-7 {{ ($dato['href'] ?? null) ? 'transition-colors hover:bg-pg-negro/40' : '' }}">
                        {{-- El icono solo, sin el cuadrado de color detrás: igual
                             que en el resto de la web. --}}
                        <i class="fas fa-{{ $dato['icono'] }} text-pg-rojo-claro text-base w-5 shrink-0 text-center" aria-hidden="true"></i>
                        <span class="min-w-0">
                            <span class="block text-pg-tiza/50 font-modern text-xs uppercase tracking-widest">{{ $dato['rotulo'] }}</span>
                            <span class="block text-pg-tiza font-modern text-base">{{ $dato['valor'] }}</span>
                            @if($dato['apoyo'] ?? null)
                                <span class="block text-pg-tiza/45 font-modern text-sm">{{ $dato['apoyo'] }}</span>
                            @endif
                        </span>
                    </{{ $etiqueta }}>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== LO QUE HAY ADENTRO: sale de Pagina web -> Servicios ===== --}}
    @if(count($servicios))
        <section id="servicios" class="py-16 bg-pg-negro">
            <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Para entrenar</span>
                    <h2 class="font-display text-3xl md:text-4xl mt-4 text-pg-tiza">LO QUE ENCUENTRAS</h2>
                </div>
                @include('landing.partes.servicios')
            </div>
        </section>
    @endif

    {{-- ===== GALERIA: sale de Pagina web -> Fotos ===== --}}
    @if(count($fotos))
        <section id="fotos" class="py-16 bg-pg-carbon">
            <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Así se ve</span>
                    <h2 class="font-display text-3xl md:text-4xl mt-4 text-pg-tiza">EL GIMNASIO EN FOTOS</h2>
                </div>

                {{--
                    CADA FOTO CON SU PROPIA PROPORCIÓN. Antes era un mosaico de
                    huecos fijos: las fotos son verticales y los huecos
                    horizontales, así que entraban recortadas y a más de una le
                    cortaba la cabeza. Aquí se colocan en columnas y cada una
                    ocupa el alto que le toca, sin recortar nada.

                    `break-inside-avoid` evita que una foto se parta entre el
                    final de una columna y el principio de la siguiente.
                --}}
                <div class="columns-2 lg:columns-3 xl:columns-4 gap-4">
                    @foreach($fotos as $i => $foto)
                        <figure class="animate-on-scroll group relative mb-4 break-inside-avoid overflow-hidden rounded-2xl bg-pg-negro"
                                style="animation-delay: {{ ($i % 4) * 0.1 }}s">
                            {{-- SIN RÓTULO ENCIMA. Las fotos del gimnasio se explican
                                 solas y el nombre no lo puso nadie: eran títulos de
                                 relleno tapando justo la parte de abajo de la foto.
                                 El texto sigue en `alt`, que no se ve pero es lo que
                                 lee Google y quien navega con lector de pantalla. --}}
                            <img src="{{ $foto['imagen'] }}" alt="{{ $foto['titulo'] }}" loading="lazy"
                                 class="w-full h-auto transition-transform duration-700 group-hover:scale-105">
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('landing.partes.horario')
    @include('landing.partes.llamado')
@endsection
