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

    <!-- Información Personal -->
    <div class="row mb-4">
        <!-- Card Información Personal -->
        <div class="col-lg-6 mb-4">
            <div class="card card-primary card-outline">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Información Personal
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-5">
                            <dt>ID:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd><span class="badge badge-primary">{{ $cliente->id }}</span></dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>RUT/Pasaporte:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd><strong>{{ $cliente->run_pasaporte }}</strong></dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Nombre Completo:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd>
                                <strong>{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</strong>
                                @if($cliente->apellido_materno)
                                    {{ $cliente->apellido_materno }}
                                @endif
                            </dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Email:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd><a href="mailto:{{ $cliente->email }}" class="text-primary">{{ $cliente->email }}</a></dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Celular:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd><a href="tel:{{ $cliente->celular }}" class="text-primary">{{ $cliente->celular }}</a></dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Contacto Emergencia:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd>
                                @if($cliente->contacto_emergencia)
                                    <strong>{{ $cliente->contacto_emergencia }}</strong><br>
                                    <a href="tel:{{ $cliente->telefono_emergencia }}" class="text-primary">{{ $cliente->telefono_emergencia }}</a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Dirección:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd>{{ $cliente->direccion ?? '<span class="text-muted">N/A</span>' }}</dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Fecha Nacimiento:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd>{{ $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('d/m/Y') : '<span class="text-muted">N/A</span>' }}</dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Estado:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd>
                                @if($cliente->activo)
                                    <span class="badge badge-success badge-lg">
                                        <i class="fas fa-check-circle"></i> Activo
                                    </span>
                                @else
                                    <span class="badge badge-secondary badge-lg">
                                        <i class="fas fa-ban"></i> Inactivo
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Registro:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd><small class="text-muted">{{ $cliente->created_at->format('d/m/Y H:i') }}</small></dd>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row">
                        <div class="col-sm-5">
                            <dt>Última Actualización:</dt>
                        </div>
                        <div class="col-sm-7">
                            <dd><small class="text-muted">{{ $cliente->updated_at->format('d/m/Y H:i') }}</small></dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Observaciones -->
            @if($cliente->observaciones)
                <div class="card card-secondary card-outline mt-4">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title">
                            <i class="fas fa-sticky-note"></i> Observaciones
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $cliente->observaciones }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagos Históricos -->
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

        <!-- Card Estadísticas -->
        <div class="col-lg-6 mb-4">
            <div class="card card-info card-outline">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Estadísticas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="far fa-credit-card"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Inscripciones</span>
                                    <span class="info-box-number">{{ $cliente->inscripciones->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pagos Totales</span>
                                    <span class="info-box-number">${{ number_format($cliente->pagos->sum('monto_abonado'), 0, '.', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Convenio -->
            @if($cliente->convenio)
                <div class="card card-success card-outline">
                    <div class="card-header bg-success">
                        <h3 class="card-title">
                            <i class="fas fa-handshake"></i> Convenio
                        </h3>
                    </div>
                    <div class="card-body">
                        <h5>{{ $cliente->convenio->nombre }}</h5>
                        <p class="text-muted mb-0">{{ $cliente->convenio->descripcion }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Inscripciones -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-warning card-outline">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Inscripciones
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
