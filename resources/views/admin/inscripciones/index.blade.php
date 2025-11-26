@extends('adminlte::page')

@section('title', 'Inscripciones - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Gestión de Inscripciones</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.inscripciones.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nueva Inscripción
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Éxito!</strong> {{ $message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card card-primary collapsed-card">
        <div class="card-header with-border">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros y Búsqueda</h3>
            <div class="card-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display:none;">
            <form method="GET" class="form-horizontal">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cliente">Cliente:</label>
                            <input type="text" id="cliente" name="cliente" class="form-control" 
                                   placeholder="Nombre, apellido o email..." value="{{ request('cliente') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select id="estado" name="estado" class="form-control">
                                <option value="">-- Todos --</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}" {{ request('estado') == $estado->id ? 'selected' : '' }}>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="membresia">Membresía:</label>
                            <select id="membresia" name="membresia" class="form-control">
                                <option value="">-- Todas --</option>
                                @foreach($membresias as $membresia)
                                    <option value="{{ $membresia->id }}" {{ request('membresia') == $membresia->id ? 'selected' : '' }}>
                                        {{ $membresia->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-secondary btn-block mt-2">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Inscripciones</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>
                            <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'id_cliente', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" style="cursor: pointer; text-decoration: none; color: inherit;">
                                Cliente <i class="fas fa-sort"></i>
                            </a>
                        </th>
                        <th>Estado</th>
                        <th>Pausa</th>
                        <th>Monto</th>
                        <th>Estado de Pago</th>
                        <th>
                            <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'fecha_inicio', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" style="cursor: pointer; text-decoration: none; color: inherit;">
                                Inicio <i class="fas fa-sort"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.inscripciones.index', array_merge(request()->query(), ['ordenar' => 'fecha_vencimiento', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc'])) }}" style="cursor: pointer; text-decoration: none; color: inherit;">
                                Vencimiento <i class="fas fa-sort"></i>
                            </a>
                        </th>
                        <th>Días Restantes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inscripciones as $inscripcion)
                        <tr>
                            <td>{{ $inscripcion->id }}</td>
                            <td>
                                <strong>{{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</strong>
                            </td>
                            <td>
                                {!! \App\Helpers\EstadoHelper::badgeWithIcon($inscripcion->estado) !!}
                            </td>
                            <td>
                                @php
                                    // Usar el método del modelo para verificar pausa
                                    $estaPausada = $inscripcion->estaPausada();
                                @endphp
                                
                                @if($estaPausada)
                                    <span class="badge bg-warning" title="{{ $inscripcion->razon_pausa }}">
                                        <i class="fas fa-pause-circle fa-fw"></i> 
                                        @if($inscripcion->dias_pausa == 7)
                                            Pausada - 7d
                                        @elseif($inscripcion->dias_pausa == 14)
                                            Pausada - 14d
                                        @elseif($inscripcion->dias_pausa == 30)
                                            Pausada - 30d
                                        @else
                                            Pausada
                                        @endif
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-play-circle fa-fw"></i> Activo
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong>${{ number_format($inscripcion->precio_final ?? $inscripcion->precio_base, 2) }}</strong>
                            </td>
                            <td>
                                @php
                                    // Usar el método del modelo para obtener estado de pago preciso
                                    $estadoPago = $inscripcion->obtenerEstadoPago();
                                    $montoTotal = $estadoPago['monto_total'];
                                    $totalAbonado = $estadoPago['total_abonado'];
                                    $pendiente = $estadoPago['pendiente'];
                                    $porcentajePagado = $estadoPago['porcentaje_pagado'];
                                    $estado = $estadoPago['estado'];
                                @endphp
                                
                                @if($estado === 'pagado')
                                    <!-- Pagado completamente -->
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle fa-fw"></i> Pagado
                                    </span>
                                @elseif($estado === 'parcial')
                                    <!-- Pago parcial -->
                                    <span class="badge bg-warning">
                                        <i class="fas fa-hourglass-half fa-fw"></i> Parcial
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Abonado: <strong>${{ number_format($totalAbonado, 0, ',', '.') }}</strong><br>
                                        Pendiente: <strong class="text-danger">${{ number_format($pendiente, 0, ',', '.') }}</strong>
                                    </small>
                                @else
                                    <!-- Sin pagos aún -->
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-circle fa-fw"></i> Pendiente
                                    </span>
                                    <br>
                                    <small class="text-muted">Total: <strong>${{ number_format($montoTotal, 0, ',', '.') }}</strong></small>
                                @endif
                            </td>
                            <td>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                @endphp
                                @if($diasRestantes > 7)
                                    <span class="badge bg-info">{{ $diasRestantes }} días</span>
                                @elseif($diasRestantes > 0)
                                    <span class="badge bg-warning">{{ $diasRestantes }} días</span>
                                @else
                                    <span class="badge bg-danger">Vencida</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn btn-sm btn-info" title="Ver Inscripción">
                                    <i class="fas fa-eye fa-fw"></i>
                                </a>
                                <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit fa-fw"></i>
                                </a>
                                <a href="{{ route('admin.pagos.index', ['uuid' => $inscripcion->uuid]) }}" class="btn btn-sm btn-success" title="Ver Pagos">
                                    <i class="fas fa-money-bill-wave fa-fw"></i>
                                </a>
                                <form action="{{ route('admin.inscripciones.destroy', $inscripcion) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')" title="Eliminar">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No hay inscripciones registradas</td>
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
@stop
