@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    Una especialidad: «Kinesiólogo en Los Ángeles». Los mismos paneles de
    Especialistas, solo con quienes hacen eso, y abajo las otras.
--}}
@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Especialistas',
        'titulo' => $tituloEspecialidad,
        'bajada' => 'Trabajan con ' . $gimnasio['nombre'] . '. Escríbeles directo.',
    ])

    <section class="pb-10 lg:pb-20 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            @include('landing.partes.grilla-especialistas', ['especialistas' => $grupo['especialistas']])

            <p class="mt-8 text-center font-modern text-sm text-pg-tiza/55">
                <a href="{{ route('landing.especialistas') }}" class="text-pg-tiza transition-colors hover:text-pg-rojo-claro">Todos los especialistas</a>
                @foreach($otrasEspecialidades as $esp)
                    <span class="mx-1.5 text-pg-tiza/25" aria-hidden="true">·</span><a href="{{ $esp['url'] }}" class="transition-colors hover:text-pg-rojo-claro">{{ $esp['nombre'] }}</a>
                @endforeach
            </p>
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
