@extends('layouts.documento')

@section('titulo', 'Contrato firmado')
@section('etiqueta', 'Contrato firmado')

@section('contenido')
    @if ($recien)
        <div class="mb-6 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-5 py-4 print:hidden" role="status">
            <p class="font-display text-2xl uppercase text-pg-tiza">¡Listo! Quedó firmado</p>
            <p class="mt-1 text-sm leading-relaxed text-pg-tiza/75">
                Te mandamos una copia a {{ $contrato->email_destino }}. También puedes guardarla desde aquí.
            </p>
        </div>
    @endif

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 px-1 print:hidden">
        <p class="text-sm text-pg-tiza/65">
            Firmado por {{ $contrato->firmante_nombre }} el {{ $contrato->firmado_en->format('d/m/Y \a \l\a\s H:i') }}.
        </p>
        <button type="button" id="imprimir"
                class="rounded-xl border border-pg-tiza/20 px-4 py-2 text-sm transition-colors hover:bg-pg-grafito">
            Imprimir o guardar como PDF
        </button>
    </div>

    <article class="rounded-2xl bg-white px-5 py-8 text-neutral-900 shadow-2xl sm:px-10 print:rounded-none print:px-0 print:py-0 print:shadow-none">
        {!! $contrato->contenido !!}
    </article>

    <p class="mt-4 break-all px-1 text-xs text-pg-tiza/40 print:text-neutral-500">
        Huella del documento (SHA-256): {{ $contrato->huella }}
    </p>
@endsection

@push('scripts')
<script>
    document.getElementById('imprimir').addEventListener('click', function () {
        window.print();
    });
</script>
@endpush
