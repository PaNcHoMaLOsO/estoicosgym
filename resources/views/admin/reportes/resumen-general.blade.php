@extends('adminlte::page')

@section('title', 'Resumen General')

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
            background: linear-gradient(135deg, var(--purple) 0%, #4f46e5 100%);
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

        .report-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .report-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .btn-back {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--stat-color, var(--purple));
        }

        .stat-card .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2.5rem;
            opacity: 0.7;
            color: var(--stat-color, var(--purple));
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Cards */
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .report-card-header h3 i {
            color: var(--purple);
        }

        .report-card-body {
            padding: 1.5rem;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Tendencia Grid */
        .tendencia-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1rem;
        }

        .tendencia-item {
            text-align: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
        }

        .tendencia-item .mes {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .tendencia-item .valor {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--success);
        }

        .tendencia-item .sub {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .tendencia-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endsection

@section('content')
    <div class="report-header">
        <a href="{{ route('admin.reportes.index') }}" class="btn-back">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <h1><i class="fas fa-tachometer-alt mr-2"></i> Resumen General</h1>
        <p>Vista general del estado del gimnasio - {{ now()->translatedFormat('F Y') }}</p>
    </div>

    <!-- Stats principales -->
    <div class="stats-grid">
        <div class="stat-card" style="--stat-color: #3b82f6;">
            <i class="fas fa-users stat-icon"></i>
            <div class="stat-value">{{ number_format($stats['total_clientes']) }}</div>
            <div class="stat-label">Total Clientes</div>
        </div>
        <div class="stat-card" style="--stat-color: #10b981;">
            <i class="fas fa-check-circle stat-icon"></i>
            <div class="stat-value">{{ number_format($stats['inscripciones_activas']) }}</div>
            <div class="stat-label">Inscripciones Activas</div>
        </div>
        <div class="stat-card" style="--stat-color: #f59e0b;">
            <i class="fas fa-pause-circle stat-icon"></i>
            <div class="stat-value">{{ number_format($stats['inscripciones_pausadas']) }}</div>
            <div class="stat-label">Pausadas</div>
        </div>
        <div class="stat-card" style="--stat-color: #ef4444;">
            <i class="fas fa-times-circle stat-icon"></i>
            <div class="stat-value">{{ number_format($stats['inscripciones_vencidas']) }}</div>
            <div class="stat-label">Vencidas</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Ingresos del mes -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-dollar-sign"></i> Ingresos del Mes</h3>
                    <span class="badge" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 20px;">
                        ${{ number_format($stats['ingresos_mes'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="report-card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h4 style="font-size: 2.5rem; font-weight: 800; color: #10b981;">
                                {{ number_format($stats['pagos_mes']) }}
                            </h4>
                            <p class="text-muted mb-0">Pagos realizados</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 style="font-size: 2.5rem; font-weight: 800; color: #f59e0b;">
                                {{ number_format($stats['pagos_pendientes']) }}
                            </h4>
                            <p class="text-muted mb-0">Pagos pendientes</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 style="font-size: 2.5rem; font-weight: 800; color: #ef4444;">
                                ${{ number_format($stats['monto_pendiente'], 0, ',', '.') }}
                            </h4>
                            <p class="text-muted mb-0">Monto pendiente</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tendencia últimos 6 meses -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-chart-line"></i> Tendencia Últimos 6 Meses</h3>
                </div>
                <div class="report-card-body">
                    <div class="tendencia-grid">
                        @foreach($tendencia as $item)
                        <div class="tendencia-item">
                            <div class="mes">{{ $item['mes'] }}</div>
                            <div class="valor">${{ number_format($item['ingresos'], 0, ',', '.') }}</div>
                            <div class="sub">{{ $item['inscripciones'] }} inscripciones</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Nuevos este mes -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-user-plus"></i> Nuevos Este Mes</h3>
                </div>
                <div class="report-card-body text-center">
                    <h2 style="font-size: 4rem; font-weight: 800; color: #7c3aed; margin-bottom: 0.5rem;">
                        {{ number_format($stats['clientes_mes']) }}
                    </h2>
                    <p class="text-muted">Clientes registrados</p>
                    
                    <hr>
                    
                    <h2 style="font-size: 3rem; font-weight: 800; color: #3b82f6; margin-bottom: 0.5rem;">
                        {{ number_format($stats['inscripciones_mes']) }}
                    </h2>
                    <p class="text-muted mb-0">Inscripciones nuevas</p>
                </div>
            </div>

            <!-- Membresía más vendida -->
            @if($stats['membresia_mas_vendida'])
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-trophy"></i> Más Popular</h3>
                </div>
                <div class="report-card-body text-center">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #fbbf24); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <i class="fas fa-medal" style="font-size: 2rem; color: white;"></i>
                    </div>
                    <h4 style="font-weight: 700; margin-bottom: 0.5rem;">
                        {{ $stats['membresia_mas_vendida']->membresia->nombre ?? 'N/A' }}
                    </h4>
                    <p class="text-muted mb-0">
                        {{ number_format($stats['membresia_mas_vendida']->total) }} inscripciones
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="report-card">
        <div class="report-card-header">
            <h3><i class="fas fa-bolt" style="opacity: 0.5;"></i> Acciones Rápidas</h3>
        </div>
        <div class="report-card-body">
            <div class="row">
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('admin.reportes.predefinido', 'ingresos-mensuales') }}" class="btn btn-light btn-block" style="padding: 1rem; border-radius: 10px;">
                        <i class="fas fa-chart-line mb-2" style="font-size: 1.5rem; color: rgba(16, 185, 129, 0.7);"></i><br>
                        Ver Ingresos
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('admin.reportes.predefinido', 'membresias-activas') }}" class="btn btn-light btn-block" style="padding: 1rem; border-radius: 10px;">
                        <i class="fas fa-id-card mb-2" style="font-size: 1.5rem; color: rgba(59, 130, 246, 0.7);"></i><br>
                        Ver Membresías
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('admin.reportes.predefinido', 'pagos-pendientes') }}" class="btn btn-light btn-block" style="padding: 1rem; border-radius: 10px;">
                        <i class="fas fa-exclamation-circle mb-2" style="font-size: 1.5rem; color: rgba(239, 68, 68, 0.7);"></i><br>
                        Pagos Pendientes
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('admin.reportes.builder') }}" class="btn btn-light btn-block" style="padding: 1rem; border-radius: 10px;">
                        <i class="fas fa-tools mb-2" style="font-size: 1.5rem; color: rgba(124, 58, 237, 0.7);"></i><br>
                        Constructor
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
