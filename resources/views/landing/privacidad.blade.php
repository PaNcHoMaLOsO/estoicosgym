@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Tus datos',
        'titulo' => 'Privacidad y cookies',
        'bajada' => 'Versión ' . $legal['version'] . ($legal['fecha'] ? ' · actualizada el ' . $legal['fecha'] : ''),
    ])

    <section class="pb-16 bg-pg-negro">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 font-modern text-pg-tiza/75">
            {{-- Lo escribe el gimnasio en Configuración → Política de privacidad. --}}
            <div class="documento en-oscuro">
                {!! $legal['html'] !!}
            </div>

            {{-- Esto no se escribe a mano: depende de si hay analítica configurada. --}}
            <div class="mt-10 leading-relaxed">
                <h2 class="font-display text-xl uppercase text-pg-tiza mb-3">Cookies y analítica</h2>
                @if(!empty($web['google_analytics']))
                    <p>Si aceptas en el aviso de cookies, usamos Google Analytics para saber qué partes de la página sirven. Si no aceptas, no se guardan cookies de analítica. Puedes cambiar de opinión borrando los datos de este sitio en tu navegador.</p>
                @else
                    <p>Esta página no usa cookies de analítica ni de publicidad.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
