@extends('adminlte::page')

@section('title', 'Membresias')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Membresias</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.membresias.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nueva Membresía
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Éxito!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Membresias</h3>
        </div>
        <div class="card-body">
            @if ($membresias->count())
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Nombre</th>
                            <th>Duración</th>
                            <th>Precio Actual</th>
                            <th>Inscripciones</th>
                            <th>Estado</th>
                            <th style="width: 200px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($membresias as $membresia)
                            <tr>
                                <td>{{ $membresia->id }}</td>
                                <td>{{ $membresia->nombre }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $membresia->duracion_dias }} días</span>
                                </td>
                                <td>
                                    @php
                                        $precioActual = $membresia->precios
                                            ->where('activo', true)
                                            ->first() ?? $membresia->precios->last();
                                    @endphp
                                    @if ($precioActual)
                                        ${{ number_format($precioActual->precio_normal, 2) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $membresia->inscripciones_count ?? 0 }}</span>
                                </td>
                                <td>
                                    @if ($membresia->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.membresias.show', $membresia) }}" 
                                       class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye fa-fw"></i>
                                    </a>
                                    <a href="{{ route('admin.membresias.edit', $membresia) }}" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit fa-fw"></i>
                                    </a>
                                    <form action="{{ route('admin.membresias.destroy', $membresia) }}" 
                                          method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('¿Estás seguro? Se eliminarán todos los registros asociados.')" 
                                                title="Eliminar">
                                            <i class="fas fa-trash fa-fw"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <div class="d-flex justify-content-center mt-3">
                        {{ $membresias->links() }}
                    </div>
                </nav>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay membresias registradas
                </div>
            @endif
        </div>
    </div>
@endsection
