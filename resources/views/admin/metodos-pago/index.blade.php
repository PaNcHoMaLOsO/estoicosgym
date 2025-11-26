@extends('adminlte::page')

@section('title', 'Métodos de Pago - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Métodos de Pago</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.metodos-pago.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nuevo Método
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
            <h3 class="card-title">Listado de Métodos de Pago</h3>
        </div>
        <div class="card-body table-responsive p-0">
            @if($metodos->count() > 0)
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Requiere Ref.</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metodos as $metodo)
                            <tr>
                                <td>{{ $metodo->id }}</td>
                                <td><strong>{{ $metodo->nombre }}</strong></td>
                                <td>{{ Str::limit($metodo->descripcion, 50) }}</td>
                                <td>
                                    @if($metodo->requiere_referencia)
                                        <span class="badge bg-warning">Sí</span>
                                    @else
                                        <span class="badge bg-info">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($metodo->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.metodos-pago.show', $metodo) }}" class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye fa-fw"></i>
                                    </a>
                                    <a href="{{ route('admin.metodos-pago.edit', $metodo) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit fa-fw"></i>
                                    </a>
                                    <form action="{{ route('admin.metodos-pago.destroy', $metodo) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')" title="Eliminar">
                                            <i class="fas fa-trash fa-fw"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    {{ $metodos->links('pagination::bootstrap-4') }}
                </div>
            @else
                <p class="text-center text-muted p-3">No hay métodos de pago registrados</p>
            @endif
        </div>
    </div>
@stop
