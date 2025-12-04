@extends('adminlte::page')

@section('title', 'Papelera de Pagos')

@section('content_header')
@stop

@section('content')
<div class="papelera-container">
    <div class="papelera-hero">
        <div class="hero-content">
            <div class="hero-icon"><i class="fas fa-trash-alt"></i></div>
            <div class="hero-text">
                <h1>Papelera de Pagos</h1>
                <p>Pagos eliminados que pueden ser restaurados</p>
            </div>
        </div>
        <a href="{{ route('admin.pagos.index') }}" class="btn-volver">
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
                    <th>Cliente</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Fecha Pago</th>
                    <th>Eliminado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagos as $pago)
                <tr>
                    <td>
                        <strong>{{ $pago->cliente->nombres ?? 'N/A' }} {{ $pago->cliente->apellido_paterno ?? '' }}</strong>
                        <br><small class="text-muted">{{ $pago->cliente->rut ?? 'N/A' }}</small>
                    </td>
                    <td>
                        <span class="monto">${{ number_format($pago->monto_abonado, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        <span class="metodo-badge">{{ $pago->metodoPago->nombre ?? 'N/A' }}</span>
                    </td>
                    <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? 'N/A' }}</td>
                    <td>
                        <small>{{ $pago->deleted_at->format('d/m/Y H:i') }}</small>
                        <br><small class="text-muted">(hace {{ $pago->deleted_at->diffForHumans(null, true) }})</small>
                    </td>
                    <td>
                        <div class="acciones">
                            <form action="{{ route('admin.pagos.restore', $pago->id) }}" method="POST" class="form-restaurar">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-restore" data-monto="${{ number_format($pago->monto_abonado, 0, ',', '.') }}">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.pagos.force-delete', $pago->id) }}" method="POST" class="form-eliminar">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger" data-monto="${{ number_format($pago->monto_abonado, 0, ',', '.') }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="fas fa-trash-restore"></i>
                        <p>Papelera vacía</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($pagos->hasPages())
        <div class="pagination">{{ $pagos->links() }}</div>
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
    .monto { font-weight: 700; color: #059669; font-size: 1.1rem; }
    .metodo-badge { background: rgba(67, 97, 238, 0.1); color: #4361ee; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; }
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
        const form = this, monto = $(this).find('button').data('monto');
        Swal.fire({
            title: 'Restaurar Pago',
            html: `¿Restaurar pago de <strong>${monto}</strong>?`,
            showCancelButton: true,
            confirmButtonText: 'Sí, Restaurar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981'
        }).then((r) => { if (r.isConfirmed) form.submit(); });
    });

    $('.form-eliminar').on('submit', function(e) {
        e.preventDefault();
        const form = this, monto = $(this).find('button').data('monto');
        Swal.fire({
            title: '¡Eliminar Permanentemente!',
            html: `Pago de <strong>${monto}</strong> será eliminado para siempre.<br><small style="color:#ef4444">Esta acción NO se puede deshacer.</small>`,
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444'
        }).then((r) => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@stop
