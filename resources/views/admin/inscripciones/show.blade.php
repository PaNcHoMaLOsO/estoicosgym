@extends('adminlte::page')

@section('title', 'Detalles Inscripción - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Inscripción #{{ $inscripcion->id }}</h1>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de Inscripción</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID Inscripción:</dt>
                        <dd class="col-sm-8"><strong>{{ $inscripcion->id }}</strong></dd>

                        <dt class="col-sm-4">Cliente:</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $inscripcion->cliente->email }}</dd>

                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            @if($inscripcion->estado->nombre === 'Activa')
                                <span class="badge badge-success">{{ $inscripcion->estado->nombre }}</span>
                            @elseif($inscripcion->estado->nombre === 'Por Vencer')
                                <span class="badge badge-warning">{{ $inscripcion->estado->nombre }}</span>
                            @else
                                <span class="badge badge-danger">{{ $inscripcion->estado->nombre }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Fecha Inicio:</dt>
                        <dd class="col-sm-8">{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Fecha Vencimiento:</dt>
                        <dd class="col-sm-8">{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Días Restantes:</dt>
                        <dd class="col-sm-8">
                            @php
                                $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                            @endphp
                            @if($diasRestantes > 0)
                                <span class="badge badge-success">{{ $diasRestantes }} días</span>
                            @else
                                <span class="badge badge-danger">Vencida</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Duración:</dt>
                        <dd class="col-sm-8">{{ $inscripcion->fecha_inicio->diffInDays($inscripcion->fecha_vencimiento) }} días</dd>

                        <dt class="col-sm-4">Creada:</dt>
                        <dd class="col-sm-8">{{ $inscripcion->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resumen de Pagos</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Pagos</span>
                                    <span class="info-box-number">${{ number_format($inscripcion->pagos->sum('monto_abonado'), 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-receipt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cantidad Pagos</span>
                                    <span class="info-box-number">{{ $inscripcion->pagos->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($inscripcion->pagos->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Últimos Pagos</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inscripcion->pagos->sortByDesc('fecha_pago')->take(5) as $pago)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.pagos.show', $pago) }}">{{ $pago->id }}</a>
                                        </td>
                                        <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                        <td>${{ number_format($pago->monto_abonado, 2, ',', '.') }}</td>
                                        <td>{{ $pago->metodo_pago->nombre ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay pagos registrados para esta inscripción.
                </div>
            @endif
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form action="{{ route('admin.inscripciones.destroy', $inscripcion) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta inscripción?')">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
@stop
