@extends('adminlte::page')

@section('title', 'Detalle Cliente - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Detalle del Cliente</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-warning float-right">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Éxito!</strong> {{ $message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Card Información Personal -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Información Personal</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $cliente->id }}</dd>

                        <dt class="col-sm-4">Nombre Completo:</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</strong>
                            @if($cliente->apellido_materno)
                                {{ $cliente->apellido_materno }}
                            @endif
                        </dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><a href="mailto:{{ $cliente->email }}">{{ $cliente->email }}</a></dd>

                        <dt class="col-sm-4">Teléfono:</dt>
                        <dd class="col-sm-8">{{ $cliente->celular }}</dd>

                        <dt class="col-sm-4">Ciudad:</dt>
                        <dd class="col-sm-8">{{ $cliente->direccion }}</dd>

                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            @if($cliente->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Registro:</dt>
                        <dd class="col-sm-8">{{ $cliente->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Card Estadísticas -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Estadísticas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="far fa-credit-card"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Inscripciones</span>
                                    <span class="info-box-number">{{ $cliente->inscripciones->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pagos Totales</span>
                                    <span class="info-box-number">${{ number_format($cliente->pagos->sum('monto_abonado'), 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inscripciones -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Inscripciones</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($cliente->inscripciones->count() > 0)
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Estado</th>
                                    <th>Inicio</th>
                                    <th>Vencimiento</th>
                                    <th>Pagos</th>
                                    <th>Monto Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->inscripciones as $inscripcion)
                                    <tr>
                                        <td>{{ $inscripcion->id }}</td>
                                        <td><span class="badge bg-info">{{ $inscripcion->estado->nombre ?? 'N/A' }}</span></td>
                                        <td>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</td>
                                        <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                                        <td>{{ $inscripcion->pagos->count() }}</td>
                                        <td>${{ number_format($inscripcion->pagos->sum('monto_abonado'), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-center text-muted p-3">No hay inscripciones registradas</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Botones Acción -->
    <div class="row mt-3">
        <div class="col-md-12">
            <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary">
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
@stop
