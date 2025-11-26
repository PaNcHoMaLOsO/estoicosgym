@extends('adminlte::page')

@section('title', 'Editar Cliente - EstóicosGym')

@section('css')
    <style>
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 1.5rem 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }

        .section-header i {
            font-size: 1.1em;
        }

        .error-alert {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-left: 5px solid #dc3545;
            border-radius: 0.5rem;
            animation: slideDown 0.3s ease;
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

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-lg-custom {
            padding: 0.75rem 2rem;
            font-weight: 600;
        }

        .audit-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            border-left: 4px solid #667eea;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        .audit-info dt {
            font-weight: 600;
            color: #495057;
        }

        .audit-info dd {
            margin-left: 1rem;
            color: #6c757d;
        }

        .state-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        .state-active {
            background: #d4edda;
            color: #155724;
        }

        .state-inactive {
            background: #e2e3e5;
            color: #383d41;
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
        <div class="alert error-alert alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="d-flex align-items-start">
                <div class="mr-3" style="font-size: 1.8rem; flex-shrink: 0; color: #d32f2f;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div style="flex: 1;">
                    <h5 class="alert-heading" style="color: #c62828; margin: 0 0 0.75rem 0; font-weight: 700;">
                        ⚠️ Errores en el Formulario
                    </h5>
                    <ul class="mb-0 pl-4" style="font-size: 0.95rem; color: #c62828; line-height: 1.8;">
                        @foreach ($errors->all() as $error)
                            <li class="mb-2"><strong>{{ $error }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="card card-warning">
        <div class="card-header bg-warning">
            <h3 class="card-title">
                <i class="fas fa-user-edit"></i> Editar Datos del Cliente
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    ID: {{ $cliente->id }} | UUID: {{ substr($cliente->uuid, 0, 8) }}...
                </span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Estado del Cliente -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="state-badge {{ $cliente->activo ? 'state-active' : 'state-inactive' }}">
                            <i class="fas {{ $cliente->activo ? 'fa-check-circle' : 'fa-ban' }}"></i>
                            Estado: {{ $cliente->activo ? 'ACTIVO' : 'INACTIVO' }}
                        </div>
                    </div>
                </div>

                <!-- Sección Identificación -->
                <div class="section-header">
                    <i class="fas fa-id-card"></i> Identificación
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="run_pasaporte" class="form-label">RUT/Pasaporte <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                               id="run_pasaporte" name="run_pasaporte" placeholder="XX.XXX.XXX-X" 
                               value="{{ old('run_pasaporte', $cliente->run_pasaporte) }}" required>
                        <small class="form-text text-muted d-block mt-1">Formato: XX.XXX.XXX-X</small>
                        @error('run_pasaporte')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Sección Datos Personales -->
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

                <!-- Sección Contacto -->
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

                <!-- Sección Contacto de Emergencia -->
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

                <!-- Sección Domicilio -->
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

                <!-- Sección Convenio -->
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

                <!-- Sección Observaciones -->
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

                <!-- Sección Auditoría -->
                <div class="audit-info">
                    <div class="row">
                        <div class="col-md-6">
                            <dt>Registrado:</dt>
                            <dd>{{ $cliente->created_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                        <div class="col-md-6">
                            <dt>Última Actualización:</dt>
                            <dd>{{ $cliente->updated_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Botones de Acción -->
                <div class="form-group d-flex gap-2 justify-content-between flex-wrap">
                    <div>
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary btn-lg-custom">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-warning btn-lg-custom">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop