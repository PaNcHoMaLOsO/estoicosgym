@extends('adminlte::page')

@section('title', 'Constructor de Reportes')

@section('css')
    <style>
        :root {
            --primary: #1e293b;
            --purple: #7c3aed;
            --info: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        .builder-container {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 1.5rem;
            min-height: calc(100vh - 200px);
        }

        /* Sidebar de configuración */
        .config-sidebar {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .config-section {
            padding: 1.25rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .config-section:last-child {
            border-bottom: none;
        }

        .config-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .config-title i {
            color: var(--purple);
        }

        /* Módulos */
        .modulo-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .modulo-btn {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .modulo-btn:hover {
            border-color: var(--purple);
        }

        .modulo-btn.active {
            border-color: var(--purple);
            background: rgba(124,58,237,0.1);
        }

        .modulo-btn i {
            display: block;
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
            opacity: 0.7;
        }

        .modulo-btn span {
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Campos */
        .campos-list {
            max-height: 250px;
            overflow-y: auto;
        }

        .campo-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .campo-item:last-child {
            border-bottom: none;
        }

        .campo-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--purple);
        }

        .campo-item label {
            flex: 1;
            margin: 0;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .campo-tipo {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            background: #f1f5f9;
            color: #64748b;
        }

        /* Filtros */
        .filtro-grupo {
            margin-bottom: 1rem;
        }

        .filtro-grupo:last-child {
            margin-bottom: 0;
        }

        .filtro-grupo label {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
            color: var(--primary);
        }

        .filtro-grupo input,
        .filtro-grupo select {
            width: 100%;
            padding: 0.6rem 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .filtro-grupo input:focus,
        .filtro-grupo select:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
        }

        .filtro-fechas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        /* Acciones */
        .btn-generar {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--purple) 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-generar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(124,58,237,0.3);
        }

        .btn-generar:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-exportar {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-exportar:hover {
            border-color: var(--success);
            color: var(--success);
        }

        .btn-exportar.excel { color: #10b981; }
        .btn-exportar.pdf { color: #ef4444; }

        .btn-reset {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ef4444;
            border-radius: 10px;
            background: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #ef4444;
        }

        .btn-reset:hover {
            background: #ef4444;
            color: white;
        }

        /* Panel de resultados */
        .results-panel {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .results-header {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .results-header h3 i {
            color: var(--purple);
        }

        .results-count {
            background: var(--purple);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .results-body {
            padding: 1.5rem;
            min-height: 400px;
        }

        .results-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            color: #94a3b8;
        }

        .results-placeholder i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .results-placeholder p {
            font-size: 1rem;
            text-align: center;
        }

        /* Tabla de resultados */
        .results-table-container {
            overflow-x: auto;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th {
            background: #1e293b;
            padding: 0.875rem 1rem;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
            border-bottom: 2px solid #7c3aed;
            border-right: 1px solid #334155;
            white-space: nowrap;
        }

        .results-table th:last-child {
            border-right: none;
        }

        .results-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .results-table td:last-child {
            border-right: none;
        }

        .results-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .results-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .results-table tr:hover {
            background: #ede9fe !important;
        }

        .results-table .moneda {
            font-weight: 600;
            color: var(--success);
        }

        .results-table .fecha {
            color: #475569;
            font-weight: 500;
        }

        .results-table .badge-estado {
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Fila de header de módulos */
        .results-table thead tr.header-modules th {
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Totales */
        .results-totals {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.25rem 1.5rem;
            border-top: 2px solid #e5e7eb;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .total-item {
            display: flex;
            flex-direction: column;
        }

        .total-item span {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .total-item strong {
            font-size: 1.25rem;
            color: var(--primary);
        }

        /* Loading */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e5e7eb;
            border-top-color: var(--purple);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .builder-container {
                grid-template-columns: 1fr;
            }

            .config-sidebar {
                order: 2;
            }

            .results-panel {
                order: 1;
            }
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="font-weight: 800; color: #1e293b;">
                <i class="fas fa-tools mr-2" style="color: rgba(124, 58, 237, 0.7);"></i>
                Constructor de Reportes
            </h1>
            <p class="text-muted mb-0">Configura y genera reportes personalizados</p>
        </div>
        <a href="{{ route('admin.reportes.index') }}" class="btn btn-light" style="border-radius: 10px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <div class="builder-container">
        <!-- Sidebar de configuración -->
        <div class="config-sidebar">
            <form id="reportForm">
                <!-- Selección de módulos (múltiples) -->
                <div class="config-section">
                    <div class="config-title">
                        <i class="fas fa-database"></i>
                        Módulos de Datos
                        <small style="font-weight: 400; text-transform: none; font-size: 0.7rem; color: #94a3b8;">(combina varios)</small>
                    </div>
                    <div class="modulo-selector">
                        @foreach($modulos as $key => $modulo)
                        <label class="modulo-btn {{ $moduloSeleccionado == $key ? 'active' : '' }}" 
                               data-modulo="{{ $key }}"
                               style="--modulo-color: {{ $modulo['color'] }};">
                            <input type="checkbox" name="modulos[]" value="{{ $key }}" 
                                   class="modulo-checkbox" style="display: none;"
                                   {{ $moduloSeleccionado == $key ? 'checked' : '' }}>
                            <i class="{{ $modulo['icono'] }}" style="color: {{ $modulo['color'] }};"></i>
                            <span>{{ $modulo['nombre'] }}</span>
                        </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="modulo" id="moduloInput" value="{{ $moduloSeleccionado }}">
                    <div class="mt-2" style="font-size: 0.75rem; color: #64748b;">
                        <i class="fas fa-info-circle"></i> Selecciona uno o más módulos para combinar datos
                    </div>
                </div>

                <!-- Campos a mostrar -->
                <div class="config-section">
                    <div class="config-title">
                        <i class="fas fa-columns"></i>
                        Campos a Mostrar
                    </div>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-light" id="selectAll">Todos</button>
                        <button type="button" class="btn btn-sm btn-light" id="selectNone">Ninguno</button>
                    </div>
                    <div class="campos-list" id="camposList">
                        @foreach($moduloConfig['campos'] as $campo => $config)
                        <div class="campo-item">
                            <input type="checkbox" name="campos[]" value="{{ $campo }}" id="campo_{{ $campo }}" 
                                   {{ in_array($campo, ['id', 'nombres', 'nombre', 'fecha_pago', 'monto_abonado', 'created_at']) ? 'checked' : '' }}>
                            <label for="campo_{{ $campo }}">{{ $config['label'] }}</label>
                            <span class="campo-tipo">{{ $config['tipo'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Filtros -->
                <div class="config-section">
                    <div class="config-title">
                        <i class="fas fa-filter"></i>
                        Filtros
                    </div>
                    
                    <div class="filtro-grupo">
                        <label>Rango de Fechas</label>
                        <div class="filtro-fechas">
                            <input type="date" name="filtros[created_at][desde]" placeholder="Desde">
                            <input type="date" name="filtros[created_at][hasta]" placeholder="Hasta">
                        </div>
                    </div>

                    @if(in_array($moduloSeleccionado, ['inscripciones', 'pagos']))
                    <div class="filtro-grupo">
                        <label>Membresía</label>
                        <select name="filtro_membresia">
                            <option value="">Todas</option>
                            @foreach($membresias as $membresia)
                            <option value="{{ $membresia->id }}">{{ $membresia->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if($moduloSeleccionado == 'pagos')
                    <div class="filtro-grupo">
                        <label>Método de Pago</label>
                        <select name="filtro_metodo_pago">
                            <option value="">Todos</option>
                            @foreach($metodosPago as $metodo)
                            <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filtro-grupo">
                        <label>Estado</label>
                        <select name="filtros[id_estado]">
                            <option value="">Todos</option>
                            <option value="200">Pendiente</option>
                            <option value="201">Pagado</option>
                            <option value="202">Parcial</option>
                            <option value="203">Vencido</option>
                        </select>
                    </div>
                    @endif

                    @if($moduloSeleccionado == 'clientes')
                    <div class="filtro-grupo">
                        <label>Convenio</label>
                        <select name="filtro_convenio">
                            <option value="">Todos</option>
                            @foreach($convenios as $convenio)
                            <option value="{{ $convenio->id }}">{{ $convenio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filtro-grupo">
                        <label>Género</label>
                        <select name="filtros[genero]">
                            <option value="">Todos</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro</option>
                        </select>
                    </div>
                    @endif

                    @if($moduloSeleccionado == 'inscripciones')
                    <div class="filtro-grupo">
                        <label>Estado</label>
                        <select name="filtros[id_estado]">
                            <option value="">Todos</option>
                            <option value="100">Activa</option>
                            <option value="101">Pausada</option>
                            <option value="102">Vencida</option>
                            <option value="103">Cancelada</option>
                        </select>
                    </div>
                    @endif
                </div>

                <!-- Ordenamiento -->
                <div class="config-section">
                    <div class="config-title">
                        <i class="fas fa-sort"></i>
                        Ordenar por
                    </div>
                    <div class="filtro-grupo">
                        <select name="ordenar" id="ordenarSelect">
                            @foreach($moduloConfig['campos'] as $campo => $config)
                            <option value="{{ $campo }}" {{ $campo == 'created_at' ? 'selected' : '' }}>
                                {{ $config['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <select name="direccion">
                            <option value="desc">Descendente</option>
                            <option value="asc">Ascendente</option>
                        </select>
                    </div>
                </div>

                <!-- Límite -->
                <div class="config-section">
                    <div class="config-title">
                        <i class="fas fa-list-ol"></i>
                        Límite de Resultados
                    </div>
                    <select name="limite" class="form-control">
                        <option value="0" selected>Sin límite (todos)</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                        <option value="250">250 registros</option>
                        <option value="500">500 registros</option>
                        <option value="1000">1000 registros</option>
                    </select>
                </div>

                <!-- Botones de acción -->
                <div class="config-section">
                    <button type="submit" class="btn-generar mb-2">
                        <i class="fas fa-play"></i>
                        Generar Reporte
                    </button>
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn-exportar excel" id="btnExportExcel" disabled>
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                        <button type="button" class="btn-exportar pdf" id="btnExportPdf" disabled>
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>
                    </div>
                    <button type="button" class="btn-reset" id="btnReset">
                        <i class="fas fa-undo"></i>
                        Resetear Todo
                    </button>
                </div>
            </form>
        </div>

        <!-- Panel de resultados -->
        <div class="results-panel" style="position: relative;">
            <div class="results-header">
                <h3>
                    <i class="fas fa-table"></i>
                    Resultados del Reporte
                </h3>
                <span class="results-count" id="resultsCount">0 registros</span>
            </div>
            <div class="results-body" id="resultsBody">
                <div class="results-placeholder">
                    <i class="fas fa-chart-bar"></i>
                    <p>Configura los parámetros y haz clic en<br><strong>"Generar Reporte"</strong> para ver los resultados</p>
                </div>
            </div>
            <div class="results-totals" id="resultsTotals" style="display: none;"></div>
            
            <div class="loading-overlay" id="loadingOverlay" style="display: none;">
                <div class="spinner"></div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    const modulosConfig = @json($modulos);
    let currentData = [];
    let currentCampos = [];
    let currentConfig = {};
    let selectedModulos = ['{{ $moduloSeleccionado }}'];

    // Toggle módulo (selección múltiple)
    $('.modulo-btn').on('click', function(e) {
        e.preventDefault();
        const $this = $(this);
        const modulo = $this.data('modulo');
        const $checkbox = $this.find('.modulo-checkbox');
        
        // Toggle checkbox
        $checkbox.prop('checked', !$checkbox.prop('checked'));
        $this.toggleClass('active', $checkbox.prop('checked'));
        
        // Actualizar lista de módulos seleccionados
        selectedModulos = [];
        $('.modulo-checkbox:checked').each(function() {
            selectedModulos.push($(this).val());
        });
        
        // Si no hay ninguno seleccionado, mantener al menos uno
        if (selectedModulos.length === 0) {
            $checkbox.prop('checked', true);
            $this.addClass('active');
            selectedModulos = [modulo];
        }
        
        // Actualizar input hidden con el primer módulo (principal)
        $('#moduloInput').val(selectedModulos[0]);
        
        // Mostrar indicador de módulos combinados
        updateModuloIndicator();
        
        // Actualizar campos disponibles
        updateCamposDisponibles();
    });
    
    function updateModuloIndicator() {
        const count = selectedModulos.length;
        const $indicator = $('#moduloCombinado');
        if (count > 1) {
            if ($indicator.length === 0) {
                $('.modulo-selector').after('<div id="moduloCombinado" class="mt-2 p-2" style="background: rgba(124,58,237,0.1); border-radius: 8px; font-size: 0.8rem;"><i class="fas fa-layer-group text-purple mr-1"></i> <strong>' + count + ' módulos</strong> combinados</div>');
            } else {
                $indicator.html('<i class="fas fa-layer-group text-purple mr-1"></i> <strong>' + count + ' módulos</strong> combinados');
            }
        } else {
            $indicator.remove();
        }
    }
    
    // Actualizar campos disponibles según módulos seleccionados
    function updateCamposDisponibles() {
        const $camposList = $('#camposList');
        $camposList.empty();
        
        // Campos por defecto a marcar
        const camposDefault = ['id', 'nombres', 'nombre', 'fecha_pago', 'monto_abonado', 'created_at', 'fecha_inicio', 'precio_final'];
        
        selectedModulos.forEach((modulo, index) => {
            const config = modulosConfig[modulo];
            if (!config) return;
            
            // Agregar separador si hay más de un módulo
            if (selectedModulos.length > 1) {
                $camposList.append(`
                    <div class="campo-modulo-header" style="background: ${config.color}15; padding: 0.5rem 0.75rem; margin: ${index > 0 ? '0.75rem' : '0'} -0.5rem 0.5rem; border-radius: 6px; border-left: 3px solid ${config.color};">
                        <i class="${config.icono}" style="color: ${config.color}; opacity: 0.7;"></i>
                        <strong style="font-size: 0.8rem; color: ${config.color};">${config.nombre}</strong>
                    </div>
                `);
            }
            
            // Agregar campos del módulo
            for (const [campo, campoConfig] of Object.entries(config.campos)) {
                const campoId = `${modulo}_${campo}`;
                const isChecked = camposDefault.includes(campo) ? 'checked' : '';
                
                $camposList.append(`
                    <div class="campo-item">
                        <input type="checkbox" name="campos[]" value="${modulo}.${campo}" id="campo_${campoId}" ${isChecked}>
                        <label for="campo_${campoId}">${campoConfig.label}</label>
                        <span class="campo-tipo">${campoConfig.tipo}</span>
                    </div>
                `);
            }
        });
        
        // Actualizar select de ordenamiento
        updateOrdenarSelect();
    }
    
    // Actualizar opciones de ordenamiento
    function updateOrdenarSelect() {
        const $ordenarSelect = $('#ordenarSelect');
        $ordenarSelect.empty();
        
        selectedModulos.forEach(modulo => {
            const config = modulosConfig[modulo];
            if (!config) return;
            
            for (const [campo, campoConfig] of Object.entries(config.campos)) {
                const value = selectedModulos.length > 1 ? `${modulo}.${campo}` : campo;
                const label = selectedModulos.length > 1 ? `${config.nombre}: ${campoConfig.label}` : campoConfig.label;
                const selected = campo === 'created_at' ? 'selected' : '';
                $ordenarSelect.append(`<option value="${value}" ${selected}>${label}</option>`);
            }
        });
    }

    // Función de reset completo
    function resetearTodo() {
        // Resetear módulos - dejar solo el primero (clientes)
        selectedModulos = ['clientes'];
        $('.modulo-checkbox').prop('checked', false);
        $('.modulo-btn').removeClass('active');
        $('.modulo-checkbox[value="clientes"]').prop('checked', true);
        $('.modulo-btn[data-modulo="clientes"]').addClass('active');
        $('#moduloInput').val('clientes');
        
        // Eliminar indicador de módulos combinados
        $('#moduloCombinado').remove();
        
        // Actualizar campos disponibles
        updateCamposDisponibles();
        
        // Resetear filtros de fecha
        $('input[name="filtros[created_at][desde]"]').val('');
        $('input[name="filtros[created_at][hasta]"]').val('');
        
        // Resetear otros filtros
        $('select[name="filtro_membresia"]').val('');
        $('select[name="filtro_metodo_pago"]').val('');
        $('select[name="filtro_convenio"]').val('');
        
        // Resetear ordenamiento
        $('select[name="direccion"]').val('desc');
        
        // Resetear límite
        $('select[name="limite"]').val('0');
        
        // Limpiar resultados
        $('#resultsBody').html(`
            <div class="results-placeholder">
                <i class="fas fa-chart-bar"></i>
                <p>Configura los parámetros y haz clic en<br><strong>"Generar Reporte"</strong> para ver los resultados</p>
            </div>
        `);
        $('#resultsTotals').hide();
        $('#resultsCount').text('0 registros');
        
        // Deshabilitar botones de exportar
        $('#btnExportExcel, #btnExportPdf').prop('disabled', true);
        
        // Limpiar datos actuales
        currentData = [];
        currentCampos = [];
        currentConfig = {};
        
        Swal.fire({
            icon: 'success',
            title: 'Reseteo completo',
            text: 'Se han limpiado todas las selecciones',
            timer: 1500,
            showConfirmButton: false
        });
    }

    // Botón de reset
    $('#btnReset').on('click', function() {
        Swal.fire({
            title: '¿Resetear todo?',
            text: 'Se limpiarán todas las selecciones de módulos, campos y filtros',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, resetear',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                resetearTodo();
            }
        });
    });

    // Seleccionar todos/ninguno
    $('#selectAll').on('click', function() {
        $('input[name="campos[]"]').prop('checked', true);
    });

    $('#selectNone').on('click', function() {
        $('input[name="campos[]"]').prop('checked', false);
    });

    // Generar reporte
    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        
        const camposSeleccionados = $('input[name="campos[]"]:checked').length;
        if (camposSeleccionados === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin campos',
                text: 'Debes seleccionar al menos un campo para mostrar',
                confirmButtonColor: '#7c3aed'
            });
            return;
        }

        $('#loadingOverlay').show();
        
        $.ajax({
            url: '{{ route("admin.reportes.generar") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                currentData = response.datos;
                currentCampos = response.campos;
                currentConfig = response.config;
                
                renderResults(response);
                $('#btnExportExcel, #btnExportPdf').prop('disabled', false);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al generar el reporte',
                    confirmButtonColor: '#7c3aed'
                });
            },
            complete: function() {
                $('#loadingOverlay').hide();
            }
        });
    });

    // Renderizar resultados
    function renderResults(response) {
        const { datos, campos, config, totales, total_registros, modulos_combinados } = response;
        
        $('#resultsCount').text(total_registros + ' registros');
        
        if (datos.length === 0) {
            $('#resultsBody').html(`
                <div class="results-placeholder">
                    <i class="fas fa-inbox"></i>
                    <p>No se encontraron resultados<br>con los filtros seleccionados</p>
                </div>
            `);
            $('#resultsTotals').hide();
            return;
        }

        // Construir tabla
        let html = '<div class="results-table-container"><table class="results-table"><thead><tr>';
        
        // Headers - Agrupar por módulo si es combinado
        if (modulos_combinados && selectedModulos.length > 1) {
            // Fila de grupo de módulos
            let headerGroups = '<tr class="header-modules">';
            let currentModule = '';
            let colspan = 0;
            
            campos.forEach((campo, idx) => {
                const parts = campo.split('.');
                const modulo = parts[0];
                
                if (modulo !== currentModule) {
                    if (currentModule !== '') {
                        const modConfig = modulosConfig[currentModule];
                        headerGroups += `<th colspan="${colspan}" style="background: ${modConfig?.color || '#7c3aed'}; color: white; text-align: center; border-right: 2px solid #1e293b;">${modConfig?.nombre || currentModule}</th>`;
                    }
                    currentModule = modulo;
                    colspan = 1;
                } else {
                    colspan++;
                }
                
                // Último campo
                if (idx === campos.length - 1) {
                    const modConfig = modulosConfig[currentModule];
                    headerGroups += `<th colspan="${colspan}" style="background: ${modConfig?.color || '#7c3aed'}; color: white; text-align: center;">${modConfig?.nombre || currentModule}</th>`;
                }
            });
            headerGroups += '</tr>';
            html = '<div class="results-table-container"><table class="results-table"><thead>' + headerGroups + '<tr>';
        }
        
        // Headers de columnas
        campos.forEach(campo => {
            let label = config.campos[campo]?.label || campo.split('.').pop();
            // Capitalizar primera letra
            label = label.charAt(0).toUpperCase() + label.slice(1);
            html += `<th>${label}</th>`;
        });
        html += '</tr></thead><tbody>';

        // Datos
        datos.forEach(row => {
            html += '<tr>';
            
            campos.forEach(campo => {
                let valor = row[campo];
                if (valor === null || valor === undefined) {
                    valor = '<span style="color: #94a3b8;">-</span>';
                } else {
                    const tipo = config.campos[campo]?.tipo || 'texto';
                    
                    // Formatear según tipo
                    switch(tipo) {
                        case 'moneda':
                            valor = `<span class="moneda">$${formatNumber(valor)}</span>`;
                            break;
                        case 'fecha':
                            if (valor && valor !== '-') {
                                valor = `<span class="fecha">${formatDate(valor)}</span>`;
                            }
                            break;
                        case 'booleano':
                            valor = valor ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>';
                            break;
                        case 'estado':
                            valor = renderEstado(valor);
                            break;
                    }
                }
                
                html += `<td>${valor}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#resultsBody').html(html);

        // Totales
        if (Object.keys(totales).length > 0) {
            let totalesHtml = '';
            for (const [campo, valor] of Object.entries(totales)) {
                const label = config.campos[campo]?.label || campo;
                totalesHtml += `
                    <div class="total-item">
                        <span>Total ${label}</span>
                        <strong>$${formatNumber(valor)}</strong>
                    </div>
                `;
            }
            $('#resultsTotals').html(totalesHtml).show();
        } else {
            $('#resultsTotals').hide();
        }
    }

    // Exportar Excel
    $('#btnExportExcel').on('click', function() {
        const form = $('#reportForm');
        const formData = form.serialize() + '&formato=excel';
        window.location.href = '{{ route("admin.reportes.generar") }}?' + formData;
    });

    // Exportar PDF
    $('#btnExportPdf').on('click', function() {
        const form = $('#reportForm');
        const formData = form.serialize() + '&formato=pdf';
        window.open('{{ route("admin.reportes.generar") }}?' + formData, '_blank');
    });

    // Helpers
    function formatNumber(num) {
        return new Intl.NumberFormat('es-CL').format(num || 0);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-CL');
    }

    function renderEstado(codigo) {
        const estados = {
            // Estados de Inscripción (100-106)
            100: { nombre: 'Activa', color: '#10b981' },
            101: { nombre: 'Pausada', color: '#f59e0b' },
            102: { nombre: 'Vencida', color: '#ef4444' },
            103: { nombre: 'Cancelada', color: '#6b7280' },
            104: { nombre: 'Suspendida', color: '#dc2626' },
            105: { nombre: 'Cambiada', color: '#8b5cf6' },
            106: { nombre: 'Traspasada', color: '#06b6d4' },
            // Estados de Pago (200-205)
            200: { nombre: 'Pendiente', color: '#f59e0b' },
            201: { nombre: 'Pagado', color: '#10b981' },
            202: { nombre: 'Parcial', color: '#3b82f6' },
            203: { nombre: 'Vencido', color: '#ef4444' },
            204: { nombre: 'Cancelado', color: '#6b7280' },
            205: { nombre: 'Traspasado', color: '#06b6d4' },
            // Estados de Convenio (300-302)
            300: { nombre: 'Convenio Activo', color: '#10b981' },
            301: { nombre: 'Convenio Inactivo', color: '#6b7280' },
            302: { nombre: 'Convenio Suspendido', color: '#ef4444' },
            // Estados de Cliente (400-402)
            400: { nombre: 'Cliente Activo', color: '#10b981' },
            401: { nombre: 'Cliente Inactivo', color: '#6b7280' },
            402: { nombre: 'Cliente Suspendido', color: '#ef4444' },
        };
        const estado = estados[codigo] || { nombre: codigo, color: '#6b7280' };
        return `<span class="badge-estado" style="background: ${estado.color}20; color: ${estado.color};">${estado.nombre}</span>`;
    }
});
</script>
@endsection
