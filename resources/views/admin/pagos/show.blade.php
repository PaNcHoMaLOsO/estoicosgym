@extends('adminlte::page')

@section('title', 'Detalles del Pago - EstóicosGym')

@section('css')
    <style>
        .info-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .cuota-table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        .cuota-table tbody tr:hover {
            background: #f8f9fa;
        }
        .resumen-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            text-align: center;
        }
        .resumen-box.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .resumen-box.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .resumen-box h3 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .resumen-box p {
            margin: 5px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
        }
        .metodo-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-receipt"></i> Detalles del Pago #{{ $pago->id }}
            </h1>
            <small class="text-muted d-block mt-1">Registrado el {{ $pago->created_at->format('d/m/Y H:i') }}</small>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Información Principal del Pago -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="resumen-box success">
                <div class="metodo-icon">
                    @if($pago->metodoPagoPrincipal)
                        @if($pago->metodoPagoPrincipal->codigo === 'efectivo')
                            <i class="fas fa-money-bill"></i>
                        @elseif($pago->metodoPagoPrincipal->codigo === 'tarjeta')
                            <i class="fas fa-credit-card"></i>
                        @elseif($pago->metodoPagoPrincipal->codigo === 'transferencia')
                            <i class="fas fa-university"></i>
                        @else
                            <i class="fas fa-exchange-alt"></i>
                        @endif
                    @else
                        <i class="fas fa-question-circle"></i>
                    @endif
                </div>
                <h3>${{ number_format($pago->monto_abonado, 0, '.', '.') }}</h3>
                <p>Monto Abonado</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="resumen-box">
                <i class="fas fa-calendar metodo-icon"></i>
                <h3>{{ $pago->fecha_pago->format('d/m/Y') }}</h3>
                <p>Fecha de Pago</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="resumen-box {{ $pago->inscripcion->getSaldoPendiente() <= 0 ? 'success' : 'warning' }}">
                <i class="fas {{ $pago->inscripcion->getSaldoPendiente() <= 0 ? 'fa-check-circle' : 'fa-hourglass-half' }} metodo-icon"></i>
                <h3>{{ $pago->inscripcion->getSaldoPendiente() <= 0 ? '100%' : intval(($pago->monto_abonado / ($pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base)) * 100) }}%</h3>
                <p>{{ $pago->inscripcion->getSaldoPendiente() <= 0 ? 'Completamente Pagado' : 'Porcentaje Pagado' }}</p>
            </div>
        </div>
    </div>

    <!-- Información del Pago -->
    <div class="card card-primary mb-4">
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <i class="fas fa-dollar-sign"></i> Información del Pago
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong><i class="fas fa-credit-card"></i> Método de Pago:</strong>
                        <p>{{ $pago->metodoPagoPrincipal->nombre }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong><i class="fas fa-calendar-alt"></i> Fecha de Pago:</strong>
                        <p>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            @if($pago->referencia_pago)
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <strong><i class="fas fa-fingerprint"></i> Referencia de Pago:</strong>
                            <div class="p-2 bg-light border rounded mt-2">
                                <code>{{ $pago->referencia_pago }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($pago->observaciones)
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <strong><i class="fas fa-sticky-note"></i> Observaciones:</strong>
                            <p class="text-muted">{{ $pago->observaciones }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Información de la Inscripción -->
    <div class="card card-info mb-4">
        <div class="card-header bg-info">
            <h3 class="card-title">
                <i class="fas fa-user-check"></i> Información de la Inscripción
            </h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong><i class="fas fa-user"></i> Cliente:</strong>
                        <p>
                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}">
                                {{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}
                            </a>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong><i class="fas fa-envelope"></i> Email:</strong>
                        <p>{{ $pago->inscripcion->cliente->email }}</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong><i class="fas fa-dumbbell"></i> Membresía:</strong>
                        <p>{{ $pago->inscripcion->membresia->nombre }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong><i class="fas fa-circle"></i> Estado de Inscripción:</strong>
                        <p>{!! \App\Helpers\EstadoHelper::badgeWithIcon($pago->inscripcion->estado) !!}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong><i class="fas fa-calendar-check"></i> Período:</strong>
                        <p>{{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }} - {{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Ver Inscripción
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Pagos -->
    <div class="card card-success mb-4">
        <div class="card-header bg-success">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i> Resumen de Pagos
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="text-muted">Total a Pagar</h5>
                        <h3 class="text-primary">${{ number_format($pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base, 0, '.', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="text-muted">Total Abonado</h5>
                        <h3 class="text-success">${{ number_format($pago->inscripcion->getTotalAbonado(), 0, '.', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="text-muted">Saldo Pendiente</h5>
                        <h3 class="text-warning">${{ number_format($pago->inscripcion->getSaldoPendiente(), 0, '.', '.') }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="text-muted">Cantidad Pagos</h5>
                        <h3 class="text-info">{{ $pago->inscripcion->pagos->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan de Cuotas (si aplica) -->
    @if($pago->es_plan_cuotas)
        <div class="card card-warning mb-4">
            <div class="card-header bg-warning">
                <h3 class="card-title">
                    <i class="fas fa-list-ol"></i> Plan de Cuotas
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped cuota-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 10%">Cuota #</th>
                            <th style="width: 15%">Monto</th>
                            <th style="width: 20%">Vencimiento</th>
                            <th style="width: 20%">Fecha Pago</th>
                            <th style="width: 20%">Estado</th>
                            <th style="width: 15%">Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $cuotasRelacionadas = $pago->cuotasRelacionadas();
                        @endphp
                        @forelse($cuotasRelacionadas as $cuota)
                            <tr>
                                <td><strong>#{{ $cuota->numero_cuota }}</strong></td>
                                <td>${{ number_format($cuota->monto_abonado, 0, '.', '.') }}</td>
                                <td>{{ $cuota->fecha_vencimiento_cuota ? $cuota->fecha_vencimiento_cuota->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $cuota->fecha_pago->format('d/m/Y') }}</td>
                                <td>
                                    @if($cuota->id_estado == 102)
                                        <span class="info-badge" style="background: #d4edda; color: #155724;">
                                            <i class="fas fa-check-circle"></i> Pagada
                                        </span>
                                    @elseif($cuota->id_estado == 101)
                                        <span class="info-badge" style="background: #fff3cd; color: #856404;">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </span>
                                    @else
                                        <span class="info-badge" style="background: #cce5ff; color: #004085;">
                                            <i class="fas fa-hourglass-half"></i> Parcial
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($cuota->referencia_pago)
                                        <code class="text-muted">{{ $cuota->referencia_pago }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    No hay cuotas relacionadas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Historial de Pagos de la Inscripción -->
    <div class="card card-secondary mb-4">
        <div class="card-header bg-secondary">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Historial de Pagos
            </h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 8%">ID</th>
                        <th style="width: 12%">Fecha</th>
                        <th style="width: 12%">Monto</th>
                        <th style="width: 15%">Método</th>
                        <th style="width: 15%">Referencia</th>
                        <th style="width: 20%">Estado</th>
                        <th style="width: 18%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pago->inscripcion->pagos as $p)
                        <tr class="{{ $p->id === $pago->id ? 'table-active' : '' }}">
                            <td><strong>#{{ $p->id }}{{ $p->id === $pago->id ? ' (Actual)' : '' }}</strong></td>
                            <td>{{ $p->fecha_pago->format('d/m/Y') }}</td>
                            <td>${{ number_format($p->monto_abonado, 0, '.', '.') }}</td>
                            <td>
                                <small>
                                    @if($p->metodoPagoPrincipal->codigo === 'efectivo')
                                        <i class="fas fa-money-bill"></i> Efectivo
                                    @elseif($p->metodoPagoPrincipal->codigo === 'tarjeta')
                                        <i class="fas fa-credit-card"></i> Tarjeta
                                    @elseif($p->metodoPagoPrincipal->codigo === 'transferencia')
                                        <i class="fas fa-university"></i> Transfer.
                                    @else
                                        <i class="fas fa-ellipsis-h"></i> {{ $p->metodoPagoPrincipal->nombre }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                @if($p->referencia_pago)
                                    <code class="text-muted">{{ $p->referencia_pago }}</code>
                                @else
                                    <span class="text-muted text-small">-</span>
                                @endif
                            </td>
                            <td>
                                {!! \App\Helpers\EstadoHelper::badgeWithIcon($p->estado) !!}
                            </td>
                            <td>
                                <a href="{{ route('admin.pagos.show', $p) }}" class="btn btn-xs btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($p->id === $pago->id)
                                    <span class="text-muted small">(Actual)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No hay pagos registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Información de Auditoría -->
    <div class="card card-light">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Información del Sistema
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <small class="d-block text-muted mb-2">
                        <i class="fas fa-plus-circle"></i> <strong>Creado:</strong> {{ $pago->created_at->format('d/m/Y H:i:s') }}
                    </small>
                    @if($pago->created_at->diffInMinutes($pago->updated_at) > 1)
                        <small class="d-block text-muted">
                            <i class="fas fa-sync"></i> <strong>Actualizado:</strong> {{ $pago->updated_at->format('d/m/Y H:i:s') }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <!-- Botones de Acción Final -->
    <div class="row">
        <div class="col-12">
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
            <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit"></i> Editar Pago
            </a>
            <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro? Esta acción no puede revertirse.')">
                    <i class="fas fa-trash"></i> Eliminar Pago
                </button>
            </form>
        </div>
    </div>
@stop
