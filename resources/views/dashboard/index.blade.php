@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="m-0" style="font-size: 1.8rem; font-weight: 600; color: #333;">
            <i class="fas fa-chart-line" style="color: #667eea;"></i> Dashboard
        </h1>
        <small class="text-muted">Actualizado: {{ now()->format('d/m/Y H:i') }}</small>
    </div>
@stop

@section('content')
<style>
    .dashboard-container {
        background: #f8f9fa;
        padding: 0;
    }

    /* ===== KPI CARDS - MINIMALISTAS ===== */
    .kpi-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        transition: all 0.25s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        height: 100%;
    }

    .kpi-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .kpi-value {
        font-size: 2em;
        font-weight: 700;
        color: #333;
        margin: 10px 0;
    }

    .kpi-label {
        font-size: 0.9em;
        color: #666;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .kpi-icon {
        font-size: 2.5em;
        opacity: 0.15;
        margin-bottom: 10px;
    }

    .kpi-footer {
        font-size: 0.85em;
        color: #999;
        margin-top: 10px;
        padding-top: 12px;
        border-top: 1px solid #f0f0f0;
    }

    .kpi-icon.primary { color: #667eea; }
    .kpi-icon.success { color: #28a745; }
    .kpi-icon.info { color: #17a2b8; }
    .kpi-icon.warning { color: #ffc107; }

    /* ===== CHART CARDS ===== */
    .chart-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .chart-title {
        font-size: 1.1em;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chart-title i {
        color: #667eea;
    }

    /* ===== TABLE CARDS ===== */
    .table-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .table-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
        font-weight: 600;
        font-size: 1.05em;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-card-header i {
        color: #667eea;
        margin-right: 8px;
    }

    .table-card-body {
        overflow-x: auto;
    }

    .table-card table {
        margin: 0;
        font-size: 0.95em;
    }

    .table-card thead {
        background: #f5f5f5;
        border-bottom: 2px solid #e0e0e0;
    }

    .table-card thead th {
        color: #555;
        font-weight: 600;
        padding: 12px 16px;
        border: none;
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-card tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .table-card tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table-card tbody td {
        padding: 12px 16px;
        vertical-align: middle;
    }

    .table-card .badge {
        font-size: 0.8em;
        padding: 4px 8px;
    }

    .table-card-footer {
        padding: 12px 20px;
        border-top: 1px solid #e0e0e0;
        background: #f8f9fa;
        text-align: right;
    }

    .table-card-footer a {
        font-size: 0.9em;
        text-decoration: none;
    }

    .empty-state {
        padding: 30px 20px;
        text-align: center;
        color: #999;
    }

    .empty-state i {
        font-size: 2.5em;
        opacity: 0.2;
        display: block;
        margin-bottom: 10px;
    }

    /* ===== BADGE COUNTER ===== */
    .badge-counter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 6px;
        background: #667eea;
        color: white;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 600;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .kpi-value {
            font-size: 1.5em;
        }
        .chart-card {
            margin-bottom: 20px;
        }
    }
</style>

<div class="dashboard-container">
    <!-- KPI CARDS -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <i class="fas fa-users kpi-icon primary"></i>
                <div class="kpi-label">Clientes</div>
                <div class="kpi-value">{{ $totalClientes }}</div>
                <div class="kpi-footer">{{ $clientesInactivos }} inactivos</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <i class="fas fa-dumbbell kpi-icon success"></i>
                <div class="kpi-label">Inscripciones Activas</div>
                <div class="kpi-value">{{ $totalInscripciones }}</div>
                <div class="kpi-footer">{{ $inscripcionesVencidas }} vencidas</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <i class="fas fa-calendar-check kpi-icon info"></i>
                <div class="kpi-label">Este Mes</div>
                <div class="kpi-value" style="font-size: 1.6em;">${{ number_format($pagosDelMes, 0, '.', '.') }}</div>
                <div class="kpi-footer">{{ now()->format('M Y') }}</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card">
                <i class="fas fa-money-bill-wave kpi-icon warning"></i>
                <div class="kpi-label">Ingresos Totales</div>
                <div class="kpi-value" style="font-size: 1.6em;">${{ number_format($ingresosTotales, 0, '.', '.') }}</div>
                <div class="kpi-footer">{{ $ultimosPagos->count() }} transacciones</div>
            </div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="fas fa-chart-bar"></i> Ingresos - Últimos 6 Meses
                </div>
                <canvas id="chartIngresos" height="70"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="fas fa-pie-chart"></i> Inscripciones por Estado
                </div>
                <canvas id="chartEstados" height="170"></canvas>
            </div>
        </div>
    </div>

    <!-- PRÓXIMAS A VENCER Y TOP MEMBRESÍAS -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="table-card">
                <div class="table-card-header">
                    <span><i class="fas fa-clock"></i> Próximas a Vencer (30 días)</span>
                    <span class="badge-counter">{{ $proximasAVencer->count() }}</span>
                </div>
                <div class="table-card-body">
                    @if($proximasAVencer->count() > 0)
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Membresía</th>
                                    <th>Vencimiento</th>
                                    <th>Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proximasAVencer as $inscripcion)
                                    @php
                                        $dias = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                    @endphp
                                    <tr onclick="window.location='{{ route('admin.inscripciones.show', $inscripcion) }}'">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" onclick="event.stopPropagation()">
                                                <strong>{{ Str::limit($inscripcion->cliente->nombres, 16) }}</strong>
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                        <td>
                                            @if($dias <= 5)
                                                <span class="badge bg-danger">{{ $dias }}d</span>
                                            @elseif($dias <= 14)
                                                <span class="badge bg-warning">{{ $dias }}d</span>
                                            @else
                                                <span class="badge bg-info">{{ $dias }}d</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>No hay inscripciones próximas a vencer</p>
                        </div>
                    @endif
                </div>
                @if($proximasAVencer->count() > 0)
                    <div class="table-card-footer">
                        <a href="{{ route('admin.inscripciones.index') }}" class="text-primary">Ver todas →</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="table-card">
                <div class="table-card-header">
                    <span><i class="fas fa-star"></i> Top Membresías</span>
                    <span class="badge-counter">{{ $membresiasVendidas->count() }}</span>
                </div>
                <div class="table-card-body">
                    @if($membresiasVendidas->count() > 0)
                        @php
                            $maxInscripciones = $membresiasVendidas->max('inscripciones_count') ?? 1;
                        @endphp
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Membresía</th>
                                    <th>Duración</th>
                                    <th style="text-align: center;">Inscritos</th>
                                    <th style="width: 100px;">Progreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($membresiasVendidas as $membresia)
                                    @php
                                        $percentage = $maxInscripciones > 0 ? ($membresia->inscripciones_count / $maxInscripciones) * 100 : 0;
                                    @endphp
                                    <tr onclick="window.location='{{ route('admin.membresias.show', $membresia) }}'">
                                        <td><strong>{{ $membresia->nombre }}</strong></td>
                                        <td><small>{{ $membresia->duracion_meses }} meses</small></td>
                                        <td style="text-align: center;">
                                            <span class="badge bg-primary">{{ $membresia->inscripciones_count }}</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: {{ round($percentage) }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p>Sin membresías registradas</p>
                        </div>
                    @endif
                </div>
                @if($membresiasVendidas->count() > 0)
                    <div class="table-card-footer">
                        <a href="{{ route('admin.membresias.index') }}" class="text-primary">Ver todas →</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ÚLTIMOS PAGOS Y INSCRIPCIONES RECIENTES -->
    <div class="row">
        <div class="col-lg-6">
            <div class="table-card">
                <div class="table-card-header">
                    <span><i class="fas fa-receipt"></i> Últimos Pagos</span>
                    <span class="badge-counter">{{ $ultimosPagos->count() }}</span>
                </div>
                <div class="table-card-body">
                    @if($ultimosPagos->count() > 0)
                        <table class="table table-hover table-sm">
                            <thead>
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
                                    <tr onclick="window.location='{{ route('admin.pagos.show', $pago) }}'">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" onclick="event.stopPropagation()">
                                                {{ Str::limit($pago->inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><strong>${{ number_format($pago->monto_abonado, 0, '.', '.') }}</strong></td>
                                        <td><small>{{ $pago->metodoPago?->nombre }}</small></td>
                                        <td><span class="badge bg-{{ $pago->estado?->color ?? 'secondary' }}">{{ $pago->estado?->nombre }}</span></td>
                                        <td><small>{{ $pago->fecha_pago->format('d/m H:i') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p>Sin pagos registrados</p>
                        </div>
                    @endif
                </div>
                @if($ultimosPagos->count() > 0)
                    <div class="table-card-footer">
                        <a href="{{ route('admin.pagos.index') }}" class="text-primary">Ver todas →</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="table-card">
                <div class="table-card-header">
                    <span><i class="fas fa-plus-circle"></i> Inscripciones Recientes</span>
                    <span class="badge-counter">{{ $inscripcionesRecientes->count() }}</span>
                </div>
                <div class="table-card-body">
                    @if($inscripcionesRecientes->count() > 0)
                        <table class="table table-hover table-sm">
                            <thead>
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
                                    <tr onclick="window.location='{{ route('admin.inscripciones.show', $inscripcion) }}'">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" onclick="event.stopPropagation()">
                                                {{ Str::limit($inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td><span class="badge bg-{{ $inscripcion->estado?->color ?? 'secondary' }}">{{ $inscripcion->estado?->nombre }}</span></td>
                                        <td><small>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p>Sin inscripciones recientes</p>
                        </div>
                    @endif
                </div>
                @if($inscripcionesRecientes->count() > 0)
                    <div class="table-card-footer">
                        <a href="{{ route('admin.inscripciones.index') }}" class="text-primary">Ver todas →</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        const ETIQUETAS_MESES = @json($etiquetasMeses);
        const DATOS_INGRESOS = @json($datosIngresos);
        const ETIQUETAS_ESTADOS = @json($etiquetasEstados);
        const DATOS_ESTADOS = @json($datosEstados);
        const COLORES_DISPUESTOS = @json($coloresDispuestos);

        // Gráfico de Ingresos por Mes
        const ctxIngresos = document.getElementById('chartIngresos').getContext('2d');
        new Chart(ctxIngresos, {
            type: 'bar',
            data: {
                labels: ETIQUETAS_MESES,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: DATOS_INGRESOS,
                    backgroundColor: '#667eea',
                    borderColor: '#667eea',
                    borderWidth: 0,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + (value / 1000).toFixed(0) + 'K';
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de Estados (Doughnut)
        const ctxEstados = document.getElementById('chartEstados').getContext('2d');
        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: ETIQUETAS_ESTADOS,
                datasets: [{
                    data: DATOS_ESTADOS,
                    backgroundColor: COLORES_DISPUESTOS,
                    borderColor: '#fff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection

@stop
    <!-- KPIs Principales -->
    <div class="row mb-4">
        <!-- Clientes Activos -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalClientes }}</h3>
                    <p><i class="fas fa-user-check"></i> Clientes Activos</p>
                    <small class="text-light">{{ $clientesInactivos }} inactivos</small>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.clientes.index') }}" class="small-box-footer">
                    Ir a Clientes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Inscripciones Activas -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalInscripciones }}</h3>
                    <p><i class="fas fa-check-circle"></i> Inscripciones Activas</p>
                    <small class="text-light">{{ $inscripcionesVencidas }} vencidas</small>
                </div>
                <div class="icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <a href="{{ route('admin.inscripciones.index') }}" class="small-box-footer">
                    Ir a Inscripciones <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Ingresos Este Mes -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($pagosDelMes, 0, '.', '.') }}</h3>
                    <p><i class="fas fa-calendar"></i> Este Mes</p>
                    <small class="text-light">{{ now()->format('M Y') }}</small>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <a href="{{ route('admin.pagos.index') }}" class="small-box-footer">
                    Ir a Pagos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Ingresos Totales -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($ingresosTotales, 0, '.', '.') }}</h3>
                    <p><i class="fas fa-chart-line"></i> Ingresos Totales</p>
                    <small class="text-dark">{{ $ultimosPagos->count() }} transacciones</small>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <a href="{{ route('admin.pagos.index') }}" class="small-box-footer">
                    Ir a Pagos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Gráficos y Análisis -->
    <div class="row mb-4">
        <!-- Gráfico de Ingresos por Mes -->
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Ingresos - Últimos 6 Meses
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="chartIngresos" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Inscripciones por Estado -->
        <div class="col-lg-4">
            <div class="card card-success card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-pie-chart"></i> Inscripciones por Estado
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="chartEstados" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Importante -->
    <div class="row mb-4">
        <!-- Próximas a Vencer -->
        <div class="col-lg-6">
            <div class="card card-warning card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Próximas a Vencer (30 días)
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-warning">{{ $proximasAVencer->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($proximasAVencer->count() > 0)
                        <table class="table table-sm table-hover table-striped">
                            <thead class="bg-warning">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Membresía</th>
                                    <th>Vencimiento</th>
                                    <th>Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proximasAVencer as $inscripcion)
                                    <tr onclick="window.location='{{ route('admin.inscripciones.show', $inscripcion) }}'" style="cursor:pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                                <strong>{{ Str::limit($inscripcion->cliente->nombres, 16) }}</strong>
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                        <td>
                                            @php
                                                $dias = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                            @endphp
                                            @if($dias <= 5)
                                                <span class="badge badge-danger">{{ $dias }} d</span>
                                            @elseif($dias <= 14)
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
                            <i class="fas fa-check-circle"></i> No hay inscripciones próximas a vencer
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-list"></i> Ver Todas
                    </a>
                </div>
            </div>
        </div>

        <!-- Membresías Más Vendidas -->
        <div class="col-lg-6">
            <div class="card card-info card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i> Top Membresías
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-info">{{ $membresiasVendidas->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($membresiasVendidas->count() > 0)
                        @php
                            $maxInscripciones = $membresiasVendidas->max('inscripciones_count') ?? 1;
                        @endphp
                        <table class="table table-sm table-hover table-striped">
                            <thead class="bg-info">
                                <tr>
                                    <th>Membresía</th>
                                    <th>Duración</th>
                                    <th>Inscritos</th>
                                    <th>Progreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($membresiasVendidas as $membresia)
                                    <tr onclick="window.location='{{ route('admin.membresias.show', $membresia) }}'" style="cursor:pointer;">
                                        <td><strong>{{ $membresia->nombre }}</strong></td>
                                        <td><small>{{ $membresia->duracion_meses }} meses</small></td>
                                        <td>
                                            <span class="badge badge-primary">{{ $membresia->inscripciones_count }}</span>
                                        </td>
                                        <td>
                                            <div class="progress progress-xs">
                                                @php
                                                    $percentage = $maxInscripciones > 0 ? ($membresia->inscripciones_count / $maxInscripciones) * 100 : 0;
                                                @endphp
                                                <div class="progress-bar bg-success" style="width: {{ round($percentage) }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-3 mb-0">
                            <i class="fas fa-info-circle"></i> Sin membresías registradas
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.membresias.index') }}" class="btn btn-sm btn-info">
                        <i class="fas fa-list"></i> Ver Todas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Transacciones e Inscripciones -->
    <div class="row">
        <!-- Últimos Pagos -->
        <div class="col-lg-6">
            <div class="card card-success card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Últimos Pagos
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-success">{{ $ultimosPagos->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($ultimosPagos->count() > 0)
                        <table class="table table-sm table-hover table-striped">
                            <thead class="bg-success">
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
                                    <tr onclick="window.location='{{ route('admin.pagos.show', $pago) }}'" style="cursor:pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}">
                                                {{ Str::limit($pago->inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><strong>${{ number_format($pago->monto_abonado, 0, '.', '.') }}</strong></td>
                                        <td><small>{{ $pago->metodoPago?->nombre }}</small></td>
                                        <td><span class="badge badge-{{ $pago->estado?->color ?? 'secondary' }}">{{ $pago->estado?->nombre }}</span></td>
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
                <div class="card-footer">
                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-list"></i> Ver Todos
                    </a>
                </div>
            </div>
        </div>

        <!-- Inscripciones Recientes -->
        <div class="col-lg-6">
            <div class="card card-primary card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i> Inscripciones Recientes
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-primary">{{ $inscripcionesRecientes->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($inscripcionesRecientes->count() > 0)
                        <table class="table table-sm table-hover table-striped">
                            <thead class="bg-primary">
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
                                    <tr onclick="window.location='{{ route('admin.inscripciones.show', $inscripcion) }}'" style="cursor:pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                                {{ Str::limit($inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td><span class="badge badge-{{ $inscripcion->estado?->color ?? 'secondary' }}">{{ $inscripcion->estado?->nombre }}</span></td>
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
                <div class="card-footer">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-list"></i> Ver Todas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        const ETIQUETAS_MESES = @json($etiquetasMeses);
        const DATOS_INGRESOS = @json($datosIngresos);
        const ETIQUETAS_ESTADOS = @json($etiquetasEstados);
        const DATOS_ESTADOS = @json($datosEstados);
        const COLORES_DISPUESTOS = @json($coloresDispuestos);

        // Gráfico de Ingresos por Mes
        const ctxIngresos = document.getElementById('chartIngresos').getContext('2d');
        new Chart(ctxIngresos, {
            type: 'bar',
            data: {
                labels: ETIQUETAS_MESES,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: DATOS_INGRESOS,
                    backgroundColor: '#007bff',
                    borderColor: '#004085',
                    borderWidth: 1,
                    borderRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + (value / 1000).toFixed(0) + 'K';
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Estados (Doughnut)
        const ctxEstados = document.getElementById('chartEstados').getContext('2d');
        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: ETIQUETAS_ESTADOS,
                datasets: [{
                    data: DATOS_ESTADOS,
                    backgroundColor: COLORES_DISPUESTOS,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
