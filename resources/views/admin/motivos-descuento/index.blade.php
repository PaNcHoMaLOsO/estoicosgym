@extends('adminlte::page')

@section('title', 'Motivos de Descuento - EstóicosGym')

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --purple: #7c3aed;
        --gray-50: #fafbfc;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --radius-md: 12px;
        --radius-lg: 16px;
    }

    /* ===== HERO HEADER ===== */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: var(--radius-lg);
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
    }

    .page-header h1 {
        color: white;
        margin: 0;
        font-weight: 700;
    }

    .page-header h1 i {
        color: var(--purple);
    }

    .page-header small {
        color: rgba(255,255,255,0.7);
    }

    .header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-header {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-header-primary {
        background: var(--success);
        border: none;
        color: white;
    }

    .btn-header-primary:hover {
        background: #00a67d;
        transform: translateY(-2px);
        color: white;
    }

    .btn-header-secondary {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
    }

    .btn-header-secondary:hover {
        background: rgba(255,255,255,0.25);
        color: white;
    }

    /* ===== STAT CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--radius-md);
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
        border-left: 4px solid var(--purple);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .stat-card.success { border-left-color: var(--success); }
    .stat-card.warning { border-left-color: var(--warning); }
    .stat-card.danger { border-left-color: var(--accent); }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--purple);
        line-height: 1;
    }

    .stat-card.success .stat-number { color: var(--success); }
    .stat-card.warning .stat-number { color: var(--warning); }
    .stat-card.danger .stat-number { color: var(--accent); }

    .stat-label {
        color: var(--gray-600);
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.25rem;
    }

    /* ===== MOTIVO CARDS ===== */
    .motivos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }

    .motivo-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid var(--gray-200);
    }

    .motivo-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .motivo-card.inactive {
        opacity: 0.7;
    }

    .motivo-header {
        background: linear-gradient(135deg, var(--purple) 0%, #6d28d9 100%);
        color: white;
        padding: 1.25rem;
        position: relative;
    }

    .motivo-card.inactive .motivo-header {
        background: linear-gradient(135deg, var(--gray-600) 0%, #5a6268 100%);
    }

    .motivo-icon {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .motivo-nombre {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .motivo-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .motivo-status.activo {
        background: rgba(0, 191, 142, 0.2);
        color: #00bf8e;
        border: 1px solid rgba(0, 191, 142, 0.3);
    }

    .motivo-status.inactivo {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .motivo-body {
        padding: 1.25rem;
    }

    .motivo-descripcion {
        color: var(--gray-600);
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.5;
        min-height: 44px;
    }

    .motivo-info {
        display: flex;
        gap: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
    }

    .info-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    .info-badge i {
        color: var(--purple);
    }

    .motivo-footer {
        padding: 1rem 1.25rem;
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        text-decoration: none;
        border: none;
        cursor: pointer;
        min-width: 85px;
        justify-content: center;
    }

    .btn-action-view {
        background: rgba(124, 58, 237, 0.1);
        color: var(--purple);
    }

    .btn-action-view:hover {
        background: var(--purple);
        color: white;
    }

    .btn-action-edit {
        background: rgba(240, 165, 0, 0.1);
        color: var(--warning);
    }

    .btn-action-edit:hover {
        background: var(--warning);
        color: white;
    }

    .btn-action-delete {
        background: rgba(233, 69, 96, 0.1);
        color: var(--accent);
    }

    .btn-action-delete:hover {
        background: var(--accent);
        color: white;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--gray-200);
        margin-bottom: 1rem;
    }

    .empty-state h4 {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-600);
        margin-bottom: 1.5rem;
    }

    /* ===== ALERTS ===== */
    .custom-alert {
        border-radius: var(--radius-md);
        border: none;
        box-shadow: var(--shadow-sm);
    }

    .custom-alert.alert-success {
        background: linear-gradient(135deg, rgba(0, 191, 142, 0.1), rgba(0, 191, 142, 0.05));
        border-left: 4px solid var(--success);
        color: #0a5c43;
    }

    .custom-alert.alert-danger {
        background: linear-gradient(135deg, rgba(233, 69, 96, 0.1), rgba(233, 69, 96, 0.05));
        border-left: 4px solid var(--accent);
        color: #9b2c3a;
    }
</style>
@stop

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1><i class="fas fa-percent"></i> Motivos de Descuento</h1>
                <small>Gestiona los motivos de descuento aplicables a las membresías</small>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.motivos-descuento.create') }}" class="btn btn-header btn-header-primary">
                    <i class="fas fa-plus"></i> Nuevo Motivo
                </a>
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert custom-alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>¡Éxito!</strong> {{ $message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert custom-alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>¡Error!</strong> {{ $message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Stats -->
    @php
        $totalMotivos = $motivos->total();
        $activos = $motivos->where('activo', true)->count();
        $inactivos = $motivos->where('activo', false)->count();
    @endphp

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $totalMotivos }}</div>
            <div class="stat-label">Total Motivos</div>
        </div>
        <div class="stat-card success">
            <div class="stat-number">{{ $activos }}</div>
            <div class="stat-label">Activos</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-number">{{ $inactivos }}</div>
            <div class="stat-label">Inactivos</div>
        </div>
    </div>

    <!-- Grid de Motivos -->
    @if($motivos->count() > 0)
        <div class="motivos-grid">
            @foreach($motivos as $motivo)
                <div class="motivo-card {{ !$motivo->activo ? 'inactive' : '' }}">
                    <div class="motivo-header">
                        <div class="motivo-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <h3 class="motivo-nombre">{{ $motivo->nombre }}</h3>
                        <span class="motivo-status {{ $motivo->activo ? 'activo' : 'inactivo' }}">
                            {{ $motivo->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="motivo-body">
                        <p class="motivo-descripcion">
                            {{ $motivo->descripcion ? Str::limit($motivo->descripcion, 100) : 'Sin descripción disponible' }}
                        </p>
                        <div class="motivo-info">
                            <div class="info-badge">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Creado: {{ $motivo->created_at ? $motivo->created_at->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="motivo-footer">
                        <a href="{{ route('admin.motivos-descuento.show', $motivo) }}" class="btn-action btn-action-view">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="{{ route('admin.motivos-descuento.edit', $motivo) }}" class="btn-action btn-action-edit">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="{{ route('admin.motivos-descuento.destroy', $motivo) }}" method="POST" style="display:inline;" 
                              onsubmit="return confirm('¿Estás seguro de eliminar este motivo de descuento?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action-delete">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4">
            {{ $motivos->links('pagination::bootstrap-4') }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-percent"></i>
            <h4>No hay motivos de descuento registrados</h4>
            <p>Comienza agregando un nuevo motivo para aplicar descuentos a las membresías.</p>
            <a href="{{ route('admin.motivos-descuento.create') }}" class="btn btn-header btn-header-primary">
                <i class="fas fa-plus"></i> Crear Primer Motivo
            </a>
        </div>
    @endif
@stop

@section('js')
<script>
    // Animación de entrada para las tarjetas
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.motivo-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@stop
