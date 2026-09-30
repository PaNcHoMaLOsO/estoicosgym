@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    LAS CLASES DEL GIMNASIO: judo, lucha olímpica, boxeo… Abiertas a
    cualquiera, socio o no, con mensualidad. No son el arriendo de horas: eso
    es para instituciones y tiene su propia página.

    Primero el calendario, que es lo que se viene a mirar («¿a qué hora es
    judo?»), y después una tarjeta por clase con el precio y el botón para
    inscribirse por WhatsApp.

    Los colores de cada clase van EN LÍNEA (style), no en clases de Tailwind:
    la hoja está compilada y una clase armada con el color de la base no
    existiría.
--}}
@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Horarios y precios',
        'titulo' => $tituloClases,
        'bajada' => 'Abiertas a todos, no hace falta ser socio.',
        'compacta' => true,
    ])

    {{--
        EL CALENDARIO DE LA SEMANA, COMO TABLA DE HORARIOS. Antes era una agenda
        a escala: con una clase a las 11 y el resto en la noche quedaban ocho
        horas vacías entre medio. Ahora cada fila es una hora en que EMPIEZA
        alguna clase, y nada más. Hoy va marcado, y cada clase lleva a su ficha.
    --}}
    @php
        $claves = array_keys(\App\Models\Clase::DIAS);
        $hoy = $claves[now()->dayOfWeekIso - 1];
        $filas = collect($calendario['porDia'])->flatten(1)->pluck('desde')->unique()->sort()->values();
        $ancla = fn (string $nombre) => 'clase-' . \Illuminate\Support\Str::slug($nombre);
    @endphp
    <section id="calendario" class="bg-pg-negro pb-10 lg:pb-14">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <h2 class="sr-only">Horario de las clases</h2>

            {{-- En el celular, una lista por día, sin cajas: el día y debajo sus clases. --}}
            <div class="md:hidden divide-y divide-pg-tiza/10 border-y border-pg-tiza/10">
                @foreach($calendario['porDia'] as $dia => $bloques)
                    <div class="py-3">
                        <h3 class="flex items-center gap-2 font-display text-base uppercase tracking-wide {{ $dia === $hoy ? 'text-pg-rojo-claro' : 'text-pg-tiza' }}">
                            {{ $calendario['dias'][$dia] }}
                            @if($dia === $hoy)<span class="font-modern text-[0.65rem] normal-case tracking-normal text-pg-rojo-claro">hoy</span>@endif
                        </h3>
                        <ul class="mt-1.5 space-y-1">
                            @foreach($bloques as $b)
                                <li>
                                    <a href="#{{ $ancla($b['nombre']) }}" class="flex items-center gap-2.5 font-modern text-sm">
                                        <span class="size-2 shrink-0 rounded-full" style="background: {{ $b['color'] }}" aria-hidden="true"></span>
                                        <span class="w-24 shrink-0 tabular-nums text-pg-tiza/55">{{ $b['desde'] }} a {{ $b['hasta'] }}</span>
                                        <span class="font-semibold text-pg-tiza">{{ $b['nombre'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- En pantalla ancha, la tabla: días arriba, horas de inicio al lado. --}}
            <div class="hidden md:block animate-on-scroll overflow-x-auto">
                <table class="w-full table-fixed border-collapse font-modern text-sm">
                    <thead>
                        <tr>
                            <th class="w-20" scope="col"><span class="sr-only">Hora</span></th>
                            @foreach($calendario['dias'] as $clave => $nombre)
                                <th scope="col" class="border-b-2 pb-2 text-center font-display text-sm font-normal uppercase tracking-wide {{ $clave === $hoy ? 'border-pg-rojo text-pg-rojo-claro' : 'border-pg-tiza/15 text-pg-tiza/80' }}">
                                    {{ $nombre }}@if($clave === $hoy)<span class="ml-1.5 font-modern text-[0.65rem] normal-case tracking-normal">hoy</span>@endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filas as $desde)
                            <tr class="border-b border-pg-tiza/10">
                                <th scope="row" class="py-3 pr-3 text-left align-top font-normal tabular-nums text-pg-tiza/45">{{ $desde }}</th>
                                @foreach($calendario['dias'] as $clave => $nombre)
                                    <td class="px-1.5 py-2 align-top {{ $clave === $hoy ? 'bg-pg-tiza/[0.03]' : '' }}">
                                        @foreach(collect($calendario['porDia'][$clave] ?? [])->where('desde', $desde) as $b)
                                            <a href="#{{ $ancla($b['nombre']) }}" class="mb-1 block rounded-md px-2.5 py-2 transition-transform hover:-translate-y-0.5" style="background: {{ $b['color'] }}">
                                                <span class="block font-semibold leading-tight text-white">{{ $b['nombre'] }}</span>
                                                <span class="block text-xs tabular-nums text-white/80">hasta {{ $b['hasta'] }}</span>
                                            </a>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{--
        UNA POR CLASE, SIN CAJA. Iban en tarjetas con borde, franja de color e
        íconos en fila, y se veía hecho con plantilla. Ahora la foto suelta, el
        texto debajo y el precio con el enlace al final, separados por una línea.
    --}}
    <section id="clases" class="bg-pg-carbon border-t border-pg-tiza/10 py-10 lg:py-16">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <h2 class="sr-only">Las clases</h2>

            <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($clases as $index => $c)
                    <article id="{{ $ancla($c['nombre']) }}" class="animate-on-scroll flex scroll-mt-28 flex-col" style="animation-delay: {{ ($index % 3) * 0.08 }}s">
                        @if($c['imagen'])
                            <img src="{{ $c['imagen'] }}" alt="Clase de {{ $c['nombre'] }} en {{ $gimnasio['nombre'] }}" loading="lazy" decoding="async" class="aspect-[4/3] w-full rounded-md object-cover">
                        @endif

                        {{-- El punto es el color de la clase en el calendario de arriba. --}}
                        <h3 class="mt-4 flex items-center gap-2.5 font-display text-2xl uppercase leading-none text-pg-tiza">
                            <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $c['color'] }}" aria-hidden="true"></span>
                            {{ $c['nombre'] }}
                        </h3>

                        <p class="mt-2 font-modern text-sm text-pg-tiza/55 tabular-nums">
                            {{ $c['horario_texto'] }}@if($c['para_quien']) · {{ $c['para_quien'] }}@endif
                        </p>
                        @if($c['descripcion'])
                            <p class="mt-2 font-modern text-sm text-pg-tiza/75">{{ $c['descripcion'] }}</p>
                        @endif
                        @if($c['profesor'])
                            <p class="mt-1 font-modern text-sm text-pg-tiza/55">Con {{ $c['profesor'] }}</p>
                        @endif

                        <div class="mt-auto pt-5">
                        <div class="flex items-center justify-between gap-3 border-t border-pg-tiza/10 pt-4">
                            @if($c['precio_texto'])
                                <p class="font-modern text-pg-tiza"><span class="font-display text-xl">{{ $c['precio_texto'] }}</span> <span class="text-sm text-pg-tiza/50">al mes</span></p>
                            @else
                                <p class="font-modern text-sm text-pg-tiza/60">Consulta el valor</p>
                            @endif

                            @if($c['whatsapp'])
                                <a href="{{ $c['whatsapp'] }}" target="_blank" rel="noopener" data-evento="inscripcion_clase" data-detalle="{{ $c['nombre'] }}"
                                   class="inline-flex shrink-0 items-center gap-2 px-4 py-2.5 rounded-lg bg-[#25D366] hover:brightness-110 text-white font-modern text-sm font-semibold transition-all">
                                    <i class="fab fa-whatsapp text-base" aria-hidden="true"></i> Inscribirme
                                </a>
                            @else
                                <a href="{{ route('landing.contacto') }}" class="inline-flex shrink-0 items-center px-4 py-2.5 rounded-lg bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-modern text-sm font-semibold transition-colors">Inscribirme</a>
                            @endif
                        </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
