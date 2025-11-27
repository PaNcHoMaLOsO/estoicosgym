@extends('adminlte::page')

@section('title', 'Inscripciones')

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-users fa-fw text-primary"></i> Inscripciones
                </h1>
                <small class="text-muted">Total: <strong>{{ $inscripciones->total() }}</strong> registros</small>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('admin.inscripciones.create') }}" class="btn btn-success btn-lg" title="Nueva Inscripción">
                    <i class="fas fa-plus fa-fw"></i> Nueva Inscripción
                </a>
            </div>
        </div>

        <!-- Filters Card - Collapsible -->
        <div class="card card-primary card-outline collapsed-card">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    <i class="fas fa-sliders-h fa-fw"></i> Filtros Avanzados
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Expandir/Contraer">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <div class="card-body" style="display: none;">
                <form action="{{ route('admin.inscripciones.index') }}" method="GET" class="mb-0">
                    <div class="row">
                        <!-- Filtro Cliente -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cliente" class="form-label">
                                    <i class="fas fa-search fa-fw text-muted"></i> Cliente
                                </label>
                                <input type="text" class="form-control form-control-sm" id="cliente" name="cliente" 
                                       value="{{ request('cliente') }}" placeholder="Nombre, apellido o email...">
                            </div>
                        </div>

                        <!-- Filtro Estado -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="estado" class="form-label">
                                    <i class="fas fa-info-circle fa-fw text-muted"></i> Estado
                                </label>
                                <select class="form-control form-control-sm" id="estado" name="estado">
                                    <option value="">-- Todos --</option>
                                    @foreach($estados as $estado)
                                        <option value="{{ $estado->id }}" {{ request('estado') == $estado->id ? 'selected' : '' }}>
                                            {{ $estado->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Filtro Membresía -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="membresia" class="form-label">
                                    <i class="fas fa-dumbbell fa-fw text-muted"></i> Membresía
                                </label>
                                <select class="form-control form-control-sm" id="membresia" name="membresia">
                                    <option value="">-- Todas --</option>
                                    @foreach($membresias as $membresia)
                                        <option value="{{ $membresia->id }}" {{ request('membresia') == $membresia->id ? 'selected' : '' }}>
                                            {{ $membresia->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Filtro Rango de Fechas -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_inicio" class="form-label">
                                    <i class="fas fa-calendar fa-fw text-muted"></i> Rango de Fechas
                                </label>
                                <div class="input-group input-group-sm mb-2">
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                           value="{{ request('fecha_inicio') }}" title="Desde">
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                           value="{{ request('fecha_fin') }}" title="Hasta">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search fa-fw"></i> Aplicar Filtros
                            </button>
                            <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-redo fa-fw"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-check fa-fw"></i> Listado de Inscripciones
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary">{{ $inscripciones->count() }}/{{ $inscripciones->total() }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>
                                <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'id_cliente', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark">
                                    Cliente <i class="fas fa-sort fa-xs"></i>
                                </a>
                            </th>
                            <th style="width: 120px;">Estado</th>
                            <th style="width: 100px;">Pausa</th>
                            <th style="width: 100px;"><i class="fas fa-dollar-sign"></i> Monto</th>
                            <th style="width: 140px;">Pago</th>
                            <th>
                                <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'fecha_inicio', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark">
                                    Inicio <i class="fas fa-sort fa-xs"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'fecha_vencimiento', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark">
                                    Vencimiento <i class="fas fa-sort fa-xs"></i>
                                </a>
                            </th>
                            <th style="width: 90px;">Plazo</th>
                            <th style="width: 120px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscripciones as $inscripcion)
                            <tr>
                                <td class="text-muted"><small>{{ $inscripcion->id }}</small></td>
                                <td>
                                    <strong class="text-dark">{{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $inscripcion->membresia->nombre }}</small>
                                </td>
                                <td>
                                    {!! $inscripcion->estado->badge !!}
                                </td>
                                <td>
                                    @php
                                        $estaPausada = $inscripcion->estaPausada();
                                    @endphp
                                    
                                    @if($estaPausada)
                                        <span class="badge bg-warning" title="{{ $inscripcion->razon_pausa }}">
                                            <i class="fas fa-pause-circle fa-fw"></i> 
                                            {{ $inscripcion->dias_pausa }}d
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-play-circle fa-fw"></i> Activo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-primary">${{ number_format($inscripcion->precio_final ?? $inscripcion->precio_base, 0, '.', '.') }}</strong>
                                </td>
                                <td>
                                    @php
                                        $estadoPago = $inscripcion->obtenerEstadoPago();
                                        $montoTotal = $estadoPago['monto_total'];
                                        $totalAbonado = $estadoPago['total_abonado'];
                                        $pendiente = $estadoPago['pendiente'];
                                        $porcentajePagado = $estadoPago['porcentaje_pagado'];
                                        $estado = $estadoPago['estado'];
                                    @endphp
                                    
                                    @if($estado === 'pagado')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle fa-fw"></i> Pagado
                                        </span>
                                    @elseif($estado === 'parcial')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-hourglass-half fa-fw"></i> Parcial
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <strong>${{ number_format($totalAbonado, 0, '.', '.') }}</strong> / 
                                            <strong class="text-danger">${{ number_format($pendiente, 0, '.', '.') }}</strong>
                                        </small>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-circle fa-fw"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td><small>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</small></td>
                                <td>
                                    <small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    @php
                                        $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                    @endphp
                                    @if($diasRestantes > 7)
                                        <span class="badge bg-info">{{ $diasRestantes }}d</span>
                                    @elseif($diasRestantes > 0)
                                        <span class="badge bg-warning">{{ $diasRestantes }}d</span>
                                    @else
                                        <span class="badge bg-danger">Vencida</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" 
                                           class="btn btn-info btn-sm" title="Ver Inscripción">
                                            <i class="fas fa-eye fa-fw"></i>
                                        </a>
                                        <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" 
                                           class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit fa-fw"></i>
                                        </a>
                                        <a href="{{ route('admin.pagos.index', ['uuid' => $inscripcion->uuid]) }}" 
                                           class="btn btn-success btn-sm" title="Ver Pagos">
                                            <i class="fas fa-money-bill-wave fa-fw"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    <strong>No hay inscripciones registradas</strong>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <nav aria-label="Page navigation">
                    <div class="d-flex justify-content-center">
                        {{ $inscripciones->links('pagination::bootstrap-4') }}
                    </div>
                </nav>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .text-decoration-none {
            text-decoration: none !important;
        }
        
        .text-decoration-none:hover {
            text-decoration: underline !important;
        }
        
        .btn-group-sm .btn {
            margin: 0 2px;
        }
        
        .table th {
            white-space: nowrap;
            vertical-align: middle;
        }
        
        .table td {
            vertical-align: middle;
        }
    </style>
@endpush
