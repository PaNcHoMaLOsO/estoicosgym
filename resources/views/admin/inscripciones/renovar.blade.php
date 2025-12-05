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
    }

    .form-control:focus {
        border-color: var(--info);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
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
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 576px) {
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
                        </label>
                    </div>
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago" id="tipo_abono" value="abono">
                        <label for="tipo_abono">
                            <div class="tipo-pago-icon"><i class="fas fa-coins"></i></div>
                            <strong>Abono</strong>
                        </label>
                    </div>
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago" id="tipo_pendiente" value="pendiente">
                        <label for="tipo_pendiente">
                            <div class="tipo-pago-icon"><i class="fas fa-clock"></i></div>
                            <strong>Pendiente</strong>
                        </label>
                    </div>
                </div>

                <!-- Sección de pago (para completo y abono) -->
                <div class="seccion-pago active" id="seccion-pago">
                    <div class="form-row-grid-3">
                        <div class="form-group">
                            <label>Monto a Pagar</label>
                            <input type="number" name="monto_abonado" id="monto_abonado" class="form-control" min="0" step="1000">
                        </div>
                        <div class="form-group">
                            <label>Método de Pago</label>
                            <select name="id_metodo_pago" id="id_metodo_pago" class="form-control">
                                @foreach($metodosPago as $metodo)
                                <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Pago</label>
                            <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" value="{{ date('Y-m-d') }}">
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

    // Tipo de pago
    $('input[name="tipo_pago"]').on('change', function() {
        const tipo = $(this).val();
        
        if (tipo === 'pendiente') {
            $('#seccion-pago').removeClass('active');
            $('#monto_abonado').val(0);
        } else {
            $('#seccion-pago').addClass('active');
            
            if (tipo === 'completo') {
                $('#monto_abonado').val(Math.round(precioFinal));
            } else {
                $('#monto_abonado').val('');
            }
        }
    });

    // Validación antes de enviar
    $('#formRenovacion').on('submit', function(e) {
        const tipo = $('input[name="tipo_pago"]:checked').val();
        
        if (tipo !== 'pendiente') {
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
