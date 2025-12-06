@extends('adminlte::page')

@section('title', 'Plantillas de Notificación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark d-flex align-items-center">
                <i class="fas fa-palette mr-3" style="color: #e94560;"></i>
                Plantillas de Notificación
            </h1>
            <small class="text-muted">Administra las plantillas de correo para cada tipo de notificación</small>
        </div>
        <div>
            <a href="{{ route('admin.notificaciones.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    {{-- Alert informativo --}}
    <div class="alert alert-info border-0 mb-4" style="border-radius: 10px;">
        <div class="d-flex align-items-start">
            <i class="fas fa-lightbulb mr-3 mt-1" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Variables disponibles en las plantillas:</strong><br>
                <small class="text-muted">Generales:</small> 
                <code>{nombre}</code>, 
                <code>{membresia}</code>, 
                <code>{fecha_vencimiento}</code>, 
                <code>{dias_restantes}</code>,
                <code>{monto_pendiente}</code><br>
                <small class="text-muted">Festivos:</small>
                <code>{nombre_festivo}</code>,
                <code>{fecha_festivo}</code>,
                <code>{horarios_festivo}</code>,
                <code>{mensaje_adicional}</code>,
                <code>{instagram_post_url}</code>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($tipos as $tipo)
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100" style="border-radius: 10px; overflow: hidden; border: 1px solid #dee2e6;">
                @php
                    $tipoConfig = [
                        'membresia_por_vencer' => ['icon' => 'clock', 'color' => '#f0a500'],
                        'membresia_vencida' => ['icon' => 'calendar-times', 'color' => '#e94560'],
                        'bienvenida' => ['icon' => 'hand-sparkles', 'color' => '#00bf8e'],
                        'pago_completado' => ['icon' => 'check-circle', 'color' => '#00bf8e'],
                        'pago_pendiente' => ['icon' => 'credit-card', 'color' => '#6c757d'],
                        'pausa_inscripcion' => ['icon' => 'pause-circle', 'color' => '#f0a500'],
                        'activacion_inscripcion' => ['icon' => 'play-circle', 'color' => '#00bf8e'],
                        'renovacion' => ['icon' => 'sync-alt', 'color' => '#2EB872'],
                        'manual' => ['icon' => 'paper-plane', 'color' => '#0dcaf0'],
                        'notificacion_manual' => ['icon' => 'paper-plane', 'color' => '#0dcaf0'],
                        'horario_festivo' => ['icon' => 'calendar-day', 'color' => '#e94560'],
                    ];
                    $config = $tipoConfig[$tipo->codigo] ?? ['icon' => 'bell', 'color' => '#0dcaf0'];
                @endphp
                
                <div class="card-header py-3 d-flex align-items-center bg-white border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" 
                         style="width: 45px; height: 45px; background-color: {{ $config['color'] }}15;">
                        <i class="fas fa-{{ $config['icon'] }}" style="color: {{ $config['color'] }}; font-size: 1.2rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-dark">{{ $tipo->nombre }}</h5>
                        <small class="text-muted">{{ $tipo->descripcion }}</small>
                    </div>
                    <div class="ml-auto">
                        @if($tipo->activo)
                            <span class="badge badge-success">
                                <i class="fas fa-check"></i> Activo
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                <i class="fas fa-times"></i> Inactivo
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Configuración --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Días de anticipación</small>
                            <span class="font-weight-bold">{{ $tipo->dias_anticipacion }} días</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Envío por email</small>
                            @if($tipo->enviar_email)
                                <span class="text-success font-weight-bold">
                                    <i class="fas fa-check-circle"></i> Habilitado
                                </span>
                            @else
                                <span class="text-muted">
                                    <i class="fas fa-times-circle"></i> Deshabilitado
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Asunto --}}
                    <div class="mb-3">
                        <label class="text-muted small mb-1">
                            <i class="fas fa-heading mr-1"></i> Asunto del Email
                        </label>
                        <div class="form-control-plaintext border rounded px-3 py-2" style="background: #f8f9fa;">
                            {{ $tipo->asunto_email }}
                        </div>
                    </div>
                    
                    {{-- Vista previa de plantilla --}}
                    <div class="mb-3">
                        <label class="text-muted small mb-1">
                            <i class="fas fa-code mr-1"></i> Plantilla del Email
                        </label>
                        <div class="position-relative">
                            <button type="button" class="btn btn-sm btn-outline-primary position-absolute" 
                                    style="top: 5px; right: 5px; z-index: 10;"
                                    onclick="togglePreview('preview-{{ $tipo->id }}', 'code-{{ $tipo->id }}')">
                                <i class="fas fa-eye"></i> Vista previa
                            </button>
                            <div id="code-{{ $tipo->id }}" class="border rounded p-3" 
                                 style="background: #1a1a2e; max-height: 200px; overflow-y: auto; display: block;">
                                <pre style="color: #00bf8e; margin: 0; white-space: pre-wrap; font-size: 11px;">{{ $tipo->plantilla_email }}</pre>
                            </div>
                            {{-- Usamos iframe para aislar los estilos del email --}}
                            @php
                                $previewContent = str_replace(
                                    ['{nombre}', '{membresia}', '{fecha_vencimiento}', '{dias_restantes}', '{monto_pendiente}'],
                                    ['Juan Pérez', 'Premium Mensual', date('d/m/Y', strtotime('+7 days')), '7', '150.000'],
                                    $tipo->plantilla_email
                                );
                            @endphp
                            <iframe id="preview-{{ $tipo->id }}" class="border rounded w-100" 
                                 style="height: 300px; display: none; background: #fff;">
                            </iframe>
                            <script>
                                (function() {
                                    var content = @json($previewContent);
                                    var iframe = document.getElementById('preview-{{ $tipo->id }}');
                                    iframe.onload = function() {
                                        iframe.contentDocument.open();
                                        iframe.contentDocument.write(content);
                                        iframe.contentDocument.close();
                                    };
                                    if (iframe.contentDocument) {
                                        iframe.contentDocument.open();
                                        iframe.contentDocument.write(content);
                                        iframe.contentDocument.close();
                                    }
                                })();
                            </script>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-clock mr-1"></i>
                            Actualizado: {{ $tipo->updated_at->format('d/m/Y H:i') }}
                        </small>
                        <div>
                            <a href="{{ route('admin.notificaciones.plantillas.editar', $tipo) }}" 
                               class="btn btn-outline-info btn-sm">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </a>
                            <form action="{{ route('admin.notificaciones.plantillas.actualizar', $tipo) }}" 
                                  method="POST" class="d-inline toggle-form">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="nombre" value="{{ $tipo->nombre }}">
                                <input type="hidden" name="asunto_email" value="{{ $tipo->asunto_email }}">
                                <input type="hidden" name="plantilla_email" value="{{ $tipo->plantilla_email }}">
                                <input type="hidden" name="dias_anticipacion" value="{{ $tipo->dias_anticipacion }}">
                                <input type="hidden" name="activo" value="{{ $tipo->activo ? '0' : '1' }}">
                                <input type="hidden" name="enviar_email" value="{{ $tipo->enviar_email ? '1' : '0' }}">
                                <button type="submit" class="btn btn-{{ $tipo->activo ? 'outline-secondary' : 'outline-success' }} btn-sm">
                                    <i class="fas fa-{{ $tipo->activo ? 'pause' : 'play' }} mr-1"></i>
                                    {{ $tipo->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Estadísticas de uso --}}
    <div class="card shadow-sm mt-4" style="border-radius: 10px; overflow: hidden; border: 1px solid #dee2e6;">
        <div class="card-header py-3 bg-white border-bottom">
            <h5 class="mb-0 text-dark">
                <i class="fas fa-chart-pie mr-2 text-primary"></i>
                Estadísticas de Uso por Plantilla
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($tipos as $tipo)
                @php
                    $totalEnviadas = \App\Models\Notificacion::where('id_tipo_notificacion', $tipo->id)
                        ->where('id_estado', 601)
                        ->count();
                    $totalFallidas = \App\Models\Notificacion::where('id_tipo_notificacion', $tipo->id)
                        ->where('id_estado', 602)
                        ->count();
                    $totalPendientes = \App\Models\Notificacion::where('id_tipo_notificacion', $tipo->id)
                        ->where('id_estado', 600)
                        ->count();
                @endphp
                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-3">
                            <i class="fas fa-{{ $tipoConfig[$tipo->codigo]['icon'] ?? 'bell' }} mr-1" 
                               style="color: {{ $tipoConfig[$tipo->codigo]['color'] ?? '#0dcaf0' }};"></i>
                            {{ $tipo->nombre }}
                        </h6>
                        <div class="d-flex justify-content-around text-center">
                            <div>
                                <h4 class="mb-0 text-success">{{ $totalEnviadas }}</h4>
                                <small class="text-muted">Enviadas</small>
                            </div>
                            <div>
                                <h4 class="mb-0 text-warning">{{ $totalPendientes }}</h4>
                                <small class="text-muted">Pendientes</small>
                            </div>
                            <div>
                                <h4 class="mb-0 text-danger">{{ $totalFallidas }}</h4>
                                <small class="text-muted">Fallidas</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Modal Editar Plantilla --}}
