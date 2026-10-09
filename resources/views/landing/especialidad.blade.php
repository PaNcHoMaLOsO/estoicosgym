@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

{{--
    Una especialidad: «Kinesiólogo en Los Ángeles». Los mismos paneles de
    Especialistas, solo con quienes hacen eso, y abajo las otras.
--}}
@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Profesionales del deporte',
        'titulo' => $tituloEspecialidad,
        'bajada' => (collect($grupo['especialistas'])->contains('recomendado', true) ? 'Recomendados por ' : 'Trabajan con ') . $gimnasio['nombre'] . '. '
            . (count($grupo['especialistas']) > 1 ? 'Escríbeles directo.' : 'Escríbele directo.'),
    ])

    <section class="pb-10 lg:pb-20 bg-pg-negro">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            @include('landing.partes.grilla-especialistas', ['especialistas' => $grupo['especialistas']])

            <p class="mt-8 text-center font-modern text-sm text-pg-tiza/55">
                <a href="{{ route('landing.especialistas') }}" class="text-pg-tiza transition-colors hover:text-pg-rojo-claro">Todos los profesionales</a>
                @foreach($otrasEspecialidades as $esp)
                    <span class="mx-1.5 text-pg-tiza/25" aria-hidden="true">·</span><a href="{{ $esp['url'] }}" class="transition-colors hover:text-pg-rojo-claro">{{ $esp['nombre'] }}</a>
                @endforeach
            </p>
        </div>
    </section>

    @include('landing.partes.llamado')
@endsection
