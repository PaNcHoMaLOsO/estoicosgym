@extends('adminlte::page')

@section('title', 'Detalles Inscripción - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-list-check"></i> Detalles Inscripción
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Información Principal -->
    <div class="card card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-check"></i> Información Principal
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-user"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cliente</span>
                            <span class="info-box-number">
                                <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                    {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                                </a>
                            </span>
                            <small class="text-muted">{{ $inscripcion->cliente->email }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Membresía</span>
                            <span class="info-box-number">{{ $inscripcion->membresia->nombre }}</span>
                            <small class="text-muted">{{ $inscripcion->membresia->duracion_meses }} meses</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon {{ $inscripcion->estado->activo ? 'bg-success' : 'bg-danger' }}">
                            <i class="fas fa-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado</span>
                            <span class="info-box-number">
                                {!! \App\Helpers\EstadoHelper::badgeWithIcon($inscripcion->estado) !!}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    @if($inscripcion->convenio)
                        <div class="info-box">
                            <span class="info-box-icon bg-secondary">
                                <i class="fas fa-handshake"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Convenio</span>
                                <span class="info-box-number">{{ $inscripcion->convenio->nombre }}</span>
                            </div>
                        </div>
                    @else
                        <div class="info-box">
                            <span class="info-box-icon bg-secondary">
                                <i class="fas fa-ban"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Convenio</span>
                                <span class="info-box-number">Sin Convenio</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Fechas y Duración -->
    <div class="card card-info mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-alt"></i> Fechas y Duración
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha Inicio</span>
                            <span class="info-box-number">{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-calendar-times"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha Vencimiento</span>
                            <span class="info-box-number">{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    @php
                        $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                    @endphp
                    <div class="info-box">
                        <span class="info-box-icon {{ $diasRestantes > 0 ? 'bg-success' : 'bg-danger' }}">
                            <i class="fas fa-hourglass-end"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Días Restantes</span>
                            <span class="info-box-number">
                                @if($diasRestantes > 0)
                                    {{ $diasRestantes }} días
                                @else
                                    Vencida
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-info-circle"></i> Duración Total</label>
                        <div class="border rounded p-3 bg-light">
                            <strong>{{ $inscripcion->fecha_inicio->diffInDays($inscripcion->fecha_vencimiento) }} días</strong>
                            <small class="text-muted d-block mt-1">
                                Desde {{ $inscripcion->fecha_inicio->format('d/m/Y') }} hasta {{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Precios y Descuentos -->
    <div class="card card-warning mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-dollar-sign"></i> Precios y Descuentos
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Precio Base</span>
                            <span class="info-box-number">${{ number_format($inscripcion->precio_base, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-percent"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Descuento</span>
                            <span class="info-box-number">${{ number_format($inscripcion->descuento_aplicado, 2, ',', '.') }}</span>
                            @if($inscripcion->motivoDescuento)
                                <small class="text-muted">{{ $inscripcion->motivoDescuento->nombre }}</small>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Precio Final</span>
                            <span class="info-box-number">${{ number_format($inscripcion->precio_base - $inscripcion->descuento_aplicado, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Observaciones -->
    @if($inscripcion->observaciones)
        <div class="card card-secondary mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-align-left"></i> Observaciones
                </h3>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 bg-light">
                    {{ $inscripcion->observaciones }}
                </div>
            </div>
        </div>
    @endif

    <!-- Resumen de Pagos -->
    <div class="card card-success mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-money-bill-wave"></i> Resumen de Pagos y Cuotas
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-receipt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total a Pagar</span>
                            <span class="info-box-number">${{ number_format($estadoPago['monto_total'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Abonado</span>
                            <span class="info-box-number">${{ number_format($estadoPago['total_abonado'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pendiente</span>
                            <span class="info-box-number">${{ number_format($estadoPago['pendiente'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-percentage"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pagado</span>
                            <span class="info-box-number">{{ number_format($estadoPago['porcentaje_pagado'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de progreso -->
            <div class="progress mb-3" style="height: 25px;">
                @php
                    $porcentaje = $estadoPago['porcentaje_pagado'];
                    $colorBarra = $porcentaje >= 100 ? 'bg-success' : ($porcentaje >= 75 ? 'bg-info' : ($porcentaje >= 50 ? 'bg-warning' : 'bg-danger'));
                @endphp
                <div class="progress-bar {{ $colorBarra }}" role="progressbar" 
                     style="width: {{ min($porcentaje, 100) }}%" 
                     aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                    {{ number_format($porcentaje, 1) }}%
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="alert alert-light">
                        <strong><i class="fas fa-list-ol"></i> Cantidad de Pagos:</strong> {{ $estadoPago['total_pagos'] }}
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="alert alert-light">
                        <strong><i class="fas fa-check-double"></i> Estado:</strong> 
                        <span class="badge {{ $estadoPago['estado'] == 'pagado' ? 'badge-success' : ($estadoPago['estado'] == 'parcial' ? 'badge-warning' : 'badge-danger') }}">
                            {{ ucfirst($estadoPago['estado']) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Botón para agregar pago -->
            <div class="mt-3">
                <a href="{{ route('admin.pagos.create', ['id_inscripcion' => $inscripcion->id]) }}" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Registrar Pago
                </a>
            </div>
        </div>
    </div>

            @if($inscripcion->pagos->count() > 0)
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Cuota</th>
                                <th>Vencimiento Cuota</th>
                                <th>Estado</th>
                                <th>Método</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inscripcion->pagos->sortByDesc('fecha_pago')->take(10) as $pago)
                                <tr>
                                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                    <td><strong>${{ number_format($pago->monto_abonado, 2, ',', '.') }}</strong></td>
                                    <td>
                                        @if($pago->cantidad_cuotas > 1)
                                            <span class="badge badge-info">{{ $pago->numero_cuota }}/{{ $pago->cantidad_cuotas }}</span>
                                        @else
                                            <span class="badge badge-secondary">Única</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pago->fecha_vencimiento_cuota)
                                            {{ $pago->fecha_vencimiento_cuota->format('d/m/Y') }}
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>{!! \App\Helpers\EstadoHelper::badgeWithIcon($pago->estado) !!}</td>
                                    <td>{{ $pago->metodoPago->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-sm btn-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mt-3" role="alert">
                    <i class="fas fa-info-circle"></i> No hay pagos registrados para esta inscripción.
                </div>
            @endif
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="card card-light">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <small class="d-block text-muted">
                        <i class="fas fa-calendar-plus"></i> Creada: {{ $inscripcion->created_at->format('d/m/Y H:i') }}
                    </small>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-muted text-right">
                        <i class="fas fa-sync"></i> Actualizada: {{ $inscripcion->updated_at->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <!-- Botones de Acción -->
    <div class="card card-light">
        <div class="card-body">
            <div class="d-flex gap-2 justify-content-between flex-wrap">
                <div>
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div>
                    <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.inscripciones.destroy', $inscripcion) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro? Esta acción no puede revertirse')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
