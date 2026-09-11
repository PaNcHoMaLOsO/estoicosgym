@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Tus datos',
        'titulo' => 'Privacidad y cookies',
        'bajada' => 'Qué datos guardamos, para qué, y cómo pedir que se corrijan o se borren.',
    ])

    <section class="pb-24 bg-pg-negro">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 font-modern text-pg-tiza/75 leading-relaxed">
            <div>
                <h2 class="font-display text-2xl uppercase text-pg-tiza mb-3">Quiénes somos</h2>
                <p>
                    {{ $gimnasio['nombre'] }}{{ $gimnasio['direccion'] ? ', ' . $gimnasio['direccion'] : '' }}{{ $web['ciudad'] ? ', ' . $web['ciudad'] : '' }}.
                    Para cualquier consulta sobre tus datos:
                    @if($gimnasio['email'])
                        <a href="mailto:{{ $gimnasio['email'] }}" class="text-pg-rojo-claro hover:underline">{{ $gimnasio['email'] }}</a>.
                    @else
                        en el mesón del gimnasio.
                    @endif
                </p>
            </div>

            <div>
                <h2 class="font-display text-2xl uppercase text-pg-tiza mb-3">Qué datos guardamos y para qué</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong class="text-pg-tiza">Si nos escribes por el formulario:</strong> tu nombre, correo, teléfono y mensaje, para responderte.</li>
                    <li><strong class="text-pg-tiza">Si eres socio:</strong> los datos de tu ficha, tu membresía y tus pagos, para administrar tu inscripción.</li>
                    <li><strong class="text-pg-tiza">Si consultas tu membresía aquí:</strong> tu RUT o tu celular se usan solo para buscarte, y se registra la conexión y la hora de la consulta para frenar abusos.</li>
                </ul>
                <p class="mt-3">No vendemos ni compartimos tus datos con nadie para publicidad.</p>
            </div>

            <div>
                <h2 class="font-display text-2xl uppercase text-pg-tiza mb-3">Cookies y analítica</h2>
                @if(!empty($web['google_analytics']))
                    <p>Si aceptas en el aviso de cookies, usamos Google Analytics para saber qué partes de la página sirven. Si no aceptas, no se guardan cookies de analítica. Puedes cambiar de opinión borrando los datos de este sitio en tu navegador.</p>
                @else
                    <p>Esta página no usa cookies de analítica ni de publicidad.</p>
                @endif
            </div>

            <div>
                <h2 class="font-display text-2xl uppercase text-pg-tiza mb-3">Tus derechos</h2>
                <p>Puedes pedir en cualquier momento saber qué datos tuyos tenemos, corregirlos, o que se borren cuando ya no haya una obligación de guardarlos. Escríbenos o pídelo en el mesón.</p>
            </div>
        </div>
    </section>
@endsection
