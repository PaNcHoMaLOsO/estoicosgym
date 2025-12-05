@extends('adminlte::page')

@section('title', 'Renovar Membresía - EstóicosGym')

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-50: #fafbfc;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --shadow-lg: 0 8px 32px rgba(0,0,0,0.16);
        --radius-md: 12px;
        --radius-lg: 16px;
    }

    .renovacion-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .header-renovacion {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: var(--radius-lg);
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        color: white;
        text-align: center;
    }

    .header-renovacion h2 {
        margin: 0 0 0.5rem 0;
        font-weight: 700;
    }

    .header-renovacion h2 i {
        color: var(--success);
        margin-right: 0.5rem;
    }

    .header-renovacion p {
        margin: 0;
        opacity: 0.8;
    }

    .info-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .info-card-header {
        background: linear-gradient(135deg, var(--info) 0%, #3651d4 100%);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 600;
    }

    .info-card-header i {
        margin-right: 0.5rem;
    }

    .info-card-body {
        padding: 1.25rem;
    }

    .inscripcion-anterior {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    @media (max-width: 992px) {
        .inscripcion-anterior {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .inscripcion-anterior {
            grid-template-columns: 1fr;
        }
    }

    .info-item {
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--radius-md);
        border-left: 3px solid var(--info);
    }

    .info-item label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--gray-600);
        font-weight: 600;
        display: block;
        margin-bottom: 0.25rem;
    }

    .info-item span {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--gray-800);
    }

    .form-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .form-card-header {
        background: linear-gradient(135deg, var(--success) 0%, var(--success) 100%);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 600;
    }

    .form-card-header i {
        margin-right: 0.5rem;
    }

    .form-card-body {
        padding: 1.25rem 1.5rem;
    }

    .form-row-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .form-row-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }

    @media (max-width: 768px) {
        .form-row-grid,
        .form-row-grid-3 {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-group label i {
        color: var(--info);
        margin-right: 0.5rem;
    }

    .form-control {
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        transition: all 0.2s;
        font-size: 1rem;
        background-color: #fff;
        color: var(--gray-800);
    }

    .form-control:focus {
        border-color: var(--info);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        outline: none;
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
        font-size: 1rem;
        line-height: 1.4;
        min-height: 48px;
    }

    select.form-control option {
        padding: 12px;
        font-size: 1rem;
        color: var(--gray-800);
    }

    .precio-box {
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        border: 2px solid var(--success);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        margin-top: 1rem;
    }

    .precio-box h5 {
        color: var(--success);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .precio-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px dashed var(--gray-200);
    }

    .precio-row:last-child {
        border-bottom: none;
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 2px solid var(--success);
    }

    .precio-label {
        color: var(--gray-600);
    }

    .precio-valor {
        font-weight: 600;
    }

    .precio-total .precio-valor {
        color: var(--success);
        font-size: 1.5rem;
    }

    .btn-renovar {
        background: linear-gradient(135deg, var(--success) 0%, #00a67d 100%);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: var(--radius-md);
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-renovar:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 191, 142, 0.4);
    }

    .btn-cancelar {
        background: var(--gray-200);
        color: var(--gray-800);
        border: none;
        padding: 1rem 2rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancelar:hover {
        background: var(--gray-300);
    }

    .buttons-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 2px solid var(--gray-100);
    }

    .tipo-pago-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .tipo-pago-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .tipo-pago-container {
            grid-template-columns: 1fr;
        }
    }

    .tipo-pago-option {
        position: relative;
    }

    .tipo-pago-option input {
        position: absolute;
        opacity: 0;
    }

    .tipo-pago-option label {
        display: block;
        padding: 1rem;
        text-align: center;
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all 0.2s;
    }

    .tipo-pago-option input:checked + label {
        border-color: var(--info);
        background: rgba(67, 97, 238, 0.1);
    }

    .tipo-pago-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--info);
    }

    .seccion-pago {
        display: none;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--radius-md);
        margin-top: 1rem;
    }

    .seccion-pago.active {
        display: block;
    }

    .alert-info-renovacion {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0.05) 100%);
        border: 1px solid rgba(67, 97, 238, 0.3);
        border-radius: var(--radius-md);
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .alert-info-renovacion i {
        color: var(--info);
        font-size: 1.25rem;
    }

    .alert-info-renovacion strong {
        color: var(--info);
    }
