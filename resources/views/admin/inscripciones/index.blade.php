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
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 180px;">
                                <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'id_cliente', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark">
                                    Cliente <i class="fas fa-sort fa-xs"></i>
                                </a>
                            </th>
                            <th style="width: 110px;">
                                <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'fecha_vencimiento', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark">
                                    Plazo <i class="fas fa-sort fa-xs"></i>
                                </a>
                            </th>
                            <th style="width: 100px;">Precios</th>
                            <th style="width: 110px;">Est. Pago</th>
                            <th style="width: 85px;" class="text-center">Convenio</th>
                            <th style="width: 75px;" class="text-center">Estado</th>
                            <th style="width: 70px;" class="text-center">Pausa</th>
                            <th style="width: 140px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscripciones as $inscripcion)
                            <tr>
                                <td class="text-muted"><small>{{ $inscripcion->id }}</small></td>
                                <td>
                                    <strong class="text-dark" style="font-size: 0.93rem;">
                                        {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                                    </strong>
                                    <br>
                                    <small class="text-muted" style="font-size: 0.8rem;">
                                        {{ $inscripcion->membresia->nombre }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                    @endphp
                                    <div class="d-flex flex-column">
                                        @if($diasRestantes > 30)
                                            <span class="badge bg-success mb-1">
                                                <i class="fas fa-calendar-alt fa-fw"></i> {{ $diasRestantes }}d
                                            </span>
                                        @elseif($diasRestantes > 7)
                                            <span class="badge bg-info mb-1">
                                                <i class="fas fa-calendar-alt fa-fw"></i> {{ $diasRestantes }}d
                                            </span>
                                        @elseif($diasRestantes > 0)
                                            <span class="badge bg-warning mb-1">
                                                <i class="fas fa-clock fa-fw"></i> {{ $diasRestantes }}d
                                            </span>
                                        @else
                                            <span class="badge bg-danger mb-1">
                                                <i class="fas fa-exclamation-triangle fa-fw"></i> Vencida
                                            </span>
                                        @endif
                                        <small class="text-muted" style="font-size: 0.73rem;">
                                            {{ $inscripcion->fecha_vencimiento->format('d/m/y') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div style="font-size: 0.85rem;">
                                            <small class="text-muted">Base:</small>
                                            <strong class="text-secondary">
                                                ${{ number_format($inscripcion->precio_base, 0, '.', '.') }}
                                            </strong>
                                        </div>
                                        @if($inscripcion->descuento_aplicado > 0)
                                            <div style="font-size: 0.85rem;">
                                                <small class="text-muted">Desc:</small>
                                                <span class="badge bg-danger" style="font-size: 0.75rem;">
                                                    -${{ number_format($inscripcion->descuento_aplicado, 0, '.', '.') }}
                                                </span>
                                            </div>
                                        @endif
                                        <div style="font-size: 0.87rem; border-top: 1px solid #e9ecef; padding-top: 0.4rem;">
                                            <small class="text-muted">Final:</small>
                                            <strong class="text-primary">
                                                ${{ number_format($inscripcion->precio_final ?? $inscripcion->precio_base, 0, '.', '.') }}
                                            </strong>
                                        </div>
                                    </div>
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
                                    {!! $inscripcion->estado->badge !!}
                                </td>
                                <td class="text-center">
                                    @php
                                        $estaPausada = $inscripcion->estaPausada();
                                    @endphp
                                    
                                    @if($estaPausada)
                                        <span class="badge bg-warning" title="Pausada - {{ $inscripcion->razon_pausa }}">
                                            <i class="fas fa-pause-circle fa-fw"></i> 
                                            {{ $inscripcion->dias_pausa }}d
                                        </span>
                                    @else
                                        <span class="badge bg-secondary" title="No pausada">
                                            <i class="fas fa-check fa-fw"></i> Activa
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" 
                                           class="btn btn-info btn-sm" title="Ver">
                                            <i class="fas fa-eye fa-fw"></i>
                                        </a>
                                        <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" 
                                           class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit fa-fw"></i>
                                        </a>
                                        <a href="{{ route('admin.pagos.create', ['inscripcion_id_preselect' => $inscripcion->id]) }}" 
                                           class="btn btn-primary btn-sm" title="Nuevo Pago">
                                            <i class="fas fa-plus fa-fw"></i>
                                        </a>
                                        <a href="{{ route('admin.pagos.index', ['uuid' => $inscripcion->uuid]) }}" 
                                           class="btn btn-success btn-sm" title="Ver Pagos">
                                            <i class="fas fa-list fa-fw"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
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
        
        /* Table base styling */
        .table {
            margin-bottom: 0;
            table-layout: fixed;
        }
        
        .table th {
            white-space: nowrap;
            vertical-align: middle;
            font-weight: 700;
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
            padding: 0.9rem 0.7rem;
            letter-spacing: 0.3px;
            font-size: 0.9rem;
            text-overflow: ellipsis;
            overflow: hidden;
        }
        
        .table td {
            vertical-align: middle;
            padding: 0.85rem 0.7rem;
            word-wrap: break-word;
        }
        
        /* Row styling */
        .table tbody tr {
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table tbody tr:hover {
            background-color: #f0f5ff;
        }
        
        /* Badge styling */
        .badge {
            padding: 0.4rem 0.65rem;
            font-size: 0.82rem;
            font-weight: 500;
            white-space: nowrap;
            display: inline-block;
            margin-bottom: 0.25rem;
        }
        
        .badge small {
            display: block;
            margin-top: 0.3rem;
            font-weight: 400;
            font-size: 0.75rem;
        }
        
        /* Progress bar */
        .progress {
            height: 4px;
            margin-top: 0.4rem;
            background-color: #e9ecef;
        }
        
        .progress-bar {
            border-radius: 2px;
        }
        
        /* Column 1: ID */
        th:nth-child(1), td:nth-child(1) {
            width: 40px;
            text-align: center;
        }
        
        /* Column 2: Cliente */
        th:nth-child(2), td:nth-child(2) {
            width: 180px;
        }
        
        td:nth-child(2) strong {
            display: block;
            font-size: 0.93rem;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        td:nth-child(2) small {
            font-size: 0.8rem;
            color: #6c757d;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Column 3: Plazo */
        th:nth-child(3), td:nth-child(3) {
            width: 110px;
        }
        
        td:nth-child(3) .badge {
            font-size: 0.78rem;
            padding: 0.35rem 0.6rem;
        }
        
        td:nth-child(3) small {
            font-size: 0.73rem;
            color: #6c757d;
            margin-top: 0.25rem;
            display: block;
        }
        
        /* Column 4: Precios */
        th:nth-child(4), td:nth-child(4) {
            width: 100px;
        }
        
        td:nth-child(4) {
            font-size: 0.85rem;
            padding: 0.7rem 0.6rem;
        }
        
        td:nth-child(4) > div {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        
        td:nth-child(4) .badge {
            font-size: 0.73rem;
            padding: 0.3rem 0.5rem;
            display: inline-block;
            width: fit-content;
        }
        
        td:nth-child(4) small {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        td:nth-child(4) strong {
            font-size: 0.88rem;
        }
        
        /* Column 5: Estado Pago */
        th:nth-child(5), td:nth-child(5) {
            width: 110px;
        }
        
        td:nth-child(5) .badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.65rem;
        }
        
        td:nth-child(5) small {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
            display: block;
        }
        
        /* Column 6: Convenio */
        th:nth-child(6), td:nth-child(6) {
            width: 85px;
            text-align: center;
        }
        
        td:nth-child(6) .badge {
            font-size: 0.78rem;
            padding: 0.4rem 0.6rem;
        }
        
        td:nth-child(6) small {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.2rem;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Column 7: Estado */
        th:nth-child(7), td:nth-child(7) {
            width: 75px;
            text-align: center;
        }
        
        td:nth-child(7) .badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.65rem;
        }
        
        /* Column 8: Pausa */
        th:nth-child(8), td:nth-child(8) {
            width: 70px;
            text-align: center;
        }
        
        td:nth-child(8) .badge {
            font-size: 0.78rem;
            padding: 0.4rem 0.6rem;
        }
        
        /* Column 9: Acciones */
        th:nth-child(9), td:nth-child(9) {
            width: 140px;
            text-align: center;
        }
        
        .btn-group-sm {
            gap: 2px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-group-sm .btn {
            padding: 0.35rem 0.5rem;
            font-size: 0.85rem;
            flex: 0 0 auto;
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }
        
        tr:hover .btn-group-sm .btn {
            opacity: 1;
        }
        
        .btn-group-sm .btn i {
            font-size: 0.9rem;
        }
        
        /* Empty state */
        tbody tr:only-child td {
            padding: 3rem 1rem;
            text-align: center;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .table {
                font-size: 0.9rem;
            }
            
            .table th, .table td {
                padding: 0.75rem 0.6rem;
            }
            
            th:nth-child(2), td:nth-child(2) { width: 160px; }
            th:nth-child(3), td:nth-child(3) { width: 100px; }
            th:nth-child(9), td:nth-child(9) { width: 130px; }
        }
        
        @media (max-width: 768px) {
            .table {
                font-size: 0.85rem;
            }
            
            .table th, .table td {
                padding: 0.6rem 0.5rem;
            }
            
            .badge {
                padding: 0.35rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .btn-group-sm .btn {
                padding: 0.3rem 0.4rem;
                font-size: 0.75rem;
            }
            
            td:nth-child(2) strong { font-size: 0.88rem; }
            th:nth-child(2), td:nth-child(2) { width: 140px; }
            
            td:nth-child(4) .badge { font-size: 0.7rem; }
        }
    </style>
@endpush
