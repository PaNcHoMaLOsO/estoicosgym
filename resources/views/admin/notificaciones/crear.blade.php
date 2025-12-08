@extends('adminlte::page')

@section('title', 'Nueva Notificación - Estoicos Gym')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
:root {
    --primary: #1a1a2e;
    --accent: #e94560;
    --success: #00bf8e;
}

.content-wrapper {
    background: #f4f6f9 !important;
}

/* === HEADER === */
.page-header {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 20px;
}

.page-header h1 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
}

.page-header h1 i {
    color: var(--accent);
    margin-right: 10px;
}

/* === INDICADOR DE PASOS === */
.pasos-indicador {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 24px;
    padding: 16px;
    background: white;
    border-radius: 50px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.paso {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    padding: 8px 16px;
    border-radius: 25px;
    transition: all 0.3s;
}

.paso.active {
    background: rgba(233, 69, 96, 0.1);
    color: var(--accent);
}

.paso.completed {
    background: rgba(0, 191, 142, 0.1);
    color: var(--success);
}

.paso-numero {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
}

.paso.active .paso-numero {
    background: var(--accent);
    color: white;
}

.paso.completed .paso-numero {
    background: var(--success);
    color: white;
}

/* === CARDS === */
.card-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 20px;
    overflow: hidden;
}

