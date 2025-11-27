@extends('adminlte::page')

@section('title', 'Gestión de Pagos - EstóicosGym')

@section('css')
    <style>
        .estado-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .estado-pendiente {
            background: #fff3cd;
            color: #856404;
        }
        .estado-pagado {
            background: #d4edda;
            color: #155724;
        }
        .estado-parcial {
            background: #cce5ff;
            color: #004085;
        }
        .monto-grande {
            font-weight: 600;
            font-size: 14px;
        }
        .tabla-pagos tbody tr:hover {
            background-color: #f5f5f5;
        }
        .badge-method {
            font-size: 11px;
            padding: 4px 8px;
        }
        .cell-cliente {
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-3">
        <div class="col-sm-6">
            <h1 class="m-0">
                <i class="fas fa-receipt"></i> Gestión de Pagos
            </h1>
            <small class="text-muted d-block mt-1">{{ $pagos->total() ?? 0 }} pagos registrados</small>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('admin.pagos.create') }}" class="btn btn-success btn-lg">
                <i class="fas fa-plus-circle"></i> Nuevo Pago
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filtros y Búsqueda -->
    <div class="card card-outline card-primary collapsed-card mb-4">
        <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
            <div class="card-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display:none;">
            <form action="{{ route('admin.pagos.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cliente"><i class="fas fa-user"></i> Cliente</label>
                            <input type="text" id="cliente" name="cliente" class="form-control" 
                                   placeholder="Nombre o apellido..." value="{{ request('cliente') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="metodo_pago"><i class="fas fa-credit-card"></i> Método de Pago</label>
                            <select id="metodo_pago" name="metodo_pago" class="form-control">
                                <option value="">-- Todos --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}" {{ request('metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                        {{ $metodo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Pagos -->
    <div class="card card-outline card-success">
        <div class="card-header bg-success">
            <h3 class="card-title"><i class="fas fa-table"></i> Listado de Pagos</h3>
        </div>
        <div class="card-body table-responsive p-0">
            @forelse($pagos as $pago)
                @if($loop->first)
                    <table class="table table-hover table-striped tabla-pagos mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 18%"><i class="fas fa-user"></i> Cliente</th>
                                <th style="width: 12%">Inscripción</th>
                                <th style="width: 10%" class="text-right">Monto Total</th>
                                <th style="width: 10%" class="text-right">Abonado</th>
                                <th style="width: 12%">Estado</th>
                                <th style="width: 15%" class="text-center">Detalles</th>
                                <th style="width: 18%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                @endif
                            <tr>
                                <td><strong>#{{ $pago->id }}</strong></td>
                                <td class="cell-cliente">
                                    <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" title="{{ $pago->inscripcion->cliente->nombres }}">
                                        {{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="badge bg-primary">
                                        #{{ $pago->inscripcion->id }}
                                    </a>
                                </td>
                                <td class="text-right monto-grande">
                                    ${{ number_format($pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base, 0, '.', '.') }}
                                </td>
                                <td class="text-right monto-grande text-success">
                                    ${{ number_format($pago->monto_abonado, 0, '.', '.') }}
                                </td>
                                <td>
                                    @php
                                        $saldoPendiente = $pago->inscripcion->getSaldoPendiente();
                                        $porcentajePago = ($pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base) > 0 
                                            ? (($pago->monto_abonado / ($pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base)) * 100)
                                            : 0;
                                    @endphp
                                    
                                    @if($saldoPendiente <= 0)
                                        <span class="estado-badge estado-pagado">
                                            <i class="fas fa-check-circle"></i> Pagado
                                        </span>
                                    @elseif($porcentajePago > 0)
                                        <span class="estado-badge estado-parcial">
                                            <i class="fas fa-hourglass-half"></i> {{ intval($porcentajePago) }}%
                                        </span>
                                    @else
                                        <span class="estado-badge estado-pendiente">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center text-muted small">
                                    @if($pago->metodoPagoPrincipal)
                                        <span class="badge-method badge bg-light text-dark" title="{{ $pago->metodoPagoPrincipal->nombre }}">
                                            @if($pago->metodoPagoPrincipal->codigo === 'efectivo')
                                                <i class="fas fa-money-bill"></i> Efectivo
                                            @elseif($pago->metodoPagoPrincipal->codigo === 'tarjeta')
                                                <i class="fas fa-credit-card"></i> Tarjeta
                                            @elseif($pago->metodoPagoPrincipal->codigo === 'transferencia')
                                                <i class="fas fa-university"></i> Transfer.
                                            @else
                                                <i class="fas fa-ellipsis-h"></i> {{ $pago->metodoPagoPrincipal->nombre }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white">
                                            <i class="fas fa-question-circle"></i> Sin método
                                        </span>
                                    @endif
                                    
                                    @if($pago->es_plan_cuotas)
                                        <span class="badge bg-warning text-dark" title="Plan de cuotas">
                                            <i class="fas fa-list-ol"></i> Cuotas
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('¿Eliminar este pago?')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                @if($loop->last)
                        </tbody>
                    </table>
                @endif
            @empty
                <div class="alert alert-info text-center py-4 m-3">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p class="mb-0"><strong>No hay pagos registrados</strong></p>
                    <small class="text-muted">Usa el botón "Nuevo Pago" para crear uno</small>
                </div>
            @endforelse
        </div>

        @if($pagos->hasPages())
            <div class="card-footer text-muted">
                <nav aria-label="Paginación">
                    <div class="d-flex justify-content-center">
                        {{ $pagos->links('pagination::bootstrap-4') }}
                    </div>
                </nav>
            </div>
        @endif
    </div>
@stop
