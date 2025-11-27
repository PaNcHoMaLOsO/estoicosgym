@extends('adminlte::page')

@section('title', 'Detalles Pago - EstóicosGym')

@section('css')
<style>
    * { font-family: 'Segoe UI', sans-serif; }
    
    .page-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .header-compact {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .header-compact h2 {
        margin: 0;
        font-size: 1.6em;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-compact p {
        margin: 8px 0 0 0;
        opacity: 0.95;
        font-size: 0.95em;
    }

    .card-compact {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
    }

    .card-title {
        font-size: 1.1em;
        font-weight: 700;
        color: #333;
        margin: 0 0 15px 0;
        padding-bottom: 12px;
        border-bottom: 2px solid #667eea;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
    }

    .info-cell {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        border-left: 3px solid #667eea;
    }

    .info-label {
        font-size: 0.75em;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .info-value {
        font-size: 1.15em;
        font-weight: 700;
        color: #333;
    }

    .info-value.success {
        color: #28a745;
    }

    .info-value.danger {
        color: #dc3545;
    }

    .info-value.warning {
        color: #ffc107;
    }

    .badge-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .badge-paid {
        background: #d4edda;
        color: #155724;
    }

    .badge-partial {
        background: #fff3cd;
        color: #856404;
    }

    .section-divider {
        height: 1px;
        background: #e0e0e0;
        margin: 20px 0;
    }

    .btn-group-compact {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .btn-compact {
        padding: 10px 18px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.95em;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-compact-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-compact-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-compact-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-compact-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .btn-compact-danger {
        background: #dc3545;
        color: white;
    }

    .btn-compact-danger:hover {
        background: #c82333;
    }

    .historial-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95em;
    }

    .historial-table th {
        background: #f0f0f0;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #ddd;
        color: #333;
    }

    .historial-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    .historial-table tr:last-child td {
        border-bottom: none;
    }

    .link-item {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }

    .link-item:hover {
        text-decoration: underline;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .page-wrapper { padding: 0 15px; }
        .header-compact { padding: 15px; }
        .header-compact h2 { font-size: 1.3em; }
        .info-grid { grid-template-columns: 1fr; }
        .btn-group-compact { flex-direction: column; }
        .btn-compact { width: 100%; justify-content: center; }
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="header-compact">
        <h2><i class="fas fa-receipt"></i> Detalles del Pago</h2>
        <p>Información completa del registro de pago</p>
    </div>

    <!-- PAGO -->
    <div class="card-compact">
        <div class="card-title">
            <i class="fas fa-money-bill-wave"></i> Información del Pago
        </div>
        <div class="info-grid">
            <div class="info-cell">
                <div class="info-label">Monto Pagado</div>
                <div class="info-value success">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Total Inscripción</div>
                <div class="info-value">${{ number_format($pago->monto_total, 0, '.', '.') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Pendiente</div>
                <div class="info-value warning">${{ number_format($pago->monto_pendiente, 0, '.', '.') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Fecha Pago</div>
                <div class="info-value">{{ $pago->fecha_pago->format('d/m/Y') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Método</div>
                <div class="info-value">{{ $pago->metodoPagoPrincipal?->nombre ?? '-' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Estado</div>
                <div class="info-value">
                    @if($pago->monto_pendiente == 0)
                        <span class="badge-status badge-paid">Pagado</span>
                    @else
                        <span class="badge-status badge-partial">Parcial</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- CLIENTE -->
    <div class="card-compact">
        <div class="card-title">
            <i class="fas fa-user"></i> Cliente
        </div>
        <div class="info-grid">
            <div class="info-cell" style="grid-column: span 2;">
                <div class="info-label">Nombre</div>
                <div class="info-value">{{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Membresía</div>
                <div class="info-value">{{ $pago->inscripcion->membresia->nombre }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Email</div>
                <div class="info-value" style="font-size: 0.95em;">{{ $pago->inscripcion->cliente->email }}</div>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="link-item">
                <i class="fas fa-arrow-right"></i> Ver Inscripción
            </a>
        </div>
    </div>

    <!-- HISTORIAL PAGOS -->
    @if($pago->inscripcion->pagos->count() > 1)
    <div class="card-compact">
        <div class="card-title">
            <i class="fas fa-history"></i> Historial de Pagos
        </div>
        <table class="historial-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Método</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pago->inscripcion->pagos->sortByDesc('fecha_pago')->take(5) as $p)
                <tr>
                    <td>{{ $p->fecha_pago->format('d/m/Y') }}</td>
                    <td><strong>${{ number_format($p->monto_abonado, 0, '.', '.') }}</strong></td>
                    <td>{{ $p->metodoPagoPrincipal?->nombre ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- BOTONES -->
    <div class="btn-group-compact">
        <a href="{{ route('admin.pagos.index') }}" class="btn-compact btn-compact-secondary">
            <i class="fas fa-list"></i> Lista de Pagos
        </a>
        <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn-compact btn-compact-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-compact btn-compact-danger" 
                    onclick="return confirm('¿Eliminar este pago? No se puede deshacer.')">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </form>
    </div>
</div>

@endsection
