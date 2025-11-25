@extends('adminlte::page')

@section('title', 'Motivos de Descuento - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Motivos de Descuento</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.motivos-descuento.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nuevo Motivo
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
            <h3 class="card-title">Listado de Motivos de Descuento</h3>
        </div>
        <div class="card-body table-responsive p-0">
            @if($motivos->count() > 0)
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($motivos as $motivo)
                            <tr>
                                <td>{{ $motivo->id }}</td>
                                <td><strong>{{ $motivo->nombre }}</strong></td>
                                <td>{{ Str::limit($motivo->descripcion, 50) }}</td>
                                <td>
                                    @if($motivo->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.motivos-descuento.show', $motivo) }}" class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye fa-fw"></i>
                                    </a>
                                    <a href="{{ route('admin.motivos-descuento.edit', $motivo) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit fa-fw"></i>
                                    </a>
                                    <form action="{{ route('admin.motivos-descuento.destroy', $motivo) }}" method="POST" style="display:inline;">
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
                    {{ $motivos->links() }}
                </div>
            @else
                <p class="text-center text-muted p-3">No hay motivos de descuento registrados</p>
            @endif
        </div>
    </div>
@stop
