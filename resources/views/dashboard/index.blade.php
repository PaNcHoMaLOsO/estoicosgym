@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
    <div class="row mb-3">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-chart-line"></i> Dashboard Ejecutivo
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <small class="text-muted">Actualizado: {{ now()->format('d/m/Y H:i') }}</small>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- FILA 1: KPI CARDS -->
    <div class="row mb-3">
        <!-- Total Clientes -->
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalClientes }}</h3>
                    <p><i class="fas fa-users"></i> Total Clientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.clientes.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Inscripciones Activas -->
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $inscripcionesActivas }}</h3>
                    <p><i class="fas fa-user-check"></i> Inscripciones Activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('admin.inscripciones.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Ingresos del Mes -->
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($ingresosMes, 0, '.', '.') }}</h3>
                    <p><i class="fas fa-dollar-sign"></i> Ingresos del Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('admin.pagos.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Por Vencer (7 días) -->
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $porVencer7Dias }}</h3>
                    <p><i class="fas fa-exclamation-triangle"></i> Por Vencer (7 días)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('admin.inscripciones.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- FILA 2: GRÁFICOS -->
    <div class="row mb-3">
        <!-- Gráfico Dona: Distribución Membresías -->
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Distribución de Membresías
                    </h3>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                    <canvas id="chartMembresias" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Barras: Ingresos -->
        <div class="col-md-6">
            <div class="card card-success card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Ingresos Últimos 6 Meses
                    </h3>
                </div>
                <div class="card-body" style="position: relative; height: 300px;">
                    <canvas id="chartIngresos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 3: TABLAS SUPERIORES -->
    <div class="row mb-3">
        <!-- Clientes por Vencer (7 días) -->
        <div class="col-md-6">
            <div class="card card-warning card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Clientes por Vencer (7 días)
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-warning">{{ $clientesPorVencer->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($clientesPorVencer->count() > 0)
                        <table class="table table-sm table-striped table-hover">
                            <thead class="bg-warning">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Membresía</th>
                                    <th>Vencimiento</th>
                                    <th>Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientesPorVencer as $inscripcion)
                                    <tr onclick="window.location='{{ route('admin.inscripciones.show', $inscripcion) }}'" style="cursor: pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                                <strong>{{ Str::limit($inscripcion->cliente->nombres, 18) }}</strong>
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                        <td>
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
            <div class="card card-info card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i> Top Membresías
                    </h3>
                    <div class="card-tools pull-right">
                        <span class="badge badge-info">{{ $topMembresias->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($topMembresias->count() > 0)
                        <table class="table table-sm table-striped table-hover">
                            <thead class="bg-info">
                                <tr>
                                    <th>Membresía</th>
                                    <th>Inscritos</th>
                                    <th>Progreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topMembresias as $item)
                                    <tr>
                                        <td><strong>{{ $item->membresia?->nombre }}</strong></td>
                                        <td>
                                            <span class="badge badge-primary">{{ $item->total }}</span>
                                        </td>
                                        <td>
                                            <div class="progress progress-xs">
                                                @php
                                                    $percentage = $maxMembresias > 0 ? ($item->total / $maxMembresias) * 100 : 0;
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
                            <i class="fas fa-info-circle"></i> Sin datos de membresías
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 4: TABLAS INFERIORES -->
    <div class="row">
        <!-- Últimos Pagos -->
        <div class="col-md-6">
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
                        <table class="table table-sm table-striped table-hover">
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
                                    <tr onclick="window.location='{{ route('admin.pagos.show', $pago) }}'" style="cursor: pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}">
                                                {{ Str::limit($pago->inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><strong>${{ number_format($pago->monto_abonado, 0, '.', '.') }}</strong></td>
                                        <td><small>{{ $pago->metodoPago?->nombre }}</small></td>
                                        <td>
                                            @php
                                                $estadoColor = match($pago->estado?->codigo) {
                                                    201 => 'success',    // Pagado
                                                    203 => 'warning',    // Pendiente
                                                    202 => 'info',       // Parcial
                                                    204 => 'danger',     // Vencido
                                                    205 => 'secondary',  // Cancelado
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
                        <table class="table table-sm table-striped table-hover">
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
                                    <tr onclick="window.location='{{ route('admin.inscripciones.show', $inscripcion) }}'" style="cursor: pointer;">
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                                {{ Str::limit($inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                        <td>
                                            @php
                                                $estadoInscripcionColor = match($inscripcion->id_estado) {
                                                    100 => 'success',    // Activa
                                                    101 => 'warning',    // Pausada
                                                    102 => 'danger',     // Vencida
                                                    103 => 'secondary',  // Cancelada
                                                    104 => 'orange',     // Suspendida
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            @if($estadoInscripcionColor === 'orange')
                                                <span class="badge" style="background-color: #fd7e14;">{{ $inscripcion->estado?->nombre }}</span>
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

        // Colores para Dona
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
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: { size: 11, weight: '500' },
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            } else if (ctxMembresias) {
                ctxMembresias.parentElement.innerHTML = '<div class="alert alert-info">Sin datos de membresías</div>';
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
                            backgroundColor: '#007bff',
                            borderColor: '#004085',
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: undefined,
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
            } else if (ctxIngresos) {
                ctxIngresos.parentElement.innerHTML = '<div class="alert alert-info">Sin datos de ingresos</div>';
            }
        }, 100);
    </script>
@endpush

@push('css')
    <style>
        .small-box {
            transition: transform 0.2s ease;
        }
        .small-box:hover {
            transform: translateY(-3px);
        }
        .card {
            margin-bottom: 1rem;
        }
        .badge-orange {
            background-color: #fd7e14 !important;
            color: white;
        }
    </style>
@endpush
