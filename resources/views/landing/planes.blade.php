@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @php
        // EL PASE SUELTO NO ES UNA MENSUALIDAD y no se compara con los planes.
        // Puesto en la misma fila era el primero por precio, y la página abría
        // anunciando $5.000 como si eso fuera lo que cuesta ser socio. Va aparte,
        // debajo, en una línea.
        $mensualidades = array_values(array_filter($planes, fn (array $p) => ! $p['es_pase']));
        $pases = array_values(array_filter($planes, fn (array $p) => $p['es_pase']));

        // Las clases van escritas enteras: el CSS compilado solo trae las que
        // encuentra tal cual, una armada al vuelo no existiría.
        $columnas = [
            1 => 'lg:grid-cols-1',
            2 => 'lg:grid-cols-2',
            3 => 'lg:grid-cols-3',
            4 => 'lg:grid-cols-4',
            5 => 'lg:grid-cols-5',
        ][min(5, max(1, count($mensualidades)))];
    @endphp

    @include('landing.partes.cabecera', [
        'antetitulo' => 'Inversión en ti',
        'titulo' => 'Planes y precios',
        'bajada' => 'Elige el que calce con tu ritmo.',
        'compacta' => true,
    ])

    <section id="planes" class="pb-10 lg:pb-14 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            {{-- Los planes y los precios son los del catálogo. --}}
            {{-- Una tabla de precios, no cinco tarjetas: los planes se comparan
                 mejor en columnas separadas por una línea que en cajas sueltas,
                 y cinco recuadros iguales en fila es el sello de la plantilla. --}}
            {{--
                EN EL TELÉFONO, UNA FILA POR PLAN: el nombre a la izquierda, el
                precio y el botón a la derecha. Antes cada plan era un bloque
                alto a todo el ancho, con el botón abajo: cabía uno y medio por
                pantalla y había que bajar cinco pantallas para compararlos.
                Así caben todos de un vistazo.

                Desde 1024 px ya van en columnas —antes recién desde 1280, y en
                un portátil seguían apilados con media pantalla vacía—.
            --}}
            @php
                // Lo que cuesta el mes suelto: contra eso se mide el ahorro de
                // los planes largos. Sale del catálogo, no se escribe a mano.
                $mensual = collect($planes)->firstWhere('meses', 1)['precio'] ?? null;
            @endphp
            <div class="grid grid-cols-1 divide-y {{ $columnas }} lg:divide-y-0 lg:divide-x divide-pg-tiza/10 border-y border-pg-tiza/10 lg:border-y-0">
                @forelse($mensualidades as $index => $plan)
                    @php
                        $alMes = ($plan['meses'] ?? 0) > 1 ? (int) round($plan['precio'] / $plan['meses'], -1) : null;
                        $ahorro = $alMes && $mensual ? (int) round((1 - $alMes / $mensual) * 100) : 0;
                    @endphp
                    <div class="animate-on-scroll grid grid-cols-[1fr_auto] items-center gap-x-4 gap-y-2 py-4 lg:flex lg:h-full lg:flex-col lg:items-stretch lg:gap-0 lg:px-5 lg:py-2 xl:px-6" style="animation-delay: {{ $index * 0.08 }}s">
                        <div class="row-span-2 min-w-0">
                            {{-- Se cuenta, no se decide: es el plan con más membresías
                                 activas. En columnas la fila del sello se reserva
                                 SIEMPRE, lleve sello o no: si solo la ocupara el
                                 destacado, su columna bajaría y los precios dejarían
                                 de alinearse. En el teléfono no hace falta: cada
                                 plan es su propia fila. --}}
                            <span class="{{ $plan['destacado'] ? 'flex mb-1.5' : 'hidden' }} lg:flex lg:mb-3 lg:h-6 items-center">
                                @if($plan['destacado'])
                                    <span class="whitespace-nowrap bg-pg-rojo text-white text-[0.65rem] lg:text-xs font-bold px-2.5 py-0.5 lg:px-3 lg:py-1 rounded-full font-modern tracking-wide">
                                        EL MÁS ELEGIDO
                                    </span>
                                @endif
                            </span>

                            <h2 class="font-display text-lg lg:text-xl uppercase tracking-wide text-pg-tiza">{{ $plan['nombre'] }}</h2>
                            @if($plan['duracion'])
                                <p class="text-pg-tiza/50 font-modern text-sm lg:mt-1">{{ $plan['duracion'] }}</p>
                            @endif
                            @if($alMes)
                                <p class="mt-1 font-modern text-xs text-pg-tiza/70 lg:hidden">
                                    ${{ number_format($alMes, 0, ',', '.') }} al mes
                                    @if($ahorro > 0)<span class="text-emerald-400 font-semibold ml-1.5">ahorras {{ $ahorro }}%</span>@endif
                                </p>
                            @endif
                        </div>

                        <div class="text-right lg:text-left lg:mt-5 lg:pt-5 lg:border-t lg:border-pg-tiza/10">
                            <p class="font-display text-2xl lg:text-3xl text-pg-tiza leading-none">${{ number_format($plan['precio'], 0, ',', '.') }}</p>

                            {{-- Lo que sale al mes y lo que se ahorra: es la cuenta que
                                 todos hacen de cabeza al comparar un trimestral con
                                 tres mensuales. La línea se reserva en todas las
                                 columnas para que lo de abajo no baile. --}}
                            <p class="hidden lg:block mt-2 min-h-5 whitespace-nowrap font-modern text-xs xl:text-sm text-pg-tiza/70">
                                @if($alMes)
                                    ${{ number_format($alMes, 0, ',', '.') }} al mes
                                    @if($ahorro > 0)<span class="text-emerald-400 font-semibold ml-1.5">−{{ $ahorro }}%</span>@endif
                                @endif
                            </p>

                            @if($plan['precio_convenio'])
                                <p class="mt-1 lg:mt-2 text-pg-rojo-claro font-modern text-xs lg:text-sm">Con convenio: ${{ number_format($plan['precio_convenio'], 0, ',', '.') }}</p>
                            @endif
                        </div>

                        @if($plan['descripcion'])
                            <p class="hidden lg:block mt-4 text-pg-tiza/60 font-modern text-sm leading-relaxed">{{ $plan['descripcion'] }}</p>
                        @endif

                        <div class="col-start-2 lg:mt-auto lg:pt-6">
                            <a href="{{ route('landing.contacto') }}" data-evento="elegir_plan" data-plan="{{ $plan['nombre'] }}" class="ml-auto block w-28 lg:w-full text-center whitespace-nowrap px-4 py-2 lg:py-3 rounded-lg font-semibold transition-colors font-modern text-sm {{ $plan['destacado'] ? 'bg-pg-rojo hover:bg-pg-rojo-oscuro text-white' : 'border border-pg-tiza/20 text-pg-tiza hover:border-pg-rojo hover:text-pg-rojo-claro' }}">
                                Lo quiero
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full py-8 text-center text-pg-tiza/60 font-modern">Pregunta por los planes en el mesón.</p>
                @endforelse
            </div>

            @if($pases)
                {{-- Aparte y debajo: quien viene un día suelto no está eligiendo
                     mensualidad, y mezclarlo con los planes confunde las dos cosas. --}}
                <div class="mt-10 border-t border-pg-tiza/10 pt-6 text-center">
                    <p class="font-modern text-pg-tiza/60">
                        ¿Vienes solo por un día?
                        @foreach($pases as $pase)
                            <span class="text-pg-tiza">{{ $pase['nombre'] }} a ${{ number_format($pase['precio'], 0, ',', '.') }}</span>{{ $loop->last ? '.' : ', ' }}
                        @endforeach
                    </p>
                </div>
            @endif

            @if(collect($mensualidades)->contains(fn ($p) => $p['precio_convenio']))
                <p class="mt-6 text-center text-pg-tiza/60 font-modern text-sm lg:text-base">
                    ¿Estudias o trabajas en una institución con convenio?
                    @if($navegacion['convenios'])
                        <a href="{{ route('landing.convenios') }}" class="text-pg-rojo-claro hover:underline">Mira los convenios</a>.
                    @else
                        Pregunta por el precio de convenio.
                    @endif
                </p>
            @endif
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
