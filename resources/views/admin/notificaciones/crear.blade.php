@extends('adminlte::page')

@section('title', 'Enviar Comunicado - EstóicosGym')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    .content-wrapper {
        background: #f8f9fa !important;
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

    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 16px;
        margin-bottom: 25px;
    }

    .page-header h1 {
        margin: 0;
        font-weight: 700;
        font-size: 1.6rem;
    }

    .page-header h1 i {
        color: var(--accent);
        margin-right: 10px;
    }

    .main-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .main-card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 18px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .main-card-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .main-card-header h3 i {
        color: var(--accent);
        margin-right: 8px;
    }

    .main-card-body {
        padding: 25px;
    }

    /* Filtros rápidos */
    .filtros-rapidos {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .filtro-btn {
        padding: 10px 20px;
        border-radius: 25px;
        border: 2px solid var(--gray-200);
        background: white;
        color: var(--gray-600);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filtro-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .filtro-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }

    .filtro-btn .badge {
        margin-left: 8px;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.75rem;
    }

    .filtro-btn.active .badge {
        background: rgba(255,255,255,0.3);
    }

    /* Tabla de clientes */
    .tabla-clientes {
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        overflow: hidden;
    }

    .tabla-clientes thead th {
        background: var(--gray-100);
        border-bottom: 2px solid var(--gray-200);
        padding: 12px 15px;
        font-weight: 600;
        color: var(--gray-800);
    }

    .tabla-clientes tbody td {
        padding: 12px 15px;
        vertical-align: middle;
    }

    .cliente-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cliente-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .cliente-nombre {
        font-weight: 600;
        color: var(--gray-800);
    }

    .cliente-email {
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    .badge-estado {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-activo { background: rgba(0, 191, 142, 0.15); color: var(--success); }
    .badge-inactivo { background: rgba(108, 117, 125, 0.15); color: var(--gray-600); }
    .badge-vencido { background: rgba(233, 69, 96, 0.15); color: var(--accent); }

    /* Selección */
    .seleccion-info {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .seleccion-info.hidden {
        display: none;
    }

    .seleccion-count {
        font-size: 1.1rem;
        font-weight: 700;
    }

    /* Tipos de comunicado */
    .tipos-comunicado {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 10px;
        margin-bottom: 20px;
    }

    .tipo-card {
        background: white;
        border: 2px solid var(--gray-200);
        border-radius: 16px;
        padding: 18px 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .tipo-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gray-200);
        transition: all 0.3s ease;
    }

    .tipo-card[data-tipo="horario"]::before { background: var(--info); }
    .tipo-card[data-tipo="promocion"]::before { background: var(--success); }
    .tipo-card[data-tipo="feriado"]::before { background: var(--warning); }
    .tipo-card[data-tipo="volver"]::before { background: var(--accent); }
    .tipo-card[data-tipo="personalizado"]::before { background: var(--primary); }

    .tipo-card:hover {
        border-color: var(--accent);
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .tipo-card.selected {
        border-color: var(--accent);
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(233, 69, 96, 0.3);
    }

    .tipo-card.selected::before {
        background: rgba(255,255,255,0.5);
    }

    .tipo-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.4rem;
        transition: all 0.3s ease;
    }

    .tipo-card[data-tipo="horario"] .icon { 
        background: rgba(67, 97, 238, 0.15); 
        color: var(--info); 
    }
    .tipo-card[data-tipo="promocion"] .icon { 
        background: rgba(0, 191, 142, 0.15); 
        color: var(--success); 
    }
    .tipo-card[data-tipo="feriado"] .icon { 
        background: rgba(240, 165, 0, 0.15); 
        color: var(--warning); 
    }
    .tipo-card[data-tipo="volver"] .icon { 
        background: rgba(233, 69, 96, 0.15); 
        color: var(--accent); 
    }
    .tipo-card[data-tipo="personalizado"] .icon { 
        background: rgba(26, 26, 46, 0.1); 
        color: var(--primary); 
    }

    .tipo-card.selected .icon {
        background: rgba(255,255,255,0.25);
        color: white;
    }

    .tipo-card .nombre {
        font-weight: 700;
        font-size: 0.8rem;
        line-height: 1.2;
    }

    /* Editor de mensaje */
    .mensaje-editor {
        background: var(--gray-100);
        border-radius: 12px;
        padding: 20px;
    }

    .form-control {
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        padding: 12px 15px;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
    }

    .variables-rapidas {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .variable-btn {
        background: white;
        border: 1px solid var(--gray-200);
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .variable-btn:hover {
        background: var(--info);
        color: white;
        border-color: var(--info);
    }

    /* Vista previa */
    .preview-card {
        background: white;
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .preview-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 25px;
        text-align: center;
        position: relative;
    }

    .preview-header::after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        border-left: 20px solid transparent;
        border-right: 20px solid transparent;
        border-top: 20px solid var(--primary-light);
    }

    .preview-logo {
        max-width: 100px;
        height: auto;
        filter: drop-shadow(0 2px 10px rgba(0,0,0,0.3));
    }

    .preview-body {
        padding: 35px 25px 25px;
        background: linear-gradient(180deg, #f8f9fa 0%, white 30%);
    }

    .preview-body h5 {
        color: var(--primary);
        margin-bottom: 15px;
        font-size: 1.1rem;
        font-weight: 700;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--accent);
        display: inline-block;
    }

    #previewMensaje {
        font-size: 0.9rem;
        line-height: 1.7;
        color: #555;
        background: white;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid var(--gray-200);
    }

    /* Botones */
    .btn-enviar {
        background: linear-gradient(135deg, var(--success) 0%, #00a67d 100%);
        border: none;
        color: white;
        padding: 18px 50px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-enviar::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
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

    /* Pasos */
    .pasos-indicador {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 50px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .paso {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--gray-600);
        padding: 8px 15px;
        border-radius: 25px;
        transition: all 0.3s ease;
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
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
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
        box-shadow: 0 3px 10px rgba(0, 191, 142, 0.3);
    }

    .paso span:last-child {
        font-weight: 600;
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .pasos-indicador {
            flex-direction: column;
            border-radius: 16px;
        }
    }

    /* Responsive DataTable */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 8px 15px;
        border: 2px solid var(--gray-200);
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        padding: 5px 10px;
    }

    /* Checkbox personalizado */
    .custom-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* Loading */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.95);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .loading-overlay.show {
        display: flex;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid var(--gray-200);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Sin email */
    .sin-email {
        opacity: 0.5;
    }

    .sin-email .cliente-check {
        display: none;
    }
</style>
@stop

@section('content_header')
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-paper-plane"></i> Enviar Comunicado</h1>
                <small class="text-white-50">Envía emails a tus clientes de forma rápida y sencilla</small>
            </div>
            <a href="{{ route('admin.notificaciones.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <p class="mt-3" id="loadingText">Enviando correos...</p>
    </div>

    {{-- Indicador de pasos --}}
    <div class="pasos-indicador">
        <div class="paso active" id="paso1">
            <span class="paso-numero">1</span>
            <span>Seleccionar Clientes</span>
        </div>
        <div class="paso" id="paso2">
            <span class="paso-numero">2</span>
            <span>Tipo de Mensaje</span>
        </div>
        <div class="paso" id="paso3">
            <span class="paso-numero">3</span>
            <span>Enviar</span>
        </div>
    </div>

    <form action="{{ route('admin.notificaciones.enviar-masivo') }}" method="POST" id="comunicadoForm">
        @csrf
        <input type="hidden" name="cliente_ids" id="clienteIdsInput" value="">
        <input type="hidden" name="tipo_comunicado" id="tipoComunicadoInput" value="">

        <div class="row">
            {{-- Columna Izquierda: Selección de Clientes --}}
            <div class="col-lg-7 mb-4">
                <div class="main-card">
                    <div class="main-card-header">
                        <h3><i class="fas fa-users"></i> Seleccionar Clientes</h3>
                        <button type="button" class="btn btn-sm btn-light" id="btnSeleccionarTodos">
                            <i class="fas fa-check-double"></i> Seleccionar Visibles
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

            {{-- Columna Derecha: Tipo y Mensaje --}}
            <div class="col-lg-5 mb-4">
                {{-- Tipo de Comunicado --}}
                <div class="main-card mb-4">
                    <div class="main-card-header">
                        <h3><i class="fas fa-envelope-open-text"></i> Tipo de Mensaje</h3>
                    </div>
                    <div class="main-card-body">
                        <div class="tipos-comunicado">
                            <div class="tipo-card" data-tipo="horario" 
                                 data-asunto="📅 Cambio de Horario - Estoicos Gym Los Ángeles" 
                                 data-mensaje="Estimado/a {nombre},

Te informamos que hemos realizado cambios en nuestro horario de atención:

🕐 Nuevo Horario:
• Lunes a Viernes: 6:00 - 22:00
• Sábados: 8:00 - 14:00
• Domingos: Cerrado

Estos cambios entran en vigencia desde [FECHA].

¡Gracias por tu comprensión!

Saludos,
Equipo Estoicos Gym Los Ángeles 💪">
                                <div class="icon"><i class="fas fa-clock"></i></div>
                                <div class="nombre">Cambio de Horario</div>
                            </div>
                            
                            <div class="tipo-card" data-tipo="promocion" 
                                 data-asunto="🎉 ¡Promoción Especial! - Estoicos Gym Los Ángeles" 
                                 data-mensaje="Hola {nombre},

¡Tenemos una promoción especial para ti!

🔥 [DESCRIPCIÓN DE LA PROMOCIÓN]

📅 Válido hasta: [FECHA]

¡No te lo pierdas!

Saludos,
Equipo Estoicos Gym Los Ángeles 💪">
                                <div class="icon"><i class="fas fa-tags"></i></div>
                                <div class="nombre">Promoción</div>
                            </div>
                            
                            <div class="tipo-card" data-tipo="feriado" 
                                 data-asunto="📢 Horario Feriado - Estoicos Gym Los Ángeles" 
                                 data-mensaje="Estimado/a {nombre},

Te informamos que con motivo de [NOMBRE DEL FERIADO], nuestro horario será:

📅 Fecha: [FECHA]
🕐 Horario: [HORARIO ESPECIAL]

Retomamos nuestro horario normal el [FECHA].

¡Que disfrutes el feriado!

Saludos,
Equipo Estoicos Gym Los Ángeles 💪">
                                <div class="icon"><i class="fas fa-calendar-star"></i></div>
                                <div class="nombre">Feriado</div>
                            </div>
                            
                            <div class="tipo-card" data-tipo="volver" 
                                 data-asunto="💪 ¡Te extrañamos! - Estoicos Gym Los Ángeles" 
                                 data-mensaje="Hola {nombre},

¡Hace tiempo que no te vemos por el gimnasio! 😢

Sabemos que a veces la rutina se complica, pero queremos que sepas que tu lugar siempre está esperándote.

🎁 Vuelve esta semana y recibe [BENEFICIO ESPECIAL]

¡Te esperamos!

Saludos,
Equipo Estoicos Gym Los Ángeles 💪">
                                <div class="icon"><i class="fas fa-heart"></i></div>
                                <div class="nombre">Te Extrañamos</div>
                            </div>
                            
                            <div class="tipo-card" data-tipo="personalizado" 
                                 data-asunto="" 
                                 data-mensaje="Estimado/a {nombre},

[Escribe tu mensaje aquí]

Saludos,
Equipo Estoicos Gym Los Ángeles">
                                <div class="icon"><i class="fas fa-pen-fancy"></i></div>
                                <div class="nombre">Personalizado</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Editor de Mensaje --}}
                <div class="main-card">
                    <div class="main-card-header">
                        <h3><i class="fas fa-edit"></i> Componer Mensaje</h3>
                    </div>
                    <div class="main-card-body">
                        <div class="mensaje-editor">
                            <div class="form-group">
                                <label><i class="fas fa-heading"></i> Asunto</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="asunto" 
                                       name="asunto" 
                                       placeholder="Asunto del correo..."
                                       required>
                            </div>
                            
                            <div class="form-group mb-0">
                                <label><i class="fas fa-align-left"></i> Mensaje</label>
                                <textarea class="form-control" 
                                          id="mensaje" 
                                          name="mensaje" 
                                          rows="8"
                                          placeholder="Escribe tu mensaje..."
                                          required></textarea>
                                
                                <div class="variables-rapidas">
                                    <small class="text-muted mr-2">Insertar:</small>
                                    <span class="variable-btn" data-var="{nombre}">📛 Nombre</span>
                                </div>
                            </div>
                        </div>

                        {{-- Vista Previa --}}
                        <div class="mt-4">
                            <label class="mb-2"><i class="fas fa-eye"></i> Vista Previa</label>
                            <div class="preview-card">
                                <div class="preview-header">
                                    <img src="{{ asset('images/estoicos_gym_logo.png') }}" 
                                         alt="Estoicos Gym Los Ángeles" 
                                         class="preview-logo">
                                </div>
                                <div class="preview-body">
                                    <h5 id="previewAsunto">Asunto del correo...</h5>
                                    <div id="previewMensaje" style="white-space: pre-line; color: #555; font-size: 0.9rem;">
                                        El mensaje aparecerá aquí...
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botón Enviar --}}
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-enviar btn-lg" id="btnEnviar" disabled>
                                <i class="fas fa-paper-plane"></i> Enviar a <span id="btnCount">0</span> clientes
                            </button>
                            <p class="text-muted mt-2 mb-0">
                                <small>Los correos se enviarán inmediatamente</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    const tabla = $('#tablaClientes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: 0 }
        ]
    });

    let selectedClientes = [];

    // Filtros rápidos
    $('.filtro-btn').click(function() {
        $('.filtro-btn').removeClass('active');
        $(this).addClass('active');
        
        const filtro = $(this).data('filtro');
        
        if (filtro === 'todos') {
            tabla.column(3).search('').draw();
        } else if (filtro === 'activos') {
            tabla.column(3).search('Activo').draw();
        } else if (filtro === 'vencidos') {
            tabla.column(3).search('Vencido').draw();
        } else if (filtro === 'inactivos') {
            tabla.column(3).search('Inactivo').draw();
        }
    });

    // Checkbox individual
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

    // Check all (solo visibles)
    $('#checkAll').change(function() {
        const isChecked = $(this).is(':checked');
        
        // Solo los visibles en la página actual
        $('#tablaClientes tbody tr:visible .cliente-check').each(function() {
            $(this).prop('checked', isChecked).trigger('change');
        });
    });

    // Seleccionar todos visibles (botón)
    $('#btnSeleccionarTodos').click(function() {
        $('#tablaClientes tbody tr:visible .cliente-check').each(function() {
            if (!$(this).is(':checked')) {
                $(this).prop('checked', true).trigger('change');
            }
        });
    });

    // Limpiar selección
    $('#btnLimpiarSeleccion').click(function() {
        selectedClientes = [];
        $('.cliente-check, #checkAll').prop('checked', false);
        updateSeleccion();
    });

    // Actualizar UI de selección
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
        
        // Actualizar input hidden
        $('#clienteIdsInput').val(JSON.stringify(selectedClientes.map(c => c.id)));
        
        updatePasos();
        checkFormValido();
    }

    // Tipos de comunicado
    $('.tipo-card').click(function() {
        $('.tipo-card').removeClass('selected');
        $(this).addClass('selected');
        
        const tipo = $(this).data('tipo');
        const asunto = $(this).data('asunto');
        const mensaje = $(this).data('mensaje');
        
        $('#tipoComunicadoInput').val(tipo);
        $('#asunto').val(asunto);
        $('#mensaje').val(mensaje);
        
        updatePreview();
        updatePasos();
        checkFormValido();
    });

    // Variables rápidas
    $('.variable-btn').click(function() {
        const variable = $(this).data('var');
        const textarea = document.getElementById('mensaje');
        const cursorPos = textarea.selectionStart;
        const textBefore = textarea.value.substring(0, cursorPos);
        const textAfter = textarea.value.substring(cursorPos);
        textarea.value = textBefore + variable + textAfter;
        textarea.focus();
        textarea.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);
        updatePreview();
    });

    // Actualizar preview en tiempo real
    $('#asunto, #mensaje').on('input', function() {
        updatePreview();
        checkFormValido();
    });

    function updatePreview() {
        let asunto = $('#asunto').val() || 'Asunto del correo...';
        let mensaje = $('#mensaje').val() || 'El mensaje aparecerá aquí...';
        
        // Reemplazar variables con ejemplo
        const nombreEjemplo = selectedClientes.length > 0 ? selectedClientes[0].nombre : 'Juan Pérez';
        
        mensaje = mensaje.replace(/\{nombre\}/g, nombreEjemplo);
        asunto = asunto.replace(/\{nombre\}/g, nombreEjemplo);
        
        $('#previewAsunto').text(asunto);
        $('#previewMensaje').text(mensaje);
    }

    // Actualizar indicador de pasos
    function updatePasos() {
        const clientesOk = selectedClientes.length > 0;
        const tipoOk = $('.tipo-card.selected').length > 0;
        const mensajeOk = $('#asunto').val() && $('#mensaje').val();
        
        // Paso 1
        $('#paso1').toggleClass('completed', clientesOk).toggleClass('active', !clientesOk);
        
        // Paso 2
        $('#paso2').toggleClass('completed', tipoOk).toggleClass('active', clientesOk && !tipoOk);
        
        // Paso 3
        $('#paso3').toggleClass('completed', clientesOk && tipoOk && mensajeOk)
                   .toggleClass('active', tipoOk && !mensajeOk);
    }

    // Verificar si el formulario es válido
    function checkFormValido() {
        const clientesOk = selectedClientes.length > 0;
        const asuntoOk = $('#asunto').val().trim() !== '';
        const mensajeOk = $('#mensaje').val().trim() !== '';
        
        $('#btnEnviar').prop('disabled', !(clientesOk && asuntoOk && mensajeOk));
    }

    // Envío del formulario
    $('#comunicadoForm').submit(function(e) {
        e.preventDefault();
        
        if (selectedClientes.length === 0) {
            Swal.fire('Error', 'Selecciona al menos un cliente', 'error');
            return;
        }
        
        Swal.fire({
            title: '¿Enviar comunicado?',
            html: `
                <div style="text-align: left;">
                    <p><strong>📧 Destinatarios:</strong> ${selectedClientes.length} clientes</p>
                    <p><strong>📝 Asunto:</strong> ${$('#asunto').val().substring(0, 50)}...</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00bf8e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Sí, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').addClass('show');
                this.submit();
            }
        });
    });
});
</script>
@stop
