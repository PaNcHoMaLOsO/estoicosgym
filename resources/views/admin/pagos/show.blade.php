@extends('adminlte::page')

@section('title', 'Detalles Pago - EstóicosGym')

@section('css')
<style>
    * { font-family: 'Segoe UI', sans-serif; }
    
    .content-wrapper {
        background: #f5f7fa !important;
    }

    .header-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        margin: 0 -15px 30px -15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .header-section h2 {
        margin: 0;
        font-size: 1.8em;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-section p {
        margin: 8px 0 0 0;
        opacity: 0.95;
        font-size: 0.95em;
    }

    .card-section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
    }

    .card-title {
        font-size: 1.15em;
        font-weight: 700;
        color: #333;
        margin: 0 0 20px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #667eea;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .info-cell {
        background: linear-gradient(135deg, #f5f9ff 0%, #f0f5fd 100%);
        border-radius: 10px;
        padding: 18px;
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .info-cell:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.15);
    }

    .info-label {
        font-size: 0.8em;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .info-value {
        font-size: 1.25em;
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
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .badge-paid {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .badge-partial {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .btn-group-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-top: 25px;
    }

    .btn-section {
        padding: 12px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.95em;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary-section:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary-section {
        background: #6c757d;
        color: white;
    }

    .btn-secondary-section:hover {
        background: #5a6268;
        transform: translateY(-3px);
    }

    .btn-danger-section {
        background: #dc3545;
        color: white;
    }

    .btn-danger-section:hover {
        background: #c82333;
        transform: translateY(-3px);
    }

    .historial-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95em;
    }

    .historial-table th {
        background: linear-gradient(135deg, #f5f9ff 0%, #f0f5fd 100%);
        padding: 15px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #ddd;
        color: #333;
    }

    .historial-table td {
        padding: 14px 15px;
        border-bottom: 1px solid #eee;
    }

    .historial-table tr:hover {
        background: #f9f9f9;
    }

    .historial-table tr:last-child td {
        border-bottom: none;
    }

    .link-item {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .link-item:hover {
        color: #764ba2;
        gap: 10px;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c8e6c9 100%);
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .two-column-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    @media (max-width: 1200px) {
        .two-column-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .header-section {
            padding: 20px 15px;
            margin: 0 -15px 20px -15px;
        }

        .header-section h2 {
            font-size: 1.3em;
        }

        .card-section {
            padding: 15px;
            margin-bottom: 15px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .btn-group-section {
            grid-template-columns: 1fr;
        }

        .btn-section {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> 
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="header-section">
        <h2><i class="fas fa-receipt"></i> Detalles del Pago</h2>
        <p>Información completa del registro de pago - {{ $pago->fecha_pago->format('d/m/Y') }}</p>
    </div>

    <div class="two-column-grid">
        <!-- COLUMNA IZQUIERDA -->
        <div>
            <!-- PAGO -->
            <div class="card-section">
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
                        <div class="info-label">Método de Pago</div>
                        <div class="info-value">{{ $pago->metodoPagoPrincipal?->nombre ?? '-' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Cantidad de Cuotas</div>
                        <div class="info-value">{{ $pago->cantidad_cuotas }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Estado</div>
                        <div style="margin-top: 6px;">
                            @if($pago->monto_pendiente == 0)
                                <span class="badge-status badge-paid">✓ Pagado</span>
                            @else
                                <span class="badge-status badge-partial">⚠ Parcial</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- CLIENTE -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-user"></i> Información del Cliente
                </div>
                <div class="info-grid">
                    <div class="info-cell" style="grid-column: span 2;">
                        <div class="info-label">Nombre Completo</div>
                        <div class="info-value">{{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Membresía</div>
                        <div class="info-value">{{ $pago->inscripcion->membresia->nombre }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Email</div>
                        <div class="info-value" style="font-size: 0.95em; word-break: break-all;">{{ $pago->inscripcion->cliente->email }}</div>
                    </div>
                </div>
                <div style="margin-top: 18px; padding-top: 18px; border-top: 1px solid #eee;">
                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="link-item">
                        <i class="fas fa-arrow-right"></i> Ver Inscripción Completa
                    </a>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div>
            <!-- HISTORIAL PAGOS -->
            @if($pago->inscripcion->pagos->count() > 0)
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-history"></i> Historial de Pagos
                </div>
                <table class="historial-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Método</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pago->inscripcion->pagos->sortByDesc('fecha_pago') as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->fecha_pago->format('d/m/Y') }}</td>
                            <td><strong>${{ number_format($p->monto_abonado, 0, '.', '.') }}</strong></td>
                            <td>{{ $p->metodoPagoPrincipal?->nombre ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- RESUMEN FECHAS -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-calendar"></i> Fechas Importantes
                </div>
                <div class="info-grid">
                    <div class="info-cell">
                        <div class="info-label">Inicio Membresía</div>
                        <div class="info-value">{{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Vencimiento</div>
                        <div class="info-value">{{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Días Restantes</div>
                        <div class="info-value">{{ max(0, now()->diffInDays($pago->inscripcion->fecha_vencimiento, false)) }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Creado</div>
                        <div class="info-value" style="font-size: 0.9em;">{{ $pago->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- BOTONES ACCIONES -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-cog"></i> Acciones
                </div>
                <div class="btn-group-section">
                    <a href="{{ route('admin.pagos.index') }}" class="btn-section btn-secondary-section">
                        <i class="fas fa-list"></i> Lista
                    </a>
                    <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn-section btn-primary-section">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" style="display: contents;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-section btn-danger-section" 
                                onclick="return confirm('¿Eliminar este pago? No se puede deshacer.')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
