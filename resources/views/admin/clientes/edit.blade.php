@extends('adminlte::page')

@section('title', 'Editar Cliente - EstóicosGym')

@section('css')
    <style>
        /* ===== HERO HEADER ===== */
        .edit-hero {
            background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(255, 165, 0, 0.3);
        }

        .edit-hero h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* ===== SECTION HEADER ===== */
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 2rem 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.05rem;
        }

        .section-header i {
            font-size: 1.2em;
        }

        /* ===== FORM ===== */
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-text.text-muted {
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* ===== ALERTS ===== */
        .error-alert {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-left: 5px solid #dc3545;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            animation: slideDown 0.3s ease;
        }

        .error-alert h5 {
            color: #c62828;
            margin: 0 0 0.75rem 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-alert ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
            color: #c62828;
            line-height: 1.8;
        }

        .error-alert li {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== SPINNER ANIMATION ===== */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fa-spinner {
            animation: spin 1s linear infinite !important;
        }

        /* ===== STATE BADGE ===== */
        .state-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .state-active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .state-inactive {
            background: #e9ecef;
            color: #6c757d;
        }

        /* ===== AUDIT INFO ===== */
        .audit-info {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            border: 2px solid #667eea;
            padding: 1.25rem;
            border-radius: 0.75rem;
            margin-top: 2rem;
            font-size: 0.9rem;
        }

        .audit-info dt {
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.25rem;
        }

        .audit-info dd {
            margin-left: 0;
            color: #495057;
            margin-bottom: 1rem;
        }

        .audit-info dd:last-child {
            margin-bottom: 0;
        }

        /* ===== BUTTONS ===== */
        .btn-lg-custom {
            padding: 0.75rem 2rem;
            font-weight: 600;
        }

        .btn-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 0.75rem;
            border: 1px solid #dee2e6;
        }

        .btn-actions-left, .btn-actions-right {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* ===== CARD ===== */
        .card {
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
            border-bottom: 2px solid #ff8c00;
            color: white;
        }

        .card-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-header .card-tools {
            display: flex;
            gap: 0.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .edit-hero h2 {
                font-size: 1.25rem;
            }

            .btn-actions {
                flex-direction: column;
                padding: 1rem;
            }

            .btn-actions-left, .btn-actions-right {
                width: 100%;
                justify-content: stretch;
            }

            .btn-lg-custom {
                width: 100%;
                justify-content: center;
            }

            .section-header {
                margin: 1.5rem 0 0.75rem 0;
                font-size: 0.95rem;
            }
        }
    </style>
@endsection

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-edit"></i> Editar Cliente
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-outline-info mr-2">
                <i class="fas fa-arrow-up"></i> Ver Detalles
            </a>
            <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="error-alert">
            <h5><i class="fas fa-exclamation-triangle"></i> Errores en el Formulario</h5>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- HERO CON ESTADO -->
    <div class="edit-hero">
        <h2>{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</h2>
        <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">RUT: {{ $cliente->run_pasaporte }}</p>
        <div style="margin-top: 1rem;">
            <span class="state-badge {{ $cliente->activo ? 'state-active' : 'state-inactive' }}">
                {{ $cliente->activo ? '✓ Activo' : '✗ Inactivo' }}
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="flex-grow: 1;">
                <i class="fas fa-user-edit"></i> Editar Datos del Cliente
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">ID: {{ $cliente->id }}</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST" id="editClienteForm" onsubmit="return handleEditFormSubmit(event)">
                @csrf
                @method('PUT')
                <!-- Token anti-CSRF para prevenir doble envío -->
                <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">

                <!-- IDENTIFICACIÓN -->
                <div class="section-header">
                    <i class="fas fa-id-card"></i> Identificación
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="run_pasaporte" class="form-label">RUT/Pasaporte <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                               id="run_pasaporte" name="run_pasaporte" placeholder="XX.XXX.XXX-X" 
                               value="{{ old('run_pasaporte', $cliente->run_pasaporte) }}" required>
                        <small class="form-text text-muted">Formato: XX.XXX.XXX-X</small>
                        @error('run_pasaporte')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- DATOS PERSONALES -->
                <div class="section-header">
                    <i class="fas fa-user"></i> Datos Personales
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                               id="nombres" name="nombres" value="{{ old('nombres', $cliente->nombres) }}" required>
                        @error('nombres')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                               id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $cliente->apellido_paterno) }}" required>
                        @error('apellido_paterno')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="apellido_materno" class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror" 
                               id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $cliente->apellido_materno) }}">
                        @error('apellido_materno')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                               id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento?->format('Y-m-d')) }}">
                        @error('fecha_nacimiento')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- CONTACTO -->
                <div class="section-header">
                    <i class="fas fa-phone"></i> Contacto
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" placeholder="correo@ejemplo.com" value="{{ old('email', $cliente->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="celular" class="form-label">Celular <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('celular') is-invalid @enderror" 
                               id="celular" name="celular" placeholder="+56912345678" value="{{ old('celular', $cliente->celular) }}" required>
                        @error('celular')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- CONTACTO DE EMERGENCIA -->
                <div class="section-header">
                    <i class="fas fa-heart-pulse"></i> Contacto de Emergencia
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contacto_emergencia" class="form-label">Nombre del Contacto</label>
                        <input type="text" class="form-control @error('contacto_emergencia') is-invalid @enderror" 
                               id="contacto_emergencia" name="contacto_emergencia" placeholder="Ej: Juan García" 
                               value="{{ old('contacto_emergencia', $cliente->contacto_emergencia) }}">
                        @error('contacto_emergencia')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="telefono_emergencia" class="form-label">Teléfono del Contacto</label>
                        <input type="tel" class="form-control @error('telefono_emergencia') is-invalid @enderror" 
                               id="telefono_emergencia" name="telefono_emergencia" placeholder="+56912345678" 
                               value="{{ old('telefono_emergencia', $cliente->telefono_emergencia) }}">
                        @error('telefono_emergencia')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- DOMICILIO -->
                <div class="section-header">
                    <i class="fas fa-map-marker-alt"></i> Domicilio
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                               id="direccion" name="direccion" placeholder="Calle, número, apartado..." value="{{ old('direccion', $cliente->direccion) }}">
                        @error('direccion')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- CONVENIO -->
                <div class="section-header">
                    <i class="fas fa-handshake"></i> Convenio Principal
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_convenio" class="form-label">Convenio (Opcional)</label>
                        <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                id="id_convenio" name="id_convenio">
                            <option value="">-- Sin Convenio --</option>
                            @foreach($convenios as $convenio)
                                <option value="{{ $convenio->id }}" {{ old('id_convenio', $cliente->id_convenio) == $convenio->id ? 'selected' : '' }}>
                                    {{ $convenio->nombre }} ({{ $convenio->descuento_porcentaje }}% desc.)
                                </option>
                            @endforeach
                        </select>
                        @error('id_convenio')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- OBSERVACIONES -->
                <div class="section-header">
                    <i class="fas fa-sticky-note"></i> Observaciones
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="observaciones" class="form-label">Notas Adicionales</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                  id="observaciones" name="observaciones" rows="4" placeholder="Información adicional sobre el cliente...">{{ old('observaciones', $cliente->observaciones) }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- INFORMACIÓN DE AUDITORÍA -->
                <div class="audit-info">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <dt><i class="fas fa-calendar"></i> Registrado:</dt>
                            <dd>{{ $cliente->created_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                        <div class="col-md-6">
                            <dt><i class="fas fa-history"></i> Última Actualización:</dt>
                            <dd>{{ $cliente->updated_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="btn-actions">
                    <div class="btn-actions-left">
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary btn-lg-custom">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div class="btn-actions-right">
                        <button type="submit" class="btn btn-warning btn-lg-custom" id="btn-guardar-cambios">
                            <i class="fas fa-save"></i> <span id="btn-text">Guardar Cambios</span>
                            <span id="btn-spinner" style="display:none; margin-left: 0.5rem;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let isSubmitting = false;

        function handleEditFormSubmit(event) {
            event.preventDefault();
            
            // Prevenir doble envío
            if (isSubmitting) {
                console.warn('Formulario ya se está enviando...');
                return false;
            }
            
            // Deshabilitar botón y mostrar spinner
            const btnGuardar = document.getElementById('btn-guardar-cambios');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');
            const formToken = document.getElementById('form_submit_token');
            
            // Marcar como enviando
            isSubmitting = true;
            
            // UI feedback
            btnGuardar.disabled = true;
            btnText.textContent = 'Procesando...';
            btnSpinner.style.display = 'inline';
            
            // Generar nuevo token para evitar reenvíos
            formToken.value = '{{ uniqid() }}-' + Date.now();
            
            // Enviar formulario después de un pequeño delay
            setTimeout(() => {
                document.getElementById('editClienteForm').submit();
            }, 100);
            
            // Timeout de seguridad
            setTimeout(() => {
                if (isSubmitting) {
                    isSubmitting = false;
                    btnGuardar.disabled = false;
                    btnText.textContent = 'Guardar Cambios';
                    btnSpinner.style.display = 'none';
                }
            }, 5000);
            
            return false;
        }

        // Validación básica de RUT
        document.getElementById('run_pasaporte').addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && !value.match(/^\d{1,2}\.\d{3}\.\d{3}-[0-9K]$/)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Validación de email
        document.getElementById('email').addEventListener('blur', function() {
            const value = this.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (value && !emailRegex.test(value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Scroll suave a errores
        const errorAlert = document.querySelector('.error-alert');
        if (errorAlert) {
            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
@stop
