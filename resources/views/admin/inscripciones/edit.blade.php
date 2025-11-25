@extends('adminlte::page')

@section('title', 'Editar Inscripción - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Editar Inscripción</h1>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.inscripciones.update', $inscripcion) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_cliente">Cliente <span class="text-danger">*</span></label>
                            <select class="form-control select2-cliente @error('id_cliente') is-invalid @enderror" 
                                    id="id_cliente" name="id_cliente" required style="width: 100%;">
                                <option value="">-- Seleccionar Cliente --</option>
                                <option value="{{ $inscripcion->id_cliente }}" selected>
                                    {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }} ({{ $inscripcion->cliente->email }})
                                </option>
                            </select>
                            @error('id_cliente')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_membresia">Tipo de Membresía <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                    id="id_membresia" name="id_membresia" required>
                                <option value="">-- Seleccionar Membresía --</option>
                                @foreach($membresias as $membresia)
                                    <option value="{{ $membresia->id }}" {{ old('id_membresia', $inscripcion->id_membresia) == $membresia->id ? 'selected' : '' }}>
                                        {{ $membresia->nombre }} ({{ $membresia->duracion_meses }} meses)
                                    </option>
                                @endforeach
                            </select>
                            @error('id_membresia')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_convenio">Convenio (Opcional)</label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" name="id_convenio">
                                <option value="">-- Sin Convenio --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" {{ old('id_convenio', $inscripcion->id_convenio) == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_convenio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_estado">Estado <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_estado') is-invalid @enderror" 
                                    id="id_estado" name="id_estado" required>
                                <option value="">-- Seleccionar Estado --</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}" {{ old('id_estado', $inscripcion->id_estado) == $estado->id ? 'selected' : '' }}>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_estado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $inscripcion->fecha_inicio->format('Y-m-d')) }}" 
                                   readonly required>
                            @error('fecha_inicio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_vencimiento">Fecha Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror" 
                                   id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $inscripcion->fecha_vencimiento->format('Y-m-d')) }}" 
                                   readonly required>
                            @error('fecha_vencimiento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Se calcula automáticamente</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="precio_base">Precio Base <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('precio_base') is-invalid @enderror" 
                                       id="precio_base" name="precio_base" step="0.01" min="0.01" 
                                       value="{{ old('precio_base', $inscripcion->precio_base) }}" placeholder="0.00" readonly required>
                                @error('precio_base')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="text-muted">Se carga automáticamente al seleccionar membresía</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="descuento_aplicado">Descuento (Opcional)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('descuento_aplicado') is-invalid @enderror" 
                                       id="descuento_aplicado" name="descuento_aplicado" step="0.01" min="0" 
                                       value="{{ old('descuento_aplicado', $inscripcion->descuento_aplicado) }}" placeholder="0.00">
                                @error('descuento_aplicado')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_motivo_descuento">Motivo del Descuento</label>
                            <select class="form-control @error('id_motivo_descuento') is-invalid @enderror" 
                                    id="id_motivo_descuento" name="id_motivo_descuento">
                                <option value="">-- Sin Motivo --</option>
                                @foreach($motivos as $motivo)
                                    <option value="{{ $motivo->id }}" {{ old('id_motivo_descuento', $inscripcion->id_motivo_descuento) == $motivo->id ? 'selected' : '' }}>
                                        {{ $motivo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_motivo_descuento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="3" placeholder="Notas adicionales...">{{ old('observaciones', $inscripcion->observaciones) }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <hr>

                <div class="form-group">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Inscripción
                    </button>
                </div>
            </form>
        </div>
    </div>

    @php
        $estadoPermitidosPausa = [1, 2, 3, 4, 5, 6, 7, 8, 9]; // Todos los estados de membresía
        $puedeUsarPausas = in_array($inscripcion->id_estado, $estadoPermitidosPausa);
    @endphp

    @if ($puedeUsarPausas)
        <!-- Sección de Pausas -->
        <div class="card mt-3">
            <div class="card-header bg-info">
                <h5 class="mb-0"><i class="fas fa-pause-circle"></i> Sistema de Pausas</h5>
            </div>
            <div class="card-body">
                @if ($inscripcion->pausada)
                    <!-- Membresía Pausada -->
                    <div class="alert alert-warning">
                        <strong>Estado Actual:</strong> Membresía pausada
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Días de Pausa:</strong>
                            <p class="text-muted">{{ $inscripcion->dias_pausa }} días</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Fecha de Inicio:</strong>
                            <p class="text-muted">{{ $inscripcion->fecha_pausa_inicio?->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Fecha de Fin:</strong>
                            <p class="text-muted">{{ $inscripcion->fecha_pausa_fin?->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Razón:</strong>
                            <p class="text-muted">{{ $inscripcion->razon_pausa ?? 'No especificada' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Pausas Realizadas:</strong>
                            <p class="text-muted">{{ $inscripcion->pausas_realizadas }} / {{ $inscripcion->max_pausas_permitidas }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success" id="btnReanudar">
                        <i class="fas fa-play-circle"></i> Reanudar Membresía
                    </button>
                @else
                    <!-- Membresía Activa - Opción para Pausar -->
                    <div class="alert alert-success">
                        <strong>Estado Actual:</strong> Membresía activa
                    </div>
                    <div class="form-group">
                        <label for="dias_pausa">Pausar por:</label>
                        <div class="input-group mb-2">
                            <select class="form-control" id="dias_pausa">
                                <option value="">-- Seleccionar duración --</option>
                                <option value="7">7 días</option>
                                <option value="14">14 días (2 semanas)</option>
                                <option value="30">30 días (1 mes)</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-warning" type="button" id="btnAbrirModalPausa" disabled>
                                    <i class="fas fa-pause-circle"></i> Pausar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Pausas Disponibles:</strong>
                            <p class="text-muted">{{ $inscripcion->max_pausas_permitidas - $inscripcion->pausas_realizadas }} / {{ $inscripcion->max_pausas_permitidas }}</p>
                        </div>
                    </div>
                    @if ($inscripcion->pausas_realizadas >= $inscripcion->max_pausas_permitidas)
                        <div class="alert alert-danger">
                            <strong>No se pueden realizar más pausas.</strong> Se alcanzó el límite permitido.
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Modal para Pausa -->
        <div class="modal fade" id="modalPausa" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pausar Membresía</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="formPausa">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="modalRazonPausa">Razón de la Pausa (Opcional)</label>
                                <input type="text" class="form-control" id="modalRazonPausa" placeholder="Ej: Vacaciones, Viaje, etc.">
                            </div>
                            <div class="form-group">
                                <label for="modalDiasPausa">Duración:</label>
                                <p id="modalDiasPausa" class="text-muted"></p>
                            </div>
                            <div id="resumenPausa" class="alert alert-info"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-warning" id="btnConfirmarPausa">
                                <i class="fas fa-pause-circle"></i> Confirmar Pausa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
/* eslint-disable no-unused-vars */
// Variables globales para Blade (validadas por Laravel)
const INSCRIPCION_ID = {{ $inscripcion->id }};
/* eslint-enable no-unused-vars */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Select2 para Cliente
    $('.select2-cliente').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        ajax: {
            url: '/api/clientes/search',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                };
            },
            processResults: function (data) {
                return {
                    results: data,
                };
            },
        },
        minimumInputLength: 2,
    });

    // Auto-calcular descuentos y vencimiento
    function calcularInscripcion() {
        const idMembresia = document.getElementById('id_membresia').value;
        const idConvenio = document.getElementById('id_convenio').value;
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const precioBase = document.getElementById('precio_base').value;

        if (!idMembresia || !fechaInicio || !precioBase) {
            return;
        }

        fetch('/api/inscripciones/calcular', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                id_membresia: idMembresia,
                id_convenio: idConvenio || null,
                fecha_inicio: fechaInicio,
                precio_base: parseFloat(precioBase),
            }),
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('fecha_vencimiento').value = data.fecha_vencimiento;
            document.getElementById('descuento_aplicado').value = data.descuento_aplicado;
            
            if (data.precio_final !== undefined) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `Descuento calculado: $${data.descuento_aplicado} | Precio final: $${data.precio_final} <button type="button" class="close" data-dismiss="alert">&times;</button>`;
                document.querySelector('.card-body').insertBefore(alertDiv, document.querySelector('form'));
                setTimeout(() => alertDiv.remove(), 5000);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Escuchar cambios en campos relevantes
    document.getElementById('id_membresia').addEventListener('change', calcularInscripcion);
    document.getElementById('id_convenio').addEventListener('change', calcularInscripcion);
    document.getElementById('fecha_inicio').addEventListener('change', calcularInscripcion);
    document.getElementById('precio_base').addEventListener('change', calcularInscripcion);

    // Sistema de Pausas
    const btnAbrirModalPausa = document.getElementById('btnAbrirModalPausa');
    const selectDiasPausa = document.getElementById('dias_pausa');
    const btnConfirmarPausa = document.getElementById('btnConfirmarPausa');
    const btnReanudar = document.getElementById('btnReanudar');

    if (selectDiasPausa) {
        selectDiasPausa.addEventListener('change', function() {
            if (this.value) {
                btnAbrirModalPausa.disabled = false;
            } else {
                btnAbrirModalPausa.disabled = true;
            }
        });
    }

    if (btnAbrirModalPausa) {
        btnAbrirModalPausa.addEventListener('click', function() {
            const dias = selectDiasPausa.value;
            document.getElementById('modalDiasPausa').textContent = dias + ' días';
            
            const fechaFin = new Date();
            fechaFin.setDate(fechaFin.getDate() + parseInt(dias));
            const fechaFinFormato = fechaFin.toLocaleDateString('es-ES');
            
            document.getElementById('resumenPausa').innerHTML = `
                <strong>La membresía estará pausada desde hoy hasta:</strong><br>
                <strong>${fechaFinFormato}</strong>
            `;
            
            $('#modalPausa').modal('show');
        });
    }

    if (btnConfirmarPausa) {
        btnConfirmarPausa.addEventListener('click', function() {
            const dias = selectDiasPausa.value;
            const razon = document.getElementById('modalRazonPausa').value;

            fetch(`/api/pausas/${INSCRIPCION_ID}/pausar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    dias: parseInt(dias),
                    razon: razon || '',
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Membresía pausada exitosamente');
                    location.reload();
                } else {
                    alert('✗ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la pausa');
            });
        });
    }

    if (btnReanudar) {
        btnReanudar.addEventListener('click', function() {
            if (confirm('¿Confirma que desea reanudar esta membresía?')) {
                fetch(`/api/pausas/${INSCRIPCION_ID}/reanudar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✓ Membresía reanudada exitosamente');
                        location.reload();
                    } else {
                        alert('✗ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la reanudación');
                });
            }
        });
    }
});
</script>
@stop
