@extends('adminlte::page')

@section('title', 'Crear Cliente - EstóicosGym')

@section('content_header')
@stop

@section('content')
<div class="create-cliente-container">
    <!-- Hero Header -->
    <div class="create-hero">
        <a href="{{ route('admin.clientes.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="hero-text">
                <h1>Nuevo Cliente</h1>
                <p>Registra un nuevo cliente en el sistema</p>
            </div>
        </div>
    </div>

    <!-- Wizard Container con Stepper -->
    <div class="wizard-container">
        <div class="wizard-header">
            <h2><i class="fas fa-list-ol"></i> Proceso de Registro</h2>
            <p>Sigue los pasos para completar el registro del cliente</p>
        </div>
        <div class="steps-nav">
            <button type="button" class="step-btn active" data-step="1">
                <div class="step-number"><span>1</span></div>
                <i class="fas fa-user step-icon"></i>
                <span class="step-label">Datos Personales</span>
            </button>
            <button type="button" class="step-btn" data-step="2">
                <div class="step-number"><span>2</span></div>
                <i class="fas fa-id-card step-icon"></i>
                <span class="step-label">Membresía</span>
            </button>
            <button type="button" class="step-btn" data-step="3">
                <div class="step-number"><span>3</span></div>
                <i class="fas fa-credit-card step-icon"></i>
                <span class="step-label">Pago</span>
            </button>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <form action="{{ route('admin.clientes.store') }}" method="POST" id="clienteForm">
            @csrf
            <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">
            <input type="hidden" id="flujo_cliente" name="flujo_cliente" value="solo_cliente">
            <input type="hidden" id="precio_final_oculto" name="precio_final_oculto" value="0">

            <!-- ========== PASO 1: DATOS DEL CLIENTE ========== -->
            <div class="step-content active" id="step-1">
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-id-card"></i>
                        <h3>Identificación</h3>
                    </div>
                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="run_pasaporte">
                                    <i class="fas fa-fingerprint"></i> RUT/Pasaporte
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                                           id="run_pasaporte" name="run_pasaporte" 
                                           placeholder="Ingrese RUT"
                                           value="{{ old('run_pasaporte') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="rut-status" style="min-width: 45px;">
                                            <i class="fas fa-question-circle text-muted" id="rut-icon"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="form-hint" id="rut-hint">Campo opcional</small>
                                <div id="rut-feedback" class="mt-1" style="display: none;"></div>
                                @error('run_pasaporte')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fecha_nacimiento">
                                    <i class="fas fa-birthday-cake"></i> Fecha de Nacimiento
                                </label>
                                <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                       id="fecha_nacimiento" name="fecha_nacimiento"
                                       value="{{ old('fecha_nacimiento') }}"
                                       max="{{ now()->subYears(10)->format('Y-m-d') }}">
                                <small class="form-hint" id="edad-info">Edad mínima: 10 años</small>
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== SECCIÓN APODERADO (Solo para menores de edad) ===== -->
                <div class="section-card" id="seccion-apoderado" style="display: none;">
                    <div class="section-header apoderado-header">
                        <i class="fas fa-user-shield"></i>
                        <h3>Autorización de Apoderado/Tutor</h3>
                        <span class="badge badge-warning ml-2">
                            <i class="fas fa-exclamation-triangle"></i> Cliente menor de edad
                        </span>
                    </div>
                    <div class="section-body">
                        <!-- Alerta informativa -->
                        <div class="alert alert-warning alert-menor mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-child fa-2x mr-3"></i>
                                <div>
                                    <strong>⚠️ Este cliente es menor de 18 años</strong>
                                    <p class="mb-0 mt-1">Para inscribir a un menor de edad, se requiere la autorización de un apoderado o tutor legal. Complete los siguientes datos obligatorios.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox de consentimiento -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox checkbox-apoderado">
                                <input type="checkbox" class="custom-control-input @error('consentimiento_apoderado') is-invalid @enderror" 
                                       id="consentimiento_apoderado" name="consentimiento_apoderado" value="1"
                                       {{ old('consentimiento_apoderado') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="consentimiento_apoderado">
                                    <strong>Confirmo que el apoderado/tutor ha autorizado la inscripción del menor</strong>
                                </label>
                            </div>
                            @error('consentimiento_apoderado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campos del apoderado -->
                        <div id="campos-apoderado" class="mt-4">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="apoderado_nombre">
                                        <i class="fas fa-user"></i> Nombre Completo del Apoderado <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control campo-apoderado @error('apoderado_nombre') is-invalid @enderror" 
                                           id="apoderado_nombre" name="apoderado_nombre"
                                           placeholder="Ej: María González Pérez"
                                           value="{{ old('apoderado_nombre') }}">
                                    @error('apoderado_nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="apoderado_rut">
                                        <i class="fas fa-id-card"></i> RUT del Apoderado <span class="required">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control campo-apoderado @error('apoderado_rut') is-invalid @enderror" 
                                               id="apoderado_rut" name="apoderado_rut"
                                               placeholder="Ej: 12.345.678-9"
                                               value="{{ old('apoderado_rut') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="apoderado-rut-status">
                                                <i class="fas fa-question-circle text-muted" id="apoderado-rut-icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                    @error('apoderado_rut')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="apoderado_telefono">
                                        <i class="fas fa-phone"></i> Teléfono del Apoderado <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control campo-apoderado @error('apoderado_telefono') is-invalid @enderror" 
                                           id="apoderado_telefono" name="apoderado_telefono"
                                           placeholder="+56 9 1234 5678"
                                           value="{{ old('apoderado_telefono') }}">
                                    @error('apoderado_telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="apoderado_parentesco">
                                        <i class="fas fa-users"></i> Parentesco <span class="required">*</span>
                                    </label>
                                    <select class="form-control campo-apoderado @error('apoderado_parentesco') is-invalid @enderror" 
                                            id="apoderado_parentesco" name="apoderado_parentesco">
                                        <option value="">Seleccione parentesco...</option>
                                        <option value="Padre" {{ old('apoderado_parentesco') == 'Padre' ? 'selected' : '' }}>Padre</option>
                                        <option value="Madre" {{ old('apoderado_parentesco') == 'Madre' ? 'selected' : '' }}>Madre</option>
                                        <option value="Tutor Legal" {{ old('apoderado_parentesco') == 'Tutor Legal' ? 'selected' : '' }}>Tutor Legal</option>
                                        <option value="Abuelo/a" {{ old('apoderado_parentesco') == 'Abuelo/a' ? 'selected' : '' }}>Abuelo/a</option>
                                        <option value="Tío/a" {{ old('apoderado_parentesco') == 'Tío/a' ? 'selected' : '' }}>Tío/a</option>
                                        <option value="Hermano/a Mayor" {{ old('apoderado_parentesco') == 'Hermano/a Mayor' ? 'selected' : '' }}>Hermano/a Mayor</option>
                                        <option value="Otro" {{ old('apoderado_parentesco') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                    @error('apoderado_parentesco')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label for="apoderado_observaciones">
                                        <i class="fas fa-sticky-note"></i> Observaciones de la Autorización
                                    </label>
                                    <textarea class="form-control @error('apoderado_observaciones') is-invalid @enderror" 
                                              id="apoderado_observaciones" name="apoderado_observaciones"
                                              rows="2"
                                              placeholder="Ej: Autorización presentada en persona el día...">{{ old('apoderado_observaciones') }}</textarea>
                                    @error('apoderado_observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Campo oculto para es_menor_edad -->
                <input type="hidden" id="es_menor_edad" name="es_menor_edad" value="{{ old('es_menor_edad', '0') }}">

                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-user"></i>
                        <h3>Datos Personales</h3>
                    </div>
                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="nombres">
                                    <i class="fas fa-user-tag"></i> Nombres <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                                       id="nombres" name="nombres" required
                                       placeholder="Ej: Juan Pablo"
                                       value="{{ old('nombres') }}">
                                @error('nombres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="apellido_paterno">
                                    <i class="fas fa-user"></i> Apellido Paterno <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                                       id="apellido_paterno" name="apellido_paterno" required
                                       placeholder="Ej: González"
                                       value="{{ old('apellido_paterno') }}">
                                @error('apellido_paterno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="apellido_materno">
                                    <i class="fas fa-user"></i> Apellido Materno
                                </label>
                                <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror" 
                                       id="apellido_materno" name="apellido_materno"
                                       placeholder="Ej: Pérez"
                                       value="{{ old('apellido_materno') }}">
                                @error('apellido_materno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-address-book"></i>
                        <h3>Contacto</h3>
                    </div>
                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="celular">
                                    <i class="fas fa-mobile-alt"></i> Celular <span class="required">*</span>
                                </label>
                                <input type="tel" class="form-control @error('celular') is-invalid @enderror" 
                                       id="celular" name="celular" required
                                       placeholder="Ej: +56 9 1234 5678"
                                       value="{{ old('celular') }}">
                                @error('celular')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" required
                                       placeholder="Ej: cliente@email.com"
                                       value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12 direccion-container">
                                <label for="direccion">
                                    <i class="fas fa-map-marker-alt"></i> Dirección
                                </label>
                                <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                       id="direccion" name="direccion"
                                       placeholder="Escribe una dirección... Ej: Colón 500, Los Ángeles"
                                       value="{{ old('direccion') }}"
                                       autocomplete="off">
                                <div id="direccion-dropdown"></div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Busca direcciones en Chile o escribe manualmente
                                </small>
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-ambulance"></i>
                        <h3>Contacto de Emergencia</h3>
                    </div>
                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="contacto_emergencia">
                                    <i class="fas fa-user-shield"></i> Nombre Contacto
                                </label>
                                <input type="text" class="form-control @error('contacto_emergencia') is-invalid @enderror" 
                                       id="contacto_emergencia" name="contacto_emergencia"
                                       placeholder="Ej: María González (Madre)"
                                       value="{{ old('contacto_emergencia') }}">
                                @error('contacto_emergencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="telefono_emergencia">
                                    <i class="fas fa-phone-alt"></i> Teléfono Emergencia
                                </label>
                                <input type="tel" class="form-control @error('telefono_emergencia') is-invalid @enderror" 
                                       id="telefono_emergencia" name="telefono_emergencia"
                                       placeholder="Ej: +56 9 8765 4321"
                                       value="{{ old('telefono_emergencia') }}">
                                @error('telefono_emergencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="observaciones">
                                    <i class="fas fa-sticky-note"></i> Observaciones
                                </label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" name="observaciones" rows="3"
                                          placeholder="Ej: Condiciones médicas, preferencias, etc.">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== PASO 2: MEMBRESÍA ========== -->
            <div class="step-content" id="step-2">
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-handshake"></i>
                        <h3>Convenio (Opcional)</h3>
                    </div>
                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="id_convenio">
                                    <i class="fas fa-building"></i> Seleccionar Convenio
                                </label>
                                <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                        id="id_convenio" name="id_convenio">
                                    <option value="">Sin convenio</option>
                                    @foreach($convenios as $convenio)
                                        <option value="{{ $convenio->id }}" {{ old('id_convenio') == $convenio->id ? 'selected' : '' }}>
                                            {{ $convenio->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-hint">Si el cliente pertenece a una empresa con convenio</small>
                                @error('id_convenio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-dumbbell"></i>
                        <h3>Plan de Membresía</h3>
                    </div>
                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="id_membresia">
                                    <i class="fas fa-award"></i> Tipo de Membresía <span class="required">*</span>
                                </label>
                                <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                        id="id_membresia" name="id_membresia">
                                    <option value="">Seleccionar membresía...</option>
                                    @foreach($membresias as $membresia)
                                        @php
                                            $precioActual = $membresia->precios->first();
                                            $precioNormal = $precioActual ? $precioActual->precio_normal : 0;
                                            $precioConvenio = $precioActual ? ($precioActual->precio_convenio ?? $precioActual->precio_normal) : 0;
                                        @endphp
                                        <option value="{{ $membresia->id }}" 
                                                data-duracion="{{ $membresia->duracion_dias }}"
                                                data-precio-normal="{{ $precioNormal }}"
                                                data-precio-convenio="{{ $precioConvenio }}"
                                                {{ old('id_membresia') == $membresia->id ? 'selected' : '' }}>
                                            {{ $membresia->nombre }} ({{ $membresia->duracion_dias }} días) - ${{ number_format($precioNormal, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_membresia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="fecha_inicio">
                                    <i class="fas fa-calendar-alt"></i> Fecha de Inicio <span class="required">*</span>
                                </label>
                                <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                       id="fecha_inicio" name="fecha_inicio"
                                       value="{{ old('fecha_inicio', date('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}">
                                <small class="form-hint">Debe ser hoy o fecha futura</small>
                                @error('fecha_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="fecha_termino">
                                    <i class="fas fa-calendar-check"></i> Fecha de Término
                                </label>
                                <input type="date" class="form-control" 
                                       id="fecha_termino" name="fecha_termino"
                                       readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                <small class="form-hint">Se calcula automáticamente</small>
                            </div>
                        </div>

                        <!-- Precio Box -->
                        <div class="precio-display" id="precioDisplay" style="display: none;">
                            <div class="precio-header">
                                <i class="fas fa-tag"></i>
                                <span>Resumen de Precio</span>
                            </div>
                            <div class="precio-body">
                                <div class="precio-row">
                                    <span class="precio-label">Precio Normal:</span>
                                    <span class="precio-value" id="precioNormal">$0</span>
                                </div>
                                <div class="precio-row descuento" id="descuentoConvenioRow" style="display: none;">
                                    <span class="precio-label">Descuento Convenio:</span>
                                    <span class="precio-value text-success" id="descuentoConvenio">-$0</span>
                                </div>
                                <div class="precio-row descuento" id="descuentoManualRow" style="display: none;">
                                    <span class="precio-label">Descuento Manual:</span>
                                    <span class="precio-value text-success" id="descuentoManualDisplay">-$0</span>
                                </div>
                                <div class="precio-row total">
                                    <span class="precio-label">Precio Final:</span>
                                    <span class="precio-value" id="precioFinal">$0</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row mt-3">
                            <div class="form-group col-md-6">
                                <label for="id_motivo_descuento">
                                    <i class="fas fa-percent"></i> Motivo de Descuento
                                </label>
                                <select class="form-control @error('id_motivo_descuento') is-invalid @enderror" 
                                        id="id_motivo_descuento" name="id_motivo_descuento">
                                    <option value="">Sin descuento adicional</option>
                                    @foreach($motivos_descuento as $motivo)
                                        <option value="{{ $motivo->id }}" {{ old('id_motivo_descuento') == $motivo->id ? 'selected' : '' }}>
                                            {{ $motivo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_motivo_descuento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="descuento_manual">
                                    <i class="fas fa-dollar-sign"></i> Descuento Manual ($)
                                </label>
                                <input type="number" class="form-control" 
                                       id="descuento_manual" name="descuento_manual"
                                       min="0" step="100" value="{{ old('descuento_manual', 0) }}"
                                       placeholder="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="observaciones_inscripcion">
                                    <i class="fas fa-comment-alt"></i> Observaciones de Inscripción
                                </label>
                                <textarea class="form-control" id="observaciones_inscripcion" 
                                          name="observaciones_inscripcion" rows="2"
                                          placeholder="Notas adicionales sobre la inscripción...">{{ old('observaciones_inscripcion') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== PASO 3: PAGO ========== -->
            <div class="step-content" id="step-3">
                <!-- Ficha Resumen del Cliente y Membresía -->
                <div class="resumen-ficha">
                    <div class="ficha-header">
                        <i class="fas fa-file-invoice"></i>
                        <h3>Resumen de Inscripción</h3>
                    </div>
                    <div class="ficha-body">
                        <div class="ficha-row">
                            <div class="ficha-item">
                                <span class="ficha-label"><i class="fas fa-user"></i> Cliente</span>
                                <span class="ficha-value" id="fichaCliente">-</span>
                            </div>
                            <div class="ficha-item">
                                <span class="ficha-label"><i class="fas fa-id-card"></i> RUT</span>
                                <span class="ficha-value" id="fichaRut">-</span>
                            </div>
                        </div>
                        <div class="ficha-row">
                            <div class="ficha-item">
                                <span class="ficha-label"><i class="fas fa-dumbbell"></i> Membresía</span>
                                <span class="ficha-value" id="fichaMembresia">-</span>
                            </div>
                            <div class="ficha-item">
                                <span class="ficha-label"><i class="fas fa-clock"></i> Duración</span>
                                <span class="ficha-value" id="fichaDuracion">-</span>
                            </div>
                        </div>
                        <div class="ficha-row">
                            <div class="ficha-item">
                                <span class="ficha-label"><i class="fas fa-calendar-alt"></i> Fecha Inicio</span>
                                <span class="ficha-value" id="fichaFechaInicio">-</span>
                            </div>
                            <div class="ficha-item">
                                <span class="ficha-label"><i class="fas fa-calendar-check"></i> Fecha Término</span>
                                <span class="ficha-value ficha-fecha-termino" id="fichaFechaTermino">-</span>
                            </div>
                        </div>
                        <div class="ficha-row" id="fichaConvenioRow" style="display: none;">
                            <div class="ficha-item full">
                                <span class="ficha-label"><i class="fas fa-building"></i> Convenio</span>
                                <span class="ficha-value ficha-convenio" id="fichaConvenio">-</span>
                            </div>
                        </div>
                        <div class="ficha-divider"></div>
                        <div class="ficha-row">
                            <div class="ficha-item">
                                <span class="ficha-label">Precio Normal</span>
                                <span class="ficha-value" id="fichaPrecioNormal">$0</span>
                            </div>
                            <div class="ficha-item" id="fichaDescuentoConvenioItem" style="display: none;">
                                <span class="ficha-label">Desc. Convenio</span>
                                <span class="ficha-value ficha-descuento" id="fichaDescuentoConvenio">-$0</span>
                            </div>
                        </div>
                        <div class="ficha-row" id="fichaDescuentoManualRow" style="display: none;">
                            <div class="ficha-item">
                                <span class="ficha-label">Desc. Manual</span>
                                <span class="ficha-value ficha-descuento" id="fichaDescuentoManual">-$0</span>
                            </div>
                        </div>
                        <div class="ficha-total">
                            <span class="ficha-total-label">TOTAL A PAGAR</span>
                            <span class="ficha-total-value" id="montoAPagar">$0</span>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-credit-card"></i>
                        <h3>Forma de Pago</h3>
                    </div>
                    <div class="section-body">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="tipo_pago">
                                    <i class="fas fa-money-check-alt"></i> Tipo de Pago <span class="required">*</span>
                                </label>
                                <select class="form-control @error('tipo_pago') is-invalid @enderror" 
                                        id="tipo_pago" name="tipo_pago" required>
                                    <option value="completo" {{ old('tipo_pago', 'completo') == 'completo' ? 'selected' : '' }}>Pago Completo</option>
                                    <option value="parcial" {{ old('tipo_pago') == 'parcial' ? 'selected' : '' }}>Abono Parcial</option>
                                    <option value="pendiente" {{ old('tipo_pago') == 'pendiente' ? 'selected' : '' }}>Dejar Pendiente</option>
                                    <option value="mixto" {{ old('tipo_pago') == 'mixto' ? 'selected' : '' }}>Pago Mixto</option>
                                </select>
                                @error('tipo_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fecha_pago">
                                    <i class="fas fa-calendar-check"></i> Fecha de Pago <span class="required">*</span>
                                </label>
                                <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                       id="fecha_pago" name="fecha_pago" required
                                       value="{{ old('fecha_pago', date('Y-m-d')) }}">
                                @error('fecha_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Método de pago principal -->
                        <div class="form-row" id="metodoPagoRow">
                            <div class="form-group col-md-6">
                                <label for="id_metodo_pago">
                                    <i class="fas fa-wallet"></i> Método de Pago <span class="required">*</span>
                                </label>
                                <select class="form-control select-styled @error('id_metodo_pago') is-invalid @enderror" 
                                        id="id_metodo_pago" name="id_metodo_pago">
                                    <option value="">Seleccionar método...</option>
                                    @foreach($metodos_pago as $metodo)
                                        <option value="{{ $metodo->id }}" {{ old('id_metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                            {{ $metodo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_metodo_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Campos para pago parcial -->
                        <div class="form-row" id="montoAbonadoRow" style="display: none;">
                            <div class="form-group col-md-6">
                                <label for="monto_abonado">
                                    <i class="fas fa-coins"></i> Monto a Abonar <span class="required">*</span>
                                </label>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado"
                                       min="0" step="100" placeholder="Ingrese el monto"
                                       value="{{ old('monto_abonado') }}">
                                <small class="form-hint" id="montoPendienteHint"></small>
                                @error('monto_abonado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <div class="monto-pendiente-box" id="montoPendienteBox">
                                    <span class="monto-pendiente-label">Quedará Pendiente:</span>
                                    <span class="monto-pendiente-value" id="montoPendienteDisplay">$0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Campos para pago MIXTO - Dos métodos de pago -->
                        <div id="pagoMixtoSection" style="display: none;">
                            <div class="mixto-header">
                                <i class="fas fa-random"></i>
                                <span>Distribución de Pago Mixto</span>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="metodo_mixto_1">
                                        <i class="fas fa-wallet"></i> Primer Método <span class="required">*</span>
                                    </label>
                                    <select class="form-control select-styled" id="metodo_mixto_1" name="metodo_mixto_1">
                                        <option value="">Seleccionar método...</option>
                                        @foreach($metodos_pago as $metodo)
                                            <option value="{{ $metodo->id }}" {{ old('metodo_mixto_1') == $metodo->id ? 'selected' : '' }}>{{ $metodo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="monto_mixto_1">
                                        <i class="fas fa-coins"></i> Monto Primer Pago <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control" 
                                           id="monto_mixto_1" name="monto_mixto_1"
                                           min="0" step="100" placeholder="$0"
                                           value="{{ old('monto_mixto_1') }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="metodo_mixto_2">
                                        <i class="fas fa-wallet"></i> Segundo Método <span class="required">*</span>
                                    </label>
                                    <select class="form-control select-styled" id="metodo_mixto_2" name="metodo_mixto_2">
                                        <option value="">Seleccionar método...</option>
                                        @foreach($metodos_pago as $metodo)
                                            <option value="{{ $metodo->id }}" {{ old('metodo_mixto_2') == $metodo->id ? 'selected' : '' }}>{{ $metodo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="monto_mixto_2">
                                        <i class="fas fa-coins"></i> Monto Segundo Pago <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control" 
                                           id="monto_mixto_2" name="monto_mixto_2"
                                           min="0" step="100" placeholder="$0"
                                           value="{{ old('monto_mixto_2') }}">
                                </div>
                            </div>
                            <div class="mixto-total-box">
                                <div class="mixto-total-row">
                                    <span>Total a pagar:</span>
                                    <span id="mixtoTotalAPagar">$0</span>
                                </div>
                                <div class="mixto-total-row">
                                    <span>Suma de pagos:</span>
                                    <span id="mixtoSumaPagos">$0</span>
                                </div>
                                <div class="mixto-total-row diferencia" id="mixtoDiferenciaRow" style="display: none;">
                                    <span>Diferencia:</span>
                                    <span id="mixtoDiferencia">$0</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="referencia_pago">
                                    <i class="fas fa-receipt"></i> Referencia/Comprobante
                                </label>
                                <input type="text" class="form-control" 
                                       id="referencia_pago" name="referencia_pago"
                                       placeholder="Ej: N° transferencia, voucher, etc."
                                       value="{{ old('referencia_pago') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="observaciones_pago">
                                    <i class="fas fa-comment"></i> Observaciones del Pago
                                </label>
                                <input type="text" class="form-control" 
                                       id="observaciones_pago" name="observaciones_pago"
                                       placeholder="Notas adicionales..."
                                       value="{{ old('observaciones_pago') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="wizard-navigation">
                <div class="nav-left">
                    <button type="button" class="btn-nav btn-prev" id="btnPrev" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                </div>
                <div class="nav-center">
                    <button type="button" class="btn-save-only" id="btnSoloCliente">
                        <i class="fas fa-user-check"></i> Guardar Solo Cliente
                    </button>
                </div>
                <div class="nav-right">
                    <button type="button" class="btn-nav btn-next" id="btnNext">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn-nav btn-submit" id="btnSubmit" style="display: none;">
                        <i class="fas fa-check"></i> Registrar Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --danger: #dc3545;
        --text-primary: #2c3e50;
        --text-secondary: #6c757d;
        --bg-light: #f8f9fa;
        --border-color: #e9ecef;
        --shadow: 0 4px 20px rgba(0,0,0,0.08);
        --shadow-hover: 0 8px 30px rgba(0,0,0,0.12);
    }

    .content-wrapper {
        background: var(--bg-light) !important;
    }

    .create-cliente-container {
        padding: 20px;
        width: 100%;
    }

    /* Hero Header */
    .create-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 20px;
        padding: 30px 40px;
        margin-bottom: 30px;
        box-shadow: var(--shadow);
        position: relative;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .btn-back {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: rgba(255,255,255,0.25);
        color: #fff;
        transform: translateX(-3px);
    }

    .hero-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .hero-icon {
        width: 70px;
        height: 70px;
        background: rgba(255,255,255,0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-icon i {
        font-size: 32px;
        color: #fff;
    }

    .hero-text h1 {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .hero-text p {
        color: rgba(255,255,255,0.8);
        margin: 5px 0 0;
        font-size: 15px;
    }

    /* Wizard Container */
    .wizard-container {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 6px 25px rgba(0,0,0,0.15);
    }

    .wizard-header {
        text-align: center;
        margin-bottom: 1rem;
    }

    .wizard-header h2 {
        color: white;
        font-weight: 800;
        font-size: 1.1rem;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .wizard-header h2 i {
        color: var(--accent);
        font-size: 1.25rem;
    }

    .wizard-header p {
        color: rgba(255,255,255,0.7);
        margin: 0.25rem 0 0 0;
        font-size: 0.8rem;
    }

    .steps-nav { 
        display: flex; 
        gap: 0.75rem;
        position: relative;
        padding: 0;
        background: transparent;
    }
    
    .step-btn {
        flex: 1;
        padding: 0.6rem 0.5rem;
        text-align: center;
        border-radius: 12px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255,255,255,0.2);
        cursor: pointer;
        font-weight: 700;
        font-size: 0.75rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: rgba(255,255,255,0.5);
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
    }

    .step-btn .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 800;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .step-btn .step-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .step-btn .step-icon {
        font-size: 0.95rem;
    }
    
    .step-btn:hover:not(:disabled) {
        transform: translateY(-3px);
        background: rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.4);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        color: rgba(255,255,255,0.9);
    }
    
    /* PASO ACTIVO - Color accent destacado */
    .step-btn.active {
        background: white !important;
        color: var(--primary) !important;
        border-color: var(--accent) !important;
        box-shadow: 0 8px 30px rgba(233, 69, 96, 0.5);
        transform: translateY(-2px);
        opacity: 1 !important;
    }

    .step-btn.active .step-number {
        background: linear-gradient(135deg, var(--accent) 0%, #ff6b6b 100%);
        color: white;
        border-color: var(--accent);
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.5);
    }

    .step-btn.active .step-icon {
        color: var(--accent);
    }

    /* PASO COMPLETADO - Color success */
    .step-btn.completed {
        background: rgba(0, 191, 142, 0.2) !important;
        color: white !important;
        border-color: var(--success) !important;
        opacity: 1 !important;
    }

    .step-btn.completed .step-number {
        background: var(--success);
        color: white;
        border-color: var(--success);
    }

    .step-btn.completed .step-number::after {
        content: '✓';
        font-weight: 900;
        font-size: 1.2rem;
    }

    .step-btn.completed .step-number span {
        display: none;
    }

    /* Form Container - Card Principal */
    .form-container {
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: none;
    }

    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Section Cards - Diseño compacto */
    .section-card {
        background: #fff;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 1rem;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .section-card:hover {
        border-color: rgba(233, 69, 96, 0.3);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .section-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 0.65rem 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
    }

    .section-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--accent), transparent);
    }

    .section-header i {
        font-size: 1rem;
        color: #fff;
        width: 28px;
        height: 28px;
        background: rgba(255,255,255,0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .section-header h3 {
        color: #fff;
        font-size: 0.85rem;
        font-weight: 700;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .section-body {
        padding: 1rem;
        background: linear-gradient(135deg, rgba(248,249,250,0.5) 0%, white 100%);
    }

    /* Form Elements */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -8px;
    }

    .form-group {
        padding: 0 8px;
        margin-bottom: 12px;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 6px;
        font-size: 13px;
    }

    .form-group label i {
        color: var(--primary);
        font-size: 12px;
    }

    .required {
        color: var(--accent);
        font-weight: 700;
    }

    .form-control {
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 0.6rem 0.85rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background-color: var(--bg-light);
        color: var(--text-primary);
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(233, 69, 96, 0.1);
        outline: none;
        background-color: #fff;
    }

    .form-control.is-invalid {
        border-color: var(--danger);
        background-color: rgba(220, 53, 69, 0.05);
    }

    .form-hint {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .form-hint::before {
        content: '💡';
        font-size: 0.7rem;
    }

    .invalid-feedback {
        color: var(--danger);
        font-size: 0.8rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .invalid-feedback::before {
        content: '⚠️';
        font-size: 0.75rem;
    }

    /* ============================================
       PRECIO DISPLAY - MEJORADO
       ============================================ */
    .precio-display {
        background: linear-gradient(135deg, rgba(26,26,46,0.02) 0%, rgba(26,26,46,0.05) 100%);
        border: 2px solid var(--primary);
        border-radius: 16px;
        overflow: hidden;
        margin-top: 1.25rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        position: relative;
    }

    .precio-display::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
    }

    .precio-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: #fff;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
    }

    .precio-header i {
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .precio-body {
        padding: 1.25rem;
        background: linear-gradient(180deg, white 0%, rgba(248,249,250,0.5) 100%);
    }

    .precio-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px dashed var(--border-color);
    }

    .precio-row:last-child {
        border-bottom: none;
    }

    .precio-row.total {
        padding-top: 1rem;
        margin-top: 0.75rem;
        border-top: 2px dashed var(--success);
        border-bottom: none;
        background: rgba(0, 191, 142, 0.05);
        margin-left: -1.25rem;
        margin-right: -1.25rem;
        margin-bottom: -1.25rem;
        padding: 1rem 1.25rem;
        border-radius: 0 0 14px 14px;
    }

    .precio-label {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .precio-row.total .precio-label {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .precio-row.total .precio-label::before {
        content: '✨';
    }

    .precio-value {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1rem;
    }

    .precio-row.total .precio-value {
        font-size: 1.75rem;
        color: var(--success);
        font-weight: 800;
    }

    .text-success {
        color: var(--success) !important;
    }

    /* ============================================
       PAGO RESUMEN CARD - MEJORADO
       ============================================ */
    .pago-resumen {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(26, 26, 46, 0.3);
    }

    .pago-resumen::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -10%;
        width: 120px;
        height: 120px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    .pago-resumen::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -5%;
        width: 80px;
        height: 80px;
        background: rgba(233, 69, 96, 0.1);
        border-radius: 50%;
    }

    .resumen-label {
        color: rgba(255,255,255,0.85);
        font-size: 0.8rem;
        display: block;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        position: relative;
    }

    .resumen-value {
        color: #fff;
        font-size: 2.25rem;
        font-weight: 800;
        position: relative;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    /* Mixto Separator */
    .mixto-separator {
        text-align: center;
        margin: 24px 0;
        position: relative;
    }

    .mixto-separator::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--border-color);
    }

    .mixto-separator span {
        background: #fff;
        padding: 0 16px;
        position: relative;
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* ============================================
       WIZARD NAVIGATION - MEJORADO
       ============================================ */
    .wizard-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 16px;
        margin-top: 2rem;
        box-shadow: 0 10px 40px rgba(26, 26, 46, 0.3);
    }

    .nav-left, .nav-right {
        display: flex;
        gap: 0.75rem;
    }

    .nav-center {
        flex: 1;
        text-align: center;
    }

    .btn-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.75rem;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-nav:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    }

    .btn-nav:active {
        transform: translateY(-1px);
    }

    .btn-prev {
        background: rgba(255,255,255,0.15);
        color: #fff;
        border: 2px solid rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
    }

    .btn-prev:hover {
        background: rgba(255,255,255,0.25);
        border-color: rgba(255,255,255,0.5);
        transform: translateX(-5px);
    }

    .btn-prev i {
        transition: transform 0.3s ease;
    }

    .btn-prev:hover i {
        transform: translateX(-3px);
    }

    .btn-next {
        background: linear-gradient(135deg, var(--accent) 0%, #c73e55 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.4);
    }

    .btn-next:hover {
        box-shadow: 0 8px 25px rgba(233, 69, 96, 0.5);
        transform: translateX(5px);
    }

    .btn-next i {
        transition: transform 0.3s ease;
    }

    .btn-next:hover i {
        transform: translateX(3px);
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(0, 191, 142, 0.4);
        position: relative;
        overflow: hidden;
    }

    .btn-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-submit:hover::before {
        left: 100%;
    }

    .btn-submit:hover {
        box-shadow: 0 8px 30px rgba(0, 191, 142, 0.5);
        transform: translateY(-3px);
    }

    .btn-save-only {
        background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        border: none;
        color: #fff;
        padding: 0.85rem 1.75rem;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-save-only:hover {
        background: linear-gradient(135deg, #3a0ca3 0%, #4361ee 100%);
        color: #fff;
        box-shadow: 0 8px 25px rgba(67, 97, 238, 0.5);
        transform: translateY(-3px);
    }

    .btn-save-only:active {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .create-hero {
            flex-direction: column;
            text-align: center;
            padding: 24px;
        }

        .btn-back {
            position: absolute;
            top: 16px;
            left: 16px;
        }

        .hero-content {
            flex-direction: column;
        }

        .wizard-container {
            padding: 1.5rem;
        }

        .steps-nav {
            flex-direction: column;
            gap: 0.75rem;
        }

        .step-btn {
            flex-direction: row;
            justify-content: flex-start;
            padding: 1rem;
            gap: 1rem;
        }

        .step-btn .step-number {
            width: 36px;
            height: 36px;
        }

        .form-container {
            padding: 1.25rem;
        }

        .wizard-navigation {
            flex-direction: column;
            gap: 1rem;
            padding: 1.25rem;
        }

        .nav-left, .nav-center, .nav-right {
            width: 100%;
            justify-content: center;
        }

        .nav-right {
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-nav {
            flex: 1;
            justify-content: center;
            width: 100%;
        }

        .btn-save-only {
            width: 100%;
            justify-content: center;
        }
    }

    /* ============================================
       ESTILOS MEJORADOS PARA SELECTS
       ============================================ */
    .form-control.select-styled,
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%231a1a2e' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 20px;
        padding-right: 45px;
        cursor: pointer;
        min-height: 48px;
        font-size: 15px;
        line-height: 1.4;
        white-space: normal;
        word-wrap: break-word;
        color: #1a1a2e;
    }

    select.form-control option {
        padding: 12px 16px;
        font-size: 14px;
        line-height: 1.5;
        background-color: #fff;
        color: #1a1a2e;
    }

    select.form-control option:hover,
    select.form-control option:focus,
    select.form-control option:checked {
        background-color: rgba(26, 26, 46, 0.1);
        color: #1a1a2e;
    }

    select.form-control:disabled {
        background-color: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }

    /* ============================================
       FICHA RESUMEN DE INSCRIPCIÓN - COMPACTA
       ============================================ */
    .resumen-ficha {
        background: #fff;
        border: 2px solid var(--primary);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        position: relative;
    }

    .resumen-ficha::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--accent), var(--info), var(--success));
    }

    .ficha-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .ficha-header i {
        font-size: 1.1rem;
        color: #fff;
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ficha-header h3 {
        color: #fff;
        font-size: 0.9rem;
        font-weight: 700;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ficha-body {
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, rgba(248,249,250,0.5) 0%, white 100%);
    }

    .ficha-row {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .ficha-row:last-child {
        margin-bottom: 0;
    }

    .ficha-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        padding: 0.4rem 0.6rem;
        background: rgba(26, 26, 46, 0.02);
        border-radius: 8px;
        border: 1px solid rgba(26, 26, 46, 0.06);
        transition: all 0.3s ease;
    }

    .ficha-item:hover {
        background: rgba(233, 69, 96, 0.03);
        border-color: rgba(233, 69, 96, 0.1);
    }

    .ficha-item.full {
        flex: 2;
    }

    .ficha-label {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .ficha-label i {
        font-size: 0.6rem;
        color: var(--accent);
    }

    .ficha-value {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .ficha-value.ficha-convenio {
        color: var(--info);
        background: rgba(67, 97, 238, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        display: inline-block;
        width: fit-content;
        font-size: 0.9rem;
    }

    .ficha-value.ficha-descuento {
        color: var(--success);
    }

    .ficha-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--accent), transparent);
        margin: 0.5rem 0;
        opacity: 0.3;
    }

    .ficha-total {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        margin: 0.5rem -1rem -0.75rem -1rem;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .ficha-total::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .ficha-total-label {
        color: rgba(255,255,255,0.95);
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .ficha-total-label::before {
        content: '💰';
        font-size: 0.9rem;
    }

    .ficha-total-value {
        color: #fff;
        font-size: 1.4rem;
        font-weight: 800;
        text-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    /* ============================================
       MONTO PENDIENTE BOX - MEJORADO
       ============================================ */
    .monto-pendiente-box {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px solid var(--warning);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        margin-top: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .monto-pendiente-box::before {
        content: '⏳';
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2rem;
        opacity: 0.2;
    }

    .monto-pendiente-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .monto-pendiente-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #b45309;
    }

    /* ============================================
       TIPO PAGO BADGES
       ============================================ */
    .tipo-pago-info {
        background: rgba(67, 97, 238, 0.1);
        border: 2px solid var(--info);
        border-radius: 12px;
        padding: 0.875rem 1rem;
        margin-top: 0.75rem;
        font-size: 0.85rem;
        color: var(--info);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
    }

    .tipo-pago-info i {
        font-size: 1rem;
    }

    /* ============================================
       FECHA TÉRMINO EN FICHA
       ============================================ */
    .ficha-value.ficha-fecha-termino {
        color: var(--accent);
        font-weight: 700;
    }

    /* ============================================
       PAGO MIXTO STYLES - MEJORADO
       ============================================ */
    .mixto-header {
        background: linear-gradient(135deg, var(--info) 0%, #3730a3 100%);
        color: #fff;
        padding: 1rem 1.25rem;
        border-radius: 14px 14px 0 0;
        margin: 1.25rem 0 0 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .mixto-header i {
        font-size: 1.1rem;
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #pagoMixtoSection .form-row {
        background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
        padding: 1rem 1.25rem;
        margin: 0;
        border-left: 2px solid var(--info);
        border-right: 2px solid var(--info);
    }

    #pagoMixtoSection .form-row:first-of-type {
        padding-top: 1.25rem;
        border-top: none;
    }

    #pagoMixtoSection .form-group label {
        color: #1e1b4b;
        font-weight: 600;
    }

    #pagoMixtoSection .form-control {
        background-color: #fff;
        border: 2px solid #c7d2fe;
        color: #1e1b4b;
    }

    #pagoMixtoSection .form-control:focus {
        border-color: var(--info);
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
    }

    #pagoMixtoSection .form-control option {
        background-color: #fff;
        color: #1e1b4b;
    }

    .mixto-total-box {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        border: 2px solid var(--info);
        border-top: none;
        border-radius: 0 0 14px 14px;
        padding: 1rem 1.25rem;
    }

    .mixto-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.4rem 0;
        font-size: 0.9rem;
        color: #4338ca;
        font-weight: 500;
    }

    .mixto-total-row span:last-child {
        font-weight: 700;
        font-size: 1rem;
    }

    .mixto-total-row.diferencia {
        border-top: 2px dashed #818cf8;
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        color: var(--danger);
    }

    .mixto-total-row.diferencia.ok {
        color: var(--success);
    }

    /* ============================================
       RESPONSIVE STYLES
       ============================================ */
    @media (max-width: 768px) {
        .ficha-row {
            flex-direction: column;
            gap: 12px;
        }

        .ficha-item {
            flex: none;
        }

        .ficha-total {
            flex-direction: column;
            gap: 8px;
            text-align: center;
        }

        .ficha-total-value {
            font-size: 26px;
        }

        .monto-pendiente-box {
            margin-top: 16px;
        }

        .section-header h3 {
            font-size: 16px;
        }
    }

    /* ===== ESTILOS SECCIÓN APODERADO (Menores de edad) ===== */
    #seccion-apoderado {
        border: 2px solid var(--warning);
        animation: fadeInApoderado 0.4s ease-out;
    }

    @keyframes fadeInApoderado {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #seccion-apoderado .section-header {
        background: linear-gradient(135deg, #f0a500 0%, #e67e22 100%);
    }

    #seccion-apoderado .section-header h3 {
        color: #fff;
    }

    #seccion-apoderado .section-header i {
        color: #fff;
    }

    .apoderado-header .badge {
        background: rgba(255,255,255,0.2);
        color: #fff;
        font-size: 11px;
        padding: 5px 10px;
    }

    .alert-menor {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
        border: none;
        border-left: 4px solid var(--warning);
        border-radius: 10px;
        padding: 20px;
    }

    .alert-menor i {
        color: var(--warning);
    }

    .checkbox-apoderado {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        border: 2px dashed var(--border-color);
        transition: all 0.3s ease;
    }

    .checkbox-apoderado:hover {
        border-color: var(--warning);
    }

    .checkbox-apoderado .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--warning);
        border-color: var(--warning);
    }

    .checkbox-apoderado .custom-control-label {
        cursor: pointer;
        font-size: 15px;
    }

    .campo-apoderado {
        border-color: #ffc107;
    }

    .campo-apoderado:focus {
        border-color: #e67e22;
        box-shadow: 0 0 0 0.2rem rgba(240, 165, 0, 0.25);
    }

    #campos-apoderado {
        border-top: 1px dashed var(--border-color);
        padding-top: 20px;
    }

    /* Info de edad */
    #edad-info {
        font-weight: 500;
        transition: all 0.3s ease;
    }

    #edad-info.menor {
        color: var(--warning);
    }

    #edad-info.mayor {
        color: var(--success);
    }

    /* =====================================================
       AUTOCOMPLETE DIRECCIÓN - API Photon
    ===================================================== */
    .direccion-container {
        position: relative !important;
        overflow: visible !important;
    }

    #direccion-dropdown {
        position: fixed;
        background: #fff;
        border: 2px solid var(--primary);
        border-radius: 12px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 99999;
        display: none;
        box-shadow: 0 15px 40px rgba(0,0,0,0.25);
        width: auto;
        min-width: 300px;
    }

    #direccion-dropdown.visible {
        display: block;
    }

    .dir-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        transition: all 0.15s ease;
    }

    .dir-item:last-child {
        border-bottom: none;
    }

    .dir-item:hover,
    .dir-item.active {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    }

    .dir-item-main {
        font-weight: 600;
        color: var(--primary);
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dir-item-main i {
        color: var(--accent);
        font-size: 12px;
    }

    .dir-item-sub {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
        padding-left: 20px;
    }

    .dir-loading {
        padding: 20px;
        text-align: center;
        color: #666;
    }

    .dir-loading i {
        animation: dirSpin 1s linear infinite;
        margin-right: 8px;
        color: var(--accent);
    }

    @keyframes dirSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .dir-empty {
        padding: 16px;
        text-align: center;
        color: #666;
        font-size: 13px;
    }

    .dir-empty i {
        color: var(--success);
        margin-right: 5px;
    }

    #direccion:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26, 26, 46, 0.15);
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* SweetAlert2 Custom Theme - EstoicosGym */
    .swal2-popup.swal-estoicos {
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .swal2-popup.swal-estoicos .swal2-title {
        color: #1a1a2e;
        font-weight: 700;
        font-size: 1.5rem;
    }
    .swal2-popup.swal-estoicos .swal2-html-container {
        color: #64748b;
        font-size: 1rem;
    }
    .swal-estoicos .swal2-confirm {
        background: linear-gradient(135deg, #e94560 0%, #c73e55 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 28px !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.4) !important;
        transition: all 0.3s ease !important;
    }
    .swal-estoicos .swal2-cancel {
        background: #f1f5f9 !important;
        color: #64748b !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 28px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    .swal-estoicos.swal-success .swal2-confirm {
        background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%) !important;
        box-shadow: 0 4px 15px rgba(0, 191, 142, 0.4) !important;
    }
    .swal-estoicos.swal-warning .swal2-confirm {
        background: linear-gradient(135deg, #f0a500 0%, #d99400 100%) !important;
        box-shadow: 0 4px 15px rgba(240, 165, 0, 0.4) !important;
    }
    .swal-estoicos.swal-primary .swal2-confirm {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
        box-shadow: 0 4px 15px rgba(26, 26, 46, 0.4) !important;
    }
</style>
<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 3;
    let precioFinal = 0;
    let precioNormal = 0;
    let formSubmitting = false; // Flag para prevenir doble submit
    let esMenorEdad = false; // Flag para menores de edad

    // ============================================
    // AUTOTAB - Saltar al siguiente campo automáticamente
    // ============================================
    function setupAutoTab() {
        // Definir orden de campos para cada paso
        const camposOrden = [
            // Paso 1 - Datos del cliente
            'run_pasaporte',
            'fecha_nacimiento',
            'apoderado_nombre',
            'apoderado_rut',
            'apoderado_telefono',
            'apoderado_parentesco',
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'celular',
            'email',
            'direccion',
            'contacto_emergencia',
            'telefono_emergencia',
            'id_convenio',
            'observaciones'
        ];

        // Función para ir al siguiente campo
        function irAlSiguienteCampo(campoActual) {
            const indexActual = camposOrden.indexOf(campoActual);
            if (indexActual === -1) return;

            // Buscar siguiente campo visible y habilitado
            for (let i = indexActual + 1; i < camposOrden.length; i++) {
                const $siguiente = $('#' + camposOrden[i]);
                if ($siguiente.length && $siguiente.is(':visible') && !$siguiente.prop('disabled')) {
                    $siguiente.focus();
                    return;
                }
            }
        }

        // RUT - Saltar cuando tenga formato completo (XX.XXX.XXX-X)
        $('#run_pasaporte').on('input', function() {
            const valor = $(this).val();
            // Formato completo: 12.345.678-9 (12 caracteres)
            if (valor.length >= 11 && valor.includes('-')) {
                const partes = valor.split('-');
                if (partes.length === 2 && partes[1].length === 1) {
                    irAlSiguienteCampo('run_pasaporte');
                }
            }
        });

        // Fecha de nacimiento - Solo saltar con Enter (NO con change porque salta al perder foco)
        $('#fecha_nacimiento').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                setTimeout(() => {
                    if (esMenorEdad && $('#seccion-apoderado').is(':visible')) {
                        $('#apoderado_nombre').focus();
                    } else {
                        $('#nombres').focus();
                    }
                }, 50);
            }
        });

        // Campos de texto - Saltar con Enter
        const camposTexto = [
            'apoderado_nombre', 'apoderado_telefono',
            'nombres', 'apellido_paterno', 'apellido_materno',
            'celular', 'email', 'direccion',
            'contacto_emergencia', 'telefono_emergencia'
        ];

        camposTexto.forEach(campo => {
            $('#' + campo).on('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    irAlSiguienteCampo(campo);
                }
            });
        });

        // RUT del apoderado - Saltar cuando tenga formato completo
        $('#apoderado_rut').on('input', function() {
            const valor = $(this).val();
            if (valor.length >= 11 && valor.includes('-')) {
                const partes = valor.split('-');
                if (partes.length === 2 && partes[1].length === 1) {
                    irAlSiguienteCampo('apoderado_rut');
                }
            }
        });

        // Selects - Saltar al cambiar selección
        $('#apoderado_parentesco, #id_convenio').on('change', function() {
            const campo = $(this).attr('id');
            if ($(this).val()) {
                irAlSiguienteCampo(campo);
            }
        });

        // Teléfonos - Saltar cuando tengan 9 dígitos después del +56
        $('#celular, #telefono_emergencia, #apoderado_telefono').on('input', function() {
            const valor = $(this).val().replace(/\D/g, '');
            // +56 9 XXXX XXXX = 11 dígitos
            if (valor.length >= 11) {
                irAlSiguienteCampo($(this).attr('id'));
            }
        });
    }

    // Inicializar AutoTab
    setupAutoTab();

    // ============================================
    // DETECCIÓN DE EDAD Y MANEJO DE APODERADO
    // ============================================
    function calcularEdad(fechaNacimiento) {
        if (!fechaNacimiento) return null;
        
        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        
        return edad;
    }

    function verificarEdad() {
        const fechaNacimiento = $('#fecha_nacimiento').val();
        const $infoEdad = $('#edad-info');
        const $seccionApoderado = $('#seccion-apoderado');
        const $esMenorInput = $('#es_menor_edad');
        
        if (!fechaNacimiento) {
            $infoEdad.text('').removeClass('menor mayor');
            $seccionApoderado.slideUp(300);
            $esMenorInput.val('0');
            esMenorEdad = false;
            limpiarCamposApoderado();
            return;
        }
        
        const edad = calcularEdad(fechaNacimiento);
        
        if (edad === null || edad < 0) {
            $infoEdad.text('Fecha inválida').removeClass('menor mayor').addClass('text-danger');
            return;
        }
        
        if (edad < 18) {
            // MENOR DE EDAD
            esMenorEdad = true;
            $esMenorInput.val('1');
            $infoEdad.html(`<i class="fas fa-exclamation-triangle"></i> ${edad} años - <strong>Menor de edad</strong>`)
                     .removeClass('mayor').addClass('menor');
            $seccionApoderado.slideDown(400);
            
            // Hacer campos obligatorios
            $('.campo-apoderado').prop('required', true);
        } else {
            // MAYOR DE EDAD
            esMenorEdad = false;
            $esMenorInput.val('0');
            $infoEdad.html(`<i class="fas fa-check-circle"></i> ${edad} años`)
                     .removeClass('menor').addClass('mayor');
            $seccionApoderado.slideUp(300);
            
            // Quitar obligatoriedad y limpiar campos
            $('.campo-apoderado').prop('required', false);
            limpiarCamposApoderado();
        }
    }

    function limpiarCamposApoderado() {
        $('#consentimiento_apoderado').prop('checked', false);
        $('#apoderado_nombre').val('');
        $('#apoderado_rut').val('');
        $('#apoderado_telefono').val('');
        $('#apoderado_parentesco').val('');
        $('#apoderado_observaciones').val('');
    }

    // Evento al cambiar fecha de nacimiento
    $('#fecha_nacimiento').on('change', verificarEdad);
    
    // Verificar al cargar si hay valor previo (por old())
    if ($('#fecha_nacimiento').val()) {
        verificarEdad();
    }

    // Formatear RUT del apoderado
    $('#apoderado_rut').on('input', function() {
        let cursorPos = this.selectionStart;
        let valorAnterior = $(this).val();
        let valorFormateado = formatRut(valorAnterior);
        
        $(this).val(valorFormateado);
        
        let diff = valorFormateado.length - valorAnterior.length;
        this.setSelectionRange(cursorPos + diff, cursorPos + diff);
        
        // Validar RUT del apoderado
        if (valorFormateado.length >= 9) {
            let resultado = validarRutChileno(valorFormateado);
            actualizarUIValidacionRutApoderado(resultado);
        } else {
            actualizarUIValidacionRutApoderado({ valid: null });
        }
    });

    // Formatear teléfono del apoderado
    $('#apoderado_telefono').on('input', function() {
        let valorFormateado = formatTelefono($(this).val());
        $(this).val(valorFormateado);
    });

    $('#apoderado_telefono').on('focus', function() {
        if (!$(this).val() || $(this).val().trim() === '') {
            $(this).val('+56 9 ');
        }
    });

    function actualizarUIValidacionRutApoderado(resultado) {
        const $input = $('#apoderado_rut');
        const $icon = $('#apoderado-rut-icon');
        const $status = $('#apoderado-rut-status');
        
        $input.removeClass('is-valid is-invalid');
        $status.removeClass('bg-success bg-danger');
        
        if (resultado.valid === null) {
            $icon.attr('class', 'fas fa-question-circle text-muted');
        } else if (resultado.valid) {
            $input.addClass('is-valid');
            $icon.attr('class', 'fas fa-check-circle text-success');
            $status.addClass('bg-success').css('border-color', '#28a745');
        } else {
            $input.addClass('is-invalid');
            $icon.attr('class', 'fas fa-times-circle text-danger');
            $status.addClass('bg-danger').css('border-color', '#dc3545');
        }
    }

    // Validación antes de enviar formulario
    function validarApoderado() {
        if (!esMenorEdad) return true;
        
        let errores = [];
        
        if (!$('#consentimiento_apoderado').is(':checked')) {
            errores.push('Debe confirmar la autorización del apoderado');
        }
        if (!$('#apoderado_nombre').val().trim()) {
            errores.push('Nombre del apoderado es obligatorio');
        }
        if (!$('#apoderado_rut').val().trim()) {
            errores.push('RUT del apoderado es obligatorio');
        } else {
            let resultado = validarRutChileno($('#apoderado_rut').val());
            if (!resultado.valid) {
                errores.push('RUT del apoderado no es válido');
            }
        }
        if (!$('#apoderado_telefono').val().trim() || $('#apoderado_telefono').val().length < 10) {
            errores.push('Teléfono del apoderado es obligatorio');
        }
        if (!$('#apoderado_parentesco').val()) {
            errores.push('Debe seleccionar el parentesco');
        }
        
        if (errores.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Datos de Apoderado Incompletos',
                html: '<ul class="text-left">' + errores.map(e => `<li>${e}</li>`).join('') + '</ul>',
                customClass: { popup: 'swal-estoicos' }
            });
            return false;
        }
        
        return true;
    }

    // ============================================
    // FORMATEO AUTOMÁTICO DE RUT CHILENO
    // ============================================
    function formatRut(value) {
        // Eliminar todo excepto números y K/k
        let rut = value.replace(/[^0-9kK]/g, '').toUpperCase();
        
        if (rut.length === 0) return '';
        
        // Separar cuerpo y dígito verificador
        let dv = rut.slice(-1);
        let cuerpo = rut.slice(0, -1);
        
        if (cuerpo.length === 0) return rut;
        
        // Formatear con puntos
        let cuerpoFormateado = '';
        let contador = 0;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            cuerpoFormateado = cuerpo[i] + cuerpoFormateado;
            contador++;
            if (contador === 3 && i > 0) {
                cuerpoFormateado = '.' + cuerpoFormateado;
                contador = 0;
            }
        }
        
        return cuerpoFormateado + '-' + dv;
    }

    // ============================================
    // VALIDACIÓN DE DÍGITO VERIFICADOR RUT (Módulo 11)
    // Soporta dígitos: 0, 1-9, K
    // ============================================
    function validarRutChileno(rut) {
        // Limpiar RUT
        let rutLimpio = rut.replace(/[^0-9kK]/g, '').toUpperCase();
        
        // Si está vacío o muy corto, no validar aún
        if (rutLimpio.length < 8) {
            return { valid: null, message: 'Ingresa el RUT completo' };
        }
        
        // Separar cuerpo y dígito verificador
        let dv = rutLimpio.slice(-1);
        let cuerpo = rutLimpio.slice(0, -1);
        
        // El cuerpo debe ser solo números
        if (!/^\d+$/.test(cuerpo)) {
            return { valid: false, message: 'Formato de RUT inválido' };
        }
        
        // Validar máximo 9 dígitos totales (8 dígitos de cuerpo + 1 DV)
        if (cuerpo.length > 8) {
            return { valid: false, message: 'RUT inválido (máximo 8 dígitos + DV)' };
        }
        
        // Calcular dígito verificador con Módulo 11
        let suma = 0;
        let multiplicador = 2;
        
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo[i]) * multiplicador;
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }
        
        let resto = suma % 11;
        let dvCalculado = 11 - resto;
        
        // Convertir a caracter
        let dvEsperado;
        if (dvCalculado === 11) {
            dvEsperado = '0';  // ✅ Soporta dígito verificador 0
        } else if (dvCalculado === 10) {
            dvEsperado = 'K';  // ✅ Soporta dígito verificador K
        } else {
            dvEsperado = dvCalculado.toString();
        }
        
        // Comparar
        if (dv === dvEsperado) {
            return { valid: true, message: 'RUT válido ✓', dv: dvEsperado };
        } else {
            return { valid: false, message: 'RUT inválido', dv: dvEsperado };
        }
    }
    
    // Actualizar UI de validación de RUT
    function actualizarUIValidacionRut(resultado) {
        const $input = $('#run_pasaporte');
        const $icon = $('#rut-icon');
        const $status = $('#rut-status');
        const $hint = $('#rut-hint');
        const $feedback = $('#rut-feedback');
        
        // Limpiar clases previas
        $input.removeClass('is-valid is-invalid border-warning');
        $status.removeClass('bg-success bg-danger bg-warning');
        
        if (resultado.valid === null) {
            // Estado neutral - aún escribiendo
            $icon.attr('class', 'fas fa-question-circle text-muted');
            $hint.text('Campo opcional');
            $feedback.hide();
        } else if (resultado.valid) {
            // ✅ RUT válido - solo mostrar ícono verde, sin texto adicional
            $input.addClass('is-valid');
            $icon.attr('class', 'fas fa-check-circle text-success');
            $status.addClass('bg-success').css('border-color', '#28a745');
            $hint.text(''); // Sin mensaje
            $feedback.hide();
        } else {
            // ❌ RUT inválido
            $input.addClass('is-invalid');
            $icon.attr('class', 'fas fa-times-circle text-danger');
            $status.addClass('bg-danger').css('border-color', '#dc3545');
            $hint.html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + resultado.message + '</span>');
            $feedback.hide(); // No duplicar mensaje
        }
    }

    // Aplicar formateo al campo RUT
    $('#run_pasaporte').on('input', function() {
        let cursorPos = this.selectionStart;
        let valorAnterior = $(this).val();
        let valorFormateado = formatRut(valorAnterior);
        
        $(this).val(valorFormateado);
        
        // Ajustar posición del cursor
        let diff = valorFormateado.length - valorAnterior.length;
        this.setSelectionRange(cursorPos + diff, cursorPos + diff);
        
        // Validar RUT en tiempo real
        if (valorFormateado.length >= 9) { // Mínimo para validar: X.XXX.XXX-X
            let resultado = validarRutChileno(valorFormateado);
            actualizarUIValidacionRut(resultado);
        } else if (valorFormateado.length === 0) {
            // Campo vacío - estado neutral
            actualizarUIValidacionRut({ valid: null });
        } else {
            // Escribiendo - estado neutral
            actualizarUIValidacionRut({ valid: null, message: 'Ingresa el RUT completo' });
        }
    });
    
    // Validar también al perder foco
    $('#run_pasaporte').on('blur', function() {
        let valor = $(this).val().trim();
        if (valor.length > 0 && valor.length >= 9) {
            let resultado = validarRutChileno(valor);
            actualizarUIValidacionRut(resultado);
        }
    });

    // ============================================
    // FORMATEO AUTOMÁTICO DE TELÉFONO CHILENO
    // ============================================
    function formatTelefono(value) {
        // Eliminar todo excepto números
        let numeros = value.replace(/\D/g, '');
        
        // Si empieza con 56, quitarlo para procesar
        if (numeros.startsWith('56')) {
            numeros = numeros.substring(2);
        }
        
        // Si empieza con 9 y tiene 9 dígitos, es celular chileno
        if (numeros.startsWith('9')) {
            numeros = numeros.substring(0, 9); // Máximo 9 dígitos
            
            // Formatear: +56 9 XXXX XXXX
            if (numeros.length <= 1) {
                return '+56 ' + numeros;
            } else if (numeros.length <= 5) {
                return '+56 ' + numeros[0] + ' ' + numeros.substring(1);
            } else {
                return '+56 ' + numeros[0] + ' ' + numeros.substring(1, 5) + ' ' + numeros.substring(5);
            }
        }
        
        // Si no empieza con 9, agregar el 9
        if (numeros.length > 0 && !numeros.startsWith('9')) {
            numeros = '9' + numeros;
        }
        
        numeros = numeros.substring(0, 9); // Máximo 9 dígitos
        
        if (numeros.length === 0) return '+56 9 ';
        if (numeros.length <= 1) {
            return '+56 ' + numeros;
        } else if (numeros.length <= 5) {
            return '+56 ' + numeros[0] + ' ' + numeros.substring(1);
        } else {
            return '+56 ' + numeros[0] + ' ' + numeros.substring(1, 5) + ' ' + numeros.substring(5);
        }
    }

    // Aplicar formateo al campo celular
    $('#celular').on('input', function() {
        let valorFormateado = formatTelefono($(this).val());
        $(this).val(valorFormateado);
    });

    // Al hacer focus en celular, si está vacío, poner prefijo
    $('#celular').on('focus', function() {
        if (!$(this).val() || $(this).val().trim() === '') {
            $(this).val('+56 9 ');
        }
    });

    // Aplicar formateo al campo teléfono de emergencia
    $('#telefono_emergencia').on('input', function() {
        let valorFormateado = formatTelefono($(this).val());
        $(this).val(valorFormateado);
    });

    // Al hacer focus en teléfono emergencia, si está vacío, poner prefijo
    $('#telefono_emergencia').on('focus', function() {
        if (!$(this).val() || $(this).val().trim() === '') {
            $(this).val('+56 9 ');
        }
    });

    // ============================================
    // SANITIZACIÓN DE CAMPOS DE TEXTO
    // ============================================
    
    // Limpiar espacios dobles y capitalizar nombres al salir del campo
    ['nombres', 'apellido_paterno', 'apellido_materno', 'apoderado_nombre', 'contacto_emergencia'].forEach(function(campo) {
        const $input = $('#' + campo);
        if ($input.length) {
            $input.on('blur', function() {
                let valor = $(this).val();
                if (valor) {
                    // Eliminar espacios múltiples y trim
                    valor = valor.replace(/\s+/g, ' ').trim();
                    // Capitalizar cada palabra
                    valor = valor.toLowerCase().replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
                    $(this).val(valor);
                }
            });
            
            // Validar que solo contenga letras y espacios
            $input.on('input', function() {
                let valor = $(this).val();
                // Remover caracteres no permitidos (números, símbolos)
                valor = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
                $(this).val(valor);
            });
        }
    });

    // Validar formato de email
    $('#email').on('blur', function() {
        let valor = $(this).val();
        if (valor) {
            // Convertir a minúsculas y trim
            valor = valor.toLowerCase().trim();
            $(this).val(valor);
            
            // Validar formato básico
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(valor)) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.invalid-feedback').length) {
                    $(this).after('<div class="invalid-feedback">Ingrese un correo electrónico válido</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        }
    });

    // Validar edad al cambiar fecha (además de mostrar info)
    // Solo validar si la fecha está completa (año con 4 dígitos)
    $('#fecha_nacimiento').on('change', function() {
        const valor = this.value;
        
        // Verificar que la fecha esté completa (formato YYYY-MM-DD)
        if (!valor || valor.length < 10) {
            return; // No validar fechas incompletas
        }
        
        // Verificar que el año tenga 4 dígitos y sea razonable (> 1900)
        const año = parseInt(valor.split('-')[0]);
        if (año < 1900 || año > new Date().getFullYear()) {
            return; // No validar años incompletos o futuros
        }
        
        const fechaNac = new Date(valor);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNac.getFullYear();
        const m = hoy.getMonth() - fechaNac.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }
        
        if (edad < 14) {
            Swal.fire({
                icon: 'error',
                title: 'Edad no válida',
                text: 'El cliente debe tener al menos 14 años para registrarse.',
                confirmButtonColor: '#e94560'
            });
            this.value = '';
            $('#edad-info').text('').removeClass('menor mayor');
        } else if (edad > 110) {
            Swal.fire({
                icon: 'error',
                title: 'Fecha no válida',
                text: 'Verifique la fecha de nacimiento ingresada.',
                confirmButtonColor: '#e94560'
            });
            this.value = '';
            $('#edad-info').text('').removeClass('menor mayor');
        }
    });

    // ============================================
    // NAVEGACIÓN POR STEPPER (click en pasos)
    // ============================================
    $('.step-btn').on('click', function() {
        const stepNumber = parseInt($(this).data('step'));
        if (stepNumber && stepNumber !== currentStep) {
            // Solo permitir ir a pasos anteriores o al siguiente si el actual está validado
            if (stepNumber < currentStep) {
                goToStep(stepNumber);
            } else if (stepNumber === currentStep + 1) {
                goToStep(stepNumber);
            }
        }
    });

    // Prevenir doble submit del formulario
    $('#clienteForm').on('submit', function(e) {
        if (formSubmitting) {
            e.preventDefault();
            return false;
        }
        
        // VALIDAR DATOS DE APODERADO SI ES MENOR DE EDAD
        if (!validarApoderado()) {
            e.preventDefault();
            return false;
        }
        
        formSubmitting = true;
        
        // Deshabilitar botones de submit
        $('#btnSubmit, #btnSoloCliente').prop('disabled', true).css('opacity', '0.6');
        
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Guardando cliente...',
            html: `
                <div style="padding: 1.5rem;">
                    <div style="width: 60px; height: 60px; border: 4px solid #e2e8f0; border-top-color: #e94560; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    <p style="color: #64748b; margin-top: 1rem;">Por favor espere...</p>
                </div>
                <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            customClass: { popup: 'swal-estoicos' }
        });
    });

    // Función para cambiar de paso
    function goToStep(step) {
        // Validar paso actual antes de avanzar
        if (step > currentStep && !validateStep(currentStep)) {
            return false;
        }

        // Marcar paso anterior como completado
        if (step > currentStep) {
            $(`.step-btn[data-step="${currentStep}"]`).addClass('completed').removeClass('active');
        }

        // Actualizar paso actual
        currentStep = step;

        // Actualizar UI de pasos
        $('.step-btn').removeClass('active');
        $(`.step-btn[data-step="${step}"]`).addClass('active');

        // Mostrar contenido del paso
        $('.step-content').removeClass('active');
        $(`#step-${step}`).addClass('active');

        // Actualizar botones de navegación
        updateNavButtons();

        // Actualizar flujo según paso
        updateFlujo();

        // Actualizar ficha resumen al llegar al paso 3
        if (step === 3) {
            updateFichaResumen();
        }
    }

    function validateStep(step) {
        let isValid = true;
        let errores = [];
        
        if (step === 1) {
            // Limpiar estados previos
            $('#nombres, #apellido_paterno, #celular, #email, #fecha_nacimiento, #run_pasaporte').removeClass('is-invalid');
            
            // 1. Validar campos requeridos
            const camposRequeridos = [
                { id: 'nombres', nombre: 'Nombres' },
                { id: 'apellido_paterno', nombre: 'Apellido Paterno' },
                { id: 'celular', nombre: 'Celular' },
                { id: 'email', nombre: 'Email' }
            ];
            
            camposRequeridos.forEach(campo => {
                const input = $(`#${campo.id}`);
                if (!input.val().trim()) {
                    input.addClass('is-invalid');
                    errores.push(`${campo.nombre} es requerido`);
                    isValid = false;
                }
            });
            
            // 2. Validar formato de nombres (solo letras)
            const regexNombres = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
            ['nombres', 'apellido_paterno', 'apellido_materno'].forEach(campo => {
                const input = $(`#${campo}`);
                const valor = input.val().trim();
                if (valor && !regexNombres.test(valor)) {
                    input.addClass('is-invalid');
                    errores.push(`${campo === 'nombres' ? 'Nombres' : (campo === 'apellido_paterno' ? 'Apellido Paterno' : 'Apellido Materno')} solo debe contener letras`);
                    isValid = false;
                }
            });
            
            // 3. Validar formato de email
            const email = $('#email').val().trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email)) {
                $('#email').addClass('is-invalid');
                errores.push('El formato del email no es válido');
                isValid = false;
            }
            
            // 4. Validar formato de celular chileno
            const celular = $('#celular').val().trim();
            const celularRegex = /^(\+?56)?[\s]?9[\s]?[0-9]{4}[\s]?[0-9]{4}$/;
            if (celular && !celularRegex.test(celular)) {
                $('#celular').addClass('is-invalid');
                errores.push('El formato del celular no es válido (+56 9 XXXX XXXX)');
                isValid = false;
            }
            
            // 5. Validar edad si hay fecha de nacimiento
            const fechaNac = $('#fecha_nacimiento').val();
            if (fechaNac && fechaNac.length === 10) {
                const fecha = new Date(fechaNac);
                const hoy = new Date();
                let edad = hoy.getFullYear() - fecha.getFullYear();
                const m = hoy.getMonth() - fecha.getMonth();
                if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) {
                    edad--;
                }
                
                if (edad < 14) {
                    $('#fecha_nacimiento').addClass('is-invalid');
                    errores.push('El cliente debe tener al menos 14 años');
                    isValid = false;
                } else if (edad > 110) {
                    $('#fecha_nacimiento').addClass('is-invalid');
                    errores.push('Verifique la fecha de nacimiento');
                    isValid = false;
                }
            }
            
            // 6. Validar RUT si fue ingresado
            const rut = $('#run_pasaporte').val().trim();
            if (rut && rut.length >= 9) {
                const resultadoRut = validarRutChileno(rut);
                if (resultadoRut.valid === false) {
                    $('#run_pasaporte').addClass('is-invalid');
                    errores.push('El RUT ingresado no es válido');
                    isValid = false;
                }
            }
            
            // 7. Validar apoderado si es menor de edad
            if ($('#es_menor_edad').val() === '1') {
                if (!$('#consentimiento_apoderado').is(':checked')) {
                    errores.push('Debe confirmar la autorización del apoderado');
                    isValid = false;
                }
                
                const camposApoderado = [
                    { id: 'apoderado_nombre', nombre: 'Nombre del apoderado' },
                    { id: 'apoderado_rut', nombre: 'RUT del apoderado' },
                    { id: 'apoderado_telefono', nombre: 'Teléfono del apoderado' },
                    { id: 'apoderado_parentesco', nombre: 'Parentesco' }
                ];
                
                camposApoderado.forEach(campo => {
                    const input = $(`#${campo.id}`);
                    if (!input.val().trim()) {
                        input.addClass('is-invalid');
                        errores.push(`${campo.nombre} es requerido`);
                        isValid = false;
                    }
                });
            }

            if (!isValid) {
                const listaErrores = errores.slice(0, 5).map(e => `<li>${e}</li>`).join('');
                const masErrores = errores.length > 5 ? `<li>...y ${errores.length - 5} más</li>` : '';
                
                Swal.fire({
                    title: 'Verifica los datos',
                    html: `
                        <div style="text-align: left; padding: 1rem 0;">
                            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; padding: 16px; border: 1px solid #f59e0b;">
                                <ul style="color: #92400e; margin: 0; padding-left: 20px; font-size: 14px;">
                                    ${listaErrores}${masErrores}
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: null,
                    confirmButtonText: 'Entendido',
                    customClass: {
                        popup: 'swal-estoicos swal-warning',
                        confirmButton: 'swal2-confirm'
                    },
                    buttonsStyling: false
                });
            }
        }

        if (step === 2) {
            const membresia = $('#id_membresia').val();
            const fechaInicio = $('#fecha_inicio').val();

            if (!membresia) {
                $('#id_membresia').addClass('is-invalid');
                isValid = false;
            }
            if (!fechaInicio) {
                $('#fecha_inicio').addClass('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                Swal.fire({
                    title: 'Membresía requerida',
                    html: `
                        <div style="text-align: center; padding: 1rem 0;">
                            <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                <i class="fas fa-id-card" style="font-size: 1.8rem; color: #b45309;"></i>
                            </div>
                            <p style="color: #64748b;">Debes seleccionar una membresía y fecha de inicio</p>
                        </div>
                    `,
                    icon: null,
                    confirmButtonText: 'Entendido',
                    customClass: {
                        popup: 'swal-estoicos swal-warning',
                        confirmButton: 'swal2-confirm'
                    },
                    buttonsStyling: false
                });
            }
        }

        return isValid;
    }

    function updateNavButtons() {
        // Botón anterior
        if (currentStep > 1) {
            $('#btnPrev').show();
        } else {
            $('#btnPrev').hide();
        }

        // Botones siguiente/submit
        if (currentStep < totalSteps) {
            $('#btnNext').show();
            $('#btnSubmit').hide();
        } else {
            $('#btnNext').hide();
            $('#btnSubmit').show();
        }

        // Botón solo cliente
        if (currentStep === 1) {
            $('#btnSoloCliente').show();
        } else {
            $('#btnSoloCliente').hide();
        }
    }

    function updateFlujo() {
        if (currentStep === 1) {
            $('#flujo_cliente').val('solo_cliente');
        } else if (currentStep === 2) {
            $('#flujo_cliente').val('con_membresia');
        } else {
            $('#flujo_cliente').val('completo');
        }
    }

    // Event Listeners
    $('#btnNext').on('click', function() {
        if (currentStep < totalSteps) {
            goToStep(currentStep + 1);
        }
    });

    $('#btnPrev').on('click', function() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    });

    $('#btnSoloCliente').on('click', function() {
        if (validateStep(1)) {
            $('#flujo_cliente').val('solo_cliente');
            Swal.fire({
                title: '¿Guardar solo cliente?',
                html: `
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="fas fa-user-plus" style="font-size: 1.8rem; color: #0369a1;"></i>
                        </div>
                        <p style="color: #64748b;">Se registrará el cliente sin membresía ni pago</p>
                    </div>
                `,
                icon: null,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'swal-estoicos swal-primary',
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#clienteForm').submit();
                }
            });
        }
    });

    // Cargar precio de membresía desde data attributes
    $('#id_membresia, #id_convenio').on('change', function() {
        calcularPrecios();
        calcularFechaTermino();
    });

    // Calcular fecha de término al cambiar fecha de inicio + validar fecha no pasada
    $('#fecha_inicio').on('change', function() {
        const fechaSeleccionada = new Date(this.value);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        fechaSeleccionada.setHours(0, 0, 0, 0);
        
        if (fechaSeleccionada < hoy) {
            Swal.fire({
                title: 'Fecha no válida',
                text: 'La fecha de inicio no puede ser anterior a hoy.',
                icon: 'error',
                confirmButtonText: 'Entendido',
                customClass: {
                    popup: 'swal-estoicos',
                    confirmButton: 'swal2-confirm'
                },
                buttonsStyling: false
            });
            // Resetear a hoy
            const today = new Date().toISOString().split('T')[0];
            this.value = today;
        }
        calcularFechaTermino();
    });

    function calcularFechaTermino() {
        const membresiaOption = $('#id_membresia option:selected');
        const duracion = parseInt(membresiaOption.data('duracion')) || 0;
        const fechaInicio = $('#fecha_inicio').val();

        if (duracion > 0 && fechaInicio) {
            const fecha = new Date(fechaInicio);
            fecha.setDate(fecha.getDate() + duracion);
            
            // Formatear fecha para input
            const year = fecha.getFullYear();
            const month = String(fecha.getMonth() + 1).padStart(2, '0');
            const day = String(fecha.getDate()).padStart(2, '0');
            $('#fecha_termino').val(`${year}-${month}-${day}`);
        } else {
            $('#fecha_termino').val('');
        }
    }

    function calcularPrecios() {
        const membresiaSelect = $('#id_membresia');
        const membresiaOption = membresiaSelect.find('option:selected');
        const convenioId = $('#id_convenio').val();

        if (membresiaSelect.val()) {
            // Obtener precios de los data attributes
            precioNormal = parseInt(membresiaOption.data('precio-normal')) || 0;
            const precioConvenioBase = parseInt(membresiaOption.data('precio-convenio')) || precioNormal;
            
            // Calcular descuento convenio y precio base para descuento
            let descuentoConvenio = 0;
            let precioParaDescuento = precioNormal; // Precio máximo que se puede descontar
            
            if (convenioId && precioConvenioBase < precioNormal) {
                descuentoConvenio = precioNormal - precioConvenioBase;
                precioFinal = precioConvenioBase;
                precioParaDescuento = precioConvenioBase;
            } else {
                precioFinal = precioNormal;
                precioParaDescuento = precioNormal;
            }

            // Validar y limitar descuento manual al precio disponible
            let descuentoManual = parseInt($('#descuento_manual').val()) || 0;
            
            // Si el descuento supera el precio disponible, limitarlo
            if (descuentoManual > precioParaDescuento) {
                descuentoManual = precioParaDescuento;
                $('#descuento_manual').val(descuentoManual);
                
                // Mostrar advertencia
                Swal.fire({
                    title: 'Descuento limitado',
                    html: `<p>El descuento no puede superar el precio de la membresía.</p><p>Descuento máximo permitido: <strong>${formatCurrency(precioParaDescuento)}</strong></p>`,
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        popup: 'swal-estoicos swal-warning',
                        confirmButton: 'swal2-confirm'
                    },
                    buttonsStyling: false
                });
            }
            
            // Actualizar el máximo del input
            $('#descuento_manual').attr('max', precioParaDescuento);
            
            // Aplicar descuento manual
            precioFinal = Math.max(0, precioFinal - descuentoManual);

            // Actualizar display
            $('#precioNormal').text(formatCurrency(precioNormal));
            
            if (descuentoConvenio > 0) {
                $('#descuentoConvenioRow').show();
                $('#descuentoConvenio').text('-' + formatCurrency(descuentoConvenio));
            } else {
                $('#descuentoConvenioRow').hide();
            }

            if (descuentoManual > 0) {
                $('#descuentoManualRow').show();
                $('#descuentoManualDisplay').text('-' + formatCurrency(descuentoManual));
            } else {
                $('#descuentoManualRow').hide();
            }

            $('#precioFinal').text(formatCurrency(precioFinal));
            $('#montoAPagar').text(formatCurrency(precioFinal));
            $('#precio_final_oculto').val(precioFinal);
            $('#precioDisplay').show();
        } else {
            $('#precioDisplay').hide();
            precioNormal = 0;
            precioFinal = 0;
            $('#descuento_manual').attr('max', '');
        }
    }

    // Descuento manual - recalcular usando la función principal
    $('#descuento_manual').on('input', function() {
        calcularPrecios();
    });

    // Tipo de pago
    $('#tipo_pago').on('change', function() {
        const tipo = $(this).val();
        
        if (tipo === 'parcial') {
            $('#montoAbonadoRow').show();
            $('#metodoPagoRow').show();
            $('#pagoMixtoSection').hide();
            $('#montoPendienteHint').text(`Monto máximo: ${formatCurrency(precioFinal)}`);
        } else if (tipo === 'mixto') {
            $('#montoAbonadoRow').hide();
            $('#metodoPagoRow').hide();
            $('#pagoMixtoSection').show();
            // Actualizar total en sección mixto
            $('#mixtoTotalAPagar').text(formatCurrency(precioFinal));
            actualizarSumaMixto();
        } else if (tipo === 'pendiente') {
            $('#montoAbonadoRow').hide();
            $('#metodoPagoRow').hide();
            $('#pagoMixtoSection').hide();
        } else {
            // completo
            $('#montoAbonadoRow').hide();
            $('#metodoPagoRow').show();
            $('#pagoMixtoSection').hide();
        }

        // Actualizar ficha pendiente
        updateFichaPendiente();
    });

    // Eventos para pago mixto
    $('#monto_mixto_1, #monto_mixto_2').on('input', function() {
        actualizarSumaMixto();
    });

    function actualizarSumaMixto() {
        const monto1 = parseInt($('#monto_mixto_1').val()) || 0;
        const monto2 = parseInt($('#monto_mixto_2').val()) || 0;
        const suma = monto1 + monto2;
        const diferencia = precioFinal - suma;

        $('#mixtoSumaPagos').text(formatCurrency(suma));

        if (diferencia !== 0) {
            $('#mixtoDiferenciaRow').show();
            $('#mixtoDiferencia').text(formatCurrency(Math.abs(diferencia)));
            if (diferencia > 0) {
                $('#mixtoDiferenciaRow').removeClass('ok').addClass('diferencia');
                $('#mixtoDiferenciaRow span:first').text('Falta:');
            } else {
                $('#mixtoDiferenciaRow').removeClass('diferencia').addClass('ok');
                $('#mixtoDiferenciaRow span:first').text('Excede:');
            }
        } else {
            $('#mixtoDiferenciaRow').hide();
            $('#mixtoDiferenciaRow').addClass('ok');
        }
    }

    // Validación de monto y actualización de pendiente
    $('#monto_abonado').on('input', function() {
        const monto = parseInt($(this).val()) || 0;
        if (monto > precioFinal) {
            $(this).val(precioFinal);
            Swal.fire({
                title: 'Monto excedido',
                text: `El monto no puede superar ${formatCurrency(precioFinal)}`,
                icon: 'warning',
                confirmButtonColor: '#1a1a2e'
            });
        }
        // Actualizar monto pendiente display
        const montoFinal = parseInt($(this).val()) || 0;
        const pendiente = Math.max(0, precioFinal - montoFinal);
        $('#montoPendienteDisplay').text(formatCurrency(pendiente));
        
        // Actualizar ficha pendiente
        updateFichaPendiente();
    });

    // Formato de moneda
    function formatCurrency(value) {
        return '$' + parseInt(value).toLocaleString('es-CL');
    }

    // Actualizar ficha resumen
    function updateFichaResumen() {
        // Datos del cliente
        const nombres = $('#nombres').val() || '';
        const apPaterno = $('#apellido_paterno').val() || '';
        const apMaterno = $('#apellido_materno').val() || '';
        const nombreCompleto = `${nombres} ${apPaterno} ${apMaterno}`.trim();
        $('#fichaCliente').text(nombreCompleto || 'Sin nombre');

        // RUT del cliente - CORREGIDO: usar run_pasaporte
        const rut = $('#run_pasaporte').val() || 'No ingresado';
        $('#fichaRut').text(rut);

        // Datos de membresía
        const membresiaSelect = $('#id_membresia option:selected');
        const membresiaTexto = membresiaSelect.text().split(' - $')[0]; // Quitar precio del texto
        const duracion = membresiaSelect.data('duracion') || '-';
        $('#fichaMembresia').text(membresiaTexto || 'No seleccionada');
        $('#fichaDuracion').text(duracion + ' días');

        // Fechas de inicio y término
        const fechaInicio = $('#fecha_inicio').val();
        const fechaTermino = $('#fecha_termino').val();
        
        if (fechaInicio) {
            const fechaInicioObj = new Date(fechaInicio + 'T00:00:00');
            $('#fichaFechaInicio').text(fechaInicioObj.toLocaleDateString('es-CL', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            }));
        } else {
            $('#fichaFechaInicio').text('-');
        }

        if (fechaTermino) {
            const fechaTerminoObj = new Date(fechaTermino + 'T00:00:00');
            $('#fichaFechaTermino').text(fechaTerminoObj.toLocaleDateString('es-CL', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            }));
        } else {
            $('#fichaFechaTermino').text('-');
        }

        // Convenio aplicado
        const convenioId = $('#id_convenio').val();
        const convenioTexto = $('#id_convenio option:selected').text();
        if (convenioId && convenioId !== '') {
            $('#fichaConvenioRow').show();
            $('#fichaConvenio').text(convenioTexto);
        } else {
            $('#fichaConvenioRow').hide();
        }

        // Precio normal
        $('#fichaPrecioNormal').text(formatCurrency(precioNormal));

        // Descuento convenio
        const precioConvenioBase = parseInt(membresiaSelect.data('precio-convenio')) || precioNormal;
        if (convenioId && convenioId !== '' && precioConvenioBase < precioNormal) {
            const descConvenio = precioNormal - precioConvenioBase;
            $('#fichaDescuentoConvenioItem').show();
            $('#fichaDescuentoConvenio').text('-' + formatCurrency(descConvenio));
        } else {
            $('#fichaDescuentoConvenioItem').hide();
        }

        // Descuento manual
        const descuentoManual = parseInt($('#descuento_manual').val()) || 0;
        const motivoDescuento = $('#id_motivo_descuento option:selected').text();
        if (descuentoManual > 0) {
            $('#fichaDescuentoManualRow').show();
            $('#fichaDescuentoManual').text('-' + formatCurrency(descuentoManual) + ` (${motivoDescuento})`);
        } else {
            $('#fichaDescuentoManualRow').hide();
        }

        // Total
        $('#montoAPagar').text(formatCurrency(precioFinal));
    }

    // Actualizar monto pendiente en la ficha
    function updateFichaPendiente() {
        const tipoPago = $('#tipo_pago').val();
        const montoAbonado = parseInt($('#monto_abonado').val()) || 0;

        if (tipoPago === 'completo') {
            $('#montoPendienteBox').hide();
        } else if (tipoPago === 'pendiente') {
            $('#montoPendienteBox').show();
            $('#montoPendienteDisplay').text(formatCurrency(precioFinal));
        } else if (tipoPago === 'parcial' || tipoPago === 'mixto') {
            $('#montoPendienteBox').show();
            const pendiente = Math.max(0, precioFinal - montoAbonado);
            $('#montoPendienteDisplay').text(formatCurrency(pendiente));
        }
    }

    // Inicializar
    updateNavButtons();
    
    // Restaurar estado si hay datos previos (por ejemplo, después de error de validación)
    function inicializarDatosPrevios() {
        // Si hay membresía seleccionada, recalcular todo
        if ($('#id_membresia').val()) {
            calcularPrecios();
            calcularFechaTermino();
            $('#precioDisplay').show();
        }
        
        // Restaurar tipo de pago y sus campos
        const tipoPagoGuardado = $('#tipo_pago').val();
        if (tipoPagoGuardado) {
            $('#tipo_pago').trigger('change');
            
            // Si es mixto, actualizar la suma
            if (tipoPagoGuardado === 'mixto') {
                actualizarSumaMixto();
            }
            
            // Si es parcial, actualizar el monto pendiente
            if (tipoPagoGuardado === 'parcial') {
                const monto = parseInt($('#monto_abonado').val()) || 0;
                const pendiente = Math.max(0, precioFinal - monto);
                $('#montoPendienteDisplay').text(formatCurrency(pendiente));
            }
        }
    }
    
    // Ejecutar inicialización
    inicializarDatosPrevios();

    // Success/Error messages from session
    @if(session('success'))
    Swal.fire({
        title: '¡Cliente registrado!',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-check" style="font-size: 2rem; color: #00bf8e;"></i>
                </div>
                <p style="color: #64748b;">{{ session('success') }}</p>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Continuar',
        timer: 4000,
        timerProgressBar: true,
        customClass: {
            popup: 'swal-estoicos swal-success',
            confirmButton: 'swal2-confirm'
        },
        buttonsStyling: false
    });
    @endif

    @if(session('error'))
    Swal.fire({
        title: '¡Ocurrió un error!',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #dc3545;"></i>
                </div>
                <p style="color: #64748b;">{{ session('error') }}</p>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Entendido',
        customClass: {
            popup: 'swal-estoicos',
            confirmButton: 'swal2-confirm'
        },
        buttonsStyling: false
    });
    @endif

    @if($errors->any())
    // Determinar en qué paso está el error
    (function() {
        const camposPaso1 = ['run_pasaporte', 'fecha_nacimiento', 'nombres', 'apellido_paterno', 'apellido_materno', 'celular', 'email', 'direccion', 'contacto_emergencia', 'telefono_emergencia', 'observaciones', 'apoderado_nombre', 'apoderado_rut', 'apoderado_telefono', 'apoderado_parentesco', 'consentimiento_apoderado'];
        const camposPaso2 = ['id_membresia', 'id_convenio', 'fecha_inicio', 'descuento_manual', 'id_motivo_descuento'];
        const camposPaso3 = ['tipo_pago', 'id_metodo_pago', 'monto_abonado'];
        
        const erroresArray = @json($errors->keys());
        let pasoConError = 1;
        
        for (let campo of erroresArray) {
            if (camposPaso3.includes(campo)) {
                pasoConError = 3;
                break;
            } else if (camposPaso2.includes(campo)) {
                pasoConError = Math.max(pasoConError, 2);
            }
        }
        
        // Navegar al paso con error
        if (pasoConError !== currentStep) {
            currentStep = pasoConError;
            $('.step-btn').removeClass('active');
            $(`.step-btn[data-step="${pasoConError}"]`).addClass('active');
            $('.step-content').removeClass('active');
            $(`#step-${pasoConError}`).addClass('active');
            updateNavButtons();
        }
        
        // Marcar campos con error
        erroresArray.forEach(campo => {
            $(`#${campo}`).addClass('is-invalid');
        });
    })();
    
    Swal.fire({
        title: 'Errores en el formulario',
        html: `
            <div style="text-align: left; padding: 1rem 0;">
                <div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 12px; padding: 16px; border: 1px solid #dc3545;">
                    <h6 style="color: #b91c1c; font-weight: 700; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-times-circle"></i> Por favor corrige los siguientes errores:
                    </h6>
                    <ul style="color: #7f1d1d; margin: 0; padding-left: 20px; font-size: 14px;">
                        @foreach($errors->all() as $error)
                        <li style="margin-bottom: 4px;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Entendido',
        customClass: {
            popup: 'swal-estoicos',
            confirmButton: 'swal2-confirm'
        },
        buttonsStyling: false
    });
    @endif

    // =====================================================
    // AUTOCOMPLETE DIRECCIÓN - Lista Local de Calles
    // Rápido y sin dependencias externas
    // =====================================================
    (function initDireccionAutocomplete() {
        const input = document.getElementById('direccion');
        const dropdown = document.getElementById('direccion-dropdown');
        
        if (!input || !dropdown) return;
        
        let activeIndex = -1;
        let callesFiltradas = [];
        
        // Lista de calles de Los Ángeles, Biobío
        const calles = [
            'Av. Alemania', 'Av. Ricardo Vicuña', 'Av. Bernardo O\'Higgins', 'Av. Los Carrera',
            'Av. Ercilla', 'Av. Gabriela Mistral', 'Av. Orompello', 'Colón', 'Valdivia',
            'Caupolicán', 'Lautaro', 'Colo Colo', 'Tucapel', 'Rengo', 'Villagrán',
            'Mendoza', 'Almagro', 'Manuel Rodríguez', 'Janequeo', 'Sargento Aldea',
            'Lord Cochrane', 'Chacabuco', 'Maipú', 'Arturo Prat', 'San Martín',
            'Riquelme', 'Castellón', 'General Cruz', 'Freire', 'Bulnes', 'Orompello',
            'Los Copihues', 'Las Violetas', 'Los Aromos', 'El Roble', 'Los Alerces',
            'Los Cipreses', 'Temuco', 'Angol', 'Talca', 'Concepción', 'Santiago',
            'Mulchén', 'Nacimiento', 'Negrete', 'Pje. Pacífico', 'Pje. Atlántico',
            'Pje. Los Pinos', 'Pje. Las Rosas', 'Pje. Los Olivos', 'Pje. El Sol',
            'Pje. La Luna', 'Pje. Las Estrellas', 'Pje. Los Naranjos', 'Pje. Los Cerezos',
            'Pje. Tilao', 'Tilao', 'Pob. Los Acacios', 'Pob. Villa Los Ríos',
            'Pob. Santa María', 'Pob. Sor Vicenta', 'Pob. Bicentenario', 'Pob. Las Vegas',
            'Villa Los Héroes', 'Villa Cordillera', 'Villa España', 'Villa Italia', 'Villa Galilea'
        ];
        
        function normalizar(str) {
            return str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }
        
        function posicionarDropdown() {
            const rect = input.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + window.scrollY + 2) + 'px';
            dropdown.style.left = rect.left + 'px';
            dropdown.style.width = rect.width + 'px';
        }
        
        function buscar(query) {
            const q = normalizar(query);
            if (q.length < 2) {
                ocultar();
                return;
            }
            
            callesFiltradas = calles.filter(c => normalizar(c).includes(q)).slice(0, 8);
            mostrarResultados();
        }
        
        function mostrarResultados() {
            posicionarDropdown();
            
            if (callesFiltradas.length === 0) {
                dropdown.innerHTML = `
                    <div class="dir-empty">
                        <i class="fas fa-edit"></i> Escribe la dirección manualmente
                    </div>
                `;
            } else {
                dropdown.innerHTML = callesFiltradas.map((calle, i) => `
                    <div class="dir-item" data-index="${i}">
                        <div class="dir-item-main">
                            <i class="fas fa-map-marker-alt"></i> ${calle}
                        </div>
                    </div>
                `).join('');
            }
            
            dropdown.classList.add('visible');
            activeIndex = -1;
        }
        
        function ocultar() {
            dropdown.classList.remove('visible');
            activeIndex = -1;
        }
        
        function seleccionar(index) {
            if (index < 0 || index >= callesFiltradas.length) return;
            
            // Conservar número si ya escribió uno
            const numMatch = input.value.match(/\d+/);
            const num = numMatch ? ' ' + numMatch[0] : '';
            
            input.value = callesFiltradas[index] + num;
            ocultar();
            input.focus();
        }
        
        input.addEventListener('input', () => buscar(input.value.trim()));
        
        input.addEventListener('keydown', function(e) {
            if (!dropdown.classList.contains('visible')) return;
            
            const items = dropdown.querySelectorAll('.dir-item');
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                seleccionar(activeIndex);
                return;
            } else if (e.key === 'Escape') {
                ocultar();
                return;
            }
            
            items.forEach((item, i) => item.classList.toggle('active', i === activeIndex));
        });
        
        dropdown.addEventListener('click', function(e) {
            const item = e.target.closest('.dir-item');
            if (item) seleccionar(parseInt(item.dataset.index));
        });
        
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) ocultar();
        });
        
        input.addEventListener('focus', () => {
            if (input.value.length >= 2) buscar(input.value.trim());
        });
    })();
});
</script>
@stop
