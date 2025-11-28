@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1 class="m-0" style="font-size: 2.5rem; font-weight: 700; color: #2c3e50;">
                <i class="fas fa-tachometer-alt" style="color: #007bff;"></i> Dashboard
            </h1>
        </div>
        <div class="col-sm-6 text-right">
            <small class="text-muted">{{ now()->format('d/m/Y - H:i') }}</small>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- SECCIÓN 1: MÉTRICAS PRINCIPALES (Lo que importa AHORA) -->
    <div class="row mb-4">
        <!-- Membresías Activas HOY -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="info-box" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; color: white;">
                <span class="info-box-icon"><i class="fas fa-users-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $inscripcionesActivas }}</span>
                    <span class="info-box-text">Membresías Activas</span>
                    <small>Vigentes ahora</small>
                </div>
            </div>
        </div>

        <!-- Vencen Esta Semana (URGENTE) -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="info-box" style="background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%); border: none; color: white;">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $porVencer7Dias }}</span>
                    <span class="info-box-text">Vencen en 7 días</span>
                    <small>Necesitan renovar</small>
                </div>
            </div>
        </div>

        <!-- Ingresos Este Mes (Objetivo de ventas) -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="info-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none; color: white;">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">${{ number_format($ingresosMes, 0, '.', '.') }}</span>
                    <span class="info-box-text">Ingresos Este Mes</span>
                    <small>Recaudado</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: ESTADO GENERAL DEL NEGOCIO -->
    <div class="row mb-4">
        <!-- Resumen de Inscripciones -->
        <div class="col-lg-6">
            <div class="card" style="border-top: 4px solid #007bff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                    <h5 class="card-title mb-0" style="color: #2c3e50;">
                        <i class="fas fa-list"></i> Estado de Inscripciones
                    </h5>
                </div>
                <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="text-align: center; padding: 15px; background: #e8f5e9; border-radius: 8px;">
                        <div style="font-size: 32px; font-weight: 700; color: #28a745;">{{ $inscripcionesActivas }}</div>
                        <small style="color: #666;">Activas</small>
                    </div>
                    <div style="text-align: center; padding: 15px; background: #fff3cd; border-radius: 8px;">
                        <div style="font-size: 32px; font-weight: 700; color: #ffc107;">{{ $inscripcionesPausadas }}</div>
                        <small style="color: #666;">Pausadas</small>
                    </div>
                    <div style="text-align: center; padding: 15px; background: #ffebee; border-radius: 8px;">
                        <div style="font-size: 32px; font-weight: 700; color: #dc3545;">{{ $inscripcionesVencidas }}</div>
                        <small style="color: #666;">Vencidas</small>
                    </div>
                    <div style="text-align: center; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                        <div style="font-size: 32px; font-weight: 700; color: #6c757d;">{{ $inscripcionesCanceladas + $inscripcionesSuspendidas }}</div>
                        <small style="color: #666;">Canceladas</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución de Membresías (Qué vende más) -->
        <div class="col-lg-6">
            <div class="card" style="border-top: 4px solid #17a2b8; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                    <h5 class="card-title mb-0" style="color: #2c3e50;">
                        <i class="fas fa-chart-pie"></i> Top Membresías
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($topMembresias->count() > 0)
                        @php $total = $topMembresias->sum('total'); @endphp
                        @foreach($topMembresias->take(5) as $item)
                            @php $pct = $total > 0 ? ($item->total / $total) * 100 : 0; @endphp
                            <div style="padding: 12px 15px; border-bottom: 1px solid #f0f0f0;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <strong style="font-size: 0.95rem;">{{ $item->membresia?->nombre }}</strong>
                                    <span style="font-weight: 700; color: #17a2b8; font-size: 1.1rem;">{{ $item->total }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ $pct }}%; background: linear-gradient(90deg, #17a2b8, #20c997);"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="padding: 20px; text-align: center; color: #999;">Sin datos</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: GRÁFICOS DE ANÁLISIS -->
    <div class="row mb-4">
        <!-- Gráfico: Ingresos últimos meses -->
        <div class="col-lg-8">
            <div class="card" style="border-top: 4px solid #28a745; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                    <h5 class="card-title mb-0" style="color: #2c3e50;">
                        <i class="fas fa-chart-line"></i> Ingresos - Últimos 6 Meses
                    </h5>
                </div>
                <div class="card-body" style="position: relative; height: 300px; padding: 0;">
                    <canvas id="chartIngresos" style="padding: 15px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico: Distribución membresías (Dona) -->
        <div class="col-lg-4">
            <div class="card" style="border-top: 4px solid #007bff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                    <h5 class="card-title mb-0" style="color: #2c3e50;">
                        <i class="fas fa-circle"></i> Distribución
                    </h5>
                </div>
                <div class="card-body" style="position: relative; height: 300px; padding: 0;">
                    <canvas id="chartMembresias" style="padding: 15px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 4: ACCIONES NECESARIAS (Lo urgente) -->
    <div class="row mb-4">
        <!-- Clientes que vencen esta semana -->
        <div class="col-md-6">
            <div class="card" style="border-top: 4px solid #dc3545; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                    <h5 class="card-title mb-0" style="color: #dc3545;">
                        <i class="fas fa-bell"></i> Contactar Esta Semana
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($clientesPorVencer->count() > 0)
                        <table class="table table-sm mb-0" style="border: none;">
                            <thead>
                                <tr style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                                    <th style="padding: 10px 15px; font-weight: 600; color: #495057;">Cliente</th>
                                    <th style="padding: 10px 15px; font-weight: 600; color: #495057;">Membresía</th>
                                    <th style="padding: 10px 15px; font-weight: 600; color: #495057; text-align: center;">Vence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientesPorVencer->take(8) as $inscripcion)
                                    @php $dias = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false); @endphp
                                    <tr style="border-bottom: 1px solid #f0f0f0; hover: { background: #f8f9fa; }">
                                        <td style="padding: 12px 15px;">
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" style="color: #007bff; text-decoration: none; font-weight: 500;">
                                                {{ Str::limit($inscripcion->cliente->nombres, 16) }}
                                            </a>
                                        </td>
                                        <td style="padding: 12px 15px; font-size: 0.9rem;">{{ $inscripcion->membresia?->nombre }}</td>
                                        <td style="padding: 12px 15px; text-align: center;">
                                            @if($dias <= 2)
                                                <span style="background: #dc3545; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">{{ $dias }}d</span>
                                            @elseif($dias <= 5)
                                                <span style="background: #ffc107; color: #333; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">{{ $dias }}d</span>
                                            @else
                                                <span style="background: #17a2b8; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">{{ $dias }}d</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding: 20px; text-align: center; color: #999;">
                            <i class="fas fa-check-circle"></i> Excelente! Sin clientes por vencer
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Últimos pagos -->
        <div class="col-md-6">
            <div class="card" style="border-top: 4px solid #28a745; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                    <h5 class="card-title mb-0" style="color: #28a745;">
                        <i class="fas fa-money-bill"></i> Últimos Pagos
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($ultimosPagos->count() > 0)
                        <table class="table table-sm mb-0" style="border: none;">
                            <thead>
                                <tr style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                                    <th style="padding: 10px 15px; font-weight: 600; color: #495057;">Cliente</th>
                                    <th style="padding: 10px 15px; font-weight: 600; color: #495057;">Monto</th>
                                    <th style="padding: 10px 15px; font-weight: 600; color: #495057;">Método</th>
                                    <th style="padding: 10px 15px; font-weight: 600; color: #495057;">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ultimosPagos->take(8) as $pago)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td style="padding: 12px 15px; font-size: 0.9rem;">
                                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" style="color: #007bff; text-decoration: none;">
                                                {{ Str::limit($pago->inscripcion->cliente->nombres, 14) }}
                                            </a>
                                        </td>
                                        <td style="padding: 12px 15px; font-weight: 600; color: #28a745;">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</td>
                                        <td style="padding: 12px 15px; font-size: 0.85rem;">{{ $pago->metodoPago?->nombre }}</td>
                                        <td style="padding: 12px 15px; font-size: 0.85rem;">{{ $pago->fecha_pago->format('d/m H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding: 20px; text-align: center; color: #999;">Sin pagos registrados</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 5: INFORMACIÓN GENERAL (Para referencia) -->
    <div class="row">
        <!-- Total de clientes -->
        <div class="col-md-3">
            <div class="card" style="border-top: 4px solid #667eea; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-body text-center">
                    <div style="font-size: 28px; font-weight: 700; color: #667eea;">{{ $totalClientes }}</div>
                    <small style="color: #999;">Total Clientes</small>
                </div>
            </div>
        </div>

        <!-- Métodos de pago populares -->
        <div class="col-md-3">
            <div class="card" style="border-top: 4px solid #6c757d; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-body p-0">
                    <div style="padding: 15px;">
                        <h6 style="font-weight: 600; color: #2c3e50; margin-bottom: 12px;">
                            <i class="fas fa-credit-card"></i> Métodos Populares
                        </h6>
                        @if(count($etiquetasMetodosPago) > 0)
                            @foreach(array_slice($etiquetasMetodosPago, 0, 3) as $idx => $metodo)
                                <div style="font-size: 0.85rem; margin-bottom: 6px; color: #666;">
                                    {{ $metodo }}: <strong style="color: #333;">{{ $datosMetodosPago[$idx] ?? 0 }}</strong>
                                </div>
                            @endforeach
                        @else
                            <small style="color: #999;">Sin datos</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Inscripciones nuevas este mes -->
        <div class="col-md-3">
            <div class="card" style="border-top: 4px solid #17a2b8; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-body text-center">
                    <div style="font-size: 28px; font-weight: 700; color: #17a2b8;">{{ $totalInscripcionesEsteMes }}</div>
                    <small style="color: #999;">Inscripciones (Mes)</small>
                </div>
            </div>
        </div>

        <!-- Tasa de retención -->
        <div class="col-md-3">
            <div class="card" style="border-top: 4px solid #ffc107; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div class="card-body text-center">
                    <div style="font-size: 28px; font-weight: 700; color: #ffc107;">{{ round($tasaConversion, 1) }}%</div>
                    <small style="color: #999;">Conversión</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        const etiquetasIngresos = @json($etiquetasIngresos) || [];
        const datosIngresos = @json($datosIngresosBarras) || [];
        const etiquetasMembresias = @json($etiquetasMembresias) || [];
        const datosMembresias = @json($datosMembresias) || [];
        const colores = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#fd7e14'];

        setTimeout(() => {
            if (document.getElementById('chartIngresos') && etiquetasIngresos.length > 0) {
                new Chart(document.getElementById('chartIngresos'), {
                    type: 'line',
                    data: {
                        labels: etiquetasIngresos,
                        datasets: [{
                            label: 'Ingresos ($)',
                            data: datosIngresos,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#28a745',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: true, position: 'top' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        }, 50);

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
                            legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }
                        }
                    }
                });
            }
        }, 50);
    </script>
@endpush
