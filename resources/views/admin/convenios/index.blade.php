@extends('adminlte::page')

@section('title', 'Convenios - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Convenios</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.convenios.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nuevo Convenio
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
            <h3 class="card-title">Listado de Convenios</h3>
        </div>
        <div class="card-body table-responsive p-0">
            @if($convenios->count() > 0)
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($convenios as $convenio)
                            <tr>
                                <td>{{ $convenio->id }}</td>
                                <td><strong>{{ $convenio->nombre }}</strong></td>
                                <td>
                                    @php
                                        $tipos = [
                                            'institucion_educativa' => 'Institución Educativa',
                                            'empresa' => 'Empresa',
                                            'organizacion' => 'Organización',
                                            'otro' => 'Otro'
                                        ];
                                    @endphp
                                    <span class="badge bg-info">{{ $tipos[$convenio->tipo] ?? $convenio->tipo }}</span>
                                </td>
                                <td>
                                    @if($convenio->contacto_nombre)
                                        {{ $convenio->contacto_nombre }}<br>
                                        <small>{{ $convenio->contacto_telefono ?? '' }}</small>
                                    @else
                                        <em>-</em>
                                    @endif
                                </td>
                                <td>
                                    @if($convenio->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.convenios.show', $convenio) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.convenios.edit', $convenio) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.convenios.destroy', $convenio) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    {{ $convenios->links() }}
                </div>
            @else
                <p class="text-center text-muted p-3">No hay convenios registrados</p>
            @endif
        </div>
    </div>
@stop
