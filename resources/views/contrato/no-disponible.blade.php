@extends('layouts.documento')

@section('titulo', 'Enlace no disponible')
@section('etiqueta', 'Contrato')

@php($correoDelGimnasio = \App\Support\Ajustes::obtener('gimnasio.email'))

@section('contenido')
    <div class="rounded-2xl border border-pg-tiza/10 bg-pg-grafito px-6 py-12 text-center">
        <h1 class="font-display text-3xl uppercase">
            {{ $motivo === 'vencido' ? 'Este enlace venció' : 'Este enlace ya no sirve' }}
        </h1>

        <p class="mx-auto mt-4 max-w-md leading-relaxed text-pg-tiza/70">
            @if ($motivo === 'vencido')
                Servía hasta el {{ $contrato?->vence_en?->format('d/m/Y') }}. Pídele al gimnasio que te mande uno nuevo.
            @else
                Puede que el gimnasio te haya mandado uno más nuevo: busca el último correo. Si no lo encuentras, pídelo en el mesón.
            @endif
        </p>

        @if ($correoDelGimnasio)
            <a href="mailto:{{ $correoDelGimnasio }}"
               class="mt-7 inline-block rounded-xl bg-pg-rojo px-5 py-3 font-semibold text-white transition-colors hover:bg-pg-rojo-oscuro">
                Escribir al gimnasio
            </a>
        @endif
    </div>
@endsection
