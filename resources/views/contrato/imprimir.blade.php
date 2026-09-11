@extends('layouts.documento')

@section('titulo', 'Contrato de ' . $socio)
@section('etiqueta', 'Contrato para imprimir')

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 px-1 print:hidden">
        <a href="{{ route('panel.clientes.show', $cliente->uuid) }}"
           class="text-sm text-pg-tiza/60 transition-colors hover:text-pg-tiza">
            ← Volver a la ficha
        </a>
        <button type="button" id="imprimir"
                class="rounded-xl bg-pg-rojo px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-pg-rojo-oscuro">
            Imprimir o guardar como PDF
        </button>
    </div>

    {{-- Lo que firmó por correo es ESE documento, con su huella; esto es el
         contrato vigente rellenado hoy. Se dice para no confundirlos. --}}
    @if ($firmado)
        <p class="mb-4 rounded-2xl border border-pg-tiza/10 bg-pg-grafito px-5 py-3 text-sm leading-relaxed text-pg-tiza/75 print:hidden">
            Ya firmó por correo el {{ $firmado->firmado_en->format('d/m/Y') }}:
            <a href="{{ route('panel.contratos.show', $firmado->uuid) }}" class="text-pg-rojo-claro underline underline-offset-2">ver lo que firmó</a>.
            Esto de abajo es el contrato vigente, con sus datos de hoy.
        </p>
    @endif

    <article class="rounded-2xl bg-white px-5 py-8 text-neutral-900 shadow-2xl sm:px-10 print:rounded-none print:px-0 print:py-0 print:shadow-none">
        {{-- Estilos propios, como el contrato firmado: la hoja tiene que salir
             igual en pantalla y en papel, sin depender de la hoja de la web. --}}
        <div class="cp">
<style>
.cp{font-family:Georgia,'Times New Roman',serif;color:#16161a;font-size:14px;line-height:1.6}
.cp h1{font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:700;line-height:1.25;text-transform:uppercase;letter-spacing:.03em;margin:0 0 14px}
.cp h2{font-family:Arial,Helvetica,sans-serif;font-size:14.5px;font-weight:700;margin:20px 0 5px}
.cp h3{font-size:14px;font-weight:700;margin:14px 0 4px}
.cp p{margin:0 0 9px}
.cp ul,.cp ol{margin:0 0 10px;padding-left:22px}
.cp ul{list-style:disc}
.cp ol{list-style:decimal}
.cp li{margin:2px 0}
.cp strong,.cp b{font-weight:700}
.cp a{color:#16161a}
.cp-cabecera{display:flex;justify-content:space-between;align-items:baseline;gap:12px;border-bottom:2px solid #d81f26;padding-bottom:8px;margin-bottom:22px;font-family:Arial,Helvetica,sans-serif}
.cp-cabecera strong{font-size:18px;letter-spacing:.04em}
.cp-cabecera span{font-size:11px;color:#6a6a72;text-align:right}
.cp-seccion{border-top:1px solid #d9d9de;margin-top:26px;padding-top:16px}
.cp-anexo{font-size:13px}
.cp-rotulo{font-family:Arial,Helvetica,sans-serif;font-size:10.5px;letter-spacing:.12em;text-transform:uppercase;color:#6a6a72;margin:0 0 10px}
.cp-firmas{break-inside:avoid;page-break-inside:avoid}
.cp .cp-casilla{display:flex;gap:8px;margin:0 0 7px}
.cp-caja{flex:none;width:13px;height:13px;border:1.5px solid #16161a;margin-top:4px}
.cp-dos{display:flex;gap:40px;margin-top:46px}
.cp-dos>div{flex:1;min-width:0}
.cp-linea{border-bottom:1px solid #16161a;height:44px;margin-bottom:6px}
.cp-dos p,.cp-fecha{font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.45;margin:0}
.cp .cp-fecha{margin-top:26px}
@page{size:A4;margin:16mm 15mm}
</style>
            <div class="cp-cabecera">
                <strong>{{ $gimnasio['gimnasio'] }}</strong>
                <span>Contrato versión {{ $textos['contrato']->version }} · {{ $fecha->format('d/m/Y') }}</span>
            </div>

            {!! $contrato_html !!}

            <div class="cp-seccion cp-anexo">
                <p class="cp-rotulo">Anexo · Términos y condiciones · versión {{ $textos['terminos']->version }}</p>
                {!! $terminos_html !!}
            </div>

            <div class="cp-seccion cp-firmas">
                <p class="cp-rotulo">Aceptación y firma</p>

                @foreach ([
                    'Acepto este contrato y los términos y condiciones de su anexo.',
                    'Leí la política de privacidad del gimnasio, publicada en ' . route('landing.privacidad') . '.',
                    'Autorizo que el gimnasio guarde una foto mía en mi ficha, para reconocerme en el mesón. Solo la ve el personal.',
                    'Autorizo que usen fotos o videos donde yo aparezca en las redes sociales del gimnasio.',
                ] as $casilla)
                    <p class="cp-casilla"><span class="cp-caja"></span><span>{{ $casilla }}</span></p>
                @endforeach

                <div class="cp-dos">
                    <div>
                        <div class="cp-linea"></div>
                        <p>
                            <strong>{{ $firmante['nombre'] ?: '______________________________' }}</strong><br>
                            RUT o pasaporte {{ $firmante['rut'] ?: '________________' }}<br>
                            {{ $firmante['tipo'] === 'apoderado' ? 'Apoderado de ' . $socio : 'Socio' }}
                        </p>
                    </div>
                    <div>
                        <div class="cp-linea"></div>
                        <p><strong>{{ $gimnasio['gimnasio'] }}</strong><br>Por el gimnasio</p>
                    </div>
                </div>

                <p class="cp-fecha">Fecha: ______ / ______ / __________</p>
            </div>
        </div>
    </article>
@endsection

@push('scripts')
<script>
    document.getElementById('imprimir').addEventListener('click', function () {
        window.print();
    });
</script>
@endpush
