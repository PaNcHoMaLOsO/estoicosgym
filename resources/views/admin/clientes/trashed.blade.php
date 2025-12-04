@extends('adminlte::page')

@section('title', 'Papelera de Clientes')

@section('content_header')
@stop

@section('content')
<div class="clientes-papelera-container">
    <!-- Hero Header -->
    <div class="clientes-hero papelera">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-trash-alt"></i>
            </div>
            <div class="hero-text">
                <h1>Papelera de Clientes</h1>
                <p>Clientes eliminados que pueden ser restaurados</p>
            </div>
        </div>
        <div class="hero-actions">
            <a href="{{ route('admin.clientes.index') }}" class="btn-volver">
                <i class="fas fa-arrow-left"></i>
                <span>Volver a Clientes</span>
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if ($message = Session::get('success'))
        <div class="alert-custom success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
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
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <strong>Error</strong>
                <p>{{ $message }}</p>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-eliminados">
            <div class="stat-icon">
                <i class="fas fa-trash"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $totalEliminados }}</span>
                <span class="stat-label">En Papelera</span>
            </div>
        </div>
    </div>

    <!-- Tabla de Clientes Eliminados -->
    <div class="card-tabla">
        <div class="table-responsive">
            <table class="tabla-clientes">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>RUT</th>
                        <th>Eliminado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                    <tr class="cliente-row eliminado">
                        <td>
                            <div class="cliente-info">
                                <div class="cliente-avatar eliminado">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <div class="cliente-datos">
                                    <span class="cliente-nombre">
                                        {{ $cliente->nombres }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}
                                    </span>
                                    <span class="cliente-email">{{ $cliente->email ?? 'Sin email' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="rut-badge">{{ $cliente->rut }}</span>
                        </td>
                        <td>
                            <div class="fecha-eliminacion">
                                <i class="fas fa-calendar-times"></i>
                                {{ $cliente->deleted_at->format('d/m/Y H:i') }}
                                <span class="tiempo-eliminado">
                                    (hace {{ $cliente->deleted_at->diffForHumans(null, true) }})
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="acciones-btns">
                                <!-- Restaurar -->
                                <form action="{{ route('admin.clientes.restore', $cliente->id) }}" 
                                      method="POST" 
                                      class="d-inline form-restaurar">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="btn-action btn-restore" 
                                            data-tooltip="Restaurar"
                                            data-nombre="{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>

                                <!-- Eliminar Permanente -->
                                <form action="{{ route('admin.clientes.force-delete', $cliente->id) }}" 
                                      method="POST" 
                                      class="d-inline form-eliminar-permanente">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn-action btn-danger-permanent" 
                                            data-tooltip="Eliminar Permanentemente"
                                            data-nombre="{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            <div class="empty-content">
                                <i class="fas fa-trash-restore"></i>
                                <h3>Papelera vacía</h3>
                                <p>No hay clientes eliminados</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($clientes->hasPages())
        <div class="pagination-section">
            {{ $clientes->links() }}
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #4361ee;
        --primary-dark: #3a56d4;
        --danger: #ef4444;
        --danger-dark: #dc2626;
        --success: #10b981;
        --warning: #f59e0b;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e1;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #475569;
        --gray-700: #334155;
        --gray-800: #1e293b;
        --gray-900: #0f172a;
        --papelera: #ef4444;
        --papelera-light: rgba(239, 68, 68, 0.1);
    }

    .clientes-papelera-container {
        padding: 1.5rem;
        background: var(--gray-50);
        min-height: calc(100vh - 60px);
    }

    /* Hero Section */
    .clientes-hero.papelera {
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
        font-size: 1rem;
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
        backdrop-filter: blur(10px);
    }

    .btn-volver:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateX(-5px);
    }

    /* Alertas */
    .alert-custom {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        animation: slideIn 0.3s ease;
    }

    .alert-custom.success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .alert-custom.success .alert-icon {
        color: var(--success);
    }

    .alert-custom.error {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .alert-custom.error .alert-icon {
        color: var(--danger);
    }

    .alert-icon {
        font-size: 1.5rem;
    }

    .alert-content strong {
        display: block;
        font-weight: 700;
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
        padding: 0.5rem;
    }

    /* Stats Grid */
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
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
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

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--gray-800);
        line-height: 1;
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--gray-500);
        margin-top: 0.25rem;
    }

    /* Tabla */
    .card-tabla {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .tabla-clientes {
        width: 100%;
        border-collapse: collapse;
    }

    .tabla-clientes thead {
        background: var(--gray-50);
    }

    .tabla-clientes th {
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: var(--gray-600);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--gray-100);
    }

    .tabla-clientes td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
    }

    .cliente-row.eliminado {
        background: rgba(239, 68, 68, 0.03);
    }

    .cliente-row:hover {
        background: var(--gray-50);
    }

    /* Cliente Info */
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
        font-size: 1.1rem;
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

    .cliente-email {
        font-size: 0.85rem;
        color: var(--gray-500);
    }

    .rut-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        background: var(--gray-100);
        border-radius: 8px;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.85rem;
        color: var(--gray-700);
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

    /* Acciones */
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
        z-index: 100;
    }

    .btn-restore {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.2) 100%);
        color: var(--success);
    }

    .btn-restore:hover {
        background: var(--success);
        color: white;
        transform: translateY(-2px);
    }

    .btn-danger-permanent {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.2) 100%);
        color: var(--danger);
    }

    .btn-danger-permanent:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem !important;
    }

    .empty-content i {
        font-size: 4rem;
        color: var(--gray-300);
        margin-bottom: 1rem;
    }

    .empty-content h3 {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .empty-content p {
        color: var(--gray-400);
    }

    /* Pagination */
    .pagination-section {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--gray-100);
    }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .clientes-hero.papelera {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .hero-content {
            flex-direction: column;
        }

        .tabla-clientes th,
        .tabla-clientes td {
            padding: 0.75rem 1rem;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Confirmar restauración
    $('.form-restaurar').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const nombre = $(this).find('button').data('nombre');

        Swal.fire({
            title: '<i class="fas fa-undo" style="color: #10b981;"></i> Restaurar Cliente',
            html: `<p>¿Deseas restaurar a <strong>${nombre}</strong>?</p>
                   <small style="color: #64748b;">El cliente volverá a estar disponible en el sistema.</small>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-undo"></i> Sí, Restaurar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title',
                confirmButton: 'swal-custom-confirm',
                cancelButton: 'swal-custom-cancel'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Confirmar eliminación permanente
    $('.form-eliminar-permanente').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const nombre = $(this).find('button').data('nombre');

        Swal.fire({
            title: '<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> ¡Advertencia!',
            html: `<p>Estás a punto de eliminar <strong>permanentemente</strong> a:</p>
                   <p style="font-size: 1.1rem; font-weight: 600; color: #1e293b; margin: 1rem 0;">${nombre}</p>
                   <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                       <p style="color: #dc2626; margin: 0; font-weight: 600;">
                           <i class="fas fa-ban"></i> Esta acción NO se puede deshacer
                       </p>
                       <small style="color: #991b1b;">Todos los datos del cliente serán eliminados para siempre.</small>
                   </div>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Eliminar Permanentemente',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title',
                confirmButton: 'swal-custom-confirm-danger',
                cancelButton: 'swal-custom-cancel'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@stop
