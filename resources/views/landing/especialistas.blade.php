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
        'antetitulo' => 'Con quién entrenas',
        'titulo' => 'Especialistas',
        'bajada' => 'Profesionales que trabajan con ' . $gimnasio['nombre'] . '. Escríbeles directo.',
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
                @include('landing.partes.grilla-especialistas', ['especialistas' => $especialistas])
            @else
                <p class="text-center text-pg-tiza/60 font-modern py-12">Pronto vas a encontrar aquí a los profesionales que trabajan con nosotros.</p>
            @endif
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
