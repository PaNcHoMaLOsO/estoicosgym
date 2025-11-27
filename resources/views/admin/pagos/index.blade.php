@extends('adminlte::page')

@section('title', 'Pagos - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Gestión de Pagos</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('admin.pagos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Pago
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card card-outline card-info mb-3 collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros y Búsqueda</h3>
            <div class="card-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <form action="{{ route('admin.pagos.index') }}" method="GET" class="form-horizontal">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cliente">Cliente:</label>
                            <input type="text" id="cliente" name="cliente" class="form-control" 
                                   placeholder="Nombre o apellido..." value="{{ request('cliente') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="metodo_pago">Método de Pago:</label>
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary btn-block mt-2">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabla de Pagos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Pagos</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th style="width: 25%">Cliente</th>
                        <th style="width: 15%">Inscripción</th>
                        <th style="width: 15%">Monto Total</th>
                        <th style="width: 20%">Abonado</th>
                        <th style="width: 15%">Saldo Pendiente</th>
                        <th>Estado</th>
                        <th>Método</th>
                        <th style="width: 12%">Acciones</th>
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
                        <tr>
                            <td><strong>#{{ $pago->id }}</strong></td>
                            <td>
                                <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}">
                                    {{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}">
                                    #{{ $pago->inscripcion->id }}
                                </a>
                            </td>
                            <td>
                                <span style="font-size: 1.1em;">${{ number_format($total, 0, '.', '.') }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong style="color: #28a745;">
                                        ${{ number_format($abonado, 0, '.', '.') }}
                                    </strong>
                                </div>
                                <small style="color: #28a745; font-size: 0.85em;">
                                    <i class="fas fa-check-circle"></i> {{ $porcentaje }}%
                                </small>
                            </td>
                            <td>
                                @if($pendiente > 0)
                                    <span class="text-danger"><strong>${{ number_format($pendiente, 0, '.', '.') }}</strong></span>
                                @else
                                    <span class="text-success"><strong>Completo</strong></span>
                                @endif
                            </td>
                            <td>{!! $pago->estado->badge !!}</td>
                            <td>{{ $pago->metodoPagoPrincipal?->nombre }}</td>
                            <td>
                                <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye fa-fw"></i>
                                </a>
                                <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit fa-fw"></i>
                                </a>
                                <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Eliminar este pago?')" title="Eliminar">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fas fa-inbox"></i> No hay pagos registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <div class="d-flex justify-content-center">
                    {{ $pagos->links('pagination::bootstrap-4') }}
                </div>
            </nav>
        </div>
    </div>
@stop
