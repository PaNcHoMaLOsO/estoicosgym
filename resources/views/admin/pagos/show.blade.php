@extends('adminlte::page')

@section('title', 'Detalles del Pago - EstóicosGym')

@section('content_header')
    <div class="row mb-2 align-items-center">
        <div class="col-sm-7">
            <h1 class="m-0"><i class="fas fa-receipt text-success"></i> Detalles del Pago</h1>
            <small class="text-muted">ID: <strong>#{{ $pago->id }}</strong> | Registro: {{ $pago->created_at->format('d/m/Y H:i') }}</small>
        </div>
        <div class="col-sm-5 text-right">
            <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-pencil-alt"></i> Editar
            </a>
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-list"></i> Listado
            </a>
        </div>
    </div>
@stop

@section('content')

    <!-- CARD PRINCIPAL - MONTO DESTACADO -->
    <div class="card card-outline shadow-sm mb-4" style="border-top: 5px solid {{ $pago->estado->color ?? '#6c757d' }}; border-radius: 8px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-1"><i class="fas fa-wallet"></i> Monto Pagado</p>
                    <h2 class="mb-0 font-weight-bold" style="font-size: 2.5rem; color: #28a745;">
                        ${{ number_format($pago->monto_abonado, 0, '.', '.') }}
                    </h2>
                    <small class="text-muted d-block mt-2">
                        Inscripción: <strong>{{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}</strong>
                    </small>
                </div>
                <div class="col-md-6 text-right">
                    <div class="mb-3">
                        <h5 class="mb-2">Estado</h5>
                        <span class="badge p-3" style="background-color: {{ $pago->estado->color ?? '#6c757d' }}; font-size: 14px; border-radius: 20px;">
                            <i class="fas fa-circle-notch"></i> {{ $pago->estado->nombre }}
                        </span>
                    </div>
                    @if($pago->monto_pendiente > 0)
                        <p class="text-warning font-weight-bold mb-0">
                            <i class="fas fa-hourglass-half"></i> Pendiente: ${{ number_format($pago->monto_pendiente, 0, '.', '.') }}
                        </p>
                    @else
                        <p class="text-success font-weight-bold mb-0">
                            <i class="fas fa-check-circle"></i> Completamente Pagado
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- GRID 1: INFORMACIÓN DEL PAGO + PROGRESO -->
    <div class="row mb-4">

        <!-- DETALLES DEL PAGO -->
        <div class="col-lg-6">
            <div class="card card-primary card-outline shadow-sm" style="border-radius: 8px;">
                <div class="card-header bg-primary text-white" style="border-radius: 8px 8px 0 0;">
                    <h5 class="card-title m-0"><i class="fas fa-credit-card"></i> Información del Pago</h5>
                </div>
                <div class="card-body">
                    <div class="info-row mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-dollar-sign"></i> Monto Abonado</span>
                            <strong class="text-success" style="font-size: 1.2rem;">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</strong>
                        </div>
                    </div>

                    <div class="info-row mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-calendar-alt"></i> Fecha</span>
                            <strong>{{ $pago->fecha_pago->format('d/m/Y') }}</strong>
                        </div>
                    </div>

                    <div class="info-row mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-credit-card"></i> Método</span>
                            <strong>{{ $pago->metodoPago?->nombre ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    <div class="info-row mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-list-ol"></i> Cuota</span>
                            <strong>{{ $pago->numero_cuota ?? 1 }}/{{ $pago->cantidad_cuotas ?? 1 }}</strong>
                        </div>
                    </div>

                    @if($pago->cantidad_cuotas && $pago->cantidad_cuotas > 1)
                        <div class="info-row mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="fas fa-divide"></i> Por Cuota</span>
                                <strong>${{ number_format($pago->monto_cuota ?? 0, 0, '.', '.') }}</strong>
                            </div>
                        </div>
                    @endif

                    @if($pago->referencia_pago)
                        <div class="info-row mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="fas fa-barcode"></i> Referencia</span>
                                <strong class="text-info">{{ $pago->referencia_pago }}</strong>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- PROGRESO DE PAGO -->
        <div class="col-lg-6">
            <div class="card card-success card-outline shadow-sm" style="border-radius: 8px;">
                <div class="card-header bg-success text-white" style="border-radius: 8px 8px 0 0;">
                    <h5 class="card-title m-0"><i class="fas fa-chart-bar"></i> Progreso de Inscripción</h5>
                </div>
                <div class="card-body">
                    @php
                        $totalPagos = $pago->inscripcion->pagos->sum('monto_abonado');
                        $montoTotal = $pago->monto_total ?? 0;
                        $porcentaje = $montoTotal > 0 ? min(($totalPagos / $montoTotal) * 100, 100) : 0;
                        $colorBarra = $porcentaje >= 100 ? 'success' : ($porcentaje >= 75 ? 'info' : ($porcentaje >= 50 ? 'warning' : 'danger'));
                    @endphp

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Avance de Pago</span>
                            <strong class="text-{{ $colorBarra }}" style="font-size: 1.1rem;">{{ round($porcentaje, 1) }}%</strong>
                        </div>
                        <div class="progress" style="height: 30px; border-radius: 20px; background: #f0f0f0;">
                            <div class="progress-bar bg-{{ $colorBarra }}" 
                                 style="width: {{ $porcentaje }}%; border-radius: 20px; font-weight: bold; font-size: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                                {{ round($porcentaje, 1) }}%
                            </div>
                        </div>
                    </div>

                    <div class="row text-center mb-3 pb-3 border-bottom">
                        <div class="col-6">
                            <p class="text-muted mb-1 small"><i class="fas fa-coins"></i> Pagado</p>
                            <h5 class="text-success mb-0">${{ number_format($totalPagos, 0, '.', '.') }}</h5>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1 small"><i class="fas fa-tag"></i> Total</p>
                            <h5 class="text-primary mb-0">${{ number_format($montoTotal, 0, '.', '.') }}</h5>
                        </div>
                    </div>

                    <div class="row text-center mb-0">
                        <div class="col-6">
                            <p class="text-muted mb-1 small"><i class="fas fa-history"></i> Pagos</p>
                            <h5 class="text-info mb-0">{{ $pago->inscripcion->pagos->count() }}</h5>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1 small"><i class="fas fa-hourglass-half"></i> Pendiente</p>
                            <h5 class="mb-0" style="color: {{ max(0, $montoTotal - $totalPagos) > 0 ? '#ffc107' : '#28a745' }};">
                                ${{ number_format(max(0, $montoTotal - $totalPagos), 0, '.', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- GRID 2: INSCRIPCIÓN + HISTORIAL DE PAGOS -->
    <div class="row mb-4">

        <!-- DATOS DE INSCRIPCIÓN -->
        <div class="col-lg-5">
            <div class="card card-info card-outline shadow-sm" style="border-radius: 8px;">
                <div class="card-header bg-info text-white" style="border-radius: 8px 8px 0 0;">
                    <h5 class="card-title m-0"><i class="fas fa-user-tie"></i> Datos de Inscripción</h5>
                </div>
                <div class="card-body">
                    <div class="info-row mb-3 pb-3 border-bottom">
                        <p class="text-muted mb-1 small"><i class="fas fa-user"></i> Cliente</p>
                        <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" class="text-dark font-weight-bold" target="_blank">
                            {{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}
                            <i class="fas fa-external-link-alt fa-xs"></i>
                        </a>
                    </div>

                    <div class="info-row mb-3 pb-3 border-bottom">
                        <p class="text-muted mb-1 small"><i class="fas fa-dumbbell"></i> Membresía</p>
                        <p class="mb-0 font-weight-bold">{{ $pago->inscripcion->membresia->nombre }}</p>
                    </div>

                    <div class="info-row mb-3 pb-3 border-bottom">
                        <p class="text-muted mb-1 small"><i class="fas fa-calendar-check"></i> Período</p>
                        <p class="mb-0">
                            <strong>Inicio:</strong> {{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }}<br>
                            <strong>Vence:</strong> {{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}
                        </p>
                    </div>

                    <div class="info-row mb-0">
                        <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="btn btn-info btn-sm btn-block">
                            <i class="fas fa-arrow-right"></i> Ver Inscripción Completa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- HISTORIAL DE PAGOS -->
        <div class="col-lg-7">
            <div class="card card-warning card-outline shadow-sm" style="border-radius: 8px;">
                <div class="card-header bg-warning text-white" style="border-radius: 8px 8px 0 0;">
                    <h5 class="card-title m-0"><i class="fas fa-history"></i> Historial de Pagos</h5>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 380px; overflow-y: auto;">
                        @php $pagosOrdenados = $pago->inscripcion->pagos->sortByDesc('fecha_pago')->take(10); @endphp
                        @forelse($pagosOrdenados as $p)
                            <div class="payment-item p-3" style="border-bottom: 1px solid #f0f0f0; transition: background 0.3s;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <strong class="text-success" style="font-size: 1.1rem;">${{ number_format($p->monto_abonado, 0, '.', '.') }}</strong>
                                            <span class="badge p-2" style="background-color: {{ $p->estado->color ?? '#6c757d' }}; font-size: 11px; border-radius: 10px;">
                                                {{ $p->estado->nombre }}
                                            </span>
                                            @if($p->id == $pago->id)
                                                <span class="badge badge-primary p-2" style="font-size: 11px; border-radius: 10px;">
                                                    <i class="fas fa-arrow-left"></i> ACTUAL
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-calendar"></i> {{ $p->fecha_pago->format('d/m/Y') }} 
                                            <i class="fas fa-credit-card"></i> {{ $p->metodoPago?->nombre ?? 'N/A' }}
                                        </small>
                                        @if($p->referencia_pago)
                                            <small class="text-info d-block">
                                                <i class="fas fa-barcode"></i> {{ $p->referencia_pago }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">Sin pagos registrados</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- OBSERVACIONES -->
    @if($pago->observaciones)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-light card-outline shadow-sm" style="border-radius: 8px;">
                    <div class="card-header" style="border-radius: 8px 8px 0 0;">
                        <h5 class="card-title m-0"><i class="fas fa-sticky-note text-warning"></i> Observaciones</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0" style="line-height: 1.6;">{{ $pago->observaciones }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- BOTÓN DE REGISTRAR PAGO SI HAY PENDIENTE -->
    @if($pago->monto_pendiente > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-success card-outline shadow-sm" style="border-top: 4px solid #28a745; border-radius: 8px;">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h5 class="mb-2"><i class="fas fa-exclamation-circle text-warning"></i> Saldo Pendiente</h5>
                                <p class="text-muted mb-0">Aún hay saldo pendiente de pagar en esta inscripción. Registra un nuevo pago para continuarcon el proceso.</p>
                            </div>
                            <div class="col-md-5 text-right">
                                <h2 class="text-warning mb-3" style="font-size: 1.8rem;">
                                    ${{ number_format($pago->monto_pendiente, 0, '.', '.') }}
                                </h2>
                                <a href="{{ route('admin.pagos.create', ['inscripcion_id' => $pago->inscripcion->id]) }}" class="btn btn-success btn-lg">
                                    <i class="fas fa-credit-card"></i> Registrar Nuevo Pago
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- BOTONES DE ACCIÓN (FOOTER) -->
    <div class="row">
        <div class="col-12">
            <div class="card card-light shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <div>
                            <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Listado
                            </a>
                            <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="btn btn-info">
                                <i class="fas fa-user-circle"></i> Ver Inscripción
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-warning">
                                <i class="fas fa-pencil-alt"></i> Editar Pago
                            </a>
                            <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('⚠️ ¿Está seguro de eliminar este pago? Esta acción no se puede deshacer.')">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        .info-row {
            transition: padding 0.2s ease;
        }

        .info-row:hover {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .payment-item:hover {
            background-color: #f9f9f9;
        }

        .card {
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-2px);
        }

        .progress {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .badge {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .btn {
            transition: all 0.2s ease;
            border-radius: 6px;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
@stop
