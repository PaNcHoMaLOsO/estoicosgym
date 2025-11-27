@extends('adminlte::page')

@section('title', 'Detalles Pago - EstóicosGym')

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

    /* GRIDS */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 22px;
    }

    .info-cell {
        background: linear-gradient(135deg, #f8fbff 0%, #f2f7fd 100%);
        border-radius: 12px;
        padding: 24px;
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
        margin-bottom: 12px;
        font-weight: 700;
    }

    .info-value {
        font-size: 1.5em;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1.2;
    }

    .info-value.success {
        color: #27ae60;
    }

    .info-value.warning {
        color: #e67e22;
    }

    /* BADGES */
    .badge-status {
        display: inline-block;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 0.9em;
        font-weight: 600;
        margin-top: 8px;
    }

    .badge-paid {
        background: #d5f4e6;
        color: #27ae60;
        border: 1px solid #a9dfbf;
    }

    .badge-partial {
        background: #fdebd0;
        color: #d68910;
        border: 1px solid #f8b88b;
    }

    /* TABLA */
    .table-section {
        overflow-x: auto;
    }

    .historial-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95em;
    }

    .historial-table th {
        background: linear-gradient(135deg, #f8fbff 0%, #f2f7fd 100%);
        padding: 18px 22px;
        text-align: left;
        font-weight: 700;
        border-bottom: 2px solid #667eea;
        color: #333;
    }

    .historial-table td {
        padding: 16px 22px;
        border-bottom: 1px solid #eee;
    }

    .historial-table tr:hover {
        background: #f9fbfc;
    }

    .historial-table tr:last-child td {
        border-bottom: none;
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

    .btn-danger-section {
        background: #fee;
        color: #c82333;
        border: 2px solid #fcc;
    }

    .btn-danger-section:hover {
        background: #fdd;
        transform: translateY(-4px);
    }

    /* ENLACES */
    .link-item {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .link-item:hover {
        color: #764ba2;
        gap: 12px;
    }

    /* LAYOUT */
    .two-column-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 30px;
    }

    .single-column {
        grid-column: 1 / -1;
    }

    /* CONTENEDOR SECTORES */
    .section-wrapper {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    /* ALERTA */
    .alert-success {
        background: linear-gradient(135deg, #d5f4e6 0%, #c9e4de 100%);
        color: #27ae60;
        border: 2px solid #a9dfbf;
        border-radius: 10px;
        padding: 18px 24px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 15px;
        font-weight: 600;
    }

    /* SEPARADOR */
    .separator {
        height: 1px;
        background: #e8eef5;
        margin: 25px 0;
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
    <!-- ALERTA ÉXITO -->
    @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle" style="font-size: 1.3em;"></i> 
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- HEADER -->
    <div class="header-section">
        <h2><i class="fas fa-receipt"></i> Detalles del Pago</h2>
        <p>Información completa del registro - {{ $pago->fecha_pago->format('d/m/Y') }}</p>
    </div>

    <!-- GRID DOS COLUMNAS -->
    <div class="two-column-grid">
        <!-- COLUMNA IZQUIERDA (PRINCIPAL) -->
        <div class="section-wrapper">
            <!-- INFORMACIÓN MONTOS -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-money-bill-wave"></i> Montos del Pago
                </div>
                <div class="info-grid">
                    <div class="info-cell">
                        <div class="info-label">Monto Pagado</div>
                        <div class="info-value success">${{ number_format($pago->monto_abonado, 0, '.', '.') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Total Inscripción</div>
                        <div class="info-value">${{ number_format($pago->monto_total, 0, '.', '.') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Pendiente</div>
                        <div class="info-value warning">${{ number_format($pago->monto_pendiente, 0, '.', '.') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">% Pagado</div>
                        <div class="info-value">{{ number_format(($pago->monto_abonado / $pago->monto_total) * 100, 1) }}%</div>
                    </div>
                </div>
            </div>

            <!-- DETALLES DE LA TRANSACCIÓN -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-file-invoice"></i> Detalles de la Transacción
                </div>
                <div class="info-grid">
                    <div class="info-cell">
                        <div class="info-label">Método de Pago</div>
                        <div class="info-value">{{ $pago->metodoPagoPrincipal?->nombre ?? 'N/A' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Fecha del Pago</div>
                        <div class="info-value">{{ $pago->fecha_pago->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Cuotas</div>
                        <div class="info-value">{{ $pago->cantidad_cuotas }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Estado</div>
                        <div style="margin-top: 6px;">
                            @if($pago->monto_pendiente == 0)
                                <span class="badge-status badge-paid"><i class="fas fa-check"></i> Pagado</span>
                            @else
                                <span class="badge-status badge-partial"><i class="fas fa-clock"></i> Parcial</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($pago->referencia_pago)
                <div class="separator"></div>
                <div style="padding-top: 10px;">
                    <div style="font-size: 0.8em; color: #999; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Referencia/Comprobante</div>
                    <div style="background: #f8fbfc; padding: 14px; border-radius: 8px; border-left: 4px solid #667eea; color: #333; font-weight: 600; word-break: break-all;">
                        {{ $pago->referencia_pago }}
                    </div>
                </div>
                @endif
            </div>

            <!-- INFORMACIÓN DEL CLIENTE -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-user-circle"></i> Información del Cliente
                </div>
                <div class="info-grid">
                    <div class="info-cell" style="grid-column: span 2;">
                        <div class="info-label">Nombre Completo</div>
                        <div class="info-value">{{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Membresía</div>
                        <div class="info-value">{{ $pago->inscripcion->membresia->nombre }}</div>
                    </div>
                    <div class="info-cell" style="grid-column: span 2;">
                        <div class="info-label">Email</div>
                        <div class="info-value" style="font-size: 1.1em; word-break: break-all;">{{ $pago->inscripcion->cliente->email }}</div>
                    </div>
                </div>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e8eef5;">
                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="link-item">
                        <i class="fas fa-arrow-right"></i> Ver Inscripción Completa
                    </a>
                </div>
            </div>

            <!-- HISTORIAL DE PAGOS -->
            @if($pago->inscripcion->pagos->count() > 0)
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-history"></i> Historial de Pagos
                </div>
                <div class="table-section">
                    <table class="historial-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">#</th>
                                <th style="width: 25%;">Fecha</th>
                                <th style="width: 30%;">Monto</th>
                                <th style="width: 35%;">Método</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pago->inscripcion->pagos->sortByDesc('fecha_pago') as $p)
                            <tr>
                                <td><strong>{{ $loop->iteration }}</strong></td>
                                <td>{{ $p->fecha_pago->format('d/m/Y') }}</td>
                                <td><strong style="color: #27ae60;">${{ number_format($p->monto_abonado, 0, '.', '.') }}</strong></td>
                                <td>{{ $p->metodoPagoPrincipal?->nombre ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- COLUMNA DERECHA (SIDEBAR) -->
        <div class="section-wrapper">
            <!-- FECHAS IMPORTANTES -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-calendar-alt"></i> Fechas Importantes
                </div>
                <div class="info-grid">
                    <div class="info-cell" style="grid-column: span 2;">
                        <div class="info-label">Inicio Membresía</div>
                        <div class="info-value">{{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-cell" style="grid-column: span 2;">
                        <div class="info-label">Vencimiento</div>
                        <div class="info-value">{{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-cell" style="grid-column: span 2;">
                        <div class="info-label">Días Restantes</div>
                        <div class="info-value">{{ max(0, now()->diffInDays($pago->inscripcion->fecha_vencimiento, false)) }}</div>
                    </div>
                </div>
            </div>

            <!-- TIMESTAMPS -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-clock"></i> Registro
                </div>
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <div>
                        <div style="font-size: 0.75em; color: #999; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Creado</div>
                        <div style="color: #333; font-weight: 600; font-size: 1.1em;">{{ $pago->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75em; color: #999; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Actualizado</div>
                        <div style="color: #333; font-weight: 600; font-size: 1.1em;">{{ $pago->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- ACCIONES -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-tools"></i> Acciones
                </div>
                <div class="btn-group-section">
                    <a href="{{ route('admin.pagos.index') }}" class="btn-section btn-secondary-section">
                        <i class="fas fa-list"></i> Lista
                    </a>
                    <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn-section btn-primary-section">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" style="display: contents;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-section btn-danger-section" style="grid-column: 1 / -1;"
                                onclick="return confirm('¿Eliminar este pago? No se puede deshacer.')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>

            <!-- ENLACES RÁPIDOS -->
            <div class="card-section">
                <div class="card-title">
                    <i class="fas fa-link"></i> Enlaces Rápidos
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="link-item">
                        <i class="fas fa-arrow-right"></i> Ver Inscripción
                    </a>
                    <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" class="link-item">
                        <i class="fas fa-arrow-right"></i> Ver Cliente
                    </a>
                    <a href="{{ route('admin.pagos.index') }}" class="link-item">
                        <i class="fas fa-arrow-right"></i> Todos los Pagos
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