<div class="modal fade" id="modalEditarPlantilla" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark">
                    <i class="fas fa-edit mr-2 text-primary"></i>
                    Editar Plantilla: <span id="modalTipoNombre"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEditarPlantilla" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Nota:</strong> La funcionalidad de edición de plantillas está disponible en la versión premium del sistema.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asunto_email">Asunto del Email</label>
                                <input type="text" class="form-control" id="asunto_email" name="asunto_email" 
                                       placeholder="Ej: ¡Hola {nombre}! Tu membresía...">
                            </div>
                            
                            <div class="form-group">
                                <label for="dias_anticipacion">Días de Anticipación</label>
                                <input type="number" class="form-control" id="dias_anticipacion" name="dias_anticipacion" 
                                       min="0" max="30">
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enviar_email" name="enviar_email">
                                    <label class="custom-control-label" for="enviar_email">Enviar por Email</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="activo" name="activo">
                                    <label class="custom-control-label" for="activo">Plantilla Activa</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plantilla_email">Plantilla HTML</label>
                                <textarea class="form-control" id="plantilla_email" name="plantilla_email" 
                                          rows="12" style="font-family: monospace; font-size: 12px;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" disabled>
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .content-wrapper {
        background: #f8f9fa !important;
    }
    
    .card {
        transition: all 0.2s ease;
    }
    .card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
    pre {
        font-family: 'Consolas', 'Monaco', monospace;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function togglePreview(previewId, codeId) {
        const preview = document.getElementById(previewId);
        const code = document.getElementById(codeId);
        
        if (preview.style.display === 'none') {
            preview.style.display = 'block';
            code.style.display = 'none';
        } else {
            preview.style.display = 'none';
            code.style.display = 'block';
        }
    }
    
    function editarPlantilla(id, nombre) {
        document.getElementById('modalTipoNombre').textContent = nombre;
        $('#modalEditarPlantilla').modal('show');
    }
    
    function toggleEstado(id, activo) {
        const accion = activo ? 'desactivar' : 'activar';
        Swal.fire({
            title: `¿${activo ? 'Desactivar' : 'Activar'} plantilla?`,
            text: `Esta acción ${activo ? 'pausará' : 'habilitará'} el envío de este tipo de notificaciones.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: activo ? '#6c757d' : '#00bf8e',
            cancelButtonColor: '#adb5bd',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Funcionalidad en desarrollo',
                    text: 'Esta característica estará disponible próximamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
</script>
@stop
