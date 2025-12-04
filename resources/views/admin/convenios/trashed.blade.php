@extends('adminlte::page')

@section('title', 'Papelera de Convenios')

@section('content_header')
@stop

@section('content')
<div class="papelera-container">
    <div class="papelera-hero">
        <div class="hero-content">
            <div class="hero-icon"><i class="fas fa-trash-alt"></i></div>
            <div class="hero-text">
                <h1>Papelera de Convenios</h1>
                <p>Convenios eliminados que pueden ser restaurados</p>
            </div>
        </div>
        <a href="{{ route('admin.convenios.index') }}" class="btn-volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert-success"><i class="fas fa-check-circle"></i> {{ $message }}</div>
    @endif
    @if ($message = Session::get('error'))
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
    @endif

    <div class="stat-card">
        <i class="fas fa-trash"></i>
        <span class="stat-number">{{ $totalEliminados }}</span>
        <span class="stat-label">En Papelera</span>
    </div>

    <div class="card-tabla">
        <table class="tabla-items">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Clientes</th>
                    <th>Eliminado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($convenios as $convenio)
                <tr>
                    <td><strong>{{ $convenio->nombre }}</strong></td>
                    <td>
                        <span class="tipo-badge tipo-{{ $convenio->tipo }}">
                            {{ str_replace('_', ' ', ucfirst($convenio->tipo)) }}
                        </span>
                    </td>
                    <td><span class="badge">{{ $convenio->clientes_count ?? 0 }}</span></td>
                    <td>
                        <small>{{ $convenio->deleted_at->format('d/m/Y H:i') }}</small>
                        <br><small class="text-muted">(hace {{ $convenio->deleted_at->diffForHumans(null, true) }})</small>
                    </td>
                    <td>
                        <div class="acciones">
                            <form action="{{ route('admin.convenios.restore', $convenio->id) }}" method="POST" class="form-restaurar">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-restore" data-nombre="{{ $convenio->nombre }}">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.convenios.force-delete', $convenio->id) }}" method="POST" class="form-eliminar">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger" data-nombre="{{ $convenio->nombre }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="fas fa-trash-restore"></i>
                        <p>Papelera vacía</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($convenios->hasPages())
        <div class="pagination">{{ $convenios->links() }}</div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .papelera-container { padding: 1.5rem; background: #f8fafc; min-height: calc(100vh - 60px); }
    .papelera-hero { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border-radius: 16px; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; color: white; }
    .hero-content { display: flex; align-items: center; gap: 1rem; }
    .hero-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .hero-text h1 { margin: 0; font-size: 1.5rem; }
    .hero-text p { margin: 0; opacity: 0.8; }
    .btn-volver { padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.2); color: white; border-radius: 8px; text-decoration: none; font-weight: 600; }
    .btn-volver:hover { background: rgba(255,255,255,0.3); color: white; }
    .alert-success, .alert-error { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .alert-success { background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); }
    .alert-error { background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3); }
    .stat-card { background: white; border-radius: 12px; padding: 1rem 1.5rem; display: inline-flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; border-left: 4px solid #ef4444; }
    .stat-card i { font-size: 1.5rem; color: #ef4444; }
    .stat-number { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
    .stat-label { color: #64748b; }
    .card-tabla { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .tabla-items { width: 100%; border-collapse: collapse; }
    .tabla-items th { background: #f8fafc; padding: 1rem; text-align: left; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; }
    .tabla-items td { padding: 1rem; border-bottom: 1px solid #f1f5f9; }
    .badge { background: #e2e8f0; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
    .tipo-badge { padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .tipo-badge.tipo-institucion_educativa { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
    .tipo-badge.tipo-empresa { background: rgba(16, 185, 129, 0.1); color: #059669; }
    .tipo-badge.tipo-organizacion { background: rgba(168, 85, 247, 0.1); color: #7c3aed; }
    .tipo-badge.tipo-otro { background: rgba(100, 116, 139, 0.1); color: #475569; }
    .text-muted { color: #94a3b8; }
    .acciones { display: flex; gap: 0.5rem; }
    .btn-restore, .btn-danger { width: 36px; height: 36px; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .btn-restore { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .btn-restore:hover { background: #10b981; color: white; }
    .btn-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .btn-danger:hover { background: #ef4444; color: white; }
    .empty-state { text-align: center; padding: 3rem !important; color: #94a3b8; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
    .pagination { padding: 1rem; border-top: 1px solid #f1f5f9; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.form-restaurar').on('submit', function(e) {
        e.preventDefault();
        const form = this, nombre = $(this).find('button').data('nombre');
        Swal.fire({
            title: 'Restaurar Convenio',
            html: `¿Restaurar <strong>${nombre}</strong>?`,
            showCancelButton: true,
            confirmButtonText: 'Sí, Restaurar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981'
        }).then((r) => { if (r.isConfirmed) form.submit(); });
    });

    $('.form-eliminar').on('submit', function(e) {
        e.preventDefault();
        const form = this, nombre = $(this).find('button').data('nombre');
        Swal.fire({
            title: '¡Eliminar Permanentemente!',
            html: `<strong>${nombre}</strong> será eliminado para siempre.<br><small style="color:#ef4444">Esta acción NO se puede deshacer.</small>`,
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444'
        }).then((r) => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@stop
