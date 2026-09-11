@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Inversión en ti',
        'titulo' => 'Planes y precios',
        'bajada' => 'Precios vigentes, los mismos que se cobran en el mesón. Elige el que calce con tu ritmo.',
    ])

    <section id="planes" class="pb-24 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Los planes y los precios son los del catálogo. --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                @forelse($planes as $index => $plan)
                    <div class="animate-on-scroll" style="animation-delay: {{ $index * 0.1 }}s">
                        <div class="h-full flex flex-col bg-pg-carbon/70 border {{ $plan['destacado'] ? 'border-pg-rojo ring-2 ring-pg-rojo/30' : 'border-pg-tiza/10' }} rounded-2xl p-6 relative card-hover">
                            @if($plan['destacado'])
                                {{-- Se cuenta, no se decide: es el plan con más membresías activas. --}}
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap bg-pg-rojo text-white text-xs font-bold px-3 py-1 rounded-full font-modern tracking-wide">
                                    EL MÁS ELEGIDO
                                </span>
                            @endif

                            <h2 class="font-display text-2xl uppercase tracking-wide text-pg-tiza">{{ $plan['nombre'] }}</h2>
                            @if($plan['duracion'])
                                <p class="text-pg-tiza/50 font-modern text-sm">{{ $plan['duracion'] }}</p>
                            @endif

                            <p class="mt-5 font-display text-4xl text-pg-tiza">${{ number_format($plan['precio'], 0, ',', '.') }}</p>

                            @if($plan['precio_convenio'])
                                <p class="mt-1 text-pg-rojo-claro font-modern text-sm">Con convenio: ${{ number_format($plan['precio_convenio'], 0, ',', '.') }}</p>
                            @endif

                            @if($plan['descripcion'])
                                <p class="mt-4 text-pg-tiza/60 font-modern text-sm">{{ $plan['descripcion'] }}</p>
                            @endif

                            <div class="mt-auto pt-6">
                                <a href="{{ route('landing.contacto') }}" data-evento="elegir_plan" data-plan="{{ $plan['nombre'] }}" class="block w-full text-center py-3 rounded-lg font-semibold transition-all font-modern text-sm {{ $plan['destacado'] ? 'bg-pg-rojo hover:bg-pg-rojo-oscuro text-white btn-glow' : 'border border-pg-tiza/20 text-pg-tiza hover:border-pg-rojo hover:text-pg-rojo-claro' }}">
                                    Lo quiero
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-center text-pg-tiza/60 font-modern">Pregunta por los planes en el mesón.</p>
                @endforelse
            </div>

            @if(collect($planes)->contains(fn ($p) => $p['precio_convenio']))
                <p class="mt-12 text-center text-pg-tiza/60 font-modern">
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
