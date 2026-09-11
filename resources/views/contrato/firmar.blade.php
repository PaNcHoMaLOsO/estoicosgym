@extends('layouts.documento')

@section('titulo', 'Firma tu contrato')
@section('etiqueta', 'Contrato para firmar')

@section('contenido')
    <div class="mb-6 px-1">
        <h1 class="font-display text-3xl uppercase leading-tight sm:text-4xl">
            Hola, {{ \Illuminate\Support\Str::before($firmante['nombre'], ' ') ?: $firmante['nombre'] }}
        </h1>
        <p class="mt-3 leading-relaxed text-pg-tiza/75">
            @if ($firmante['tipo'] === 'apoderado')
                Te pedimos firmar como apoderado de <strong class="text-pg-tiza">{{ $socio }}</strong>.
            @endif
            Lee el contrato con calma, marca las casillas y firma al final con el dedo o con el mouse.
            El enlace sirve hasta el <strong class="text-pg-tiza">{{ $contrato->vence_en->format('d/m/Y') }}</strong>.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-pg-rojo/40 bg-pg-rojo/10 px-5 py-4 text-sm" role="alert">
            <p class="font-semibold text-pg-tiza">Falta algo antes de firmar:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-pg-tiza/80">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- El contrato, con sus datos y los de su plan: se lee entero antes de
         llegar a la firma, no escondido detrás de un enlace. --}}
    <article class="documento rounded-2xl bg-white px-5 py-8 text-neutral-900 shadow-2xl sm:px-10">
        {!! $contrato_html !!}
    </article>

    <details class="mt-4 overflow-hidden rounded-2xl border border-pg-tiza/10 bg-pg-grafito">
        <summary class="cursor-pointer px-5 py-4 font-semibold">
            Términos y condiciones
            <span class="font-normal text-pg-acero">· versión {{ $textos['terminos']->version }}</span>
        </summary>
        <div class="documento en-oscuro border-t border-pg-tiza/10 px-5 py-5 text-sm text-pg-tiza/80">
            {!! $terminos_html !!}
        </div>
    </details>

    <details class="mt-3 overflow-hidden rounded-2xl border border-pg-tiza/10 bg-pg-grafito">
        <summary class="cursor-pointer px-5 py-4 font-semibold">
            Política de privacidad
            <span class="font-normal text-pg-acero">· versión {{ $textos['privacidad']->version }}</span>
        </summary>
        <div class="documento en-oscuro border-t border-pg-tiza/10 px-5 py-5 text-sm text-pg-tiza/80">
            {!! $privacidad_html !!}
        </div>
    </details>

    <form method="POST" action="{{ route('contrato.firmar', $token) }}" id="formulario-firma"
          class="mt-6 space-y-7 rounded-2xl border border-pg-tiza/10 bg-pg-grafito p-5 sm:p-7">
        @csrf
        {{-- Lo que se leyó: si el gimnasio guarda otra versión mientras tanto,
             la firma se rechaza y se muestra la nueva. --}}
        <input type="hidden" name="version_contrato" value="{{ $textos['contrato']->version }}">
        <input type="hidden" name="version_terminos" value="{{ $textos['terminos']->version }}">
        <input type="hidden" name="version_privacidad" value="{{ $textos['privacidad']->version }}">
        <input type="hidden" name="firma" id="campo-firma">

        <fieldset class="space-y-3">
            <legend class="mb-3 font-display text-xl uppercase">Lo que aceptas</legend>
            @foreach ([
                'acepto_contrato' => 'Leí el contrato y lo acepto.',
                'acepto_terminos' => 'Acepto los términos y condiciones.',
                'leido_privacidad' => 'Leí la política de privacidad y sé cómo pedir que borren mis datos.',
            ] as $campo => $texto)
                <label class="flex items-start gap-3 text-sm leading-relaxed">
                    <input type="checkbox" name="{{ $campo }}" value="1" @checked(old($campo)) required
                           class="mt-0.5 size-5 shrink-0 accent-pg-rojo">
                    <span>{{ $texto }}</span>
                </label>
            @endforeach
        </fieldset>

        <fieldset class="space-y-3">
            <legend class="mb-1 font-display text-xl uppercase">Tu imagen</legend>
            <p class="text-sm text-pg-tiza/55">
                Son voluntarias y no cambian nada de tu membresía. Puedes cambiar de opinión cuando quieras, en el mesón.
            </p>
            <label class="flex items-start gap-3 text-sm leading-relaxed">
                <input type="checkbox" name="consentimiento_imagen" value="1" @checked(old('consentimiento_imagen'))
                       class="mt-0.5 size-5 shrink-0 accent-pg-rojo">
                <span>
                    Autorizo que el gimnasio guarde una foto mía en mi ficha, para reconocerme en el mesón.
                    <span class="text-pg-tiza/50">Solo la ve el personal.</span>
                </span>
            </label>
            <label class="flex items-start gap-3 text-sm leading-relaxed">
                <input type="checkbox" name="consentimiento_difusion" value="1" @checked(old('consentimiento_difusion'))
                       class="mt-0.5 size-5 shrink-0 accent-pg-rojo">
                <span>Autorizo que usen fotos o videos donde yo aparezca en las redes sociales del gimnasio.</span>
            </label>
        </fieldset>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-3 font-display text-xl uppercase sm:col-span-2">Quién firma</legend>
            <label class="block text-sm">
                <span class="mb-1.5 block text-pg-tiza/70">Nombre completo</span>
                <input type="text" name="nombre" value="{{ old('nombre', $firmante['nombre']) }}" required maxlength="150"
                       autocomplete="name"
                       class="w-full rounded-xl border border-pg-tiza/15 bg-pg-negro px-3.5 py-3 text-pg-tiza focus:border-pg-rojo focus:outline-none">
            </label>
            <label class="block text-sm">
                <span class="mb-1.5 block text-pg-tiza/70">RUT o pasaporte</span>
                <input type="text" name="rut" value="{{ old('rut') }}" required maxlength="20" placeholder="12.345.678-9"
                       autocomplete="off"
                       class="w-full rounded-xl border border-pg-tiza/15 bg-pg-negro px-3.5 py-3 text-pg-tiza placeholder:text-pg-tiza/25 focus:border-pg-rojo focus:outline-none">
            </label>
        </fieldset>

        <div>
            <div class="mb-3 flex items-end justify-between gap-3">
                <p class="font-display text-xl uppercase">Tu firma</p>
                <button type="button" id="borrar-firma"
                        class="text-sm text-pg-tiza/60 underline-offset-4 hover:text-pg-tiza hover:underline">
                    Borrar y volver a firmar
                </button>
            </div>
            <canvas id="lienzo-firma" class="block h-44 w-full touch-none rounded-xl bg-white"
                    aria-label="Recuadro para firmar con el dedo o con el mouse"></canvas>
            <p class="mt-2 text-xs text-pg-tiza/50">Firma dentro del recuadro blanco, con el dedo o con el mouse.</p>
            <p id="aviso-firma" class="mt-2 text-sm font-medium text-pg-rojo-claro" hidden>
                Falta tu firma: dibújala en el recuadro.
            </p>
        </div>

        <div>
            <button type="submit" id="boton-firmar"
                    class="w-full rounded-xl bg-pg-rojo px-6 py-4 font-display text-lg uppercase tracking-wide text-white transition-colors hover:bg-pg-rojo-oscuro disabled:opacity-60">
                Firmar contrato
            </button>
            <p class="mt-3 text-center text-xs leading-relaxed text-pg-tiza/45">
                Al firmar quedan guardados el documento, tu firma, la fecha y la conexión desde la que firmas.
                Te llega una copia a {{ $contrato->email_destino }}.
            </p>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    /*
     * El recuadro de la firma: se dibuja con el dedo o con el mouse, y al
     * soltar se guarda como imagen en el campo oculto que viaja con el
     * formulario. Sin librerías: son cuarenta líneas.
     */
    (function () {
        const lienzo = document.getElementById('lienzo-firma');
        const campo = document.getElementById('campo-firma');
        const aviso = document.getElementById('aviso-firma');
        const boton = document.getElementById('boton-firmar');
        const pincel = lienzo.getContext('2d');
        let dibujando = false;
        let ultimo = null;
        let ancho = 0;

        function preparar() {
            const escala = window.devicePixelRatio || 1;
            lienzo.width = lienzo.clientWidth * escala;
            lienzo.height = lienzo.clientHeight * escala;
            pincel.setTransform(escala, 0, 0, escala, 0, 0);
            pincel.lineWidth = 2.4;
            pincel.lineCap = 'round';
            pincel.lineJoin = 'round';
            pincel.strokeStyle = '#111114';
            // Al cambiar el tamaño se pierde lo dibujado: se pide firmar otra vez.
            campo.value = '';
        }

        function punto(evento) {
            const caja = lienzo.getBoundingClientRect();
            return { x: evento.clientX - caja.left, y: evento.clientY - caja.top };
        }

        lienzo.addEventListener('pointerdown', function (evento) {
            evento.preventDefault();
            lienzo.setPointerCapture(evento.pointerId);
            dibujando = true;
            ultimo = punto(evento);
            pincel.beginPath();
            pincel.moveTo(ultimo.x, ultimo.y);
            pincel.lineTo(ultimo.x + 0.1, ultimo.y + 0.1);
            pincel.stroke();
        });

        lienzo.addEventListener('pointermove', function (evento) {
            if (!dibujando) return;
            evento.preventDefault();
            const ahora = punto(evento);
            pincel.beginPath();
            pincel.moveTo(ultimo.x, ultimo.y);
            pincel.lineTo(ahora.x, ahora.y);
            pincel.stroke();
            ultimo = ahora;
        });

        function soltar() {
            if (!dibujando) return;
            dibujando = false;
            campo.value = lienzo.toDataURL('image/png');
            aviso.hidden = true;
        }

        lienzo.addEventListener('pointerup', soltar);
        lienzo.addEventListener('pointercancel', soltar);

        document.getElementById('borrar-firma').addEventListener('click', function () {
            pincel.clearRect(0, 0, lienzo.width, lienzo.height);
            campo.value = '';
        });

        document.getElementById('formulario-firma').addEventListener('submit', function (evento) {
            if (!campo.value) {
                evento.preventDefault();
                aviso.hidden = false;
                lienzo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            // Un doble toque no firma dos veces.
            boton.disabled = true;
            boton.textContent = 'Firmando…';
        });

        // En el celular, la barra de direcciones que aparece y desaparece
        // cambia el alto de la ventana: solo un cambio de ANCHO rehace el
        // recuadro, o se borraría la firma a medio dibujar.
        function medir() {
            if (lienzo.clientWidth !== ancho) {
                ancho = lienzo.clientWidth;
                preparar();
            }
        }

        window.addEventListener('resize', medir);
        medir();
    })();
</script>
@endpush
