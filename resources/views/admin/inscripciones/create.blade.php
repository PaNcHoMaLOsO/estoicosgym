@extends('adminlte::page')

@section('title', 'Nueva Inscripción - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-section {
            border-left: 4px solid #0066cc;
            padding-left: 15px;
            margin-top: 25px;
            margin-bottom: 20px;
        }
        .form-section-title {
            font-weight: bold;
            color: #0066cc;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .conditional-field {
            display: none;
            animation: slideDown 0.3s ease;
        }
        .conditional-field.visible {
            display: block;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .price-summary {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 10px;
        }
        .price-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .price-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 16px;
        }
        .badge-info {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            margin-top: 5px;
        }
        .info-alert {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 2px;
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-plus-circle"></i> Crear Nueva Inscripción
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Errores:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('admin.inscripciones.store') }}" method="POST" id="formInscripcion">
        @csrf

        <!-- PASO 1: INFORMACIÓN DEL CLIENTE Y MEMBRESÍA -->
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-check"></i> Paso 1: Cliente y Membresía
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-user"></i> Cliente <span class="text-danger">*</span></label>
                        <select class="form-control select2-cliente @error('id_cliente') is-invalid @enderror" 
                                id="id_cliente" name="id_cliente" required style="width: 100%;">
                            <option value="">-- Seleccionar Cliente --</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('id_cliente') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombres }} {{ $cliente->apellido_paterno }} 
                                    @if($cliente->apellido_materno) {{ $cliente->apellido_materno }} @endif
                                    @if($cliente->run_pasaporte)- {{ $cliente->run_pasaporte }}@endif
                                </option>
                            @endforeach
                        </select>
                        @error('id_cliente')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Solo se muestran clientes con membresía vencida</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-layer-group"></i> Membresía <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                id="id_membresia" name="id_membresia" required>
                            <option value="">-- Seleccionar Membresía --</option>
                            @foreach($membresias as $membresia)
                                <option value="{{ $membresia->id }}" {{ old('id_membresia') == $membresia->id ? 'selected' : '' }}>
                                    {{ $membresia->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_membresia')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- RESUMEN DE PRECIOS (Dinámico) -->
                <div id="priceSummary" class="price-summary" style="display: none;">
                    <div class="price-item">
                        <span>Precio Base:</span>
                        <span id="precioBase">$0.00</span>
                    </div>
                    <div class="price-item">
                        <span>Descuento:</span>
                        <span id="precioDescuento">$0.00</span>
                    </div>
                    <div class="price-item">
                        <span>Precio Total:</span>
                        <span id="precioTotal">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PASO 2: FECHAS Y ESTADO -->
        <div class="card card-info mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt"></i> Paso 2: Fechas
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-calendar-check"></i> Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                               id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" 
                               required>
                        @error('fecha_inicio')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-calendar-times"></i> Fecha Vencimiento</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" readonly>
                        <small class="text-muted d-block mt-1">Se calcula automáticamente</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado está oculto, siempre será "Activa" -->
        <input type="hidden" name="id_estado" value="201">

        <!-- PASO 3: DESCUENTOS (Opcional) -->
        <div class="card card-warning mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-percent"></i> Paso 3: Convenio y Descuentos
                </h3>
            </div>
            <div class="card-body">
                @if($convenios->count() > 0)
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label"><i class="fas fa-handshake"></i> Convenio (Solo aplica a membresía mensual)</label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" name="id_convenio">
                                <option value="">-- Sin Convenio --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" data-nombre="{{ $convenio->nombre }}" {{ old('id_convenio') == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }} - {{ $convenio->tipo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_convenio')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Se aplicará descuento automático de $15.000 si aplica</small>
                        </div>
                    </div>
                    <hr>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-dollar-sign"></i> Descuento Total</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control @error('descuento_aplicado') is-invalid @enderror" 
                                   id="descuento_adicional" name="descuento_aplicado" step="0.01" min="0" 
                                   value="{{ old('descuento_aplicado', 0) }}">
                        </div>
                        @error('descuento_aplicado')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1" id="descuento_info">Descuento a aplicar (se calcula automáticamente si hay convenio)</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-tag"></i> Motivo Descuento</label>
                        <select class="form-control @error('id_motivo_descuento') is-invalid @enderror" 
                                id="id_motivo_descuento" name="id_motivo_descuento">
                            <option value="">-- Sin Motivo --</option>
                            @foreach($motivos as $motivo)
                                <option value="{{ $motivo->id }}" {{ old('id_motivo_descuento') == $motivo->id ? 'selected' : '' }}>
                                    {{ $motivo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_motivo_descuento')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Se auto-completa según convenio si aplica</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- PASO 4: INFORMACIÓN DE PAGO -->
        <div class="card card-success mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-money-bill-wave"></i> Paso 4: Información de Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="info-alert">
                    <strong><i class="fas fa-info-circle"></i> Nota:</strong> Marca "Pago Pendiente" si el cliente realizará el pago después.
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="pago_pendiente" name="pago_pendiente">
                            <label class="custom-control-label" for="pago_pendiente">
                                <strong>Dejar pago pendiente</strong> - El cliente pagará después
                            </label>
                        </div>
                    </div>
                </div>

                <div id="seccionPago">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-calendar"></i> Fecha Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                            @error('fecha_pago')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-money-bill-wave"></i> Monto Abonado <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                       value="{{ old('monto_abonado') }}" placeholder="0.00" required>
                            </div>
                            @error('monto_abonado')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-credit-card"></i> Método Pago <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_metodo_pago') is-invalid @enderror" 
                                    id="id_metodo_pago" name="id_metodo_pago" required>
                                <option value="">-- Seleccionar Método --</option>
                                @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo->id }}" {{ old('id_metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                        {{ $metodo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_metodo_pago')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- CAMPOS CONDICIONALES: Solo visible si pago es parcial -->
                    <div id="cuotasSection" class="conditional-field" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="fas fa-divide"></i> Cantidad Cuotas</label>
                                <input type="number" class="form-control @error('cantidad_cuotas') is-invalid @enderror" 
                                       id="cantidad_cuotas" name="cantidad_cuotas" min="2" max="12" value="1">
                                <small class="text-muted d-block mt-1">Solo si el pago es parcial</small>
                                @error('cantidad_cuotas')
                                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="fas fa-receipt"></i> Monto por Cuota</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="monto_cuota" readonly>
                                </div>
                                <small class="text-muted d-block mt-1">Se calcula automáticamente</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="fas fa-calendar-times"></i> Vencimiento Cuota</label>
                                <input type="date" class="form-control @error('fecha_vencimiento_cuota') is-invalid @enderror" 
                                       id="fecha_vencimiento_cuota" name="fecha_vencimiento_cuota">
                                @error('fecha_vencimiento_cuota')
                                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OBSERVACIONES -->
        <div class="card card-secondary mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-align-left"></i> Observaciones (Opcional)
                </h3>
            </div>
            <div class="card-body">
                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                          id="observaciones" name="observaciones" rows="3" placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
                @error('observaciones')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- BOTONES -->
        <hr class="my-4">
        <div class="row">
            <div class="col-12">
                <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Crear Inscripción con Pago
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del formulario
    const idMembresia = document.getElementById('id_membresia');
    const fechaInicio = document.getElementById('fecha_inicio');
    const descuentoAdicional = document.getElementById('descuento_adicional');
    const montoAbonado = document.getElementById('monto_abonado');
    const cantidadCuotas = document.getElementById('cantidad_cuotas');
    const cuotasSection = document.getElementById('cuotasSection');
    const priceSummary = document.getElementById('priceSummary');
    const idConvenio = document.getElementById('id_convenio');
    const idMotivoDescuento = document.getElementById('id_motivo_descuento');
    const pagoPendiente = document.getElementById('pago_pendiente');
    const seccionPago = document.getElementById('seccionPago');
    
    // Elementos para mostrar precios
    const precioBaseEl = document.getElementById('precioBase');
    const precioDescuentoEl = document.getElementById('precioDescuento');
    const precioTotalEl = document.getElementById('precioTotal');
    const montoCuotaEl = document.getElementById('monto_cuota');
    const fechaVencimientoEl = document.getElementById('fecha_vencimiento');

    // Variable para mantener el descuento de convenio
    let descuentoConvenioActual = 0;

    // Inicializar Select2
    $('#id_cliente').select2({
        width: '100%',
        allowClear: true,
        language: 'es',
        dropdownParent: $('#id_cliente').parent(),
        dropdownCssClass: 'select2-large-dropdown'
    });
    
    // Aumentar tamaño del contenedor del dropdown
    $('.select2-large-dropdown').css('min-height', '300px');

    // Cargar precio cuando se selecciona membresía
    async function cargarPrecioMembresia() {
        if (!idMembresia.value) {
            priceSummary.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/membresias/${idMembresia.value}`);
            const data = await response.json();

            if (response.ok) {
                const precioBase = parseFloat(data.precio_normal) || 0;
                
                // Calcular descuento de convenio SOLO si es membresía mensual (id=1) Y hay convenio seleccionado
                if (idConvenio.value && data.id === 1) { // 1 es membresía mensual
                    descuentoConvenioActual = 15000; // Descuento de 15.000 para membresía mensual con convenio
                } else {
                    descuentoConvenioActual = 0;
                }
                
                // El descuento adicional es lo que el usuario especifica
                const descuentoAdicionalManual = parseFloat(descuentoAdicional.value) || 0;
                
                // Descuento total es la suma
                const descuentoTotal = descuentoConvenioActual + descuentoAdicionalManual;
                const precioFinal = precioBase - descuentoTotal;

                // Mostrar precios
                precioBaseEl.textContent = '$' + precioBase.toFixed(2);
                precioDescuentoEl.textContent = '$' + descuentoTotal.toFixed(2);
                precioTotalEl.textContent = '$' + precioFinal.toFixed(2);
                
                // El campo guarda el TOTAL (convenio + adicional)
                // Pero NO lo sobrescribimos si el usuario está editando
                // Solo actualizamos si no hay valor previo o se cambió convenio
                if (!descuentoAdicional.value || descuentoConvenioActual > 0) {
                    descuentoAdicional.value = descuentoTotal.toFixed(2);
                }
                
                // Actualizar el mensaje de descuento
                let mensajeDescuento = 'Descuento a aplicar (se calcula automáticamente si hay convenio)';
                if (descuentoConvenioActual > 0) {
                    mensajeDescuento = `Descuento: $${descuentoConvenioActual.toFixed(2)} (convenio) + $${descuentoAdicionalManual.toFixed(2)} (adicional) = $${descuentoTotal.toFixed(2)}`;
                } else if (descuentoAdicionalManual > 0) {
                    mensajeDescuento = `Descuento adicional: $${descuentoAdicionalManual.toFixed(2)}`;
                }
                document.getElementById('descuento_info').textContent = mensajeDescuento;

                priceSummary.style.display = 'block';
                calcularVencimiento();
                validarPagoCompleto();
            }
        } catch (error) {
            console.error('Error al cargar precio:', error);
        }
    }

    // Calcular fecha de vencimiento
    async function calcularVencimiento() {
        if (!idMembresia.value || !fechaInicio.value) return;

        try {
            const response = await fetch(`/api/membresias/${idMembresia.value}`);
            const membresiaData = await response.json();

            if (response.ok) {
                const [year, month, day] = fechaInicio.value.split('-').map(Number);
                const fechaInicioParsed = new Date(year, month - 1, day); // month es 0-indexed
                
                // Usar duracion_dias si está disponible (para Pase Diario), sino usar duracion_meses
                const duracionDias = membresiaData.duracion_dias || (membresiaData.duracion_meses * 30);
                const duracionMeses = membresiaData.duracion_meses || 1;
                
                // Calcular vencimiento
                const fechaVencimiento = new Date(fechaInicioParsed);
                
                if (duracionMeses === 0) {
                    // Para Pase Diario: sumar días directamente
                    fechaVencimiento.setDate(fechaVencimiento.getDate() + duracionDias - 1);
                } else {
                    // Para membresías de meses: sumar meses y restar 1 día
                    fechaVencimiento.setMonth(fechaVencimiento.getMonth() + duracionMeses);
                    fechaVencimiento.setDate(fechaVencimiento.getDate() - 1);
                }
                
                // Formatear a YYYY-MM-DD
                const yearFormato = fechaVencimiento.getFullYear();
                const monthFormato = String(fechaVencimiento.getMonth() + 1).padStart(2, '0');
                const dayFormato = String(fechaVencimiento.getDate()).padStart(2, '0');
                const fechaVencimientoFormato = `${yearFormato}-${monthFormato}-${dayFormato}`;
                
                fechaVencimientoEl.value = fechaVencimientoFormato;
            }
        } catch (error) {
            console.error('Error al calcular vencimiento:', error);
        }
    }

    // Validar si el pago es completo o parcial
    function validarPagoCompleto() {
        const precioTotal = parseFloat(precioTotalEl.textContent.replace('$', '')) || 0;
        const monto = parseFloat(montoAbonado.value) || 0;

        if (monto > 0 && monto < precioTotal) {
            // Pago parcial: mostrar opciones de cuotas
            cuotasSection.classList.add('visible');
            cuotasSection.style.display = 'block';
            cantidadCuotas.value = 2;
            calcularMontoCuota();
        } else {
            // Pago completo o sin especificar: ocultar opciones de cuotas
            cuotasSection.classList.remove('visible');
            cuotasSection.style.display = 'none';
            cantidadCuotas.value = 1;
        }
    }

    // Manejar cambio en convenio
    function manejarCambioConvenio() {
        // Auto-rellenar motivo descuento según convenio
        if (idConvenio.value) {
            const nombreConvenio = idConvenio.options[idConvenio.selectedIndex].getAttribute('data-nombre');
            
            // Mapeo: Si el convenio es INACAP, rellenar con "Estudiante"
            if (nombreConvenio && nombreConvenio.toUpperCase().includes('INACAP')) {
                // Buscar opción "Estudiante" en motivos
                for (let option of idMotivoDescuento.options) {
                    if (option.textContent.toLowerCase().includes('estudiante')) {
                        idMotivoDescuento.value = option.value;
                        break;
                    }
                }
            }
        } else {
            // Si se quita el convenio, limpiar motivo descuento
            idMotivoDescuento.value = '';
            // Y restaurar descuento a 0
            descuentoAdicional.value = '0.00';
        }
        
        // Recalcular precio CON el nuevo descuento de convenio
        cargarPrecioMembresia();
    }

    // Manejar checkbox pago pendiente
    function manejarPagoPendiente() {
        const campos = seccionPago.querySelectorAll('input[required], select[required]');
        
        if (pagoPendiente.checked) {
            // Ocultar sección de pago
            seccionPago.style.display = 'none';
            // Quitar required de todos los campos
            campos.forEach(campo => campo.removeAttribute('required'));
        } else {
            // Mostrar sección de pago
            seccionPago.style.display = 'block';
            // Agregar required a todos los campos
            campos.forEach(campo => campo.setAttribute('required', 'required'));
        }
    }

    // Calcular monto por cuota
    function calcularMontoCuota() {
        const precioTotal = parseFloat(precioTotalEl.textContent.replace('$', '')) || 0;
        const cuotas = parseInt(cantidadCuotas.value) || 1;
        const montoPorCuota = precioTotal / cuotas;

        montoCuotaEl.value = montoPorCuota.toFixed(2);
    }

    // Event listeners
    idMembresia.addEventListener('change', cargarPrecioMembresia);
    idMembresia.addEventListener('change', calcularVencimiento);
    fechaInicio.addEventListener('change', calcularVencimiento);
    descuentoAdicional.addEventListener('change', cargarPrecioMembresia);
    descuentoAdicional.addEventListener('input', cargarPrecioMembresia);
    idConvenio.addEventListener('change', manejarCambioConvenio);
    pagoPendiente.addEventListener('change', manejarPagoPendiente);
    montoAbonado.addEventListener('change', validarPagoCompleto);
    montoAbonado.addEventListener('input', validarPagoCompleto);
    cantidadCuotas.addEventListener('change', calcularMontoCuota);
});
</script>
@endsection
