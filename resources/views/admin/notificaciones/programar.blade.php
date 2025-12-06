@extends('adminlte::page')

@section('title', 'Programar Notificación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">
            <i class="fas fa-calendar-plus mr-2 text-warning"></i>
            Programar Notificación
        </h1>
        <a href="{{ route('admin.notificaciones.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <!-- Formulario Principal -->
            <form action="{{ route('admin.notificaciones.guardar-programada') }}" method="POST" id="formProgramar">
                @csrf
                
                <!-- Card Destinatarios -->
                <div class="card shadow-sm mb-4" style="border-radius: 15px; border: none;">
                    <div class="card-header" style="background: linear-gradient(135deg, #f0a500 0%, #e09400 100%); border-radius: 15px 15px 0 0; padding: 20px;">
                        <h5 class="mb-0 text-white font-weight-bold">
                            <i class="fas fa-users mr-2"></i>
                            1. Seleccionar Destinatarios
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Tipo de Envío</label>
                            <select name="tipo_envio" id="tipo_envio" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <option value="todos">Todos los clientes activos</option>
                                <option value="membresia">Por tipo de membresía</option>
                                <option value="estado">Por estado de membresía</option>
                                <option value="individual">Cliente individual</option>
                            </select>
                        </div>

                        <!-- Filtros Condicionales -->
                        <div id="filtro-membresia" class="form-group" style="display: none;">
                            <label>Membresía</label>
                            <select name="id_membresia" class="form-control">
                                <option value="">Todas</option>
                                @foreach(\App\Models\Membresia::where('activo', true)->get() as $membresia)
                                    <option value="{{ $membresia->id }}">{{ $membresia->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="filtro-estado" class="form-group" style="display: none;">
                            <label>Estado</label>
                            <select name="id_estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="100">Activos</option>
                                <option value="200">Por Vencer (próximos 7 días)</option>
                                <option value="300">Vencidos</option>
                                <option value="400">Pausados</option>
                            </select>
                        </div>

                        <div id="filtro-individual" class="form-group" style="display: none;">
                            <label>Buscar Cliente</label>
                            <input type="text" id="buscar-cliente" class="form-control" placeholder="Nombre, RUT o email...">
                            <div id="resultados-clientes" class="mt-2"></div>
                            <input type="hidden" name="id_cliente" id="id_cliente">
                        </div>

                        <!-- Contador de Destinatarios -->
                        <div id="contador-destinatarios" class="alert alert-info mt-3" style="display: none;">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong id="num-destinatarios">0</strong> destinatarios seleccionados
                        </div>
                    </div>
                </div>

                <!-- Card Contenido -->
                <div class="card shadow-sm mb-4" style="border-radius: 15px; border: none;">
                    <div class="card-header" style="background: linear-gradient(135deg, #4361ee 0%, #3451d4 100%); border-radius: 15px 15px 0 0; padding: 20px;">
                        <h5 class="mb-0 text-white font-weight-bold">
                            <i class="fas fa-edit mr-2"></i>
                            2. Contenido del Mensaje
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Plantilla Base</label>
                            <select name="id_tipo_notificacion" id="id_tipo_notificacion" class="form-control" required>
                                <option value="">Seleccione una plantilla...</option>
                                @foreach(\App\Models\TipoNotificacion::where('activo', true)->get() as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">La plantilla se adaptará automáticamente según cada cliente</small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Asunto del Email</label>
                            <input type="text" name="asunto_custom" id="asunto_custom" class="form-control" placeholder="Dejar vacío para usar asunto de la plantilla">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Mensaje Adicional (Opcional)</label>
                            <textarea name="mensaje_adicional" class="form-control" rows="4" placeholder="Texto adicional que se agregará al email..."></textarea>
                            <small class="text-muted">Este mensaje aparecerá al inicio del email</small>
                        </div>
                    </div>
                </div>

                <!-- Card Programación -->
                <div class="card shadow-sm mb-4" style="border-radius: 15px; border: none;">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0; padding: 20px;">
                        <h5 class="mb-0 text-white font-weight-bold">
                            <i class="fas fa-clock mr-2"></i>
                            3. Fecha y Hora de Envío
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Fecha de Envío</label>
                                    <input type="date" name="fecha_programada" id="fecha_programada" class="form-control" 
                                           min="{{ now()->format('Y-m-d') }}" 
                                           max="{{ now()->addMonths(3)->format('Y-m-d') }}" 
                                           required>
                                    <small class="text-muted">Máximo 3 meses adelante</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Hora de Envío</label>
                                    <input type="time" name="hora_programada" id="hora_programada" class="form-control" value="09:00" required>
                                    <small class="text-muted">Hora sugerida: 09:00 - 18:00</small>
                                </div>
                            </div>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="enviar_ahora" name="enviar_ahora">
                            <label class="custom-control-label" for="enviar_ahora">
                                <strong>Enviar inmediatamente</strong> (ignorar programación)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="text-right mb-4">
                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="window.history.back()">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); color: white; border: none;">
                        <i class="fas fa-check-circle mr-2"></i> Programar Notificación
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar con Validaciones -->
        <div class="col-xl-4 col-lg-5">
            <!-- Card Límites -->
            <div class="card shadow-sm mb-4" style="border-radius: 15px; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%); border-radius: 15px 15px 0 0; padding: 20px;">
                    <h5 class="mb-0 text-white font-weight-bold">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Límites de Envío
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Enviados Hoy</span>
                            <strong id="enviados-hoy">{{ \App\Models\Notificacion::whereDate('created_at', today())->count() }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, (\App\Models\Notificacion::whereDate('created_at', today())->count() / 500) * 100) }}%"></div>
                        </div>
                        <small class="text-muted">Límite diario: 500 notificaciones</small>
                    </div>

                    <hr>

                    <h6 class="font-weight-bold mb-3">Validaciones Activas:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Máximo 3 envíos por cliente al día</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Intervalo mínimo: 2 horas entre envíos</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>No enviar a clientes desactivados</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Validar emails válidos</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Respetar autorización de apoderados</small>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Card Ayuda -->
            <div class="card shadow-sm" style="border-radius: 15px; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%); border-radius: 15px 15px 0 0; padding: 20px;">
                    <h5 class="mb-0 text-white font-weight-bold">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Consejos
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            <small>Los emails a menores se envían al apoderado automáticamente</small>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            <small>Usa plantillas existentes para mantener consistencia</small>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            <small>Programa envíos en horario laboral (9AM-6PM)</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            <small>Revisa el historial para evitar duplicados</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Mostrar/ocultar filtros según tipo de envío
    $('#tipo_envio').on('change', function() {
        const tipo = $(this).val();
        
        $('#filtro-membresia, #filtro-estado, #filtro-individual').hide();
        
        if (tipo === 'membresia') {
            $('#filtro-membresia').show();
        } else if (tipo === 'estado') {
            $('#filtro-estado').show();
        } else if (tipo === 'individual') {
            $('#filtro-individual').show();
        }
        
        if (tipo && tipo !== 'individual') {
            actualizarContador();
        }
    });

    // Buscar cliente individual
    let buscarTimeout;
    $('#buscar-cliente').on('input', function() {
        clearTimeout(buscarTimeout);
        const query = $(this).val();
        
        if (query.length < 3) {
            $('#resultados-clientes').html('');
            return;
        }
        
        buscarTimeout = setTimeout(() => {
            $.get('{{ route("admin.notificaciones.buscar-cliente") }}', { query }, function(clientes) {
                let html = '<div class="list-group">';
                clientes.forEach(cliente => {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action seleccionar-cliente" data-id="${cliente.id}" data-nombre="${cliente.nombre_completo}">
                            <strong>${cliente.nombre_completo}</strong><br>
                            <small class="text-muted">${cliente.email || 'Sin email'} | ${cliente.run_pasaporte || 'Sin RUT'}</small>
                        </a>
                    `;
                });
                html += '</div>';
                $('#resultados-clientes').html(html);
            });
        }, 500);
    });

    // Seleccionar cliente
    $(document).on('click', '.seleccionar-cliente', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        $('#id_cliente').val(id);
        $('#buscar-cliente').val(nombre);
        $('#resultados-clientes').html('<div class="alert alert-success mt-2"><i class="fas fa-check mr-2"></i>Cliente seleccionado</div>');
        $('#contador-destinatarios').show().find('#num-destinatarios').text('1');
    });

    // Actualizar contador de destinatarios
    function actualizarContador() {
        const tipo = $('#tipo_envio').val();
        const data = {
            tipo_envio: tipo,
            id_membresia: $('#id_membresia').val(),
            id_estado: $('#id_estado').val()
        };
        
        $.get('{{ route("admin.notificaciones.contar-destinatarios") }}', data, function(response) {
            $('#contador-destinatarios').show().find('#num-destinatarios').text(response.count);
        });
    }

    // Toggle enviar ahora
    $('#enviar_ahora').on('change', function() {
        if ($(this).is(':checked')) {
            $('#fecha_programada, #hora_programada').prop('disabled', true).prop('required', false);
        } else {
            $('#fecha_programada, #hora_programada').prop('disabled', false).prop('required', true);
        }
    });

    // Validación antes de enviar
    $('#formProgramar').on('submit', function(e) {
        const numDestinatarios = parseInt($('#num-destinatarios').text());
        
        if (numDestinatarios === 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Sin Destinatarios',
                text: 'Debe seleccionar al menos un destinatario',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
        
        if (numDestinatarios > 500) {
            e.preventDefault();
            Swal.fire({
                title: 'Demasiados Destinatarios',
                text: 'El límite máximo es 500 destinatarios por envío',
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
        
        // Confirmación
        e.preventDefault();
        Swal.fire({
            title: '¿Confirmar Programación?',
            html: `
                <p>Se programará el envío de <strong>${numDestinatarios}</strong> notificaciones</p>
                <p class="text-muted small">Las notificaciones se procesarán según la fecha programada</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, Programar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#00bf8e',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>
@stop
