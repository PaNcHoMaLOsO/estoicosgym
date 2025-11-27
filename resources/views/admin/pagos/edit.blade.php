@extends('adminlte::page')

@section('title', 'Editar Pago - EstóicosGym')

@section('css')
<style>
    * { font-family: 'Segoe UI', sans-serif; }
    
    .content-wrapper {
        background: #f5f7fa !important;
    }

    .header-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        margin: 0 -15px 30px -15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .header-section h2 {
        margin: 0;
        font-size: 1.8em;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-section p {
        margin: 8px 0 0 0;
        opacity: 0.95;
        font-size: 0.95em;
    }

    .card-section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
    }

    .card-title {
        font-size: 1.15em;
        font-weight: 700;
        color: #333;
        margin: 0 0 20px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #667eea;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .info-cell {
        background: linear-gradient(135deg, #f5f9ff 0%, #f0f5fd 100%);
        border-radius: 10px;
        padding: 15px;
        border-left: 4px solid #667eea;
    }

    .info-label {
        font-size: 0.75em;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .info-value {
        font-size: 1.1em;
        font-weight: 700;
        color: #333;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.95em;
        display: block;
    }

    .form-control {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 11px 14px;
        font-size: 0.95em;
        transition: all 0.3s ease;
        width: 100%;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .btn-group-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-top: 25px;
    }

    .btn-section {
        padding: 12px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.95em;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary-section:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary-section {
        background: #6c757d;
        color: white;
    }

    .btn-secondary-section:hover {
        background: #5a6268;
        transform: translateY(-3px);
    }

    .text-danger {
        color: #dc3545;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.85em;
        margin-top: 5px;
        display: block;
    }

    .two-column-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }

    @media (max-width: 1200px) {
        .two-column-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .header-section {
            padding: 20px 15px;
            margin: 0 -15px 20px -15px;
        }

        .header-section h2 {
            font-size: 1.3em;
        }

        .card-section {
            padding: 15px;
            margin-bottom: 15px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .btn-group-section {
            grid-template-columns: 1fr;
        }

        .btn-section {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
    <div class="header-section">
        <h2><i class="fas fa-edit"></i> Editar Pago</h2>
        <p>Modifica los detalles del pago registrado</p>
    </div>

    <div class="two-column-grid">
        <!-- FORMULARIO -->
        <form action="{{ route('admin.pagos.update', $pago) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- INFO ACTUAL -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-info-circle"></i> Pago Actual
                </div>
                <div class="info-grid">
                    <div class="info-cell">
                        <div class="info-label">Cliente</div>
                        <div class="info-value">{{ $pago->inscripcion->cliente->nombres }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Membresía</div>
                        <div class="info-value">{{ $pago->inscripcion->membresia->nombre }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Monto Pagado</div>
                        <div class="info-value">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Fecha</div>
                        <div class="info-value">{{ $pago->fecha_pago->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO EDICIÓN -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-pencil-alt"></i> Editar Datos
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="id_inscripcion">Inscripción <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_inscripcion') is-invalid @enderror" 
                                id="id_inscripcion" name="id_inscripcion" required>
                            @foreach($inscripciones as $insc)
                                <option value="{{ $insc->id }}" 
                                        {{ $pago->id_inscripcion == $insc->id ? 'selected' : '' }}>
                                    {{ $insc->cliente->nombres }} - {{ $insc->membresia->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_inscripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="monto_abonado">Monto ($) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                               id="monto_abonado" name="monto_abonado" 
                               value="{{ old('monto_abonado', $pago->monto_abonado) }}"
                               step="1000" min="0" required>
                        @error('monto_abonado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_pago">Fecha <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                               id="fecha_pago" name="fecha_pago" 
                               value="{{ old('fecha_pago', $pago->fecha_pago->format('Y-m-d')) }}" required>
                        @error('fecha_pago')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="id_metodo_pago_principal">Método <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_metodo_pago_principal') is-invalid @enderror" 
                                id="id_metodo_pago_principal" name="id_metodo_pago_principal" required>
                            @foreach($metodos_pago as $metodo)
                                <option value="{{ $metodo->id }}" 
                                        {{ $pago->id_metodo_pago_principal == $metodo->id ? 'selected' : '' }}>
                                    {{ $metodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_metodo_pago_principal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cantidad_cuotas">Cuotas</label>
                        <select class="form-control" id="cantidad_cuotas" name="cantidad_cuotas">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $pago->cantidad_cuotas == $i ? 'selected' : '' }}>
                                    {{ $i }} {{ $i == 1 ? 'cuota' : 'cuotas' }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="referencia_pago">Referencia</label>
                        <input type="text" class="form-control" id="referencia_pago" name="referencia_pago"
                               value="{{ old('referencia_pago', $pago->referencia_pago) }}"
                               placeholder="Ej: Transf #12345">
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" 
                              rows="3" placeholder="Notas...">{{ old('observaciones', $pago->observaciones) }}</textarea>
                </div>

                <div class="btn-group-section">
                    <a href="{{ route('admin.pagos.show', $pago) }}" class="btn-section btn-secondary-section">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn-section btn-primary-section">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>

        <!-- SIDEBAR INFO -->
        <div>
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-calendar"></i> Detalles
                </div>
                <div class="info-grid">
                    <div class="info-cell">
                        <div class="info-label">Creado</div>
                        <div class="info-value" style="font-size: 0.9em;">{{ $pago->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Actualizado</div>
                        <div class="info-value" style="font-size: 0.9em;">{{ $pago->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-link"></i> Enlaces
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" 
                       class="btn-section btn-secondary-section" style="justify-content: flex-start;">
                        <i class="fas fa-eye"></i> Ver Inscripción
                    </a>
                    <a href="{{ route('admin.pagos.show', $pago) }}" 
                       class="btn-section btn-secondary-section" style="justify-content: flex-start;">
                        <i class="fas fa-receipt"></i> Ver Pago
                    </a>
                    <a href="{{ route('admin.pagos.index') }}" 
                       class="btn-section btn-secondary-section" style="justify-content: flex-start;">
                        <i class="fas fa-list"></i> Todos los Pagos
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
