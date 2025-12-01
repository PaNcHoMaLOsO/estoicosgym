@extends('adminlte::page')

@section('title', 'Pagos Pendientes')

@section('css')
    <style>
        :root {
            --primary: #1e293b;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        .report-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .report-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .report-header h1 { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; }
        .report-header p { opacity: 0.9; margin-bottom: 0; }

        .btn-back {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            text-decoration: none;
        }

        .btn-back:hover { background: rgba(255,255,255,0.3); color: white; }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .summary-card .valor {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .summary-card .label { color: #64748b; }

        .summary-card.pendientes .valor { color: var(--danger); }
        .summary-card.abonado .valor { color: #10b981; }
        .summary-card.cantidad .valor { color: #3b82f6; }

        .report-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .report-card-header {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .table-container { overflow-x: auto; }

        .pagos-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pagos-table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e5e7eb;
        }

        .pagos-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .pagos-table tr:hover { background: #f8fafc; }

        .cliente-info h6 { margin: 0; font-weight: 600; }
        .cliente-info span { font-size: 0.8rem; color: #64748b; }

        .badge-estado {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-estado.pendiente { background: #fef2f2; color: #ef4444; }
        .badge-estado.parcial { background: #fffbeb; color: #f59e0b; }

        .monto { font-weight: 700; }
        .monto.pendiente { color: var(--danger); }
        .monto.abonado { color: #10b981; }

        .btn-ver {
            padding: 0.4rem 0.75rem;
            background: #f1f5f9;
            border: none;
            border-radius: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .btn-ver:hover { background: #e2e8f0; color: #1e293b; }

        @media (max-width: 768px) {
            .summary-cards { grid-template-columns: 1fr; }
        }
    </style>
@endsection

@section('content')
    <div class="report-header">
        <a href="{{ route('admin.reportes.index') }}" class="btn-back">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <h1><i class="fas fa-exclamation-circle mr-2"></i> Pagos Pendientes</h1>
        <p>Pagos que requieren seguimiento y cobro</p>
    </div>

    <!-- Summary -->
    <div class="summary-cards">
        <div class="summary-card pendientes">
            <div class="valor">${{ number_format($totalPendiente, 0, ',', '.') }}</div>
            <div class="label">Total Pendiente</div>
        </div>
        <div class="summary-card abonado">
            <div class="valor">${{ number_format($totalAbonado, 0, ',', '.') }}</div>
            <div class="label">Total Abonado</div>
        </div>
        <div class="summary-card cantidad">
            <div class="valor">{{ number_format($pagosPendientes->count()) }}</div>
            <div class="label">Pagos Pendientes</div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="report-card">
        <div class="report-card-header">
            <h3><i class="fas fa-list mr-2" style="color: rgba(239, 68, 68, 0.5);"></i> Listado de Pagos Pendientes</h3>
            <span class="badge bg-danger text-white px-3 py-2" style="border-radius: 20px;">
                {{ $pagosPendientes->count() }} registros
            </span>
        </div>
        <div class="table-container">
            <table class="pagos-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Membresía</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Abonado</th>
                        <th>Pendiente</th>
                        <th>Método</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagosPendientes as $pago)
                    <tr>
                        <td>
                            <div class="cliente-info">
                                <h6>{{ $pago->cliente->nombres ?? 'N/A' }} {{ $pago->cliente->apellido_paterno ?? '' }}</h6>
                                <span>{{ $pago->cliente->telefono ?? '-' }}</span>
                            </div>
                        </td>
                        <td>{{ $pago->inscripcion->membresia->nombre ?? '-' }}</td>
                        <td>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span class="badge-estado {{ $pago->id_estado == 200 ? 'pendiente' : 'parcial' }}">
                                {{ $pago->id_estado == 200 ? 'Pendiente' : 'Parcial' }}
                            </span>
                        </td>
                        <td><span class="monto abonado">${{ number_format($pago->monto_abonado, 0, ',', '.') }}</span></td>
                        <td><span class="monto pendiente">${{ number_format($pago->monto_pendiente, 0, ',', '.') }}</span></td>
                        <td>{{ $pago->metodoPago->nombre ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.pagos.show', $pago) }}" class="btn-ver">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0 text-muted">¡No hay pagos pendientes!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
