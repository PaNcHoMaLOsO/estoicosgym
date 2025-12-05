@extends('adminlte::page')

@section('title', 'Editar Convenio - EstóicosGym')

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-50: #fafbfc;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --radius-md: 12px;
        --radius-lg: 16px;
    }

    .page-header {
        background: linear-gradient(135deg, var(--warning) 0%, #d99500 100%);
        color: white;
        padding: 25px 30px;
        border-radius: var(--radius-lg);
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(240, 165, 0, 0.3);
    }

    .page-header h1 { color: white; margin: 0; font-weight: 700; }
    .page-header h1 i { color: white; }
    .page-header small { color: rgba(255,255,255,0.8); }

    .btn-header {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
    }
    .btn-header:hover { background: rgba(255,255,255,0.3); color: white; }

    .form-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        border: 1px solid var(--gray-200);
    }

    .form-card-header {
        background: var(--gray-50);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .form-card-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
    }

    .form-card-body { padding: 1.5rem; }

    .section-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--info);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid rgba(67,97,238,0.2);
    }

    .section-title i { margin-right: 0.5rem; }

    .form-label {
        font-weight: 600;
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .form-control-modern {
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .form-control-modern:focus {
        border-color: var(--info);
        box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
    }

    .form-footer {
        background: var(--gray-50);
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-cancel {
        background: white;
        border: 2px solid var(--gray-200);
        color: var(--gray-600);
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        text-decoration: none;
    }
    .btn-cancel:hover { background: var(--gray-100); color: var(--gray-600); }

    .btn-submit {
        background: var(--warning);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 1rem;
    }
    .btn-submit:hover { background: #d99500; color: white; transform: translateY(-2px); }

    .alert-modern {
        border: none;
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        background: rgba(233,69,96,0.1);
        color: #d63050;
        border-left: 4px solid var(--accent);
    }

    .custom-switch-modern {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--radius-md);
    }
</style>
@stop

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-edit"></i> Editar Convenio</h1>
                <small>{{ $convenio->nombre }}</small>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('admin.convenios.show', $convenio) }}" class="btn btn-header">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Errores -->
    @if ($errors->any())
        <div class="alert-modern">
            <strong><i class="fas fa-exclamation-circle"></i> Errores en el formulario:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario -->
    <form action="{{ route('admin.convenios.update', $convenio) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="form_token" value="{{ 'convenio_update_' . $convenio->id . '_' . uniqid() }}">
        
        <div class="form-card">
            <div class="form-card-header">
                <h3><i class="fas fa-handshake"></i> Datos del Convenio</h3>
            </div>
            <div class="form-card-body">
                <!-- Información Básica -->
                <div class="section-title"><i class="fas fa-info-circle"></i> Información Básica</div>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-modern @error('nombre') is-invalid @enderror" 
                               name="nombre" value="{{ old('nombre', $convenio->nombre) }}" required>
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select class="form-control form-control-modern @error('tipo') is-invalid @enderror" name="tipo" required>
                            <option value="">-- Seleccionar tipo --</option>
                            <option value="institucion_educativa" {{ old('tipo', $convenio->tipo) == 'institucion_educativa' ? 'selected' : '' }}>Institución Educativa</option>
                            <option value="empresa" {{ old('tipo', $convenio->tipo) == 'empresa' ? 'selected' : '' }}>Empresa</option>
                            <option value="organizacion" {{ old('tipo', $convenio->tipo) == 'organizacion' ? 'selected' : '' }}>Organización</option>
                            <option value="otro" {{ old('tipo', $convenio->tipo) == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Descripción -->
                <div class="section-title"><i class="fas fa-align-left"></i> Descripción</div>
                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <label class="form-label">Descripción del convenio</label>
                        <textarea class="form-control form-control-modern @error('descripcion') is-invalid @enderror" 
                                  name="descripcion" rows="3">{{ old('descripcion', $convenio->descripcion) }}</textarea>
                        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Contacto -->
                <div class="section-title"><i class="fas fa-user-tie"></i> Información de Contacto</div>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre del Contacto</label>
                        <input type="text" class="form-control form-control-modern @error('contacto_nombre') is-invalid @enderror" 
                               name="contacto_nombre" value="{{ old('contacto_nombre', $convenio->contacto_nombre) }}">
                        @error('contacto_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control form-control-modern @error('contacto_telefono') is-invalid @enderror" 
                               name="contacto_telefono" value="{{ old('contacto_telefono', $convenio->contacto_telefono) }}">
                        @error('contacto_telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control form-control-modern @error('contacto_email') is-invalid @enderror" 
                               name="contacto_email" value="{{ old('contacto_email', $convenio->contacto_email) }}">
                        @error('contacto_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Estado -->
                <div class="section-title"><i class="fas fa-toggle-on"></i> Estado</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="custom-switch-modern">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" {{ $convenio->activo ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">Convenio Activo</label>
                            </div>
                            <small class="text-muted">Los clientes podrán acceder a los beneficios</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <a href="{{ route('admin.convenios.show', $convenio) }}" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>
@stop
