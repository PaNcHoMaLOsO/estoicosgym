@extends('adminlte::page')

@section('title', 'Crear Convenio - EstóicosGym')

@section('content_header')
    <h1>Crear Nuevo Convenio</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Información del Convenio</h3>
                </div>
                <form action="{{ route('admin.convenios.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre') }}" placeholder="Ej: INACAP, Cruz Verde" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tipo -->
                        <div class="form-group">
                            <label for="tipo">Tipo <span class="text-danger">*</span></label>
                            <select class="form-control @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                                <option value="">-- Seleccionar tipo --</option>
                                <option value="institucion_educativa" {{ old('tipo') == 'institucion_educativa' ? 'selected' : '' }}>Institución Educativa</option>
                                <option value="empresa" {{ old('tipo') == 'empresa' ? 'selected' : '' }}>Empresa</option>
                                <option value="organizacion" {{ old('tipo') == 'organizacion' ? 'selected' : '' }}>Organización</option>
                                <option value="otro" {{ old('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="3" placeholder="Descripción del convenio">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Contacto Nombre -->
                        <div class="form-group">
                            <label for="contacto_nombre">Nombre del Contacto</label>
                            <input type="text" class="form-control @error('contacto_nombre') is-invalid @enderror" 
                                id="contacto_nombre" name="contacto_nombre" value="{{ old('contacto_nombre') }}" 
                                placeholder="Nombre del contacto">
                            @error('contacto_nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Contacto Teléfono -->
                        <div class="form-group">
                            <label for="contacto_telefono">Teléfono</label>
                            <input type="text" class="form-control @error('contacto_telefono') is-invalid @enderror" 
                                id="contacto_telefono" name="contacto_telefono" value="{{ old('contacto_telefono') }}" 
                                placeholder="Ej: +56 9 1234 5678">
                            @error('contacto_telefono')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Contacto Email -->
                        <div class="form-group">
                            <label for="contacto_email">Email</label>
                            <input type="email" class="form-control @error('contacto_email') is-invalid @enderror" 
                                id="contacto_email" name="contacto_email" value="{{ old('contacto_email') }}" 
                                placeholder="contacto@ejemplo.com">
                            @error('contacto_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="activo">
                                <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                                Activo
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.convenios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Convenio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
