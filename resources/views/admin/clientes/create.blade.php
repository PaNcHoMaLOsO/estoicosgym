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

    <!-- Wizard Steps -->
    <div class="wizard-steps">
        <div class="step active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-info">
                <span class="step-title">Datos Personales</span>
                <span class="step-desc">Información del cliente</span>
            </div>
        </div>
        <div class="step-connector"></div>
        <div class="step" data-step="2">
            <div class="step-number">2</div>
            <div class="step-info">
                <span class="step-title">Membresía</span>
                <span class="step-desc">Plan y convenio</span>
            </div>
        </div>
        <div class="step-connector"></div>
        <div class="step" data-step="3">
            <div class="step-number">3</div>
            <div class="step-info">
                <span class="step-title">Pago</span>
                <span class="step-desc">Forma de pago</span>
            </div>
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
                                <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                                       id="run_pasaporte" name="run_pasaporte" 
                                       placeholder="Ej: 12.345.678-9"
                                       value="{{ old('run_pasaporte') }}">
                                <small class="form-hint">Campo opcional - Formato chileno o pasaporte</small>
                                @error('run_pasaporte')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fecha_nacimiento">
                                    <i class="fas fa-birthday-cake"></i> Fecha de Nacimiento
                                </label>
                                <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                       id="fecha_nacimiento" name="fecha_nacimiento"
                                       value="{{ old('fecha_nacimiento') }}">
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

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
                            <div class="form-group col-md-12">
                                <label for="direccion">
                                    <i class="fas fa-map-marker-alt"></i> Dirección
                                </label>
                                <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                       id="direccion" name="direccion"
                                       placeholder="Ej: Av. Principal 123, Santiago"
                                       value="{{ old('direccion') }}">
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
                                       value="{{ old('fecha_inicio', date('Y-m-d')) }}">
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

    /* Wizard Steps */
    .wizard-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 16px;
        padding: 24px 40px;
        margin-bottom: 30px;
        box-shadow: var(--shadow);
    }

    .step {
        display: flex;
        align-items: center;
        gap: 14px;
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .step.active, .step.completed {
        opacity: 1;
    }

    .step-number {
        width: 45px;
        height: 45px;
        background: var(--border-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        color: var(--text-secondary);
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: var(--primary);
        color: #fff;
    }

    .step.completed .step-number {
        background: var(--success);
        color: #fff;
    }

    .step-info {
        display: flex;
        flex-direction: column;
    }

    .step-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 15px;
    }

    .step-desc {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .step-connector {
        width: 60px;
        height: 3px;
        background: var(--border-color);
        margin: 0 20px;
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    .step.completed + .step-connector {
        background: var(--success);
    }

    /* Form Container */
    .form-container {
        background: #fff;
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--shadow);
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

    /* Section Cards */
    .section-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 16px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-header i {
        font-size: 20px;
        color: #fff;
    }

    .section-header h3 {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .section-body {
        padding: 24px;
    }

    /* Form Elements */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }

    .form-group {
        padding: 0 10px;
        margin-bottom: 20px;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group label i {
        color: var(--primary);
        font-size: 14px;
    }

    .required {
        color: var(--accent);
    }

    .form-control {
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.3s ease;
        background-color: #fff;
        color: var(--text-primary);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,26,46,0.1);
        outline: none;
    }

    .form-control.is-invalid {
        border-color: var(--danger);
    }

    .form-hint {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 6px;
        display: block;
    }

    .invalid-feedback {
        color: var(--danger);
        font-size: 12px;
        margin-top: 6px;
    }

    /* Precio Display */
    .precio-display {
        background: linear-gradient(135deg, rgba(26,26,46,0.03) 0%, rgba(26,26,46,0.06) 100%);
        border: 2px solid var(--primary);
        border-radius: 14px;
        overflow: hidden;
        margin-top: 20px;
    }

    .precio-header {
        background: var(--primary);
        color: #fff;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    .precio-body {
        padding: 20px;
    }

    .precio-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px dashed var(--border-color);
    }

    .precio-row:last-child {
        border-bottom: none;
    }

    .precio-row.total {
        padding-top: 16px;
        margin-top: 10px;
        border-top: 2px solid var(--primary);
        border-bottom: none;
    }

    .precio-label {
        font-weight: 500;
        color: var(--text-secondary);
    }

    .precio-row.total .precio-label {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 16px;
    }

    .precio-value {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 16px;
    }

    .precio-row.total .precio-value {
        font-size: 24px;
        color: var(--success);
    }

    .text-success {
        color: var(--success) !important;
    }

    /* Pago Resumen */
    .pago-resumen {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 24px;
        text-align: center;
    }

    .resumen-label {
        color: rgba(255,255,255,0.8);
        font-size: 14px;
        display: block;
        margin-bottom: 8px;
    }

    .resumen-value {
        color: #fff;
        font-size: 32px;
        font-weight: 700;
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

    /* Wizard Navigation */
    .wizard-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 0 0;
        border-top: 1px solid var(--border-color);
        margin-top: 30px;
    }

    .nav-left, .nav-right {
        display: flex;
        gap: 12px;
    }

    .nav-center {
        flex: 1;
        text-align: center;
    }

    .btn-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-prev {
        background: var(--border-color);
        color: var(--text-primary);
    }

    .btn-prev:hover {
        background: #dee2e6;
        transform: translateX(-3px);
    }

    .btn-next {
        background: var(--primary);
        color: #fff;
    }

    .btn-next:hover {
        background: var(--primary-light);
        transform: translateX(3px);
    }

    .btn-submit {
        background: var(--success);
        color: #fff;
    }

    .btn-submit:hover {
        background: #00a67e;
        transform: translateY(-2px);
    }

    .btn-save-only {
        background: transparent;
        border: 2px solid var(--info);
        color: var(--info);
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-save-only:hover {
        background: var(--info);
        color: #fff;
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

        .wizard-steps {
            flex-direction: column;
            gap: 16px;
            padding: 20px;
        }

        .step-connector {
            width: 3px;
            height: 30px;
            margin: 0;
        }

        .form-container {
            padding: 20px;
        }

        .wizard-navigation {
            flex-direction: column;
            gap: 16px;
        }

        .nav-left, .nav-center, .nav-right {
            width: 100%;
            justify-content: center;
        }

        .btn-nav {
            flex: 1;
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
       FICHA RESUMEN DE INSCRIPCIÓN
       ============================================ */
    .resumen-ficha {
        background: #fff;
        border: 2px solid var(--primary);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: var(--shadow);
    }

    .ficha-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 16px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ficha-header i {
        font-size: 22px;
        color: #fff;
    }

    .ficha-header h3 {
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .ficha-body {
        padding: 24px;
    }

    .ficha-row {
        display: flex;
        gap: 24px;
        margin-bottom: 16px;
    }

    .ficha-row:last-child {
        margin-bottom: 0;
    }

    .ficha-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .ficha-item.full {
        flex: 2;
    }

    .ficha-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ficha-label i {
        font-size: 12px;
        color: var(--primary);
    }

    .ficha-value {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .ficha-value.ficha-convenio {
        color: var(--info);
        background: rgba(67, 97, 238, 0.1);
        padding: 4px 12px;
        border-radius: 20px;
        display: inline-block;
        width: fit-content;
    }

    .ficha-value.ficha-descuento {
        color: var(--success);
    }

    .ficha-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--border-color), transparent);
        margin: 20px 0;
    }

    .ficha-total {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        margin: 20px -24px -24px -24px;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ficha-total-label {
        color: rgba(255,255,255,0.9);
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .ficha-total-value {
        color: #fff;
        font-size: 32px;
        font-weight: 800;
    }

    /* ============================================
       MONTO PENDIENTE BOX
       ============================================ */
    .monto-pendiente-box {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px solid var(--warning);
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-top: 28px;
    }

    .monto-pendiente-label {
        font-size: 12px;
        font-weight: 600;
        color: #92400e;
        text-transform: uppercase;
    }

    .monto-pendiente-value {
        font-size: 24px;
        font-weight: 700;
        color: #b45309;
    }

    /* ============================================
       TIPO PAGO BADGES
       ============================================ */
    .tipo-pago-info {
        background: rgba(67, 97, 238, 0.1);
        border: 1px solid var(--info);
        border-radius: 10px;
        padding: 12px 16px;
        margin-top: 12px;
        font-size: 13px;
        color: var(--info);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tipo-pago-info i {
        font-size: 16px;
    }

    /* ============================================
       FECHA TÉRMINO EN FICHA
       ============================================ */
    .ficha-value.ficha-fecha-termino {
        color: var(--accent);
        font-weight: 700;
    }

    /* ============================================
       PAGO MIXTO STYLES
       ============================================ */
    .mixto-header {
        background: linear-gradient(135deg, var(--info) 0%, #3730a3 100%);
        color: #fff;
        padding: 14px 20px;
        border-radius: 12px 12px 0 0;
        margin: 20px 0 0 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 15px;
    }

    .mixto-header i {
        font-size: 18px;
    }

    #pagoMixtoSection .form-row {
        background: #f8f9ff;
        padding: 16px 20px;
        margin: 0;
        border-left: 2px solid var(--info);
        border-right: 2px solid var(--info);
    }

    #pagoMixtoSection .form-row:first-of-type {
        padding-top: 20px;
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
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    #pagoMixtoSection .form-control option {
        background-color: #fff;
        color: #1e1b4b;
    }

    .mixto-total-box {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        border: 2px solid var(--info);
        border-top: none;
        border-radius: 0 0 12px 12px;
        padding: 16px 20px;
    }

    .mixto-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 14px;
        color: #4338ca;
    }

    .mixto-total-row span:last-child {
        font-weight: 700;
        font-size: 16px;
    }

    .mixto-total-row.diferencia {
        border-top: 1px dashed #818cf8;
        margin-top: 8px;
        padding-top: 12px;
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

        .wizard-steps {
            gap: 8px;
        }

        .step {
            flex-direction: column;
            padding: 10px 8px;
        }

        .step-number {
            margin-bottom: 4px;
        }

        .step-info {
            text-align: center;
        }

        .step-desc {
            font-size: 10px;
        }

        .monto-pendiente-box {
            margin-top: 16px;
        }

        .section-header h3 {
            font-size: 16px;
        }
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

    // Aplicar formateo al campo RUT
    $('#run_pasaporte').on('input', function() {
        let cursorPos = this.selectionStart;
        let valorAnterior = $(this).val();
        let valorFormateado = formatRut(valorAnterior);
        
        $(this).val(valorFormateado);
        
        // Ajustar posición del cursor
        let diff = valorFormateado.length - valorAnterior.length;
        this.setSelectionRange(cursorPos + diff, cursorPos + diff);
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

    // Prevenir doble submit del formulario
    $('#clienteForm').on('submit', function(e) {
        if (formSubmitting) {
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
            $(`.step[data-step="${currentStep}"]`).addClass('completed').removeClass('active');
        }

        // Actualizar paso actual
        currentStep = step;

        // Actualizar UI de pasos
        $('.step').removeClass('active');
        $(`.step[data-step="${step}"]`).addClass('active');

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
        
        if (step === 1) {
            // Validar campos requeridos del paso 1
            const campos = ['nombres', 'apellido_paterno', 'celular', 'email'];
            campos.forEach(campo => {
                const input = $(`#${campo}`);
                if (!input.val().trim()) {
                    input.addClass('is-invalid');
                    isValid = false;
                } else {
                    input.removeClass('is-invalid');
                }
            });

            if (!isValid) {
                Swal.fire({
                    title: 'Campos requeridos',
                    html: `
                        <div style="text-align: center; padding: 1rem 0;">
                            <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                <i class="fas fa-exclamation" style="font-size: 1.8rem; color: #b45309;"></i>
                            </div>
                            <p style="color: #64748b;">Por favor completa todos los campos obligatorios</p>
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

    // Calcular fecha de término al cambiar fecha de inicio
    $('#fecha_inicio').on('change', function() {
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
            
            // Calcular descuento convenio
            let descuentoConvenio = 0;
            if (convenioId && precioConvenioBase < precioNormal) {
                descuentoConvenio = precioNormal - precioConvenioBase;
                precioFinal = precioConvenioBase;
            } else {
                precioFinal = precioNormal;
            }

            // Aplicar descuento manual
            const descuentoManual = parseInt($('#descuento_manual').val()) || 0;
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

        // RUT del cliente
        const rut = $('#rut').val() || '-';
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
});
</script>
@stop
