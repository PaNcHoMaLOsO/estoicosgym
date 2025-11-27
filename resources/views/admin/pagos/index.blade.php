@extends('adminlte::page')

@section('title', 'Pagos - EstóicosGym')

@section('css')
<style>
    .progress-bar-custom {
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 4px;
    }
    .progress-custom {
        height: 6px;
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    .pago-row {
        transition: all 0.3s ease;
    }
    .pago-row:hover {
        background-color: #f8f9fa !important;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
    }
    .monto-cell {
        font-weight: 600;
        font-size: 0.95em;
    }
    .porcentaje-badge {
        display: inline-block;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75em;
        font-weight: 700;
        margin-top: 4px;
    }
    .estado-badge {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 600;
    }
    .saldo-pendiente {
        background-color: #fff3cd;
        color: #856404;
    }
    .saldo-pagado {
        background-color: #d4edda;
        color: #155724;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-credit-card"></i> Gestión de Pagos</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('admin.pagos.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo Pago
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

    <!-- Filtros -->
    <div class="card card-outline card-primary mb-3 collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <form action="{{ route('admin.pagos.index') }}" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <input type="text" id="cliente" name="cliente" class="form-control form-control-sm" 
                           placeholder="Cliente..." value="{{ request('cliente') }}">
                </div>
                <div class="form-group mr-2">
                    <select id="metodo_pago" name="metodo_pago" class="form-control form-control-sm">
                        <option value="">Método...</option>
                        @foreach($metodos_pago as $metodo)
                            <option value="{{ $metodo->id }}" {{ request('metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                {{ $metodo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-sm btn-secondary ml-2">
                    <i class="fas fa-redo"></i> Limpiar
                </a>
            </form>
        </div>
    </div>
    
    <!-- Tabla de Pagos -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Listado de Pagos</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover m-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 8%">#ID</th>
                            <th style="width: 20%">Cliente</th>
                            <th style="width: 12%">Inscripción</th>
                            <th style="width: 15%">Monto Total</th>
                            <th style="width: 20%">Progreso de Pago</th>
                            <th style="width: 15%">Saldo</th>
                            <th style="width: 10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                            @php
                                $total = $pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base;
                                $abonado = $pago->monto_abonado;
                                $pendiente = $total - $abonado;
                                $porcentaje = round(($abonado / $total) * 100);
                            @endphp
                            <tr class="pago-row">
                                <td class="align-middle"><strong>#{{ $pago->id }}</strong></td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" class="text-decoration-none">
                                        <strong>{{ substr($pago->inscripcion->cliente->nombres, 0, 15) }}</strong><br>
                                        <small class="text-muted">{{ substr($pago->inscripcion->cliente->apellido_paterno, 0, 20) }}</small>
                                    </a>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="badge badge-info">
                                        #{{ $pago->inscripcion->id }}
                                    </a>
                                </td>
                                <td class="align-middle">
                                    <span class="monto-cell" style="color: #0066cc; font-size: 1.05em;">
                                        ${{ number_format($total, 0, '.', '.') }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <div class="progress-custom" title="${{ number_format($abonado, 0, '.', '.') }} de ${{ number_format($total, 0, '.', '.') }}">
                                        <div class="progress-bar-custom" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                    <small style="color: #28a745; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i> ${{ number_format($abonado, 0, '.', '.') }} ({{ $porcentaje }}%)
                                    </small>
                                </td>
                                <td class="align-middle">
                                    @if($pendiente > 0)
                                        <span class="estado-badge saldo-pendiente">
                                            💰 ${{ number_format($pendiente, 0, '.', '.') }}
                                        </span>
                                    @else
                                        <span class="estado-badge saldo-pagado">
                                            ✅ Pagado
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-warning" title="Registrar abono">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                        <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar?')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No hay pagos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <nav aria-label="Page navigation">
                <div class="d-flex justify-content-center">
                    {{ $pagos->links('pagination::bootstrap-4') }}
                </div>
            </nav>
        </div>
    </div>
@stop
