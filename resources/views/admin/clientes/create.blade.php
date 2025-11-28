@extends('adminlte::page')

@section('title', 'Crear Cliente - EstóicosGym')

@section('css')
    <style>
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
        }
        
        .step-btn {
            flex: 1;
            min-width: 120px;
            padding: 1rem;
            text-align: center;
            border-radius: 0.75rem;
            background: white;
            border: 2px solid #dee2e6;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .step-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .step-btn.completed {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .step-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 1.5rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
        }

        .precio-box {
            background: #f0f4ff;
            border: 2px solid #667eea;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }

        .buttons-container {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .buttons-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
    </style>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function goToStep(step) {
            if (step < 1 || step > totalSteps) return;
            
            document.querySelectorAll('.step-indicator').forEach(el => {
                el.classList.remove('active');
            });
            
            const stepElement = document.getElementById(`step-${step}`);
            if (stepElement) {
                stepElement.classList.add('active');
                currentStep = step;
                updateButtons();
                updateStepButtons();
            }
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                goToStep(currentStep - 1);
            }
        }

        function updateButtons() {
            const btnAnterior = document.getElementById('btn-anterior');
            const btnSiguiente = document.getElementById('btn-siguiente');
            const btnGuardarSoloCliente = document.getElementById('btn-guardar-solo-cliente');
            const btnGuardarConMembresia = document.getElementById('btn-guardar-con-membresia');
            const btnGuardarCompleto = document.getElementById('btn-guardar-completo');
            const flujoInput = document.getElementById('flujo_cliente');
            
            btnAnterior.style.display = 'none';
            btnSiguiente.style.display = 'none';
            btnGuardarSoloCliente.style.display = 'none';
            btnGuardarConMembresia.style.display = 'none';
            btnGuardarCompleto.style.display = 'none';
            
            if (currentStep === 1) {
                btnSiguiente.style.display = 'block';
                btnGuardarSoloCliente.style.display = 'block';
                flujoInput.value = 'solo_cliente';
            } else if (currentStep === 2) {
                btnAnterior.style.display = 'block';
                btnSiguiente.style.display = 'block';
                btnGuardarConMembresia.style.display = 'block';
                flujoInput.value = 'con_membresia';
            } else if (currentStep === 3) {
                btnAnterior.style.display = 'block';
                btnGuardarCompleto.style.display = 'block';
                flujoInput.value = 'completo';
            }
        }

        function updateStepButtons() {
            for (let i = 1; i <= totalSteps; i++) {
                const btn = document.getElementById(`step${i}-btn`);
                btn.classList.remove('active', 'completed');
                
                if (i < currentStep) {
                    btn.classList.add('completed');
                    btn.disabled = false;
                } else if (i === currentStep) {
                    btn.classList.add('active');
                    btn.disabled = false;
                } else {
                    btn.disabled = true;
                }
            }
        }

        function actualizarPrecio() {
            const membresiaSelect = document.getElementById('id_membresia');
            const convenioSelect = document.getElementById('id_convenio');
            const fechaInicio = document.getElementById('fecha_inicio');
            
            if (!membresiaSelect || !membresiaSelect.value) {
                const precioBox = document.getElementById('precioBox');
                if (precioBox) precioBox.style.display = 'none';
                return;
            }
            
            const membresia_id = membresiaSelect.value;
            const convenio_id = convenioSelect ? convenioSelect.value : '';
            
            console.log('Fetching precio para membresia:', membresia_id, 'convenio:', convenio_id);
            
            fetch(`/admin/api/precio-membresia/${membresia_id}${convenio_id ? '?convenio=' + convenio_id : ''}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta API:', data);
                    
                    if (!data || data.error) {
                        console.error('Error en API:', data?.error);
                        return;
                    }
                    
                    const precioBase = parseInt(data.precio_base) || 0;
                    const precioConConvenio = parseInt(data.precio_final) || precioBase;
                    const duracionDias = parseInt(data.duracion_dias) || 30;
                    
                    const precioBoxEl = document.getElementById('precioBox');
                    if (precioBoxEl) precioBoxEl.style.display = 'block';
                    
                    const normalEl = document.getElementById('precio-normal');
                    const convenioEl = document.getElementById('precio-convenio');
                    
                    if (normalEl) normalEl.textContent = '$' + precioBase.toLocaleString('es-CL');
                    if (convenioEl) convenioEl.textContent = '$' + precioConConvenio.toLocaleString('es-CL');
                    
                    if (fechaInicio && fechaInicio.value) {
                        const inicio = new Date(fechaInicio.value);
                        const termino = new Date(inicio);
                        termino.setDate(termino.getDate() + duracionDias);
                        
                        const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
                        const terminoFormato = termino.toLocaleDateString('es-CL', options);
                        
                        const fechaTerminoEl = document.getElementById('fecha-termino');
                        if (fechaTerminoEl) fechaTerminoEl.textContent = terminoFormato;
                    }
                    
                    actualizarPrecioFinal(precioConConvenio);
                })
                .catch(error => {
                    console.error('Error en fetch:', error);
                });
        }

        function actualizarPrecioFinal(precioConConvenio = null) {
            const descuentoManualInput = document.getElementById('descuento_manual');
            const precioTotalEl = document.getElementById('precio-total');
            const descManualDisplay = document.getElementById('desc-manual-display');
            
            if (!precioTotalEl || !descuentoManualInput) return;
            
            if (precioConConvenio === null) {
                const convenioEl = document.getElementById('precio-convenio');
                if (convenioEl) {
                    const text = convenioEl.textContent.replace('$', '').replace(/\./g, '').trim();
                    precioConConvenio = parseInt(text) || 0;
                } else {
                    return;
                }
            }
            
            const descuentoManual = parseInt(descuentoManualInput.value) || 0;
            const precioTotal = Math.max(0, precioConConvenio - descuentoManual);
            
            if (descManualDisplay) {
                descManualDisplay.textContent = descuentoManual > 0 ? '-$' + descuentoManual.toLocaleString('es-CL') : '-$0';
            }
            
            precioTotalEl.textContent = '$' + precioTotal.toLocaleString('es-CL');
        }

        function actualizarNombreCliente() {
            const nombres = document.getElementById('nombres').value || '';
            const apellido = document.getElementById('apellido_paterno').value || '';
            const clienteNombreEl = document.getElementById('cliente-nombre');
            
            if (clienteNombreEl) {
                clienteNombreEl.textContent = (nombres + ' ' + apellido).trim() || 'Ingrese datos en Paso 1';
            }
        }

        function handleFormSubmit(event) {
            event.preventDefault();
            document.getElementById('clienteForm').submit();
            return false;
        }

        document.addEventListener('DOMContentLoaded', function() {
            goToStep(1);
            
            const convenioSelect = document.getElementById('id_convenio');
            const membresiaSelect = document.getElementById('id_membresia');
            const fechaInicio = document.getElementById('fecha_inicio');
            const nombresInput = document.getElementById('nombres');
            const apellidoInput = document.getElementById('apellido_paterno');
            const descuentoManualInput = document.getElementById('descuento_manual');
            
            if (convenioSelect) convenioSelect.addEventListener('change', actualizarPrecio);
            if (membresiaSelect) membresiaSelect.addEventListener('change', actualizarPrecio);
            if (fechaInicio) fechaInicio.addEventListener('change', actualizarPrecio);
            
            if (nombresInput) nombresInput.addEventListener('change', actualizarNombreCliente);
            if (apellidoInput) apellidoInput.addEventListener('change', actualizarNombreCliente);
            
            if (descuentoManualInput) {
                descuentoManualInput.addEventListener('change', actualizarPrecioFinal);
                descuentoManualInput.addEventListener('input', actualizarPrecioFinal);
            }
        });
    </script>
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
                <i class="fas fa-tasks"></i> Registro de Cliente - 3 Pasos
            </h3>
        </div>

        <div class="card-body">
            <div class="steps-nav">
                <button type="button" class="step-btn active" onclick="goToStep(1)" id="step1-btn">
                    <i class="fas fa-user"></i> Paso 1: Datos
                </button>
                <button type="button" class="step-btn" onclick="goToStep(2)" id="step2-btn" disabled>
                    <i class="fas fa-dumbbell"></i> Paso 2: Membresía
                </button>
                <button type="button" class="step-btn" onclick="goToStep(3)" id="step3-btn" disabled>
                    <i class="fas fa-credit-card"></i> Paso 3: Pago
                </button>
            </div>

            <form action="{{ route('admin.clientes.store') }}" method="POST" id="clienteForm" onsubmit="return handleFormSubmit(event)">
                @csrf
                <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">
                <input type="hidden" id="flujo_cliente" name="flujo_cliente" value="solo_cliente">

                <!-- PASO 1: DATOS DEL CLIENTE -->
                <div class="step-indicator active" id="step-1">
                    
                    <div class="form-section-title">
                        <i class="fas fa-id-card"></i> Identificación
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="run_pasaporte" class="form-label">RUT/Pasaporte</label>
                            <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                                   id="run_pasaporte" name="run_pasaporte" placeholder="Ej: 7.882.382-4" 
                                   value="{{ old('run_pasaporte') }}">
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

                    <div class="form-section-title">
                        <i class="fas fa-heart-pulse"></i> Contacto de Emergencia
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contacto_emergencia" class="form-label">Nombre del Contacto</label>
                            <input type="text" class="form-control @error('contacto_emergencia') is-invalid @enderror" 
                                   id="contacto_emergencia" name="contacto_emergencia" 
                                   value="{{ old('contacto_emergencia') }}">
                            @error('contacto_emergencia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_emergencia" class="form-label">Teléfono del Contacto</label>
                            <input type="tel" class="form-control @error('telefono_emergencia') is-invalid @enderror" 
                                   id="telefono_emergencia" name="telefono_emergencia" 
                                   value="{{ old('telefono_emergencia') }}">
                            @error('telefono_emergencia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-map-marker-alt"></i> Domicilio
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                   id="direccion" name="direccion" value="{{ old('direccion') }}">
                            @error('direccion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-sticky-note"></i> Observaciones
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="observaciones" class="form-label">Notas Adicionales</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <!-- PASO 2: MEMBRESÍA -->
                <div class="step-indicator" id="step-2">
                    
                    <div class="alert alert-info">
                        <strong><i class="fas fa-user"></i> Cliente:</strong> 
                        <span id="cliente-nombre">Ingrese datos en Paso 1</span>
                    </div>

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
                                        {{ $membresia->nombre }}
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
                                   id="fecha_inicio" name="fecha_inicio" 
                                   value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}"
                                   onchange="actualizarPrecio()">
                            @error('fecha_inicio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-handshake"></i> Convenio / Descuento
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_convenio" class="form-label">¿Tiene Convenio?</label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" name="id_convenio" onchange="actualizarPrecio()">
                                <option value="">-- Sin Convenio --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" {{ old('id_convenio') == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }} ({{ $convenio->descuento_porcentaje }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('id_convenio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_motivo_descuento" class="form-label">Motivo del Descuento</label>
                            <select class="form-control @error('id_motivo_descuento') is-invalid @enderror" 
                                    id="id_motivo_descuento" name="id_motivo_descuento">
                                <option value="">-- Sin Motivo --</option>
                                @php
                                    $motivosDescuento = \App\Models\MotivoDescuento::where('activo', true)->get();
                                @endphp
                                @foreach($motivosDescuento as $motivo)
                                    <option value="{{ $motivo->id }}" {{ old('id_motivo_descuento') == $motivo->id ? 'selected' : '' }}>
                                        {{ $motivo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_motivo_descuento')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="descuento_manual" class="form-label">Descuento Manual ($)</label>
                            <input type="number" class="form-control @error('descuento_manual') is-invalid @enderror" 
                                   id="descuento_manual" name="descuento_manual" 
                                   min="0" step="1" value="{{ old('descuento_manual', 0) }}"
                                   placeholder="0"
                                   onchange="actualizarPrecioFinal()"
                                   oninput="actualizarPrecioFinal()">
                            @error('descuento_manual')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="observaciones_inscripcion" class="form-label">Observaciones</label>
                            <input type="text" class="form-control" 
                                   id="observaciones_inscripcion" name="observaciones_inscripcion" 
                                   placeholder="Notas sobre la inscripción"
                                   value="{{ old('observaciones_inscripcion', '') }}">
                        </div>
                    </div>

                    <div class="precio-box" id="precioBox" style="display:none;">
                        <h5><i class="fas fa-tag"></i> Resumen de Precios</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div><strong>Precio Base:</strong> <span id="precio-normal" style="font-size: 1.1em; color: #667eea;">$0</span></div>
                                <div style="margin-top: 0.5rem;"><strong>Convenio:</strong> <span id="precio-convenio" style="color: #28a745;">$0</span></div>
                            </div>
                            <div class="col-md-6">
                                <div><strong>Descuento Manual:</strong> <span id="desc-manual-display" style="color: #dc3545;">-$0</span></div>
                            </div>
                        </div>
                        <hr>
                        <div style="font-size: 1.2em; text-align: center;">
                            <strong>Precio Final: <span id="precio-total" style="color: #667eea; font-size: 1.3em;">$0</span></strong>
                        </div>
                        <div style="margin-top: 1rem; text-align: center; color: #666; font-size: 0.9em;">
                            <strong>Fecha de Término:</strong> <span id="fecha-termino">-</span>
                        </div>
                    </div>

                </div>

                <!-- PASO 3: PAGO -->
                <div class="step-indicator" id="step-3">
                    
                    <div class="form-section-title">
                        <i class="fas fa-credit-card"></i> Información de Pago
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="monto_abonado" class="form-label">Monto Abonado <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                   id="monto_abonado" name="monto_abonado" min="500" step="1" 
                                   value="{{ old('monto_abonado', 500) }}" placeholder="500">
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

                        <button type="submit" id="btn-guardar-solo-cliente" class="btn btn-info" style="display:none;">
                            <i class="fas fa-user-check"></i> Guardar Cliente
                        </button>

                        <button type="submit" id="btn-guardar-con-membresia" class="btn btn-success" style="display:none;">
                            <i class="fas fa-layer-group"></i> Guardar con Membresía
                        </button>

                        <button type="submit" id="btn-guardar-completo" class="btn btn-success" style="display:none;">
                            <i class="fas fa-check-circle"></i> Guardar Todo
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Scripts adicionales aquí si se necesita
    </script>
@endpush
