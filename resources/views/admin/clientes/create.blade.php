@extends('adminlte::page')

@section('title', 'Crear Cliente - EstóicosGym')

@section('css')
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

        /* ===== VALIDATION ALERTS ===== */
        .validation-alert {
            display: none;
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            border: 0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { 
                opacity: 0; 
                transform: translateY(-20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .validation-alert.show {
            display: block;
        }

        .validation-alert h5 {
            margin-top: 0;
            margin-bottom: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .validation-alert ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }

        .validation-alert li {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .validation-alert li:last-child {
            margin-bottom: 0;
        }

        /* ===== BUTTONS LAYOUT ===== */
        .buttons-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: #f8f9fa;
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

        .btn-action {
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            border: 0;
            cursor: pointer;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-action:active {
            transform: translateY(0);
        }

        .btn-success-lg {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-success-lg:hover {
            color: white;
            filter: brightness(1.1);
        }

        .btn-primary-lg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary-lg:hover {
            color: white;
            filter: brightness(1.1);
        }

        .btn-secondary-outline {
            background: white;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }

        .btn-secondary-outline:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
            color: #495057;
        }

        .btn-danger-outline {
            background: white;
            color: #dc3545;
            border: 2px solid #dc3545;
        }

        .btn-danger-outline:hover {
            background: #fff5f7;
            color: #c82333;
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

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .form-control:hover {
            border-color: #adb5bd;
        }

        /* ===== CARD LAYOUT ===== */
        .card {
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
        }

        .card-header {
            border-bottom: 2px solid #667eea;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .card-body {
            padding: 2rem;
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
                justify-content: stretch;
            }

            .btn-action {
                flex: 1;
                justify-content: center;
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
    <div id="errorAlert" class="validation-alert">
        <h5>
            <i class="fas fa-exclamation-triangle"></i> Errores encontrados
        </h5>
        <ul id="errorList"></ul>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-lg" role="alert" style="border-left: 5px solid #dc3545; background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); border-radius: 0.5rem; padding: 1.5rem;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #c62828;">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="d-flex align-items-start">
                <div class="mr-3" style="font-size: 1.8rem; flex-shrink: 0; color: #d32f2f;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div style="flex: 1;">
                    <h5 class="alert-heading" style="color: #c62828; margin-top: 0; margin-bottom: 1rem; font-weight: 700; font-size: 1.1rem;">
                        ⚠️ Errores en el Formulario
                    </h5>
                    <small class="d-block mb-3 text-dark" style="opacity: 0.8; font-size: 0.9rem;">
                        Por favor, corrige los siguientes errores antes de continuar:
                    </small>
                    <ul class="mb-0 pl-4" style="font-size: 0.95rem; color: #c62828; line-height: 1.8;">
                        @foreach ($errors->all() as $error)
                            <li class="mb-2">
                                <strong>{{ $error }}</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
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
                    <i class="fas fa-user"></i>Paso 1: Datos del Cliente
                </button>
                <button type="button" class="step-btn" onclick="goToStep(2)" id="step2-btn" disabled>
                    <i class="fas fa-dumbbell"></i>Paso 2: Membresía
                </button>
                <button type="button" class="step-btn" onclick="goToStep(3)" id="step3-btn" disabled>
                    <i class="fas fa-credit-card"></i>Paso 3: Pago
                </button>
            </div>

            <form action="{{ route('admin.clientes.store') }}" method="POST" id="clienteForm">
                @csrf
                <input type="hidden" id="action" name="action" value="save_cliente">

                <!-- ============ PASO 1: DATOS DEL CLIENTE ============ -->
                <div class="step-indicator active" id="step-1">
                    
                    <!-- Identificación -->
                    <div class="form-section-title">
                        <i class="fas fa-id-card"></i> Identificación
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="run_pasaporte" class="form-label">RUT/Pasaporte <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                                   id="run_pasaporte" name="run_pasaporte" placeholder="XX.XXX.XXX-X" 
                                   value="{{ old('run_pasaporte') }}" required>
                            <small class="form-text text-muted">Formato: XX.XXX.XXX-X</small>
                            @error('run_pasaporte')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Datos Personales -->
                    <h4 class="text-primary mb-3 mt-4">
                        <i class="fas fa-user"></i> Datos Personales
                    </h4>
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

                    <!-- Contacto -->
                    <h4 class="text-primary mb-3 mt-4">
                        <i class="fas fa-phone"></i> Contacto
                    </h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="celular" class="form-label">Celular <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('celular') is-invalid @enderror" 
                                   id="celular" name="celular" placeholder="+56912345678" value="{{ old('celular') }}" required>
                            @error('celular')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contacto de Emergencia -->
                    <h4 class="text-primary mb-3 mt-4">
                        <i class="fas fa-exclamation-triangle"></i> Contacto de Emergencia
                    </h4>
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

                    <!-- Dirección -->
                    <h4 class="text-primary mb-3 mt-4">
                        <i class="fas fa-map-marker-alt"></i> Domicilio
                    </h4>
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

                    <!-- Convenio -->
                    <h4 class="text-primary mb-3 mt-4">
                        <i class="fas fa-handshake"></i> Convenio
                    </h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_convenio" class="form-label">Convenio (Opcional)</label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" name="id_convenio">
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

                    <!-- Observaciones -->
                    <h4 class="text-primary mb-3 mt-4">
                        <i class="fas fa-sticky-note"></i> Observaciones
                    </h4>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="observaciones" class="form-label">Notas Adicionales</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="2" 
                                      placeholder="Información adicional...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <!-- ============ PASO 2: MEMBRESÍA ============ -->
                <div class="step-indicator" id="step-2">
                    
                    <h4 class="text-primary mb-3">
                        <i class="fas fa-dumbbell"></i> Seleccionar Membresía
                    </h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_membresia" class="form-label">Membresía <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                    id="id_membresia" name="id_membresia" onchange="actualizarPrecio()">
                                <option value="">-- Seleccionar Membresía --</option>
                                @foreach($membresias as $membresia)
                                    <option value="{{ $membresia->id }}" data-precio="{{ $membresia->precio }}" 
                                            {{ old('id_membresia') == $membresia->id ? 'selected' : '' }}>
                                        {{ $membresia->nombre }} ({{ $membresia->duracion_dias }} días - ${{ number_format($membresia->precio, 0, ',', '.') }})
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_convenio_inscripcion" class="form-label">Convenio para esta Inscripción (Opcional)</label>
                            <select class="form-control @error('id_convenio_inscripcion') is-invalid @enderror" 
                                    id="id_convenio_inscripcion" name="id_convenio_inscripcion" onchange="actualizarPrecio()">
                                <option value="">-- Sin Convenio Adicional --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" data-descuento="{{ $convenio->descuento_porcentaje }}"
                                            {{ old('id_convenio_inscripcion') == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }} ({{ $convenio->descuento_porcentaje }}% desc.)
                                    </option>
                                @endforeach
                            </select>
                            @error('id_convenio_inscripcion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="descuento_aplicado" class="form-label">Descuento Manual ($)</label>
                            <input type="number" class="form-control @error('descuento_aplicado') is-invalid @enderror" 
                                   id="descuento_aplicado" name="descuento_aplicado" min="0" step="0.01" 
                                   value="{{ old('descuento_aplicado', 0) }}" onchange="actualizarPrecio()">
                            @error('descuento_aplicado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3">
                        <h5>Resumen de Precio:</h5>
                        <p class="mb-1">Precio Base: <strong id="precio-base">$0</strong></p>
                        <p class="mb-1">Descuento: <strong id="precio-descuento">-$0</strong></p>
                        <p class="mb-0">Precio Final: <strong id="precio-final" class="text-success" style="font-size: 1.1em;">$0</strong></p>
                    </div>

                </div>

                <!-- ============ PASO 3: PAGO ============ -->
                <div class="step-indicator" id="step-3">
                    
                    <h4 class="text-primary mb-3">
                        <i class="fas fa-credit-card"></i> Información de Pago
                    </h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="monto_abonado" class="form-label">Monto Abonado <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                   id="monto_abonado" name="monto_abonado" min="0.01" step="0.01" 
                                   value="{{ old('monto_abonado', 0) }}">
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
                        <div class="col-md-6 mb-3">
                            <label for="cantidad_cuotas" class="form-label">Cantidad de Cuotas</label>
                            <input type="number" class="form-control @error('cantidad_cuotas') is-invalid @enderror" 
                                   id="cantidad_cuotas" name="cantidad_cuotas" min="1" max="12" 
                                   value="{{ old('cantidad_cuotas', 1) }}">
                            <small class="text-muted">Dejar en 1 para pago completo</small>
                            @error('cantidad_cuotas')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <hr class="my-4">

                <!-- Navegación entre pasos -->
                <!-- Paso 1: Botones Guardar o Continuar -->
                <div id="buttons-step-1" class="buttons-container" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #dee2e6; padding: 1.75rem; border-radius: 0.75rem; margin-top: 2rem; justify-content: space-between; align-items: center;">
                    <div style="flex: 1; min-width: 150px;">
                        <a href="{{ route('admin.clientes.index') }}" class="btn-action btn-outline-secondary" style="width: 100%; justify-content: center; padding: 0.75rem 1.5rem; border: 2px solid #6c757d; color: #6c757d; background: white;">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div class="buttons-group" style="gap: 1rem; flex: 2; justify-content: flex-end;">
                        <button type="button" class="btn-action btn-success-lg" onclick="guardarSoloCliente()" title="Guardar cliente sin membresía" style="padding: 0.75rem 2rem; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 0; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);">
                            <i class="fas fa-save"></i> Guardar y Salir
                        </button>
                        <button type="button" class="btn-action btn-primary-lg" onclick="nextStep()" title="Ir al siguiente paso" style="padding: 0.75rem 2rem; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; border: 0; box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);">
                            Continuar <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Pasos 2 y 3: Botones de navegación -->
                <div id="buttons-other-steps" class="buttons-container" style="display:none; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #dee2e6; padding: 1.75rem; border-radius: 0.75rem; margin-top: 2rem; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn-action btn-secondary-outline" onclick="previousStep()" title="Volver al paso anterior" style="padding: 0.75rem 1.5rem; border: 2px solid #6c757d; color: #6c757d; background: white; min-width: 140px; justify-content: center;">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <div class="buttons-group" style="gap: 1rem; flex: 2; justify-content: flex-end;">
                        <a href="{{ route('admin.clientes.index') }}" class="btn-action btn-outline-secondary" style="padding: 0.75rem 1.5rem; border: 2px solid #6c757d; color: #6c757d; background: white;">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="button" id="btn-siguiente-final" class="btn-action btn-primary-lg" onclick="nextStep()" title="Ir al siguiente paso" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; border: 0; box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="submit" id="btn-guardar-final" class="btn-action btn-success-lg" style="display:none; padding: 0.75rem 2rem; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 0; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);" onclick="setActionAndSubmit('save_completo')" title="Crear cliente, membresía y pago">
                            <i class="fas fa-check-circle"></i> Guardar Todo
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function goToStep(step) {
            if (step < 1 || step > totalSteps) return;
            
            // Validar paso actual antes de avanzar
            if (step > currentStep && !validateStep(currentStep)) return;
            
            // Ocultar todos los pasos
            document.querySelectorAll('.step-indicator').forEach(el => {
                el.classList.remove('active');
            });
            
            // Mostrar paso actual
            document.getElementById(`step-${step}`).classList.add('active');
            currentStep = step;
            
            // Actualizar indicadores
            updateStepIndicators();
            
            // Actualizar botones
            const buttons1 = document.getElementById('buttons-step-1');
            const buttonsOther = document.getElementById('buttons-other-steps');
            
            if (step === 1) {
                buttons1.style.display = 'flex';
                buttonsOther.style.display = 'none';
            } else {
                buttons1.style.display = 'none';
                buttonsOther.style.display = 'flex';
                
                // Mostrar/ocultar botón Guardar en paso 3
                const btnSiguiente = document.getElementById('btn-siguiente-final');
                const btnGuardar = document.getElementById('btn-guardar-final');
                
                if (step === totalSteps) {
                    btnSiguiente.style.display = 'none';
                    btnGuardar.style.display = 'block';
                } else {
                    btnSiguiente.style.display = 'block';
                    btnGuardar.style.display = 'none';
                }
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

        function updateStepIndicators() {
            for (let i = 1; i <= totalSteps; i++) {
                const btn = document.getElementById(`step${i}-btn`);
                btn.classList.remove('active', 'completed');
                
                if (i < currentStep) {
                    btn.classList.add('completed');
                } else if (i === currentStep) {
                    btn.classList.add('active');
                }
            }
        }

        function validateStep(step) {
            let inputs = [];
            const fieldLabels = {
                'run_pasaporte': 'RUT/Pasaporte',
                'nombres': 'Nombres',
                'apellido_paterno': 'Apellido Paterno',
                'email': 'Email',
                'celular': 'Celular',
                'id_membresia': 'Membresía',
                'fecha_inicio': 'Fecha de Inicio',
                'monto_abonado': 'Monto Abonado',
                'id_metodo_pago': 'Método de Pago',
                'fecha_pago': 'Fecha de Pago'
            };
            
            if (step === 1) {
                inputs = [
                    'run_pasaporte', 'nombres', 'apellido_paterno', 'email', 'celular'
                ];
            } else if (step === 2) {
                inputs = ['id_membresia', 'fecha_inicio'];
            } else if (step === 3) {
                inputs = ['monto_abonado', 'id_metodo_pago', 'fecha_pago'];
            }
            
            let emptyFields = [];
            for (let input of inputs) {
                const el = document.getElementById(input);
                if (!el || !el.value) {
                    emptyFields.push(fieldLabels[input] || input);
                }
            }
            
            if (emptyFields.length > 0) {
                showValidationAlert(emptyFields);
                return false;
            }
            return true;
        }

        function showValidationAlert(fields) {
            const alert = document.getElementById('errorAlert');
            const list = document.getElementById('errorList');
            
            list.innerHTML = '';
            fields.forEach(field => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${field}</strong> es requerido`;
                list.appendChild(li);
            });
            
            alert.classList.add('show');
            
            // Auto-hide después de 5 segundos
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
            
            // Scroll al alert
            alert.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function actualizarPrecio() {
            const membresia = document.getElementById('id_membresia');
            const convenio = document.getElementById('id_convenio_inscripcion');
            const descuentoManual = parseFloat(document.getElementById('descuento_aplicado').value) || 0;
            
            if (!membresia.value) {
                document.getElementById('precio-base').textContent = '$0';
                document.getElementById('precio-descuento').textContent = '-$0';
                document.getElementById('precio-final').textContent = '$0';
                return;
            }
            
            const option = membresia.options[membresia.selectedIndex];
            let precioBase = parseFloat(option.dataset.precio) || 0;
            
            let descuento = descuentoManual;
            
            if (convenio.value) {
                const convenioOption = convenio.options[convenio.selectedIndex];
                const porcentaje = parseFloat(convenioOption.dataset.descuento) || 0;
                descuento += (precioBase * porcentaje / 100);
            }
            
            const precioFinal = precioBase - descuento;
            
            document.getElementById('precio-base').textContent = '$' + precioBase.toLocaleString('es-CL', {maximumFractionDigits: 0});
            document.getElementById('precio-descuento').textContent = '-$' + descuento.toLocaleString('es-CL', {maximumFractionDigits: 0});
            document.getElementById('precio-final').textContent = '$' + precioFinal.toLocaleString('es-CL', {maximumFractionDigits: 0});
            
            // Actualizar monto_abonado sugerido
            document.getElementById('monto_abonado').placeholder = '$' + precioFinal.toLocaleString('es-CL', {maximumFractionDigits: 0});
        }

        function guardarSoloCliente() {
            if (!validateStep(1)) return;
            document.getElementById('action').value = 'save_cliente';
            document.getElementById('clienteForm').submit();
        }

        function setActionAndSubmit(action) {
            if (!validateStep(3)) return;
            document.getElementById('action').value = action;
            document.getElementById('clienteForm').submit();
        }

        // Inicializar
        window.addEventListener('load', function() {
            goToStep(1);
        });
    </script>
@stop
