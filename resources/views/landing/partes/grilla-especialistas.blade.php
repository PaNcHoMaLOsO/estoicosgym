{{--
    La grilla de especialistas: un panel alto por persona, con la foto de
    fondo, lo que hace y cómo escribirle. La usan Especialistas y cada página
    de especialidad. Recibe `especialistas` y, si va bajo un título de
    sección, `nivel` = 'h3' para el nombre.
--}}
@php
    // Tantas columnas como especialistas, hasta cuatro: con dos, dos
    // paneles anchos; nunca un tercio de pantalla vacío.
    $columnas = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][count($especialistas)] ?? 'lg:grid-cols-4';
    // En el celular, de a dos: se ven todos sin deslizar de lado.
    // Deslizando se veía uno a medias y había que adivinar que
    // había más.
    $enCelular = count($especialistas) === 1 ? 'grid-cols-1' : 'grid-cols-2';
    $nivel = ($nivel ?? 'h2') === 'h3' ? 'h3' : 'h2';
@endphp

<div class="grid {{ $enCelular }} gap-2 sm:grid-cols-2 sm:gap-3 {{ $columnas }} lg:gap-4">
    @foreach($especialistas as $index => $e)
        <article class="animate-on-scroll group relative flex min-h-[15rem] flex-col justify-end overflow-hidden bg-pg-carbon sm:min-h-[20rem] lg:min-h-[24rem]" style="animation-delay: {{ ($index % 4) * 0.08 }}s">
            @if($e['foto'])
                {{-- Las dos primeras son lo primero que se ve (en el celular van de a dos): sin esperar. --}}
                <img src="{{ $e['foto'] }}" alt="{{ $e['nombre'] }}, {{ $e['especialidad'] }} en {{ $gimnasio['nombre'] }}{{ $web['ciudad'] ? ', ' . $web['ciudad'] : '' }}"
                     @if($index < 2) loading="eager" fetchpriority="high" @else loading="lazy" @endif decoding="async"
                     class="absolute inset-0 h-full w-full object-cover object-top grayscale transition duration-700 group-hover:scale-105 group-hover:grayscale-0">
            @else
                {{-- Sin foto, la inicial llena el panel: un fondo liso se ve vacío. --}}
                <span class="absolute -right-4 -top-6 select-none font-display text-[10rem] sm:-right-6 sm:-top-10 sm:text-[18rem] leading-none text-transparent [-webkit-text-stroke:1.5px_rgba(221,42,50,0.35)] transition-colors duration-700 group-hover:text-pg-rojo/10" aria-hidden="true">
                    {{ mb_strtoupper(mb_substr($e['nombre'], 0, 1)) }}
                </span>
            @endif

            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-pg-negro via-pg-negro/70 to-transparent" aria-hidden="true"></div>

            @if($e['recomendado'])
                <span class="absolute left-2 top-2 bg-pg-negro/75 px-2 py-0.5 font-modern text-[9px] uppercase tracking-[0.15em] text-pg-tiza/80 sm:left-3 sm:top-3 sm:text-[10px]">Recomendado</span>
            @endif

            {{-- LO JUSTO PARA ELEGIR. La presentación iba aquí y con más de
                 dos líneas tapaba la foto; ahora va en su perfil, al que lleva
                 todo el panel. El WhatsApp queda a mano, para quien ya sabe. --}}
            <div class="relative p-3 sm:p-5 lg:p-6">
                <span class="block h-0.5 w-6 bg-pg-rojo transition-all duration-500 group-hover:w-12 sm:w-8" aria-hidden="true"></span>
                <p class="mt-2.5 line-clamp-2 font-modern text-[10px] uppercase leading-snug tracking-[0.12em] text-pg-rojo-claro sm:mt-3 sm:text-[11px] sm:tracking-[0.18em]">{{ $e['especialidad'] }}</p>
                <{{ $nivel }} class="mt-1 font-display text-lg uppercase leading-none text-pg-tiza sm:text-2xl lg:text-[1.7rem]">
                    <a href="{{ $e['perfil'] }}" class="after:absolute after:inset-0">{{ $e['nombre'] }}</a>
                </{{ $nivel }}>
                @if($e['dias'] || $e['modalidad'])
                    <p class="mt-1.5 line-clamp-2 font-modern text-[11px] text-pg-tiza/60 sm:text-xs">{{ collect([$e['dias'], $e['dias'] ? null : $e['modalidad']])->filter()->implode(' · ') }}</p>
                @endif

                <div class="mt-3 flex items-center justify-between gap-2 font-modern text-xs sm:mt-4 sm:text-sm">
                    <span class="inline-flex items-center gap-1.5 border-b border-pg-tiza/30 pb-0.5 text-pg-tiza transition-colors group-hover:border-pg-rojo group-hover:text-pg-rojo-claro">
                        Ver perfil <x-icono nombre="arrow-right" class="text-xs" />
                    </span>
                    @if($e['whatsapp'])
                        <a href="{{ $e['whatsapp'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                           aria-label="Escribirle a {{ $e['nombre'] }} por WhatsApp"
                           class="relative z-10 flex size-8 shrink-0 items-center justify-center rounded-full border border-pg-tiza/25 text-pg-tiza transition-colors hover:border-[#25D366] hover:bg-[#25D366] sm:size-9">
                            <x-icono nombre="whatsapp" class="text-sm sm:text-base" />
                        </a>
                    @endif
                </div>
            </div>
        </article>
    @endforeach
</div>
