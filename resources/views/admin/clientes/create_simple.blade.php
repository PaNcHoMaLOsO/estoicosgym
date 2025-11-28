@extends('adminlte::page')

@section('title', 'Crear Cliente - EstóicosGym')

@section('content')
    <div class="container mt-4">
        <h1>Crear Nuevo Cliente - VERSIÓN SIMPLE PARA TESTING</h1>
        
        <div class="card">
            <div class="card-body">
                
                <!-- Indicador de paso actual -->
                <div class="alert alert-info">
                    <strong>Paso Actual: <span id="paso-actual">1</span> de 3</strong>
                </div>

                <form action="{{ route('admin.clientes.store') }}" method="POST" id="clienteForm">
                    @csrf
                    <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">
                    <input type="hidden" id="flujo_cliente" name="flujo_cliente" value="solo_cliente">

                    <!-- ============ PASO 1 ============ -->
                    <div id="paso-1" class="paso-content">
                        <h3>Paso 1: Datos del Cliente</h3>
                        
                        <div class="form-group">
                            <label for="nombres">Nombres *</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>

                        <div class="form-group">
                            <label for="apellido_paterno">Apellido Paterno *</label>
                            <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="celular">Celular *</label>
                            <input type="tel" class="form-control" id="celular" name="celular" required>
                        </div>

                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion">
                        </div>
                    </div>

                    <!-- ============ PASO 2 ============ -->
                    <div id="paso-2" class="paso-content" style="display:none;">
                        <h3>Paso 2: Membresía</h3>
                        
                        <div class="form-group">
                            <label for="id_membresia">Membresía *</label>
                            <select class="form-control" id="id_membresia" name="id_membresia">
                                <option value="">-- Seleccionar --</option>
                                @foreach($membresias as $membresia)
                                    <option value="{{ $membresia->id }}">{{ $membresia->nombre }} ({{ $membresia->duracion_dias }} días)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de Inicio *</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label for="id_convenio">Convenio (Descuento)</label>
                            <select class="form-control" id="id_convenio" name="id_convenio">
                                <option value="">-- Sin Convenio --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}">{{ $convenio->nombre }} ({{ $convenio->descuento_porcentaje }}% desc.)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- ============ PASO 3 ============ -->
                    <div id="paso-3" class="paso-content" style="display:none;">
                        <h3>Paso 3: Pago</h3>
                        
                        <div class="form-group">
                            <label for="monto_abonado">Monto Abonado *</label>
                            <input type="number" class="form-control" id="monto_abonado" name="monto_abonado" min="0.01" step="0.01">
                        </div>

                        <div class="form-group">
                            <label for="id_metodo_pago">Método de Pago *</label>
                            <select class="form-control" id="id_metodo_pago" name="id_metodo_pago">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha_pago">Fecha de Pago *</label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Botones de navegación -->
                    <div class="mt-4">
                        <button type="button" class="btn btn-secondary" id="btn-anterior" onclick="console.log('CLICK ANTERIOR'); pasoAnterior(); return false;" style="display:none;">
                            ← Anterior
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-siguiente" onclick="console.log('✅ CLICK SIGUIENTE CAPTURADO'); pasoSiguiente(); return false;">
                            Siguiente →
                        </button>
                        <button type="submit" class="btn btn-success" id="btn-guardar" onclick="console.log('CLICK GUARDAR'); setFlujo('solo_cliente');" style="display:none;">
                            Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let pasoActual = 1;
        const totalPasos = 3;

        console.log('✅ Script cargado - pasoActual:', pasoActual);

        function mostrarPaso(paso) {
            console.log('📍 mostrarPaso(' + paso + ')');
            
            // Ocultar todos los pasos
            for (let i = 1; i <= totalPasos; i++) {
                document.getElementById('paso-' + i).style.display = 'none';
            }
            
            // Mostrar paso actual
            document.getElementById('paso-' + paso).style.display = 'block';
            pasoActual = paso;
            
            // Actualizar indicador
            document.getElementById('paso-actual').textContent = paso;
            
            // Actualizar botones
            const btnAnterior = document.getElementById('btn-anterior');
            const btnSiguiente = document.getElementById('btn-siguiente');
            const btnGuardar = document.getElementById('btn-guardar');
            
            btnSiguiente.style.display = paso < totalPasos ? 'block' : 'none';
            btnAnterior.style.display = paso > 1 ? 'block' : 'none';
            btnGuardar.style.display = paso === totalPasos ? 'block' : 'none';
            
            console.log('✅ Paso', paso, 'mostrado - Botones:', {
                anterior: btnAnterior.style.display,
                siguiente: btnSiguiente.style.display,
                guardar: btnGuardar.style.display
            });
        }

        function pasoSiguiente() {
            console.log('➡️ pasoSiguiente() - pasoActual:', pasoActual, 'totalPasos:', totalPasos);
            if (pasoActual < totalPasos) {
                mostrarPaso(pasoActual + 1);
            }
        }

        function pasoAnterior() {
            console.log('⬅️ pasoAnterior() - pasoActual:', pasoActual);
            if (pasoActual > 1) {
                mostrarPaso(pasoActual - 1);
            }
        }

        function setFlujo(flujo) {
            console.log('💾 setFlujo(' + flujo + ')');
            document.getElementById('flujo_cliente').value = flujo;
        }

        // Mostrar paso 1 al cargar
        window.addEventListener('DOMContentLoaded', function() {
            console.log('🎯 DOMContentLoaded - inicializando');
            mostrarPaso(1);
        });
    </script>
@endpush
