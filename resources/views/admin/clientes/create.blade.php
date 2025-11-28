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
            font-size: 1rem;
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
            
            if (!membresiaSelect || !membresiaSelect.value) {
                const precioBox = document.getElementById('precioBox');
                if (precioBox) precioBox.style.display = 'none';
                return;
            }
            
            const membresia_id = membresiaSelect.value;
            const convenio_id = convenioSelect ? convenioSelect.value : '';
            
            fetch(`/admin/api/precio-membresia/${membresia_id}${convenio_id ? '?convenio=' + convenio_id : ''}`)
                .then(response => response.json())
                .then(data => {
                    if (!data) return;
                    
                    const precioNormal = data.precio_normal || 0;
                    const precioFinal = data.precio_final || precioNormal;
                    
                    const normalEl = document.getElementById('precio-normal');
                    const finalEl = document.getElementById('precio-final');
                    const precioBoxEl = document.getElementById('precioBox');
                    const montoSugeridoEl = document.getElementById('monto_sugerido');
                    const montoAbonado = document.getElementById('monto_abonado');
                    
                    if (normalEl) normalEl.textContent = '$' + precioNormal.toLocaleString('es-CL');
                    if (finalEl) finalEl.textContent = '$' + precioFinal.toLocaleString('es-CL');
                    if (montoSugeridoEl) montoSugeridoEl.textContent = `Sugerido: $${precioFinal.toLocaleString('es-CL')}`;
                    if (montoAbonado) montoAbonado.placeholder = `$${precioFinal.toLocaleString('es-CL')}`;
                    if (precioBoxEl) precioBoxEl.style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
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
            
            if (convenioSelect) {
                convenioSelect.addEventListener('change', actualizarPrecio);
            }
            if (membresiaSelect) {
                membresiaSelect.addEventListener('change', actualizarPrecio);
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
        <div class="alert alert-danger">
            <h4><i class="fas fa-exclamation-triangle"></i> Errores en el Formulario</h4>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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

                <!-- ============ PASO 1: DATOS DEL CLIENTE ============ -->
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
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                                   id="nombres" name="nombres" value="{{ old('nombres') }}" required>
                            @error('nombres')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
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
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="celular" class="form-label">Celular *</label>
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
                                   id="direccion" name="direccion" 
                                   value="{{ old('direccion') }}">
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

                <!-- ============ PASO 2: MEMBRESÍA ============ -->
                <div class="step-indicator" id="step-2">
                    
                    <div class="form-section-title">
                        <i class="fas fa-dumbbell"></i> Seleccionar Membresía
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_membresia" class="form-label">Membresía *</label>
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
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}">
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
                            @error('id_convenio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="precio-box" id="precioBox" style="display:none;">
                        <h5><i class="fas fa-tag"></i> Resumen de Precios</h5>
                        <div><strong>Precio Normal:</strong> <span id="precio-normal">$0</span></div>
                        <div><strong>Precio Final:</strong> <span id="precio-final">$0</span></div>
                    </div>

                </div>

                <!-- ============ PASO 3: PAGO ============ -->
                <div class="step-indicator" id="step-3">
                    
                    <div class="form-section-title">
                        <i class="fas fa-credit-card"></i> Información de Pago
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="monto_abonado" class="form-label">Monto Abonado *</label>
                            <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                   id="monto_abonado" name="monto_abonado" min="0.01" step="0.01" 
                                   value="{{ old('monto_abonado', 0) }}">
                            <small class="text-muted" id="monto_sugerido"></small>
                            @error('monto_abonado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_metodo_pago" class="form-label">Método de Pago *</label>
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
                            <label for="fecha_pago" class="form-label">Fecha de Pago *</label>
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
