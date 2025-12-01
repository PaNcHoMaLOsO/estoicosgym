@extends('adminlte::page')

@section('title', 'Detalle Notificación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark d-flex align-items-center">
                <i class="fas fa-bell mr-3 text-info"></i>
                Detalle de Notificación
            </h1>
            <small class="text-muted">ID: #{{ $notificacion->id }}</small>
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
    <div class="row">
        {{-- Información Principal --}}
        <div class="col-lg-8">
            {{-- Card Estado --}}
            <div class="card shadow-sm mb-4" style="border-radius: 10px; overflow: hidden; border: 1px solid #dee2e6;">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        Estado de la Notificación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                @php
                                    $estadoConfig = [
                                        600 => ['icon' => 'clock', 'color' => 'warning', 'bg' => '#fff3cd'],
                                        601 => ['icon' => 'check-circle', 'color' => 'success', 'bg' => '#d4edda'],
                                        602 => ['icon' => 'times-circle', 'color' => 'danger', 'bg' => '#f8d7da'],
                                        603 => ['icon' => 'ban', 'color' => 'secondary', 'bg' => '#e2e3e5'],
                                    ];
                                    $config = $estadoConfig[$notificacion->id_estado] ?? ['icon' => 'question', 'color' => 'info', 'bg' => '#d1ecf1'];
                                @endphp
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" 
                                     style="width: 60px; height: 60px; background: {{ $config['bg'] }};">
                                    <i class="fas fa-{{ $config['icon'] }} text-{{ $config['color'] }}" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-{{ $config['color'] }}">{{ $notificacion->estado->nombre ?? 'Desconocido' }}</h4>
                                    <small class="text-muted">{{ $notificacion->estado->descripcion ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-right mt-3 mt-md-0">
                            @if($notificacion->id_estado == 600)
                                <form action="{{ route('admin.notificaciones.reenviar', $notificacion) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-paper-plane mr-1"></i> Enviar Ahora
                                    </button>
                                </form>
                                <form action="{{ route('admin.notificaciones.cancelar', $notificacion) }}" method="POST" class="d-inline ml-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="fas fa-ban mr-1"></i> Cancelar
                                    </button>
                                </form>
                            @elseif($notificacion->id_estado == 602 && $notificacion->intentos < 3)
                                <form action="{{ route('admin.notificaciones.reenviar', $notificacion) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-redo mr-1"></i> Reintentar Envío
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Destinatario --}}
            <div class="card shadow-sm mb-4" style="border-radius: 10px; overflow: hidden; border: 1px solid #dee2e6;">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        Información del Destinatario
                    </h5>
                </div>
                <div class="card-body">
                    @if($notificacion->cliente)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Nombre Completo</label>
                                    <p class="mb-0 font-weight-bold" style="font-size: 1.1rem;">
                                        {{ $notificacion->cliente->user->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Email</label>
                                    <p class="mb-0">
                                        <i class="fas fa-envelope text-muted mr-1"></i>
                                        {{ $notificacion->email_destino ?? $notificacion->cliente->user->email ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Teléfono</label>
                                    <p class="mb-0">
                                        <i class="fas fa-phone text-muted mr-1"></i>
                                        {{ $notificacion->cliente->telefono ?? 'No registrado' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Documento</label>
                                    <p class="mb-0">
                                        <i class="fas fa-id-card text-muted mr-1"></i>
                                        {{ $notificacion->cliente->tipo_documento ?? '' }} {{ $notificacion->cliente->numero_documento ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('clientes.show', $notificacion->cliente) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver perfil del cliente
                        </a>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">Cliente no disponible</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card Contenido Email --}}
            <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: none;">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-envelope-open-text mr-2 text-primary"></i>
                        Contenido del Email
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Asunto</label>
                        <p class="mb-0 font-weight-bold" style="font-size: 1.1rem;">
                            {{ $notificacion->asunto ?? $notificacion->tipoNotificacion->asunto_email ?? 'Sin asunto' }}
                        </p>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="text-muted small mb-2 d-block">Vista Previa del Contenido</label>
                        <div class="border rounded p-3" style="background: #fafafa; max-height: 400px; overflow-y: auto;">
                            @if($notificacion->contenido_html)
                                {!! $notificacion->contenido_html !!}
                            @elseif($notificacion->tipoNotificacion && $notificacion->tipoNotificacion->plantilla_email)
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-code mb-2" style="font-size: 2rem;"></i>
                                    <p class="mb-0">El contenido se generará usando la plantilla del tipo de notificación</p>
                                </div>
                            @else
                                <p class="text-muted text-center mb-0">Sin contenido disponible</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Historial de Intentos --}}
            @if($notificacion->logs && $notificacion->logs->count() > 0)
            <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: none;">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-history mr-2 text-secondary"></i>
                        Historial de Envíos
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th class="border-0">Fecha</th>
                                    <th class="border-0">Resultado</th>
                                    <th class="border-0">Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notificacion->logs as $log)
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($log->exitoso)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check mr-1"></i> Exitoso
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times mr-1"></i> Fallido
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $log->mensaje ?? 'Sin mensaje' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Panel Lateral --}}
        <div class="col-lg-4">
            {{-- Tipo de Notificación --}}
            <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: none;">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-tags mr-2"></i>
                        Tipo de Notificación
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($notificacion->tipoNotificacion)
                        @php
                            $tipoIcons = [
                                'membresia_por_vencer' => ['icon' => 'clock', 'color' => '#f0a500'],
                                'membresia_vencida' => ['icon' => 'calendar-times', 'color' => '#e94560'],
                                'bienvenida' => ['icon' => 'hand-sparkles', 'color' => '#00bf8e'],
                                'pago_pendiente' => ['icon' => 'credit-card', 'color' => '#5c636a'],
                            ];
                            $tipoConfig = $tipoIcons[$notificacion->tipoNotificacion->codigo] ?? ['icon' => 'bell', 'color' => '#0dcaf0'];
                        @endphp
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px; background: {{ $tipoConfig['color'] }}20;">
                            <i class="fas fa-{{ $tipoConfig['icon'] }}" style="font-size: 2rem; color: {{ $tipoConfig['color'] }};"></i>
                        </div>
                        <h5 class="mb-1">{{ $notificacion->tipoNotificacion->nombre }}</h5>
                        <p class="text-muted small mb-0">{{ $notificacion->tipoNotificacion->descripcion }}</p>
                    @else
                        <p class="text-muted mb-0">Tipo no especificado</p>
                    @endif
                </div>
            </div>

            {{-- Datos Técnicos --}}
            <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: none;">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-cog mr-2 text-secondary"></i>
                        Datos Técnicos
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="py-2 border-bottom d-flex justify-content-between">
                            <span class="text-muted">Programada para:</span>
                            <span class="font-weight-bold">
                                {{ $notificacion->fecha_programada ? $notificacion->fecha_programada->format('d/m/Y') : 'N/A' }}
                            </span>
                        </li>
                        <li class="py-2 border-bottom d-flex justify-content-between">
                            <span class="text-muted">Fecha de envío:</span>
                            <span class="font-weight-bold">
                                {{ $notificacion->fecha_envio ? $notificacion->fecha_envio->format('d/m/Y H:i') : 'Pendiente' }}
                            </span>
                        </li>
                        <li class="py-2 border-bottom d-flex justify-content-between">
                            <span class="text-muted">Intentos:</span>
                            <span>
                                <span class="badge badge-{{ $notificacion->intentos >= 3 ? 'danger' : 'info' }}">
                                    {{ $notificacion->intentos ?? 0 }} / 3
                                </span>
                            </span>
                        </li>
                        <li class="py-2 border-bottom d-flex justify-content-between">
                            <span class="text-muted">Creada:</span>
                            <span class="font-weight-bold">{{ $notificacion->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="py-2 d-flex justify-content-between">
                            <span class="text-muted">Actualizada:</span>
                            <span class="font-weight-bold">{{ $notificacion->updated_at->format('d/m/Y H:i') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Inscripción Relacionada --}}
            @if($notificacion->inscripcion)
            <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: none;">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-file-contract mr-2 text-success"></i>
                        Inscripción Relacionada
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-muted">ID Inscripción:</span>
                        <span class="font-weight-bold">#{{ $notificacion->inscripcion->id }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Membresía:</span>
                        <span class="font-weight-bold">{{ $notificacion->inscripcion->membresia->nombre ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Vencimiento:</span>
                        <span class="font-weight-bold">
                            {{ $notificacion->inscripcion->fecha_fin ? \Carbon\Carbon::parse($notificacion->inscripcion->fecha_fin)->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    <a href="{{ route('inscripciones.show', $notificacion->inscripcion) }}" class="btn btn-outline-success btn-sm btn-block mt-3">
                        <i class="fas fa-external-link-alt mr-1"></i> Ver Inscripción
                    </a>
                </div>
            </div>
            @endif
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
    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
        });
    @endif
</script>
@stop
