@extends('layouts.documento')

{{--
    La cotización de un taller, en papel.

    ES EL MISMO DOCUMENTO DE SIEMPRE, a propósito: el membrete arriba, la banda
    negra con «COTIZACIÓN», los datos del gimnasio a la izquierda y el número y
    las fechas a la derecha, la banda de «DATOS DEL CLIENTE» y la tabla de
    DESCRIPCIÓN · UNIDADES/mes · VALOR(hora) · TOTAL. El colegio lleva años
    recibiendo esta hoja y la reconoce; cambiarle la cara no aportaba nada.
    Lo que cambia es que la cuenta ya no se hace a mano.

    Sale del navegador —«Imprimir o guardar como PDF»— y no de un generador de
    PDF: es una hoja con seis datos, y una librería más habría que mantenerla.

    ABAJO VA EL DETALLE DE LAS HORAS, que el Word no llevaba y hacía falta: es
    lo que responde «¿por qué este mes son 18 y no 20?» sin abrir el calendario,
    y enseña tachadas las clases suspendidas.
--}}

@section('titulo', 'Cotización N° ' . $cotizacion->numero)
@section('etiqueta', 'Cotización')

@php
    $pesos = fn ($monto) => '$' . number_format((int) $monto, 0, ',', '.');
    $horas = fn ($cantidad) => rtrim(rtrim(number_format((float) $cantidad, 2, ',', '.'), '0'), ',');
    $incluidas = $cotizacion->lineasIncluidas();
    $quitadas = $cotizacion->lineasQuitadas();
@endphp

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 px-1 print:hidden">
        <a href="{{ route('panel.talleres.cotizaciones.show', $cotizacion->uuid) }}"
           class="text-sm text-pg-tiza/60 transition-colors hover:text-pg-tiza">
            ← Volver a la cotización
        </a>
        <button type="button" id="imprimir"
                class="rounded-xl bg-pg-rojo px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-pg-rojo-oscuro">
            Imprimir o guardar como PDF
        </button>
    </div>

    <article class="rounded-2xl bg-white px-5 py-8 text-neutral-900 shadow-2xl sm:px-10 print:rounded-none print:px-0 print:py-0 print:shadow-none">
        <div class="ct">
