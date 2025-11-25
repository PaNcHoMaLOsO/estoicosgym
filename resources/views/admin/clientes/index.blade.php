@extends('adminlte::page')

@section('title', 'Clientes - EstóicosGym')

@section('css')
    <style>
        .table-responsive { overflow-x: auto; }
        .btn-sm { margin: 0 2px; }
    </style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Gestión de Clientes</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nuevo Cliente
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Clientes</h3>
            <div class="card-tools">
                <input type="text" class="form-control form-control-sm" style="width: 200px" placeholder="Buscar...">
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>RUT</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Celular</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->id }}</td>
                            <td><strong>{{ $cliente->run_pasaporte }}</strong></td>
                            <td>
                                {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                                @if($cliente->apellido_materno)
                                    {{ $cliente->apellido_materno }}
                                @endif
                            </td>
                            <td>{{ $cliente->email }}</td>
                            <td>{{ $cliente->celular }}</td>
                            <td>
                                @if($cliente->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye fa-fw"></i>
                                </a>
                                <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit fa-fw"></i>
                                </a>
                                <form action="{{ route('admin.clientes.destroy', $cliente) }}" method="POST" style="display:inline;">
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
                            <td colspan="7" class="text-center text-muted">No hay clientes registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <div class="d-flex justify-content-center">
                    {{ $clientes->links() }}
                </div>
            </nav>
        </div>
    </div>
@stop

@section('js')
    <script>
        console.log('Página de Clientes cargada');
    </script>
@stop
