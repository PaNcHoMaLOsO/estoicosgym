@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Dashboard Ejecutivo</h1>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- KPIs Principales -->
    <div class="row mb-4">
        <!-- Clientes -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalClientes }}</h3>
                    <p>Clientes Activos</p>
                    <small class="text-light">{{ $clientesInactivos }} inactivos</small>
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
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalInscripciones }}</h3>
                    <p>Inscripciones Activas</p>
                    <small class="text-light">{{ $inscripcionesVencidas }} vencidas</small>
                </div>
                <div class="icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <a href="{{ route('admin.inscripciones.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Ingresos Mes -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($pagosDelMes, 0) }}</h3>
                    <p>Ingresos Este Mes</p>
                    <small class="text-light">${{ number_format($pagosPendientes, 0) }} pendiente</small>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('admin.pagos.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Ingresos Totales -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($ingresosTotales, 0) }}</h3>
                    <p>Ingresos Totales</p>
                    <small class="text-light">{{ count($ultimosPagos) }} transacciones</small>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('admin.pagos.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Gráfico de Ingresos por Mes -->
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Ingresos - Últimos 6 Meses</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartIngresos" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Inscripciones por Estado -->
        <div class="col-lg-4">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">Inscripciones por Estado</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartEstados" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Importante -->
    <div class="row mb-4">
        <!-- Próximas a Vencer -->
        <div class="col-lg-6">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> Próximas a Vencer (30 días)
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Membresía</th>
                                <th>Vencimiento</th>
                                <th>Días</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proximasAVencer as $inscripcion)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($inscripcion->cliente->nombres, 15) }}</strong>
                                    </td>
                                    <td>{{ $inscripcion->membresia?->nombre }}</td>
                                    <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $dias = (int) now()->diffInDays($inscripcion->fecha_vencimiento);
                                        @endphp
                                        @if($dias <= 7)
                                            <span class="badge badge-danger">{{ $dias }} días</span>
                                        @elseif($dias <= 14)
                                            <span class="badge badge-warning">{{ $dias }} días</span>
                                        @else
                                            <span class="badge badge-info">{{ $dias }} días</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Sin inscripciones próximas a vencer</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-sm btn-warning">Ver todas</a>
                </div>
            </div>
        </div>

        <!-- Membresías Populares -->
        <div class="col-lg-6">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i> Membresías Más Vendidas
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Membresía</th>
                                <th>Duración</th>
                                <th>Inscripciones</th>
                                <th>Progreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $maxInscripciones = $membresiasVendidas->max('inscripciones_count') ?? 1;
                            @endphp
                            @forelse($membresiasVendidas as $membresia)
                                <tr>
                                    <td><strong>{{ $membresia->nombre }}</strong></td>
                                    <td>{{ $membresia->duracion_dias }} días</td>
                                    <td>{{ $membresia->inscripciones_count }}</td>
                                    <td>
                                        <div class="progress progress-xs">
                                            @php
                                                $percentage = $maxInscripciones > 0 ? ($membresia->inscripciones_count / $maxInscripciones) * 100 : 0;
                                                $widthStyle = round($percentage, 1);
                                            @endphp
                                            <!-- htmlhint - Blade template syntax -->
                                            <div class="progress-bar bg-success" style="width: {{ $widthStyle }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Sin membresías</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Transacciones e Inscripciones -->
    <div class="row">
        <!-- Últimos Pagos -->
        <div class="col-lg-6">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Últimos Pagos
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
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
                            @forelse($ultimosPagos as $pago)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}">
                                            {{ Str::limit($pago->inscripcion->cliente->nombres, 12) }}
                                        </a>
                                    </td>
                                    <td><strong>${{ number_format($pago->monto_abonado, 0) }}</strong></td>
                                    <td><small>{{ $pago->metodoPago?->nombre }}</small></td>
                                    <td>{!! \App\Helpers\EstadoHelper::badgeWithIcon($pago->estado) !!}</td>
                                    <td><small>{{ $pago->created_at->format('d/m H:i') }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Sin pagos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-sm btn-success">Ver todos</a>
                </div>
            </div>
        </div>

        <!-- Inscripciones Recientes -->
        <div class="col-lg-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i> Inscripciones Recientes
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
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
                            @forelse($inscripcionesRecientes as $inscripcion)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                            {{ Str::limit($inscripcion->cliente->nombres, 12) }}
                                        </a>
                                    </td>
                                    <td><small>{{ $inscripcion->membresia?->nombre }}</small></td>
                                    <td>{!! \App\Helpers\EstadoHelper::badgeWithIcon($inscripcion->estado) !!}</td>
                                    <td><small>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</small></td>
                                    <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Sin inscripciones recientes</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-sm btn-primary">Ver todas</a>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        /* eslint-disable no-unused-vars */
        // Variables globales Blade (validadas por Laravel)
        const ETIQUETAS_MESES = @json($etiquetasMeses);
        const DATOS_INGRESOS = @json($datosIngresos);
        const ETIQUETAS_ESTADOS = @json($etiquetasEstados);
        const DATOS_ESTADOS = @json($datosEstados);
        const COLORES_DISPUESTOS = @json($coloresDispuestos);
        /* eslint-enable no-unused-vars */

        // Gráfico de Ingresos
        const ctxIngresos = document.getElementById('chartIngresos').getContext('2d');
        const chartIngresos = new Chart(ctxIngresos, {
            type: 'line',
            data: {
                labels: ETIQUETAS_MESES,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: DATOS_INGRESOS,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#007bff',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    filler: {
                        propagate: true
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

        // Gráfico de Estados
        const ctxEstados = document.getElementById('chartEstados').getContext('2d');
        const chartEstados = new Chart(ctxEstados, {
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
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
@endsection

@stop
