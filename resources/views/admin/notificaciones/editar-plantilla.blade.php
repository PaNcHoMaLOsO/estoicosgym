@extends('adminlte::page')

@section('title', 'Editar Plantilla - ' . $tipoNotificacion->nombre)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark d-flex align-items-center">
                <i class="fas fa-edit mr-3" style="color: #e94560;"></i>
                Editar Plantilla
            </h1>
            <small class="text-muted">{{ $tipoNotificacion->nombre }}</small>
        </div>
        <a href="{{ route('admin.notificaciones.plantillas') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.notificaciones.plantillas.actualizar', $tipoNotificacion) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            {{-- Columna Izquierda: Configuración --}}
            <div class="col-lg-5">
                <div class="card shadow-sm" style="border-radius: 10px; border: 1px solid #dee2e6;">
                    <div class="card-header py-3 bg-white border-bottom">
                        <h5 class="mb-0 text-dark">
                            <i class="fas fa-cog mr-2 text-primary"></i> Configuración
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Código (solo lectura) --}}
                        <div class="form-group">
                            <label class="text-muted small">Código de Plantilla</label>
                            <input type="text" class="form-control-plaintext font-weight-bold" 
                                   value="{{ $tipoNotificacion->codigo }}" readonly>
                        </div>

                        {{-- Nombre --}}
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" 
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $tipoNotificacion->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Asunto del Email --}}
                        <div class="form-group">
                            <label for="asunto_email">Asunto del Email <span class="text-danger">*</span></label>
                            <input type="text" name="asunto_email" id="asunto_email" 
                                   class="form-control @error('asunto_email') is-invalid @enderror"
                                   value="{{ old('asunto_email', $tipoNotificacion->asunto_email) }}" required>
                            <small class="text-muted">Usa variables: {nombre}, {membresia}, {dias_restantes}</small>
                            @error('asunto_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Días de anticipación --}}
                        <div class="form-group">
                            <label for="dias_anticipacion">Días de Anticipación</label>
                            <input type="number" name="dias_anticipacion" id="dias_anticipacion" 
                                   class="form-control @error('dias_anticipacion') is-invalid @enderror"
                                   value="{{ old('dias_anticipacion', $tipoNotificacion->dias_anticipacion) }}"
                                   min="0" max="30">
                            <small class="text-muted">Días antes del evento para enviar la notificación</small>
                            @error('dias_anticipacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Switches --}}
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="activo" name="activo" value="1"
                                               {{ old('activo', $tipoNotificacion->activo) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="activo">
                                            <strong>Plantilla Activa</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="enviar_email" name="enviar_email" value="1"
                                               {{ old('enviar_email', $tipoNotificacion->enviar_email) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="enviar_email">
                                            <strong>Enviar por Email</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Variables Disponibles --}}
                <div class="card shadow-sm mt-4" style="border-radius: 10px; border: 1px solid #dee2e6;">
                    <div class="card-header py-3" style="background: #f8f9fa;">
                        <h6 class="mb-0">
                            <i class="fas fa-code mr-2 text-info"></i> Variables Disponibles
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td><code class="text-primary">{nombre}</code></td>
                                    <td>Nombre completo del cliente</td>
                                </tr>
                                <tr>
                                    <td><code class="text-primary">{membresia}</code></td>
                                    <td>Nombre de la membresía</td>
                                </tr>
                                <tr>
                                    <td><code class="text-primary">{fecha_vencimiento}</code></td>
                                    <td>Fecha de vencimiento</td>
                                </tr>
                                <tr>
                                    <td><code class="text-primary">{dias_restantes}</code></td>
                                    <td>Días restantes</td>
                                </tr>
                                <tr>
                                    <td><code class="text-primary">{fecha_inicio}</code></td>
                                    <td>Fecha de inicio</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Columna Derecha: Plantilla HTML --}}
            <div class="col-lg-7">
                <div class="card shadow-sm" style="border-radius: 10px; border: 1px solid #dee2e6;">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom">
                        <h5 class="mb-0 text-dark">
                            <i class="fas fa-code mr-2 text-primary"></i> Plantilla del Email (HTML)
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="togglePreview()">
                            <i class="fas fa-eye mr-1"></i> Vista Previa
                        </button>
                    </div>
                    <div class="card-body p-0">
                        {{-- Editor --}}
                        <div id="editorContainer">
                            <textarea name="plantilla_email" id="plantilla_email" 
                                      class="form-control @error('plantilla_email') is-invalid @enderror"
                                      rows="20" 
                                      style="font-family: 'Fira Code', Consolas, monospace; font-size: 13px; border: none; border-radius: 0; resize: vertical;"
                                      required>{{ old('plantilla_email', $tipoNotificacion->plantilla_email) }}</textarea>
                            @error('plantilla_email')
                                <div class="invalid-feedback d-block px-3">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Vista Previa --}}
                        <div id="previewContainer" style="display: none; padding: 20px; min-height: 400px; background: #fff;">
                            <div id="previewContent"></div>
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="mt-4 text-right">
                    <a href="{{ route('admin.notificaciones.plantillas') }}" class="btn btn-secondary mr-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .content-wrapper {
        background: #f8f9fa !important;
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #00bf8e;
        border-color: #00bf8e;
    }
    #plantilla_email:focus {
        box-shadow: none;
        border: 2px solid #4361ee;
    }
    code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 4px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let showingPreview = false;

    function togglePreview() {
        const editor = document.getElementById('editorContainer');
        const preview = document.getElementById('previewContainer');
        const previewContent = document.getElementById('previewContent');
        const plantilla = document.getElementById('plantilla_email').value;

        if (!showingPreview) {
            // Mostrar vista previa
            const rendered = plantilla
                .replace(/{nombre}/g, 'Juan Pérez')
                .replace(/{membresia}/g, 'Premium Mensual')
                .replace(/{fecha_vencimiento}/g, '{{ now()->addDays(7)->format("d/m/Y") }}')
                .replace(/{dias_restantes}/g, '7')
                .replace(/{fecha_inicio}/g, '{{ now()->subMonth()->format("d/m/Y") }}');
            
            previewContent.innerHTML = rendered;
            editor.style.display = 'none';
            preview.style.display = 'block';
            showingPreview = true;
        } else {
            // Mostrar editor
            editor.style.display = 'block';
            preview.style.display = 'none';
            showingPreview = false;
        }
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Guardado!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}'
        });
    @endif
</script>
@stop
