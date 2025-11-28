@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0" style="font-size: 2rem; font-weight: 700; color: #2c3e50;">
                <i class="fas fa-chart-line" style="margin-right: 10px; color: #007bff;"></i> Dashboard Ejecutivo
            </h1>
            <small class="text-muted" style="font-size: 0.95rem;">Monitoreo en tiempo real</small>
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
    <!-- FILA 1: KPI CARDS (4 PRINCIPALES) -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card kpi-primary">
                <div class="kpi-icon"><i class="fas fa-users"></i></div>
                <div class="kpi-content">
                    <h2 class="kpi-number">{{ $totalClientes }}</h2>
                    <p class="kpi-label">Total Clientes</p>
                    <small class="kpi-desc">Registrados</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card kpi-success">
                <div class="kpi-icon"><i class="fas fa-dumbbell"></i></div>
                <div class="kpi-content">
                    <h2 class="kpi-number">{{ $inscripcionesActivas }}</h2>
                    <p class="kpi-label">Membresías Activas</p>
                    <small class="kpi-desc">En vigencia</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card kpi-warning">
                <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="kpi-content">
                    <h2 class="kpi-number">${{ number_format($ingresosMes, 0, '.', '.') }}</h2>
                    <p class="kpi-label">Ingresos Este Mes</p>
                    <small class="kpi-desc">Total generado</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card kpi-danger">
                <div class="kpi-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="kpi-content">
                    <h2 class="kpi-number">{{ $porVencer7Dias }}</h2>
                    <p class="kpi-label">Por Vencer</p>
                    <small class="kpi-desc">Próximos 7 días</small>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 2: RESUMEN DE ESTADOS -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $inscripcionesActivas }}</span>
                    <span class="info-box-text">Activas</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-pause-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $inscripcionesPausadas }}</span>
                    <span class="info-box-text">Pausadas</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $inscripcionesVencidas }}</span>
                    <span class="info-box-text">Vencidas</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="info-box bg-dark">
                <span class="info-box-icon"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $inscripcionesCanceladas + $inscripcionesSuspendidas }}</span>
                    <span class="info-box-text">Canceladas/Suspendidas</span>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 3: GRÁFICOS -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card card-primary card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Distribución de Membresías</h3>
                    <div class="card-tools pull-right"><span class="badge badge-primary">{{ count($etiquetasMembresias) }}</span></div>
                </div>
                <div class="card-body elegant-chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartMembresias"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-success card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Ingresos Últimos 6 Meses</h3>
                    <div class="card-tools pull-right"><span class="badge badge-success">Anual</span></div>
                </div>
                <div class="card-body elegant-chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartIngresos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 4: TABLAS PRINCIPALES -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card card-warning card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title"><i class="fas fa-clock"></i> Clientes por Vencer</h3>
                    <div class="card-tools pull-right"><span class="badge badge-warning">{{ $clientesPorVencer->count() }}</span></div>
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
                                    <tr class="elegant-row">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" class="elegant-link">
                                                {{ Str::limit($inscripcion->cliente->nombres, 18) }}
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                        <td style="text-align: center;">
                                            @php $dias = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false); @endphp
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
                        <div class="alert alert-info m-3 mb-0"><i class="fas fa-check-circle"></i> Sin clientes por vencer</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-secondary card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title"><i class="fas fa-credit-card"></i> Métodos de Pago Populares</h3>
                    <div class="card-tools pull-right"><span class="badge badge-secondary">{{ count($etiquetasMetodosPago) }}</span></div>
                </div>
                <div class="card-body elegant-chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartMetodosPago"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 5: ÚLTIMOS PAGOS + INSCRIPCIONES -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-success card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title"><i class="fas fa-receipt"></i> Últimos Pagos</h3>
                    <div class="card-tools pull-right"><span class="badge badge-success">{{ $ultimosPagos->count() }}</span></div>
                </div>
                <div class="card-body p-0">
                    @if($ultimosPagos->count() > 0)
                        <table class="table table-sm table-striped table-hover elegant-table">
                            <thead class="elegant-thead">
                                <tr><th>Cliente</th><th>Monto</th><th>Método</th><th>Fecha</th></tr>
                            </thead>
                            <tbody>
                                @foreach($ultimosPagos as $pago)
                                    <tr class="elegant-row">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" class="elegant-link">
                                                {{ Str::limit($pago->inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><strong>${{ number_format($pago->monto_abonado, 0, '.', '.') }}</strong></td>
                                        <td><small>{{ $pago->metodoPago?->nombre }}</small></td>
                                        <td><small>{{ $pago->fecha_pago->format('d/m H:i') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-3 mb-0"><i class="fas fa-info-circle"></i> Sin pagos registrados</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-primary card-outline elegant-card">
                <div class="card-header with-border elegant-header">
                    <h3 class="card-title"><i class="fas fa-plus-circle"></i> Inscripciones Recientes</h3>
                    <div class="card-tools pull-right"><span class="badge badge-primary">{{ $inscripcionesRecientes->count() }}</span></div>
                </div>
                <div class="card-body p-0">
                    @if($inscripcionesRecientes->count() > 0)
                        <table class="table table-sm table-striped table-hover elegant-table">
                            <thead class="elegant-thead">
                                <tr><th>Cliente</th><th>Membresía</th><th>Inicio</th><th>Vencimiento</th></tr>
                            </thead>
                            <tbody>
                                @foreach($inscripcionesRecientes as $inscripcion)
                                    <tr class="elegant-row">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" class="elegant-link">
                                                {{ Str::limit($inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-3 mb-0"><i class="fas fa-info-circle"></i> Sin inscripciones recientes</div>
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
        const etiquetasMembresias = @json($etiquetasMembresias) || [];
        const datosMembresias = @json($datosMembresias) || [];
        const etiquetasIngresos = @json($etiquetasIngresos) || [];
        const datosIngresos = @json($datosIngresosBarras) || [];
        const etiquetasMetodos = @json($etiquetasMetodosPago) || [];
        const datosMetodos = @json($datosMetodosPago) || [];
        const colores = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'];

        setTimeout(() => {
            if (document.getElementById('chartMembresias') && etiquetasMembresias.length > 0) {
                new Chart(document.getElementById('chartMembresias'), {
                    type: 'doughnut',
                    data: {
                        labels: etiquetasMembresias,
                        datasets: [{
                            data: datosMembresias,
                            backgroundColor: colores.slice(0, etiquetasMembresias.length),
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 15, font: { size: 11, weight: '600' } } }
                        }
                    }
                });
            }
        }, 50);

        setTimeout(() => {
            if (document.getElementById('chartIngresos') && etiquetasIngresos.length > 0) {
                new Chart(document.getElementById('chartIngresos'), {
                    type: 'bar',
                    data: {
                        labels: etiquetasIngresos,
                        datasets: [{
                            label: 'Ingresos ($)',
                            data: datosIngresos,
                            backgroundColor: '#28a745',
                            borderColor: '#1e7e34',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        }, 50);

        setTimeout(() => {
            if (document.getElementById('chartMetodosPago') && etiquetasMetodos.length > 0) {
                new Chart(document.getElementById('chartMetodosPago'), {
                    type: 'bar',
                    data: {
                        labels: etiquetasMetodos,
                        datasets: [{
                            label: 'Transacciones',
                            data: datosMetodos,
                            backgroundColor: '#6c757d',
                            borderColor: '#495057',
                            borderWidth: 1
                        }]
                    },
                    indexAxis: 'y',
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    }
                });
            }
        }, 50);
    </script>
@endpush

@push('css')
    <style>
        .kpi-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }
        .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; width: 5px; height: 100%; background: currentColor; opacity: 0.8; }
        .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12); border-color: currentColor; }
        .kpi-primary { color: #667eea; border-top-color: #667eea; }
        .kpi-success { color: #28a745; border-top-color: #28a745; }
        .kpi-warning { color: #4facfe; border-top-color: #4facfe; }
        .kpi-danger { color: #dc3545; border-top-color: #dc3545; }
        .kpi-icon { font-size: 40px; opacity: 0.15; margin-bottom: 10px; float: right; }
        .kpi-number { font-size: 2.2rem; font-weight: 700; margin: 0 0 6px 0; }
        .kpi-label { font-size: 0.95rem; font-weight: 600; margin: 0 0 3px 0; color: #2c3e50; }
        .kpi-desc { font-size: 0.8rem; color: #7f8c8d; }
        .elegant-card { border: none; border-radius: 12px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08); }
        .elegant-card:hover { box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12); transform: translateY(-2px); }
        .elegant-header { background: linear-gradient(90deg, rgba(0, 0, 0, 0.02) 0%, rgba(0, 0, 0, 0.04) 100%); border-bottom: 2px solid rgba(0, 0, 0, 0.06); }
        .elegant-header .card-title { font-weight: 600; font-size: 1.15rem; color: #2c3e50; }
        .elegant-table { margin-bottom: 0; }
        .elegant-thead { background: linear-gradient(90deg, rgba(0, 0, 0, 0.03) 0%, rgba(0, 0, 0, 0.06) 100%); font-weight: 600; color: #2c3e50; text-transform: uppercase; font-size: 0.85rem; }
        .elegant-thead th { border: none; padding: 15px 12px; }
        .elegant-row { transition: all 0.2s ease; border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
        .elegant-row:hover { background-color: rgba(0, 123, 255, 0.05); transform: scale(1.01); }
        .elegant-row td { vertical-align: middle; padding: 12px; }
        .elegant-link { color: #007bff; text-decoration: none; font-weight: 500; }
        .elegant-link:hover { color: #0056b3; text-decoration: underline; }
        .elegant-chart-container { background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.95) 100%); border-radius: 8px; padding: 15px; }
    </style>
@endpush
