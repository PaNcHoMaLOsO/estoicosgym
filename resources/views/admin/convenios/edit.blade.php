@extends('adminlte::page')

@section('title', 'Editar Convenio - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-edit"></i> Editar Convenio: {{ $convenio->nombre }}
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.convenios.show', $convenio) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-circle"></i> Errores en el formulario
            </h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-handshake"></i> Datos del Convenio
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.convenios.update', $convenio) }}" method="POST" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <!-- Sección Información Básica -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-info-circle"></i> Información Básica
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre', $convenio->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-control @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                                <option value="">-- Seleccionar tipo --</option>
                                <option value="institucion_educativa" {{ old('tipo', $convenio->tipo) == 'institucion_educativa' ? 'selected' : '' }}>Institución Educativa</option>
                                <option value="empresa" {{ old('tipo', $convenio->tipo) == 'empresa' ? 'selected' : '' }}>Empresa</option>
                                <option value="organizacion" {{ old('tipo', $convenio->tipo) == 'organizacion' ? 'selected' : '' }}>Organización</option>
                                <option value="otro" {{ old('tipo', $convenio->tipo) == 'otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Descripción -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-align-left"></i> Descripción
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="4">{{ old('descripcion', $convenio->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Contacto -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-user-tie"></i> Información de Contacto
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="contacto_nombre" class="form-label">Nombre del Contacto</label>
                            <input type="text" class="form-control @error('contacto_nombre') is-invalid @enderror" 
                                id="contacto_nombre" name="contacto_nombre" value="{{ old('contacto_nombre', $convenio->contacto_nombre) }}">
                            @error('contacto_nombre')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="contacto_telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('contacto_telefono') is-invalid @enderror" 
                                id="contacto_telefono" name="contacto_telefono" value="{{ old('contacto_telefono', $convenio->contacto_telefono) }}">
                            @error('contacto_telefono')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="contacto_email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('contacto_email') is-invalid @enderror" 
                                id="contacto_email" name="contacto_email" value="{{ old('contacto_email', $convenio->contacto_email) }}">
                            @error('contacto_email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Estado -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-toggle-on"></i> Estado
                        </h5>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" {{ $convenio->activo ? 'checked' : '' }}>
                            <label class="custom-control-label" for="activo">Convenio Activo</label>
                        </div>
                        <small class="d-block text-muted mt-2">Los clientes podrán acceder a los beneficios</small>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Botones de Acción -->
                <div class="form-group d-flex gap-2 justify-content-between flex-wrap">
                    <div>
                        <a href="{{ route('admin.convenios.show', $convenio) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Actualizar Convenio
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
