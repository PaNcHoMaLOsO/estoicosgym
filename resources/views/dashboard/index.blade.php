@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
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

@stop
