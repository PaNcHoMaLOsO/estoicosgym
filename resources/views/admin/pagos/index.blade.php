@extends('adminlte::page')

@section('title', 'Pagos - EstóicosGym')

@section('css')
<style>
    /* Circular Progress Bar */
    .progress-circle {
        position: relative;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: conic-gradient(#28a745 0deg, #28a745 var(--progress), #e9ecef var(--progress), #e9ecef 360deg);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .progress-circle::after {
        content: '';
        position: absolute;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: white;
    }
    .progress-circle-text {
        position: relative;
        z-index: 1;
        font-weight: 700;
        font-size: 0.9em;
        color: #28a745;
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
    .estado-badge {
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 0.85em;
        font-weight: 600;
        display: inline-block;
    }
    .saldo-pendiente {
        background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
        color: #856404;
    }
    .saldo-pagado {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }
    .table thead th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8em;
        letter-spacing: 0.5px;
        border-bottom: 3px solid #667eea;
        color: #333;
    }
    .btn-group-sm .btn {
        padding: 4px 8px;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-credit-card"></i> Gestión de Pagos</h1>
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
                           placeholder="Buscar cliente..." value="{{ request('cliente') }}">
                </div>
                <div class="form-group mr-2">
                    <select id="metodo_pago" name="metodo_pago" class="form-control form-control-sm">
                        <option value="">Todos los métodos</option>
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
        <div class="card-header bg-gradient">
            <h3 class="card-title"><i class="fas fa-list-alt"></i> Historial de Pagos</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover m-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 22%">Cliente / Membresía</th>
                            <th style="width: 10%">Ref.</th>
                            <th style="width: 12%">Total</th>
                            <th style="width: 15%">Pagado</th>
                            <th style="width: 15%">% Progreso</th>
                            <th style="width: 12%">Estado</th>
                            <th style="width: 9%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                            @php
                                $total = $pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base;
                                $abonado = $pago->monto_abonado;
                                $pendiente = $total - $abonado;
                                $porcentaje = round(($abonado / $total) * 100);
                                $progressDeg = ($porcentaje / 100) * 360;
                            @endphp
                            <tr class="pago-row">
                                <td class="align-middle"><strong>#{{ $pago->id }}</strong></td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" class="text-decoration-none font-weight-bold">
                                        {{ substr($pago->inscripcion->cliente->nombres, 0, 12) }} {{ substr($pago->inscripcion->cliente->apellido_paterno, 0, 12) }}
                                    </a><br>
                                    <small class="text-muted">{{ substr($pago->inscripcion->membresia->nombre, 0, 20) }}</small>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="badge badge-primary">
                                        #{{ $pago->inscripcion->id }}
                                    </a>
                                </td>
                                <td class="align-middle">
                                    <span class="monto-cell" style="color: #667eea; font-size: 1.05em;">
                                        ${{ number_format($total, 0, '.', '.') }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <span style="color: #28a745; font-weight: 700;">
                                        ${{ number_format($abonado, 0, '.', '.') }}
                                    </span><br>
                                    <small style="color: #999;">
                                        Pendiente: ${{ number_format($pendiente, 0, '.', '.') }}
                                    </small>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="progress-circle" style="--progress: {{ $progressDeg }}deg;">
                                        <div class="progress-circle-text">{{ $porcentaje }}%</div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    @if($pendiente > 0)
                                        <span class="estado-badge saldo-pendiente">
                                            <i class="fas fa-hourglass-half"></i> Pendiente
                                        </span>
                                    @else
                                        <span class="estado-badge saldo-pagado">
                                            <i class="fas fa-check-circle"></i> Pagado
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-outline-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-outline-warning" title="Registrar abono">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar?')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.5;"></i><br>
                                    <strong>No hay pagos registrados</strong>
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

