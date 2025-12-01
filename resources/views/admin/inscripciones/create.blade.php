@extends('adminlte::page')

@section('title', 'Nueva Inscripción - EstóicosGym')

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --accent-light: #ff6b6b;
        --success: #00bf8e;
        --success-dark: #00a67d;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
    }

    /* ===== WIZARD STEPS ===== */
    .step-indicator { display: none; }
    .step-indicator.active { display: block; animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    
    .steps-nav { 
        display: flex; 
        gap: 1rem; 
        margin-bottom: 2rem; 
        flex-wrap: wrap;
        padding: 1.25rem;
        background: var(--gray-100);
        border-radius: 16px;
    }
    
    .step-btn {
        flex: 1;
        min-width: 150px;
        padding: 1rem;
        text-align: center;
        border-radius: 12px;
        background: white;
        border: 2px solid var(--gray-200);
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        color: var(--gray-600);
    }
    
    .step-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: var(--accent);
    }
    
    .step-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .step-btn.completed {
        background: var(--success);
        color: white;
        border-color: var(--success);
    }

    .step-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ===== FORM SECTIONS ===== */
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
        margin: 1.5rem 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid var(--accent);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-title i {
        color: var(--accent);
    }

    .precio-box {
        background: white;
        border: 2px solid var(--primary);
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 1rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    }

    .precio-box h5 {
        color: var(--primary);
    }

    .precio-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed var(--gray-200);
    }

    .precio-row:last-child {
        border-bottom: none;
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 2px solid var(--primary);
    }

    .precio-label {
        color: var(--gray-600);
        font-weight: 500;
    }

    .precio-valor {
        font-weight: 700;
        color: var(--gray-800);
    }

    .precio-total {
        font-size: 1.5rem;
        color: var(--success);
    }

    /* ===== BUTTONS ===== */
    .buttons-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
        padding: 1.5rem;
        background: var(--gray-100);
        border-radius: 16px;
    }

    .buttons-group {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn {
        transition: all 0.3s ease;
        font-weight: 600;
        border-radius: 10px;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-info {
        background: var(--info);
        border: none;
        color: white;
    }

    .btn-info:hover {
        background: #3451d4;
        color: white;
    }

    .btn-warning {
        background: var(--warning);
        border: none;
        color: white;
    }

    .btn-warning:hover {
        background: #d99200;
        color: white;
    }

    .btn-success {
        background: var(--success);
        border: none;
        color: white;
    }

    .btn-success:hover {
        background: var(--success-dark);
        color: white;
    }

    .btn-primary {
        background: var(--primary);
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-light);
        color: white;
    }

    /* ===== CARD ===== */
    .card-primary .card-header {
        background: var(--primary);
    }

    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
    }

    .page-header h1 {
        color: white;
        margin: 0;
        font-weight: 700;
    }

    .page-header h1 i {
        color: var(--accent);
    }

    .btn-back {
        background: transparent;
        color: white;
        border: 2px solid rgba(255,255,255,0.5);
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
    }

    .btn-back:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        border-color: white;
    }

    /* ===== FORM CONTROLS ===== */
    .form-control {
        border: 2px solid var(--gray-200);
        border-radius: 10px;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
    }

    .form-control.is-invalid {
        border-color: var(--accent) !important;
        background-color: rgba(233, 69, 96, 0.05) !important;
    }

    /* ===== CLIENTE CARD ===== */
    .cliente-card {
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .cliente-card:hover {
        border-color: var(--accent);
        background: rgba(233, 69, 96, 0.03);
        transform: translateX(5px);
    }

    .cliente-card.selected {
        border-color: var(--success);
        background: rgba(0, 191, 142, 0.08);
    }

    .cliente-card .cliente-nombre {
        font-weight: 700;
        color: var(--gray-800);
        font-size: 1.1rem;
    }

    .cliente-card .cliente-rut {
        color: var(--accent);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .cliente-card .cliente-estado {
        font-size: 0.85rem;
    }

    .cliente-card .estado-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .estado-sin-membresia {
        background: rgba(240, 165, 0, 0.15);
        color: var(--warning);
    }

    .estado-vencida {
        background: rgba(233, 69, 96, 0.15);
        color: var(--accent);
    }

    /* ===== SEARCH BOX ===== */
    .search-clientes {
        margin-bottom: 1rem;
    }

    .search-clientes input {
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .search-clientes input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
    }

    .clientes-list {
        max-height: 400px;
        overflow-y: auto;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 1rem;
        background: var(--gray-100);
    }

    /* ===== TIPO PAGO ===== */
    .tipo-pago-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .tipo-pago-card {
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .tipo-pago-card:hover {
        border-color: var(--accent);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }

    .tipo-pago-card.selected {
        border-color: var(--success);
        background: rgba(0, 191, 142, 0.08);
    }

    .tipo-pago-card i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: var(--accent);
    }

    .tipo-pago-card .tipo-nombre {
        font-weight: 700;
        color: var(--gray-800);
    }

    .tipo-pago-card .tipo-desc {
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    /* ===== RESUMEN INSCRIPCIÓN ===== */
    .resumen-inscripcion .info-card {
        padding: 1.5rem;
        border-radius: 16px;
        text-align: center;
        height: 100%;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .resumen-inscripcion .info-card.bg-primary {
        background: var(--primary) !important;
    }

    .resumen-inscripcion .info-card.bg-info {
        background: var(--info) !important;
    }

    .resumen-inscripcion .info-card.bg-success {
        background: var(--success) !important;
    }

    .resumen-inscripcion .info-label {
        font-size: 0.85rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .resumen-inscripcion .info-value {
        font-size: 1.1rem;
        font-weight: 700;
        margin-top: 0.25rem;
    }

    /* ===== PAGO MIXTO TABLA ===== */
    #tabla-pagos-mixto {
        border-radius: 12px;
        overflow: hidden;
    }

    #tabla-pagos-mixto thead {
        background: var(--primary);
    }

    #tabla-pagos-mixto thead th {
        color: white;
        border: none;
    }

    #tabla-pagos-mixto .monto-mixto,
    #tabla-pagos-mixto .metodo-mixto {
        min-width: 120px;
    }

    .resumen-mixto {
        background: var(--gray-100);
        border-radius: 12px;
        padding: 1rem;
    }

    #mixto-diferencia-box {
        background: rgba(240, 165, 0, 0.15);
    }

    #mixto-diferencia-box.ok {
        background: rgba(0, 191, 142, 0.15);
    }

    #mixto-diferencia-box.ok #mixto-diferencia {
        color: var(--success) !important;
    }

    #mixto-diferencia-box.error {
        background: rgba(233, 69, 96, 0.15);
    }

    #mixto-diferencia-box.error #mixto-diferencia {
        color: var(--accent) !important;
    }

    /* ===== ALERT CUSTOM ===== */
    .alert-info {
        background: rgba(67, 97, 238, 0.1);
        border: none;
        color: var(--info);
        border-radius: 12px;
    }

    .alert-success {
        background: rgba(0, 191, 142, 0.1);
        border: none;
        color: var(--success-dark);
        border-radius: 12px;
    }

    .alert-warning {
        background: rgba(240, 165, 0, 0.1);
        border: none;
        color: #c78800;
        border-radius: 12px;
    }

    .alert-danger {
        background: rgba(233, 69, 96, 0.1);
        border: none;
        color: var(--accent);
        border-radius: 12px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .buttons-container { flex-direction: column; }
        .buttons-group { width: 100%; }
        .buttons-group .btn { flex: 1; justify-content: center; }
        .resumen-inscripcion .col-md-4 { margin-bottom: 1rem; }
    }

    /* ===== SWEETALERT ESTOICOS THEME ===== */
    .swal-estoicos {
        border-radius: 16px !important;
        border: 2px solid var(--accent) !important;
    }
    
    .swal-estoicos .swal2-title {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    
    .swal-estoicos .swal2-html-container {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    
    .swal-estoicos .swal2-icon {
        border-color: var(--accent) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-success {
        border-color: var(--success) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-success [class^="swal2-success-line"] {
        background-color: var(--success) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-warning {
        border-color: var(--warning) !important;
        color: var(--warning) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-error {
        border-color: var(--accent) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-error [class^="swal2-x-mark-line"] {
        background-color: var(--accent) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-question {
        border-color: var(--info) !important;
        color: var(--info) !important;
    }
</style>
@endsection

@section('content_header')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-sm-8">
            <h1><i class="fas fa-clipboard-list"></i> Nueva Inscripción</h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>
@stop

@section('content')
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5><i class="fas fa-exclamation-triangle"></i> Errores en el Formulario</h5>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tasks"></i> Registro de Inscripción - 3 Pasos</h3>
    </div>

    <div class="card-body">
        <div class="steps-nav">
            <button type="button" class="step-btn active" id="step1-btn">
                <i class="fas fa-user-check"></i> Paso 1: Cliente
            </button>
            <button type="button" class="step-btn" id="step2-btn" disabled>
                <i class="fas fa-dumbbell"></i> Paso 2: Membresía
            </button>
            <button type="button" class="step-btn" id="step3-btn" disabled>
                <i class="fas fa-credit-card"></i> Paso 3: Pago
            </button>
        </div>

        <form action="{{ route('admin.inscripciones.store') }}" method="POST" id="inscripcionForm">
            @csrf
            <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">
            <input type="hidden" name="id_estado" value="{{ $estadoActiva->codigo ?? 100 }}">
            <input type="hidden" id="id_cliente" name="id_cliente" value="">
            <input type="hidden" id="precio_base_hidden" name="precio_base" value="0">
            <input type="hidden" id="precio_final_hidden" name="precio_final" value="0">

            <!-- ========== PASO 1: SELECCIONAR CLIENTE ========== -->
            <div class="step-indicator active" id="step-1">
                <div class="form-section-title">
                    <i class="fas fa-user-check"></i> Seleccionar Cliente
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Clientes disponibles:</strong> Solo se muestran clientes activos que NO tienen una membresía vigente 
                    (clientes nuevos sin inscripción o con membresía vencida/cancelada).
                </div>

                <div class="search-clientes">
                    <input type="text" class="form-control form-control-lg" id="buscarCliente" 
                           placeholder="🔍 Buscar cliente por nombre o RUT...">
                </div>

                <div class="clientes-list" id="clientesList">
                    @forelse($clientes as $cliente)
                        @php
                            // Verificar si tiene inscripción activa
                            $inscripcionActiva = $cliente->inscripciones()
                                ->whereIn('id_estado', [1, 100]) // Estados activos
                                ->where('fecha_vencimiento', '>=', now())
                                ->first();
                            
                            $ultimaInscripcion = $cliente->inscripciones()
                                ->orderBy('fecha_vencimiento', 'desc')
                                ->first();
                            
                            $tieneMembresia = $inscripcionActiva !== null;
                            
                            // Determinar estado del cliente
                            if ($tieneMembresia) {
                                $estadoTexto = 'Membresía Activa';
                                $estadoClase = 'bg-success text-white';
                                $disponible = false;
                            } elseif ($ultimaInscripcion && $ultimaInscripcion->fecha_vencimiento < now()) {
                                $estadoTexto = 'Membresía Vencida';
                                $estadoClase = 'estado-vencida';
                                $disponible = true;
                            } else {
                                $estadoTexto = 'Sin Membresía';
                                $estadoClase = 'estado-sin-membresia';
                                $disponible = true;
                            }
                        @endphp
                        
                        @if($disponible)
                        <div class="cliente-card" data-id="{{ $cliente->id }}" 
                             data-nombre="{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}"
                             data-rut="{{ $cliente->run_pasaporte }}">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="cliente-nombre">
                                        {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                                        @if($cliente->apellido_materno) {{ $cliente->apellido_materno }} @endif
                                    </div>
                                    <div class="cliente-rut">
                                        <i class="fas fa-id-card"></i> {{ $cliente->run_pasaporte ?? 'Sin RUT' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="fas fa-envelope"></i> {{ $cliente->email }}
                                    </small>
                                </div>
                                <div class="col-md-3 text-right">
                                    <span class="estado-badge {{ $estadoClase }}">
                                        {{ $estadoTexto }}
                                    </span>
                                    @if($ultimaInscripcion && $ultimaInscripcion->fecha_vencimiento < now())
                                        <br>
                                        <small class="text-danger">
                                            <i class="fas fa-calendar-times"></i> 
                                            Venció: {{ $ultimaInscripcion->fecha_vencimiento->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users-slash fa-3x mb-3"></i>
                            <h5>No hay clientes disponibles</h5>
                            <p>Todos los clientes tienen membresías activas o no hay clientes registrados.</p>
                            <a href="{{ route('admin.clientes.create') }}" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Crear Nuevo Cliente
                            </a>
                        </div>
                    @endforelse
                </div>

                <div id="clienteSeleccionado" class="alert alert-success mt-3" style="display: none;">
                    <strong><i class="fas fa-check-circle"></i> Cliente seleccionado:</strong>
                    <span id="clienteNombreDisplay"></span>
                </div>
            </div>

            <!-- ========== PASO 2: MEMBRESÍA ========== -->
            <div class="step-indicator" id="step-2">
                <div class="alert alert-info mb-3">
                    <strong><i class="fas fa-user"></i> Cliente:</strong> 
                    <span id="paso2-cliente-nombre">-</span>
                </div>

                <div class="form-section-title"><i class="fas fa-dumbbell"></i> Seleccionar Membresía</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_membresia">Membresía <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                id="id_membresia" name="id_membresia" required>
                            <option value="">-- Seleccionar Membresía --</option>
                            @foreach($membresias as $membresia)
                                @php
                                    $duracionTexto = $membresia->duracion_meses > 0 
                                        ? ($membresia->duracion_meses == 1 ? '1 mes' : $membresia->duracion_meses . ' meses')
                                        : $membresia->duracion_dias . ' día' . ($membresia->duracion_dias > 1 ? 's' : '');
                                    $precio = $membresia->precios->first()->precio_normal ?? 0;
                                @endphp
                                <option value="{{ $membresia->id }}" 
                                        data-duracion="{{ $membresia->duracion_dias }}"
                                        data-duracion-meses="{{ $membresia->duracion_meses }}"
                                        data-precio="{{ $precio }}"
                                        data-precio-convenio="{{ $membresia->precios->first()->precio_convenio ?? 0 }}"
                                        data-max-pausas="{{ $membresia->max_pausas ?? 2 }}">
                                    {{ $membresia->nombre }} ({{ $duracionTexto }}) - ${{ number_format($precio, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_membresia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                               id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" required>
                        @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="fecha_termino_display">Fecha de Término</label>
                        <input type="text" class="form-control" id="fecha_termino_display" readonly 
                               style="background-color: #e9ecef; font-weight: bold; color: #28a745;">
                        <small class="text-muted">Se calcula automáticamente</small>
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-handshake"></i> Convenio / Descuento</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_convenio">¿Tiene Convenio?</label>
                        <select class="form-control" id="id_convenio" name="id_convenio">
                            <option value="">-- Sin Convenio --</option>
                            @foreach($convenios as $convenio)
                                <option value="{{ $convenio->id }}">
                                    {{ $convenio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_motivo_descuento">Motivo del Descuento</label>
                        <select class="form-control" id="id_motivo_descuento" name="id_motivo_descuento">
                            <option value="">-- Sin Motivo --</option>
                            @foreach($motivos as $motivo)
                                <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="descuento_aplicado">Descuento Manual ($)</label>
                        <input type="number" class="form-control" id="descuento_aplicado" name="descuento_aplicado" 
                               value="0" min="0" step="1">
                        <small class="text-muted">Ingrese el monto del descuento adicional</small>
                    </div>
                </div>

                <!-- RESUMEN DE PRECIOS -->
                <div class="precio-box" id="precioBox">
                    <h5 class="mb-3"><i class="fas fa-receipt"></i> Resumen de Precios</h5>
                    <div class="precio-row">
                        <span class="precio-label">Precio Base:</span>
                        <span class="precio-valor" id="display-precio-base">$0</span>
                    </div>
                    <div class="precio-row" id="row-descuento-convenio" style="display: none;">
                        <span class="precio-label">Descuento Convenio:</span>
                        <span class="precio-valor text-success" id="display-descuento-convenio">-$0</span>
                    </div>
                    <div class="precio-row" id="row-descuento-manual" style="display: none;">
                        <span class="precio-label">Descuento Manual:</span>
                        <span class="precio-valor text-success" id="display-descuento-manual">-$0</span>
                    </div>
                    <div class="precio-row">
                        <span class="precio-label"><strong>TOTAL A PAGAR:</strong></span>
                        <span class="precio-valor precio-total" id="display-precio-final">$0</span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 mb-3">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2" 
                                  placeholder="Notas adicionales sobre la inscripción...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- ========== PASO 3: PAGO ========== -->
            <div class="step-indicator" id="step-3">
                <!-- Resumen visual del cliente y membresía -->
                <div class="resumen-inscripcion mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-card bg-primary text-white">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <div class="info-label">Cliente</div>
                                <div class="info-value" id="paso3-cliente-nombre">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card bg-info text-white">
                                <i class="fas fa-dumbbell fa-2x mb-2"></i>
                                <div class="info-label">Membresía</div>
                                <div class="info-value" id="paso3-membresia-nombre">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card bg-success text-white">
                                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                                <div class="info-label">Total a Pagar</div>
                                <div class="info-value h3" id="paso3-precio-total">$0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-money-check-alt"></i> Seleccione Tipo de Pago</div>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="tipo_pago_select">Tipo de Pago <span class="text-danger">*</span></label>
                        <select class="form-control form-control-lg" id="tipo_pago_select">
                            <option value="">-- Seleccionar Tipo --</option>
                            <option value="completo">💵 Pago Completo</option>
                            <option value="abono">💰 Pago Parcial / Abono</option>
                            <option value="mixto">🔀 Pago Mixto</option>
                            <option value="pendiente">⏰ Pago Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-lg" id="fecha_pago" name="fecha_pago" 
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>

                <input type="hidden" id="tipo_pago" name="tipo_pago" value="">
                <input type="hidden" id="pago_pendiente" name="pago_pendiente" value="0">

                <!-- SECCIÓN PAGO SIMPLE (Completo / Abono) -->
                <div id="seccion-pago-simple" style="display:none;">
                    <div class="card card-outline card-primary mb-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-credit-card"></i> Detalles del Pago</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label id="label-monto">Monto a Pagar <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-success text-white">$</span>
                                        </div>
                                        <input type="number" class="form-control" id="monto_abonado" name="monto_abonado" 
                                               value="0" min="0" step="1">
                                    </div>
                                    <small class="text-muted" id="hint-monto"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_metodo_pago">Método de Pago <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-lg" id="id_metodo_pago" name="id_metodo_pago">
                                        <option value="">-- Seleccionar Método --</option>
                                        @foreach($metodosPago as $metodo)
                                            @if(strtolower($metodo->nombre) !== 'mixto')
                                            <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="seccion-restante" style="display:none;">
                                <div class="alert alert-warning">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Saldo Pendiente:</strong>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <span class="h4 text-danger" id="monto-restante-display">$0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN PAGO MIXTO -->
                <div id="seccion-mixto" style="display:none;">
                    <div class="card card-outline card-warning mb-3">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0"><i class="fas fa-shuffle"></i> Pago Mixto - Múltiples Métodos</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover" id="tabla-pagos-mixto">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="80" class="text-center">Acción</th>
                                        <th>Monto</th>
                                        <th>Método de Pago</th>
                                        <th width="150" class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <button type="button" class="btn btn-primary btn-sm" id="btn-agregar-linea">
                                <i class="fas fa-plus"></i> Agregar Línea de Pago
                            </button>
                            
                            <div class="resumen-mixto mt-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <small class="text-muted">Total a Pagar</small>
                                            <div class="h4 text-primary mb-0" id="mixto-total-pagar">$0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <small class="text-muted">Total Ingresado</small>
                                            <div class="h4 text-success mb-0" id="mixto-total-ingresado">$0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 rounded" id="mixto-diferencia-box">
                                            <small class="text-muted">Diferencia</small>
                                            <div class="h4 mb-0" id="mixto-diferencia">$0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="total-mixto" name="total_mixto" value="0">
                            <input type="hidden" id="detalle-pagos-mixto" name="detalle_pagos_mixto" value="[]">
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN PAGO PENDIENTE -->
                <div id="seccion-pendiente" style="display:none;">
                    <div class="alert alert-warning">
                        <div class="text-center py-3">
                            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                            <h4>Pago Pendiente</h4>
                            <p class="mb-0">La inscripción se creará sin pago. El cliente deberá abonar posteriormente.</p>
                            <p class="h4 text-danger mt-2">Total Pendiente: <span id="total-pendiente-display">$0</span></p>
                        </div>
                    </div>
                </div>

                <!-- INFO ADICIONAL -->
                <div id="info-tipo-pago" style="display:none;">
                    <div class="alert" id="alert-tipo-pago"></div>
                </div>
            </div>

            <!-- ========== BOTONES DE NAVEGACIÓN ========== -->
            <div class="buttons-container">
                <div class="buttons-group">
                    <button type="button" class="btn btn-secondary btn-lg" id="btnAnterior" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                </div>
                <div class="buttons-group">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-danger btn-lg">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="button" class="btn btn-primary btn-lg" id="btnSiguiente">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-success btn-lg" id="btnGuardar" style="display: none;">
                        <i class="fas fa-save"></i> Registrar Inscripción
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Configuración global de SweetAlert2 con colores del tema
    const SwalEstoicos = Swal.mixin({
        customClass: {
            popup: 'swal-estoicos',
            confirmButton: 'btn btn-success mx-1',
            cancelButton: 'btn btn-secondary mx-1',
            denyButton: 'btn btn-danger mx-1'
        },
        buttonsStyling: false,
        confirmButtonColor: '#00bf8e',
        cancelButtonColor: '#6c757d',
        background: '#1a1a2e',
        color: '#ffffff',
        iconColor: '#e94560'
    });

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        background: '#1a1a2e',
        color: '#ffffff'
    });

    let pasoActual = 1;
    let clienteSeleccionadoId = null;
    let clienteSeleccionadoNombre = '';
    let precioBase = 0;
    let precioFinal = 0;

    // ========== BUSCAR CLIENTE ==========
    $('#buscarCliente').on('keyup', function() {
        const busqueda = $(this).val().toLowerCase();
        $('.cliente-card').each(function() {
            const nombre = $(this).data('nombre').toLowerCase();
            const rut = ($(this).data('rut') || '').toLowerCase();
            if (nombre.includes(busqueda) || rut.includes(busqueda)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // ========== SELECCIONAR CLIENTE ==========
    $('.cliente-card').on('click', function() {
        $('.cliente-card').removeClass('selected');
        $(this).addClass('selected');
        
        clienteSeleccionadoId = $(this).data('id');
        clienteSeleccionadoNombre = $(this).data('nombre');
        
        $('#id_cliente').val(clienteSeleccionadoId);
        $('#clienteSeleccionado').show();
        $('#clienteNombreDisplay').text(clienteSeleccionadoNombre);
        $('#paso2-cliente-nombre').text(clienteSeleccionadoNombre);
        $('#paso3-cliente-nombre').text(clienteSeleccionadoNombre);

        // Efecto visual
        Toast.fire({
            icon: 'success',
            title: 'Cliente Seleccionado',
            text: clienteSeleccionadoNombre
        });
    });

    // ========== CALCULAR PRECIOS ==========
    function calcularPrecios() {
        const membresiaSelect = $('#id_membresia option:selected');
        precioBase = parseInt(membresiaSelect.data('precio')) || 0;
        let precioConvenio = parseInt(membresiaSelect.data('precio-convenio')) || 0;
        let descuentoConvenio = 0;
        let descuentoManual = parseInt($('#descuento_aplicado').val()) || 0;

        // Si tiene convenio seleccionado y hay precio convenio
        if ($('#id_convenio').val() && precioConvenio > 0) {
            descuentoConvenio = precioBase - precioConvenio;
            $('#row-descuento-convenio').show();
            $('#display-descuento-convenio').text('-$' + descuentoConvenio.toLocaleString('es-CL'));
        } else {
            $('#row-descuento-convenio').hide();
            descuentoConvenio = 0;
        }

        // Descuento manual
        if (descuentoManual > 0) {
            $('#row-descuento-manual').show();
            $('#display-descuento-manual').text('-$' + descuentoManual.toLocaleString('es-CL'));
        } else {
            $('#row-descuento-manual').hide();
        }

        precioFinal = Math.max(0, precioBase - descuentoConvenio - descuentoManual);

        $('#display-precio-base').text('$' + precioBase.toLocaleString('es-CL'));
        $('#display-precio-final').text('$' + precioFinal.toLocaleString('es-CL'));
        
        // Actualizar paso 3
        $('#paso3-precio-total').text('$' + precioFinal.toLocaleString('es-CL'));
        $('#mixto-total-pagar').text('$' + precioFinal.toLocaleString('es-CL'));
        $('#total-pendiente-display').text('$' + precioFinal.toLocaleString('es-CL'));
        $('#monto_abonado').val(precioFinal);
    }

    // ========== CALCULAR FECHA TÉRMINO ==========
    function calcularFechaTermino() {
        const membresiaSelect = $('#id_membresia option:selected');
        const duracion = parseInt(membresiaSelect.data('duracion')) || 0;
        const fechaInicio = $('#fecha_inicio').val();
        
        if (fechaInicio && duracion > 0) {
            const fecha = new Date(fechaInicio);
            fecha.setDate(fecha.getDate() + duracion);
            const fechaFormateada = fecha.toLocaleDateString('es-CL');
            $('#fecha_termino_display').val(fechaFormateada);
        }
    }

    // ========== EVENTOS DE MEMBRESÍA ==========
    $('#id_membresia').on('change', function() {
        calcularPrecios();
        calcularFechaTermino();
        
        const nombreMembresia = $(this).find('option:selected').text();
        $('#paso3-membresia-nombre').text(nombreMembresia);
    });

    $('#fecha_inicio').on('change', calcularFechaTermino);
    $('#id_convenio').on('change', calcularPrecios);
    $('#descuento_aplicado').on('input', calcularPrecios);

    // ========== TIPO DE PAGO (SELECT) ==========
    $('#tipo_pago_select').on('change', function() {
        const tipo = $(this).val();
        $('#tipo_pago').val(tipo);
        
        // Ocultar todas las secciones
        $('#seccion-pago-simple').hide();
        $('#seccion-mixto').hide();
        $('#seccion-pendiente').hide();
        $('#info-tipo-pago').hide();
        
        if (tipo === 'completo') {
            $('#seccion-pago-simple').show();
            $('#pago_pendiente').val('0');
            $('#label-monto').text('Monto Total');
            $('#hint-monto').text('');
            $('#monto_abonado').val(precioFinal);
            $('#seccion-restante').hide();
            mostrarInfoPago('success', '<i class="fas fa-check-circle"></i> Pago completo - Total: $' + precioFinal.toLocaleString('es-CL'));
        } 
        else if (tipo === 'abono') {
            $('#seccion-pago-simple').show();
            $('#pago_pendiente').val('0');
            $('#label-monto').text('Monto a Abonar');
            $('#hint-monto').text('Ingrese el monto que abona el cliente');
            $('#monto_abonado').val('');
            $('#seccion-restante').show();
            mostrarInfoPago('info', '<i class="fas fa-info-circle"></i> Pago parcial - Total a cubrir: $' + precioFinal.toLocaleString('es-CL'));
        } 
        else if (tipo === 'pendiente') {
            $('#seccion-pendiente').show();
            $('#pago_pendiente').val('1');
            mostrarInfoPago('warning', '<i class="fas fa-clock"></i> Sin pago - Se registrará como pendiente');
        } 
        else if (tipo === 'mixto') {
            $('#seccion-mixto').show();
            $('#pago_pendiente').val('0');
            // Agregar primera línea si no hay ninguna
            if ($('#tabla-pagos-mixto tbody tr').length === 0) {
                agregarLineaPago();
            }
            actualizarResumenMixto();
            mostrarInfoPago('info', '<i class="fas fa-shuffle"></i> Pago mixto - Combine métodos de pago');
        }
    });

    function mostrarInfoPago(tipo, mensaje) {
        const alert = $('#alert-tipo-pago');
        alert.removeClass('alert-success alert-info alert-warning alert-danger');
        alert.addClass('alert-' + tipo);
        alert.html(mensaje);
        $('#info-tipo-pago').show();
    }

    // ========== PAGO MIXTO - TABLA DINÁMICA ==========
    function agregarLineaPago() {
        const tbody = $('#tabla-pagos-mixto tbody');
        const fila = $(`
            <tr>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-linea">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" class="form-control monto-mixto" min="0" step="1" placeholder="0">
                    </div>
                </td>
                <td>
                    <select class="form-control metodo-mixto">
                        <option value="">-- Método --</option>
                        @foreach($metodosPago as $metodo)
                        @if(strtolower($metodo->nombre) !== 'mixto')
                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                        @endif
                        @endforeach
                    </select>
                </td>
                <td class="text-right">
                    <span class="subtotal-display font-weight-bold">$0</span>
                </td>
            </tr>
        `);
        tbody.append(fila);
        actualizarResumenMixto();
    }

    // Botón agregar línea
    $('#btn-agregar-linea').on('click', function() {
        agregarLineaPago();
        Toast.fire({
            icon: 'info',
            title: 'Línea agregada'
        });
    });

    // Eliminar línea
    $(document).on('click', '.btn-eliminar-linea', function() {
        const fila = $(this).closest('tr');
        if ($('#tabla-pagos-mixto tbody tr').length > 1) {
            fila.remove();
            actualizarResumenMixto();
        } else {
            SwalEstoicos.fire({
                icon: 'warning',
                title: 'Acción no permitida',
                text: 'Debe haber al menos una línea de pago',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });

    // Actualizar subtotal y resumen al cambiar montos
    $(document).on('input', '.monto-mixto', function() {
        const fila = $(this).closest('tr');
        const monto = parseInt($(this).val()) || 0;
        fila.find('.subtotal-display').text('$' + monto.toLocaleString('es-CL'));
        actualizarResumenMixto();
    });

    $(document).on('change', '.metodo-mixto', function() {
        actualizarResumenMixto();
    });

    function actualizarResumenMixto() {
        let totalIngresado = 0;
        const detalles = [];

        $('#tabla-pagos-mixto tbody tr').each(function() {
            const monto = parseInt($(this).find('.monto-mixto').val()) || 0;
            const metodo = $(this).find('.metodo-mixto').val();
            const metodoNombre = $(this).find('.metodo-mixto option:selected').text();
            
            totalIngresado += monto;
            
            if (monto > 0 && metodo) {
                detalles.push({
                    monto: monto,
                    id_metodo_pago: metodo,
                    metodo_nombre: metodoNombre
                });
            }
        });

        const diferencia = precioFinal - totalIngresado;

        $('#mixto-total-pagar').text('$' + precioFinal.toLocaleString('es-CL'));
        $('#mixto-total-ingresado').text('$' + totalIngresado.toLocaleString('es-CL'));
        
        const boxDiferencia = $('#mixto-diferencia-box');
        boxDiferencia.removeClass('ok error');
        
        if (diferencia === 0) {
            $('#mixto-diferencia').text('$0 ✓');
            boxDiferencia.addClass('ok');
        } else if (diferencia > 0) {
            $('#mixto-diferencia').text('-$' + diferencia.toLocaleString('es-CL'));
            boxDiferencia.addClass('error');
        } else {
            $('#mixto-diferencia').text('+$' + Math.abs(diferencia).toLocaleString('es-CL'));
            boxDiferencia.addClass('error');
        }

        // Guardar en campos ocultos
        $('#total-mixto').val(totalIngresado);
        $('#detalle-pagos-mixto').val(JSON.stringify(detalles));
    }

    // ========== CALCULAR RESTANTE (PAGO PARCIAL) ==========
    $('#monto_abonado').on('input', function() {
        let monto = parseInt($(this).val()) || 0;
        
        // No permitir más del precio final
        if (monto > precioFinal) {
            monto = precioFinal;
            $(this).val(precioFinal);
        }
        
        const restante = Math.max(0, precioFinal - monto);
        $('#monto-restante-display').text('$' + restante.toLocaleString('es-CL'));
        
        // Actualizar alerta
        if (monto > 0 && monto < precioFinal) {
            mostrarInfoPago('info', `<i class="fas fa-coins"></i> Abono: $${monto.toLocaleString('es-CL')} | Restante: $${restante.toLocaleString('es-CL')}`);
        } else if (monto >= precioFinal) {
            mostrarInfoPago('success', '<i class="fas fa-check-circle"></i> El monto cubre el total');
        }
    });

    // ========== NAVEGACIÓN DE PASOS ==========
    function irAPaso(paso) {
        $('.step-indicator').removeClass('active');
        $('#step-' + paso).addClass('active');
        
        $('.step-btn').removeClass('active');
        $('#step' + paso + '-btn').addClass('active');
        
        // Marcar pasos anteriores como completados
        for (let i = 1; i < paso; i++) {
            $('#step' + i + '-btn').addClass('completed').prop('disabled', false);
        }
        
        // Mostrar/ocultar botones
        if (paso === 1) {
            $('#btnAnterior').hide();
            $('#btnSiguiente').show();
            $('#btnGuardar').hide();
        } else if (paso === 3) {
            $('#btnAnterior').show();
            $('#btnSiguiente').hide();
            $('#btnGuardar').show();
        } else {
            $('#btnAnterior').show();
            $('#btnSiguiente').show();
            $('#btnGuardar').hide();
        }
        
        pasoActual = paso;
    }

    // Validar paso actual
    function validarPaso(paso) {
        if (paso === 1) {
            if (!clienteSeleccionadoId) {
                SwalEstoicos.fire({
                    icon: 'warning',
                    title: 'Cliente requerido',
                    text: 'Por favor, selecciona un cliente de la lista',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            return true;
        } else if (paso === 2) {
            if (!$('#id_membresia').val()) {
                SwalEstoicos.fire({
                    icon: 'warning',
                    title: 'Membresía requerida',
                    text: 'Por favor, selecciona una membresía',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            if (!$('#fecha_inicio').val()) {
                SwalEstoicos.fire({
                    icon: 'warning',
                    title: 'Fecha requerida',
                    text: 'Por favor, ingresa la fecha de inicio',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            return true;
        }
        return true;
    }

    $('#btnSiguiente').on('click', function() {
        if (validarPaso(pasoActual)) {
            irAPaso(pasoActual + 1);
        }
    });

    $('#btnAnterior').on('click', function() {
        irAPaso(pasoActual - 1);
    });

    // Navegación por botones de paso
    $('.step-btn').on('click', function() {
        const pasoDestino = parseInt($(this).attr('id').replace('step', '').replace('-btn', ''));
        if (!$(this).prop('disabled')) {
            irAPaso(pasoDestino);
        }
    });

    // ========== ENVÍO DEL FORMULARIO ==========
    $('#inscripcionForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validar todos los pasos
        if (!clienteSeleccionadoId) {
            SwalEstoicos.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Por favor, selecciona un cliente',
                confirmButtonText: '<i class="fas fa-check"></i> Entendido'
            });
            irAPaso(1);
            return false;
        }
        
        if (!$('#id_membresia').val()) {
            SwalEstoicos.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Por favor, selecciona una membresía',
                confirmButtonText: '<i class="fas fa-check"></i> Entendido'
            });
            irAPaso(2);
            return false;
        }

        const tipoPago = $('#tipo_pago').val();
        
        if (!tipoPago) {
            SwalEstoicos.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Por favor, selecciona un tipo de pago',
                confirmButtonText: '<i class="fas fa-check"></i> Entendido'
            });
            return false;
        }
        
        if (tipoPago === 'mixto') {
            const totalMixto = parseInt($('#total-mixto').val()) || 0;
            const detalles = JSON.parse($('#detalle-pagos-mixto').val() || '[]');
            
            if (detalles.length === 0 || totalMixto <= 0) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error en pago mixto',
                    text: 'Agregue al menos un método de pago con monto',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }

            // Verificar que todos tengan método seleccionado
            let lineasSinMetodo = 0;
            $('#tabla-pagos-mixto tbody tr').each(function() {
                const monto = parseInt($(this).find('.monto-mixto').val()) || 0;
                const metodo = $(this).find('.metodo-mixto').val();
                if (monto > 0 && !metodo) lineasSinMetodo++;
            });
            
            if (lineasSinMetodo > 0) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: `Seleccione método de pago en ${lineasSinMetodo} línea(s)`,
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
        } 
        else if (tipoPago !== 'pendiente') {
            if (!$('#monto_abonado').val() || parseFloat($('#monto_abonado').val()) <= 0) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Por favor, ingresa el monto del pago',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            if (!$('#id_metodo_pago').val()) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Por favor, selecciona un método de pago',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
        }

        // Determinar texto de tipo de pago
        let tipoPagoTexto = '';
        switch(tipoPago) {
            case 'completo': tipoPagoTexto = '💵 Pago Completo'; break;
            case 'abono': tipoPagoTexto = '💰 Pago Parcial'; break;
            case 'mixto': tipoPagoTexto = '🔀 Pago Mixto'; break;
            case 'pendiente': tipoPagoTexto = '⏰ Pago Pendiente'; break;
        }

        // Confirmar antes de enviar
        SwalEstoicos.fire({
            icon: 'question',
            title: '¿Confirmar inscripción?',
            html: `
                <div style="text-align: left; padding: 10px;">
                    <p style="margin: 8px 0;"><i class="fas fa-user text-info"></i> <strong>Cliente:</strong> ${clienteSeleccionadoNombre}</p>
                    <p style="margin: 8px 0;"><i class="fas fa-dumbbell text-warning"></i> <strong>Membresía:</strong> ${$('#id_membresia option:selected').text().split(' - ')[0]}</p>
                    <p style="margin: 8px 0;"><i class="fas fa-credit-card text-primary"></i> <strong>Tipo Pago:</strong> ${tipoPagoTexto}</p>
                    <hr style="border-color: rgba(255,255,255,0.2); margin: 12px 0;">
                    <p style="margin: 8px 0; font-size: 1.2em;"><i class="fas fa-dollar-sign text-success"></i> <strong>Total:</strong> <span style="color: #00bf8e;">$${precioFinal.toLocaleString('es-CL')}</span></p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Confirmar Inscripción',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                SwalEstoicos.fire({
                    title: 'Registrando inscripción...',
                    html: '<i class="fas fa-spinner fa-spin fa-2x"></i><br><br>Por favor espere...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Desactivar botón para evitar doble envío
                $('#btnGuardar').prop('disabled', true);
                
                // Enviar formulario
                this.submit();
            }
        });
    });
});
</script>
@stop