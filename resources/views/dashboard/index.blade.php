@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 style="font-size: 2rem; font-weight: 700; color: #2c3e50; margin: 0;">
        Dashboard
    </h1>
@stop

@section('content')
<div class="container-fluid">
    
    <!-- ========== NIVEL 1: SALUD DEL NEGOCIO ========== -->
    <div class="row mb-4">
        <!-- Membresías Activas -->
        <div class="col-md-3">
            <div class="info-box" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 0; color: white; box-shadow: 0 3px 12px rgba(40, 167, 69, 0.3);">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $inscripcionesActivas }}</span>
                    <span class="info-box-text">Miembros Activos</span>
                    <small>Hoy</small>
                </div>
            </div>
        </div>

        <!-- Ingresos Este Mes -->
        <div class="col-md-3">
            <div class="info-box" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border: 0; color: white; box-shadow: 0 3px 12px rgba(0, 123, 255, 0.3);">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">${{ number_format($ingresosMes, 0, '.', '.') }}</span>
                    <span class="info-box-text">Ingresos Mes</span>
                    <small>Recaudado</small>
                </div>
            </div>
        </div>

        <!-- Clientes Nuevos Este Mes -->
        <div class="col-md-3">
            <div class="info-box" style="background: linear-gradient(135deg, #17a2b8 0%, #0c5460 100%); border: 0; color: white; box-shadow: 0 3px 12px rgba(23, 162, 184, 0.3);">
                <span class="info-box-icon"><i class="fas fa-user-plus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $totalInscripcionesEsteMes }}</span>
                    <span class="info-box-text">Nuevos Clientes</span>
                    <small>Este mes</small>
                </div>
            </div>
        </div>

        <!-- Total Clientes Base -->
        <div class="col-md-3">
            <div class="info-box" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border: 0; color: white; box-shadow: 0 3px 12px rgba(108, 117, 125, 0.3);">
                <span class="info-box-icon"><i class="fas fa-database"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">{{ $totalClientes }}</span>
                    <span class="info-box-text">Total Registrados</span>
                    <small>Base de datos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MÉTRICAS OPERACIONALES ========== -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card" style="border-top: 4px solid #667eea; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 10px 12px;">
                    <h6 class="card-title mb-0" style="color: #667eea; font-weight: 600; font-size: 0.9rem;">
                        <i class="fas fa-calculator"></i> Ticket Promedio
                    </h6>
                </div>
                <div class="card-body text-center" style="padding: 15px;">
                    <div style="font-size: 1.8rem; font-weight: 700; color: #667eea;">${{ number_format($ticketPromedio, 0, '.', '.') }}</div>
                    <small style="color: #6c757d;">monto promedio por pago</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card" style="border-top: 4px solid #20c997; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 10px 12px;">
                    <h6 class="card-title mb-0" style="color: #20c997; font-weight: 600; font-size: 0.9rem;">
                        <i class="fas fa-percent"></i> Tasa de Cobranza
                    </h6>
                </div>
                <div class="card-body text-center" style="padding: 15px;">
                    <div style="font-size: 1.8rem; font-weight: 700; color: #20c997;">{{ round($tasaCobranza, 1) }}%</div>
                    <small style="color: #6c757d;">pagos completados</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card" style="border-top: 4px solid #fd7e14; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 10px 12px;">
                    <h6 class="card-title mb-0" style="color: #fd7e14; font-weight: 600; font-size: 0.9rem;">
                        <i class="fas fa-chart-pie"></i> Conversión Mes
                    </h6>
                </div>
                <div class="card-body text-center" style="padding: 15px;">
                    <div style="font-size: 1.8rem; font-weight: 700; color: #fd7e14;">{{ round($tasaConversion, 1) }}%</div>
                    <small style="color: #6c757d;">nuevos / activos</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card" style="border-top: 4px solid #0dcaf0; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 10px 12px;">
                    <h6 class="card-title mb-0" style="color: #0dcaf0; font-weight: 600; font-size: 0.9rem;">
                        <i class="fas fa-user-check"></i> Ingresos Promedio
                    </h6>
                </div>
                <div class="card-body text-center" style="padding: 15px;">
                    <div style="font-size: 1.8rem; font-weight: 700; color: #0dcaf0;">${{ number_format($inscripcionesActivas > 0 ? $ingresosMes / $inscripcionesActivas : 0, 0, '.', '.') }}</div>
                    <small style="color: #6c757d;">ingresos / miembro</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <!-- Pagos Vencidos (Crítico) -->
        <div class="col-lg-6">
            <div class="card" style="border-top: 4px solid #dc3545; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #dc3545; font-weight: 600;">
                        <i class="fas fa-exclamation-circle"></i> Pagos Vencidos
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div style="padding: 15px;">
                        <div style="font-size: 2.5rem; font-weight: 700; color: #dc3545;">{{ $pagosVencidos }}</div>
                        <small style="color: #6c757d;">pagos sin cobrar</small>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e9ecef;">
                            <small style="color: #6c757d;">Monto en riesgo:</small>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #dc3545;">${{ number_format($montoPagosVencidos, 0, '.', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Membresía con Más Ingresos -->
        <div class="col-lg-6">
            <div class="card" style="border-top: 4px solid #28a745; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #28a745; font-weight: 600;">
                        <i class="fas fa-money-bill-wave"></i> Top Membresías por Ingresos
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($membresiasIngresos->count() > 0)
                        @foreach($membresiasIngresos as $item)
                            <div style="padding: 12px 15px; border-bottom: 1px solid #f0f0f0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong>{{ $item->nombre }}</strong>
                                    <span style="color: #28a745; font-weight: 700; font-size: 1.1rem;">${{ number_format($item->totalIngresos, 0, '.', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="padding: 20px; text-align: center; color: #999;">Sin datos</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Vencen Esta Semana -->
        <div class="col-lg-6">
            <div class="card" style="border-top: 4px solid #ffc107; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #ffc107; font-weight: 600;">
                        <i class="fas fa-hourglass-half"></i> Membresías Vencen en 7d
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div style="padding: 15px;">
                        <div style="font-size: 2.5rem; font-weight: 700; color: #ffc107;">{{ $porVencer7Dias }}</div>
                        <small style="color: #6c757d;">clientes requieren renovación</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparativas vs Mes Anterior -->
        <div class="col-lg-6">
            <div class="card" style="border-top: 4px solid #17a2b8; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #17a2b8; font-weight: 600;">
                        <i class="fas fa-chart-bar"></i> Comparativa vs Mes Anterior
                    </h6>
                </div>
                <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 15px;">
                    <div style="text-align: center; padding: 12px; background: @if($variacionIngresos >= 0) #e8f5e9 @else #ffebee @endif; border-radius: 6px;">
                        <small style="color: #666;">Ingresos</small>
                        <div style="font-size: 1.5rem; font-weight: 700; color: @if($variacionIngresos >= 0) #28a745 @else #dc3545 @endif;">
                            @if($variacionIngresos >= 0) ↑ @else ↓ @endif {{ abs(round($variacionIngresos, 1)) }}%
                        </div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: @if($variacionClientes >= 0) #e8f5e9 @else #ffebee @endif; border-radius: 6px;">
                        <small style="color: #666;">Clientes Nuevos</small>
                        <div style="font-size: 1.5rem; font-weight: 700; color: @if($variacionClientes >= 0) #28a745 @else #dc3545 @endif;">
                            @if($variacionClientes >= 0) ↑ @else ↓ @endif {{ abs(round($variacionClientes, 1)) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== DETALLE DE CLIENTES POR CONTACTAR ========== -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card" style="border-top: 4px solid #dc3545; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #dc3545; font-weight: 600;">
                        <i class="fas fa-phone"></i> Detalle: Clientes para Contactar Esta Semana
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($clientesPorVencer->count() > 0)
                        <table class="table table-sm mb-0" style="border: none;">
                            <thead>
                                <tr style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057;">Cliente</th>
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057;">Membresía</th>
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057;">Teléfono</th>
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057;">Vence en</th>
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057; text-align: center;">Prioridad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientesPorVencer->take(15) as $inscripcion)
                                    @php $dias = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false); @endphp
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td style="padding: 12px 15px;">
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" style="color: #007bff; text-decoration: none; font-weight: 500;">
                                                {{ $inscripcion->cliente->nombres }}
                                            </a>
                                        </td>
                                        <td style="padding: 12px 15px; font-size: 0.9rem;">{{ $inscripcion->membresia?->nombre }}</td>
                                        <td style="padding: 12px 15px; font-size: 0.9rem;">{{ $inscripcion->cliente->telefono ?? 'N/A' }}</td>
                                        <td style="padding: 12px 15px;">{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                                        <td style="padding: 12px 15px; text-align: center;">
                                            @if($dias <= 2)
                                                <span style="background: #dc3545; color: white; padding: 6px 14px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">CRÍTICO ({{ $dias }}d)</span>
                                            @elseif($dias <= 5)
                                                <span style="background: #ffc107; color: #333; padding: 6px 14px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">ALTO ({{ $dias }}d)</span>
                                            @else
                                                <span style="background: #17a2b8; color: white; padding: 6px 14px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">Normal ({{ $dias }}d)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding: 40px; text-align: center; color: #28a745;">
                            <i class="fas fa-check-circle" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                            <strong style="font-size: 1.1rem;">¡Excelente!</strong><br>
                            <small>No hay clientes con vencimientos próximos</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Gráfico Ingresos -->
        <div class="col-lg-8">
            <div class="card" style="border-top: 4px solid #28a745; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #28a745; font-weight: 600;">
                        <i class="fas fa-chart-line"></i> Ingresos - Últimos 6 Meses
                    </h6>
                </div>
                <div class="card-body" style="height: 300px; position: relative;">
                    <canvas id="chartIngresos"></canvas>
                </div>
            </div>
        </div>

        <!-- Estado General -->
        <div class="col-lg-4">
            <div class="card" style="border-top: 4px solid #007bff; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #007bff; font-weight: 600;">
                        <i class="fas fa-chart-bar"></i> Estado de Inscripciones
                    </h6>
                </div>
                <div class="card-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="text-align: center; padding: 15px; background: #e8f5e9; border-radius: 6px;">
                        <div style="font-size: 1.8rem; font-weight: 700; color: #28a745;">{{ $inscripcionesActivas }}</div>
                        <small style="color: #666;">Activas</small>
                    </div>
                    <div style="text-align: center; padding: 15px; background: #fff3cd; border-radius: 6px;">
                        <div style="font-size: 1.8rem; font-weight: 700; color: #ffc107;">{{ $inscripcionesPausadas }}</div>
                        <small style="color: #666;">Pausadas</small>
                    </div>
                    <div style="text-align: center; padding: 15px; background: #ffebee; border-radius: 6px;">
                        <div style="font-size: 1.8rem; font-weight: 700; color: #dc3545;">{{ $inscripcionesVencidas }}</div>
                        <small style="color: #666;">Vencidas</small>
                    </div>
                    <div style="text-align: center; padding: 15px; background: #f5f5f5; border-radius: 6px;">
                        <div style="font-size: 1.8rem; font-weight: 700; color: #6c757d;">{{ $inscripcionesCanceladas + $inscripcionesSuspendidas }}</div>
                        <small style="color: #666;">Canceladas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== NIVEL 4: TOP MEMBRESÍAS ========== -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card" style="border-top: 4px solid #17a2b8; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #17a2b8; font-weight: 600;">
                        <i class="fas fa-star"></i> Membresías Más Vendidas
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($topMembresias->count() > 0)
                        @php $total = $topMembresias->sum('total'); @endphp
                        @foreach($topMembresias as $item)
                            @php $pct = $total > 0 ? ($item->total / $total) * 100 : 0; @endphp
                            <div style="padding: 15px; border-bottom: 1px solid #f0f0f0;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <strong>{{ $item->membresia?->nombre }}</strong>
                                    <span style="color: #17a2b8; font-weight: 600;">{{ $item->total }} inscritos</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ $pct }}%; background: linear-gradient(90deg, #17a2b8, #20c997);"></div>
                                </div>
                                <small style="color: #6c757d;">{{ round($pct, 1) }}% del total</small>
                            </div>
                        @endforeach
                    @else
                        <div style="padding: 30px; text-align: center; color: #999;">Sin datos</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Métodos de Pago -->
        <div class="col-lg-6">
            <div class="card" style="border-top: 4px solid #6c757d; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #6c757d; font-weight: 600;">
                        <i class="fas fa-credit-card"></i> Métodos de Pago Usados
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(count($etiquetasMetodosPago) > 0)
                        @php $totalMetodos = array_sum($datosMetodosPago); @endphp
                        @foreach(array_combine($etiquetasMetodosPago, $datosMetodosPago) as $metodo => $count)
                            @php $pct = $totalMetodos > 0 ? ($count / $totalMetodos) * 100 : 0; @endphp
                            <div style="padding: 15px; border-bottom: 1px solid #f0f0f0;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <strong>{{ $metodo }}</strong>
                                    <span style="color: #6c757d; font-weight: 600;">{{ $count }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ $pct }}%; background: #6c757d;"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="padding: 30px; text-align: center; color: #999;">Sin datos</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ========== NIVEL 5: REFERENCIAS ========== -->
    <div class="row">
        <!-- Últimos Pagos -->
        <div class="col-lg-12">
            <div class="card" style="border-top: 4px solid #28a745; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 15px;">
                    <h6 class="card-title mb-0" style="color: #28a745; font-weight: 600;">
                        <i class="fas fa-history"></i> Últimos Pagos Registrados
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($ultimosPagos->count() > 0)
                        <table class="table table-sm mb-0" style="border: none;">
                            <thead>
                                <tr style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057;">Cliente</th>
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057;">Membresía</th>
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057;">Monto</th>
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057;">Método</th>
                                    <th style="padding: 12px 15px; font-weight: 600; color: #495057; text-align: right;">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ultimosPagos->take(15) as $pago)
                                    <tr style="border-bottom: 1px solid #f0f0f0; hover: { background: #f8f9fa; }">
                                        <td style="padding: 12px 15px;">
                                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" style="color: #007bff; text-decoration: none;">
                                                {{ Str::limit($pago->inscripcion->cliente->nombres, 18) }}
                                            </a>
                                        </td>
                                        <td style="padding: 12px 15px; font-size: 0.9rem;">{{ $pago->inscripcion->membresia?->nombre }}</td>
                                        <td style="padding: 12px 15px; font-weight: 600; color: #28a745;">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</td>
                                        <td style="padding: 12px 15px; font-size: 0.9rem;">{{ $pago->metodoPago?->nombre }}</td>
                                        <td style="padding: 12px 15px; font-size: 0.9rem; text-align: right;">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding: 30px; text-align: center; color: #999;">Sin pagos registrados</div>
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
        const etiquetasIngresos = @json($etiquetasIngresos) || [];
        const datosIngresos = @json($datosIngresosBarras) || [];

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
                            backgroundColor: 'rgba(40, 167, 69, 0.08)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#28a745',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'top', labels: { usePointStyle: true, padding: 15 } }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }
                        }
                    }
                });
            }
        }, 100);
    </script>
@endpush
