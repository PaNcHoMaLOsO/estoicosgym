@extends('adminlte::page')

@section('title', 'Detalles Pago - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Pago #{{ $pago->id }}</h1>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Pago</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">ID Pago:</dt>
                        <dd class="col-sm-7"><strong>{{ $pago->id }}</strong></dd>

                        <dt class="col-sm-5">Fecha Pago:</dt>
                        <dd class="col-sm-7">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-5">Monto Abonado:</dt>
                        <dd class="col-sm-7"><strong class="text-success">${{ number_format($pago->monto_abonado, 2, ',', '.') }}</strong></dd>

                        <dt class="col-sm-5">Método Pago:</dt>
                        <dd class="col-sm-7">
                            <span class="badge badge-info">{{ $pago->metodoPago->nombre }}</span>
                        </dd>

                        @if($pago->referencia)
                            <dt class="col-sm-5">Referencia:</dt>
                            <dd class="col-sm-7">{{ $pago->referencia }}</dd>
                        @endif

                        <dt class="col-sm-5">Creado:</dt>
                        <dd class="col-sm-7">{{ $pago->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de la Inscripción</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">ID Inscripción:</dt>
                        <dd class="col-sm-7">
                            <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}">
                                #{{ $pago->inscripcion->id }}
                            </a>
                        </dd>

                        <dt class="col-sm-5">Cliente:</dt>
                        <dd class="col-sm-7">
                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}">
                                {{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}
                            </a>
                        </dd>

                        <dt class="col-sm-5">Email:</dt>
                        <dd class="col-sm-7">{{ $pago->inscripcion->cliente->email }}</dd>

                        <dt class="col-sm-5">Estado:</dt>
                        <dd class="col-sm-7">
                            @if($pago->inscripcion->estado->nombre === 'Activa')
                                <span class="badge badge-success">{{ $pago->inscripcion->estado->nombre }}</span>
                            @elseif($pago->inscripcion->estado->nombre === 'Por Vencer')
                                <span class="badge badge-warning">{{ $pago->inscripcion->estado->nombre }}</span>
                            @else
                                <span class="badge badge-danger">{{ $pago->inscripcion->estado->nombre }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5">Inicio:</dt>
                        <dd class="col-sm-7">{{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }}</dd>

                        <dt class="col-sm-5">Vencimiento:</dt>
                        <dd class="col-sm-7">{{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resumen de Pagos de la Inscripción</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pagos Inscripción</span>
                            <span class="info-box-number">${{ number_format($pago->inscripcion->pagos->sum('monto_abonado'), 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar este pago?')">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
@stop
