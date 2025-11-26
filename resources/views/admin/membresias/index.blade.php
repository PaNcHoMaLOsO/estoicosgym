@extends('adminlte::page')

@section('title', 'Membresías - Configuración EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-cog"></i> Membresías</h1>
            <small class="text-muted">Gestión de planes y precios</small>
        </div>
        <div class="col-sm-6">
            <div class="btn-group float-right" role="group">
                <a href="{{ route('admin.membresias.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Membresía
                </a>
                <a href="{{ route('admin.membresias.inactivas') }}" class="btn btn-warning">
                    <i class="fas fa-ban"></i> Ver Desactivadas
                </a>
            </div>
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
                                                title="Desactivar o Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        
                                        <!-- Modal de Confirmación: Desactivar o Eliminar -->
                                        <div class="modal fade" id="deleteModal{{ $membresia->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-warning text-dark">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-cog"></i> Gestionar Membresía
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>¿Qué deseas hacer con esta membresía?</strong></p>
                                                        <div class="alert alert-info">
                                                            <strong>Membresía:</strong> {{ $membresia->nombre }}<br>
                                                            <strong>Inscripciones activas:</strong> 
                                                            @php
                                                                $activas = $membresia->inscripciones()
                                                                    ->whereNotIn('id_estado', [3, 5])
                                                                    ->count();
                                                            @endphp
                                                            {{ $activas }}
                                                        </div>
                                                        
                                                        @if ($activas > 0)
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <strong>Hay {{ $activas }} inscripción(es) activa(s).</strong><br>
                                                                <small>Se recomienda <strong>Desactivar</strong> para mantener las inscripciones existentes.</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                            <i class="fas fa-times"></i> Cancelar
                                                        </button>
                                                        
                                                        <form action="{{ route('admin.membresias.destroy', $membresia) }}" 
                                                              method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="force_delete" value="0">
                                                            <button type="submit" class="btn btn-warning">
                                                                <i class="fas fa-pause-circle"></i> Desactivar
                                                            </button>
                                                        </form>
                                                        
                                                        <form action="{{ route('admin.membresias.destroy', $membresia) }}" 
                                                              method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="force_delete" value="1">
                                                            <button type="submit" class="btn btn-danger" 
                                                                    onclick="return confirm('¿Estás SEGURO? Esta acción NO se puede deshacer.')">
                                                                <i class="fas fa-trash"></i> Eliminar Completamente
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
