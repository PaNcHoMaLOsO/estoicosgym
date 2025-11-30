@extends('adminlte::page')

@section('title', 'Membresías - Configuración EstóicosGym')

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --accent-light: #ff6b6b;
        --success: #00bf8e;
        --success-dark: #00a67d;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
    }

    /* ===== CARD STYLING ===== */
    .card-primary .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    }

    .card-header h3 {
        color: white;
    }

    /* ===== TABLE STYLING ===== */
    .table thead th {
        background: var(--gray-100);
        border-bottom: 2px solid var(--accent);
        font-weight: 700;
        color: var(--primary);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(233, 69, 96, 0.05);
    }

    /* ===== BUTTONS ===== */
    .btn {
        transition: all 0.3s ease;
        font-weight: 600;
        border-radius: 8px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-primary {
        background: var(--accent);
        border-color: var(--accent);
    }

    .btn-primary:hover {
        background: var(--accent-light);
        border-color: var(--accent-light);
    }

    /* ===== BADGES ===== */
    .badge-success {
        background: var(--success);
    }

    .badge-info {
        background: var(--info);
    }

    .badge-primary {
        background: var(--accent);
    }

    /* ===== ALERT STYLING ===== */
    .alert {
        border-radius: 12px;
        border: none;
    }

    .alert-success {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
    }

    .alert-success .close {
        color: white;
    }

    .alert-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%);
        color: white;
    }

    .alert-info {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
        color: white;
    }

    .alert-info a {
        color: white;
        text-decoration: underline;
    }

    /* ===== HELPER CLASSES ===== */
    .text-accent {
        color: var(--accent) !important;
    }

    /* ===== PRECIO CELL ===== */
    .precio-cell {
        font-weight: 700;
        color: var(--success);
    }

    .precio-convenio {
        font-size: 0.85rem;
        color: var(--info);
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-credit-card text-accent"></i> Membresías</h1>
            <small class="text-muted">Gestión de planes y precios del gimnasio</small>
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
                                            <span class="precio-cell">${{ number_format($precioActual->precio_normal, 0, ',', '.') }}</span>
                                            @if ($precioActual->precio_convenio)
                                                <br><small class="precio-convenio">
                                                    <i class="fas fa-handshake"></i> ${{ number_format($precioActual->precio_convenio, 0, ',', '.') }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">Sin precio</span>
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
                                        @if (!$membresia->activo)
                                            <!-- Botón Reactivar si está desactivada -->
                                            <form action="{{ route('admin.membresias.update', $membresia) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="activo" value="1">
                                                <input type="hidden" name="nombre" value="{{ $membresia->nombre }}">
                                                <input type="hidden" name="duracion_meses" value="{{ $membresia->duracion_meses }}">
                                                <input type="hidden" name="duracion_dias" value="{{ $membresia->duracion_dias }}">
                                                @php
                                                    $precioActual = $membresia->precios()->where('activo', true)->first() ?? $membresia->precios->last();
                                                @endphp
                                                @if ($precioActual)
                                                    <input type="hidden" name="precio_normal" value="{{ $precioActual->precio_normal }}">
                                                    @if ($precioActual->precio_convenio)
                                                        <input type="hidden" name="precio_convenio" value="{{ $precioActual->precio_convenio }}">
                                                    @endif
                                                @endif
                                                <button type="submit" class="btn btn-sm btn-success" title="Reactivar membresía">
                                                    <i class="fas fa-redo"></i> Reactivar
                                                </button>
                                            </form>
                                        @else
                                            <!-- Botón Eliminar/Desactivar si está activa -->
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-toggle="modal" data-target="#deleteModal{{ $membresia->id }}"
                                                    title="Desactivar o Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Modal de Confirmación: Desactivar o Eliminar (solo para activas) -->
                                        @if ($membresia->activo)
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
                                                                $estadoCancelada = \App\Models\Estado::where('codigo', 103)->first();
                                                                $estadoVencida = \App\Models\Estado::where('codigo', 102)->first();
                                                                $activas = $membresia->inscripciones()
                                                                    ->whereNotIn('id_estado', [$estadoCancelada?->id, $estadoVencida?->id])
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
                                        @endif
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
