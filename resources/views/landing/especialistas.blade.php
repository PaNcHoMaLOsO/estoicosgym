@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Con quién entrenas',
        'titulo' => 'Especialistas',
        'bajada' => 'Profesionales que trabajan con ' . $gimnasio['nombre'] . '. Escríbeles directo.',
    ])

    <section id="especialistas" class="pb-24 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
            @if(count($especialistas))
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    @foreach($especialistas as $index => $e)
                        <article class="animate-on-scroll card-hover group h-full flex flex-col bg-pg-carbon/70 border border-pg-tiza/10 hover:border-pg-rojo/40 rounded-2xl p-8 text-center" style="animation-delay: {{ $index * 0.1 }}s">
                            @if($e['foto'])
                                <img src="{{ $e['foto'] }}" alt="{{ $e['nombre'] }}, {{ $e['especialidad'] }} en {{ $gimnasio['nombre'] }}" loading="lazy" width="128" height="128"
                                     class="w-32 h-32 rounded-full object-cover mx-auto border-2 border-pg-rojo/40 transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-32 h-32 rounded-full mx-auto bg-pg-grafito border-2 border-pg-rojo/40 flex items-center justify-center font-display text-5xl text-pg-tiza" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($e['nombre'], 0, 1)) }}
                                </div>
                            @endif

                            <p class="mt-5 text-pg-rojo-claro font-modern text-xs uppercase tracking-widest">{{ $e['especialidad'] }}</p>
                            <h2 class="font-display text-2xl uppercase text-pg-tiza mt-1">{{ $e['nombre'] }}</h2>

                            @if($e['descripcion'])
                                <p class="text-pg-tiza/60 font-modern text-sm mt-3">{{ $e['descripcion'] }}</p>
                            @endif

                            @if($e['whatsapp'] || $e['instagram'])
                                <div class="mt-auto pt-6 flex flex-wrap justify-center gap-3">
                                    @if($e['whatsapp'])
                                        <a href="{{ $e['whatsapp'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-pg-tiza/20 text-pg-tiza hover:border-[#25D366] hover:text-[#25D366] transition-colors font-modern text-sm">
                                            <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i> WhatsApp
                                        </a>
                                    @endif
                                    @if($e['instagram'])
                                        <a href="{{ $e['instagram'] }}" target="_blank" rel="noopener" data-evento="contacto_especialista" data-detalle="{{ $e['nombre'] }}"
                                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-pg-tiza/20 text-pg-tiza hover:border-pg-rojo hover:text-pg-rojo-claro transition-colors font-modern text-sm">
                                            <i class="fab fa-instagram text-lg" aria-hidden="true"></i> Instagram
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-center text-pg-tiza/60 font-modern py-16">Pronto vas a encontrar aquí a los profesionales que trabajan con nosotros.</p>
            @endif
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
