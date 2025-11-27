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
                            <th style="width: 140px;">
                                <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'fecha_vencimiento', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark">
                                    Plazo <i class="fas fa-sort fa-xs"></i>
                                </a>
                            </th>
                            <th style="width: 110px;">Monto Final</th>
                            <th style="width: 140px;">Estado Pago</th>
                            <th style="width: 110px;">Convenio</th>
                            <th style="width: 100px;" class="text-center">Estado</th>
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
                                    <small class="text-muted">
                                        <i class="fas fa-dumbbell fa-fw"></i> {{ $inscripcion->membresia->nombre }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                    @endphp
                                    <div class="d-flex flex-column">
                                        @if($diasRestantes > 30)
                                            <span class="badge bg-success mb-1">
                                                <i class="fas fa-calendar-alt fa-fw"></i> {{ $diasRestantes }} días
                                            </span>
                                        @elseif($diasRestantes > 7)
                                            <span class="badge bg-info mb-1">
                                                <i class="fas fa-calendar-alt fa-fw"></i> {{ $diasRestantes }} días
                                            </span>
                                        @elseif($diasRestantes > 0)
                                            <span class="badge bg-warning mb-1">
                                                <i class="fas fa-clock fa-fw"></i> {{ $diasRestantes }} días
                                            </span>
                                        @else
                                            <span class="badge bg-danger mb-1">
                                                <i class="fas fa-exclamation-triangle fa-fw"></i> Vencida
                                            </span>
                                        @endif
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            Hasta: {{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-primary">
                                        ${{ number_format($inscripcion->precio_final ?? $inscripcion->precio_base, 0, '.', '.') }}
                                    </strong>
                                </td>
                                <td>
                                    @php
                                        $estadoPago = $inscripcion->obtenerEstadoPago();
                                        $estado = $estadoPago['estado'];
                                        $totalAbonado = $estadoPago['total_abonado'];
                                        $pendiente = $estadoPago['pendiente'];
                                        $porcentaje = $estadoPago['porcentaje_pagado'];
                                    @endphp
                                    
                                    <div class="d-flex flex-column">
                                        @if($estado === 'pagado')
                                            <span class="badge bg-success mb-1">
                                                <i class="fas fa-check-circle fa-fw"></i> Pagado
                                            </span>
                                        @elseif($estado === 'parcial')
                                            <span class="badge bg-warning mb-1">
                                                <i class="fas fa-hourglass-half fa-fw"></i> Parcial
                                            </span>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                ${{ number_format($totalAbonado, 0, '.', '.') }} de ${{ number_format($totalAbonado + $pendiente, 0, '.', '.') }}
                                            </small>
                                        @else
                                            <span class="badge bg-danger mb-1">
                                                <i class="fas fa-exclamation-circle fa-fw"></i> Pendiente
                                            </span>
                                        @endif
                                        
                                        <!-- Progress bar -->
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ min($porcentaje, 100) }}%; background-color: {{ $porcentaje >= 100 ? '#28a745' : ($porcentaje >= 50 ? '#ffc107' : '#dc3545') }};" 
                                                 aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($inscripcion->id_convenio)
                                        <span class="badge bg-primary" title="Convenio aplicado">
                                            <i class="fas fa-handshake fa-fw"></i> Sí
                                        </span>
                                        @if($inscripcion->convenio)
                                            <br>
                                            <small class="text-muted">{{ $inscripcion->convenio->nombre }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-minus fa-fw"></i> No
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $estaPausada = $inscripcion->estaPausada();
                                    @endphp
                                    
                                    @if($estaPausada)
                                        <span class="badge bg-warning" title="Pausada - {{ $inscripcion->razon_pausa }}">
                                            <i class="fas fa-pause-circle fa-fw"></i> 
                                            Pausada
                                            <br>
                                            <small>{{ $inscripcion->dias_pausa }}d restantes</small>
                                        </span>
                                    @else
                                        {!! $inscripcion->estado->badge !!}
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
                                <td colspan="7" class="text-center text-muted py-4">
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
            font-weight: 600;
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f0f5ff;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        
        .progress {
            border-radius: 3px;
            background-color: #e9ecef;
        }
        
        .progress-bar {
            transition: width 0.3s ease;
        }
        
        /* Estado column styling */
        td:has(.badge.bg-success) {
            font-weight: 500;
        }
        
        /* Plazo column - compact layout */
        .plazo-cell {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .plazo-cell .badge {
            padding: 0.35rem 0.5rem;
            font-size: 0.8rem;
        }
        
        /* Cliente column - enhanced */
        .cliente-cell strong {
            display: block;
            margin-bottom: 0.25rem;
        }
        
        /* Pago status with progress */
        .pago-status {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        
        .pago-status .badge {
            padding: 0.35rem 0.5rem;
            font-size: 0.8rem;
        }
        
        /* Hover effects */
        .btn-group-sm {
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        tr:hover .btn-group-sm {
            opacity: 1;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table-sm td {
                padding: 0.5rem 0.3rem;
            }
            
            .badge {
                padding: 0.3rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
@endpush
