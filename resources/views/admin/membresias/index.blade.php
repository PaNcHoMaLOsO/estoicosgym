@extends('adminlte::page')

@section('title', 'Membresías - Configuración EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-cog"></i> Membresías</h1>
            <small class="text-muted">Gestión de planes y precios</small>
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
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading">
                <i class="fas fa-check-circle"></i> ¡Éxito!
            </h5>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-triangle"></i> Error
            </h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Lista de Membresías
            </h3>
        </div>
        <div class="card-body">
            @if ($membresias->count())
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 8%">#</th>
                                <th style="width: 20%">Nombre</th>
                                <th style="width: 15%">Duración</th>
                                <th style="width: 15%">Precio Actual</th>
                                <th style="width: 12%">Inscripciones</th>
                                <th style="width: 12%">Estado</th>
                                <th style="width: 18%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($membresias as $membresia)
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary">{{ $membresia->id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $membresia->nombre }}</strong>
                                        @if ($membresia->duracion_meses == 0)
                                            <br><small class="text-muted">(Plan de corta duración)</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $membresia->duracion_dias }} días</span>
                                        @if ($membresia->duracion_meses > 0)
                                            <br><small class="text-muted">({{ $membresia->duracion_meses }}m)</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $precioActual = $membresia->precios
                                                ->where('activo', true)
                                                ->first() ?? $membresia->precios->last();
                                        @endphp
                                        @if ($precioActual)
                                            <strong>${{ number_format($precioActual->precio_normal, 0, '.', '.') }}</strong>
                                            @if ($precioActual->precio_convenio)
                                                <br><small class="text-success">con convenio: ${{ number_format($precioActual->precio_convenio, 0, '.', '.') }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $membresia->inscripciones_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($membresia->activo)
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Activo</span>
                                        @else
                                            <span class="badge badge-secondary"><i class="fas fa-times-circle"></i> Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.membresias.show', $membresia) }}" 
                                           class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.membresias.edit', $membresia) }}" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-toggle="modal" data-target="#deleteModal{{ $membresia->id }}"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        
                                        <!-- Modal de Confirmación de Eliminación -->
                                        <div class="modal fade" id="deleteModal{{ $membresia->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-trash-alt"></i> Eliminar Membresía
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>¿Estás seguro de que deseas eliminar esta membresía?</strong></p>
                                                        <div class="alert alert-warning">
                                                            <strong>Membresía:</strong> {{ $membresia->nombre }}<br>
                                                            <strong>Inscripciones asociadas:</strong> {{ $membresia->inscripciones_count ?? 0 }}
                                                        </div>
                                                        <p class="text-muted">
                                                            Esta acción <strong>no se puede deshacer</strong>. 
                                                            @if (($membresia->inscripciones_count ?? 0) > 0)
                                                                <span class="text-danger">
                                                                    <i class="fas fa-exclamation-circle"></i>
                                                                    Hay {{ $membresia->inscripciones_count }} inscripción(es) asociada(s).
                                                                </span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                            <i class="fas fa-times"></i> Cancelar
                                                        </button>
                                                        <form action="{{ route('admin.membresias.destroy', $membresia) }}" 
                                                              method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-trash"></i> Sí, Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($membresias->hasPages())
                    <nav aria-label="Page navigation">
                        <div class="d-flex justify-content-center mt-3">
                            {{ $membresias->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                @endif
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay membresías registradas. 
                    <a href="{{ route('admin.membresias.create') }}" class="alert-link">Crea una nueva</a>
                </div>
            @endif
        </div>
    </div>
@endsection
