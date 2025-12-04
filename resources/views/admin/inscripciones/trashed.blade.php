@extends('adminlte::page')

@section('title', 'Papelera de Inscripciones')

@section('content_header')
@stop

@section('content')
<div class="inscripciones-papelera-container">
    <!-- Hero Header -->
    <div class="inscripciones-hero papelera">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-trash-alt"></i>
            </div>
            <div class="hero-text">
                <h1>Papelera de Inscripciones</h1>
                <p>Inscripciones eliminadas que pueden ser restauradas</p>
            </div>
        </div>
        <div class="hero-actions">
            <a href="{{ route('admin.inscripciones.index') }}" class="btn-volver">
                <i class="fas fa-arrow-left"></i>
                <span>Volver a Inscripciones</span>
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if ($message = Session::get('success'))
        <div class="alert-custom success">
            <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
            <div class="alert-content">
                <strong>¡Éxito!</strong>
                <p>{{ $message }}</p>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert-custom error">
            <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="alert-content">
                <strong>Error</strong>
                <p>{{ $message }}</p>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card stat-eliminados">
            <div class="stat-icon"><i class="fas fa-trash"></i></div>
            <div class="stat-info">
                <span class="stat-number">{{ $totalEliminadas }}</span>
                <span class="stat-label">En Papelera</span>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card-tabla">
        <div class="table-responsive">
            <table class="tabla-inscripciones">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Membresía</th>
                        <th>Estado Original</th>
                        <th>Eliminada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inscripciones as $inscripcion)
                    <tr class="inscripcion-row eliminada">
                        <td>
                            <div class="cliente-info">
                                <div class="cliente-avatar eliminado">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="cliente-datos">
                                    <span class="cliente-nombre">
                                        {{ $inscripcion->cliente->nombres ?? 'Sin cliente' }} 
                                        {{ $inscripcion->cliente->apellido_paterno ?? '' }}
                                    </span>
                                    <span class="cliente-rut">{{ $inscripcion->cliente->rut ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="membresia-badge">
                                <i class="fas fa-dumbbell"></i>
                                {{ $inscripcion->membresia->nombre ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="estado-badge estado-{{ strtolower($inscripcion->estado->nombre ?? 'desconocido') }}">
                                {{ $inscripcion->estado->nombre ?? 'Desconocido' }}
                            </span>
                        </td>
                        <td>
                            <div class="fecha-eliminacion">
                                <i class="fas fa-calendar-times"></i>
                                {{ $inscripcion->deleted_at->format('d/m/Y H:i') }}
                                <span class="tiempo-eliminado">
                                    (hace {{ $inscripcion->deleted_at->diffForHumans(null, true) }})
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="acciones-btns">
                                <form action="{{ route('admin.inscripciones.restore', $inscripcion->id) }}" 
                                      method="POST" class="d-inline form-restaurar">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-action btn-restore" 
                                            data-tooltip="Restaurar"
                                            data-nombre="{{ $inscripcion->cliente->nombres ?? 'Cliente' }}">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.inscripciones.force-delete', $inscripcion->id) }}" 
                                      method="POST" class="d-inline form-eliminar-permanente">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-danger-permanent" 
                                            data-tooltip="Eliminar Permanentemente"
                                            data-nombre="{{ $inscripcion->cliente->nombres ?? 'Cliente' }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <div class="empty-content">
                                <i class="fas fa-trash-restore"></i>
                                <h3>Papelera vacía</h3>
                                <p>No hay inscripciones eliminadas</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inscripciones->hasPages())
        <div class="pagination-section">
            {{ $inscripciones->links() }}
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #4361ee;
        --danger: #ef4444;
        --success: #10b981;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #475569;
        --gray-800: #1e293b;
        --papelera-light: rgba(239, 68, 68, 0.1);
    }

    .inscripciones-papelera-container {
        padding: 1.5rem;
        background: var(--gray-50);
        min-height: calc(100vh - 60px);
    }

    .inscripciones-hero.papelera {
        background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);
        border-radius: 20px;
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(239, 68, 68, 0.3);
    }

    .hero-content {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .hero-icon {
        width: 70px;
        height: 70px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-icon i {
        font-size: 2rem;
        color: white;
    }

    .hero-text h1 {
        color: white;
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .hero-text p {
        color: rgba(255, 255, 255, 0.8);
        margin: 0.25rem 0 0;
    }

    .btn-volver {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-volver:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateX(-5px);
    }

    .alert-custom {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .alert-custom.success {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .alert-custom.success .alert-icon { color: var(--success); }

    .alert-custom.error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .alert-custom.error .alert-icon { color: var(--danger); }

    .alert-icon { font-size: 1.5rem; }

    .alert-content strong {
        display: block;
        color: var(--gray-800);
    }

    .alert-content p {
        margin: 0;
        color: var(--gray-600);
    }

    .alert-close {
        margin-left: auto;
        background: none;
        border: none;
        color: var(--gray-400);
        cursor: pointer;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .stat-card.stat-eliminados {
        border-left: 4px solid var(--danger);
    }

    .stat-card.stat-eliminados .stat-icon {
        background: var(--papelera-light);
        color: var(--danger);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--gray-800);
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--gray-500);
    }

    .card-tabla {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .tabla-inscripciones {
        width: 100%;
        border-collapse: collapse;
    }

    .tabla-inscripciones thead {
        background: var(--gray-50);
    }

    .tabla-inscripciones th {
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: var(--gray-600);
        font-size: 0.85rem;
        text-transform: uppercase;
        border-bottom: 2px solid var(--gray-100);
    }

    .tabla-inscripciones td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
    }

    .inscripcion-row.eliminada {
        background: rgba(239, 68, 68, 0.03);
    }

    .cliente-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .cliente-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cliente-avatar.eliminado {
        background: var(--papelera-light);
        color: var(--danger);
    }

    .cliente-datos {
        display: flex;
        flex-direction: column;
    }

    .cliente-nombre {
        font-weight: 600;
        color: var(--gray-800);
    }

    .cliente-rut {
        font-size: 0.85rem;
        color: var(--gray-500);
    }

    .membresia-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.75rem;
        background: rgba(67, 97, 238, 0.1);
        color: var(--primary);
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .estado-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .estado-badge.estado-activa {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .estado-badge.estado-vencida {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .estado-badge.estado-pausada {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }

    .estado-badge.estado-cancelada {
        background: rgba(100, 116, 139, 0.1);
        color: var(--gray-600);
    }

    .fecha-eliminacion {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        font-size: 0.9rem;
        color: var(--gray-600);
    }

    .fecha-eliminacion i {
        color: var(--danger);
        margin-right: 0.5rem;
    }

    .tiempo-eliminado {
        font-size: 0.8rem;
        color: var(--gray-400);
    }

    .acciones-btns {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .btn-action[data-tooltip]:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.5rem 0.75rem;
        background: var(--gray-800);
        color: white;
        font-size: 0.75rem;
        border-radius: 6px;
        white-space: nowrap;
        margin-bottom: 5px;
    }

    .btn-restore {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .btn-restore:hover {
        background: var(--success);
        color: white;
    }

    .btn-danger-permanent {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .btn-danger-permanent:hover {
        background: var(--danger);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem !important;
    }

    .empty-content i {
        font-size: 4rem;
        color: var(--gray-400);
        margin-bottom: 1rem;
    }

    .empty-content h3 {
        color: var(--gray-600);
    }

    .empty-content p {
        color: var(--gray-400);
    }

    .pagination-section {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--gray-100);
    }

    @media (max-width: 768px) {
        .inscripciones-hero.papelera {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .hero-content {
            flex-direction: column;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.form-restaurar').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const nombre = $(this).find('button').data('nombre');

        Swal.fire({
            title: '<i class="fas fa-undo" style="color: #10b981;"></i> Restaurar Inscripción',
            html: `<p>¿Deseas restaurar la inscripción de <strong>${nombre}</strong>?</p>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-undo"></i> Sí, Restaurar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });

    $('.form-eliminar-permanente').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const nombre = $(this).find('button').data('nombre');

        Swal.fire({
            title: '<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> ¡Advertencia!',
            html: `<p>Eliminar <strong>permanentemente</strong> inscripción de:</p>
                   <p style="font-size: 1.1rem; font-weight: 600;">${nombre}</p>
                   <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                       <p style="color: #dc2626; margin: 0;"><i class="fas fa-ban"></i> Esta acción NO se puede deshacer</p>
                   </div>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@stop
