@extends('adminlte::page')

@section('title', 'Editar Pago - EstóicosGym')

@section('content_header')
    <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-pencil-alt text-primary"></i> Editar Pago</h1>
            <small class="text-muted">ID: <strong>#{{ $pago->id }}</strong> | Última actualización: {{ $pago->updated_at->format('d/m/Y H:i') }}</small>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-info btn-sm">
                <i class="fas fa-arrow-left"></i> Ver Detalles
            </a>
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-list"></i> Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-exclamation-circle"></i> Errores de Validación</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form action="{{ route('admin.pagos.update', $pago) }}" method="POST" id="formEditarPago" autocomplete="off">
        @csrf
        @method('PUT')
        <input type="hidden" name="id_inscripcion" value="{{ $pago->id_inscripcion }}">

        <div class="row">
            <!-- COLUMNA IZQUIERDA - FORMULARIO PRINCIPAL -->
            <div class="col-md-8">

                <!-- CARD: INFORMACIÓN DE INSCRIPCIÓN -->
                <div class="card card-info card-outline mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-circle"></i> Datos de Inscripción</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="text-muted small"><i class="fas fa-user"></i> Cliente</label>
                                    <p class="m-0">
                                        <strong>
                                            <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" target="_blank" class="text-dark">
                                                {{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}
                                                <i class="fas fa-external-link-alt fa-xs"></i>
                                            </a>
                                        </strong>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="text-muted small"><i class="fas fa-dumbbell"></i> Membresía</label>
                                    <p class="m-0">
                                        <strong>{{ $pago->inscripcion->membresia->nombre }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="text-muted small"><i class="fas fa-calendar-alt"></i> Período Membresía</label>
                                <p class="m-0 small">
                                    <strong>Inicio:</strong> {{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }} <br>
                                    <strong>Vence:</strong> {{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small"><i class="fas fa-coins"></i> Valor Membresía</label>
                                <p class="m-0">
                                    <strong class="text-primary">${{ number_format($pago->monto_total, 0, '.', '.') }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD: INFORMACIÓN DE PAGO -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Detalles del Pago</h3>
                    </div>
                    <div class="card-body">

                        <!-- MONTO ABONADO -->
                        <div class="form-group">
                            <label for="monto_abonado" class="font-weight-bold">
                                <i class="fas fa-dollar-sign text-success"></i> Monto Abonado
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-success text-white"><i class="fas fa-peso-sign"></i></span>
                                </div>
                                <input type="number" 
                                       class="form-control form-control-lg font-weight-bold @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" 
                                       name="monto_abonado"
                                       value="{{ old('monto_abonado', $pago->monto_abonado) }}"
                                       step="1000"
                                       min="1"
                                       max="999999999"
                                       required
                                       onchange="actualizarEstado(); recalcularMontoCuota();">
                                @error('monto_abonado')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-1">
                                Anterior: <strong>${{ number_format($pago->monto_abonado, 0, '.', '.') }}</strong> | 
                                Máximo: <strong>${{ number_format($pago->monto_total, 0, '.', '.') }}</strong>
                            </small>
                        </div>

                        <!-- FILA: FECHA Y MÉTODO -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_pago" class="font-weight-bold">
                                        <i class="fas fa-calendar text-info"></i> Fecha del Pago
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control form-control-lg @error('fecha_pago') is-invalid @enderror" 
                                           id="fecha_pago" 
                                           name="fecha_pago"
                                           value="{{ old('fecha_pago', $pago->fecha_pago->format('Y-m-d')) }}"
                                           required>
                                    @error('fecha_pago')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Anterior: {{ $pago->fecha_pago->format('d/m/Y') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_metodo_pago_principal" class="font-weight-bold">
                                        <i class="fas fa-credit-card text-warning"></i> Método de Pago
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-control-lg @error('id_metodo_pago_principal') is-invalid @enderror" 
                                            id="id_metodo_pago_principal" 
                                            name="id_metodo_pago_principal"
                                            style="width: 100%;"
                                            required>
                                        <option value="">-- Seleccionar --</option>
                                        @foreach($metodos_pago as $metodo)
                                            <option value="{{ $metodo->id }}" 
                                                    {{ old('id_metodo_pago_principal', $pago->id_metodo_pago_principal) == $metodo->id ? 'selected' : '' }}>
                                                {{ $metodo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_metodo_pago_principal')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Anterior: {{ $pago->metodoPagoPrincipal?->nombre ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- FILA: CUOTAS Y MONTO POR CUOTA -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cantidad_cuotas" class="font-weight-bold">
                                        <i class="fas fa-list-ol text-danger"></i> Cantidad de Cuotas
                                    </label>
                                    <input type="number" 
                                           class="form-control form-control-lg @error('cantidad_cuotas') is-invalid @enderror" 
                                           id="cantidad_cuotas" 
                                           name="cantidad_cuotas"
                                           value="{{ old('cantidad_cuotas', $pago->cantidad_cuotas ?? 1) }}"
                                           min="1"
                                           max="12"
                                           onchange="recalcularMontoCuota();">
                                    @error('cantidad_cuotas')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Anterior: {{ $pago->cantidad_cuotas ?? 1 }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monto_cuota_display" class="font-weight-bold">
                                        <i class="fas fa-divide text-secondary"></i> Monto por Cuota
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg bg-light" 
                                           id="monto_cuota_display"
                                           placeholder="$0"
                                           readonly>
                                    <small class="text-muted d-block mt-1">Calculado automáticamente</small>
                                </div>
                            </div>
                        </div>

                        <!-- REFERENCIA DE PAGO -->
                        <div class="form-group">
                            <label for="referencia_pago" class="font-weight-bold">
                                <i class="fas fa-barcode text-primary"></i> Referencia/Comprobante
                            </label>
                            <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-paperclip"></i></span>
                                </div>
                                <input type="text" 
                                       class="form-control @error('referencia_pago') is-invalid @enderror" 
                                       id="referencia_pago" 
                                       name="referencia_pago"
                                       value="{{ old('referencia_pago', $pago->referencia_pago) }}"
                                       maxlength="100"
                                       placeholder="TRF-2025-001, BOL-001, CHEQUE123...">
                                @error('referencia_pago')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-1">
                                {{ $pago->referencia_pago ? 'Anterior: ' . $pago->referencia_pago : 'Sin referencia registrada' }}
                            </small>
                        </div>

                        <!-- OBSERVACIONES -->
                        <div class="form-group">
                            <label for="observaciones" class="font-weight-bold">
                                <i class="fas fa-sticky-note text-warning"></i> Observaciones
                            </label>
                            <textarea class="form-control form-control-lg @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" 
                                      name="observaciones"
                                      rows="4"
                                      maxlength="500"
                                      placeholder="Notas adicionales sobre este pago..."
                                      onchange="actualizarContador(); actualizarEstado();">{{ old('observaciones', $pago->observaciones) }}</textarea>
                            <small class="text-muted d-block mt-1">
                                <span id="charCount">{{ strlen($pago->observaciones ?? '') }}</span>/500 caracteres
                            </small>
                            @error('observaciones')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

            </div>

            <!-- COLUMNA DERECHA - PANEL INFORMATIVO -->
            <div class="col-md-4">

                <!-- CARD: ESTADO DEL PAGO -->
                <div class="card card-info card-outline mb-3">
                    <div class="card-header py-2 bg-info">
                        <h5 class="card-title m-0"><i class="fas fa-traffic-light"></i> Estado del Pago</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="badge badge-lg p-3" style="background-color: {{ $pago->estado->color ?? '#6c757d' }}; font-size: 16px;">
                                {{ $pago->estado->nombre }}
                            </span>
                        </div>
                        <hr>
                        <div class="mb-2">
                            <small class="text-muted">Monto Actual</small>
                            <h4 class="text-success mb-0">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</h4>
                        </div>
                        <small class="text-muted d-block">
                            <em>Se asignará automáticamente al guardar</em>
                        </small>
                    </div>
                </div>

                <!-- CARD: RESUMEN FINANCIERO -->
                <div class="card card-warning card-outline mb-3">
                    <div class="card-header py-2 bg-warning">
                        <h5 class="card-title m-0"><i class="fas fa-chart-pie"></i> Resumen Financiero</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Precio Total</small>
                                <strong class="text-primary">${{ number_format($pago->monto_total ?? 0, 0, '.', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Monto a Pagar</small>
                                <strong class="text-success" id="monto_a_pagar_display">${{ number_format($pago->monto_abonado ?? 0, 0, '.', '.') }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                @php
                                    $porcentaje = ($pago->monto_total ?? 0) > 0 ? (($pago->monto_abonado ?? 0) / $pago->monto_total) * 100 : 0;
                                @endphp
                                <div class="progress-bar bg-success" id="progress_bar" style="width: {{ min($porcentaje, 100) }}%"></div>
                            </div>
                            <small class="text-muted d-block mt-1">
                                @php
                                    $porcentajeMostrar = ($pago->monto_total ?? 0) > 0 ? round((($pago->monto_abonado ?? 0) / $pago->monto_total) * 100, 1) : 0;
                                @endphp
                                {{ $porcentajeMostrar }}% de avance
                            </small>
                        </div>
                        <hr>
                        <div class="mb-0">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Saldo Pendiente</small>
                                @php
                                    $saldoPendiente = max(0, ($pago->monto_total ?? 0) - ($pago->monto_abonado ?? 0));
                                @endphp
                                <strong class="{{ $saldoPendiente > 0 ? 'text-warning' : 'text-success' }}" id="saldo_pendiente_display">
                                    ${{ number_format($saldoPendiente, 0, '.', '.') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD: HISTORIAL -->
                <div class="card card-secondary card-outline mb-3">
                    <div class="card-header py-2 bg-secondary">
                        <h5 class="card-title m-0"><i class="fas fa-history"></i> Registro</h5>
                    </div>
                    <div class="card-body small">
                        <div class="mb-2">
                            <strong>Creado:</strong><br>
                            <small class="text-muted">{{ $pago->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <div class="mb-0">
                            <strong>Última Edición:</strong><br>
                            <small class="text-muted">{{ $pago->updated_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                </div>

                <!-- CARD: ACCIONES RÁPIDAS -->
                <div class="card card-light card-outline">
                    <div class="card-header py-2">
                        <h5 class="card-title m-0"><i class="fas fa-rocket"></i> Acciones</h5>
                    </div>
                    <div class="card-body p-2">
                        <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-sm btn-info btn-block mb-2">
                            <i class="fas fa-eye"></i> Ver Detalles
                        </a>
                        <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="btn btn-sm btn-primary btn-block mb-2">
                            <i class="fas fa-clipboard-list"></i> Ver Inscripción
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- BOTONES DE ACCIÓN (FOOTER) -->
        <div class="row mt-3">
            <div class="col-md-12">
                <button type="submit" class="btn btn-success btn-lg" id="btnGuardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>

    </form>

@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .form-control-lg, .input-group-lg > .form-control {
            font-size: 1rem;
        }
        .badge-lg {
            display: inline-block;
            min-width: 180px;
        }
        .card-header {
            border-bottom: 2px solid rgba(0,0,0,.125);
        }
        .input-group-text {
            font-weight: bold;
        }
        #monto_cuota_display {
            font-size: 1.1rem;
            font-weight: bold;
            color: #28a745;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 con tema Bootstrap
            $('#id_metodo_pago_principal').select2({
                theme: 'bootstrap-5',
                width: '100%',
                language: 'es'
            });

            // Establecer máximo en fecha (no puede ser futura)
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('fecha_pago').setAttribute('max', hoy);

            // Calcular monto por cuota al cargar
            recalcularMontoCuota();
            actualizarContador();
        });

        // Función para recalcular monto por cuota
        function recalcularMontoCuota() {
            const monto = parseFloat($('#monto_abonado').val()) || 0;
            const cuotas = parseInt($('#cantidad_cuotas').val()) || 1;
            const montoPorCuota = cuotas > 0 ? monto / cuotas : 0;
            
            $('#monto_cuota_display').val('$' + montoPorCuota.toLocaleString('es-CL', { maximumFractionDigits: 0 }));
        }

        // Función para actualizar estado proyectado
        function actualizarEstado() {
            const monto = parseFloat($('#monto_abonado').val()) || 0;
            const montoTotal = parseFloat('{{ $pago->monto_total }}');
            
            let estado = '';
            if (monto >= montoTotal) {
                estado = 'PAGADO ✓';
            } else if (monto > 0) {
                estado = 'PARCIAL ⏳';
            } else {
                estado = 'PENDIENTE ⏹';
            }
            
            console.log('Estado proyectado: ' + estado);
        }

        // Función para actualizar contador de caracteres
        function actualizarContador() {
            const texto = $('#observaciones').val();
            $('#charCount').text(texto.length);
        }

        // Actualizar dinámicamente los montos mostrados
        $('#monto_abonado').on('input', function() {
            const monto = parseFloat($(this).val()) || 0;
            const montoTotal = parseFloat('{{ $pago->monto_total }}');
            const pendiente = Math.max(0, montoTotal - monto);
            const porcentaje = montoTotal > 0 ? (monto / montoTotal) * 100 : 0;
            
            $('#monto_a_pagar_display').text('$' + monto.toLocaleString('es-CL', { maximumFractionDigits: 0 }));
            $('#saldo_pendiente_display').text('$' + pendiente.toLocaleString('es-CL', { maximumFractionDigits: 0 }));
            $('#progress_bar').css('width', Math.min(porcentaje, 100) + '%');
            
            // Cambiar color del saldo según corresponda
            if (pendiente > 0) {
                $('#saldo_pendiente_display').removeClass('text-success').addClass('text-warning');
            } else {
                $('#saldo_pendiente_display').removeClass('text-warning').addClass('text-success');
            }
            
            recalcularMontoCuota();
            actualizarEstado();
        });

        // Actualizar caracteres en tiempo real
        $('#observaciones').on('input', function() {
            actualizarContador();
        });

        // Validación antes de enviar
        $('#formEditarPago').on('submit', function(e) {
            const monto = parseFloat($('#monto_abonado').val()) || 0;
            const montoTotal = parseFloat('{{ $pago->monto_total ?? 0 }}') || 0;
            const fecha = new Date($('#fecha_pago').val());
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (montoTotal === 0) {
                e.preventDefault();
                alert('⚠️ Error: La membresía no tiene un precio asignado');
                return false;
            }

            if (monto > montoTotal) {
                e.preventDefault();
                alert('⚠️ El monto no puede exceder el precio de la membresía ($' + montoTotal.toLocaleString('es-CL') + ')');
                return false;
            }

            if (monto <= 0) {
                e.preventDefault();
                alert('⚠️ El monto debe ser mayor a $0');
                return false;
            }

            if (fecha > hoy) {
                e.preventDefault();
                alert('⚠️ La fecha de pago no puede ser futura');
                return false;
            }
        });
    </script>
@stop
