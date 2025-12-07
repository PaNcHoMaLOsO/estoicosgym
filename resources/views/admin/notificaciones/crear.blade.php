@extends('adminlte::page')

@section('title', 'Nueva Notificación - Estoicos Gym')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<style>
    /* Reset y base */
    * {
        box-sizing: border-box;
    }

    .content-wrapper {
        background: #f4f6f9 !important;
    }

    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --accent-light: #ff6b6b;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
    }

    /* Header */
    .page-header {
        background: white;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        margin-bottom: 24px;
    }

    .page-header h1 {
        margin: 0;
        font-weight: 700;
        font-size: 1.75rem;
        color: #2c3e50;
    }

    .page-header h1 i {
        color: var(--accent);
        margin-right: 12px;
    }

    .page-header p {
        margin: 8px 0 0;
        color: #7f8c8d;
        font-size: 0.95rem;
    }

    /* Main Container - Asegurar que esté sobre el sidebar */
    .content {
        position: relative;
        z-index: 100 !important;
    }

    /* Cards */
    .main-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
        overflow: visible;
        width: 100%;
        max-width: 100%;
    }

    .main-card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 16px 16px 0 0;
    }

    .main-card-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .main-card-header h3 i {
        color: var(--accent);
        margin-right: 8px;
    }

    .main-card-body {
        padding: 24px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    /* Paso 3 - Sin vista previa */
    #seccionMensaje {
        margin-bottom: 20px;
    }

    /* Indicador de pasos */
    .pasos-indicador {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 15px;
        padding: 12px 15px;
        background: white;
        border-radius: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        max-width: 650px;
        margin-left: auto;
        margin-right: auto;
    }

    .paso {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--gray-600);
        padding: 6px 10px;
        border-radius: 20px;
        transition: all 0.3s ease;
        position: relative;
    }

    .paso::after {
        content: '→';
        position: absolute;
        right: -18px;
        color: var(--gray-200);
        font-weight: bold;
    }

    .paso:last-child::after {
        display: none;
    }

    .paso.active {
        color: var(--accent);
        background: rgba(233, 69, 96, 0.1);
    }

    .paso.completed {
        color: var(--success);
        background: rgba(0, 191, 142, 0.1);
    }

    .paso-numero {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
        transition: all 0.3s ease;
    }

    .paso.active .paso-numero {
        background: var(--accent);
        color: white;
        box-shadow: 0 3px 10px rgba(233, 69, 96, 0.3);
    }

    .paso.completed .paso-numero {
        background: var(--success);
        color: white;
    }

    .paso-texto {
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Filtros rápidos */
    .filtros-rapidos {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .filtro-btn {
        padding: 6px 14px;
        border-radius: 20px;
        border: 2px solid var(--gray-200);
        background: white;
        color: var(--gray-600);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.8rem;
    }

    .filtro-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
        transform: translateY(-2px);
    }

    .filtro-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(233, 69, 96, 0.3);
    }

    .filtro-btn .badge {
        margin-left: 6px;
        padding: 2px 6px;
        border-radius: 8px;
        font-size: 0.7rem;
    }

    .filtro-btn.active .badge {
        background: rgba(255,255,255,0.3);
    }

    /* Info de selección */
    .seleccion-info {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(233, 69, 96, 0.3);
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .seleccion-info.hidden {
        display: none;
    }

    .seleccion-count {
        font-size: 1.1rem;
        font-weight: 700;
    }

    /* Tabla de clientes */
    .tabla-clientes {
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        overflow: hidden;
    }

    /* Arreglar tamaño del selector de registros */
    .dataTables_length select {
        padding: 4px 8px;
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        font-size: 0.875rem;
        margin: 0 8px;
        min-width: 60px;
        background: white;
    }

    .dataTables_length label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dataTables_filter label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-600);
    }

    .dataTables_filter input {
        padding: 6px 12px;
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        font-size: 0.875rem;
        margin-left: 8px;
    }

    .tabla-clientes thead th {
        background: var(--gray-100);
        border-bottom: 2px solid var(--gray-200);
        padding: 12px 15px;
        font-weight: 600;
        color: var(--gray-800);
        font-size: 0.9rem;
    }

    .tabla-clientes tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .tabla-clientes tbody tr:hover {
        background-color: var(--gray-100);
    }

    .cliente-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cliente-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .cliente-nombre {
        font-weight: 600;
        color: var(--gray-800);
        font-size: 0.95rem;
    }

    .cliente-email {
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .badge-estado {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-activo { background: rgba(0, 191, 142, 0.15); color: var(--success); }
    .badge-inactivo { background: rgba(108, 117, 125, 0.15); color: var(--gray-600); }
    .badge-vencido { background: rgba(233, 69, 96, 0.15); color: var(--accent); }

    /* Plantillas */
    .plantillas-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: flex-start;
    }

    .plantilla-card {
        background: white;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 15px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        min-width: 140px;
        flex: 0 0 auto;
    }

    .plantilla-card:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .plantilla-card.selected {
        border-color: var(--accent);
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(233, 69, 96, 0.25);
    }

    .plantilla-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.3rem;
        transition: all 0.3s ease;
        background: var(--gray-100);
    }

    .plantilla-card.selected .plantilla-icon {
        background: rgba(255,255,255,0.25);
        color: white;
        transform: scale(1.1);
    }

    .plantilla-nombre {
        font-weight: 600;
        font-size: 0.85rem;
        line-height: 1.2;
    }

    /* Formulario */
    .form-group label {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 5px;
        display: block;
        font-size: 0.9rem;
    }

    .form-group label i {
        color: var(--accent);
        margin-right: 4px;
    }

    .form-control {
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        padding: 8px 12px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
    }

    /* Summernote customization */
    .note-editor.note-frame {
        border: 2px solid var(--gray-200);
        border-radius: 8px;
    }

    .note-editor.note-frame.focused {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
    }

    .note-toolbar {
        background: var(--gray-100) !important;
        border-bottom: 2px solid var(--gray-200) !important;
    }

    .note-btn-group .note-btn {
        background: white !important;
        border: 1px solid var(--gray-200) !important;
        color: var(--gray-800) !important;
    }

    .note-btn-group .note-btn:hover {
        background: var(--accent) !important;
        color: white !important;
        border-color: var(--accent) !important;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .form-control.is-valid {
        border-color: var(--success);
    }

    .invalid-feedback, .valid-feedback {
        display: block;
        margin-top: 5px;
        font-size: 0.85rem;
    }

    .char-counter {
        font-size: 0.85rem;
        color: var(--gray-600);
        margin-top: 5px;
        text-align: right;
    }

    .char-counter.warning {
        color: var(--warning);
    }

    .char-counter.danger {
        color: var(--accent);
    }

    /* Variables rápidas */
    .variables-rapidas {
        margin-top: 8px;
        padding: 8px;
        background: var(--gray-100);
        border-radius: 6px;
    }

    .variable-btn {
        display: inline-block;
        padding: 4px 10px;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 15px;
        margin: 2px;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .variable-btn:hover {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
        transform: scale(1.05);
    }



    /* Botones */
    .btn-enviar {
        background: linear-gradient(135deg, var(--success) 0%, #00a67d 100%);
        border: none;
        color: white;
        padding: 10px 30px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 191, 142, 0.3);
    }

    .btn-enviar::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }

    .btn-enviar:hover::before {
        left: 100%;
    }

    .btn-enviar:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(0, 191, 142, 0.4);
        color: white;
    }

    .btn-enviar:disabled {
        background: var(--gray-200);
        color: var(--gray-600);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-enviar:disabled::before {
        display: none;
    }

    /* Alertas de validación */
    .alert-validacion {
        background: #fff3cd;
        border-left: 4px solid var(--warning);
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        display: none;
        font-size: 0.85rem;
    }

    .alert-validacion.show {
        display: block;
        animation: slideIn 0.3s ease;
    }

    .alert-validacion ul {
        margin: 0;
        padding-left: 20px;
    }

    .alert-validacion li {
        margin: 5px 0;
        color: #856404;
    }
</style>
@endsection

@section('content_header')
    <div class="page-header">
        <h1><i class="fas fa-paper-plane"></i> Nueva Notificación</h1>
        <p>Envía comunicados personalizados a tus clientes</p>
    </div>
@stop

@section('content')
    <!-- Indicador de pasos -->
    <div class="pasos-indicador">
        <div class="paso active" id="paso1">
            <span class="paso-numero">1</span>
            <span class="paso-texto">Clientes</span>
        </div>
        <div class="paso" id="paso2">
            <span class="paso-numero">2</span>
            <span class="paso-texto">Plantilla</span>
        </div>
        <div class="paso" id="paso3">
            <span class="paso-numero">3</span>
            <span class="paso-texto">Mensaje</span>
        </div>
        <div class="paso" id="paso4">
            <span class="paso-numero">4</span>
            <span class="paso-texto">Enviar</span>
        </div>
    </div>

    <!-- Alerta de validación -->
    <div class="alert-validacion" id="alertValidacion">
        <strong><i class="fas fa-exclamation-triangle"></i> Completa los siguientes campos:</strong>
        <ul id="listaValidacion"></ul>
    </div>

    <form action="{{ route('admin.notificaciones.enviar-masivo') }}" method="POST" id="comunicadoForm">
        @csrf
        <input type="hidden" name="cliente_ids" id="clienteIdsInput" value="">
        <input type="hidden" name="plantilla_id" id="plantillaIdInput" value="">

        {{-- PASO 1: DESTINATARIOS --}}
        <div class="main-card mb-4">
            <div class="main-card-header">
                <h3><i class="fas fa-users"></i> 1. Seleccionar Destinatarios</h3>
                <button type="button" class="btn btn-sm btn-light" id="btnSeleccionarTodos">
                    <i class="fas fa-check-double"></i> Todos los visibles
                </button>
            </div>
            <div class="main-card-body">
                {{-- Filtros rápidos --}}
                <div class="filtros-rapidos">
                            <button type="button" class="filtro-btn active" data-filtro="todos">
                                <i class="fas fa-users"></i> Todos
                                <span class="badge badge-secondary">{{ $totalClientes ?? 0 }}</span>
                            </button>
                            <button type="button" class="filtro-btn" data-filtro="activos">
                                <i class="fas fa-check-circle"></i> Activos
                                <span class="badge badge-success">{{ $clientesActivos ?? 0 }}</span>
                            </button>
                            <button type="button" class="filtro-btn" data-filtro="vencidos">
                                <i class="fas fa-calendar-times"></i> Vencidos
                                <span class="badge badge-danger">{{ $clientesVencidos ?? 0 }}</span>
                            </button>
                            <button type="button" class="filtro-btn" data-filtro="inactivos">
                                <i class="fas fa-user-slash"></i> Sin inscripción
                                <span class="badge badge-warning">{{ $clientesInactivos ?? 0 }}</span>
                            </button>
                        </div>

                        {{-- Info de selección --}}
                        <div class="seleccion-info hidden" id="seleccionInfo">
                            <span class="seleccion-count">
                                <i class="fas fa-check-circle"></i> 
                                <span id="seleccionCount">0</span> clientes seleccionados
                            </span>
                            <button type="button" class="btn btn-sm btn-light" id="btnLimpiarSeleccion">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>

                        {{-- Tabla de clientes --}}
                        <div class="tabla-clientes">
                            <table class="table table-hover mb-0" id="tablaClientes" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="custom-checkbox" id="checkAll">
                                        </th>
                                        <th>Cliente</th>
                                        <th>Membresía</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clientes as $cliente)
                                    @php
                                        $inscripcionActiva = $cliente->inscripciones->where('id_estado', 100)->first();
                                        $ultimaInscripcion = $cliente->inscripciones->sortByDesc('created_at')->first();
                                        
                                        if ($inscripcionActiva) {
                                            $estado = 'Activo';
                                            $estadoClass = 'activo';
                                        } elseif ($ultimaInscripcion && $ultimaInscripcion->id_estado == 102) {
                                            $estado = 'Vencido';
                                            $estadoClass = 'vencido';
                                        } else {
                                            $estado = 'Inactivo';
                                            $estadoClass = 'inactivo';
                                        }
                                        
                                        $tieneEmail = !empty($cliente->email);
                                    @endphp
                                    <tr class="{{ !$tieneEmail ? 'sin-email' : '' }}" data-estado="{{ $estadoClass }}">
                                        <td>
                                            @if($tieneEmail)
                                            <input type="checkbox" 
                                                   class="custom-checkbox cliente-check" 
                                                   value="{{ $cliente->id }}"
                                                   data-nombre="{{ $cliente->nombre_completo }}"
                                                   data-email="{{ $cliente->email }}">
                                            @else
                                            <i class="fas fa-ban text-muted" title="Sin email"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="cliente-info">
                                                <div class="cliente-avatar">
                                                    {{ strtoupper(substr($cliente->nombres, 0, 1)) }}{{ strtoupper(substr($cliente->apellido_paterno, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="cliente-nombre">{{ $cliente->nombre_completo }}</div>
                                                    <div class="cliente-email">
                                                        @if($tieneEmail)
                                                            {{ $cliente->email }}
                                                        @else
                                                            <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Sin email</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($inscripcionActiva && $inscripcionActiva->membresia)
                                                {{ $inscripcionActiva->membresia->nombre }}
                                            @elseif($ultimaInscripcion && $ultimaInscripcion->membresia)
                                                <span class="text-muted">{{ $ultimaInscripcion->membresia->nombre }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-estado badge-{{ $estadoClass }}">
                                                @if($estadoClass == 'activo')
                                                    <i class="fas fa-check"></i>
                                                @elseif($estadoClass == 'vencido')
                                                    <i class="fas fa-calendar-times"></i>
                                                @else
                                                    <i class="fas fa-user-slash"></i>
                                                @endif
                                                {{ $estado }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PASO 2: PLANTILLAS --}}
        <div class="main-card mb-4" id="seccionPlantillas" style="display:none;">
            <div class="main-card-header">
                <h3><i class="fas fa-file-alt"></i> 2. Seleccionar Plantilla</h3>
            </div>
            <div class="main-card-body">
                <div class="plantillas-grid">
                            @foreach($plantillas as $plantilla)
                            <div class="plantilla-card" 
                                 data-id="{{ $plantilla->id }}"
                                 data-nombre="{{ $plantilla->nombre }}"
                                 data-asunto="{{ $plantilla->asunto_email }}"
                                 data-contenido="{{ $plantilla->plantilla_email }}">
                                <div class="plantilla-icon">
                                    @switch($plantilla->codigo)
                                        @case('membresia_por_vencer')
                                            <i class="fas fa-clock"></i>
                                            @break
                                        @case('membresia_vencida')
                                            <i class="fas fa-calendar-times"></i>
                                            @break
                                        @case('bienvenida')
                                            <i class="fas fa-hand-sparkles"></i>
                                            @break
                                        @case('pago_pendiente')
                                            <i class="fas fa-dollar-sign"></i>
                                            @break
                                        @default
                                            <i class="fas fa-envelope"></i>
                                    @endswitch
                                </div>
                                <div class="plantilla-nombre">{{ $plantilla->nombre }}</div>
                            </div>
                            @endforeach
                            
                            {{-- Plantilla personalizada --}}
                            <div class="plantilla-card" 
                                 data-id="custom"
                                 data-nombre="Personalizado"
                                 data-asunto=""
                                 data-contenido="">
                                <div class="plantilla-icon">
                                    <i class="fas fa-pen-fancy"></i>
                                </div>
                                <div class="plantilla-nombre">Personalizado</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PASO 3: COMPONER MENSAJE --}}
        <div class="main-card mb-4" id="seccionMensaje" style="display:none;">
            {{-- Editor de Mensaje --}}
                <div class="main-card-header">
                    <h3><i class="fas fa-edit"></i> 3. Componer Mensaje</h3>
                </div>
                <div class="main-card-body">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Asunto del Correo *</label>
                        <input type="text" 
                               class="form-control" 
                               id="asunto" 
                               name="asunto" 
                               placeholder="Ej: ¡Promoción especial para ti!"
                               maxlength="255"
                               required>
                        <div class="char-counter">
                            <span id="asuntoCount">0</span>/255 caracteres
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Mensaje *</label>
                        <textarea class="form-control" 
                                  id="mensaje" 
                                  name="mensaje" 
                                  placeholder="Escribe tu mensaje aquí..."
                                  required></textarea>
                        <div class="char-counter">
                            <span id="mensajeCount">0</span>/5000 caracteres
                        </div>
                        
                        <div class="variables-rapidas">
                            <small class="text-muted d-block mb-2"><i class="fas fa-magic"></i> Insertar variable:</small>
                            <span class="variable-btn" data-var="{nombre}">📛 Nombre</span>
                            <span class="variable-btn" data-var="{email}">📧 Correo</span>
                            <span class="variable-btn" data-var="{membresia}">🏋️ Membresía</span>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PASO 4: ENVIAR --}}
        <div class="text-center mb-4" id="seccionEnviar" style="display:none;">
            <button type="submit" class="btn btn-enviar btn-lg" id="btnEnviar" disabled>
                <i class="fas fa-paper-plane"></i> 4. Enviar a <span id="btnCount">0</span> clientes
            </button>
            <p class="text-muted mt-2 mb-0">
                <small><i class="fas fa-info-circle"></i> Los correos se enviarán inmediatamente</small>
            </p>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-es-ES.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar Summernote
    $('#mensaje').summernote({
        height: 350,
        lang: 'es-ES',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['codeview', 'help']]
        ],
        styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana'],
        placeholder: 'Escribe tu mensaje aquí... Puedes usar formato HTML',
        callbacks: {
            onChange: function(contents, $editable) {
                // Actualizar contador y preview en tiempo real
                const textLength = $editable.text().length;
                $('#mensajeCount').text(textLength);
                
                // Cambiar color según límite
                if (textLength > 4500) {
                    $('#mensajeCount').parent().addClass('warning');
                } else {
                    $('#mensajeCount').parent().removeClass('warning');
                }
                
                validarFormulario();
            },
            onPaste: function(e) {
                // Permitir pegar HTML sin problemas
                const bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('text/html');
                e.preventDefault();
                
                // Limpiar y sanitizar el HTML pegado
                const cleanHtml = bufferText.replace(/<(script|iframe|object|embed)[^>]*>.*?<\/\1>/gi, '');
                document.execCommand('insertHTML', false, cleanHtml);
            }
        }
    });
    // Inicializar DataTable
    const tabla = $('#tablaClientes').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: 0 }
        ]
    });

    let selectedClientes = [];
    let plantillaSeleccionada = null;

    // ========================================
    // FILTROS RÁPIDOS
    // ========================================
    $('.filtro-btn').click(function() {
        $('.filtro-btn').removeClass('active');
        $(this).addClass('active');
        
        const filtro = $(this).data('filtro');
        
        // Filtrar por data-estado de la fila, no por texto del badge
        $.fn.dataTable.ext.search.pop(); // Limpiar filtros previos
        
        if (filtro !== 'todos') {
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    const row = tabla.row(dataIndex).node();
                    const estado = $(row).attr('data-estado');
                    return estado === filtro.slice(0, -1); // 'activos' -> 'activo'
                }
            );
        }
        
        tabla.draw();
    });

    // ========================================
    // SELECCIÓN DE CLIENTES
    // ========================================
    $(document).on('change', '.cliente-check', function() {
        const id = parseInt($(this).val());
        const nombre = $(this).data('nombre');
        const email = $(this).data('email');
        
        if ($(this).is(':checked')) {
            if (!selectedClientes.find(c => c.id === id)) {
                selectedClientes.push({ id, nombre, email });
            }
        } else {
            selectedClientes = selectedClientes.filter(c => c.id !== id);
        }
        
        updateSeleccion();
    });

    $('#checkAll').change(function() {
        const isChecked = $(this).is(':checked');
        // Solo selecciona los visibles en la página actual
        $('#tablaClientes tbody tr:visible .cliente-check').each(function() {
            $(this).prop('checked', isChecked).trigger('change');
        });
    });

    $('#btnSeleccionarTodos').click(function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Seleccionando...');
        
        // Selecciona TODOS los clientes (todas las páginas)
        let countSeleccionados = 0;
        tabla.rows().every(function() {
            const row = this.node();
            const checkbox = $(row).find('.cliente-check');
            if (checkbox.length && !checkbox.is(':checked')) {
                const id = parseInt(checkbox.val());
                const nombre = checkbox.data('nombre');
                const email = checkbox.data('email');
                if (!selectedClientes.find(c => c.id === id)) {
                    selectedClientes.push({ id, nombre, email });
                    countSeleccionados++;
                }
                checkbox.prop('checked', true);
            }
        });
        
        setTimeout(() => {
            updateSeleccion();
            $btn.prop('disabled', false).html('<i class="fas fa-check-double"></i> Todos los visibles');
            
            if (countSeleccionados > 0) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Seleccionados!',
                    text: `Se seleccionaron ${countSeleccionados} clientes adicionales de todas las páginas`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }, 100);
    });

    $('#btnLimpiarSeleccion').click(function() {
        selectedClientes = [];
        $('.cliente-check, #checkAll').prop('checked', false);
        updateSeleccion();
    });

    function updateSeleccion() {
        const count = selectedClientes.length;
        
        if (count > 0) {
            $('#seleccionInfo').removeClass('hidden');
            $('#seleccionCount').text(count);
            $('#btnCount').text(count);
        } else {
            $('#seleccionInfo').addClass('hidden');
            $('#btnCount').text('0');
        }
        
        $('#clienteIdsInput').val(JSON.stringify(selectedClientes.map(c => c.id)));
        
        updatePasos();
        validarFormulario();
    }

    // ========================================
    // SELECCIÓN DE PLANTILLA
    // ========================================
    $('.plantilla-card').click(function() {
        $('.plantilla-card').removeClass('selected');
        $(this).addClass('selected');
        
        plantillaSeleccionada = {
            id: $(this).data('id'),
            nombre: $(this).data('nombre'),
            asunto: $(this).data('asunto'),
            contenido: $(this).data('contenido')
        };
        
        $('#plantillaIdInput').val(plantillaSeleccionada.id);
        $('#asunto').val(plantillaSeleccionada.asunto);
        $('#mensaje').summernote('code', plantillaSeleccionada.contenido);
        
        updateCharCounters();
        updatePasos();
        validarFormulario();
    });

    // ========================================
    // VARIABLES RÁPIDAS
    // ========================================
    $('.variable-btn').click(function() {
        const variable = $(this).data('var');
        $('#mensaje').summernote('insertText', variable);
        $('#mensaje').summernote('focus');
    });

    // ========================================
    // ACTUALIZACIÓN EN TIEMPO REAL
    // ========================================
    $('#asunto').on('input', function() {
        updateCharCounters();
        validarFormulario();
    });

    function updateCharCounters() {
        const asuntoLength = $('#asunto').val().length;
        const mensajeLength = $('#mensaje').summernote('isEmpty') ? 0 : $('.note-editable').text().length;
        
        $('#asuntoCount').text(asuntoLength);
        $('#mensajeCount').text(mensajeLength);
        
        // Cambiar color según límite
        if (asuntoLength > 200) {
            $('#asuntoCount').parent().addClass('warning');
        } else {
            $('#asuntoCount').parent().removeClass('warning');
        }
        
        if (mensajeLength > 4500) {
            $('#mensajeCount').parent().addClass('warning');
        } else {
            $('#mensajeCount').parent().removeClass('warning');
        }
    }



    // ========================================
    // ACTUALIZAR PASOS
    // ========================================
    function updatePasos() {
        const clientesOk = selectedClientes.length > 0;
        const plantillaOk = plantillaSeleccionada !== null;
        const asuntoOk = $('#asunto').val().trim() !== '';
        const mensajeOk = $('#mensaje').val().trim() !== '';
        const mensajeCompleto = asuntoOk && mensajeOk;
        
        // Paso 1: Clientes
        $('#paso1')
            .toggleClass('completed', clientesOk)
            .toggleClass('active', !clientesOk);
        
        // Paso 2: Plantilla - Mostrar solo si hay clientes seleccionados
        $('#paso2')
            .toggleClass('completed', plantillaOk)
            .toggleClass('active', clientesOk && !plantillaOk);
        
        if (clientesOk) {
            $('#seccionPlantillas').slideDown(300);
        } else {
            $('#seccionPlantillas').slideUp(300);
            $('#seccionMensaje').slideUp(300);
            $('#seccionEnviar').slideUp(300);
        }
        
        // Paso 3: Mensaje - Mostrar solo si hay plantilla seleccionada
        const mensajeOkReal = !$('#mensaje').summernote('isEmpty') && $('.note-editable').text().trim().length >= 10;
        $('#paso3')
            .toggleClass('completed', asuntoOk && mensajeOkReal)
            .toggleClass('active', plantillaOk && !(asuntoOk && mensajeOkReal));
        
        if (plantillaOk) {
            $('#seccionMensaje').slideDown(300);
        } else {
            $('#seccionMensaje').slideUp(300);
            $('#seccionEnviar').slideUp(300);
        }
        
        // Paso 4: Enviar - Mostrar solo si el mensaje está completo
        $('#paso4')
            .toggleClass('completed', clientesOk && plantillaOk && mensajeCompleto)
            .toggleClass('active', mensajeCompleto);
        
        if (mensajeCompleto && clientesOk && plantillaOk) {
            $('#seccionEnviar').slideDown(300);
        } else {
            $('#seccionEnviar').slideUp(300);
        }
    }

    // ========================================
    // VALIDACIÓN DEL FORMULARIO
    // ========================================
    function validarFormulario() {
        const errores = [];
        
        // Validar clientes
        if (selectedClientes.length === 0) {
            errores.push('Selecciona al menos un cliente');
            $('#seleccionInfo').addClass('is-invalid');
        } else {
            $('#seleccionInfo').removeClass('is-invalid');
        }
        
        // Validar asunto
        const asunto = $('#asunto').val().trim();
        if (asunto === '') {
            errores.push('El asunto es obligatorio');
            $('#asunto').addClass('is-invalid').removeClass('is-valid');
            $('#asunto').siblings('.invalid-feedback').text('El asunto es obligatorio');
        } else if (asunto.length < 5) {
            errores.push('El asunto debe tener al menos 5 caracteres');
            $('#asunto').addClass('is-invalid').removeClass('is-valid');
            $('#asunto').siblings('.invalid-feedback').text('Debe tener al menos 5 caracteres');
        } else {
            $('#asunto').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Validar mensaje
        const mensajeEmpty = $('#mensaje').summernote('isEmpty');
        const mensajeText = $('.note-editable').text().trim();
        if (mensajeEmpty || mensajeText === '') {
            errores.push('El mensaje es obligatorio');
            $('.note-editor').addClass('is-invalid').removeClass('is-valid');
            $('#mensaje').siblings('.invalid-feedback').text('El mensaje es obligatorio');
        } else if (mensajeText.length < 10) {
            errores.push('El mensaje debe tener al menos 10 caracteres');
            $('.note-editor').addClass('is-invalid').removeClass('is-valid');
            $('#mensaje').siblings('.invalid-feedback').text('Debe tener al menos 10 caracteres');
        } else {
            $('.note-editor').removeClass('is-invalid').addClass('is-valid');
        }
        
        // Mostrar/ocultar alerta
        if (errores.length > 0) {
            $('#listaValidacion').html(errores.map(e => `<li>${e}</li>`).join(''));
            $('#alertValidacion').addClass('show');
            $('#btnEnviar').prop('disabled', true);
        } else {
            $('#alertValidacion').removeClass('show');
            $('#btnEnviar').prop('disabled', false);
        }
    }

    // ========================================
    // ENVÍO DEL FORMULARIO
    // ========================================
    $('#comunicadoForm').submit(function(e) {
        e.preventDefault();
        
        if (selectedClientes.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Selecciona al menos un cliente',
                confirmButtonColor: '#e94560'
            });
            return;
        }
        
        const mensajePreview = $('.note-editable').text().substring(0, 100);
        Swal.fire({
            title: '¿Enviar notificación?',
            html: `
                <div style="text-align: left; padding: 20px;">
                    <p><strong>📧 Destinatarios:</strong> ${selectedClientes.length} clientes</p>
                    <p><strong>📝 Asunto:</strong> ${$('#asunto').val().substring(0, 60)}${$('#asunto').val().length > 60 ? '...' : ''}</p>
                    <p><strong>💬 Mensaje:</strong> ${mensajePreview}${mensajePreview.length > 100 ? '...' : ''}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00bf8e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Sí, enviar ahora',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Enviando...',
                    html: 'Por favor espera mientras se envían las notificaciones',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar formulario
                this.submit();
            }
        });
    });

    // Inicializar
    updateCharCounters();
    validarFormulario();
});
</script>
@stop