<style>
.ct{font-family:Calibri,Carlito,'Segoe UI',Arial,sans-serif;color:#000;font-size:14px;line-height:1.45}
.ct-membrete{display:block;width:100%;max-width:640px;margin:0 auto 34px}
.ct-banda{background:#000;color:#fff;font-weight:700;font-size:15px;text-align:center;padding:5px 8px;letter-spacing:.01em;margin:0 0 12px}
.ct-datos{display:flex;gap:24px;margin-bottom:14px}
.ct-datos>div{flex:1}
.ct-datos p{margin:0 0 7px}
.ct b{font-weight:700}
.ct-correo{color:#c00000}
.ct-doc td{padding:0 0 7px;vertical-align:top}
.ct-doc td:first-child{padding-right:14px;white-space:nowrap}
.ct-cliente p{margin:0 0 3px}
.ct-items{width:100%;border-collapse:collapse;margin:26px 0 8px;font-size:13.5px}
.ct-items th,.ct-items td{border:1px solid #000;padding:4px 7px;text-align:left}
.ct-items th{font-weight:700}
.ct-items .num{text-align:left;white-space:nowrap}
.ct-items .ancho{width:52%}
.ct-pie{font-size:12.5px;color:#404040;margin:0}
.ct-anexo{margin-top:30px;break-inside:avoid}
.ct-anexo table{width:100%;border-collapse:collapse;font-size:13px}
.ct-anexo th,.ct-anexo td{border:1px solid #000;padding:3px 7px;text-align:left}
.ct-anexo th{font-weight:700}
.ct-anexo .num{text-align:right;white-space:nowrap}
.ct-fuera td{color:#707070;text-decoration:line-through}
.ct-nota{margin:14px 0 0;font-size:13.5px;white-space:pre-line}
@page{size:A4;margin:14mm 16mm}
</style>
            {{-- El membrete del Word: la misma foto con el logo encima. --}}
            <img src="{{ asset('images/progym-membrete.jpg') }}" alt="{{ $emisor['nombre'] }}" class="ct-membrete">

            <p class="ct-banda">COTIZACIÓN</p>

            <div class="ct-datos">
                <div>
                    <p><b>Nombre:</b> {{ $emisor['nombre'] }}</p>
                    @if ($emisor['direccion'])<p><b>Dirección:</b> {{ $emisor['direccion'] }}</p>@endif
                    @if ($emisor['rut'])<p><b>Rut:</b> {{ $emisor['rut'] }}</p>@endif
                    @if ($emisor['telefono'])<p><b>Teléfono:</b> {{ $emisor['telefono'] }}</p>@endif
                    @if ($emisor['email'])<p><b>Correo:</b> <span class="ct-correo">{{ $emisor['email'] }}</span></p>@endif
                </div>

                <div>
                    <table class="ct-doc">
                        <tr>
                            <td>NÚMERO:</td>
                            <td>{{ $cotizacion->numero }}</td>
                        </tr>
                        <tr>
                            <td>FECHA:</td>
                            <td>{{ $cotizacion->fecha->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td>VÁLIDO HASTA:</td>
                            <td>{{ $cotizacion->valido_hasta->format('d-m-Y') }}</td>
                        </tr>
                        @if ($cotizacion->periodo)
                            <tr>
                                <td>MES:</td>
                                <td class="capitalize">
                                    {{ \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $cotizacion->periodo . '-01')->translatedFormat('F \d\e Y') }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <p class="ct-banda">DATOS DEL CLIENTE</p>

            <div class="ct-cliente">
                <p><b>Nombre:</b> {{ $institucion?->nombre ?? 'Sin institución' }}</p>
                @if ($institucion?->direccion)
                    <p><b>Dirección:</b> {{ $institucion->direccion }}{{ $institucion->comuna ? ';' . $institucion->comuna : '' }}</p>
                @endif
                @if ($institucion?->rut)<p><b>Rut:</b> {{ $institucion->rut }}</p>@endif
                @if ($institucion?->giro)<p><b>Giro:</b> {{ $institucion->giro }}</p>@endif
                @if ($institucion?->contacto_email)
                    <p><b>E-mail cto:</b> <span class="ct-correo">{{ $institucion->contacto_email }}</span></p>
                @endif
            </div>

            <table class="ct-items">
                <thead>
                    <tr>
                        <th class="ancho">DESCRIPCIÓN</th>
                        <th class="num">UNIDADES/mes</th>
                        <th class="num">VALOR(hora)</th>
                        <th class="num">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $cotizacion->descripcion }}</td>
                        <td class="num">{{ $horas($cotizacion->horas) }}</td>
                        <td class="num">{{ $pesos($cotizacion->precio_hora) }}</td>
                        <td class="num">{{ $pesos($cotizacion->total) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- El precio se acuerda con IVA dentro —«la hora sale treinta
                 mil»—, así que se dice en el papel y no se desglosa: el neto y
                 el IVA hacen falta al facturar, y para eso están en el panel. --}}
            <p class="ct-pie">Valores con IVA incluido.</p>

            @if ($cotizacion->notas)
                <p class="ct-nota">{{ $cotizacion->notas }}</p>
            @endif

            @if ($conDetalle && count($incluidas) > 0)
                <div class="ct-anexo">
                    <p class="ct-banda">DETALLE DE LAS HORAS</p>
                    <table>
                        <thead>
                            <tr>
                                <th>DÍA</th>
                                <th>HORARIO</th>
                                <th class="num">HORAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($incluidas as $linea)
                                <tr>
                                    <td>
                                        @if (! empty($linea['fecha']))
                                            {{ \Illuminate\Support\Carbon::parse($linea['fecha'])->translatedFormat('D d/m/Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $linea['detalle'] ?? '—' }}</td>
                                    <td class="num">{{ $horas($linea['horas']) }}</td>
                                </tr>
                            @endforeach

                            @foreach ($quitadas as $linea)
                                {{-- Las suspendidas también se enseñan: es lo que
                                     explica por qué el mes sale más barato. --}}
                                <tr class="ct-fuera">
                                    <td>
                                        @if (! empty($linea['fecha']))
                                            {{ \Illuminate\Support\Carbon::parse($linea['fecha'])->translatedFormat('D d/m/Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $linea['detalle'] ?? '—' }} (no se considera)</td>
                                    <td class="num">—</td>
                                </tr>
                            @endforeach

                            <tr>
                                <td colspan="2"><b>TOTAL DE HORAS</b></td>
                                <td class="num"><b>{{ $horas($cotizacion->horas) }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
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
