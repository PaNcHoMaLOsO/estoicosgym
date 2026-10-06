@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Sala de máquinas',
        'titulo' => 'Rutinas',
        'bajada' => 'Elige una y síguela día a día, con las máquinas de la sala.',
        'compacta' => true,
    ])

    <section class="bg-pg-negro pb-14">
        <div class="mx-auto max-w-6xl px-4 sm:px-8">

            {{-- Filtro por objetivo: enlaces comunes, lo arma el servidor. --}}
            <nav aria-label="Filtrar por objetivo" class="-mx-4 mb-8 flex gap-2 overflow-x-auto px-4 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0 [&::-webkit-scrollbar]:hidden">
                <a href="{{ route('landing.rutinas') }}" @if(! $filtro) aria-current="page" @endif
                    class="shrink-0 rounded-lg px-4 py-2 font-modern text-sm font-semibold transition-colors {{ $filtro ? 'bg-pg-grafito text-pg-tiza/70 hover:text-pg-tiza' : 'bg-pg-rojo text-white' }}">
                    Todas
                </a>
                @foreach($objetivos as $clave => $etiqueta)
                    <a href="{{ route('landing.rutinas', ['objetivo' => $clave]) }}" @if($filtro === $clave) aria-current="page" @endif
                        class="shrink-0 rounded-lg px-4 py-2 font-modern text-sm font-semibold transition-colors {{ $filtro === $clave ? 'bg-pg-rojo text-white' : 'bg-pg-grafito text-pg-tiza/70 hover:text-pg-tiza' }}">
                        {{ $etiqueta }}
                    </a>
                @endforeach
            </nav>

            @forelse($grupos as $clave => $grupo)
                <div id="{{ $clave }}" class="mb-10">
                    <h2 class="font-display text-2xl uppercase text-pg-tiza sm:text-3xl">{{ $grupo['nombre'] }}</h2>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($grupo['rutinas'] as $r)
                            <li>
                                <a href="{{ $r['url'] }}" class="flex h-full items-center justify-between gap-4 rounded-xl bg-pg-grafito/40 px-4 py-4 font-modern text-pg-tiza/85 transition-colors hover:bg-pg-grafito hover:text-white sm:px-5 sm:py-5">
                                    <span>
                                        <span class="block font-semibold sm:text-lg">{{ $r['nombre'] }}</span>
                                        <span class="mt-0.5 block text-sm text-pg-tiza/50">{{ $r['nivel'] }} · {{ $r['dias'] }} días por semana</span>
                                    </span>
                                    <x-icono nombre="arrow-right" class="text-sm text-pg-tiza/40" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="font-modern text-sm text-pg-tiza/70">Todavía no hay rutinas cargadas. Pregunta en el mesón y te orientamos.</p>
            @endforelse

            <p class="flex flex-wrap gap-x-6 gap-y-2 font-modern text-sm sm:text-base">
                <a href="{{ route('landing.rutina') }}" class="text-pg-tiza underline underline-offset-4 hover:text-white">Qué entrenar hoy</a>
                <a href="{{ route('landing.ejercicios') }}" class="text-pg-tiza underline underline-offset-4 hover:text-white">Mira los ejercicios del gimnasio</a>
            </p>
        </div>
    </section>
@endsection
