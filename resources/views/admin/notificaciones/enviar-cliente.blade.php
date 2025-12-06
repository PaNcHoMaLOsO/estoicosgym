@extends('adminlte::page')

@section('title', 'Enviar Notificación a Cliente')

@section('content_header')
    <h1><i class="fas fa-envelope"></i> Enviar Notificación a Cliente</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title"><i class="fas fa-search"></i> Paso 1: Buscar Cliente</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Buscar por nombre, RUT, email o celular</label>
                                <div class="input-group">
                                    <input type="text" id="buscar-cliente" class="form-control" placeholder="Ej: Juan Pérez, 12.345.678-9, juan@email.com">
                                    <div class="input-group-append">
                                        <button class="btn btn-info" type="button" id="btn-buscar">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="resultados-busqueda" style="display: none;">
                        <h5 class="mt-3"><i class="fas fa-users"></i> Resultados</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>RUT</th>
                                        <th>Email</th>
                                        <th>Membresía</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-clientes">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: Seleccionar Plantilla (oculto inicialmente) -->
            <div class="card" id="card-plantilla" style="display: none;">
                <div class="card-header bg-success">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Paso 2: Seleccionar Plantilla</h3>
                </div>
                <div class="card-body">
                    <div id="info-cliente" class="alert alert-info">
                        <strong><i class="fas fa-user"></i> Cliente seleccionado:</strong>
                        <span id="cliente-nombre"></span> | <span id="cliente-email"></span>
                    </div>

                    <div class="form-group">
                        <label>Seleccionar plantilla de notificación</label>
                        <select id="plantilla-select" class="form-control">
                            <option value="">-- Seleccione una plantilla --</option>
                            @foreach($plantillas as $plantilla)
                                <option value="{{ $plantilla->id }}">
                                    {{ $plantilla->nombre }} - {{ $plantilla->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nota personalizada (opcional)</label>
                        <textarea id="nota-personalizada" class="form-control" rows="3" 
                                  placeholder="Agregue una nota adicional si lo desea..."></textarea>
                        <small class="text-muted">Esta nota se agregará al final del email</small>
                    </div>

                    <button class="btn btn-primary" id="btn-preview">
                        <i class="fas fa-eye"></i> Vista Previa
                    </button>
                </div>
            </div>

            <!-- PASO 3: Vista Previa y Enviar (oculto inicialmente) -->
            <div class="card" id="card-preview" style="display: none;">
                <div class="card-header bg-warning">
                    <h3 class="card-title"><i class="fas fa-paper-plane"></i> Paso 3: Confirmar y Enviar</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Destinatario:</strong> <span id="preview-email"></span></p>
                            <p><strong>Asunto:</strong> <span id="preview-asunto"></span></p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h5>Vista previa del email:</h5>
                        <div id="preview-contenido" style="border: 1px solid #ddd; padding: 20px; background: #f9f9f9; max-height: 500px; overflow-y: auto;">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-success btn-lg" id="btn-enviar">
                            <i class="fas fa-paper-plane"></i> Enviar Notificación
                        </button>
                        <button class="btn btn-secondary" id="btn-cancelar">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .cliente-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .cliente-item:hover {
        background-color: #f0f0f0;
    }
</style>
@stop

@section('js')
<script>
let clienteSeleccionado = null;

$(document).ready(function() {
    // Buscar cliente al presionar Enter
    $('#buscar-cliente').on('keypress', function(e) {
        if (e.which === 13) {
            $('#btn-buscar').click();
        }
    });

    // Buscar cliente
    $('#btn-buscar').on('click', function() {
        const buscar = $('#buscar-cliente').val().trim();
        
        if (!buscar) {
            Swal.fire('Error', 'Debe ingresar un criterio de búsqueda', 'error');
            return;
        }

        $.ajax({
            url: '{{ route("admin.notificaciones.buscar-cliente") }}',
            method: 'POST',
            data: {
                buscar: buscar,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success && response.clientes.length > 0) {
                    mostrarResultados(response.clientes);
                } else {
                    Swal.fire('Sin resultados', 'No se encontraron clientes con ese criterio', 'info');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al buscar clientes', 'error');
            }
        });
    });

    // Seleccionar cliente
    $(document).on('click', '.btn-seleccionar-cliente', function() {
        clienteSeleccionado = {
            id: $(this).data('id'),
            nombre: $(this).data('nombre'),
            email: $(this).data('email')
        };

        $('#cliente-nombre').text(clienteSeleccionado.nombre);
        $('#cliente-email').text(clienteSeleccionado.email);
        $('#card-plantilla').slideDown();
        $('html, body').animate({
            scrollTop: $('#card-plantilla').offset().top - 20
        }, 500);
    });

    // Vista previa
    $('#btn-preview').on('click', function() {
        const plantillaId = $('#plantilla-select').val();
        
        if (!plantillaId) {
            Swal.fire('Error', 'Debe seleccionar una plantilla', 'error');
            return;
        }

        $.ajax({
            url: '{{ route("admin.notificaciones.preview") }}',
            method: 'POST',
            data: {
                cliente_id: clienteSeleccionado.id,
                plantilla_id: plantillaId,
                nota_personalizada: $('#nota-personalizada').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#preview-email').text(response.email_destino);
                    $('#preview-asunto').text(response.asunto);
                    $('#preview-contenido').html(response.contenido);
                    $('#card-preview').slideDown();
                    $('html, body').animate({
                        scrollTop: $('#card-preview').offset().top - 20
                    }, 500);
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al generar vista previa', 'error');
            }
        });
    });

    // Enviar notificación
    $('#btn-enviar').on('click', function() {
        Swal.fire({
            title: '¿Confirmar envío?',
            text: 'Se enviará el email a ' + clienteSeleccionado.email,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                enviarNotificacion();
            }
        });
    });

    // Cancelar
    $('#btn-cancelar').on('click', function() {
        location.reload();
    });
});

function mostrarResultados(clientes) {
    let html = '';
    clientes.forEach(function(cliente) {
        html += `
            <tr class="cliente-item">
                <td>${cliente.nombre_completo}</td>
                <td>${cliente.run_pasaporte}</td>
                <td>${cliente.email}</td>
                <td>${cliente.membresia}</td>
                <td>${cliente.estado_membresia}</td>
                <td>
                    <button class="btn btn-sm btn-primary btn-seleccionar-cliente" 
                            data-id="${cliente.id}"
                            data-nombre="${cliente.nombre_completo}"
                            data-email="${cliente.email}">
                        <i class="fas fa-check"></i> Seleccionar
                    </button>
                </td>
            </tr>
        `;
    });
    
    $('#tabla-clientes').html(html);
    $('#resultados-busqueda').slideDown();
}

function enviarNotificacion() {
    const plantillaId = $('#plantilla-select').val();
    
    $.ajax({
        url: '{{ route("admin.notificaciones.enviar-individual") }}',
        method: 'POST',
        data: {
            cliente_id: clienteSeleccionado.id,
            plantilla_id: plantillaId,
            nota_personalizada: $('#nota-personalizada').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Enviado!',
                    text: response.message,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = '{{ route("admin.notificaciones.index") }}';
                });
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Error al enviar la notificación';
            Swal.fire('Error', message, 'error');
        }
    });
}
</script>
@stop
