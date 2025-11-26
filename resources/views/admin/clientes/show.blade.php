@extends('adminlte::page')

@section('title', 'Detalle Cliente - EstóicosGym')

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
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading">
                <i class="fas fa-check-circle"></i> ¡Éxito!
            </h5>
            <p class="mb-0">{{ $message }}</p>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-circle"></i> Error
            </h5>
            <p class="mb-0">{{ $message }}</p>
        </div>
    @endif

    <!-- HEADER CON DATOS PRINCIPALES Y ESTADÍSTICAS -->
    <div class="row mb-4">
        <!-- Información Personal Compacta -->
        <div class="col-lg-4 mb-4">
            <div class="card card-primary card-outline">
                <div class="card-header bg-primary d-flex align-items-center">
                    <h3 class="card-title flex-grow-1">
                        <i class="fas fa-user-circle"></i> {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                        @if($cliente->apellido_materno)
                            {{ $cliente->apellido_materno }}
                        @endif
                    </h3>
                    <span class="badge" style="background-color: {{ $cliente->activo ? '#28a745' : '#6c757d' }};">
                        {{ $cliente->activo ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label class="text-muted small">RUT / Pasaporte</label>
                        <p class="h6 mb-0"><strong>{{ $cliente->run_pasaporte }}</strong></p>
                    </div>
                    <hr class="my-2">
                    <div class="form-group mb-3">
                        <label class="text-muted small">Email</label>
                        <p class="mb-0"><a href="mailto:{{ $cliente->email }}" class="text-primary">{{ $cliente->email }}</a></p>
                    </div>
                    <hr class="my-2">
                    <div class="form-group mb-3">
                        <label class="text-muted small">Teléfono</label>
                        <p class="mb-0"><a href="tel:{{ $cliente->celular }}" class="text-primary">{{ $cliente->celular }}</a></p>
                    </div>
                    <hr class="my-2">
                    <div class="form-group mb-0">
                        <label class="text-muted small">Nacimiento</label>
                        <p class="mb-0"><small>{{ $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('d/m/Y') : 'N/A' }}</small></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dirección y Contacto -->
        <div class="col-lg-4 mb-4">
            <div class="card card-info card-outline">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt"></i> Dirección y Contacto
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label class="text-muted small">Domicilio</label>
                        <p class="mb-0 small">{{ $cliente->direccion ?? '<span class="text-muted">No registrado</span>' }}</p>
                    </div>
                    <hr class="my-2">
                    <div class="form-group mb-0">
                        <label class="text-muted small">Contacto de Emergencia</label>
                        @if($cliente->contacto_emergencia)
                            <p class="mb-0"><strong class="small">{{ $cliente->contacto_emergencia }}</strong></p>
                            <p class="mb-0"><a href="tel:{{ $cliente->telefono_emergencia }}" class="text-primary small">{{ $cliente->telefono_emergencia }}</a></p>
                        @else
                            <p class="mb-0 text-muted small">No registrado</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas y Convenio -->
        <div class="col-lg-4 mb-4">
            <!-- Card Estadísticas -->
            <div class="card card-success card-outline mb-3">
                <div class="card-header bg-success">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Resumen
                    </h3>
                </div>
                <div class="card-body p-3">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <div class="info-box" style="margin: 0;">
                                <span class="info-box-icon bg-info" style="font-size: 0.9rem; display: flex; align-items: center; justify-content: center;">
                                    <i class="far fa-credit-card"></i>
                                </span>
                                <div class="info-box-content" style="margin-left: 5px;">
                                    <span class="info-box-text" style="font-size: 0.75rem;">Inscripciones</span>
                                    <span class="info-box-number" style="font-size: 1.2rem;">{{ $cliente->inscripciones->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="info-box" style="margin: 0;">
                                <span class="info-box-icon bg-success" style="font-size: 0.9rem; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                                <div class="info-box-content" style="margin-left: 5px;">
                                    <span class="info-box-text" style="font-size: 0.75rem;">Pagos</span>
                                    <span class="info-box-number" style="font-size: 1.1rem;">${{ number_format($cliente->pagos->sum('monto_abonado'), 0, '.', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Convenio -->
            @if($cliente->convenio)
                <div class="card card-warning card-outline">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">
                            <i class="fas fa-handshake"></i> Convenio
                        </h3>
                    </div>
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-1">{{ $cliente->convenio->nombre }}</h6>
                        <p class="text-muted mb-0 small">{{ $cliente->convenio->descripcion }}</p>
                        <div class="mt-2">
                            <span class="badge badge-success">{{ $cliente->convenio->descuento_porcentaje ?? 0 }}% desc.</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- OBSERVACIONES SI EXISTEN -->
    @if($cliente->observaciones)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title">
                            <i class="fas fa-sticky-note"></i> Observaciones
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $cliente->observaciones }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- INSCRIPCIONES -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-warning card-outline">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Inscripciones Activas
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($cliente->inscripciones->count() > 0)
                        <table class="table table-hover table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Membresía</th>
                                    <th>Estado</th>
                                    <th>Inicio</th>
                                    <th>Vencimiento</th>
                                    <th>Pagos</th>
                                    <th>Monto Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->inscripciones as $inscripcion)
                                    <tr>
                                        <td>{{ $inscripcion->id }}</td>
                                        <td>{{ $inscripcion->membresia->nombre ?? 'N/A' }}</td>
                                        <td>{!! \App\Helpers\EstadoHelper::badgeWithIcon($inscripcion->estado) !!}</td>
                                        <td><small>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</small></td>
                                        <td><small>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                        <td><span class="badge badge-info">{{ $inscripcion->pagos->count() }}</span></td>
                                        <td><strong>${{ number_format($inscripcion->pagos->sum('monto_abonado'), 0, '.', '.') }}</strong></td>
                                        <td>
                                            <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn btn-xs btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-0">
                            <i class="fas fa-info-circle"></i> No hay inscripciones registradas
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- HISTORIAL DE PAGOS -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-success card-outline">
                <div class="card-header bg-success">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Historial de Pagos
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($cliente->pagos->count() > 0)
                        <table class="table table-hover table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Inscripción</th>
                                    <th>Estado</th>
                                    <th>Monto Abonado</th>
                                    <th>Método Pago</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->pagos as $pago)
                                    <tr>
                                        <td>{{ $pago->id }}</td>
                                        <td>
                                            @if($pago->inscripcion)
                                                <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="text-primary">
                                                    {{ $pago->inscripcion->membresia->nombre ?? 'Membresía' }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{!! \App\Helpers\EstadoHelper::badgeWithIcon($pago->estado) !!}</td>
                                        <td><strong>${{ number_format($pago->monto_abonado, 0, '.', '.') }}</strong></td>
                                        <td><small>{{ $pago->metodoPago->nombre ?? 'N/A' }}</small></td>
                                        <td><small>{{ $pago->fecha_pago->format('d/m/Y') }}</small></td>
                                        <td>
                                            <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-xs btn-success" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info m-0">
                            <i class="fas fa-info-circle"></i> No hay pagos registrados
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                @php
                    $puedoDesactivar = !$cliente->inscripciones()->where('id_estado', 1)->exists() && 
                                      !$cliente->pagos()->where('id_estado', 101)->exists();
                @endphp
                <button type="button" class="btn {{ $puedoDesactivar ? 'btn-danger' : 'btn-secondary disabled' }}" 
                        {{ !$puedoDesactivar ? 'disabled' : '' }}
                        @if($puedoDesactivar) data-toggle="modal" data-target="#desactivarClienteModal" @endif>
                    <i class="fas fa-user-slash"></i> Desactivar Cliente
                </button>
                @if(!$puedoDesactivar)
                    <small class="text-muted align-self-center">
                        <i class="fas fa-info-circle"></i> No se puede desactivar: hay inscripciones activas o pagos pendientes
                    </small>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Desactivar -->
    <div class="modal fade" id="desactivarClienteModal" tabindex="-1" role="dialog" aria-labelledby="desactivarClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="desactivarClienteLabel">
                        <i class="fas fa-exclamation-triangle"></i> Desactivar Cliente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-bell"></i> Importante
                        </h5>
                        <p class="mb-0">
                            Estás a punto de <strong>desactivar a {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</strong>.
                        </p>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold mb-2">¿Qué sucede al desactivar?</h6>
                    <ul class="small text-muted">
                        <li><i class="fas fa-check text-success"></i> El cliente <strong>NO será eliminado</strong> del sistema</li>
                        <li><i class="fas fa-check text-success"></i> Todo su <strong>historial se conserva</strong> (inscripciones, pagos, datos)</li>
                        <li><i class="fas fa-check text-success"></i> <strong>No aparecerá</strong> en el listado de clientes activos</li>
                        <li><i class="fas fa-check text-success"></i> Podrá ser <strong>reactivado</strong> en el futuro si es necesario</li>
                    </ul>
                    <hr>
                    <p class="text-danger font-weight-bold mb-0">
                        <i class="fas fa-lock"></i> Para desactivar, confirma tu acción dos veces:
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <form action="{{ route('admin.clientes.destroy', $cliente) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('⚠️ SEGUNDA CONFIRMACIÓN\\n\\n¿Confirmas que deseas DESACTIVAR definitivamente a este cliente?\\n\\nSu información se conservará en el sistema.')">
                            <i class="fas fa-check"></i> Sí, Desactivar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
