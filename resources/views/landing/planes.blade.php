@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Inversión en ti',
        'titulo' => 'Planes y precios',
        'bajada' => 'Precios vigentes, los mismos que se cobran en el mesón. Elige el que calce con tu ritmo.',
    ])

    <section id="planes" class="pb-16 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Los planes y los precios son los del catálogo. --}}
            {{-- Una tabla de precios, no cinco tarjetas: los planes se comparan
                 mejor en columnas separadas por una línea que en cajas sueltas,
                 y cinco recuadros iguales en fila es el sello de la plantilla. --}}
            <div class="grid grid-cols-1 divide-y xl:grid-cols-5 xl:divide-y-0 xl:divide-x divide-pg-tiza/10">
                @forelse($planes as $index => $plan)
                    {{--
                        La tarjeta respira: el nombre arriba, el precio separado
                        por una línea y el botón abajo del todo, a la misma altura
                        en todas. Iba todo apretado contra el borde con el mismo
                        hueco entre cosas que no tienen nada que ver entre sí.

                        El sello del destacado se monta sobre el borde, así que esa
                        tarjeta lleva más aire arriba para que no le caiga encima
                        del nombre.
                    --}}
                    <div class="animate-on-scroll flex h-full flex-col py-7 xl:px-6" style="animation-delay: {{ $index * 0.1 }}s">
                            {{-- Se cuenta, no se decide: es el plan con más membresías
                                 activas. El sello va en línea, encima del nombre, porque
                                 colgado del borde necesitaba una tarjeta de la que colgar.

                                 La fila se reserva SIEMPRE, lleve sello o no: si solo la
                                 ocupara el destacado, su columna bajaría y los precios
                                 dejarían de alinearse entre sí, que es justo lo que uno
                                 viene a comparar. --}}
                            <span class="mb-3 flex h-6 items-center">
                                @if($plan['destacado'])
                                    <span class="whitespace-nowrap bg-pg-rojo text-white text-xs font-bold px-3 py-1 rounded-full font-modern tracking-wide">
                                        EL MÁS ELEGIDO
                                    </span>
                                @endif
                            </span>

                            <h2 class="font-display text-xl uppercase tracking-wide text-pg-tiza">{{ $plan['nombre'] }}</h2>
                            @if($plan['duracion'])
                                <p class="text-pg-tiza/50 font-modern text-sm mt-1">{{ $plan['duracion'] }}</p>
                            @endif

                            <div class="mt-6 pt-6 border-t border-pg-tiza/10">
                                <p class="font-display text-3xl text-pg-tiza">${{ number_format($plan['precio'], 0, ',', '.') }}</p>

                                @if($plan['precio_convenio'])
                                    <p class="mt-2 text-pg-rojo-claro font-modern text-sm">Con convenio: ${{ number_format($plan['precio_convenio'], 0, ',', '.') }}</p>
                                @endif
                            </div>

                            @if($plan['descripcion'])
                                <p class="mt-5 text-pg-tiza/60 font-modern text-sm leading-relaxed">{{ $plan['descripcion'] }}</p>
                            @endif

                            <div class="mt-auto pt-8">
                                <a href="{{ route('landing.contacto') }}" data-evento="elegir_plan" data-plan="{{ $plan['nombre'] }}" class="block w-full text-center py-3 rounded-lg font-semibold transition-colors font-modern text-sm {{ $plan['destacado'] ? 'bg-pg-rojo hover:bg-pg-rojo-oscuro text-white' : 'border border-pg-tiza/20 text-pg-tiza hover:border-pg-rojo hover:text-pg-rojo-claro' }}">
                                    Lo quiero
                                </a>
                            </div>
                    </div>
                @empty
                    <p class="col-span-full text-center text-pg-tiza/60 font-modern">Pregunta por los planes en el mesón.</p>
                @endforelse
            </div>

            @if(collect($planes)->contains(fn ($p) => $p['precio_convenio']))
                <p class="mt-8 text-center text-pg-tiza/60 font-modern">
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
