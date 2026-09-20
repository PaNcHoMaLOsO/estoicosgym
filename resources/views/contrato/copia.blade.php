@extends('layouts.documento')

@section('titulo', 'Contrato de ' . ($contrato->cliente?->nombre_completo ?? 'un socio'))
@section('etiqueta', 'Contrato firmado')

@php
    $fila = fn (string $que, ?string $valor) => ['que' => $que, 'valor' => $valor !== null && $valor !== '' ? $valor : '-'];
    $constancia = [
        $fila('Firmado el', $contrato->firmado_en?->format('d/m/Y \a \l\a\s H:i:s')),
        $fila('Firmó', $contrato->firmante_nombre
            ? $contrato->firmante_nombre . ($contrato->firmante_tipo === 'apoderado' ? ' (apoderado)' : '')
            : null),
        $fila('RUT o pasaporte', $contrato->firmante_rut),
        $fila('Enlace enviado a', $contrato->email_destino),
        $fila('Lo mandó', $contrato->usuario?->name),
        $fila('Conexión', $contrato->ip),
        $fila('Navegador', $contrato->navegador),
        $fila('Versiones', "contrato {$contrato->version_contrato} · términos {$contrato->version_terminos} · privacidad {$contrato->version_privacidad}"),
        $fila('Foto en la ficha', $contrato->consentimiento_imagen ? 'autorizada' : 'no autorizada'),
        $fila('Redes sociales', $contrato->consentimiento_difusion ? 'autorizada' : 'no autorizada'),
    ];
@endphp

@section('contenido')
    @if ($contrato->cliente)
        <a href="{{ route('panel.clientes.show', $contrato->cliente->uuid) }}"
           class="mb-4 inline-flex text-sm text-pg-tiza/60 transition-colors hover:text-pg-tiza print:hidden">
            ← Volver a la ficha
        </a>
    @endif

    <section class="mb-5 rounded-2xl border border-pg-tiza/10 bg-pg-grafito p-5 text-sm print:hidden">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <h1 class="font-display text-2xl uppercase">Constancia de la firma</h1>
            @if ($contrato->contenido)
                <button type="button" id="imprimir"
                        class="rounded-xl border border-pg-tiza/20 px-4 py-2 text-sm transition-colors hover:bg-pg-negro">
                    Imprimir o guardar como PDF
                </button>
            @endif
        </div>

        @if ($contrato->datos_borrados_en)
            <p class="mt-2 text-pg-tiza/70">
                Los datos personales de este socio se borraron el {{ $contrato->datos_borrados_en->format('d/m/Y') }}.
                Del contrato queda solo su huella: prueba de que existió un documento firmado, sin el documento.
            </p>
        @elseif ($integro)
            <p class="mt-2 text-emerald-300">
                El documento está igual que cuando se firmó: su huella cuadra.
            </p>
        @else
            <p class="mt-2 font-semibold text-pg-rojo-claro">
                El documento guardado NO coincide con su huella: se modificó después de la firma.
            </p>
        @endif

        <dl class="mt-4 grid gap-x-6 gap-y-2.5 sm:grid-cols-2">
            @foreach ($constancia as $dato)
                <div>
                    <dt class="text-xs uppercase tracking-wider text-pg-acero">{{ $dato['que'] }}</dt>
                    <dd class="mt-0.5 break-words text-pg-tiza/85">{{ $dato['valor'] }}</dd>
                </div>
            @endforeach
            <div class="sm:col-span-2">
                <dt class="text-xs uppercase tracking-wider text-pg-acero">Huella (SHA-256)</dt>
                <dd class="mt-0.5 break-all font-mono text-xs text-pg-tiza/70">{{ $contrato->huella ?? '-' }}</dd>
            </div>
        </dl>
    </section>

    @if ($contrato->contenido)
        <article class="rounded-2xl bg-white px-5 py-8 text-neutral-900 shadow-2xl sm:px-10 print:rounded-none print:px-0 print:py-0 print:shadow-none">
            {!! $contrato->contenido !!}
        </article>
    @endif
@endsection

@push('scripts')
<script>
    document.getElementById('imprimir')?.addEventListener('click', function () {
        window.print();
    });
</script>
@endpush
