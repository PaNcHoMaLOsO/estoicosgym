@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0" style="font-size: 2rem; font-weight: 700; color: #2c3e50;">
                <i class="fas fa-chart-line" style="margin-right: 10px; color: #007bff;"></i> Dashboard Ejecutivo
            </h1>
            <small class="text-muted" style="font-size: 0.95rem;">Monitoreo en tiempo real de operaciones</small>
        </div>
        <div class="col-sm-4 text-right">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 15px; border-radius: 8px; display: inline-block;">
                <i class="fas fa-clock"></i> {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- FILA 1: KPI CARDS - ELEGANTE -->
    <div class="row mb-4">
        <!-- Total Clientes -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-primary">
                <div class="kpi-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-content">
                    <h2 class="kpi-number">{{ $totalClientes }}</h2>
                    <p class="kpi-label">Total Clientes</p>
                    <small class="kpi-desc">Clientes activos en el sistema</small>
                </div>
            </div>
        </div>

        <!-- Inscripciones Activas -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-success">
                <div class="kpi-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="kpi-content">
                    <h2 class="kpi-number">{{ $inscripcionesActivas }}</h2>
                    <p class="kpi-label">Activas Ahora</p>
                    <small class="kpi-desc">Membresías en vigencia</small>
                </div>
            </div>
        </div>

        <!-- Ingresos del Mes -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-warning">
                <div class="kpi-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="kpi-content">
                    <h2 class="kpi-number">${{ number_format($ingresosMes, 0, '.', '.') }}</h2>
                    <p class="kpi-label">Este Mes</p>
                    <small class="kpi-desc">{{ now()->format('F Y') }}</small>
                </div>
            </div>
        </div>

        <!-- Por Vencer (7 días) -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-danger">
                <div class="kpi-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="kpi-content">
                    <h2 class="kpi-number">{{ $porVencer7Dias }}</h2>
                    <p class="kpi-label">Por Vencer</p>
                    <small class="kpi-desc">Próximos 7 días</small>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 2: GRÁFICOS - ELEGANTE -->
    <div class="row mb-4">
        <!-- Gráfico Dona: Distribución Membresías -->
        <div class="col-md-6">
            <div class="card card-primary card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Distribución de Membresías
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-primary">{{ count($etiquetasMembresias) }}</span>
                    </div>
                </div>
                <div class="card-body elegant-chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartMembresias"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Barras: Ingresos -->
        <div class="col-md-6">
            <div class="card card-success card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Ingresos Últimos 6 Meses
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-success">Anual</span>
                    </div>
                </div>
                <div class="card-body elegant-chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartIngresos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 3: TABLAS SUPERIORES - ELEGANTE -->
    <div class="row mb-4">
        <!-- Clientes por Vencer (7 días) -->
        <div class="col-md-6">
            <div class="card card-warning card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Clientes por Vencer
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-warning">{{ $clientesPorVencer->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($clientesPorVencer->count() > 0)
                        <table class="table table-sm table-striped table-hover elegant-table">
                            <thead class="elegant-thead">
                                <tr>
                                    <th style="width: 40%;">Cliente</th>
                                    <th style="width: 30%;">Membresía</th>
                                    <th style="width: 20%;">Vencimiento</th>
                                    <th style="width: 10%; text-align: center;">Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientesPorVencer as $inscripcion)
                                    <tr class="elegant-row" onclick="window.location='{{ route('admin.inscripciones.show', $inscripcion) }}'" style="cursor: pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" class="elegant-link">
                                                <strong>{{ Str::limit($inscripcion->cliente->nombres, 18) }}</strong>
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                        <td style="text-align: center;">
                                            @php
                                                $dias = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                            @endphp
                                            @if($dias <= 2)
                                                <span class="badge badge-danger">{{ $dias }} d</span>
                                            @elseif($dias <= 5)
                                                <span class="badge badge-warning">{{ $dias }} d</span>
                                            @else
                                                <span class="badge badge-info">{{ $dias }} d</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-3 mb-0">
                            <i class="fas fa-check-circle"></i> Sin clientes por vencer en los próximos 7 días
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Membresías -->
        <div class="col-md-6">
            <div class="card card-info card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i> Top Membresías
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-info">{{ $topMembresias->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($topMembresias->count() > 0)
                        <table class="table table-sm elegant-table">
                            <thead class="elegant-thead">
                                <tr>
                                    <th style="width: 50%;">Membresía</th>
                                    <th style="width: 15%; text-align: center;">Inscritos</th>
                                    <th style="width: 35%;">Progreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topMembresias as $item)
                                    <tr class="elegant-row">
                                        <td><strong>{{ $item->membresia?->nombre }}</strong></td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-primary" style="font-size: 12px;">{{ $item->total }}</span>
                                        </td>
                                        <td>
                                            <div class="progress progress-sm elegant-progress">
                                                @php
                                                    $percentage = $maxMembresias > 0 ? ($item->total / $maxMembresias) * 100 : 0;
                                                @endphp
                                                <div class="progress-bar bg-success" style="width: {{ round($percentage) }}%; transition: width 0.6s ease;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-3 mb-0">
                            <i class="fas fa-info-circle"></i> Sin datos de membresías
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 4: TABLAS INFERIORES - ELEGANTE -->
    <div class="row">
        <!-- Últimos Pagos -->
        <div class="col-md-6">
            <div class="card card-success card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Últimos Pagos
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-success">{{ $ultimosPagos->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($ultimosPagos->count() > 0)
                        <table class="table table-sm table-striped table-hover elegant-table">
                            <thead class="elegant-thead">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ultimosPagos as $pago)
                                    <tr class="elegant-row" onclick="window.location='{{ route('admin.pagos.show', $pago) }}'" style="cursor: pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" class="elegant-link">
                                                {{ Str::limit($pago->inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><strong>${{ number_format($pago->monto_abonado, 0, '.', '.') }}</strong></td>
                                        <td><small>{{ $pago->metodoPago?->nombre }}</small></td>
                                        <td>
                                            @php
                                                $estadoColor = match($pago->estado?->codigo ?? 0) {
                                                    201 => 'success',
                                                    203 => 'warning',
                                                    202 => 'info',
                                                    204 => 'danger',
                                                    205 => 'secondary',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $estadoColor }}">{{ $pago->estado?->nombre }}</span>
                                        </td>
                                        <td><small>{{ $pago->fecha_pago->format('d/m H:i') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-3 mb-0">
                            <i class="fas fa-info-circle"></i> Sin pagos registrados
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Inscripciones Recientes -->
        <div class="col-md-6">
            <div class="card card-primary card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i> Inscripciones Recientes
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-primary">{{ $inscripcionesRecientes->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($inscripcionesRecientes->count() > 0)
                        <table class="table table-sm table-striped table-hover elegant-table">
                            <thead class="elegant-thead">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Membresía</th>
                                    <th>Estado</th>
                                    <th>Inicio</th>
                                    <th>Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inscripcionesRecientes as $inscripcion)
                                    <tr class="elegant-row" onclick="window.location='{{ route('admin.inscripciones.show', $inscripcion) }}'" style="cursor: pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" class="elegant-link">
                                                {{ Str::limit($inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td>
                                            @php
                                                $estadoInscripcionColor = match($inscripcion->id_estado) {
                                                    100 => 'success',
                                                    101 => 'warning',
                                                    102 => 'danger',
                                                    103 => 'secondary',
                                                    104 => 'orange',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            @if($estadoInscripcionColor === 'orange')
                                                <span class="badge" style="background-color: #fd7e14; color: white;">{{ $inscripcion->estado?->nombre }}</span>
                                            @else
                                                <span class="badge badge-{{ $estadoInscripcionColor }}">{{ $inscripcion->estado?->nombre }}</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-3 mb-0">
                            <i class="fas fa-info-circle"></i> Sin inscripciones recientes
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Datos para Chart.js
        const etiquetasMembresias = @json($etiquetasMembresias) || [];
        const datosMembresias = @json($datosMembresias) || [];
        const etiquetasIngresos = @json($etiquetasIngresos) || [];
        const datosIngresos = @json($datosIngresosBarras) || [];

        // Colores elegantes para Dona
        const coloresMembresias = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'];

        // ===== GRÁFICO DONA: Distribución Membresías =====
        setTimeout(function() {
            const ctxMembresias = document.getElementById('chartMembresias');
            if (ctxMembresias && etiquetasMembresias.length > 0) {
                new Chart(ctxMembresias.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: etiquetasMembresias,
                        datasets: [{
                            data: datosMembresias,
                            backgroundColor: coloresMembresias.slice(0, etiquetasMembresias.length),
                            borderColor: '#fff',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: { 
                                        size: 12, 
                                        weight: '600'
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            }
                        }
                    }
                });
            }
        }, 100);

        // ===== GRÁFICO BARRAS: Ingresos =====
        setTimeout(function() {
            const ctxIngresos = document.getElementById('chartIngresos');
            if (ctxIngresos && etiquetasIngresos.length > 0) {
                new Chart(ctxIngresos.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: etiquetasIngresos,
                        datasets: [{
                            label: 'Ingresos ($)',
                            data: datosIngresos,
                            backgroundColor: 'rgba(0, 123, 255, 0.8)',
                            borderColor: '#004085',
                            borderWidth: 1,
                            borderRadius: 8,
                            hoverBackgroundColor: 'rgba(0, 123, 255, 1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font: { size: 12, weight: '600' }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + (value / 1000).toFixed(0) + 'K';
                                    },
                                    font: { size: 11 }
                                }
                            },
                            x: {
                                ticks: {
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            }
        }, 100);
    </script>
@endpush

@push('css')
    <style>
        /* ========== KPI CARDS ELEGANTES ========== */
        .kpi-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border: none;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            position: relative;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .kpi-card:hover::before {
            transform: translateX(100%);
        }

        .kpi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.16);
        }

        .kpi-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .kpi-success {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .kpi-warning {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .kpi-danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .kpi-icon {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 15px;
            float: right;
        }

        .kpi-content {
            overflow: hidden;
        }

        .kpi-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
            margin-bottom: 8px;
        }

        .kpi-label {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            margin-bottom: 4px;
            opacity: 0.95;
        }

        .kpi-desc {
            font-size: 0.85rem;
            opacity: 0.8;
            display: block;
        }

        /* ========== TARJETAS ELEGANTES ========== */
        .elegant-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .elegant-card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .elegant-header {
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.02) 0%, rgba(0, 0, 0, 0.04) 100%);
            border-bottom: 2px solid rgba(0, 0, 0, 0.06);
            padding: 1rem !important;
        }

        .elegant-header .card-title {
            font-weight: 600;
            font-size: 1.15rem;
            color: #2c3e50;
        }

        /* ========== TABLAS ELEGANTES ========== */
        .elegant-table {
            margin-bottom: 0;
        }

        .elegant-thead {
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.03) 0%, rgba(0, 0, 0, 0.06) 100%);
            font-weight: 600;
            color: #2c3e50;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .elegant-thead th {
            border: none;
            padding: 15px 12px;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.03) 0%, rgba(0, 0, 0, 0.06) 100%);
        }

        .elegant-row {
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .elegant-row:hover {
            background-color: rgba(0, 123, 255, 0.05);
            transform: scale(1.01);
        }

        .elegant-row td {
            vertical-align: middle;
            padding: 12px;
        }

        .elegant-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .elegant-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        /* ========== PROGRESO ELEGANTE ========== */
        .elegant-progress {
            height: 6px;
            border-radius: 3px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .elegant-progress .progress-bar {
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.3);
        }

        /* ========== GRÁFICOS ELEGANTES ========== */
        .elegant-chart-container {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.95) 100%);
            border-radius: 8px;
            padding: 15px;
        }

        /* ========== BADGES ELEGANTES ========== */
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
        }

        /* ========== ANIMACIONES ========== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .kpi-card, .elegant-card {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .kpi-card:nth-child(1) { animation-delay: 0.1s; }
        .kpi-card:nth-child(2) { animation-delay: 0.2s; }
        .kpi-card:nth-child(3) { animation-delay: 0.3s; }
        .kpi-card:nth-child(4) { animation-delay: 0.4s; }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .kpi-number {
                font-size: 1.8rem;
            }

            .kpi-label {
                font-size: 0.95rem;
            }

            .elegant-table {
                font-size: 0.9rem;
            }
        }
    </style>
@endpush