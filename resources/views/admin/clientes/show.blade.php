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
                                    <span class="info-box-number">${{ number_format($cliente->pagos->sum('monto_abonado'), 0) }}</span>
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
                                        <td><strong>${{ number_format($inscripcion->pagos->sum('monto_abonado'), 0) }}</strong></td>
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
                <form action="{{ route('admin.clientes.destroy', $cliente) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro? Esta acción no puede revertirse')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop
