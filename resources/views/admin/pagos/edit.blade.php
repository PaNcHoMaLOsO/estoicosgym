@extends('adminlte::page')

@section('title', 'Editar Pago - EstóicosGym')

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

    /* HERO HEADER */
    .hero-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 30px 35px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 15px 40px rgba(26, 26, 46, 0.4);
        position: relative;
        overflow: hidden;
    }
    .hero-header::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 250px;
        height: 250px;
        background: var(--warning);
        border-radius: 50%;
        opacity: 0.1;
    }
    .hero-header-content { position: relative; z-index: 1; }
    .hero-title { 
        font-size: 1.8em; 
        font-weight: 800; 
        margin-bottom: 5px;
        letter-spacing: -0.5px;
    }
    .hero-subtitle { 
        font-size: 1em; 
        opacity: 0.9;
        font-weight: 400;
    }
    .btn-back {
        background: transparent;
        color: white;
        border: 2px solid rgba(255,255,255,0.5);
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: rgba(255,255,255,0.1);
        border-color: white;
        color: white;
    }

    /* FORM CARD */
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        margin-bottom: 25px;
        border: none;
        overflow: hidden;
    }
    .form-card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 18px 25px;
        font-weight: 700;
        font-size: 1.05em;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .form-card-header i { color: var(--warning); }
    .form-card-body {
        padding: 25px;
    }

    /* FORM ELEMENTS */
    .form-label {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 8px;
        font-size: 0.9em;
    }
    .form-control, .form-select {
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.95em;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--info);
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: var(--accent);
    }
    .form-control:disabled, .form-select:disabled {
        background-color: var(--gray-100);
    }
    .invalid-feedback {
        color: var(--accent);
        font-weight: 500;
    }

    /* INFO BOX */
    .info-box {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.08) 0%, rgba(0, 191, 142, 0.05) 100%);
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid var(--info);
        margin-bottom: 20px;
    }
    .info-box-title {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .info-box-title i { color: var(--info); }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed var(--gray-200);
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: var(--gray-600); font-weight: 500; }
    .info-value { font-weight: 700; color: var(--gray-800); }
    .info-value.success { color: var(--success); }
    .info-value.danger { color: var(--accent); }

    /* PRECIO BOX */
    .precio-box {
        background: linear-gradient(135deg, rgba(0, 191, 142, 0.05) 0%, rgba(67, 97, 238, 0.05) 100%);
        border: 2px solid var(--success);
        border-radius: 16px;
        padding: 20px;
        margin-top: 15px;
    }
    .precio-box-title {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .precio-box-title i { color: var(--success); }
    .precio-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px dashed var(--gray-200);
    }
    .precio-row:last-child {
        border-bottom: none;
        padding-top: 15px;
        margin-top: 10px;
        border-top: 2px solid var(--success);
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
        font-size: 1.5em;
        color: var(--success);
    }

    /* BUTTONS */
    .btn-custom {
        border-radius: 10px;
        padding: 12px 28px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-custom-warning {
        background: var(--warning);
        color: white;
        border: none;
    }
    .btn-custom-warning:hover {
        background: #d99500;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(240, 165, 0, 0.3);
    }
    .btn-custom-outline {
        background: transparent;
        border: 2px solid var(--gray-200);
        color: var(--gray-600);
    }
    .btn-custom-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }
    .btn-custom-danger {
        background: var(--accent);
        color: white;
        border: none;
    }
    .btn-custom-danger:hover {
        background: #d73a55;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(233, 69, 96, 0.3);
    }

    /* ACTIONS BAR */
    .actions-bar {
        background: white;
        border-radius: 16px;
        padding: 20px 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* ALERT */
    .alert-custom {
        border-radius: 12px;
        padding: 16px 20px;
        border: none;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    .alert-custom.danger {
        background: rgba(233, 69, 96, 0.12);
        color: var(--accent);
    }
    .alert-custom.success {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
    }
    .alert-custom.warning {
        background: rgba(240, 165, 0, 0.12);
        color: var(--warning);
    }

    /* CLIENT PREVIEW */
    .client-preview {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: var(--gray-100);
        border-radius: 12px;
    }
    .client-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1em;
    }
    .client-info h5 {
        margin: 0;
        font-weight: 700;
        color: var(--gray-800);
    }
    .client-info p {
        margin: 0;
        color: var(--gray-600);
        font-size: 0.9em;
    }
</style>
@stop

@section('content')
<div class="container-fluid py-4">
    
    {{-- HERO HEADER --}}
    <div class="hero-header">
        <div class="hero-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="hero-title">
                        <i class="fas fa-edit me-2"></i>
                        Editar Pago #{{ substr($pago->uuid, 0, 8) }}
                    </h1>
                    <p class="hero-subtitle mb-0">
                        Modifica los detalles del pago registrado
                    </p>
                </div>
                <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver al Detalle
                </a>
            </div>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if ($errors->any())
        <div class="alert-custom danger">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Error:</strong>
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-custom danger">
            <i class="fas fa-exclamation-triangle"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert-custom success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.pagos.update', $pago) }}" method="POST" id="formEditPago">
        @csrf
        @method('PUT')
        <input type="hidden" name="form_submit_token" value="{{ uniqid() }}">
        <input type="hidden" name="id_inscripcion" value="{{ $pago->id_inscripcion }}">

        <div class="row">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">
                
                {{-- INFORMACIÓN DE LA INSCRIPCIÓN --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-file-contract"></i>
                        Inscripción Asociada
                    </div>
                    <div class="form-card-body">
                        @if($pago->inscripcion && $pago->inscripcion->cliente)
                            @php
                                $cliente = $pago->inscripcion->cliente;
                                $user = $cliente->user ?? null;
                                $iniciales = '';
                                if ($user && $user->name) {
                                    $palabras = explode(' ', $user->name);
                                    foreach($palabras as $palabra) {
                                        $iniciales .= strtoupper(substr($palabra, 0, 1));
                                    }
                                    $iniciales = substr($iniciales, 0, 2);
                                }
                            @endphp
                            <div class="client-preview mb-3">
                                <div class="client-avatar">{{ $iniciales ?: 'CL' }}</div>
                                <div class="client-info">
                                    <h5>{{ $user->name ?? 'Cliente' }}</h5>
                                    <p>{{ $pago->inscripcion->membresia->nombre ?? 'Sin membresía' }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="info-box">
                            <div class="info-box-title">
                                <i class="fas fa-info-circle"></i>
                                Información de la Inscripción
                            </div>
                            @php
                                $montoTotal = $pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base ?? 0;
                                $totalPagado = $pago->inscripcion->pagos()->sum('monto_abonado') ?? 0;
                                $saldoPendiente = $montoTotal - $totalPagado;
                            @endphp
                            <div class="info-row">
                                <span class="info-label">Monto Total Inscripción:</span>
                                <span class="info-value">${{ number_format($montoTotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Total Pagado (todos los pagos):</span>
                                <span class="info-value success">${{ number_format($totalPagado, 0, ',', '.') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Saldo Pendiente:</span>
                                <span class="info-value {{ $saldoPendiente > 0 ? 'danger' : 'success' }}">
                                    ${{ number_format($saldoPendiente, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DETALLES DEL PAGO --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-money-bill-wave"></i>
                        Detalles del Pago
                    </div>
                    <div class="form-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="monto_abonado" class="form-label">
                                    Monto Abonado <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="1" min="0" 
                                           class="form-control @error('monto_abonado') is-invalid @enderror" 
                                           name="monto_abonado" id="monto_abonado"
                                           value="{{ old('monto_abonado', intval($pago->monto_abonado)) }}"
                                           required>
                                </div>
                                @error('monto_abonado')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="id_metodo_pago" class="form-label">
                                    Método de Pago <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('id_metodo_pago') is-invalid @enderror" 
                                        name="id_metodo_pago" id="id_metodo_pago" required>
                                    <option value="">Seleccione método...</option>
                                    @foreach($metodos_pago as $metodo)
                                        <option value="{{ $metodo->id }}" 
                                                {{ old('id_metodo_pago', $pago->id_metodo_pago) == $metodo->id ? 'selected' : '' }}>
                                            {{ $metodo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_metodo_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_pago" class="form-label">
                                    Fecha de Pago <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                       name="fecha_pago" id="fecha_pago"
                                       value="{{ old('fecha_pago', $pago->fecha_pago ? $pago->fecha_pago->format('Y-m-d') : '') }}" 
                                       max="{{ date('Y-m-d') }}" required>
                                @error('fecha_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="referencia_pago" class="form-label">
                                    Referencia / Comprobante
                                </label>
                                <input type="text" class="form-control @error('referencia_pago') is-invalid @enderror" 
                                       name="referencia_pago" id="referencia_pago"
                                       value="{{ old('referencia_pago', $pago->referencia_pago) }}"
                                       placeholder="Ej: N° Transferencia, Recibo...">
                                @error('referencia_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="observaciones" class="form-label">
                                    Observaciones
                                </label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          name="observaciones" id="observaciones" rows="3"
                                          placeholder="Notas adicionales sobre el pago...">{{ old('observaciones', $pago->observaciones) }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN - RESUMEN --}}
            <div class="col-lg-4">
                <div class="form-card" style="position: sticky; top: 20px;">
                    <div class="form-card-header">
                        <i class="fas fa-calculator"></i>
                        Resumen del Pago
                    </div>
                    <div class="form-card-body">
                        <div class="precio-box">
                            <div class="precio-box-title">
                                <i class="fas fa-receipt"></i>
                                Detalle de Montos
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">UUID:</span>
                                <span class="precio-valor" style="font-family: monospace; font-size: 0.85em;">
                                    {{ substr($pago->uuid, 0, 8) }}...
                                </span>
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">Monto Original:</span>
                                <span class="precio-valor">${{ number_format($pago->monto_total, 0, ',', '.') }}</span>
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">Abonado Actual:</span>
                                <span class="precio-valor" style="color: var(--info);">
                                    ${{ number_format($pago->monto_abonado, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">Pendiente:</span>
                                <span class="precio-valor" style="color: {{ $pago->monto_pendiente > 0 ? 'var(--accent)' : 'var(--success)' }};">
                                    ${{ number_format($pago->monto_pendiente, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">Estado:</span>
                                <span class="precio-valor">{{ $pago->estado->nombre ?? 'Sin estado' }}</span>
                            </div>
                        </div>

                        <div class="alert-custom warning mt-3">
                            <i class="fas fa-info-circle"></i>
                            <small>Los cambios en el monto afectarán el saldo de la inscripción asociada.</small>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-custom btn-custom-warning w-100">
                                <i class="fas fa-save"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

    {{-- ACTIONS BAR --}}
    <div class="actions-bar mt-4">
        <div>
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-custom btn-custom-outline">
                <i class="fas fa-list"></i>
                Ver Todos los Pagos
            </a>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-custom btn-custom-outline">
                <i class="fas fa-eye"></i>
                Ver Detalle
            </a>
            <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('¿Estás seguro de eliminar este pago? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-custom btn-custom-danger">
                    <i class="fas fa-trash"></i>
                    Eliminar Pago
                </button>
            </form>
        </div>
    </div>

</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Función para formatear números
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    @php
        $montoTotal = $pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base ?? 0;
        $totalPagadoOtros = $pago->inscripcion->pagos()->where('id', '!=', $pago->id)->sum('monto_abonado') ?? 0;
        $maxPermitido = $montoTotal - $totalPagadoOtros;
    @endphp
    
    const montoMaximo = {{ intval($maxPermitido) }};
    const montoOriginal = {{ intval($pago->monto_abonado) }};

    // Validar monto en tiempo real
    $('#monto_abonado').on('input', function() {
        const monto = parseFloat($(this).val()) || 0;
        
        if (monto > montoMaximo) {
            $(this).addClass('is-invalid');
            Swal.fire({
                icon: 'warning',
                title: 'Monto excede el máximo',
                html: `El monto máximo permitido es <strong>$${formatNumber(montoMaximo)}</strong>`,
                confirmButtonColor: '#e94560'
            });
            $(this).val(montoMaximo);
        } else if (monto <= 0) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validación antes de enviar con SweetAlert
    $('#formEditPago').on('submit', function(e) {
        e.preventDefault();
        
        const monto = parseFloat($('#monto_abonado').val()) || 0;
        const metodoPago = $('#id_metodo_pago option:selected').text();
        
        if (monto <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Monto inválido',
                text: 'El monto debe ser mayor a $0',
                confirmButtonColor: '#e94560'
            });
            return false;
        }
        
        if (!$('#id_metodo_pago').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Método requerido',
                text: 'Debes seleccionar un método de pago',
                confirmButtonColor: '#e94560'
            });
            return false;
        }
        
        // Mostrar confirmación
        Swal.fire({
            title: '¿Confirmar cambios?',
            html: `
                <div style="text-align: left; padding: 10px 0;">
                    <p><strong>💵 Nuevo monto:</strong> <span style="color: #00bf8e;">$${formatNumber(monto)}</span></p>
                    <p><strong>💳 Método:</strong> ${metodoPago}</p>
                    ${monto !== montoOriginal ? `<p style="color: #f0a500;"><i class="fas fa-exclamation-triangle"></i> El monto cambió de $${formatNumber(montoOriginal)} a $${formatNumber(monto)}</p>` : ''}
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-1"></i> Guardar cambios',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancelar',
            confirmButtonColor: '#f0a500',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Guardando cambios...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                this.submit();
            }
        });
    });
});
</script>
@stop
