@extends('layouts.documento')

{{--
    La cotización de un taller, en papel.

    IGUAL QUE EL WORD QUE SE MANDABA ANTES, porque el colegio lleva años
    recibiendo esa hoja y reconoce sus casillas: quién cotiza, a quién, el
    número de la serie, la descripción y el total. Lo que cambia es que la
    cuenta ya no se hace a mano.

    Sale del navegador —«Imprimir o guardar como PDF»— y no de un generador de
    PDF: es una hoja con seis datos, y una librería más habría que mantenerla.

    ABAJO VA EL DETALLE DE LAS HORAS, que el Word no llevaba y hacía falta: es
    lo que responde «¿por qué este mes son 18 y no 20?» sin tener que abrir el
    calendario.
--}}

@section('titulo', 'Cotización N° ' . $cotizacion->numero)
@section('etiqueta', 'Cotización')

@php
    $pesos = fn ($monto) => '$ ' . number_format((int) $monto, 0, ',', '.');
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
.ct{font-family:Arial,Helvetica,sans-serif;color:#16161a;font-size:13px;line-height:1.5}
.ct h1{font-size:22px;font-weight:700;letter-spacing:.08em;text-align:center;margin:0 0 18px}
.ct-cajas{display:flex;gap:14px;margin-bottom:14px}
.ct-caja{flex:1;border:1px solid #16161a;padding:9px 11px}
.ct-caja p{margin:0 0 3px}
.ct-rotulo{font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:#6a6a72;margin:0 0 6px!important}
.ct-cliente{border:1px solid #16161a;padding:9px 11px;margin-bottom:16px}
.ct-cliente p{margin:0 0 3px}
.ct table{width:100%;border-collapse:collapse;margin-bottom:14px}
.ct th,.ct td{border:1px solid #16161a;padding:6px 8px;text-align:left;vertical-align:top}
.ct th{font-size:11px;letter-spacing:.04em;text-transform:uppercase;background:#f1f1f3}
.ct .num{text-align:right;white-space:nowrap}
.ct-totales{width:auto;min-width:250px;margin-left:auto}
.ct-totales td{border:none;padding:3px 0}
.ct-totales tr.total td{border-top:1.5px solid #16161a;font-weight:700;font-size:15px;padding-top:6px}
.ct-anexo{margin-top:26px;break-inside:avoid}
.ct-anexo h2{font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#6a6a72;margin:0 0 7px}
.ct-anexo table{font-size:12px}
.ct-anexo td{padding:4px 8px}
.ct-fuera td{color:#6a6a72;text-decoration:line-through}
.ct-nota{margin-top:18px;font-size:12px;white-space:pre-line}
.ct-pie{margin-top:26px;font-size:11px;color:#6a6a72;text-align:center}
@page{size:A4;margin:16mm 15mm}
</style>
            <h1>COTIZACIÓN</h1>

            <div class="ct-cajas">
                <div class="ct-caja">
                    <p class="ct-rotulo">Quien cotiza</p>
                    <p><strong>{{ $emisor['nombre'] }}</strong></p>
                    @if ($emisor['rut'])<p>RUT: {{ $emisor['rut'] }}</p>@endif
                    @if ($emisor['direccion'])<p>{{ $emisor['direccion'] }}</p>@endif
                    @if ($emisor['telefono'])<p>Teléfono: {{ $emisor['telefono'] }}</p>@endif
                    @if ($emisor['email'])<p>{{ $emisor['email'] }}</p>@endif
                </div>

                <div class="ct-caja">
                    <p class="ct-rotulo">Documento</p>
                    <p><strong>N° {{ $cotizacion->numero }}</strong></p>
                    <p>Fecha: {{ $cotizacion->fecha->format('d-m-Y') }}</p>
                    <p>Válido hasta: {{ $cotizacion->valido_hasta->format('d-m-Y') }}</p>
                    @if ($cotizacion->periodo)
                        <p class="capitalize">
                            Mes cotizado:
                            {{ \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $cotizacion->periodo . '-01')->translatedFormat('F \d\e Y') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="ct-cliente">
                <p class="ct-rotulo">Datos del cliente</p>
                <p><strong>{{ $institucion?->nombre ?? 'Sin institución' }}</strong></p>
                @if ($institucion?->rut)<p>RUT: {{ $institucion->rut }}</p>@endif
                @if ($institucion?->giro)<p>Giro: {{ $institucion->giro }}</p>@endif
                @if ($institucion?->direccion)
                    <p>{{ $institucion->direccion }}{{ $institucion->comuna ? ', ' . $institucion->comuna : '' }}</p>
                @endif
                @if ($institucion?->contacto_email)
                    <p>Contacto: {{ $institucion->contacto_nombre ? $institucion->contacto_nombre . ' · ' : '' }}{{ $institucion->contacto_email }}</p>
                @endif
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th class="num">Horas del mes</th>
                        <th class="num">Valor hora (IVA incl.)</th>
                        <th class="num">Total</th>
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

            {{-- El neto y el IVA salen del total y no al revés: lo acordado es
                 «la hora sale treinta mil», y así el papel cuadra al peso con
                 la factura que se emitirá después. --}}
            <table class="ct-totales">
                <tr>
                    <td>Neto</td>
                    <td class="num">{{ $pesos($cotizacion->neto) }}</td>
                </tr>
                <tr>
                    <td>IVA 19%</td>
                    <td class="num">{{ $pesos($cotizacion->iva) }}</td>
                </tr>
                <tr class="total">
                    <td>Total</td>
                    <td class="num">{{ $pesos($cotizacion->total) }}</td>
                </tr>
            </table>

            @if ($cotizacion->notas)
                <p class="ct-nota">{{ $cotizacion->notas }}</p>
            @endif

            @if (count($incluidas) > 0)
                <div class="ct-anexo">
                    <h2>Detalle de las horas</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Horario</th>
                                <th class="num">Horas</th>
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
                                <td colspan="2"><strong>Total de horas</strong></td>
                                <td class="num"><strong>{{ $horas($cotizacion->horas) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <p class="ct-pie">
                Las horas efectivamente realizadas se confirman al cierre del mes y son las que se facturan.
            </p>
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
