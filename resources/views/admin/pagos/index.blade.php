@extends('adminlte::page')

@section('title', 'Pagos - EstóicosGym')

@section('css')
    <style>
        /* Reducir tamaño de flechas de paginación */
        .pagination {
            gap: 0;
        }
        .pagination svg {
            width: 12px !important;
            height: 12px !important;
            stroke-width: 2 !important;
        }
        .pagination a, .pagination span {
            font-size: 12px !important;
            padding: 0.375rem 0.5rem !important;
            line-height: 1.2 !important;
            min-height: 28px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .pagination .page-link {
            padding: 0.375rem 0.5rem !important;
        }
    </style>
@stop

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

    <div class="card">
        <!-- Filtros -->
        <div class="card card-outline card-info collapsed-card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros y Búsqueda</h3>
                <div class="card-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display:none;">
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
        
        <div class="card-header">
            <h3 class="card-title">Listado de Pagos</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
                                <input type="number" name="id_inscripcion" id="id_inscripcion" 
                                       class="form-control" placeholder="ID Inscripción" 
                                       value="{{ request('id_inscripcion') }}">
                            </div>
                            <div class="form-group mr-2">
                                <label for="id_metodo_pago" class="mr-2">Método Pago:</label>
                                <select name="id_metodo_pago" id="id_metodo_pago" class="form-control">
                                    <option value="">-- Todos --</option>
                                    @foreach($metodos_pago as $metodo)
                                        <option value="{{ $metodo->id }}" {{ request('id_metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                            {{ $metodo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabla de Pagos -->
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Inscripción</th>
                            <th>Monto Total</th>
                            <th>Fecha Pago</th>
                            <th>Monto Abonado</th>
                            <th>Saldo Pendiente</th>
                            <th>Estado</th>
                            <th>Método Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                            <tr>
                                <td>{{ $pago->id }}</td>
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
                                    <strong>${{ number_format($pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base, 2, ',', '.') }}</strong>
                                </td>
                                <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                <td>
                                    <span class="text-success"><strong>${{ number_format($pago->monto_abonado, 2, ',', '.') }}</strong></span>
                                </td>
                                <td>
                                    @php
                                        $monto_total = $pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base;
                                        $total_abonado = $pago->inscripcion->pagos()->where('id_estado', 102)->sum('monto_abonado');
                                        $pendiente = $monto_total - $total_abonado;
                                    @endphp
                                    @if($pendiente > 0)
                                        <span class="badge bg-danger">Pendiente: ${{ number_format($pendiente, 2, ',', '.') }}</span>
                                    @else
                                        <span class="badge bg-success">Pagado</span>
                                    @endif
                                </td>
                                <td>{!! \App\Helpers\EstadoHelper::badgeWithIcon($pago->estado) !!}</td>
                                <td>{{ $pago->metodoPago->nombre }}</td>
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
                                <td colspan="10" class="text-center text-muted">
                                    <i class="fas fa-inbox"></i> No hay pagos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="row mt-3">
        <div class="col-12">
            <nav aria-label="Page navigation">
                <div class="d-flex justify-content-center">
                    {{ $pagos->links() }}
                </div>
            </nav>
        </div>
    </div>
@stop
