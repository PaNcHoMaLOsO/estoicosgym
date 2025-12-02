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

    /* ===== HERO HEADER ===== */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
    }

    .page-header h1 {
        color: white;
        margin: 0;
        font-weight: 700;
    }

    .page-header h1 i {
        color: var(--accent);
    }

    .page-header small {
        color: rgba(255,255,255,0.7);
    }

    /* ===== CARDS ESTADÍSTICAS ===== */
    .stat-card {
        border: 0;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        border-radius: 16px;
        background: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border-left: 5px solid var(--info);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }

    .stat-card.success { border-left-color: var(--success); }
    .stat-card.warning { border-left-color: var(--warning); }
    .stat-card.danger { border-left-color: var(--accent); }

    .stat-number {
        font-size: 2.5em;
        font-weight: 800;
        color: var(--info);
    }

    .stat-card.success .stat-number { color: var(--success); }
    .stat-card.warning .stat-number { color: var(--warning); }
    .stat-card.danger .stat-number { color: var(--accent); }

    .stat-label {
        color: var(--gray-600);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .stat-icon {
        position: absolute;
        right: 15px;
        top: 15px;
        font-size: 2.5rem;
        opacity: 0.1;
        color: var(--primary);
    }

    /* ===== BUSCADOR ===== */
    .search-box {
        background: white;
        border: none;
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    }

    .search-box input,
    .search-box select {
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .search-box input:focus,
    .search-box select:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
    }

    /* ===== TABLA ===== */
    .table-responsive {
        border-radius: 0 0 16px 16px;
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead {
        background: var(--primary);
    }

    .table thead th {
        color: white;
        font-weight: 600;
        padding: 1rem 0.75rem;
        border: 0;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--gray-200);
    }

    .table tbody tr:hover {
        background-color: var(--gray-100);
    }

    .table tbody td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
    }

    /* ===== MEMBERSHIP NAME ===== */
    .membership-name {
        font-weight: 700;
        color: var(--gray-800);
        font-size: 0.95rem;
    }

    .membership-type {
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    /* ===== STATUS BADGES ===== */
    .status-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-active {
        background: rgba(0, 191, 142, 0.15);
        color: var(--success);
        border: 2px solid var(--success);
    }

    .status-inactive {
        background: rgba(108, 117, 125, 0.15);
        color: var(--gray-600);
        border: 2px solid var(--gray-600);
    }

    /* ===== DURATION BADGES ===== */
    .duration-badge {
        display: inline-block;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(67, 97, 238, 0.15);
        color: var(--info);
    }

    /* ===== PRECIOS ===== */
    .precio-normal {
        color: var(--success);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .precio-convenio {
        font-size: 0.8rem;
        color: var(--info);
    }

    /* ===== ACCIONES ===== */
    .action-buttons {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .btn-action {
        padding: 0.4rem 0.65rem;
        font-size: 0.8rem;
        border-radius: 8px;
        border: 0;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        white-space: nowrap;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-view {
        background: var(--primary);
        color: white;
    }

    .btn-edit {
        background: var(--warning);
        color: white;
    }

    .btn-delete {
        background: var(--accent);
        color: white;
    }

    .btn-reactivate {
        background: var(--success);
        color: white;
    }

    /* ===== CARD HEADER ===== */
    .card-header {
        background: var(--primary);
        color: white;
        border-radius: 16px 16px 0 0 !important;
        border-bottom: none;
    }

    .card-header .card-title {
        color: white;
    }

    .card {
        border: 0;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        border-radius: 16px;
    }

    /* ===== BUTTON NEW ===== */
    .btn-new-membresia {
        background: var(--success);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-new-membresia:hover {
        background: var(--success-dark);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 191, 142, 0.3);
    }

    /* ===== ALERT STYLING ===== */
    .alert {
        border-radius: 12px;
        border: none;
    }

    .alert-success-custom {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
        border-left: 5px solid #007a5e;
    }

    .alert-danger-custom {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%);
        color: white;
        border-left: 5px solid #c0392b;
    }

    .alert-info-custom {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
        color: white;
        border-left: 5px solid #2942b8;
    }

    .alert-info-custom a {
        color: white;
        text-decoration: underline;
    }

    /* ===== INSCRIPCIONES BADGE ===== */
    .inscripciones-badge {
        background: rgba(233, 69, 96, 0.15);
        color: var(--accent);
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.85rem;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--gray-200);
        margin-bottom: 1rem;
    }

    .empty-state h4 {
        color: var(--gray-600);
        margin-bottom: 1rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .table thead th,
        .table tbody td {
            padding: 0.65rem 0.5rem;
            font-size: 0.85rem;
        }

        .stat-number {
            font-size: 1.8em;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.8rem;
        }

        .btn-action {
            padding: 0.3rem 0.5rem;
            font-size: 0.75rem;
        }

        .page-header {
            padding: 15px 20px;
        }
    }
</style>
@stop

@section('content_header')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1>
                    <i class="fas fa-credit-card"></i> Gestión de Membresías
                </h1>
                <small>Administra los planes y precios del gimnasio</small>
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('admin.membresias.create') }}" class="btn btn-new-membresia">
                    <i class="fas fa-plus"></i> Nueva Membresía
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success-custom alert-dismissible fade show shadow-lg" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading mb-2">
                <i class="fas fa-check-circle"></i> ¡Éxito!
            </h5>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger-custom alert-dismissible fade show shadow-lg" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading mb-2">
                <i class="fas fa-exclamation-triangle"></i> Error
            </h5>
            {{ session('error') }}
        </div>
    @endif

    @php
        // Calcular estadísticas
        $totalMembresias = $membresias->total();
        $activas = $membresias->filter(fn($m) => $m->activo)->count();
        $inactivas = $membresias->filter(fn($m) => !$m->activo)->count();
        $totalInscripciones = $membresias->sum('inscripciones_count');
    @endphp

    <!-- ESTADÍSTICAS RÁPIDAS -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card stat-icon"></i>
                    <div class="stat-number">{{ $totalMembresias }}</div>
                    <div class="stat-label">Total Membresías</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle stat-icon"></i>
                    <div class="stat-number">{{ $activas }}</div>
                    <div class="stat-label">Activas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body text-center">
                    <i class="fas fa-pause-circle stat-icon"></i>
                    <div class="stat-number">{{ $inactivas }}</div>
                    <div class="stat-label">Inactivas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card danger">
                <div class="card-body text-center">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="stat-number">{{ $totalInscripciones }}</div>
                    <div class="stat-label">Inscripciones</div>
                </div>
            </div>
        </div>
    </div>

    <!-- BUSCADOR -->
    <div class="search-box">
        <div class="row">
            <div class="col-md-8">
                <input type="text" class="form-control form-control-lg" id="searchInput" placeholder="🔍 Buscar por nombre de membresía...">
            </div>
            <div class="col-md-4">
                <select class="form-control form-control-lg" id="filterEstado">
                    <option value="">-- Todos los Estados --</option>
                    <option value="activo">Activas</option>
                    <option value="inactivo">Inactivas</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TABLA DE MEMBRESÍAS -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Listado de Membresías
            </h3>
            <div class="card-tools">
                <span class="badge badge-light" id="resultCount">{{ $membresias->count() }} de {{ $membresias->total() }}</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            @if ($membresias->count())
                <table class="table table-hover" id="membresiaTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 22%;">Nombre</th>
                            <th style="width: 12%;">Duración</th>
                            <th style="width: 18%;">Precio</th>
                            <th style="width: 12%;">Inscripciones</th>
                            <th style="width: 12%;">Estado</th>
                            <th style="width: 19%; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($membresias as $membresia)
                            @php
                                $precioActual = $membresia->precios
                                    ->where('activo', true)
                                    ->first() ?? $membresia->precios->last();
                            @endphp
                            <tr data-estado="{{ $membresia->activo ? 'activo' : 'inactivo' }}" 
                                data-nombre="{{ strtolower($membresia->nombre) }}">
                                <td>
                                    <span class="badge badge-secondary">#{{ $membresia->id }}</span>
                                </td>
                                <td>
                                    <div class="membership-name">{{ $membresia->nombre }}</div>
                                    @if ($membresia->duracion_meses == 0)
                                        <div class="membership-type">
                                            <i class="fas fa-bolt"></i> Plan de corta duración
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="duration-badge">
                                        <i class="fas fa-calendar-alt"></i> {{ $membresia->duracion_dias }} días
                                    </span>
                                    @if ($membresia->duracion_meses > 0)
                                        <div class="membership-type mt-1">
                                            ({{ $membresia->duracion_meses }} {{ $membresia->duracion_meses == 1 ? 'mes' : 'meses' }})
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($precioActual)
                                        <div class="precio-normal">
                                            ${{ number_format($precioActual->precio_normal, 0, ',', '.') }}
                                        </div>
                                        @if ($precioActual->precio_convenio)
                                            <div class="precio-convenio">
                                                <i class="fas fa-handshake"></i> ${{ number_format($precioActual->precio_convenio, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin precio</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="inscripciones-badge">
                                        <i class="fas fa-users"></i> {{ $membresia->inscripciones_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    @if ($membresia->activo)
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle"></i> Activa
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="fas fa-pause-circle"></i> Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.membresias.show', $membresia) }}" 
                                           class="btn-action btn-view" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.membresias.edit', $membresia) }}" 
                                           class="btn-action btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if (!$membresia->activo)
                                            <button type="button" class="btn-action btn-reactivate" 
                                                    title="Reactivar membresía"
                                                    onclick="confirmarReactivar({{ $membresia->id }}, '{{ $membresia->nombre }}')">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <form id="formReactivar{{ $membresia->id }}" 
                                                  action="{{ route('admin.membresias.update', $membresia) }}" 
                                                  method="POST" style="display:none;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="activo" value="1">
                                                <input type="hidden" name="nombre" value="{{ $membresia->nombre }}">
                                                <input type="hidden" name="duracion_meses" value="{{ $membresia->duracion_meses }}">
                                                <input type="hidden" name="duracion_dias" value="{{ $membresia->duracion_dias }}">
                                                @if ($precioActual)
                                                    <input type="hidden" name="precio_normal" value="{{ $precioActual->precio_normal }}">
                                                    @if ($precioActual->precio_convenio)
                                                        <input type="hidden" name="precio_convenio" value="{{ $precioActual->precio_convenio }}">
                                                    @endif
                                                @endif
                                            </form>
                                        @else
                                            <button type="button" class="btn-action btn-delete" 
                                                    title="Desactivar o Eliminar"
                                                    onclick="confirmarEliminar({{ $membresia->id }}, '{{ $membresia->nombre }}', {{ $membresia->inscripciones_count ?? 0 }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="formDesactivar{{ $membresia->id }}" 
                                                  action="{{ route('admin.membresias.destroy', $membresia) }}" 
                                                  method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="force_delete" value="0">
                                            </form>
                                            <form id="formEliminar{{ $membresia->id }}" 
                                                  action="{{ route('admin.membresias.destroy', $membresia) }}" 
                                                  method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="force_delete" value="1">
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <i class="fas fa-credit-card"></i>
                    <h4>No hay membresías registradas</h4>
                    <p class="text-muted">Crea tu primera membresía para comenzar</p>
                    <a href="{{ route('admin.membresias.create') }}" class="btn btn-new-membresia">
                        <i class="fas fa-plus"></i> Crear Membresía
                    </a>
                </div>
            @endif
        </div>

        @if ($membresias->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $membresias->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterEstado = document.getElementById('filterEstado');
    const table = document.getElementById('membresiaTable');
    const resultCount = document.getElementById('resultCount');

    function filtrarTabla() {
        if (!table) return;
        
        const searchTerm = searchInput.value.toLowerCase();
        const estadoFiltro = filterEstado.value;
        const rows = table.querySelectorAll('tbody tr');
        let visibles = 0;

        rows.forEach(row => {
            const nombre = row.dataset.nombre || '';
            const estado = row.dataset.estado || '';
            
            const coincideNombre = nombre.includes(searchTerm);
            const coincideEstado = !estadoFiltro || estado === estadoFiltro;
            
            if (coincideNombre && coincideEstado) {
                row.style.display = '';
                visibles++;
            } else {
                row.style.display = 'none';
            }
        });

        resultCount.textContent = `${visibles} de ${rows.length}`;
    }

    if (searchInput) searchInput.addEventListener('input', filtrarTabla);
    if (filterEstado) filterEstado.addEventListener('change', filtrarTabla);
});

function confirmarReactivar(id, nombre) {
    Swal.fire({
        title: '¿Reactivar membresía?',
        html: `
            <p>Vas a reactivar la membresía:</p>
            <strong class="text-primary">${nombre}</strong>
            <p class="mt-3 text-muted">Los clientes podrán contratarla nuevamente.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00bf8e',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-redo"></i> Sí, reactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formReactivar' + id).submit();
        }
    });
}

function confirmarEliminar(id, nombre, inscripciones) {
    let mensaje = `
        <p>¿Qué deseas hacer con la membresía?</p>
        <strong class="text-danger">${nombre}</strong>
    `;
    
    if (inscripciones > 0) {
        mensaje += `
            <div class="alert alert-warning mt-3 text-left" style="font-size: 0.9rem;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Atención:</strong> Esta membresía tiene <strong>${inscripciones}</strong> inscripción(es).
                <br>Se recomienda <strong>Desactivar</strong> para mantener el historial.
            </div>
        `;
    }

    Swal.fire({
        title: 'Gestionar Membresía',
        html: mensaje,
        icon: 'warning',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonColor: '#f0a500',
        denyButtonColor: '#e94560',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-pause-circle"></i> Desactivar',
        denyButtonText: '<i class="fas fa-trash"></i> Eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Desactivar
            document.getElementById('formDesactivar' + id).submit();
        } else if (result.isDenied) {
            // Confirmar eliminación permanente
            Swal.fire({
                title: '¿Eliminar permanentemente?',
                html: `
                    <p class="text-danger"><strong>⚠️ Esta acción NO se puede deshacer</strong></p>
                    <p>Se eliminarán todos los datos asociados a esta membresía.</p>
                `,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#e94560',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result2) => {
                if (result2.isConfirmed) {
                    document.getElementById('formEliminar' + id).submit();
                }
            });
        }
    });
}
</script>
@endsection
