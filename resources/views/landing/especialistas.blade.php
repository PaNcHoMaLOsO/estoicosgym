@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    Los especialistas, lado a lado y de borde a borde.

    PRIMERO FUERON TRES TARJETAS con un círculo arriba —la maqueta de siempre,
    se notaba hecha en serie— y después una lista hacia abajo que dejaba media
    pantalla vacía a la derecha. El dueño pidió horizontal y sin espacio
    perdido: cada especialista es un panel alto que ocupa su parte del ancho,
    como los embajadores de la portada, con la foto de fondo y lo que hace y
    cómo escribirle abajo. Sin foto, su inicial gigante en hueco llena el
    panel. En el celular se deslizan de lado.
--}}

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => $hayRecomendados ? 'Recomendados por ' . $gimnasio['nombre'] : 'Con quién entrenas',
        // «Profesionales del deporte»: el lema de la marca, y lo que hay aquí.
        'titulo' => 'Profesionales del deporte',
        'bajada' => $hayRecomendados
            ? 'Profesionales' . ($web['ciudad'] ? ' de ' . $web['ciudad'] : '') . ' que recomendamos. Mira qué días atienden y escríbeles directo.'
            : 'Profesionales que trabajan con ' . $gimnasio['nombre'] . '. Escríbeles directo.',
    ])

    <section id="especialistas" class="pb-10 lg:pb-20 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            @if(count($especialistas))
                {{-- Por especialidad: cada una tiene su página. --}}
                @if(count($especialidades) > 1)
                    <p class="mb-5 -mt-4 text-center font-modern text-sm text-pg-tiza/55">
                        @foreach($especialidades as $i => $esp)
                            @if($i > 0)<span class="mx-1.5 text-pg-tiza/25" aria-hidden="true">·</span>@endif<a href="{{ $esp['url'] }}" class="underline decoration-pg-tiza/25 underline-offset-4 transition-colors hover:text-pg-rojo-claro hover:decoration-pg-rojo">{{ $esp['nombre'] }}</a>
                        @endforeach
                    </p>
                @endif
                {{-- SEPARADOS POR PROFESIÓN cuando hay más de una: nutricionistas
                     con nutricionistas, kinesiólogos con kinesiólogos. --}}
                @if(count($secciones) > 1)
                    <div class="space-y-10 lg:space-y-14">
                        @foreach($secciones as $seccion)
                            <div>
                                <div class="mb-3 flex items-baseline justify-between gap-4 border-b border-pg-tiza/10 pb-2 lg:mb-4">
                                    <h2 class="font-display text-2xl uppercase text-pg-tiza lg:text-3xl">{{ $seccion['nombre'] }}</h2>
                                    @if($seccion['url'])
                                        <a href="{{ $seccion['url'] }}" class="shrink-0 font-modern text-xs text-pg-tiza/55 transition-colors hover:text-pg-rojo-claro sm:text-sm">
                                            {{ count($seccion['especialistas']) }} {{ count($seccion['especialistas']) === 1 ? 'profesional' : 'profesionales' }} <x-icono nombre="arrow-right" class="text-[10px]" />
                                        </a>
                                    @endif
                                </div>
                                @include('landing.partes.grilla-especialistas', ['especialistas' => $seccion['especialistas'], 'nivel' => 'h3', 'fijas' => true])
                            </div>
                        @endforeach
                    </div>
                @else
                    @include('landing.partes.grilla-especialistas', ['especialistas' => $especialistas])
                @endif
            @else
                <p class="text-center text-pg-tiza/60 font-modern py-12">Pronto vas a encontrar aquí a los profesionales que trabajan con nosotros.</p>
            @endif

            @if($whatsappProfesionales)
                <p class="mt-10 text-center font-modern text-sm text-pg-tiza/55 lg:mt-14">
                    ¿Eres profesional de la salud o el deporte{{ $web['ciudad'] ? ' en ' . $web['ciudad'] : '' }}?
                    <a href="{{ $whatsappProfesionales }}" target="_blank" rel="noopener" data-evento="profesional_quiere_aparecer"
                       class="ml-1 whitespace-nowrap text-pg-tiza underline decoration-pg-rojo underline-offset-4 transition-colors hover:text-pg-rojo-claro">Escríbenos</a>
                </p>
            @endif
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
