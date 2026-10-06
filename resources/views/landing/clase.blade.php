@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    La página de UNA clase: /clases/judo.

    Lo que alguien quiere saber antes de venir, en este orden: qué días y a
    qué hora, para quién es, con quién, cuánto sale y cómo inscribirse. Sin
    cajas: la foto suelta, el texto al lado y líneas finas entre las partes,
    igual que la lista de clases. El color de la clase es el mismo punto del
    calendario, en línea (style) porque la hoja está compilada.
--}}
@section('content')
    @php($c = $clase)
    <section class="bg-pg-negro pt-24 pb-12 lg:pt-32 lg:pb-20">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <a href="{{ route('landing.clases') }}" class="inline-flex items-center gap-2 font-modern text-sm text-pg-tiza/60 transition-colors hover:text-pg-tiza">
                <x-icono nombre="arrow-left" class="text-xs" /> Clases
            </a>

            <div class="mt-6 grid gap-8 lg:mt-8 {{ $c['imagen'] ? 'lg:grid-cols-2' : '' }} lg:gap-14">
                @if($c['imagen'])
                    <img src="{{ $c['imagen'] }}" alt="Clase de {{ $c['nombre'] }} en {{ $gimnasio['nombre'] }}{{ $web['ciudad'] ? ', ' . $web['ciudad'] : '' }}"
                         loading="eager" fetchpriority="high" decoding="async"
                         class="aspect-[4/3] w-full rounded-md object-cover lg:sticky lg:top-28 lg:self-start">
                @endif

                <div class="min-w-0 {{ $c['imagen'] ? '' : 'max-w-3xl' }}">
                    <p class="flex items-center gap-2.5 font-modern text-xs uppercase tracking-[0.2em] text-pg-rojo-claro sm:text-sm">
                        <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $c['color'] }}" aria-hidden="true"></span>
                        Abierta a todos
                    </p>
                    <h1 class="mt-2 font-display text-4xl uppercase leading-[0.95] text-pg-tiza md:text-5xl">{{ $tituloClase }}</h1>

                    @if($c['para_quien'])
                        <p class="mt-4 font-modern text-base text-pg-tiza/80">{{ $c['para_quien'] }}</p>
                    @endif
                    @if($c['descripcion'])
                        <p class="mt-3 max-w-2xl font-modern text-base leading-relaxed text-pg-tiza/70">{{ $c['descripcion'] }}</p>
                    @endif

                    <h2 class="mt-8 font-display text-xl uppercase text-pg-tiza">Horario</h2>
                    <dl class="mt-2 divide-y divide-pg-tiza/10 border-y border-pg-tiza/10 font-modern text-sm">
                        @foreach($c['horario'] as $b)
                            <div class="flex items-baseline justify-between gap-3 py-2.5">
                                <dt class="text-pg-tiza/70">{{ \App\Models\Clase::DIAS[$b['dia']] ?? $b['dia'] }}</dt>
                                <dd class="tabular-nums text-pg-tiza">{{ $b['desde'] }} a {{ $b['hasta'] }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if($c['profesor'])
                        <p class="mt-4 font-modern text-sm text-pg-tiza/60">Con {{ $c['profesor'] }}</p>
                    @endif

                    <div class="mt-6 flex items-center justify-between gap-4 sm:justify-start sm:gap-8">
                        @if($c['precio_texto'])
                            <p class="font-modern text-pg-tiza"><span class="font-display text-3xl">{{ $c['precio_texto'] }}</span> <span class="text-sm text-pg-tiza/50">al mes</span></p>
                        @else
                            <p class="font-modern text-sm text-pg-tiza/60">Consulta el valor</p>
                        @endif

                        @if($c['whatsapp'])
                            <a href="{{ $c['whatsapp'] }}" target="_blank" rel="noopener" data-evento="inscripcion_clase" data-detalle="{{ $c['nombre'] }}"
                               class="inline-flex shrink-0 items-center gap-2 px-5 py-3 rounded-lg bg-[#25D366] hover:brightness-110 text-white font-modern text-sm font-semibold transition-all">
                                <x-icono nombre="whatsapp" class="text-base" /> Inscribirme
                            </a>
                        @else
                            <a href="{{ route('landing.contacto') }}" class="inline-flex shrink-0 items-center px-5 py-3 rounded-lg bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-modern text-sm font-semibold transition-colors">Inscribirme</a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- La galería: cada foto con su proporción, en columnas, como la
                 del gimnasio. Tocarla la abre entera. --}}
            @if($c['fotos'])
                <div class="mt-14 lg:mt-20">
                    <h2 class="font-display text-2xl uppercase text-pg-tiza">Fotos</h2>
                    <div class="mt-4 columns-2 gap-3 lg:columns-3 lg:gap-4">
                        @foreach($c['fotos'] as $i => $f)
                            <a href="{{ $f['url'] }}" target="_blank" rel="noopener" class="group mb-3 block break-inside-avoid overflow-hidden rounded-md bg-pg-carbon lg:mb-4">
                                <img src="{{ $f['url'] }}" alt="Clase de {{ $c['nombre'] }} en {{ $gimnasio['nombre'] }}, foto {{ $i + 1 }}"
                                     @if($f['medidas']) width="{{ $f['medidas'][0] }}" height="{{ $f['medidas'][1] }}" @endif
                                     loading="lazy" decoding="async"
                                     class="h-auto w-full transition-transform duration-700 group-hover:scale-105">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($otras)
                <div class="mt-14 lg:mt-20">
                    <h2 class="font-display text-2xl uppercase text-pg-tiza">Otras clases</h2>
                    <ul class="mt-3 divide-y divide-pg-tiza/10 border-y border-pg-tiza/10">
                        @foreach($otras as $o)
                            <li>
                                <a href="{{ $o['url'] }}" class="group flex items-center gap-3 py-3.5 font-modern">
                                    <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $o['color'] }}" aria-hidden="true"></span>
                                    <span class="min-w-0">
                                        <span class="block font-display text-lg uppercase leading-tight text-pg-tiza transition-colors group-hover:text-pg-rojo-claro">{{ $o['nombre'] }}</span>
                                        <span class="block text-xs tabular-nums text-pg-tiza/50">{{ $o['horario_texto'] }}</span>
                                    </span>
                                    <x-icono nombre="chevron-right" class="ml-auto text-xs text-pg-tiza/35" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>
@endsection
