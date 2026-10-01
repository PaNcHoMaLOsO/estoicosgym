@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Sala de máquinas',
        'titulo' => 'Ejercicios del gimnasio',
        'bajada' => 'Lo que puedes hacer en la sala, por grupo muscular.',
        'compacta' => true,
    ])

    <section class="bg-pg-negro pb-14">
        <div class="mx-auto max-w-5xl px-4 sm:px-8">

            {{-- Saltar a un grupo: anclas, sin JavaScript. --}}
            @if(count($grupos) > 1)
                <nav aria-label="Grupos musculares" class="-mx-4 mb-8 flex gap-2 overflow-x-auto px-4 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0 [&::-webkit-scrollbar]:hidden">
                    @foreach($grupos as $clave => $grupo)
                        <a href="#{{ $clave }}" class="shrink-0 rounded-lg bg-pg-grafito px-4 py-2 font-modern text-sm font-semibold text-pg-tiza/70 transition-colors hover:text-pg-tiza">
                            {{ $grupo['nombre'] }}
                        </a>
                    @endforeach
                </nav>
            @endif

            @forelse($grupos as $clave => $grupo)
                <div id="{{ $clave }}" class="mb-12 scroll-mt-24">
                    <h2 class="border-b border-pg-tiza/10 pb-2 font-display text-xl uppercase text-pg-tiza">{{ $grupo['nombre'] }}</h2>
                    <ul class="grid gap-x-8 sm:grid-cols-2">
                        @foreach($grupo['ejercicios'] as $e)
                            <li class="flex gap-4 border-b border-pg-tiza/10 py-4">
                                @include('landing.partes.imagen-ejercicio', ['e' => $e, 'tamano' => 'size-24 sm:size-28'])

                                <div class="min-w-0 font-modern">
                                    <h3 class="font-semibold text-pg-tiza">{{ $e['nombre'] }}</h3>
                                    @if($e['equipo'])
                                        <p class="text-xs text-pg-tiza/45">{{ $e['equipo'] }}</p>
                                    @endif
                                    @if($e['nota'])
                                        <p class="mt-1.5 text-sm text-pg-tiza/60">{{ $e['nota'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="font-modern text-sm text-pg-tiza/70">Pronto vas a ver aquí los ejercicios de la sala.</p>
            @endforelse

            <p class="font-modern text-sm text-pg-tiza/60">
                ¿No sabes por dónde partir?
                <a href="{{ route('landing.rutina') }}" class="text-pg-tiza underline underline-offset-4 hover:text-white">Mira las rutinas</a>
            </p>
        </div>
    </section>
@endsection
