@extends('adminlte::page')

@section('title', 'Detalle Cliente - EstóicosGym')

@section('css')
    <style>
        /* ===== HERO HEADER ===== */
        .cliente-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .cliente-hero h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .cliente-hero p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 700;
            margin-top: 1rem;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .hero-badge.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border: 0;
        }

        .hero-badge.inactive {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        /* ===== INFO CARDS ===== */
        .info-card {
            background: white;
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .info-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .info-card-header {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-bottom: 2px solid #667eea;
            padding: 1rem;
        }

        .info-card-header h4 {
            margin: 0;
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-card-header i {
            color: #667eea;
            font-size: 1.3em;
        }

        .info-card-body {
            padding: 1.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: 0;
        }

        .info-label {
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 700;
            font-size: 1rem;
        }

        .info-value.link {
            color: #667eea;
            text-decoration: none;
        }

        .info-value.link:hover {
            text-decoration: underline;
        }

        /* ===== STATS BOXES ===== */
        .stat-box {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            border: 2px solid #667eea;
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .stat-box.success {
            background: linear-gradient(135deg, #f0fff4 0%, #e8f8f0 100%);
            border-color: #11998e;
        }

        .stat-box.warning {
            background: linear-gradient(135deg, #fffaf0 0%, #fff5e8 100%);
            border-color: #ffa500;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-box.success .stat-number {
            color: #11998e;
        }

        .stat-box.warning .stat-number {
            color: #ffa500;
        }

        .stat-label {
            color: #495057;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== TABLES ===== */
        .table-card {
            background: white;
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .table-responsive {
            border-radius: 0;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            border-bottom: 2px solid #667eea;
        }

        .table thead th {
            color: #2c3e50;
            font-weight: 700;
            padding: 1rem;
            border: 0;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        /* ===== BADGES ===== */
        .badge-estado {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-pagado {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .badge-pendiente {
            background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
            color: white;
        }

        /* ===== ACTION BUTTONS ===== */
        .action-bar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .action-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: 0;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            text-decoration: none;
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .action-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .action-btn-warning {
            background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
        }

        .action-btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
        }

        .action-btn-secondary {
            background: #e9ecef;
            color: #495057;
        }

        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* ===== ALERTS ===== */
        .alert-info-custom {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            border: 2px solid #667eea;
            border-radius: 0.75rem;
            padding: 1rem;
            color: #2c3e50;
            text-align: center;
            font-size: 0.95rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .cliente-hero h2 {
                font-size: 1.5rem;
            }

            .info-card-body {
                padding: 1rem;
            }

            .action-bar {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }

            .stat-box {
                margin-bottom: 1rem;
            }

            .table {
                font-size: 0.85rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
@endsection

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-user-circle"></i> Detalle del Cliente
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-lg" role="alert" style="border-left: 5px solid #28a745;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading mb-2">
                <i class="fas fa-check-circle"></i> ¡Éxito!
            </h5>
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-lg" role="alert" style="border-left: 5px solid #dc3545;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading mb-2">
                <i class="fas fa-exclamation-circle"></i> Error
            </h5>
            {{ $message }}
        </div>
    @endif

    <!-- HERO HEADER -->
    <div class="cliente-hero">
        <h2>{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
            @if($cliente->apellido_materno)
                {{ $cliente->apellido_materno }}
            @endif
        </h2>
        <p><i class="fas fa-id-card"></i> {{ $cliente->run_pasaporte }}</p>
        <div>
            <span class="hero-badge {{ $cliente->activo ? 'active' : 'inactive' }}">
                {{ $cliente->activo ? '✓ CLIENTE ACTIVO' : '✗ CLIENTE INACTIVO' }}
            </span>
        </div>
    </div>

    <!-- INFORMACIÓN PRINCIPAL -->
    <div class="row mb-4">
        <!-- Datos de Contacto -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <h4><i class="fas fa-phone"></i> Contacto</h4>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <a href="mailto:{{ $cliente->email }}" class="info-value link">{{ $cliente->email }}</a>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Celular</span>
                        <a href="tel:{{ $cliente->celular }}" class="info-value link">{{ $cliente->celular }}</a>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha Nacimiento</span>
                        <span class="info-value">
                            {{ $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dirección -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <h4><i class="fas fa-map-marker-alt"></i> Domicilio</h4>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Dirección</span>
                        <span class="info-value">{{ $cliente->direccion ?? 'No registrado' }}</span>
                    </div>
                    @if($cliente->contacto_emergencia)
                        <div class="info-row">
                            <span class="info-label">Emergencia</span>
                            <span class="info-value">{{ $cliente->contacto_emergencia }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tel. Emergencia</span>
                            <a href="tel:{{ $cliente->telefono_emergencia }}" class="info-value link">{{ $cliente->telefono_emergencia }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ESTADÍSTICAS Y CONVENIO -->
    <div class="row mb-4">
        <!-- Estadísticas -->
        <div class="col-lg-8">
            <div class="row">
                <div class="col-sm-6">
                    <div class="stat-box">
                        <div class="stat-number">{{ $cliente->inscripciones->count() }}</div>
                        <div class="stat-label">Inscripciones</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="stat-box success">
                        <div class="stat-number">${{ number_format($cliente->pagos->sum('monto_abonado'), 0, ',', '.') }}</div>
                        <div class="stat-label">Monto Total Pagado</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Convenio -->
        <div class="col-lg-4">
            @if($cliente->convenio)
                <div class="info-card">
                    <div class="info-card-header">
                        <h4><i class="fas fa-handshake"></i> Convenio</h4>
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <span class="info-label">Nombre</span>
                            <span class="info-value">{{ $cliente->convenio->nombre }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tipo</span>
                            <span class="info-value">{{ $cliente->convenio->tipo }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert-info-custom">
                    <i class="fas fa-info-circle"></i> Sin convenio asignado
                </div>
            @endif
        </div>
    </div>

    <!-- OBSERVACIONES -->
    @if($cliente->observaciones)
        <div class="row mb-4">
            <div class="col-12">
                <div class="info-card">
                    <div class="info-card-header">
                        <h4><i class="fas fa-sticky-note"></i> Observaciones</h4>
                    </div>
                    <div class="info-card-body">
                        {{ $cliente->observaciones }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- INSCRIPCIONES -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="info-card-header">
                    <h4><i class="fas fa-dumbbell"></i> Inscripciones</h4>
                </div>
                <div class="table-responsive">
                    @if($cliente->inscripciones->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Membresía</th>
                                    <th>Inicio</th>
                                    <th>Vencimiento</th>
                                    <th>Pagos</th>
                                    <th>Monto</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->inscripciones as $inscripcion)
                                    <tr>
                                        <td><span class="badge badge-secondary">#{{ $inscripcion->id }}</span></td>
                                        <td><strong>{{ $inscripcion->membresia->nombre ?? 'N/A' }}</strong></td>
                                        <td>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</td>
                                        <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                                        <td><span class="badge badge-info">{{ $inscripcion->pagos->count() }}</span></td>
                                        <td><strong>${{ number_format($inscripcion->pagos->sum('monto_abonado'), 0, ',', '.') }}</strong></td>
                                        <td>
                                            <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert-info-custom m-3">
                            <i class="fas fa-inbox"></i> Sin inscripciones registradas
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- HISTORIAL DE PAGOS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="info-card-header">
                    <h4><i class="fas fa-receipt"></i> Historial de Pagos</h4>
                </div>
                <div class="table-responsive">
                    @if($cliente->pagos->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Inscripción</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->pagos as $pago)
                                    <tr>
                                        <td><span class="badge badge-secondary">#{{ $pago->id }}</span></td>
                                        <td>
                                            @if($pago->inscripcion)
                                                <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="text-primary">
                                                    {{ $pago->inscripcion->membresia->nombre ?? 'Membresía' }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td><strong>${{ number_format($pago->monto_abonado, 0, ',', '.') }}</strong></td>
                                        <td>{{ $pago->metodoPago?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                        <td>
                                            @if($pago->id_estado === 201)
                                                <span class="badge-estado badge-pagado">Pagado</span>
                                            @else
                                                <span class="badge-estado badge-pendiente">Pendiente</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert-info-custom m-3">
                            <i class="fas fa-inbox"></i> Sin pagos registrados
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION BAR -->
    <div class="action-bar">
        <a href="{{ route('admin.clientes.index') }}" class="action-btn action-btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <a href="{{ route('admin.clientes.edit', $cliente) }}" class="action-btn action-btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        @php
            $estadoActiva = \App\Models\Estado::where('codigo', 100)->first();
            $estadoPendiente = \App\Models\Estado::where('codigo', 200)->first();
            $puedoDesactivar = !$cliente->inscripciones()->where('id_estado', $estadoActiva?->id)->exists() && 
                              !$cliente->pagos()->where('id_estado', $estadoPendiente?->id)->exists();
        @endphp
        <button type="button" class="action-btn action-btn-danger {{ !$puedoDesactivar ? 'disabled' : '' }}"
                {{ !$puedoDesactivar ? 'disabled' : '' }}
                @if($puedoDesactivar) data-toggle="modal" data-target="#desactivarClienteModal" @endif>
            <i class="fas fa-user-slash"></i> Desactivar
        </button>
        @if(!$puedoDesactivar)
            <small class="text-muted align-self-center">
                <i class="fas fa-lock"></i> No se puede desactivar: hay inscripciones activas o pagos pendientes
            </small>
        @endif
    </div>

    <!-- MODAL DESACTIVAR -->
    <div class="modal fade" id="desactivarClienteModal" tabindex="-1" role="dialog" aria-labelledby="desactivarClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0">
                <div class="modal-header" style="background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%); color: white; border: 0;">
                    <h5 class="modal-title" id="desactivarClienteLabel">
                        <i class="fas fa-exclamation-triangle"></i> Desactivar Cliente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div style="background: linear-gradient(135deg, #fffaf0 0%, #fff5e8 100%); border: 2px solid #ffa500; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;">
                        <h6 style="color: #ff8c00; font-weight: 700; margin: 0;">
                            <i class="fas fa-bell"></i> Confirmación de Desactivación
                        </h6>
                    </div>
                    <p>Estás a punto de <strong>desactivar a {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</strong>.</p>
                    <hr>
                    <h6 style="font-weight: 700; margin-bottom: 1rem;">¿Qué sucede al desactivar?</h6>
                    <ul style="color: #495057; margin-bottom: 1rem;">
                        <li><i class="fas fa-check text-success"></i> El cliente <strong>NO será eliminado</strong></li>
                        <li><i class="fas fa-check text-success"></i> Todo su <strong>historial se conserva</strong></li>
                        <li><i class="fas fa-check text-success"></i> <strong>No aparecerá</strong> en clientes activos</li>
                        <li><i class="fas fa-check text-success"></i> Podrá ser <strong>reactivado</strong> después</li>
                    </ul>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <form action="{{ route('admin.clientes.destroy', $cliente) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%); color: white; border: 0;" 
                                onclick="return confirm('⚠️ SEGUNDA CONFIRMACIÓN\\n\\n¿Confirmas que deseas DESACTIVAR a este cliente?\\n\\nSu información se conservará.')">
                            <i class="fas fa-check"></i> Sí, Desactivar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
