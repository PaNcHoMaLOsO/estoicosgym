@extends('adminlte::page')

@section('title', 'Editar Pago - EstóicosGym')

@section('css')
<style>
    * { font-family: 'Segoe UI', sans-serif; }
    
    .content-wrapper {
        background: #f8f9fa !important;
        padding-bottom: 40px;
    }

    /* HEADER */
    .header-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 30px;
        margin: 0 -15px 40px -15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .header-section h2 {
        margin: 0;
        font-size: 2em;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-section p {
        margin: 12px 0 0 0;
        opacity: 0.95;
        font-size: 1.05em;
    }

    /* CARDS */
    .card-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 0;
        box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        border: 1px solid #e8eef5;
    }

    .card-title {
        font-size: 1.35em;
        font-weight: 700;
        color: #333;
        margin: 0 0 28px 0;
        padding-bottom: 20px;
        border-bottom: 3px solid #667eea;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .card-title i {
        color: #667eea;
        font-size: 1.2em;
    }

    /* INFO GRID */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .info-cell {
        background: linear-gradient(135deg, #f8fbff 0%, #f2f7fd 100%);
        border-radius: 12px;
        padding: 22px;
        border-left: 5px solid #667eea;
        border-top: 1px solid #e8eef5;
        transition: all 0.3s ease;
    }

    .info-cell:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(102, 126, 234, 0.2);
        border-left-color: #764ba2;
    }

    .info-label {
        font-size: 0.75em;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .info-value {
        font-size: 1.3em;
        font-weight: 700;
        color: #2c3e50;
    }

    /* FORMULARIO */
    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
        font-size: 0.95em;
        display: block;
        text-transform: capitalize;
    }

    .form-control {
        border: 2px solid #e8eef5;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.95em;
        transition: all 0.3s ease;
        width: 100%;
        background: #f9fbfc;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
        outline: none;
        background: white;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    /* BOTONES */
    .btn-group-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
        margin-top: 30px;
    }

    .btn-section {
        padding: 14px 24px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.95em;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-primary-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary-section:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary-section {
        background: #f0f2f5;
        color: #333;
        border: 2px solid #ddd;
    }

    .btn-secondary-section:hover {
        background: #e8ebed;
        transform: translateY(-4px);
    }

    /* ERRORES */
    .text-danger {
        color: #e74c3c;
    }

    .is-invalid {
        border-color: #e74c3c !important;
        background-color: #fef5f5;
    }

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.85em;
        margin-top: 8px;
        display: block;
        font-weight: 600;
    }

    /* LAYOUT */
    .two-column-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 30px;
    }

    .section-wrapper {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    /* ENLACES */
    .link-button {
        width: 100%;
        text-align: left;
    }

    /* SEPARADOR */
    .separator {
        height: 1px;
        background: #e8eef5;
        margin: 20px 0;
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
        .two-column-grid {
            grid-template-columns: 1fr;
        }

        .info-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .header-section {
            padding: 25px 15px;
            margin: 0 -15px 25px -15px;
        }

        .header-section h2 {
            font-size: 1.4em;
        }

        .card-section {
            padding: 20px;
        }

        .card-title {
            font-size: 1.15em;
            margin-bottom: 20px;
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
        <p>Modifica los detalles del registro de pago</p>
    </div>

    <div class="two-column-grid">
        <!-- COLUMNA IZQUIERDA (FORMULARIO) -->
        <div class="section-wrapper">
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
                            <div class="info-value" style="color: #27ae60;">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</div>
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
                            <label for="cantidad_cuotas">Cantidad de Cuotas</label>
                            <select class="form-control" id="cantidad_cuotas" name="cantidad_cuotas">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $pago->cantidad_cuotas == $i ? 'selected' : '' }}>
                                        {{ $i }} {{ $i == 1 ? 'cuota' : 'cuotas' }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="referencia_pago">Referencia/Comprobante</label>
                            <input type="text" class="form-control" id="referencia_pago" name="referencia_pago"
                                   value="{{ old('referencia_pago', $pago->referencia_pago) }}"
                                   placeholder="Ej: Transferencia #12345">
                        </div>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" 
                                  rows="4" placeholder="Notas adicionales sobre este pago...">{{ old('observaciones', $pago->observaciones) }}</textarea>
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
        </div>

        <!-- COLUMNA DERECHA (SIDEBAR) -->
        <div class="section-wrapper">
            <!-- DETALLES -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-calendar"></i> Detalles del Registro
                </div>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <div style="font-size: 0.75em; color: #999; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Creado</div>
                        <div style="color: #333; font-weight: 600; font-size: 1.05em;">{{ $pago->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75em; color: #999; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Actualizado</div>
                        <div style="color: #333; font-weight: 600; font-size: 1.05em;">{{ $pago->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN RESUMIDA -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-money-bill-wave"></i> Resumen
                </div>
                <div class="info-grid">
                    <div class="info-cell" style="grid-column: span 2;">
                        <div class="info-label">Total Inscripción</div>
                        <div class="info-value">${{ number_format($pago->monto_total, 0, '.', '.') }}</div>
                    </div>
                    <div class="info-cell" style="grid-column: span 2;">
                        <div class="info-label">Pendiente</div>
                        <div class="info-value" style="color: #e67e22;">${{ number_format($pago->monto_pendiente, 0, '.', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- ENLACES RÁPIDOS -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-link"></i> Enlaces Rápidos
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" 
                       class="btn-section btn-secondary-section link-button">
                        <i class="fas fa-eye"></i> Ver Inscripción
                    </a>
                    <a href="{{ route('admin.pagos.show', $pago) }}" 
                       class="btn-section btn-secondary-section link-button">
                        <i class="fas fa-receipt"></i> Ver Detalles
                    </a>
                    <a href="{{ route('admin.pagos.index') }}" 
                       class="btn-section btn-secondary-section link-button">
                        <i class="fas fa-list"></i> Todos los Pagos
                    </a>
                    <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" 
                       class="btn-section btn-secondary-section link-button">
                        <i class="fas fa-user"></i> Ver Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
