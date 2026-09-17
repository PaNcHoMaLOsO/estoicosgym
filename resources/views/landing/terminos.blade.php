@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Reglas del gimnasio',
        'titulo' => 'Términos y condiciones',
        'bajada' => 'Versión ' . $legal['version'] . ($legal['fecha'] ? ' · actualizada el ' . $legal['fecha'] : ''),
    ])

    <section class="pb-16 bg-pg-negro">
        {{-- Lo escribe el gimnasio en Configuración → Términos y condiciones. --}}
        <div class="documento en-oscuro max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 font-modern text-pg-tiza/75">
            {!! $legal['html'] !!}
        </div>
    </section>
@endsection
