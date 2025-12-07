@extends('adminlte::page')

@section('title', 'Nueva Notificación - Estoicos Gym')

@section('content_header')
    <div class="page-header">
        <h1><i class="fas fa-paper-plane"></i> Nueva Notificación</h1>
        <p>Envía comunicados personalizados a tus clientes</p>
    </div>
@stop

@section('content')
    {{-- Indicador de pasos --}}
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

    <form action="{{ route('admin.notificaciones.enviar-masivo') }}" method="POST" id="comunicadoForm">
        @csrf
        <input type="hidden" name="cliente_ids" id="clienteIdsInput" value="">
        <input type="hidden" name="plantilla_id" id="plantillaIdInput" value="">

        {{-- PASO 1: DESTINATARIOS --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> 1. Seleccionar Destinatarios</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" id="btnSeleccionarTodos">
                        <i class="fas fa-check-double"></i> Seleccionar todos
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Filtros rápidos --}}
                <div class="filtros-rapidos mb-3">
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
                <div class="alert alert-info hidden" id="seleccionInfo">
                    <i class="fas fa-check-circle"></i>
                    <strong><span id="seleccionCount">0</span> clientes seleccionados</strong>
                    <button type="button" class="btn btn-sm btn-light float-right" id="btnLimpiarSeleccion">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>

                {{-- Tabla de clientes --}}
                <table class="table table-hover" id="tablaClientes">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="checkAll"></th>
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
                                $badgeClass = 'success';
                            } elseif ($ultimaInscripcion && $ultimaInscripcion->id_estado == 102) {
                                $estado = 'Vencido';
                                $estadoClass = 'vencido';
                                $badgeClass = 'danger';
                            } else {
                                $estado = 'Inactivo';
                                $estadoClass = 'inactivo';
                                $badgeClass = 'secondary';
                            }
                            
                            $tieneEmail = !empty($cliente->email);
                        @endphp
                        <tr class="{{ !$tieneEmail ? 'table-secondary' : '' }}" data-estado="{{ $estadoClass }}">
                            <td>
                                @if($tieneEmail)
                                <input type="checkbox" 
                                       class="cliente-check" 
                                       value="{{ $cliente->id }}"
                                       data-nombre="{{ $cliente->nombre_completo }}"
                                       data-email="{{ $cliente->email }}">
                                @else
                                <i class="fas fa-ban text-muted" title="Sin email"></i>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $cliente->nombre_completo }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        @if($tieneEmail)
                                            <i class="fas fa-envelope"></i> {{ $cliente->email }}
                                        @else
                                            <i class="fas fa-exclamation-triangle text-danger"></i> Sin email
                                        @endif
                                    </small>
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
                                <span class="badge badge-{{ $badgeClass }}">
                                    {{ $estado }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PASO 2: PLANTILLAS --}}
        <div class="card mb-4" id="seccionPlantillas" style="display:none;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> 2. Seleccionar Plantilla</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($plantillas as $plantilla)
                    <div class="col-md-3 mb-3">
                        <div class="card plantilla-card" 
                             data-id="{{ $plantilla->id }}"
                             data-nombre="{{ $plantilla->nombre }}"
                             data-asunto="{{ $plantilla->asunto_email }}"
                             data-contenido="{{ $plantilla->plantilla_email }}">
                            <div class="card-body text-center">
                                @switch($plantilla->codigo)
                                    @case('membresia_por_vencer')
                                        <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                                        @break
                                    @case('membresia_vencida')
                                        <i class="fas fa-calendar-times fa-3x text-danger mb-2"></i>
                                        @break
                                    @case('bienvenida')
                                        <i class="fas fa-hand-sparkles fa-3x text-success mb-2"></i>
                                        @break
                                    @case('pago_pendiente')
                                        <i class="fas fa-dollar-sign fa-3x text-info mb-2"></i>
                                        @break
                                    @default
                                        <i class="fas fa-envelope fa-3x text-primary mb-2"></i>
                                @endswitch
                                <h5>{{ $plantilla->nombre }}</h5>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    {{-- Plantilla personalizada --}}
                    <div class="col-md-3 mb-3">
                        <div class="card plantilla-card" 
                             data-id="custom"
                             data-nombre="Personalizado"
                             data-asunto=""
                             data-contenido="">
                            <div class="card-body text-center">
                                <i class="fas fa-pen-fancy fa-3x text-secondary mb-2"></i>
                                <h5>Personalizado</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PASO 3: COMPONER MENSAJE --}}
        <div class="card mb-4" id="seccionMensaje" style="display:none;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> 3. Componer Mensaje</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Asunto del Correo *</label>
                    <input type="text" 
                           class="form-control" 
                           id="asunto" 
                           name="asunto" 
                           placeholder="Ej: ¡Promoción especial para ti!"
                           maxlength="255"
                           required>
                    <small class="form-text text-muted">
                        <span id="asuntoCount">0</span>/255 caracteres
                    </small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Mensaje *</label>
                    <textarea class="form-control" 
                              id="mensaje" 
                              name="mensaje" 
                              placeholder="Escribe tu mensaje aquí..."
                              required></textarea>
                    <small class="form-text text-muted">
                        <span id="mensajeCount">0</span>/5000 caracteres
                    </small>
                    
                    <div class="alert alert-light mt-2">
                        <small><i class="fas fa-magic"></i> <strong>Variables disponibles:</strong></small>
                        <div class="mt-2">
                            <span class="badge badge-info mr-1 variable-btn" data-var="{nombre}">📛 {nombre}</span>
                            <span class="badge badge-info mr-1 variable-btn" data-var="{email}">📧 {email}</span>
                            <span class="badge badge-info variable-btn" data-var="{membresia}">🏋️ {membresia}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PASO 4: ENVIAR --}}
        <div class="text-center mb-4" id="seccionEnviar" style="display:none;">
            <button type="submit" class="btn btn-success btn-lg" id="btnEnviar" disabled>
                <i class="fas fa-paper-plane"></i> Enviar a <span id="btnCount">0</span> clientes
            </button>
            <p class="text-muted mt-2">
                <small><i class="fas fa-info-circle"></i> Los correos se enviarán inmediatamente</small>
            </p>
        </div>
    </form>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<style>
    :root {
        --primary: #2c3e50;
        --primary-dark: #1a252f;
        --accent: #e74c3c;
        --accent-light: #ec7063;
        --success: #27ae60;
        --success-light: #2ecc71;
        --warning: #f39c12;
        --info: #3498db;
        --dark: #34495e;
    }

    body {
        background: #ecf0f1;
    }

    /* Header elegante */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .page-header h1 {
        margin: 0;
        font-weight: 800;
        font-size: 2rem;
        color: white;
        position: relative;
        z-index: 1;
        letter-spacing: -0.5px;
    }

    .page-header h1 i {
        color: var(--accent);
        margin-right: 15px;
        text-shadow: 0 2px 10px rgba(231, 76, 60, 0.5);
    }

    .page-header p {
        margin: 12px 0 0;
        color: rgba(255,255,255,0.85);
        font-size: 1rem;
        position: relative;
        z-index: 1;
        font-weight: 300;
    }

    /* Indicador de pasos elegante */
    .pasos-indicador {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 30px;
        padding: 25px 30px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        position: relative;
    }

    .pasos-indicador::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 60px;
        right: 60px;
        height: 3px;
        background: linear-gradient(to right, #e9ecef 0%, #e9ecef 100%);
        transform: translateY(-50%);
        z-index: 0;
    }

    .paso {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #95a5a6;
        padding: 10px 20px;
        border-radius: 15px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1;
        background: white;
    }

    .paso.active {
        color: var(--accent);
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.1) 0%, rgba(236, 112, 99, 0.05) 100%);
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.2);
    }

    .paso.completed {
        color: var(--success);
        background: linear-gradient(135deg, rgba(39, 174, 96, 0.1) 0%, rgba(46, 204, 113, 0.05) 100%);
    }

    .paso-numero {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ecf0f1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        transition: all 0.4s ease;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }

    .paso.active .paso-numero {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        color: white;
        box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
        transform: scale(1.1);
    }

    .paso.completed .paso-numero {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-light) 100%);
        color: white;
        box-shadow: 0 3px 15px rgba(39, 174, 96, 0.3);
    }

    .paso-texto {
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
    }

    /* Filtros rápidos elegantes */
    .filtros-rapidos {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filtro-btn {
        padding: 10px 20px;
        border-radius: 25px;
        border: 2px solid #ecf0f1;
        background: white;
        color: #7f8c8d;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.875rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }

    .filtro-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(231, 76, 60, 0.1);
        transform: translate(-50%, -50%);
        transition: width 0.5s, height 0.5s;
    }

    .filtro-btn:hover::before {
        width: 300px;
        height: 300px;
    }

    .filtro-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(231, 76, 60, 0.2);
    }

    .filtro-btn.active {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        border-color: var(--accent);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 5px 25px rgba(231, 76, 60, 0.35);
    }

    .filtro-btn i,
    .filtro-btn .badge {
        position: relative;
        z-index: 1;
    }

    .filtro-btn .badge {
        margin-left: 8px;
        padding: 3px 8px;
        font-weight: 700;
    }

    .filtro-btn.active .badge {
        background: rgba(255,255,255,0.25);
        color: white;
    }

    /* Plantillas elegantes */
    .plantilla-card {
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #ecf0f1;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        background: white;
    }

    .plantilla-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(to right, var(--accent), var(--accent-light));
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    
    .plantilla-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        border-color: var(--accent);
    }

    .plantilla-card:hover::before {
        transform: scaleX(1);
    }
    
    .plantilla-card.selected {
        border: 3px solid var(--accent);
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.05) 0%, rgba(236, 112, 99, 0.05) 100%);
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 50px rgba(231, 76, 60, 0.3);
    }

    .plantilla-card.selected::before {
        transform: scaleX(1);
    }

    .plantilla-card .card-body {
        padding: 30px 20px;
    }

    .plantilla-card i {
        transition: all 0.3s ease;
    }

    .plantilla-card:hover i {
        transform: scale(1.1) rotate(5deg);
    }

    .plantilla-card h5 {
        font-weight: 700;
        margin-top: 15px;
        font-size: 1rem;
        color: var(--dark);
    }

    /* Summernote elegante */
    .note-editor.note-frame {
        border: 2px solid #ecf0f1;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .note-editor.note-frame.focused {
        border-color: var(--accent);
        box-shadow: 0 5px 30px rgba(231, 76, 60, 0.15);
        transform: translateY(-2px);
    }

    .note-toolbar {
        background: linear-gradient(135deg, #f8f9fa 0%, #ecf0f1 100%) !important;
        border-bottom: 2px solid #ecf0f1 !important;
        padding: 15px !important;
    }

    .note-btn-group .note-btn {
        border-radius: 8px !important;
        margin: 2px !important;
        transition: all 0.2s ease !important;
    }

    .note-btn-group .note-btn:hover {
        background: var(--accent) !important;
        color: white !important;
        transform: translateY(-2px) !important;
    }

    .note-editable {
        min-height: 300px;
        padding: 20px;
        font-size: 0.95rem;
        line-height: 1.8;
    }

    .note-editable:focus {
        background: #fefefe;
    }

    /* Variables elegantes */
    .variable-btn {
        cursor: pointer;
        font-size: 0.875rem;
        padding: 8px 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 20px;
        font-weight: 600;
        border: 2px solid transparent;
    }

    .variable-btn:hover {
        background: var(--accent) !important;
        color: white !important;
        transform: translateY(-3px) scale(1.1);
        box-shadow: 0 5px 20px rgba(231, 76, 60, 0.3);
        border-color: var(--accent) !important;
    }

    /* DataTables customización */
    .dataTables_length select {
        min-width: 80px;
        padding: 8px 35px 8px 12px;
        border: 2px solid #ecf0f1;
        border-radius: 10px;
        font-size: 0.95rem;
        margin: 0 10px;
        font-weight: 600;
        color: var(--dark);
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .dataTables_length select:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
    }

    .dataTables_length label,
    .dataTables_filter label {
        font-weight: 500;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .dataTables_filter input {
        padding: 8px 12px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 0.9rem;
        margin-left: 8px;
        min-width: 250px;
    }

    .dataTables_filter input:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
    }

    /* Cards AdminLTE mejoradas */
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        margin-bottom: 30px;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .card:hover {
        box-shadow: 0 8px 35px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ecf0f1 100%);
        border-bottom: 3px solid var(--accent);
        padding: 20px 25px;
        border-radius: 20px 20px 0 0 !important;
    }

    .card-title {
        font-weight: 800;
        font-size: 1.2rem;
        color: var(--dark);
        margin: 0;
        letter-spacing: -0.3px;
    }

    .card-body {
        padding: 30px;
    }

    /* Alerts elegantes */
    .alert-info {
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(93, 173, 226, 0.05) 100%);
        border-left: 4px solid var(--info);
        border-radius: 15px;
        padding: 15px 20px;
        box-shadow: 0 3px 15px rgba(52, 152, 219, 0.1);
    }

    /* Botones principales elegantes */
    .btn-success {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-light) 100%);
        border: none;
        box-shadow: 0 8px 25px rgba(39, 174, 96, 0.35);
        border-radius: 30px;
        padding: 15px 40px;
        font-weight: 800;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-success::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn-success:hover::before {
        width: 400px;
        height: 400px;
    }

    .btn-success:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 15px 40px rgba(39, 174, 96, 0.5);
    }

    .btn-success i {
        margin-right: 10px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--info) 0%, #5dade2 100%);
        border: none;
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        border-radius: 20px;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
    }

    /* Tabla elegante */
    .table {
        border-radius: 15px;
        overflow: hidden;
    }

    .table thead th {
        background: linear-gradient(135deg, var(--dark) 0%, var(--primary) 100%);
        color: white;
        font-weight: 700;
        padding: 15px;
        border: none;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.03) 0%, rgba(236, 112, 99, 0.03) 100%);
        transform: scale(1.01);
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-color: #ecf0f1;
    }

    .hidden {
        display: none;
    }

    /* Animaciones suaves */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.5s ease-out;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-es-ES.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    const tabla = $('#tablaClientes').DataTable({
        language: {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "searchPlaceholder": "Ej: Juan Pérez, email...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: 0 }
        ]
    });
    
    // Agregar placeholder manualmente al input de búsqueda
    $('.dataTables_filter input').attr('placeholder', 'Ej: Juan Pérez, email...');
    
    // Inicializar Summernote
    $('#mensaje').summernote({
        height: 300,
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
        placeholder: 'Escribe tu mensaje aquí... Puedes usar formato HTML',
        callbacks: {
            onChange: function(contents, $editable) {
                const textLength = $editable.text().length;
                $('#mensajeCount').text(textLength);
                validarFormulario();
            }
        }
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
        
        $.fn.dataTable.ext.search.pop();
        
        if (filtro !== 'todos') {
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    const row = tabla.row(dataIndex).node();
                    const estado = $(row).attr('data-estado');
                    return estado === filtro.slice(0, -1);
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
        
        if (isChecked) {
            // Seleccionar TODOS los registros (todas las páginas)
            tabla.rows({ search: 'applied' }).every(function() {
                const row = this.node();
                const checkbox = $(row).find('.cliente-check');
                
                if (checkbox.length) {
                    const id = parseInt(checkbox.val());
                    const nombre = checkbox.data('nombre');
                    const email = checkbox.data('email');
                    
                    if (!selectedClientes.find(c => c.id === id)) {
                        selectedClientes.push({ id, nombre, email });
                    }
                    checkbox.prop('checked', true);
                }
            });
        } else {
            // Deseleccionar todos
            selectedClientes = [];
            tabla.rows().every(function() {
                const row = this.node();
                const checkbox = $(row).find('.cliente-check');
                if (checkbox.length) {
                    checkbox.prop('checked', false);
                }
            });
        }
        
        updateSeleccion();
    });

    $('#btnSeleccionarTodos').click(function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Seleccionando...');
        
        let countNuevos = 0;
        
        // Recorrer TODOS los registros, incluso los de otras páginas
        tabla.rows({ search: 'applied' }).every(function() {
            const row = this.node();
            const checkbox = $(row).find('.cliente-check');
            
            if (checkbox.length) {
                const id = parseInt(checkbox.val());
                const nombre = checkbox.data('nombre');
                const email = checkbox.data('email');
                
                // Solo agregar si no está ya seleccionado
                if (!selectedClientes.find(c => c.id === id)) {
                    selectedClientes.push({ id, nombre, email });
                    countNuevos++;
                }
                
                // Marcar el checkbox
                checkbox.prop('checked', true);
            }
        });
        
        setTimeout(() => {
            updateSeleccion();
            $btn.prop('disabled', false).html('<i class="fas fa-check-double"></i> Seleccionar todos los registros');
            $('#checkAll').prop('checked', true);
            
            Swal.fire({
                icon: 'success',
                title: '¡Seleccionados!',
                html: `<p>Total: <strong>${selectedClientes.length} clientes</strong></p>${countNuevos > 0 ? `<p><small>Agregados: ${countNuevos} nuevos</small></p>` : ''}`,
                timer: 2500,
                showConfirmButton: false
            });
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
    // CONTADORES
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
    }

    // ========================================
    // ACTUALIZAR PASOS
    // ========================================
    function updatePasos() {
        const clientesOk = selectedClientes.length > 0;
        const plantillaOk = plantillaSeleccionada !== null;
        const asuntoOk = $('#asunto').val().trim() !== '';
        const mensajeOk = !$('#mensaje').summernote('isEmpty');
        
        $('#paso1').toggleClass('completed', clientesOk).toggleClass('active', !clientesOk);
        $('#paso2').toggleClass('completed', plantillaOk).toggleClass('active', clientesOk && !plantillaOk);
        $('#paso3').toggleClass('completed', asuntoOk && mensajeOk).toggleClass('active', plantillaOk && !(asuntoOk && mensajeOk));
        $('#paso4').toggleClass('completed', clientesOk && plantillaOk && asuntoOk && mensajeOk).toggleClass('active', asuntoOk && mensajeOk);
        
        if (clientesOk) {
            $('#seccionPlantillas').slideDown(300);
        } else {
            $('#seccionPlantillas').slideUp(300);
            $('#seccionMensaje').slideUp(300);
            $('#seccionEnviar').slideUp(300);
        }
        
        if (plantillaOk) {
            $('#seccionMensaje').slideDown(300);
        } else {
            $('#seccionMensaje').slideUp(300);
            $('#seccionEnviar').slideUp(300);
        }
        
        if (asuntoOk && mensajeOk && clientesOk && plantillaOk) {
            $('#seccionEnviar').slideDown(300);
        } else {
            $('#seccionEnviar').slideUp(300);
        }
    }

    // ========================================
    // VALIDACIÓN
    // ========================================
    function validarFormulario() {
        const clientesOk = selectedClientes.length > 0;
        const asuntoOk = $('#asunto').val().trim().length >= 5;
        const mensajeOk = !$('#mensaje').summernote('isEmpty') && $('.note-editable').text().trim().length >= 10;
        
        $('#btnEnviar').prop('disabled', !(clientesOk && asuntoOk && mensajeOk));
    }

    // ========================================
    // ENVÍO
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
        
        Swal.fire({
            title: '¿Enviar notificación?',
            html: `
                <div style="text-align: left; padding: 20px;">
                    <p><strong>📧 Destinatarios:</strong> ${selectedClientes.length} clientes</p>
                    <p><strong>📝 Asunto:</strong> ${$('#asunto').val().substring(0, 60)}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00bf8e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Sí, enviar ahora',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enviando...',
                    html: 'Por favor espera mientras se envían las notificaciones',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
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