</style>
@stop

@section('content')
<div class="renovacion-container">
    <!-- Header -->
    <div class="header-renovacion">
        <h2><i class="fas fa-sync-alt"></i> Renovar Membresía</h2>
        <p>Renueva la membresía de {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</p>
    </div>

    <!-- Información de inscripción anterior -->
    <div class="info-card">
        <div class="info-card-header">
            <i class="fas fa-history"></i> Inscripción Actual (a renovar)
        </div>
        <div class="info-card-body">
            <div class="inscripcion-anterior">
                <div class="info-item">
                    <label>Cliente</label>
                    <span>{{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</span>
                </div>
                <div class="info-item">
                    <label>Membresía</label>
                    <span>{{ $inscripcion->membresia->nombre }}</span>
                </div>
                <div class="info-item">
                    <label>Fecha Vencimiento</label>
                    <span>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <label>Estado</label>
                    <span class="badge" style="background: {{ $inscripcion->dias_restantes < 0 ? 'var(--accent)' : 'var(--warning)' }}; color: white; padding: 0.25rem 0.75rem; border-radius: 20px;">
                        {{ $inscripcion->dias_restantes < 0 ? 'Vencida' : 'Por vencer ('.$inscripcion->dias_restantes.' días)' }}
                    </span>
                </div>
                <div class="info-item">
                    <label>Precio Anterior</label>
                    <span>${{ number_format($inscripcion->precio_final, 0, ',', '.') }}</span>
                </div>
                @if($inscripcion->convenio)
                <div class="info-item">
                    <label>Convenio</label>
                    <span>{{ $inscripcion->convenio->nombre }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Alerta informativa -->
    <div class="alert-info-renovacion">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>¿Cómo funciona la renovación?</strong>
            <p class="mb-0" style="font-size: 0.9rem; margin-top: 0.5rem;">
                Se creará una nueva inscripción que comenzará el día siguiente al vencimiento de la actual.
                Puedes cambiar la membresía o mantener la misma. El convenio se puede mantener o cambiar.
            </p>
        </div>
    </div>

    <!-- Formulario de renovación -->
    <form action="{{ route('admin.inscripciones.renovar.store', $inscripcion) }}" method="POST" id="formRenovacion">
        @csrf
        <input type="hidden" name="form_submit_token" value="{{ Str::random(40) }}">

        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-edit"></i> Nueva Membresía
            </div>
            <div class="form-card-body">
                <div class="form-row-grid">
                    <div class="form-group">
                        <label><i class="fas fa-dumbbell"></i> Membresía</label>
                        <select name="id_membresia" id="id_membresia" class="form-control" required>
                            @foreach($membresias as $membresia)
                            <option value="{{ $membresia->id }}" 
                                data-precio="{{ $membresia->precios->where('activo', true)->first()->precio_normal ?? 0 }}"
                                data-duracion="{{ $membresia->duracion_dias }}"
                                {{ $inscripcion->id_membresia == $membresia->id ? 'selected' : '' }}>
                                {{ $membresia->nombre }} - ${{ number_format($membresia->precios->where('activo', true)->first()->precio_normal ?? 0, 0, ',', '.') }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-handshake"></i> Convenio (opcional)</label>
                        <select name="id_convenio" id="id_convenio" class="form-control">
                            <option value="">Sin convenio</option>
                            @foreach($convenios as $convenio)
                            <option value="{{ $convenio->id }}" {{ $inscripcion->id_convenio == $convenio->id ? 'selected' : '' }}>
                                {{ $convenio->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" 
                               value="{{ $datosRenovacion['fecha_inicio_sugerida'] }}" required>
                        <small class="text-muted">Sugerencia: día siguiente al vencimiento</small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-check"></i> Fecha de Término (calculada)</label>
                        <input type="text" id="fecha_termino_display" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-row-grid">
                    <div class="form-group">
                        <label><i class="fas fa-percent"></i> Descuento Adicional ($)</label>
                        <input type="number" name="descuento_aplicado" id="descuento_aplicado" class="form-control" 
                               value="0" min="0" step="1000">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-question-circle"></i> Motivo del Descuento</label>
                        <select name="id_motivo_descuento" id="id_motivo_descuento" class="form-control">
                            <option value="">Seleccionar motivo...</option>
                            @foreach($motivos as $motivo)
                            <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Resumen de Precios -->
                <div class="precio-box">
                    <h5><i class="fas fa-calculator"></i> Resumen de Pago</h5>
                    <div class="precio-row">
                        <span class="precio-label">Precio Base</span>
                        <span class="precio-valor" id="display-precio-base">$0</span>
                    </div>
                    <div class="precio-row" id="row-descuento" style="display: none;">
                        <span class="precio-label">Descuento</span>
                        <span class="precio-valor text-danger" id="display-descuento">-$0</span>
                    </div>
                    <div class="precio-row precio-total">
                        <span class="precio-label"><strong>Total a Pagar</strong></span>
                        <span class="precio-valor" id="display-precio-final">$0</span>
                    </div>
                </div>

                <!-- Tipo de Pago -->
                <h5 style="margin-top: 1.5rem; margin-bottom: 0.75rem;"><i class="fas fa-credit-card"></i> Forma de Pago</h5>
                <div class="tipo-pago-container">
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago" id="tipo_completo" value="completo" checked>
                        <label for="tipo_completo">
                            <div class="tipo-pago-icon"><i class="fas fa-check-circle"></i></div>
                            <strong>Pago Completo</strong>
                            <small style="display:block;color:var(--gray-600);font-size:0.75rem;margin-top:0.25rem;">100% del total</small>
                        </label>
                    </div>
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago" id="tipo_abono" value="abono">
                        <label for="tipo_abono">
                            <div class="tipo-pago-icon"><i class="fas fa-coins"></i></div>
                            <strong>Pago Parcial</strong>
                            <small style="display:block;color:var(--gray-600);font-size:0.75rem;margin-top:0.25rem;">Abono inicial</small>
                        </label>
                    </div>
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago" id="tipo_mixto" value="mixto">
                        <label for="tipo_mixto">
                            <div class="tipo-pago-icon"><i class="fas fa-random"></i></div>
                            <strong>Pago Mixto</strong>
                            <small style="display:block;color:var(--gray-600);font-size:0.75rem;margin-top:0.25rem;">2 métodos</small>
                        </label>
                    </div>
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago" id="tipo_pendiente" value="pendiente">
                        <label for="tipo_pendiente">
                            <div class="tipo-pago-icon"><i class="fas fa-clock"></i></div>
                            <strong>Pendiente</strong>
                            <small style="display:block;color:var(--gray-600);font-size:0.75rem;margin-top:0.25rem;">Pagar después</small>
                        </label>
                    </div>
                </div>

                <!-- Sección de pago simple (completo y parcial) -->
                <div class="seccion-pago active" id="seccion-pago-simple">
                    <div class="form-row-grid-3">
                        <div class="form-group">
                            <label><i class="fas fa-dollar-sign"></i> Monto a Pagar</label>
                            <input type="number" name="monto_abonado" id="monto_abonado" class="form-control" min="0" step="1000" placeholder="$0">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-credit-card"></i> Método de Pago</label>
                            <select name="id_metodo_pago" id="id_metodo_pago" class="form-control">
                                @foreach($metodosPago as $metodo)
                                <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Fecha de Pago</label>
                            <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- Sección de pago mixto -->
                <div class="seccion-pago" id="seccion-pago-mixto">
                    <div class="form-row-grid">
                        <div style="background: rgba(67,97,238,0.05); padding: 1rem; border-radius: var(--radius-md); border: 1px solid rgba(67,97,238,0.2);">
                            <h6 style="color: var(--info); margin-bottom: 0.75rem;"><i class="fas fa-credit-card"></i> Método 1</h6>
                            <div class="form-group">
                                <label>Método de Pago</label>
                                <select name="id_metodo_pago1" id="id_metodo_pago1" class="form-control">
                                    @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Monto</label>
                                <input type="number" name="monto_metodo1" id="monto_metodo1" class="form-control" min="0" step="1000" placeholder="$0">
                            </div>
                        </div>
                        <div style="background: rgba(0,191,142,0.05); padding: 1rem; border-radius: var(--radius-md); border: 1px solid rgba(0,191,142,0.2);">
                            <h6 style="color: var(--success); margin-bottom: 0.75rem;"><i class="fas fa-credit-card"></i> Método 2</h6>
                            <div class="form-group">
                                <label>Método de Pago</label>
                                <select name="id_metodo_pago2" id="id_metodo_pago2" class="form-control">
                                    @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Monto</label>
                                <input type="number" name="monto_metodo2" id="monto_metodo2" class="form-control" min="0" step="1000" placeholder="$0">
                            </div>
                        </div>
                    </div>
                    <div class="form-row-grid" style="margin-top: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label><i class="fas fa-calendar"></i> Fecha de Pago</label>
                            <input type="date" name="fecha_pago_mixto" id="fecha_pago_mixto" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label><i class="fas fa-calculator"></i> Total Mixto</label>
                            <input type="text" id="total_mixto_display" class="form-control" readonly style="background: var(--gray-100); font-weight: 600;">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label><i class="fas fa-sticky-note"></i> Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2" 
                              placeholder="Notas adicionales sobre la renovación..."></textarea>
                </div>

                <!-- Botones -->
                <div class="buttons-container">
                    <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn-cancelar">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-renovar">
                        <i class="fas fa-sync-alt"></i> Renovar Membresía
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let precioBase = 0;
    let precioFinal = 0;

    // Función para formatear números
    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Calcular precios
    function calcularPrecios() {
        const membresiaOption = $('#id_membresia option:selected');
        precioBase = parseFloat(membresiaOption.data('precio')) || 0;
        
        let descuento = parseFloat($('#descuento_aplicado').val()) || 0;
        
        // Validar que el descuento no supere el precio base
        if (descuento > precioBase) {
            descuento = precioBase;
            $('#descuento_aplicado').val(descuento);
            
            Swal.fire({
                title: 'Descuento limitado',
                html: `<p>El descuento no puede superar el precio de la membresía.</p><p>Descuento máximo permitido: <strong>$${formatNumber(precioBase)}</strong></p>`,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f0a500'
            });
        }
        
        // Actualizar el máximo del input
        $('#descuento_aplicado').attr('max', precioBase);
        
        precioFinal = Math.max(0, precioBase - descuento);
        
        $('#display-precio-base').text('$' + formatNumber(precioBase));
        
        if (descuento > 0) {
            $('#row-descuento').show();
            $('#display-descuento').text('-$' + formatNumber(descuento));
        } else {
            $('#row-descuento').hide();
        }
        
        $('#display-precio-final').text('$' + formatNumber(precioFinal));
        
        // Actualizar monto si es pago completo
        if ($('#tipo_completo').is(':checked')) {
            $('#monto_abonado').val(Math.round(precioFinal));
        }
        
        calcularFechaTermino();
    }

    // Calcular fecha de término
    function calcularFechaTermino() {
        const membresiaOption = $('#id_membresia option:selected');
        const duracion = parseInt(membresiaOption.data('duracion')) || 30;
        const fechaInicio = new Date($('#fecha_inicio').val());
        
        if (!isNaN(fechaInicio.getTime())) {
            const fechaTermino = new Date(fechaInicio);
            fechaTermino.setDate(fechaTermino.getDate() + duracion);
            
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            $('#fecha_termino_display').val(fechaTermino.toLocaleDateString('es-CL', options));
        }
    }

    // Eventos
    $('#id_membresia, #descuento_aplicado').on('change input', calcularPrecios);
    $('#fecha_inicio').on('change', calcularFechaTermino);

    // Calcular total mixto
    function calcularTotalMixto() {
        const monto1 = parseFloat($('#monto_metodo1').val()) || 0;
        const monto2 = parseFloat($('#monto_metodo2').val()) || 0;
        const total = monto1 + monto2;
        $('#total_mixto_display').val('$' + formatNumber(total));
        
        // Cambiar color si coincide con precio final
        if (total === precioFinal) {
            $('#total_mixto_display').css({'color': 'var(--success)', 'border-color': 'var(--success)'});
        } else {
            $('#total_mixto_display').css({'color': 'var(--accent)', 'border-color': 'var(--accent)'});
        }
    }

    $('#monto_metodo1, #monto_metodo2').on('input', calcularTotalMixto);

    // Tipo de pago
    $('input[name="tipo_pago"]').on('change', function() {
        const tipo = $(this).val();
        
        // Ocultar todas las secciones
        $('#seccion-pago-simple').removeClass('active');
        $('#seccion-pago-mixto').removeClass('active');
        
        if (tipo === 'pendiente') {
            // No mostrar ninguna sección de pago
            $('#monto_abonado').val(0);
        } else if (tipo === 'mixto') {
            // Mostrar sección de pago mixto
            $('#seccion-pago-mixto').addClass('active');
            // Precargar valores sugeridos (50% cada uno)
            const mitad = Math.round(precioFinal / 2);
            $('#monto_metodo1').val(mitad);
            $('#monto_metodo2').val(precioFinal - mitad);
            calcularTotalMixto();
        } else {
            // Mostrar sección de pago simple
            $('#seccion-pago-simple').addClass('active');
            
            if (tipo === 'completo') {
                $('#monto_abonado').val(Math.round(precioFinal));
            } else {
                // Abono/Parcial
                $('#monto_abonado').val('');
            }
        }
    });

    // Validación antes de enviar
    $('#formRenovacion').on('submit', function(e) {
        const tipo = $('input[name="tipo_pago"]:checked').val();
        
        if (tipo === 'completo' || tipo === 'abono') {
            const monto = parseFloat($('#monto_abonado').val()) || 0;
            const metodo = $('#id_metodo_pago').val();
            
            if (monto <= 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Monto requerido',
                    text: 'Ingresa el monto a pagar o selecciona "Pendiente".',
                    icon: 'warning',
                    confirmButtonColor: '#4361ee'
                });
                return false;
            }
            
            if (tipo === 'abono' && monto >= precioFinal) {
                e.preventDefault();
                Swal.fire({
                    title: 'Monto incorrecto',
                    html: `Para pago parcial, el monto debe ser menor al total.<br>Total: <strong>$${formatNumber(precioFinal)}</strong>`,
                    icon: 'warning',
                    confirmButtonColor: '#4361ee'
                });
                return false;
            }
            
            if (!metodo) {
                e.preventDefault();
                Swal.fire({
                    title: 'Método de pago requerido',
                    text: 'Selecciona un método de pago.',
                    icon: 'warning',
                    confirmButtonColor: '#4361ee'
                });
                return false;
            }
        } else if (tipo === 'mixto') {
            const monto1 = parseFloat($('#monto_metodo1').val()) || 0;
            const monto2 = parseFloat($('#monto_metodo2').val()) || 0;
            const totalMixto = monto1 + monto2;
            const metodo1 = $('#id_metodo_pago1').val();
            const metodo2 = $('#id_metodo_pago2').val();
            
            if (monto1 <= 0 || monto2 <= 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Montos requeridos',
                    text: 'Ambos métodos de pago deben tener un monto mayor a $0.',
                    icon: 'warning',
                    confirmButtonColor: '#4361ee'
                });
                return false;
            }
            
            if (totalMixto !== precioFinal) {
                e.preventDefault();
                Swal.fire({
                    title: 'Montos no coinciden',
                    html: `La suma de los montos debe ser igual al total.<br>Total esperado: <strong>$${formatNumber(precioFinal)}</strong><br>Total ingresado: <strong>$${formatNumber(totalMixto)}</strong>`,
                    icon: 'warning',
                    confirmButtonColor: '#4361ee'
                });
                return false;
            }
            
            if (metodo1 === metodo2) {
                e.preventDefault();
                Swal.fire({
                    title: 'Métodos iguales',
                    text: 'Los métodos de pago deben ser diferentes.',
                    icon: 'warning',
                    confirmButtonColor: '#4361ee'
                });
                return false;
            }
        }
        
        // Confirmación final
        e.preventDefault();
        Swal.fire({
            title: '¿Confirmar renovación?',
            html: `
                <p>Se creará una nueva inscripción para:</p>
                <p><strong>{{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</strong></p>
                <p>Total: <strong>$${formatNumber(precioFinal)}</strong></p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00bf8e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sí, renovar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Inicializar
    calcularPrecios();
});
</script>
@stop
