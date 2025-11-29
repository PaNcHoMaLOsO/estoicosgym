@extends('adminlte::page')

@section('title', 'Inscripción - EstóicosGym')

@section('css')
<style>
    .hero-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }
    .hero-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    .hero-header-content { position: relative; z-index: 1; }
    .hero-title { font-size: 2.2em; font-weight: 700; margin-bottom: 10px; }
    .hero-subtitle { font-size: 1.1em; opacity: 0.95; }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        border: none;
        border-top: 4px solid #667eea;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        margin-bottom: 15px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .stat-card.primary { border-top-color: #667eea; }
    .stat-card.success { border-top-color: #28a745; }
    .stat-card.warning { border-top-color: #ffc107; }
    .stat-card.danger { border-top-color: #dc3545; }
    .stat-card.info { border-top-color: #17a2b8; }
    
    .stat-label { font-size: 0.75em; color: #6c757d; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-size: 1.8em; font-weight: 800; color: #333; margin-top: 8px; }
    .stat-value.success { color: #28a745; }
    .stat-value.danger { color: #dc3545; }
    .stat-value.warning { color: #ffc107; }
    .stat-value.info { color: #17a2b8; }
    .stat-value.primary { color: #667eea; }
    
    .section-title {
        font-size: 1.3em;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid #667eea;
        display: inline-block;
    }
    
    .membership-item {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }
    .membership-item.alternate {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .membership-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }
</style>
@stop

@section('content_header')
    <div class="hero-header">
        <div class="hero-header-content">
            <div class="row">
                <div class="col-md-8">
                    <div class="hero-title"><i class="fas fa-list-check"></i> Inscripción #{{ $inscripcion->id }}</div>
                    <div class="hero-subtitle">
                        <i class="fas fa-user-circle"></i> {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="btn btn-light btn-sm mr-2">
                        <i class="fas fa-pencil-alt"></i> Editar
                    </a>
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    @php
        $total = $inscripcion->precio_final ?? $inscripcion->precio_base;
        $pagos = $inscripcion->pagos()->sum('monto_abonado');
        $pendiente = max(0, $total - $pagos);
        $porcentaje = ($total > 0) ? round(($pagos / $total) * 100) : 0;
        $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
    @endphp

    <!-- ENCABEZADO HERO CON INFORMACIÓN GENERAL -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-3"><i class="fas fa-receipt"></i> Información General</h4>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <small style="opacity: 0.8;">ID Inscripción</small><br/>
                                    <strong style="font-size: 1.2em;">{{ $inscripcion->id }}</strong>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small style="opacity: 0.8;">Registrada</small><br/>
                                    <strong>{{ $inscripcion->created_at->format('d/m/Y H:i') }}</strong>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small style="opacity: 0.8;">Fecha Inscripción</small><br/>
                                    <strong>{{ $inscripcion->fecha_inscripcion ? $inscripcion->fecha_inscripcion->format('d/m/Y') : 'Sin definir' }}</strong>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small style="opacity: 0.8;">Última Actualización</small><br/>
                                    <strong>{{ $inscripcion->updated_at->format('d/m/Y H:i') }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="mb-3">
                                <small style="opacity: 0.8;">ESTADO GENERAL</small><br/>
                                <span class="badge p-3 mt-2" style="background-color: rgba(255,255,255,0.3); color: white; font-size: 16px; border: 2px solid white;">
                                    {{ $inscripcion->estado?->nombre ?? 'Sin estado' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 1: CLIENTE + ESTRELLAS KPI -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                <div class="card-body p-4">
                    <div class="mb-3 pb-3 border-bottom">
                        <h3 class="mb-1" style="color: #667eea;"><i class="fas fa-user-tie"></i> {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</h3>
                        <small class="text-muted">
                            <i class="fas fa-envelope"></i> {{ $inscripcion->cliente->email }} | 
                            <i class="fas fa-phone"></i> {{ $inscripcion->cliente->telefono ?? 'N/A' }}
                        </small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><i class="fas fa-layer-group" style="color: #667eea;"></i> <strong>Membresía</strong></p>
                            <h5 class="mb-0" style="color: #333;">{{ $inscripcion->membresia?->nombre ?? 'Sin membresía' }}</h5>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><i class="fas fa-calendar-check" style="color: #28a745;"></i> <strong>Período</strong></p>
                            <h5 class="mb-0" style="color: #333;">{{ $inscripcion->fecha_inicio->format('d/m/Y') }} - {{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4 text-center">
                    <h6 class="mb-3" style="opacity: 0.9;"><i class="fas fa-circle-notch"></i> ESTADO</h6>
                    <span class="badge p-3" style="background-color: rgba(255,255,255,0.3); color: white; font-size: 16px; border: 2px solid white;">
                        {{ $inscripcion->estado?->nombre ?? 'Sin estado' }}
                    </span>
                    <p class="mt-3 mb-0">
                        <small>
                            @if($diasRestantes > 0)
                                <span><i class="fas fa-check-circle"></i> {{ $diasRestantes }} días restantes</span>
                            @elseif($diasRestantes == 0)
                                <span><i class="fas fa-exclamation-circle"></i> Vence hoy</span>
                            @else
                                <span><i class="fas fa-times-circle"></i> Vencida</span>
                            @endif
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 2: DINERO -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card primary">
                <div class="stat-label"><i class="fas fa-tag"></i> Precio Base</div>
                <div class="stat-value primary">${{ number_format($inscripcion->precio_base, 0, '.', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success">
                <div class="stat-label"><i class="fas fa-check"></i> Precio Final</div>
                <div class="stat-value success">${{ number_format($total, 0, '.', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <div class="stat-label"><i class="fas fa-coins"></i> Pagado</div>
                <div class="stat-value info">${{ number_format($pagos, 0, '.', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card {{ $pendiente > 0 ? 'warning' : 'success' }}">
                <div class="stat-label"><i class="fas fa-hourglass-half"></i> Pendiente</div>
                <div class="stat-value {{ $pendiente > 0 ? 'warning' : 'success' }}">${{ number_format($pendiente, 0, '.', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- ROW 3: BARRA PROGRESO + DETALLES -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Progreso de Pago</h6>
                            <span class="badge badge-success">{{ $porcentaje }}%</span>
                        </div>
                        <div class="progress" style="height: 25px; border-radius: 12px;">
                            <div class="progress-bar" style="width: {{ $porcentaje }}%; background: linear-gradient(90deg, #28a745, #20c997);">
                                <span style="font-weight: 600; color: white; font-size: 12px;">{{ $porcentaje }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4 text-center">
                        <div class="col-md-4">
                            <p class="text-muted mb-1 small"><i class="fas fa-receipt"></i> Pagos</p>
                            <h5 class="mb-0">{{ $inscripcion->pagos->count() }}</h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1 small"><i class="fas fa-calendar"></i> Duración</p>
                            <h5 class="mb-0">{{ $inscripcion->fecha_inicio->diffInDays($inscripcion->fecha_vencimiento) }} días</h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1 small"><i class="fas fa-handshake"></i> Convenio</p>
                            <h5 class="mb-0">{{ $inscripcion->convenio?->nombre ?? 'N/A' }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 4: DESCUENTOS Y OBSERVACIONES -->
    <div class="row mb-4">
        @if($inscripcion->descuento_aplicado > 0)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="mb-3"><i class="fas fa-percent"></i> Descuento</h6>
                        <h4 class="text-danger mb-2">-${{ number_format($inscripcion->descuento_aplicado, 0, '.', '.') }}</h4>
                        @if($inscripcion->motivoDescuento)
                            <small class="text-muted">Motivo: <strong>{{ $inscripcion->motivoDescuento?->nombre ?? 'N/A' }}</strong></small>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($inscripcion->observaciones)
            <div class="col-md-{{ $inscripcion->descuento_aplicado > 0 ? '6' : '12' }} mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="mb-3"><i class="fas fa-sticky-note"></i> Observaciones</h6>
                        <p class="text-muted mb-0">{{ $inscripcion->observaciones }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- HISTORIAL DE PAGOS -->
    <div class="row mt-5 mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-title mb-0"><i class="fas fa-credit-card"></i> Historial de Pagos</h3>
                @if($pendiente > 0)
                    <a href="{{ route('admin.pagos.create', ['inscripcion_id' => $inscripcion->id]) }}" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Nuevo Pago
                    </a>
                @endif
            </div>

            @if($inscripcion->pagos->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                    <tr>
                                        <th class="border-0">Fecha</th>
                                        <th class="border-0">Monto</th>
                                        <th class="border-0">Método</th>
                                        <th class="border-0">Estado</th>
                                        <th class="border-0 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inscripcion->pagos->sortByDesc('fecha_pago') as $pagoItem)
                                        <tr>
                                            <td>
                                                <strong>{{ $pagoItem->fecha_pago->format('d/m/Y') }}</strong>
                                                <br><small class="text-muted">{{ $pagoItem->fecha_pago->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <span class="text-success font-weight-bold" style="font-size: 1.1em;">
                                                    ${{ number_format($pagoItem->monto_abonado, 0, '.', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $pagoItem->metodoPago->nombre ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge p-2" style="background-color: {{ $pagoItem->estado->color ?? '#6c757d' }}; color: white;">
                                                    {{ $pagoItem->estado->nombre ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.pagos.show', $pagoItem) }}" class="btn btn-sm btn-info" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.pagos.edit', $pagoItem) }}" class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Resumen de pagos -->
                <div class="alert alert-info mt-3" role="alert">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Total Pagado:</strong> ${{ number_format($pagos, 0, '.', '.') }} de ${{ number_format($total, 0, '.', '.') }}
                    @if($pendiente > 0)
                        | <strong class="text-warning">Pendiente:</strong> ${{ number_format($pendiente, 0, '.', '.') }}
                    @else
                        | <strong class="text-success"><i class="fas fa-check-circle"></i> Completamente Pagado</strong>
                    @endif
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> No hay pagos registrados para esta inscripción.
                    <a href="{{ route('admin.pagos.create', ['inscripcion_id' => $inscripcion->id]) }}" class="btn btn-success btn-sm ml-3">
                        <i class="fas fa-plus-circle"></i> Registrar Primer Pago
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- HISTORIAL DE MEMBRESÍAS -->
    <div class="row mt-5 mb-4">
        <div class="col-md-12">
            <h3 class="section-title mb-4"><i class="fas fa-history"></i> Historial de Membresías</h3>
            
            @php
                $historicoMembresias = $inscripcion->cliente ? $inscripcion->cliente->inscripciones()
                    ->with('membresia')
                    ->orderByDesc('fecha_inicio')
                    ->get() : collect([]);
            @endphp

            @if($historicoMembresias->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        @foreach($historicoMembresias as $index => $insc)
                            <div class="membership-item {{ $index % 2 == 0 ? '' : 'alternate' }}">
                                <div>
                                    <div style="font-weight: 700; font-size: 1.1em;">
                                        <i class="fas fa-dumbbell"></i> {{ $insc->membresia?->nombre ?? 'Sin membresía' }}
                                    </div>
                                    <small style="opacity: 0.9;">
                                        {{ $insc->fecha_inicio->format('d/m/Y') }} 
                                        <i class="fas fa-arrow-right"></i> 
                                        {{ $insc->fecha_vencimiento->format('d/m/Y') }}
                                    </small>
                                </div>
                                <div class="text-right">
                                    <span class="membership-badge">
                                        @if($insc->id === $inscripcion->id)
                                            <i class="fas fa-star"></i> ACTUAL
                                        @else
                                            <i class="fas fa-check-circle"></i> ANTERIOR
                                        @endif
                                    </span>
                                    <div style="margin-top: 5px; font-weight: 600;">
                                        ${{ number_format($insc->precio_final ?? $insc->precio_base, 0, '.', '.') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="alert alert-info mt-3" role="alert">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Total de Membresías:</strong> {{ $historicoMembresias->count() }} asociadas
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> No hay historial de membresías disponible.
                </div>
            @endif
        </div>
    </div>
@stop
