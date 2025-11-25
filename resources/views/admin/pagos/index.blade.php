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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Pagos</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="card card-outline card-info collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <a data-toggle="collapse" href="#filtros">
                            <i class="fas fa-filter"></i> Filtros
                        </a>
                    </h3>
                </div>
                <div id="filtros" class="collapse">
                    <div class="card-body">
                        <form action="{{ route('admin.pagos.index') }}" method="GET" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="id_inscripcion" class="mr-2">Inscripción:</label>
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
                            <th>Fecha Pago</th>
                            <th>Monto Abonado</th>
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
                                <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                <td><strong>${{ number_format($pago->monto_abonado, 2, ',', '.') }}</strong></td>
                                <td>{!! \App\Helpers\EstadoHelper::badgeWithIcon($pago->estado) !!}</td>
                                <td>{{ $pago->metodoPago->nombre }}</td>
                                <td>
                                    <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('¿Eliminar este pago?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
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
            {{ $pagos->links() }}
        </div>
    </div>
@stop
