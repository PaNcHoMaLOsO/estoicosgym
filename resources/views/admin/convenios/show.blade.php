@extends('adminlte::page')

@section('title', 'Detalle Convenio - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-handshake"></i> {{ $convenio->nombre }}
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.convenios.edit', $convenio) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.convenios.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Información Principal -->
    <div class="card card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Información General
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-tag"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Nombre</span>
                            <span class="info-box-number">{{ $convenio->nombre }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tipo</span>
                            <span class="info-box-number">
                                @php
                                    $tipos = [
                                        'institucion_educativa' => 'Institución Educativa',
                                        'empresa' => 'Empresa',
                                        'organizacion' => 'Organización',
                                        'otro' => 'Otro'
                                    ];
                                @endphp
                                <span class="badge badge-info">{{ $tipos[$convenio->tipo] ?? $convenio->tipo }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon {{ $convenio->activo ? 'bg-success' : 'bg-secondary' }}">
                            <i class="fas fa-toggle-{{ $convenio->activo ? 'on' : 'off' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado</span>
                            <span class="info-box-number">
                                @if($convenio->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Creado</span>
                            <span class="info-box-number">{{ $convenio->created_at->format('d/m/Y') }}</span>
                            <small class="text-muted">{{ $convenio->created_at->format('H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-align-left"></i> Descripción</label>
                        <div class="border rounded p-3 bg-light">
                            @if ($convenio->descripcion)
                                {{ $convenio->descripcion }}
                            @else
                                <span class="text-muted">Sin descripción</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Contacto -->
    @if($convenio->contacto_nombre || $convenio->contacto_telefono || $convenio->contacto_email)
        <div class="card card-success mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-tie"></i> Información de Contacto
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($convenio->contacto_nombre)
                        <div class="col-md-4 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-user"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nombre</span>
                                    <span class="info-box-number">{{ $convenio->contacto_nombre }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($convenio->contacto_telefono)
                        <div class="col-md-4 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Teléfono</span>
                                    <span class="info-box-number">{{ $convenio->contacto_telefono }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($convenio->contacto_email)
                        <div class="col-md-4 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Email</span>
                                    <span class="info-box-number">
                                        <a href="mailto:{{ $convenio->contacto_email }}" class="text-muted">
                                            {{ $convenio->contacto_email }}
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Botones de Acción -->
    <div class="card card-light">
        <div class="card-body">
            <div class="d-flex gap-2 justify-content-between flex-wrap">
                <div>
                    <a href="{{ route('admin.convenios.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div>
                    <a href="{{ route('admin.convenios.edit', $convenio) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.convenios.destroy', $convenio) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro? Esta acción no puede revertirse')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
