@extends('adminlte::page')

@section('title', $membresia->nombre)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>{{ $membresia->nombre }}</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.membresias.edit', $membresia) }}" class="btn btn-warning float-right mr-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.membresias.index') }}" class="btn btn-secondary float-right mr-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Éxito!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Información Principal -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8">{{ $membresia->nombre }}</dd>

                        <dt class="col-sm-4">Duración:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-primary" style="font-size: 1em;">
                                {{ $membresia->duracion_dias }} días
                            </span>
                            @if ($membresia->duracion_meses > 0)
                                <small class="text-muted">({{ $membresia->duracion_meses }} meses)</small>
                            @endif
                        </dd>

                        @php
                            $precioActual = $membresia->precios->where('activo', true)->first() ?? $membresia->precios->last();
                        @endphp

                        <dt class="col-sm-4">Precio Actual:</dt>
                        <dd class="col-sm-8">
                            @if ($precioActual)
                                <span class="badge badge-success">
                                    ${{ number_format($precioActual->precio_normal, 2) }}
                                </span>
                            @else
                                N/A
                            @endif
                        </dd>

                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            @if ($membresia->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Inscripciones:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-info">{{ $membresia->inscripciones->count() }}</span>
                        </dd>

                        @if ($membresia->descripcion)
                            <dt class="col-sm-4">Descripción:</dt>
                            <dd class="col-sm-8">{{ $membresia->descripcion }}</dd>
                        @endif

                        @if ($membresia->created_at)
                            <dt class="col-sm-4">Creado:</dt>
                            <dd class="col-sm-8">{{ $membresia->created_at->format('d/m/Y H:i') }}</dd>
                        @endif

                        @if ($membresia->updated_at)
                            <dt class="col-sm-4">Actualizado:</dt>
                            <dd class="col-sm-8">{{ $membresia->updated_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Resumen de Precios -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Historial de Precios</h3>
                </div>
                <div class="card-body">
                    @if ($membresia->precios->count())
                        <div class="timeline">
                            @foreach ($membresia->precios->sortByDesc('fecha_vigencia_desde') as $precio)
                                <div class="time-label">
                                    <span class="bg-{{ $precio->activo ? 'success' : 'secondary' }}">
                                        {{ $precio->fecha_vigencia_desde->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-dollar-sign bg-{{ $precio->activo ? 'success' : 'gray' }}"></i>
                                    <div class="timeline-item">
                                        <h3 class="timeline-header">
                                            ${{ number_format($precio->precio_normal, 2) }}
                                            @if ($precio->activo)
                                                <span class="badge badge-success">Vigente</span>
                                            @endif
                                        </h3>
                                        @if ($precio->fecha_vigencia_hasta)
                                            <div class="timeline-body">
                                                Hasta: {{ $precio->fecha_vigencia_hasta->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Sin historial de precios</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Cambios -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Historial de Cambios de Precios
                    </h3>
                </div>
                <div class="card-body">
                    @if ($historialPrecios->count())
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Precio Anterior</th>
                                    <th>Precio Nuevo</th>
                                    <th>Diferencia</th>
                                    <th>Razón del Cambio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historialPrecios as $cambio)
                                    <tr>
                                        <td>{{ $cambio->created_at ? $cambio->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $cambio->usuario_cambio }}</span>
                                        </td>
                                        <td>${{ number_format($cambio->precio_anterior, 2) }}</td>
                                        <td>${{ number_format($cambio->precio_nuevo, 2) }}</td>
                                        <td>
                                            @php
                                                $diferencia = $cambio->precio_nuevo - $cambio->precio_anterior;
                                                $clase = $diferencia > 0 ? 'danger' : ($diferencia < 0 ? 'success' : 'secondary');
                                            @endphp
                                            <span class="badge badge-{{ $clase }}">
                                                {{ $diferencia >= 0 ? '+' : '' }}${{ number_format($diferencia, 2) }}
                                            </span>
                                        </td>
                                        <td>{{ $cambio->razon_cambio }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $historialPrecios->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <p class="text-muted">Sin cambios de precios registrados</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inscripciones de esta Membresía -->
    @if ($membresia->inscripciones->count())
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Inscripciones
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($membresia->inscripciones->take(10) as $inscripcion)
                                    <tr>
                                        <td>{{ $inscripcion->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                                {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                                            </a>
                                        </td>
                                        <td>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</td>
                                        <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                                        <td>${{ number_format($inscripcion->precio_base, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $inscripcion->estado->id == 1 ? 'warning' : ($inscripcion->estado->id == 2 ? 'success' : 'secondary') }}">
                                                {{ $inscripcion->estado->nombre }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if ($membresia->inscripciones->count() > 10)
                            <p class="text-muted">Mostrando 10 de {{ $membresia->inscripciones->count() }} inscripciones</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
