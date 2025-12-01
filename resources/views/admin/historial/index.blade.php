@extends('adminlte::page')

@section('title', 'Historial - EstóicosGym')

@section('css')
    <style>
        :root {
            --primary: #1a1a2e;
            --primary-light: #16213e;
            --accent: #e94560;
            --accent-light: #ff6b6b;
            --success: #00bf8e;
            --success-dark: #00a67d;
            --warning: #f0a500;
            --info: #4361ee;
            --purple: #6f42c1;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-600: #6c757d;
            --gray-800: #343a40;
        }

        /* ===== TABS ===== */
        .historial-tabs {
            background: white;
            border-radius: 16px;
            padding: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            display: flex;
            gap: 0.5rem;
        }

        .historial-tab {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            color: var(--gray-600);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .historial-tab:hover {
            background: var(--gray-100);
            color: var(--primary);
            text-decoration: none;
        }

        .historial-tab.active {
            background: linear-gradient(135deg, var(--purple), #8b5cf6);
            color: white;
        }

        .historial-tab .badge-tab {
            background: rgba(255,255,255,0.2);
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
        }

        .historial-tab:not(.active) .badge-tab {
            background: var(--gray-200);
            color: var(--gray-600);
        }

        /* ===== CARDS ESTADÍSTICAS ===== */
        .stat-card {
            border: 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            border-radius: 16px;
            background: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-left: 5px solid var(--info);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        }

        .stat-card.success { border-left-color: var(--success); }
        .stat-card.warning { border-left-color: var(--warning); }
        .stat-card.danger { border-left-color: var(--accent); }
        .stat-card.purple { border-left-color: var(--purple); }

        .stat-number {
            font-size: 2.2em;
            font-weight: 800;
            color: var(--info);
        }

        .stat-card.success .stat-number { color: var(--success); }
        .stat-card.warning .stat-number { color: var(--warning); }
        .stat-card.danger .stat-number { color: var(--accent); }
        .stat-card.purple .stat-number { color: var(--purple); }
        .stat-card.purple .stat-number { color: #6f42c1; }

        .stat-label {
            color: var(--gray-600);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* ===== FILTROS ===== */
        .filter-box {
            background: white;
            border: none;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .filter-box input,
        .filter-box select {
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            padding: 0.6rem 1rem;
            transition: all 0.3s ease;
        }

        .filter-box input:focus,
        .filter-box select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
        }

        /* ===== TABLA ===== */
        .table-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .table-card .card-header {
            background: var(--primary);
            color: white;
            border-bottom: none;
            padding: 1rem 1.25rem;
        }

        .table-card .card-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: var(--primary);
        }

        .table thead th {
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border: none;
            padding: 1rem 0.75rem;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(233, 69, 96, 0.05);
        }

        .table td {
            vertical-align: middle;
            padding: 0.85rem 0.75rem;
            border-color: var(--gray-200);
        }

        /* ===== TRASPASO CARD ===== */
        .traspaso-flow {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .traspaso-cliente {
            background: var(--gray-100);
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .traspaso-cliente.origen {
            border-left: 3px solid var(--warning);
        }

        .traspaso-cliente.destino {
            border-left: 3px solid var(--success);
        }

        .traspaso-arrow {
            color: var(--accent);
            font-size: 1.25rem;
        }

        /* ===== BADGES ===== */
        .badge-deuda {
            background: var(--accent);
            color: white;
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-sin-deuda {
            background: var(--success);
            color: white;
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-membresia {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* ===== BOTONES ===== */
        .btn-estoicos {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-estoicos:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 69, 96, 0.3);
            color: white;
        }

        .btn-outline-estoicos {
            border: 2px solid var(--accent);
            color: var(--accent);
            background: transparent;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-estoicos:hover {
            background: var(--accent);
            color: white;
        }

        .btn-info-small {
            background: var(--info);
            border: none;
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-info-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(67, 97, 238, 0.3);
            color: white;
        }

        /* ===== PÁGINA TÍTULO ===== */
        .page-title {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray-600);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--gray-200);
        }

        /* ===== PAGINACIÓN ===== */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            border: none;
            color: var(--primary);
            border-radius: 8px;
            margin: 0 3px;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: var(--accent);
            color: white;
        }

        .page-item.active .page-link {
            background: var(--accent);
            border-color: var(--accent);
        }
    </style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-history text-purple mr-2" style="color: #6f42c1;"></i>
                Historial
            </h1>
            <p class="page-subtitle">Registro de actividades y movimientos del sistema</p>
        </div>
    </div>
@endsection

@section('content')
    <!-- Tabs de navegación -->
    <div class="historial-tabs">
        <a href="{{ route('admin.historial.index', ['tab' => 'traspasos']) }}" 
           class="historial-tab {{ $tab == 'traspasos' ? 'active' : '' }}">
            <i class="fas fa-exchange-alt"></i>
            Traspasos
            <span class="badge-tab">{{ $estadisticas['total_traspasos'] }}</span>
        </a>
        {{-- Tabs futuros --}}
        {{-- 
        <a href="{{ route('admin.historial.index', ['tab' => 'pausas']) }}" 
           class="historial-tab {{ $tab == 'pausas' ? 'active' : '' }}">
            <i class="fas fa-pause-circle"></i>
            Pausas
        </a>
        <a href="{{ route('admin.historial.index', ['tab' => 'cambios']) }}" 
           class="historial-tab {{ $tab == 'cambios' ? 'active' : '' }}">
            <i class="fas fa-sync-alt"></i>
            Cambios de Plan
        </a>
        --}}
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card card p-3">
                <div class="stat-number">{{ $estadisticas['total_traspasos'] }}</div>
                <div class="stat-label">Total Traspasos</div>
                <i class="fas fa-exchange-alt position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); font-size: 2.5rem; opacity: 0.1;"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card success card p-3">
                <div class="stat-number">{{ $estadisticas['traspasos_mes'] }}</div>
                <div class="stat-label">Este Mes</div>
                <i class="fas fa-calendar-check position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); font-size: 2.5rem; opacity: 0.1;"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card warning card p-3">
                <div class="stat-number">{{ $estadisticas['con_deuda_transferida'] }}</div>
                <div class="stat-label">Con Deuda Transferida</div>
                <i class="fas fa-exclamation-circle position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); font-size: 2.5rem; opacity: 0.1;"></i>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card danger card p-3">
                <div class="stat-number">${{ number_format($estadisticas['total_deuda_transferida'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Deuda Transferida</div>
                <i class="fas fa-hand-holding-usd position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); font-size: 2.5rem; opacity: 0.1;"></i>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-box">
        <form method="GET" action="{{ route('admin.historial.index') }}">
            <input type="hidden" name="tab" value="traspasos">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted">Cliente</label>
                    <select name="cliente_id" class="form-control">
                        <option value="">-- Todos los clientes --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold text-muted">Membresía</label>
                    <select name="membresia_id" class="form-control">
                        <option value="">-- Todas --</option>
                        @foreach($membresias as $membresia)
                            <option value="{{ $membresia->id }}" {{ request('membresia_id') == $membresia->id ? 'selected' : '' }}>
                                {{ $membresia->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold text-muted">Fecha Desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold text-muted">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <div class="custom-control custom-checkbox mt-4">
                        <input type="checkbox" class="custom-control-input" id="con_deuda" name="con_deuda" value="1" {{ request('con_deuda') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="con_deuda">Solo con deuda</label>
                    </div>
                </div>
                <div class="col-md-1 mb-2">
                    <button type="submit" class="btn btn-estoicos w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Traspasos -->
    <div class="card table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-list mr-2"></i>Registro de Traspasos</h5>
            <span class="badge badge-light">{{ $traspasos->total() }} registros</span>
        </div>
        <div class="card-body p-0">
            @if($traspasos->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <h5>No hay traspasos registrados</h5>
                    <p class="text-muted">Los traspasos de membresías aparecerán aquí cuando se realicen.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Traspaso</th>
                                <th>Membresía</th>
                                <th class="text-center">Días</th>
                                <th class="text-right">Pagado</th>
                                <th class="text-center">Deuda</th>
                                <th>Motivo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($traspasos as $traspaso)
                                <tr>
                                    <td>
                                        <strong>{{ $traspaso->fecha_traspaso->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $traspaso->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="traspaso-flow">
                                            <div class="traspaso-cliente origen">
                                                <i class="fas fa-user-minus text-warning mr-1"></i>
                                                @if($traspaso->clienteOrigen)
                                                    {{ $traspaso->clienteOrigen->nombres }} {{ $traspaso->clienteOrigen->apellido_paterno }}
                                                @else
                                                    <span class="text-muted">Cliente eliminado</span>
                                                @endif
                                            </div>
                                            <span class="traspaso-arrow">
                                                <i class="fas fa-arrow-right"></i>
                                            </span>
                                            <div class="traspaso-cliente destino">
                                                <i class="fas fa-user-plus text-success mr-1"></i>
                                                @if($traspaso->clienteDestino)
                                                    {{ $traspaso->clienteDestino->nombres }} {{ $traspaso->clienteDestino->apellido_paterno }}
                                                @else
                                                    <span class="text-muted">Cliente eliminado</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-membresia">
                                            {{ $traspaso->membresia->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $traspaso->dias_restantes_traspasados }}</strong>
                                        <br>
                                        <small class="text-muted">días</small>
                                    </td>
                                    <td class="text-right">
                                        <strong>${{ number_format($traspaso->monto_pagado, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($traspaso->se_transfirio_deuda)
                                            <span class="badge-deuda">
                                                ${{ number_format($traspaso->deuda_transferida, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="badge-sin-deuda">
                                                <i class="fas fa-check"></i> Sin deuda
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span title="{{ $traspaso->motivo }}">
                                            {{ Str::limit($traspaso->motivo, 30) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.historial.traspaso.show', $traspaso) }}" 
                                           class="btn btn-info-small"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($traspasos->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-center">
                    {{ $traspasos->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
