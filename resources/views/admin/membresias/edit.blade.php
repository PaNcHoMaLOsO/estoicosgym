@extends('adminlte::page')

@section('title', 'Editar Membresía - EstóicosGym')

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

    /* ===== CARD STYLING ===== */
    .card-primary .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    }

    .card-header h3 {
        color: white;
    }

    /* ===== FORM ELEMENTS ===== */
    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.25);
    }

    .input-group-text {
        background: var(--gray-100);
        border-color: #ced4da;
    }

    /* ===== BUTTONS ===== */
    .btn {
        transition: all 0.3s ease;
        font-weight: 600;
        border-radius: 10px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-primary {
        background: var(--accent);
        border-color: var(--accent);
    }

    .btn-primary:hover {
        background: var(--accent-light);
        border-color: var(--accent-light);
    }

    .btn-outline-secondary:hover {
        background: var(--gray-200);
    }

    /* ===== CUSTOM CHECKBOX ===== */
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--success);
        border-color: var(--success);
    }

    /* ===== ALERT STYLING ===== */
    .alert {
        border-radius: 12px;
        border: none;
    }

    .alert-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%);
        color: white;
    }

    .alert-danger .close {
        color: white;
    }

    .alert-warning {
        background: linear-gradient(135deg, #f0a500 0%, #d99200 100%);
        color: white;
    }

    /* ===== HELPER CLASSES ===== */
    .text-accent {
        color: var(--accent) !important;
    }

    .bg-accent {
        background-color: var(--accent) !important;
    }

    /* ===== PRECIO BOX ===== */
    .precio-preview {
        background: linear-gradient(135deg, var(--gray-100) 0%, white 100%);
        border: 2px solid var(--primary);
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 1rem;
    }

    .precio-preview h5 {
        color: var(--primary);
        font-weight: 700;
    }

    .precio-valor {
        font-size: 2rem;
        font-weight: 700;
        color: var(--success);
    }

    /* ===== FORM ACTIONS ===== */
    .form-actions {
        background: var(--gray-100);
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 2rem;
    }

    /* ===== INFO BOX ===== */
    .info-box-custom {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
        color: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-edit text-accent"></i> Editar Membresía
            </h1>
            <small class="text-muted">Modificar: <strong>{{ $membresia->nombre }}</strong></small>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.membresias.show', $membresia) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-circle"></i> Errores en el formulario
            </h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $inscripcionesActivas = $membresia->inscripciones()
            ->whereNotIn('id_estado', [102, 103])
            ->count();
    @endphp

    @if ($inscripcionesActivas > 0)
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Atención</h5>
            <p class="mb-0">Esta membresía tiene <strong>{{ $inscripcionesActivas }}</strong> inscripción(es) activa(s). 
            Los cambios de duración o precio afectarán las futuras inscripciones, no las existentes.</p>
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-credit-card"></i> Datos de la Membresía
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.membresias.update', $membresia) }}" method="POST" id="formMembresia" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="form_submit_token" value="{{ uniqid('membresia_edit_', true) }}_{{ time() }}">

                <!-- Sección Información Básica -->
                <div class="form-section-title">
                    <i class="fas fa-info-circle"></i> Información Básica
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" name="nombre" placeholder="Ej: Plan Mensual, Pase Diario" 
                                   value="{{ old('nombre', $membresia->nombre) }}" required minlength="3" maxlength="50">
                            <small class="form-text text-muted">Nombre único que identifica la membresía</small>
                            @error('nombre')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="duracion_meses" class="form-label">Duración en Meses <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('duracion_meses') is-invalid @enderror" 
                                       id="duracion_meses" name="duracion_meses" 
                                       value="{{ old('duracion_meses', $membresia->duracion_meses) }}" min="0" max="12" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">meses</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">0 = Pase diario o personalizado</small>
                            @error('duracion_meses')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="duracion_dias_calculado" class="form-label">Duración Total en Días</label>
                            <div class="input-group">
                                <input type="number" class="form-control" 
                                       id="duracion_dias_calculado" min="1" max="365">
                                <input type="hidden" id="duracion_dias" name="duracion_dias" value="{{ old('duracion_dias', $membresia->duracion_dias) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">días</span>
                                </div>
                            </div>
                            <small class="form-text text-muted" id="dias_info">
                                <i class="fas fa-calculator"></i> Mensual: 31 días | Otros: meses × 30
                            </small>
                            @error('duracion_dias')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Descripción -->
                <div class="form-section-title">
                    <i class="fas fa-align-left"></i> Descripción
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label for="descripcion" class="form-label">Descripción <small class="text-muted">(opcional)</small></label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="3" 
                                      placeholder="Beneficios incluidos, horarios, restricciones..."
                                      maxlength="1000">{{ old('descripcion', $membresia->descripcion) }}</textarea>
                            <small class="form-text text-muted">Máximo 1000 caracteres</small>
                            @error('descripcion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Precio -->
                <div class="form-section-title">
                    <i class="fas fa-dollar-sign"></i> Configuración de Precios
                </div>

                @if ($precioActual)
                    <div class="info-box-custom mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Precio actual:</strong> ${{ number_format($precioActual->precio_normal, 0, ',', '.') }}
                        @if ($precioActual->precio_convenio)
                            | <strong>Con convenio:</strong> ${{ number_format($precioActual->precio_convenio, 0, ',', '.') }}
                        @endif
                        <br><small>Vigente desde: {{ $precioActual->fecha_vigencia_desde->format('d/m/Y') }}</small>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="precio_normal" class="form-label">Precio Normal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control @error('precio_normal') is-invalid @enderror" 
                                       id="precio_normal_display" 
                                       placeholder="Ej: 25.000" required>
                                <input type="hidden" id="precio_normal" name="precio_normal" 
                                       value="{{ old('precio_normal', $precioActual->precio_normal ?? 0) }}">
                            </div>
                            <small class="form-text text-muted">Precio sin descuento</small>
                            @error('precio_normal')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="precio_convenio" class="form-label">Precio con Convenio <small class="text-muted">(opcional)</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control @error('precio_convenio') is-invalid @enderror" 
                                       id="precio_convenio_display" 
                                       placeholder="Ej: 20.000">
                                <input type="hidden" id="precio_convenio" name="precio_convenio" 
                                       value="{{ old('precio_convenio', $precioActual->precio_convenio ?? '') }}">
                            </div>
                            <small class="form-text text-muted">Precio para clientes con convenio empresarial</small>
                            @error('precio_convenio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Preview de Precio -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="precio-preview text-center" id="precioPreview" style="display: none;">
                            <h5><i class="fas fa-tag"></i> Nuevo Precio</h5>
                            <div class="precio-valor" id="precioPreviewValor">$0</div>
                            <small class="text-muted" id="precioPreviewDescuento"></small>
                        </div>
                    </div>
                </div>

                <!-- Sección Razón del Cambio -->
                <div class="form-section-title">
                    <i class="fas fa-history"></i> Registro de Cambios
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label for="razon_cambio" class="form-label">Razón del Cambio <small class="text-muted">(recomendado si cambia el precio)</small></label>
                            <input type="text" class="form-control @error('razon_cambio') is-invalid @enderror" 
                                   id="razon_cambio" name="razon_cambio" value="{{ old('razon_cambio') }}" 
                                   placeholder="Ej: Ajuste por inflación, Promoción de temporada, etc."
                                   maxlength="255">
                            <small class="form-text text-muted">Este texto quedará registrado en el historial de precios</small>
                            @error('razon_cambio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Estado -->
                <div class="form-section-title">
                    <i class="fas fa-toggle-on"></i> Estado de la Membresía
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" 
                                   {{ $membresia->activo ? 'checked' : '' }}>
                            <label class="custom-control-label" for="activo">
                                <strong>Membresía Activa</strong>
                            </label>
                        </div>
                        <small class="d-block text-muted mt-2">
                            <i class="fas fa-info-circle"></i> Si está activa, los clientes podrán contratar esta membresía
                        </small>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="form-actions">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <a href="{{ route('admin.membresias.show', $membresia) }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg" id="btnGuardar">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== Referencias a elementos ==========
    const form = document.getElementById('formMembresia');
    const duracionMeses = document.getElementById('duracion_meses');
    const duracionDias = document.getElementById('duracion_dias');
    const duracionDiasCalculado = document.getElementById('duracion_dias_calculado');
    const diasInfo = document.getElementById('dias_info');
    const precioNormalDisplay = document.getElementById('precio_normal_display');
    const precioNormalHidden = document.getElementById('precio_normal');
    const precioConvenioDisplay = document.getElementById('precio_convenio_display');
    const precioConvenioHidden = document.getElementById('precio_convenio');
    const precioPreview = document.getElementById('precioPreview');
    const precioPreviewValor = document.getElementById('precioPreviewValor');
    const precioPreviewDescuento = document.getElementById('precioPreviewDescuento');
    const btnGuardar = document.getElementById('btnGuardar');

    // ========== Lógica de Duración de Días ==========
    function actualizarDias() {
        const meses = parseInt(duracionMeses.value) || 0;
        
        if (meses === 0) {
            // Modo manual para pase diario
            duracionDiasCalculado.removeAttribute('readonly');
            duracionDiasCalculado.value = duracionDias.value || 1;
            duracionDiasCalculado.placeholder = 'Ej: 1 para pase diario';
            diasInfo.innerHTML = '<i class="fas fa-hand-pointer"></i> Meses = 0: Ingresa los días manualmente';
            diasInfo.classList.add('text-warning');
            diasInfo.classList.remove('text-muted');
        } else {
            // Modo automático: 1 mes = 31 días (30 + 1 gracia), otros = meses × 30
            const dias = meses === 1 ? 31 : (meses * 30);
            duracionDias.value = dias;
            duracionDiasCalculado.value = dias;
            duracionDiasCalculado.setAttribute('readonly', 'readonly');
            if (meses === 1) {
                diasInfo.innerHTML = `<i class="fas fa-calculator"></i> Mensual: 30 + 1 día de gracia = <strong>${dias} días</strong>`;
            } else {
                diasInfo.innerHTML = `<i class="fas fa-calculator"></i> Cálculo: ${meses} × 30 = <strong>${dias} días</strong>`;
            }
            diasInfo.classList.remove('text-warning');
            diasInfo.classList.add('text-muted');
        }
    }

    // Sincronizar días manual con hidden
    duracionDiasCalculado.addEventListener('input', function() {
        duracionDias.value = this.value;
    });

    duracionMeses.addEventListener('change', actualizarDias);
    duracionMeses.addEventListener('input', actualizarDias);
    
    // Cargar valor inicial de días
    duracionDiasCalculado.value = duracionDias.value;
    actualizarDias();

    // ========== Formateo de Precio ==========
    function formatearNumero(num) {
        if (!num && num !== 0) return '';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function limpiarNumero(str) {
        if (!str) return 0;
        return parseInt(str.toString().replace(/\D/g, '')) || 0;
    }

    function actualizarPrecioDisplay(displayEl, hiddenEl) {
        const valor = displayEl.value;
        const numero = limpiarNumero(valor);
        hiddenEl.value = numero;
        
        // Mantener cursor
        const cursorPos = displayEl.selectionStart;
        const oldLen = displayEl.value.length;
        
        displayEl.value = numero > 0 ? formatearNumero(numero) : '';
        
        // Ajustar cursor
        const newLen = displayEl.value.length;
        const newPos = cursorPos + (newLen - oldLen);
        displayEl.setSelectionRange(Math.max(0, newPos), Math.max(0, newPos));
    }

    function actualizarPreview() {
        const precioNormal = limpiarNumero(precioNormalDisplay.value);
        const precioConvenio = limpiarNumero(precioConvenioDisplay.value);
        
        if (precioNormal > 0) {
            precioPreview.style.display = 'block';
            precioPreviewValor.textContent = '$' + formatearNumero(precioNormal);
            
            if (precioConvenio > 0 && precioConvenio < precioNormal) {
                const descuento = precioNormal - precioConvenio;
                const porcentaje = Math.round((descuento / precioNormal) * 100);
                precioPreviewDescuento.innerHTML = `
                    <span class="text-success">
                        <i class="fas fa-percentage"></i> Con convenio: $${formatearNumero(precioConvenio)} 
                        (${porcentaje}% descuento)
                    </span>`;
            } else {
                precioPreviewDescuento.textContent = '';
            }
        } else {
            precioPreview.style.display = 'none';
        }
    }

    precioNormalDisplay.addEventListener('input', function() {
        actualizarPrecioDisplay(this, precioNormalHidden);
        actualizarPreview();
    });

    precioConvenioDisplay.addEventListener('input', function() {
        actualizarPrecioDisplay(this, precioConvenioHidden);
        actualizarPreview();
    });

    // Cargar valores iniciales
    if (precioNormalHidden.value) {
        precioNormalDisplay.value = formatearNumero(precioNormalHidden.value);
        actualizarPreview();
    }
    if (precioConvenioHidden.value) {
        precioConvenioDisplay.value = formatearNumero(precioConvenioHidden.value);
    }

    // ========== Validación del Formulario ==========
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let errorMsg = '';

        // Validar nombre
        const nombre = document.getElementById('nombre').value.trim();
        if (nombre.length < 3) {
            errorMsg = 'El nombre debe tener al menos 3 caracteres';
            isValid = false;
        }

        // Validar duración días
        const dias = parseInt(duracionDias.value) || 0;
        if (dias < 1) {
            errorMsg = 'La duración en días debe ser al menos 1';
            isValid = false;
        }

        // Validar precio normal
        const precioNormal = limpiarNumero(precioNormalDisplay.value);
        if (precioNormal <= 0) {
            errorMsg = 'El precio normal debe ser mayor a 0';
            isValid = false;
        }

        // Validar precio convenio (si existe, debe ser menor al normal)
        const precioConvenio = limpiarNumero(precioConvenioDisplay.value);
        if (precioConvenio > 0 && precioConvenio >= precioNormal) {
            errorMsg = 'El precio con convenio debe ser menor al precio normal';
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: errorMsg,
                confirmButtonColor: '#e94560'
            });
            return false;
        }

        // Deshabilitar botón para evitar doble envío
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    });
});
</script>
@endsection
