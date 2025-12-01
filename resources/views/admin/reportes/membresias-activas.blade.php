@extends('adminlte::page')

@section('title', 'Membresías Activas')

@section('css')
    <style>
        :root {
            --primary: #1e293b;
            --purple: #7c3aed;
            --info: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        .report-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: white;
            border-radius: 14px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .stat-box .valor {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-box .label { color: #64748b; font-size: 0.9rem; }

        .stat-box.activas .valor { color: var(--success); }
        .stat-box.pausadas .valor { color: var(--warning); }
        .stat-box.vencidas .valor { color: var(--danger); }
        .stat-box.por-vencer .valor { color: var(--info); }

        .report-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .report-card-header {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .report-card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .report-card-body { padding: 1.5rem; }

        .membresia-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 0.75rem;
        }

        .membresia-row:last-child { margin-bottom: 0; }

        .membresia-row .info h5 { margin: 0; font-weight: 700; font-size: 1rem; }
        .membresia-row .info span { color: #64748b; font-size: 0.85rem; }

        .membresia-row .stats {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .membresia-row .stat-item { text-align: center; }
        .membresia-row .stat-item .valor { font-weight: 800; font-size: 1.25rem; color: var(--primary); }
        .membresia-row .stat-item .label { font-size: 0.75rem; color: #94a3b8; }

        .estado-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }

        .estado-bar:last-child { margin-bottom: 0; }

        .estado-bar .badge {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .estado-bar .nombre { flex: 1; font-weight: 600; }
        .estado-bar .cantidad { font-weight: 700; }

        .por-vencer-list { max-height: 400px; overflow-y: auto; }

        .cliente-vencer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .cliente-vencer:last-child { border-bottom: none; }

        .cliente-vencer .info h6 { margin: 0; font-weight: 600; font-size: 0.95rem; }
        .cliente-vencer .info span { color: #64748b; font-size: 0.8rem; }

        .cliente-vencer .dias {
            padding: 0.4rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .cliente-vencer .dias.urgente { background: #fef2f2; color: #ef4444; }
        .cliente-vencer .dias.pronto { background: #fffbeb; color: #f59e0b; }

        @media (max-width: 768px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .membresia-row { flex-direction: column; gap: 1rem; text-align: center; }
            .membresia-row .stats { justify-content: center; }
        }
    </style>
@endsection

@section('content')
    <div class="report-header">
        <a href="{{ route('admin.reportes.index') }}" class="btn-back">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <h1><i class="fas fa-id-card mr-2"></i> Membresías Activas</h1>
        <p>Distribución y estado de las membresías</p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-box activas">
            <div class="valor">{{ number_format($stats['total_activas']) }}</div>
            <div class="label">Activas</div>
        </div>
        <div class="stat-box pausadas">
            <div class="valor">{{ number_format($stats['total_pausadas']) }}</div>
            <div class="label">Pausadas</div>
        </div>
        <div class="stat-box vencidas">
            <div class="valor">{{ number_format($stats['total_vencidas']) }}</div>
            <div class="label">Vencidas</div>
        </div>
        <div class="stat-box por-vencer">
            <div class="valor">{{ number_format($stats['por_vencer_7dias']) }}</div>
            <div class="label">Por vencer (7 días)</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Por Membresía -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-chart-pie mr-2" style="color: rgba(124, 58, 237, 0.7);"></i> Distribución por Membresía</h3>
                </div>
                <div class="report-card-body">
                    @forelse($inscripcionesPorMembresia as $item)
                    <div class="membresia-row">
                        <div class="info">
                            <h5>{{ $item->nombre }}</h5>
                            <span>{{ $item->duracion_dias }} días</span>
                        </div>
                        <div class="stats">
                            <div class="stat-item">
                                <div class="valor">{{ $item->total }}</div>
                                <div class="label">Activas</div>
                            </div>
                            <div class="stat-item">
                                <div class="valor" style="color: #10b981;">${{ number_format($item->valor_total, 0, ',', '.') }}</div>
                                <div class="label">Valor Total</div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">Sin membresías activas</p>
                    @endforelse
                </div>
            </div>

            <!-- Por Estado -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-list mr-2" style="color: rgba(59, 130, 246, 0.7);"></i> Distribución por Estado</h3>
                </div>
                <div class="report-card-body">
                    @php
                        $colores = [
                            100 => '#10b981',
                            101 => '#f59e0b',
                            102 => '#ef4444',
                            103 => '#6b7280',
                            105 => '#3b82f6',
                            106 => '#7c3aed',
                        ];
                    @endphp
                    @foreach($inscripcionesPorEstado as $estado)
                    <div class="estado-bar">
                        <span class="badge" style="background: {{ $colores[$estado->codigo] ?? '#6b7280' }};"></span>
                        <span class="nombre">{{ $estado->nombre }}</span>
                        <span class="cantidad">{{ number_format($estado->total) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Por Vencer -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-clock mr-2" style="color: rgba(245, 158, 11, 0.7);"></i> Por Vencer (7 días)</h3>
                </div>
                <div class="report-card-body">
                    <div class="por-vencer-list">
                        @forelse($porVencer as $inscripcion)
                        @php
                            $diasRestantes = now()->diffInDays($inscripcion->fecha_vencimiento, false);
                        @endphp
                        <div class="cliente-vencer">
                            <div class="info">
                                <h6>{{ $inscripcion->cliente->nombres ?? 'N/A' }} {{ $inscripcion->cliente->apellido_paterno ?? '' }}</h6>
                                <span>{{ $inscripcion->membresia->nombre ?? 'N/A' }}</span>
                            </div>
                            <span class="dias {{ $diasRestantes <= 2 ? 'urgente' : 'pronto' }}">
                                {{ $diasRestantes }} días
                            </span>
                        </div>
                        @empty
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i><br>
                            Sin vencimientos próximos
                        </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