.card-header-custom {
    background: linear-gradient(135deg, var(--primary) 0%, #2d3e50 100%);
    color: white;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header-custom h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.card-header-custom h3 i {
    color: var(--accent);
    margin-right: 8px;
}

.card-body-custom {
    padding: 24px;
}

/* === PASO 1: TABLA CLIENTES === */
.filtros-clientes {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filtro-btn {
    padding: 8px 16px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
    font-size: 0.9rem;
}

.filtro-btn:hover {
    border-color: var(--accent);
    background: rgba(233, 69, 96, 0.05);
}

.filtro-btn.active {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.clientes-seleccionados {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    margin-top: 16px;
}

.badge-cliente {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--accent);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    margin: 4px;
    font-size: 0.85rem;
}

.badge-cliente .remove-badge {
    cursor: pointer;
    font-weight: bold;
}

/* === PASO 2: PLANTILLAS === */
.plantillas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
}

.plantilla-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.plantilla-card:hover {
    border-color: var(--accent);
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(233, 69, 96, 0.15);
}

.plantilla-card.selected {
    border-color: var(--accent);
    background: rgba(233, 69, 96, 0.05);
}

.plantilla-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(233, 69, 96, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 1.5rem;
    color: var(--accent);
}

.plantilla-nombre {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

/* === PASO 3: PREVISUALIZACIÓN === */
.preview-email {
    max-width: 800px;
    margin: 0 auto;
    font-family: Arial, sans-serif;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.preview-content {
    background: white;
    padding: 20px 30px;
    border-bottom: 2px solid #e9ecef;
}

.preview-asunto {
    color: #1a1a2e;
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    padding: 12px;
    border: 2px dashed transparent;
    border-radius: 8px;
    min-height: 50px;
    outline: none;
}

.preview-asunto:focus {
    border-color: var(--accent);
    background: rgba(233, 69, 96, 0.02);
}

.preview-mensaje {
    color: #495057;
    font-size: 15px;
    line-height: 1.7;
    padding: 20px;
    border: 2px dashed transparent;
    border-radius: 8px;
    min-height: 400px;
    outline: none;
    background: white;
}

.preview-mensaje:focus {
    border-color: var(--accent);
    background: rgba(233, 69, 96, 0.02);
}

/* === BOTÓN ENVIAR === */
.btn-enviar {
    background: linear-gradient(135deg, var(--success) 0%, #00a876 100%);
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-enviar:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 191, 142, 0.3);
}

.btn-enviar:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
    .pasos-indicador {
        flex-direction: column;
        gap: 10px;
    }
    
    .plantillas-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
@endsection

@section('content_header')
<div class="page-header">
    <h1><i class="fas fa-paper-plane"></i> Nueva Notificación</h1>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle"></i> <strong>Errores de validación:</strong>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<form id="formNotificacion" method="POST" action="{{ route('admin.notificaciones.enviar-masivo') }}">
    @csrf
    
    <!-- Indicador de Pasos -->
    <div class="pasos-indicador">
        <div class="paso active" id="paso1">
            <span class="paso-numero">1</span>
            <span class="paso-texto">Clientes</span>
        </div>
        <div class="paso" id="paso2">
            <span class="paso-numero">2</span>
            <span class="paso-texto">Plantilla</span>
        </div>
        <div class="paso" id="paso3">
            <span class="paso-numero">3</span>
            <span class="paso-texto">Previsualización</span>
        </div>
    </div>

    <!-- PASO 1: CLIENTES -->
    <div class="card-section" id="seccionClientes">
        <div class="card-header-custom">
            <h3><i class="fas fa-users"></i> 1. Seleccionar Destinatarios</h3>
        </div>
        <div class="card-body-custom">
            <!-- Filtros -->
            <div class="filtros-clientes">
                <button type="button" class="filtro-btn active" data-filtro="todos">
                    <i class="fas fa-users"></i> Todos ({{ $totalClientes }})
                </button>
                <button type="button" class="filtro-btn" data-filtro="activos">
                    <i class="fas fa-check-circle"></i> Activos ({{ $clientesActivos }})
                </button>
                <button type="button" class="filtro-btn" data-filtro="vencidos">
                    <i class="fas fa-times-circle"></i> Vencidos ({{ $clientesVencidos }})
                </button>
                <button type="button" class="filtro-btn" data-filtro="inactivos">
                    <i class="fas fa-pause-circle"></i> Inactivos ({{ $clientesInactivos }})
                </button>
            </div>

            <!-- Tabla -->
            <table id="tablaClientes" class="table table-hover">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll"></th>
                        <th>Cliente</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Membresía</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                        @php
                            $inscripcionActiva = $cliente->inscripciones->where('id_estado', 100)->first();
                            $inscripcionVencida = $cliente->inscripciones->where('id_estado', 102)->first();
                            $inscripcion = $inscripcionActiva ?? $inscripcionVencida;
                            
                            if ($inscripcionActiva) {
                                $estado = 'activo';
                                $estadoBadge = '<span class="badge badge-success">Activo</span>';
                            } elseif ($inscripcionVencida) {
                                $estado = 'vencido';
                                $estadoBadge = '<span class="badge badge-danger">Vencido</span>';
                            } else {
                                $estado = 'inactivo';
                                $estadoBadge = '<span class="badge badge-secondary">Sin Inscripción</span>';
                            }
                        @endphp
                        
                        @if($cliente->email)
                            <tr data-estado="{{ $estado }}">
                                <td>
                                    <input type="checkbox" 
                                           class="cliente-check" 
                                           value="{{ $cliente->id }}"
                                           data-nombre="{{ $cliente->nombre_completo }}"
                                           data-email="{{ $cliente->email }}">
                                </td>
                                <td>{{ $cliente->nombre_completo }}</td>
                                <td>{{ $cliente->email }}</td>
                                <td>{!! $estadoBadge !!}</td>
                                <td>{{ $inscripcion->membresia->nombre ?? '-' }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <!-- Clientes Seleccionados -->
            <div class="clientes-seleccionados" style="display:none;">
                <strong><i class="fas fa-check-circle"></i> Seleccionados (<span id="countSeleccionados">0</span>):</strong>
                <div id="listaSeleccionados"></div>
            </div>
        </div>
    </div>

    <!-- PASO 2: PLANTILLAS -->
    <div class="card-section" id="seccionPlantillas" style="display:none;">
        <div class="card-header-custom">
            <h3><i class="fas fa-file-alt"></i> 2. Seleccionar Plantilla</h3>
        </div>
        <div class="card-body-custom">
            <div class="plantillas-grid">
                @foreach($plantillasPersonalizadas as $plantilla)
                    <div class="plantilla-card"
                         data-id="{{ $plantilla['id'] }}"
                         data-nombre="{{ $plantilla['nombre'] }}"
                         data-asunto="{{ $plantilla['asunto_email'] ?? '' }}">
                        <div class="plantilla-icon">
                            @switch($plantilla['codigo'] ?? '')
                                @case('horario_especial')
                                    <i class="fas fa-clock"></i>
                                    @break
                                @case('promocion')
                                    <i class="fas fa-tags"></i>
                                    @break
                                @case('anuncio')
                                    <i class="fas fa-bullhorn"></i>
                                    @break
                                @case('evento')
                                    <i class="fas fa-calendar-star"></i>
                                    @break
                                @default
                                    <i class="fas fa-envelope"></i>
                            @endswitch
                        </div>
                        <div class="plantilla-nombre">{{ $plantilla['nombre'] }}</div>
                        <!-- Contenido HTML oculto -->
                        <div class="plantilla-contenido" style="display:none;">{!! $plantilla['plantilla_email'] ?? '' !!}</div>
                    </div>
                @endforeach

                <!-- Plantilla Personalizada -->
                <div class="plantilla-card"
                     data-id="custom"
                     data-nombre="Personalizado"
                     data-asunto=""
                     data-contenido="">
                    <div class="plantilla-icon">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <div class="plantilla-nombre">Personalizado</div>
                </div>
            </div>
        </div>
    </div>

    <!-- PASO 3: PREVISUALIZACIÓN -->
    <div class="card-section" id="seccionPreview" style="display:none;">
        <div class="card-header-custom">
            <h3><i class="fas fa-eye"></i> 3. Previsualización y Edición</h3>
            <small style="color: rgba(255,255,255,0.8);">Haz clic en el texto para editarlo</small>
        </div>
        <div class="card-body-custom" style="background: #f4f6f9;">
            <div class="preview-email">
                <!-- Asunto (editable) -->
                <div class="preview-content">
                    <h2 class="preview-asunto" 
                        contenteditable="true" 
                        id="previewAsunto"
                        placeholder="Asunto del correo...">Asunto del correo...</h2>
                </div>

                <!-- Contenido del email (las plantillas ya incluyen header y footer) -->
                <div class="preview-mensaje" 
                     contenteditable="true" 
                     id="previewMensaje"
                     placeholder="El mensaje aparecerá aquí...">El mensaje aparecerá aquí...</div>
            </div>

            <!-- Inputs ocultos -->
            <input type="hidden" id="asunto" name="asunto">
            <input type="hidden" id="mensaje" name="mensaje">
            <input type="hidden" id="cliente_ids" name="cliente_ids">

            <!-- Botón Enviar -->
            <div class="text-center mt-4 pt-4" style="border-top: 2px solid #e9ecef;">
                <button type="submit" class="btn-enviar" id="btnEnviar" disabled>
                    <i class="fas fa-paper-plane"></i> Enviar a <span id="btnCount">0</span> clientes
                </button>
                <p class="text-muted mt-2 mb-0">
                    <small><i class="fas fa-info-circle"></i> Los correos se enviarán inmediatamente</small>
                </p>
            </div>
        </div>
    </div>

</form>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let selectedClientes = [];
    let plantillaSeleccionada = null;

    // ===== DATATABLE =====
    const tabla = $('#tablaClientes').DataTable({
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
        pageLength: 10,
        order: [[1, 'asc']]
    });

    // ===== FILTROS =====
    $('.filtro-btn').click(function() {
        $('.filtro-btn').removeClass('active');
        $(this).addClass('active');

        const filtro = $(this).data('filtro');
        $.fn.dataTable.ext.search.pop();

        if (filtro !== 'todos') {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const row = tabla.row(dataIndex).node();
                const estado = $(row).attr('data-estado');
                return estado === filtro.slice(0, -1);
            });
        }

        tabla.draw();
        updateSelectAllState();
    });

    // ===== SELECT ALL =====
    $('#selectAll').change(function() {
        const isChecked = $(this).is(':checked');
        
        // Iterar sobre TODAS las filas, no solo las visibles
        tabla.rows({search: 'applied'}).nodes().to$().find('.cliente-check').each(function() {
            const $checkbox = $(this);
            const id = parseInt($checkbox.val());
            const nombre = $checkbox.data('nombre');
            const email = $checkbox.data('email');
            
            // Marcar/desmarcar checkbox
            $checkbox.prop('checked', isChecked);
            
            // Agregar o remover de la lista
            if (isChecked) {
                if (!selectedClientes.find(c => c.id === id)) {
                    selectedClientes.push({id, nombre, email});
                }
            } else {
                selectedClientes = selectedClientes.filter(c => c.id !== id);
            }
        });
        
        updateSeleccionados();
        updatePasos();
    });

    // ===== SELECCIÓN DE CLIENTES =====
    $(document).on('change', '.cliente-check', function() {
        const id = parseInt($(this).val());
        const nombre = $(this).data('nombre');
        const email = $(this).data('email');

        if ($(this).is(':checked')) {
            if (!selectedClientes.find(c => c.id === id)) {
                selectedClientes.push({id, nombre, email});
            }
        } else {
            selectedClientes = selectedClientes.filter(c => c.id !== id);
            // Si deselecciona uno, desmarcar el "select all"
            $('#selectAll').prop('checked', false);
        }

        updateSeleccionados();
        updatePasos();
        updateSelectAllState();
    });

    // Actualizar estado del checkbox "Seleccionar todos"
    function updateSelectAllState() {
        const totalVisibles = tabla.rows({search: 'applied'}).nodes().length;
        const totalSeleccionados = tabla.rows({search: 'applied'}).nodes().to$().find('.cliente-check:checked').length;
        $('#selectAll').prop('checked', totalVisibles > 0 && totalSeleccionados === totalVisibles);
    }

    function updateSeleccionados() {
        const count = selectedClientes.length;
        $('#countSeleccionados, #btnCount').text(count);

        if (count > 0) {
            $('.clientes-seleccionados').show();
            const html = selectedClientes.map(c => 
                `<span class="badge-cliente">
                    ${c.nombre}
                    <span class="remove-badge" data-id="${c.id}">×</span>
                </span>`
            ).join('');
            $('#listaSeleccionados').html(html);
        } else {
            $('.clientes-seleccionados').hide();
        }
    }

    $(document).on('click', '.remove-badge', function() {
        const id = $(this).data('id');
        selectedClientes = selectedClientes.filter(c => c.id !== id);
        $(`.cliente-check[value="${id}"]`).prop('checked', false);
        updateSeleccionados();
        updatePasos();
    });

    // ===== SELECCIÓN DE PLANTILLA =====
    $(document).on('click', '.plantilla-card', function() {
        $('.plantilla-card').removeClass('selected');
        $(this).addClass('selected');

        const contenidoHtml = $(this).find('.plantilla-contenido').html();

        plantillaSeleccionada = {
            id: $(this).data('id'),
            nombre: $(this).data('nombre'),
            asunto: $(this).data('asunto'),
            contenido: contenidoHtml
        };

        // Cargar contenido en preview
        if (plantillaSeleccionada.contenido && plantillaSeleccionada.contenido.trim() !== '') {
            $('#previewMensaje').html(plantillaSeleccionada.contenido);
        } else {
            $('#previewMensaje').text('Escribe tu mensaje personalizado aquí...');
        }

        if (plantillaSeleccionada.asunto) {
            $('#previewAsunto').text(plantillaSeleccionada.asunto);
        } else {
            $('#previewAsunto').text('Asunto del correo...');
        }

        updatePasos();
    });

    // ===== SINCRONIZACIÓN PREVISUALIZACIÓN =====
    $('#previewAsunto, #previewMensaje').on('input blur', function() {
        updatePasos();
    });

    // ===== ACTUALIZAR PASOS =====
    function updatePasos() {
        const clientesOk = selectedClientes.length > 0;
        const plantillaOk = plantillaSeleccionada !== null;
        const asuntoOk = $('#previewAsunto').text().trim() !== '' && 
                         $('#previewAsunto').text().trim() !== 'Asunto del correo...';
        const mensajeOk = $('#previewMensaje').text().trim() !== '' && 
                          $('#previewMensaje').text().trim() !== 'El mensaje aparecerá aquí...';
        const previewOk = asuntoOk && mensajeOk;

        // Paso 1
        $('#paso1').toggleClass('completed', clientesOk).toggleClass('active', !clientesOk);

        // Paso 2
        $('#paso2').toggleClass('completed', plantillaOk).toggleClass('active', clientesOk && !plantillaOk);
        if (clientesOk) {
            $('#seccionPlantillas').slideDown();
        } else {
            $('#seccionPlantillas, #seccionPreview').slideUp();
        }

        // Paso 3
        $('#paso3').toggleClass('completed', previewOk).toggleClass('active', plantillaOk && !previewOk);
        if (plantillaOk) {
            $('#seccionPreview').slideDown();
        } else {
            $('#seccionPreview').slideUp();
        }

        // Botón enviar
        $('#btnEnviar').prop('disabled', !(clientesOk && plantillaOk && previewOk));
    }

    // ===== ENVIAR FORMULARIO =====
    $('#formNotificacion').submit(function(e) {
        e.preventDefault();

        // Capturar contenido editable directamente (las plantillas ya tienen el HTML completo)
        const asuntoTexto = $('#previewAsunto').text().trim();
        const emailHtml = $('#previewMensaje').html();
        
        $('#asunto').val(asuntoTexto);
        $('#mensaje').val(emailHtml);
        $('#cliente_ids').val(JSON.stringify(selectedClientes.map(c => c.id)));

        // Debug: mostrar qué se va a enviar
        console.log('=== DATOS A ENVIAR ===');
        console.log('Asunto:', asuntoTexto);
        console.log('Asunto length:', asuntoTexto.length);
        console.log('Mensaje length:', emailHtml.length);
        console.log('Clientes IDs:', selectedClientes.map(c => c.id));
        console.log('Cliente_ids value:', $('#cliente_ids').val());

        if (selectedClientes.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Selecciona al menos un cliente'
            });
            return;
        }

        const $form = $(this);

        Swal.fire({
            title: '¿Enviar notificación?',
            html: `
                <div style="text-align: left; padding: 20px;">
                    <p><strong>📧 Destinatarios:</strong> ${selectedClientes.length} clientes</p>
                    <p><strong>📝 Asunto:</strong> ${asuntoTexto.substring(0, 60)}...</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00bf8e',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enviando...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $form[0].submit();
            }
        });
    });

    // Inicializar
    updatePasos();
});
</script>
@stop
