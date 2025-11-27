@extends('adminlte::page')

@section('title', 'Editar Pago - EstóicosGym')

@section('css')
<style>
    body { background: #f5f5f5; }
    .page-container { background: white; }
    
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        margin: -20px -15px 30px -15px;
        border-radius: 0 0 12px 12px;
    }
    
    .page-header h1 {
        margin: 0;
        font-size: 2.2em;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .form-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.1em;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid #667eea;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .pago-info {
        background: #f0f9ff;
        border-left: 4px solid #667eea;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
    
    .pago-info h5 {
        margin-top: 0;
        color: #333;
    }
    
    .info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 15px;
    }
    
    .info-item {
        background: white;
        padding: 12px;
        border-radius: 6px;
        border-left: 3px solid #667eea;
    }
    
    .info-label {
        font-size: 0.8em;
        color: #999;
        text-transform: uppercase;
    }
    
    .info-value {
        font-size: 1.2em;
        font-weight: 700;
        color: #333;
    }
    
    .btn-actions {
        display: flex;
        gap: 10px;
        margin-top: 30px;
    }
    
    .btn-actions a,
    .btn-actions button {
        flex: 1;
        padding: 14px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s ease;
    }
    
    .btn-actions button[type="submit"] {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-actions button[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-actions a {
        background: #6c757d;
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-actions a:hover {
        background: #5a6268;
    }
    
    .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .form-control {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 10px 12px;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    @media (max-width: 768px) {
        .page-header { padding: 20px; }
        .page-header h1 { font-size: 1.5em; }
        .info-row { grid-template-columns: 1fr; }
        .btn-actions { flex-direction: column; }
    }
</style>
@endsection

@section('content')
<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Editar Pago</h1>
        <p class="text-white mt-2">Modifica los detalles del pago registrado</p>
    </div>

    <div class="container-fluid">
        <!-- Información del Pago -->
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-info-circle"></i> Información Actual del Pago
            </div>
            
            <div class="pago-info">
                <h5>{{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}</h5>
                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">Membresía</div>
                        <div class="info-value">{{ $pago->inscripcion->membresia->nombre }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Inscripción</div>
                        <div class="info-value">${{ number_format($pago->monto_total, 0, '.', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Monto Pagado</div>
                        <div class="info-value">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Fecha Pago</div>
                        <div class="info-value">{{ $pago->fecha_pago->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Edición -->
        <form action="{{ route('admin.pagos.update', $pago) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-pencil-alt"></i> Editar Datos del Pago
                </div>

                <div class="form-group">
                    <label for="id_inscripcion">Inscripción <span class="text-danger">*</span></label>
                    <select class="form-control @error('id_inscripcion') is-invalid @enderror" 
                            id="id_inscripcion" name="id_inscripcion" required>
                        @foreach($inscripciones as $insc)
                            <option value="{{ $insc->id }}" 
                                    {{ $pago->id_inscripcion == $insc->id ? 'selected' : '' }}>
                                {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} - {{ $insc->membresia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_inscripcion')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="monto_abonado">Monto Pagado ($) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                               id="monto_abonado" name="monto_abonado" 
                               value="{{ old('monto_abonado', $pago->monto_abonado) }}"
                               step="1000" min="0" required>
                        @error('monto_abonado')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="id_metodo_pago_principal">Método de Pago <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_metodo_pago_principal') is-invalid @enderror" 
                                id="id_metodo_pago_principal" name="id_metodo_pago_principal" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($metodos_pago as $metodo)
                                <option value="{{ $metodo->id }}" 
                                        {{ $pago->id_metodo_pago_principal == $metodo->id ? 'selected' : '' }}>
                                    {{ $metodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_metodo_pago_principal')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                               id="fecha_pago" name="fecha_pago" 
                               value="{{ old('fecha_pago', $pago->fecha_pago->format('Y-m-d')) }}" required>
                        @error('fecha_pago')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="cantidad_cuotas">Cantidad de Cuotas</label>
                        <select class="form-control" id="cantidad_cuotas" name="cantidad_cuotas">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $pago->cantidad_cuotas == $i ? 'selected' : '' }}>
                                    {{ $i }} {{ $i == 1 ? 'cuota' : 'cuotas' }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="referencia_pago">Referencia (Comprobante, etc)</label>
                    <input type="text" class="form-control" id="referencia_pago" name="referencia_pago"
                           value="{{ old('referencia_pago', $pago->referencia_pago) }}"
                           placeholder="Ej: Transferencia #123456">
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" 
                              rows="3" placeholder="Notas adicionales...">{{ old('observaciones', $pago->observaciones) }}</textarea>
                </div>
            </div>

            <!-- Botones -->
            <div class="btn-actions">
                <a href="{{ route('admin.pagos.show', $pago) }}" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
