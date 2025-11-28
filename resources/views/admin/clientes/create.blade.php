@extends('adminlte::page')

@section('title', 'Crear Cliente - EstóicosGym')

@section('css')
    <script>
        // ✅ FUNCIONES DEFINIDAS AQUÍ PARA ASEGURAR QUE EXISTEN ANTES DE QUE EL HTML LAS LLAME
        let currentStep = 1;
        const totalSteps = 3;
        let isSubmitting = false;

        console.log('========== FUNCIONES JAVASCRIPT INICIALIZADAS ==========');
        console.log('currentStep:', currentStep);
        console.log('totalSteps:', totalSteps);
        console.log('Funciones disponibles: nextStep, previousStep, goToStep, validateStep');
        console.log('=======================================================');

        function nextStep() {
            console.log('🔵 nextStep() LLAMADO - currentStep:', currentStep, 'totalSteps:', totalSteps);
            if (currentStep < totalSteps) {
                console.log('🔵 Avanzando a paso:', currentStep + 1);
                goToStep(currentStep + 1);
            } else {
                console.log('🔴 YA ESTÁS EN EL ÚLTIMO PASO (paso', currentStep, ')');
            }
        }

        function previousStep() {
            console.log('🔵 previousStep() LLAMADO - currentStep:', currentStep);
            if (currentStep > 1) {
                console.log('🔵 Retrocediendo a paso:', currentStep - 1);
                goToStep(currentStep - 1);
            } else {
                console.log('🔴 YA ESTÁS EN EL PRIMER PASO');
            }
        }

        function goToStep(step) {
            console.log('🟡 goToStep(' + step + ') - Cambiar a paso ' + step);
            
            if (step < 1 || step > totalSteps) {
                console.error('🔴 Paso INVÁLIDO:', step, '(debe ser 1-' + totalSteps + ')');
                return;
            }
            
            // Ocultar todos los pasos
            console.log('🟡 Ocultando todos los pasos...');
            document.querySelectorAll('.step-indicator').forEach(el => {
                el.classList.remove('active');
            });
            
            // Mostrar paso actual
            const stepElement = document.getElementById(`step-${step}`);
            if (!stepElement) {
                console.error('🔴 NO ENCONTRÉ elemento #step-' + step);
                return;
            }
            
            console.log('🟡 Mostrando paso:', step);
            stepElement.classList.add('active');
            currentStep = step;
            
            // Actualizar indicadores
            updateStepIndicators();
            
            // Actualizar botones según el paso
            const btnAnterior = document.getElementById('btn-anterior');
            const btnSiguiente = document.getElementById('btn-siguiente');
            const btnGuardarSoloCliente = document.getElementById('btn-guardar-solo-cliente');
            const btnGuardarConMembresia = document.getElementById('btn-guardar-con-membresia');
            const btnGuardarCompleto = document.getElementById('btn-guardar-completo');
            const flujoInput = document.getElementById('flujo_cliente');
            
            console.log('🟡 Elementos de botones encontrados:', {
                btnAnterior: !!btnAnterior,
                btnSiguiente: !!btnSiguiente,
                btnGuardarSoloCliente: !!btnGuardarSoloCliente,
                btnGuardarConMembresia: !!btnGuardarConMembresia,
                btnGuardarCompleto: !!btnGuardarCompleto
            });
            
            // Ocultar todos los botones de guardado
            btnGuardarSoloCliente.style.display = 'none';
            btnGuardarConMembresia.style.display = 'none';
            btnGuardarCompleto.style.display = 'none';
            btnSiguiente.style.display = 'none';
            btnAnterior.style.display = 'none';
            
            if (step === 1) {
                // PASO 1: Mostrar opción de guardar solo cliente
                btnAnterior.style.display = 'none';
                btnSiguiente.style.display = 'block';
                btnGuardarSoloCliente.style.display = 'block';
                flujoInput.value = 'solo_cliente';
                console.log('✅ PASO 1 - Botones mostrados: [Siguiente] [Guardar Cliente]');
            } else if (step === 2) {
                // PASO 2: Mostrar opción de guardar cliente + membresía
                btnAnterior.style.display = 'block';
                btnSiguiente.style.display = 'block';
                btnGuardarConMembresia.style.display = 'block';
                flujoInput.value = 'con_membresia';
                console.log('✅ PASO 2 - Botones mostrados: [Anterior] [Siguiente] [Guardar con Membresía]');
            } else if (step === totalSteps) {
                // PASO 3: Mostrar opción de guardar todo completo
                btnAnterior.style.display = 'block';
                btnSiguiente.style.display = 'none';
                btnGuardarCompleto.style.display = 'block';
                flujoInput.value = 'completo';
                console.log('✅ PASO 3 - Botones mostrados: [Anterior] [Guardar Todo]');
            }
        }

        function updateStepIndicators() {
            for (let i = 1; i <= totalSteps; i++) {
                const btn = document.getElementById(`step${i}-btn`);
                btn.classList.remove('active', 'completed');
                
                if (i < currentStep) {
                    btn.classList.add('completed');
                    btn.disabled = false;
                } else if (i === currentStep) {
                    btn.classList.add('active');
                    btn.disabled = false;
                } else if (i === currentStep + 1) {
                    btn.disabled = false;
                } else {
                    btn.disabled = true;
                }
            }
        }

        function validateStep(step) {
            console.log('validateStep(' + step + ') - DESHABILITADO - retorna TRUE');
            return true;
        }

        function handleFormSubmit(event) {
            event.preventDefault();
            console.log('handleFormSubmit - flujo:', document.getElementById('flujo_cliente').value);
            document.getElementById('clienteForm').submit();
            return false;
        }
    </script>
    <style>
        /* ===== STEP NAVIGATION ===== */
        .step-indicator { display: none; }
        .step-indicator.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .steps-nav { 
            display: flex; 
            gap: 1rem; 
            margin-bottom: 2rem; 
            flex-wrap: wrap;
            padding: 1rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .step-btn {
            flex: 1;
            min-width: 140px;
            padding: 1rem;
            text-align: center;
            border-radius: 0.75rem;
            background: white;
            border: 2px solid #dee2e6;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .step-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .step-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .step-btn.completed {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-color: #11998e;
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4);
        }
        
        .step-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #e9ecef;
        }
        
        .step-btn i { 
            display: block; 
            margin-bottom: 0.4rem; 
            font-size: 1.2em;
        }

        /* ===== SWEETALERT2 CUSTOM STYLES ===== */
        .swal2-popup-custom {
            border-radius: 12px;
            border: 2px solid #667eea;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.2);
        }

        /* ===== FORM SECTIONS ===== */
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 1.5rem 0 1rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section-title i {
            color: #667eea;
            font-size: 1.2em;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* ===== PRECIO BOX ===== */
        .precio-box {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            border: 2px solid #667eea;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .precio-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .precio-item:last-child {
            margin-bottom: 0;
            border-top: 2px solid #667eea;
            padding-top: 0.75rem;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .precio-label {
            color: #495057;
            font-weight: 600;
        }

        .precio-valor {
            color: #667eea;
            font-weight: 700;
            font-size: 1.1em;
        }

        .precio-normal-tachado {
            text-decoration: line-through;
            color: #adb5bd;
            margin-right: 0.5rem;
        }

        .descuento-badge {
            display: inline-block;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 700;
            margin-left: 0.5rem;
        }

        /* ===== SPINNER ANIMATION ===== */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fa-spinner {
            animation: spin 1s linear infinite !important;
        }

        /* ===== BUTTONS LAYOUT ===== */
        .buttons-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 0.75rem;
            border: 1px solid #dee2e6;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .buttons-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .steps-nav {
                gap: 0.5rem;
                padding: 0.75rem;
            }

            .step-btn {
                min-width: 100px;
                padding: 0.75rem;
                font-size: 0.75rem;
            }

            .buttons-container {
                flex-direction: column;
                padding: 1rem;
            }

            .buttons-group {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-user-plus"></i> Crear Nuevo Cliente
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Errores en el Formulario</h4>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks"></i> Registro de Cliente - Flujo Completo
            </h3>
        </div>

        <div class="card-body">
            <!-- Indicador de pasos -->
            <div class="steps-nav">
                <button type="button" class="step-btn active" onclick="goToStep(1)" id="step1-btn">
                    <i class="fas fa-user"></i>Paso 1: Datos
                </button>
                <button type="button" class="step-btn" onclick="goToStep(2)" id="step2-btn" disabled>
                    <i class="fas fa-dumbbell"></i>Paso 2: Membresía
                </button>
                <button type="button" class="step-btn" onclick="goToStep(3)" id="step3-btn" disabled>
                    <i class="fas fa-credit-card"></i>Paso 3: Pago
                </button>
            </div>

            <form action="{{ route('admin.clientes.store') }}" method="POST" id="clienteForm" onsubmit="return handleFormSubmit(event)">
                @csrf
                <!-- Token anti-CSRF para prevenir doble envío -->
                <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">

                <!-- ============ PASO 1: DATOS DEL CLIENTE ============ -->
                <div class="step-indicator active" id="step-1">
                    
                    <div class="form-section-title">
                        <i class="fas fa-id-card"></i> Identificación
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="run_pasaporte" class="form-label">RUT/Pasaporte</label>
                            <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                                   id="run_pasaporte" name="run_pasaporte" placeholder="Ej: 7.882.382-4 o 78823824" 
                                   value="{{ old('run_pasaporte') }}">
                            <small class="text-muted">Opcional - Formato: XX.XXX.XXX-X</small>
                            @error('run_pasaporte')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-user"></i> Datos Personales
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                                   id="nombres" name="nombres" value="{{ old('nombres') }}" required>
                            @error('nombres')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                                   id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                            @error('apellido_paterno')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="apellido_materno" class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror" 
                                   id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}">
                            @error('apellido_materno')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                   id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-phone"></i> Contacto
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="celular" class="form-label">Celular <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('celular') is-invalid @enderror" 
                                   id="celular" name="celular" value="{{ old('celular') }}" required>
                            @error('celular')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- CONTACTO DE EMERGENCIA -->
                    <div class="form-section-title">
                        <i class="fas fa-heart-pulse"></i> Contacto de Emergencia
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contacto_emergencia" class="form-label">Nombre del Contacto</label>
                            <input type="text" class="form-control @error('contacto_emergencia') is-invalid @enderror" 
                                   id="contacto_emergencia" name="contacto_emergencia" placeholder="Ej: Juan García" 
                                   value="{{ old('contacto_emergencia') }}">
                            @error('contacto_emergencia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_emergencia" class="form-label">Teléfono del Contacto</label>
                            <input type="tel" class="form-control @error('telefono_emergencia') is-invalid @enderror" 
                                   id="telefono_emergencia" name="telefono_emergencia" placeholder="+56912345678" 
                                   value="{{ old('telefono_emergencia') }}">
                            @error('telefono_emergencia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- DOMICILIO -->
                    <div class="form-section-title">
                        <i class="fas fa-map-marker-alt"></i> Domicilio
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                   id="direccion" name="direccion" placeholder="Calle, número, apartado..." 
                                   value="{{ old('direccion') }}">
                            @error('direccion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- OBSERVACIONES -->
                    <div class="form-section-title">
                        <i class="fas fa-sticky-note"></i> Observaciones
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="observaciones" class="form-label">Notas Adicionales</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="3" 
                                      placeholder="Información adicional del cliente...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <!-- ============ PASO 2: MEMBRESÍA E INSCRIPCIÓN ============ -->
                <div class="step-indicator" id="step-2">
                    
                    <div class="form-section-title">
                        <i class="fas fa-dumbbell"></i> Seleccionar Membresía
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_membresia" class="form-label">Membresía <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                    id="id_membresia" name="id_membresia" onchange="actualizarPrecio()">
                                <option value="">-- Seleccionar Membresía --</option>
                                @foreach($membresias as $membresia)
                                    <option value="{{ $membresia->id }}" {{ old('id_membresia') == $membresia->id ? 'selected' : '' }}>
                                        {{ $membresia->nombre }} ({{ $membresia->duracion_dias }} días)
                                    </option>
                                @endforeach
                            </select>
                            @error('id_membresia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}">
                            @error('fecha_inicio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Convenio (Descuento) -->
                    <div class="form-section-title">
                        <i class="fas fa-handshake"></i> Convenio / Descuento
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_convenio" class="form-label">¿Cliente tiene descuento?</label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" name="id_convenio" onchange="actualizarPrecio()">
                                <option value="">-- Sin Convenio --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" {{ old('id_convenio') == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }} ({{ $convenio->descuento_porcentaje }}% desc.)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Aplica descuento automático al precio de la membresía</small>
                            @error('id_convenio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Resumen de Precio con Descuento -->
                    <div class="precio-box" id="precioBox" style="display:none;">
                        <h5 style="margin-top: 0; margin-bottom: 1rem; color: #2c3e50;">
                            <i class="fas fa-tag"></i> Resumen de Precios
                        </h5>
                        <div class="precio-item">
                            <span class="precio-label">Precio Normal:</span>
                            <span id="precio-normal" class="precio-valor">$0</span>
                        </div>
                        <div class="precio-item" id="descuentoItem" style="display:none;">
                            <span class="precio-label">Descuento:</span>
                            <span id="precio-descuento" class="precio-descuento">-$0</span>
                        </div>
                        <div class="precio-item">
                            <span class="precio-label">Precio Final:</span>
                            <span id="precio-final" style="color: #11998e; font-weight: 700; font-size: 1.2em;">$0</span>
                            <span id="badge-descuento" class="descuento-badge" style="display:none;"><i class="fas fa-gift"></i> Descuento Aplicado</span>
                        </div>
                    </div>

                </div>

                <!-- ============ PASO 3: PAGO ============ -->
                <div class="step-indicator" id="step-3">
                    
                    <div class="form-section-title">
                        <i class="fas fa-credit-card"></i> Información de Pago
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="monto_abonado" class="form-label">Monto Abonado <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                   id="monto_abonado" name="monto_abonado" min="0.01" step="0.01" value="{{ old('monto_abonado', 0) }}">
                            <small class="text-muted" id="monto_sugerido"></small>
                            @error('monto_abonado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_metodo_pago" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_metodo_pago') is-invalid @enderror" 
                                    id="id_metodo_pago" name="id_metodo_pago">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}" {{ old('id_metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                        {{ $metodo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_metodo_pago')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_pago" class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}">
                            @error('fecha_pago')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <hr class="my-4">

                <!-- Navegación entre pasos -->
                <div class="buttons-container">
                    <div>
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div class="buttons-group">
                        <button type="button" id="btn-anterior" class="btn btn-outline-secondary" onclick="previousStep()" style="display:none;">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <button type="button" id="btn-siguiente" class="btn btn-primary" onclick="nextStep()">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>

                        <!-- PASO 1: Solo guardar cliente -->
                        <button type="submit" id="btn-guardar-solo-cliente" class="btn btn-info" style="display:none;" title="Guardar solo este cliente sin membresía">
                            <i class="fas fa-user-check"></i> Guardar Cliente
                            <span id="btn-spinner-cliente" style="display:none; margin-left: 0.5rem;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>

                        <!-- PASO 2: Guardar cliente + membresía (sin pago) -->
                        <button type="submit" id="btn-guardar-con-membresia" class="btn btn-success" style="display:none;" title="Guardar cliente con membresía (sin pago)">
                            <i class="fas fa-layer-group"></i> Guardar con Membresía
                            <span id="btn-spinner-membresia" style="display:none; margin-left: 0.5rem;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>

                        <!-- PASO 3: Guardar cliente + membresía + pago (completo) -->
                        <button type="submit" id="btn-guardar-completo" class="btn btn-success" style="display:none;" title="Guardar cliente con membresía y pago">
                            <i class="fas fa-check-circle"></i> <span id="btn-text">Guardar Todo</span>
                            <span id="btn-spinner-completo" style="display:none; margin-left: 0.5rem;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Campo oculto para indicar qué tipo de guardado es -->
                <input type="hidden" id="flujo_cliente" name="flujo_cliente" value="completo">

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Actualizar precio cuando cambia membresía o convenio
        function actualizarPrecio() {
            const membresiaSelect = document.getElementById('id_membresia');
            const convenioSelect = document.getElementById('id_convenio');
            
            if (!membresiaSelect.value) {
                document.getElementById('precioBox').style.display = 'none';
                return;
            }
            
            const membresia_id = membresiaSelect.value;
            fetch(`/admin/api/precio-membresia/${membresia_id}${convenioSelect.value ? '?convenio=' + convenioSelect.value : ''}`)
                .then(response => response.json())
                .then(data => {
                    let precioNormal = data.precio_normal || 0;
                    let precioFinal = data.precio_final || precioNormal;
                    let descuento = precioNormal - precioFinal;
                    
                    document.getElementById('precio-normal').textContent = '$' + precioNormal.toLocaleString('es-CL', {maximumFractionDigits: 0});
                    document.getElementById('precio-final').textContent = '$' + precioFinal.toLocaleString('es-CL', {maximumFractionDigits: 0});
                    
                    if (descuento > 0) {
                        document.getElementById('descuentoItem').style.display = 'flex';
                        document.getElementById('precio-descuento').textContent = '-$' + descuento.toLocaleString('es-CL', {maximumFractionDigits: 0});
                        document.getElementById('badge-descuento').style.display = 'inline-block';
                    } else {
                        document.getElementById('descuentoItem').style.display = 'none';
                        document.getElementById('badge-descuento').style.display = 'none';
                    }
                    
                    document.getElementById('monto_sugerido').textContent = `Sugerido: $${precioFinal.toLocaleString('es-CL', {maximumFractionDigits: 0})}`;
                    document.getElementById('monto_abonado').placeholder = `$${precioFinal.toLocaleString('es-CL', {maximumFractionDigits: 0})}`;
                    
                    document.getElementById('precioBox').style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
        }

        // Formatear RUT automáticamente
        function formatearRutEnTiempoReal() {
            const rutInput = document.getElementById('run_pasaporte');
            let rut = rutInput.value.trim();

            if (!rut) {
                rutInput.classList.remove('is-invalid', 'is-valid');
                return;
            }

            rut = rut.toUpperCase().replace(/[^0-9K.\-\s]/g, '');

            if (/^[\d\s\.\-K]+$/.test(rut)) {
                let rutLimpio = rut.replace(/\s/g, '').replace(/\./g, '').replace(/\-/g, '');
                
                if (rutLimpio.length > 0 && rutLimpio.length >= 7 && rutLimpio.length <= 9) {
                    let dv = rutLimpio.substring(rutLimpio.length - 1);
                    let numero = rutLimpio.substring(0, rutLimpio.length - 1);
                    
                    if (numero.length <= 1) {
                        rutInput.value = numero + '-' + dv;
                    } else if (numero.length <= 4) {
                        let p1 = numero.substring(0, numero.length - 3);
                        let p2 = numero.substring(numero.length - 3);
                        rutInput.value = p1 + '.' + p2 + '-' + dv;
                    } else {
                        let p1 = numero.substring(0, numero.length - 6);
                        let p2 = numero.substring(numero.length - 6, numero.length - 3);
                        let p3 = numero.substring(numero.length - 3);
                        rutInput.value = p1 + '.' + p2 + '.' + p3 + '-' + dv;
                    }
                }
            }
        }

        // Validar RUT con API
        function validarRutAjax() {
            const rutInput = document.getElementById('run_pasaporte');
            const rut = rutInput.value.trim();

            if (!rut) {
                rutInput.classList.remove('is-invalid', 'is-valid');
                return;
            }

            fetch('/admin/api/clientes/validar-rut', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ rut: rut })
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    rutInput.classList.remove('is-invalid');
                    rutInput.classList.add('is-valid');
                    rutInput.value = data.rut_formateado;
                    let feedback = rutInput.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.remove();
                    }
                } else {
                    rutInput.classList.remove('is-valid');
                    rutInput.classList.add('is-invalid');
                    let feedback = rutInput.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback d-block';
                        rutInput.parentNode.insertBefore(feedback, rutInput.nextSibling);
                    }
                    feedback.textContent = data.message;
                }
            })
            .catch(error => console.error('Error validando RUT:', error));
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🟢 DOMContentLoaded - Inicializando formulario');
            
            goToStep(1);
            
            const convenioSelect = document.getElementById('id_convenio');
            if (convenioSelect) {
                convenioSelect.addEventListener('change', actualizarPrecio);
            }
            
            const rutInput = document.getElementById('run_pasaporte');
            if (rutInput) {
                rutInput.addEventListener('blur', validarRutAjax);
                rutInput.addEventListener('input', formatearRutEnTiempoReal);
            }
            
            console.log('✅ Formulario inicializado correctamente');
        });
    </script>
@endpush
