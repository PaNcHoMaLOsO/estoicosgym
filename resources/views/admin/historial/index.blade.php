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
            --warning: #f0a500;
            --info: #4361ee;
            --purple: #6f42c1;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-600: #6c757d;
            --gray-800: #343a40;
        }

        .page-title {
            color: var(--primary);
            font-weight: 700;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        /* ===== TABS PRINCIPALES ===== */
        .historial-tabs {
            background: white;
            border-radius: 16px;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .historial-tab {
            padding: 0.85rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            color: var(--gray-600);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border: 2px solid transparent;
        }

        .historial-tab:hover {
            background: var(--gray-100);
            color: var(--primary);
            text-decoration: none;
        }

        .historial-tab.active {
            background: linear-gradient(135deg, var(--purple), #8b5cf6);
            color: white;
            box-shadow: 0 4px 15px rgba(111, 66, 193, 0.3);
        }

        .historial-tab .badge-count {
            background: rgba(255,255,255,0.25);
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .historial-tab:not(.active) .badge-count {
            background: var(--gray-200);
            color: var(--gray-600);
        }

        .historial-tab i {
            font-size: 1.1rem;
        }

        /* ===== RESUMEN CARDS ===== */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: white;
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            border-left: 4px solid var(--info);
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .summary-card.purple { border-left-color: var(--purple); }
        .summary-card.success { border-left-color: var(--success); }
        .summary-card.warning { border-left-color: var(--warning); }
        .summary-card.danger { border-left-color: var(--accent); }
        .summary-card.info { border-left-color: var(--info); }

        .summary-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: rgba(67, 97, 238, 0.1);
            color: var(--info);
        }

        .summary-card.purple .summary-icon { background: rgba(111,66,193,0.1); color: var(--purple); }
        .summary-card.success .summary-icon { background: rgba(0,191,142,0.1); color: var(--success); }
        .summary-card.warning .summary-icon { background: rgba(240,165,0,0.1); color: var(--warning); }
        .summary-card.danger .summary-icon { background: rgba(233,69,96,0.1); color: var(--accent); }
        .summary-card.info .summary-icon { background: rgba(67,97,238,0.1); color: var(--info); }

        .summary-info h4 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            color: var(--primary);
        }

        .summary-info span {
            font-size: 0.8rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== FILTROS ===== */
        .filter-card {
            background: white;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .filter-card .form-control,
        .filter-card .form-select {
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .filter-card .form-control:focus,
        .filter-card .form-select:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(111,66,193,0.1);
        }

        .btn-filter {
            background: linear-gradient(135deg, var(--purple), #8b5cf6);
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(111,66,193,0.3);
            color: white;
        }

        .btn-reset {
            background: var(--gray-200);
            border: none;
            color: var(--gray-600);
            padding: 0.6rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            background: var(--gray-300);
            color: var(--gray-800);
        }

        /* ===== TIMELINE ===== */
        .timeline-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .timeline-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .timeline-header h5 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .timeline-body {
            padding: 0;
        }

        .timeline {
            position: relative;
            padding: 1.5rem 1.5rem 1.5rem 100px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 100px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, var(--purple), var(--info), var(--success));
            border-radius: 4px;
        }

        .timeline-item {
            position: relative;
            padding-left: 50px;
            padding-bottom: 2rem;
            margin-left: 0;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -16px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            border: 4px solid var(--purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: var(--purple);
            z-index: 1;
            box-shadow: 0 3px 10px rgba(111,66,193,0.25);
        }

        .timeline-marker.traspaso { border-color: #7c3aed; color: #7c3aed; background: #f5f3ff; }
        .timeline-marker.pausa { border-color: #f59e0b; color: #f59e0b; box-shadow: 0 3px 10px rgba(240,165,0,0.3); background: #fffbeb; }
        .timeline-marker.reanudacion { border-color: #10b981; color: #10b981; box-shadow: 0 3px 10px rgba(0,191,142,0.3); background: #ecfdf5; }
        .timeline-marker.cambio { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
        .timeline-marker.cambio-plan { border-color: #3b82f6; color: #3b82f6; box-shadow: 0 3px 10px rgba(67,97,238,0.3); background: #eff6ff; }
        .timeline-marker.pago { border-color: #10b981; color: #10b981; background: #ecfdf5; }
        .timeline-marker.pago.pagado { border-color: #10b981; color: #10b981; box-shadow: 0 3px 10px rgba(0,191,142,0.35); background: #ecfdf5; }
        .timeline-marker.pago.parcial { border-color: #f59e0b; color: #f59e0b; box-shadow: 0 3px 10px rgba(240,165,0,0.35); background: #fffbeb; }
        .timeline-marker.pago.pendiente { border-color: #ef4444; color: #ef4444; box-shadow: 0 3px 10px rgba(233,69,96,0.35); background: #fef2f2; }

        .timeline-date {
            position: absolute;
            left: -95px;
            top: 0;
            width: 75px;
            text-align: center;
            font-size: 0.8rem;
            color: #4b5563;
            line-height: 1.2;
            font-weight: 500;
            background: #f8fafc;
            padding: 0.5rem 0.25rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .timeline-date .dia {
            font-weight: 800;
            font-size: 1.4rem;
            color: #1e293b;
            display: block;
            line-height: 1;
            margin-bottom: 2px;
        }

        .timeline-content {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .timeline-content:hover {
            border-color: var(--purple);
            box-shadow: 0 8px 25px rgba(111,66,193,0.15);
            transform: translateY(-2px);
        }

        .timeline-content-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .timeline-type {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .timeline-type.traspaso {
            background: linear-gradient(135deg, var(--purple), #8b5cf6);
            color: white;
        }

        .timeline-type.pausa {
            background: linear-gradient(135deg, var(--warning), #ffcc00);
            color: #333;
        }

        .timeline-type.reanudacion {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
        }

        .timeline-type.cambio {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
        }

        .timeline-type.pago {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
        }

        .timeline-type.pago.parcial {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: #333;
        }

        .timeline-type.pago.pendiente {
            background: linear-gradient(135deg, #ef4444, #f87171);
            color: white;
        }

        .timeline-time {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            color: var(--gray-600);
        }

        .timeline-time i {
            color: var(--purple);
        }

        .timeline-details {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .timeline-person {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .timeline-person-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: white;
        }

        .timeline-person-avatar.origen {
            background: linear-gradient(135deg, var(--warning), #ffcc00);
            color: #333;
        }

        .timeline-person-avatar.destino {
            background: linear-gradient(135deg, var(--success), #00d9a0);
        }

        .timeline-person-info h6 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary);
        }

        .timeline-person-info span {
            font-size: 0.75rem;
            color: var(--gray-600);
        }

        .timeline-arrow {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .timeline-arrow i {
            font-size: 1.5rem;
            color: var(--accent);
        }

        .timeline-arrow span {
            font-size: 0.65rem;
            color: var(--gray-600);
            text-transform: uppercase;
        }

        .timeline-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px dashed var(--gray-300);
        }

        .timeline-meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
        }

        .timeline-meta-item i {
            color: var(--purple);
            width: 16px;
        }

        .timeline-meta-item.membresia {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .timeline-meta-item.dias {
            background: rgba(111,66,193,0.1);
            color: var(--purple);
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .timeline-meta-item.deuda {
            background: rgba(233,69,96,0.1);
            color: var(--accent);
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .timeline-meta-item.pagado {
            background: rgba(0,191,142,0.1);
            color: var(--success);
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .timeline-motivo {
            margin-top: 1rem;
            padding: 0.85rem 1rem;
            background: white;
            border-radius: 10px;
            border-left: 3px solid var(--info);
        }

        .timeline-motivo-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--gray-600);
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .timeline-motivo-text {
            font-size: 0.9rem;
            color: var(--primary);
            font-style: italic;
            margin: 0;
        }

        .timeline-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--gray-200);
        }

        .timeline-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--gray-600);
        }

        .timeline-user i {
            color: var(--info);
        }

        .btn-ver-detalle {
            background: var(--purple);
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-ver-detalle:hover {
            background: #5a32a3;
            color: white;
            transform: translateY(-2px);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        /* ===== PAGINACIÓN ===== */
        .pagination-wrapper {
            padding: 1rem 1.5rem;
            background: var(--gray-100);
            display: flex;
            justify-content: center;
        }

        .pagination .page-link {
            border: none;
            color: var(--primary);
            border-radius: 8px;
            margin: 0 3px;
            padding: 0.5rem 0.85rem;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: var(--purple);
            color: white;
        }

        .pagination .page-item.active .page-link {
            background: var(--purple);
            border-color: var(--purple);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .timeline {
                padding-left: 30px;
            }

            .timeline::before {
                left: 30px;
            }
            
            .timeline-item {
                padding-left: 40px;
            }
            
            .timeline-marker {
                left: -12px;
                width: 24px;
                height: 24px;
                font-size: 0.7rem;
            }
            
            .timeline-date {
                position: relative;
                left: 0;
                margin-bottom: 0.5rem;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                width: auto;
                padding: 0.3rem 0.6rem;
            }

            .timeline-date .dia {
                font-size: 1rem;
                margin-bottom: 0;
            }
            
            .timeline-details {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .timeline-arrow {
                flex-direction: row;
            }
            
            .timeline-arrow i {
                transform: rotate(90deg);
            }

            .historial-tab {
                padding: 0.65rem 1rem;
                font-size: 0.85rem;
            }

            .summary-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title">
                <i class="fas fa-history mr-2" style="color: #6f42c1;"></i>
                Historial de Actividades
            </h1>
            <p class="page-subtitle mb-0">Registro cronológico de todos los movimientos del sistema</p>
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
            <span class="badge-count">{{ $estadisticas['total_traspasos'] }}</span>
        </a>
        <a href="{{ route('admin.historial.index', ['tab' => 'pausas']) }}" 
           class="historial-tab {{ $tab == 'pausas' ? 'active' : '' }}">
            <i class="fas fa-pause-circle"></i>
            Pausas
            <span class="badge-count">{{ $estadisticas['total_pausas'] }}</span>
        </a>
        <a href="{{ route('admin.historial.index', ['tab' => 'cambios']) }}" 
           class="historial-tab {{ $tab == 'cambios' ? 'active' : '' }}">
            <i class="fas fa-level-up-alt"></i>
            Cambios de Plan
            <span class="badge-count">{{ $estadisticas['total_cambios_plan'] }}</span>
        </a>
        <a href="{{ route('admin.historial.index', ['tab' => 'pagos']) }}" 
           class="historial-tab {{ $tab == 'pagos' ? 'active' : '' }}">
            <i class="fas fa-dollar-sign"></i>
            Pagos
            <span class="badge-count">{{ $estadisticas['total_pagos'] }}</span>
        </a>
    </div>

    <!-- Resumen -->
    @if($tab == 'traspasos')
    <div class="summary-row">
        <div class="summary-card purple">
            <div class="summary-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['total_traspasos'] }}</h4>
                <span>Total Traspasos</span>
            </div>
        </div>
        <div class="summary-card success">
            <div class="summary-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['traspasos_mes'] }}</h4>
                <span>Este Mes</span>
            </div>
        </div>
        <div class="summary-card warning">
            <div class="summary-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['con_deuda_transferida'] }}</h4>
                <span>Con Deuda</span>
            </div>
        </div>
        <div class="summary-card danger">
            <div class="summary-icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="summary-info">
                <h4>${{ number_format($estadisticas['total_deuda_transferida'], 0, ',', '.') }}</h4>
                <span>Deuda Total</span>
            </div>
        </div>
    </div>
    @elseif($tab == 'pausas')
    <div class="summary-row">
        <div class="summary-card warning">
            <div class="summary-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['total_pausas'] }}</h4>
                <span>Total Pausas</span>
            </div>
        </div>
        <div class="summary-card success">
            <div class="summary-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['pausas_mes'] }}</h4>
                <span>Este Mes</span>
            </div>
        </div>
    </div>
    @elseif($tab == 'cambios')
    <div class="summary-row">
        <div class="summary-card info">
            <div class="summary-icon">
                <i class="fas fa-level-up-alt"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['total_cambios_plan'] }}</h4>
                <span>Total Cambios</span>
            </div>
        </div>
        <div class="summary-card success">
            <div class="summary-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['cambios_plan_mes'] }}</h4>
                <span>Este Mes</span>
            </div>
        </div>
    </div>
    @elseif($tab == 'pagos')
    <div class="summary-row">
        <div class="summary-card success">
            <div class="summary-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['total_pagos'] }}</h4>
                <span>Total Pagos</span>
            </div>
        </div>
        <div class="summary-card purple">
            <div class="summary-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $estadisticas['pagos_mes'] }}</h4>
                <span>Este Mes</span>
            </div>
        </div>
        <div class="summary-card warning">
            <div class="summary-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="summary-info">
                <h4>${{ number_format($estadisticas['total_recaudado'], 0, ',', '.') }}</h4>
                <span>Total Recaudado</span>
            </div>
        </div>
        <div class="summary-card info">
            <div class="summary-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="summary-info">
                <h4>${{ number_format($estadisticas['recaudado_mes'], 0, ',', '.') }}</h4>
                <span>Recaudado Mes</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.historial.index') }}" class="row align-items-end g-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="col-md-3 col-sm-6">
                <label class="small text-muted fw-bold mb-1">Cliente</label>
                <select name="cliente_id" class="form-control">
                    <option value="">Todos los clientes</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($tab == 'traspasos')
            <div class="col-md-2 col-sm-6">
                <label class="small text-muted fw-bold mb-1">Membresía</label>
                <select name="membresia_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach($membresias as $membresia)
                        <option value="{{ $membresia->id }}" {{ request('membresia_id') == $membresia->id ? 'selected' : '' }}>
                            {{ $membresia->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2 col-sm-6">
                <label class="small text-muted fw-bold mb-1">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="small text-muted fw-bold mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            @if($tab == 'traspasos')
            <div class="col-md-2 col-sm-6">
                <div class="form-check mt-3">
                    <input type="checkbox" class="form-check-input" id="con_deuda" name="con_deuda" value="1" {{ request('con_deuda') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="con_deuda">Solo con deuda</label>
                </div>
            </div>
            @endif
            <div class="col-md-1 col-sm-6 d-flex gap-2">
                <button type="submit" class="btn btn-filter flex-grow-1">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('admin.historial.index') }}" class="btn btn-reset" title="Limpiar filtros">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Timeline según tab activo -->
    @if($tab == 'traspasos')
    {{-- TIMELINE DE TRASPASOS --}}
    <div class="timeline-container">
        <div class="timeline-header">
            <h5>
                <i class="fas fa-stream mr-2"></i>
                Línea de Tiempo - Traspasos
            </h5>
            <span class="badge bg-light text-dark">{{ $traspasos->total() }} registros</span>
        </div>
        <div class="timeline-body">
            @if($traspasos->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <h5>Sin traspasos</h5>
                    <p>No hay traspasos registrados que coincidan con los filtros.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($traspasos as $traspaso)
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <span class="dia">{{ $traspaso->fecha_traspaso->format('d') }}</span>
                            {{ $traspaso->fecha_traspaso->translatedFormat('M') }}
                        </div>
                        <div class="timeline-marker traspaso">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-content-header">
                                <span class="timeline-type traspaso">
                                    <i class="fas fa-exchange-alt"></i>
                                    Traspaso
                                </span>
                                <span class="timeline-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $traspaso->created_at->format('H:i') }} hrs
                                </span>
                            </div>
                            
                            <div class="timeline-details">
                                <div class="timeline-person">
                                    <div class="timeline-person-avatar origen">
                                        @if($traspaso->clienteOrigen)
                                            {{ strtoupper(substr($traspaso->clienteOrigen->nombres, 0, 1)) }}{{ strtoupper(substr($traspaso->clienteOrigen->apellido_paterno, 0, 1)) }}
                                        @else
                                            ??
                                        @endif
                                    </div>
                                    <div class="timeline-person-info">
                                        <h6>
                                            @if($traspaso->clienteOrigen)
                                                {{ $traspaso->clienteOrigen->nombres }} {{ $traspaso->clienteOrigen->apellido_paterno }}
                                            @else
                                                Cliente eliminado
                                            @endif
                                        </h6>
                                        <span>Cliente origen</span>
                                    </div>
                                </div>
                                
                                <div class="timeline-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                    <span>Cede a</span>
                                </div>
                                
                                <div class="timeline-person">
                                    <div class="timeline-person-avatar destino">
                                        @if($traspaso->clienteDestino)
                                            {{ strtoupper(substr($traspaso->clienteDestino->nombres, 0, 1)) }}{{ strtoupper(substr($traspaso->clienteDestino->apellido_paterno, 0, 1)) }}
                                        @else
                                            ??
                                        @endif
                                    </div>
                                    <div class="timeline-person-info">
                                        <h6>
                                            @if($traspaso->clienteDestino)
                                                {{ $traspaso->clienteDestino->nombres }} {{ $traspaso->clienteDestino->apellido_paterno }}
                                            @else
                                                Cliente eliminado
                                            @endif
                                        </h6>
                                        <span>Cliente destino</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="timeline-meta">
                                <span class="timeline-meta-item membresia">
                                    <i class="fas fa-dumbbell"></i>
                                    {{ $traspaso->membresia->nombre ?? 'N/A' }}
                                </span>
                                <span class="timeline-meta-item dias">
                                    <i class="fas fa-calendar-day"></i>
                                    {{ $traspaso->dias_restantes_traspasados }} días
                                </span>
                                <span class="timeline-meta-item pagado">
                                    <i class="fas fa-check-circle"></i>
                                    ${{ number_format($traspaso->monto_pagado, 0, ',', '.') }}
                                </span>
                                @if($traspaso->se_transfirio_deuda)
                                <span class="timeline-meta-item deuda">
                                    <i class="fas fa-exclamation-circle"></i>
                                    ${{ number_format($traspaso->deuda_transferida, 0, ',', '.') }} deuda
                                </span>
                                @endif
                            </div>
                            
                            @if($traspaso->motivo)
                            <div class="timeline-motivo">
                                <div class="timeline-motivo-label">
                                    <i class="fas fa-comment-alt"></i> Motivo del traspaso
                                </div>
                                <p class="timeline-motivo-text">"{{ $traspaso->motivo }}"</p>
                            </div>
                            @endif
                            
                            <div class="timeline-footer">
                                <div class="timeline-user">
                                    <i class="fas fa-user-shield"></i>
                                    @if($traspaso->usuario)
                                        Realizado por: <strong>{{ $traspaso->usuario->name }}</strong>
                                    @else
                                        Sistema
                                    @endif
                                </div>
                                <a href="{{ route('admin.historial.traspaso.show', $traspaso) }}" class="btn-ver-detalle">
                                    <i class="fas fa-eye"></i>
                                    Ver detalle
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if($traspasos->hasPages())
        <div class="pagination-wrapper">
            {{ $traspasos->withQueryString()->links() }}
        </div>
        @endif
    </div>

    @elseif($tab == 'pausas')
    {{-- TIMELINE DE PAUSAS --}}
    <div class="timeline-container">
        <div class="timeline-header">
            <h5>
                <i class="fas fa-stream mr-2"></i>
                Línea de Tiempo - Pausas y Reanudaciones
            </h5>
            <span class="badge bg-light text-dark">{{ $pausas->total() }} registros</span>
        </div>
        <div class="timeline-body">
            @if($pausas->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-pause-circle"></i>
                    <h5>Sin pausas</h5>
                    <p>No hay pausas ni reanudaciones registradas que coincidan con los filtros.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($pausas as $pausa)
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <span class="dia">{{ $pausa->fecha_cambio->format('d') }}</span>
                            {{ $pausa->fecha_cambio->translatedFormat('M') }}
                        </div>
                        <div class="timeline-marker {{ $pausa->tipo_cambio == 'pausa' ? 'pausa' : 'reanudacion' }}">
                            <i class="fas fa-{{ $pausa->tipo_cambio == 'pausa' ? 'pause' : 'play' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-content-header">
                                <span class="timeline-type pausa">
                                    <i class="fas fa-{{ $pausa->tipo_cambio == 'pausa' ? 'pause-circle' : 'play-circle' }}"></i>
                                    {{ $pausa->tipo_cambio == 'pausa' ? 'Pausa' : 'Reanudación' }}
                                </span>
                                <span class="timeline-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $pausa->created_at->format('H:i') }} hrs
                                </span>
                            </div>
                            
                            <div class="timeline-details" style="display: block;">
                                <div class="timeline-person" style="margin-bottom: 1rem;">
                                    <div class="timeline-person-avatar origen">
                                        @if($pausa->cliente)
                                            {{ strtoupper(substr($pausa->cliente->nombres, 0, 1)) }}{{ strtoupper(substr($pausa->cliente->apellido_paterno, 0, 1)) }}
                                        @else
                                            ??
                                        @endif
                                    </div>
                                    <div class="timeline-person-info">
                                        <h6>
                                            @if($pausa->cliente)
                                                {{ $pausa->cliente->nombres }} {{ $pausa->cliente->apellido_paterno }}
                                            @else
                                                Cliente eliminado
                                            @endif
                                        </h6>
                                        <span>Cliente</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="timeline-meta">
                                @if($pausa->inscripcion && $pausa->inscripcion->membresia)
                                <span class="timeline-meta-item membresia">
                                    <i class="fas fa-dumbbell"></i>
                                    {{ $pausa->inscripcion->membresia->nombre }}
                                </span>
                                @endif
                                @if($pausa->detalles && isset($pausa->detalles['dias_pausa']))
                                <span class="timeline-meta-item dias">
                                    <i class="fas fa-calendar-day"></i>
                                    {{ $pausa->detalles['dias_pausa'] }} días de pausa
                                </span>
                                @endif
                                @if($pausa->detalles && isset($pausa->detalles['indefinida']) && $pausa->detalles['indefinida'])
                                <span class="timeline-meta-item deuda">
                                    <i class="fas fa-infinity"></i>
                                    Pausa indefinida
                                </span>
                                @endif
                            </div>
                            
                            @if($pausa->motivo)
                            <div class="timeline-motivo">
                                <div class="timeline-motivo-label">
                                    <i class="fas fa-comment-alt"></i> Motivo
                                </div>
                                <p class="timeline-motivo-text">"{{ $pausa->motivo }}"</p>
                            </div>
                            @endif
                            
                            <div class="timeline-footer">
                                <div class="timeline-user">
                                    <i class="fas fa-user-shield"></i>
                                    @if($pausa->usuario)
                                        Realizado por: <strong>{{ $pausa->usuario->name }}</strong>
                                    @else
                                        Sistema
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if($pausas->hasPages())
        <div class="pagination-wrapper">
            {{ $pausas->withQueryString()->links() }}
        </div>
        @endif
    </div>

    @elseif($tab == 'cambios')
    {{-- TIMELINE DE CAMBIOS DE PLAN --}}
    <div class="timeline-container">
        <div class="timeline-header">
            <h5>
                <i class="fas fa-stream mr-2"></i>
                Línea de Tiempo - Cambios de Plan
            </h5>
            <span class="badge bg-light text-dark">{{ $cambiosPlan->total() }} registros</span>
        </div>
        <div class="timeline-body">
            @if($cambiosPlan->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-level-up-alt"></i>
                    <h5>Sin cambios de plan</h5>
                    <p>No hay cambios de plan registrados que coincidan con los filtros.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($cambiosPlan as $cambio)
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <span class="dia">{{ $cambio->fecha_cambio->format('d') }}</span>
                            {{ $cambio->fecha_cambio->translatedFormat('M') }}
                        </div>
                        <div class="timeline-marker cambio-plan">
                            <i class="fas fa-level-up-alt"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-content-header">
                                <span class="timeline-type" style="background: linear-gradient(135deg, var(--info), #60a5fa); color: white;">
                                    <i class="fas fa-level-up-alt"></i>
                                    Cambio de Plan
                                </span>
                                <span class="timeline-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $cambio->created_at->format('H:i') }} hrs
                                </span>
                            </div>
                            
                            <div class="timeline-details" style="display: block;">
                                <div class="timeline-person" style="margin-bottom: 1rem;">
                                    <div class="timeline-person-avatar origen">
                                        @if($cambio->cliente)
                                            {{ strtoupper(substr($cambio->cliente->nombres, 0, 1)) }}{{ strtoupper(substr($cambio->cliente->apellido_paterno, 0, 1)) }}
                                        @else
                                            ??
                                        @endif
                                    </div>
                                    <div class="timeline-person-info">
                                        <h6>
                                            @if($cambio->cliente)
                                                {{ $cambio->cliente->nombres }} {{ $cambio->cliente->apellido_paterno }}
                                            @else
                                                Cliente eliminado
                                            @endif
                                        </h6>
                                        <span>Cliente</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="timeline-meta">
                                @if($cambio->detalles && isset($cambio->detalles['membresia_anterior']))
                                <span class="timeline-meta-item" style="background: rgba(233,69,96,0.1); color: var(--accent);">
                                    <i class="fas fa-arrow-left"></i>
                                    {{ $cambio->detalles['membresia_anterior'] }}
                                </span>
                                <span style="margin: 0 0.5rem; color: var(--gray-500);">→</span>
                                @endif
                                @if($cambio->detalles && isset($cambio->detalles['membresia_nueva']))
                                <span class="timeline-meta-item" style="background: rgba(0,191,142,0.1); color: var(--success);">
                                    <i class="fas fa-arrow-right"></i>
                                    {{ $cambio->detalles['membresia_nueva'] }}
                                </span>
                                @elseif($cambio->inscripcion && $cambio->inscripcion->membresia)
                                <span class="timeline-meta-item membresia">
                                    <i class="fas fa-dumbbell"></i>
                                    {{ $cambio->inscripcion->membresia->nombre }}
                                </span>
                                @endif
                                @if($cambio->detalles && isset($cambio->detalles['diferencia_precio']))
                                <span class="timeline-meta-item {{ $cambio->detalles['diferencia_precio'] > 0 ? 'deuda' : 'pagado' }}">
                                    <i class="fas fa-dollar-sign"></i>
                                    {{ $cambio->detalles['diferencia_precio'] > 0 ? '+' : '' }}${{ number_format($cambio->detalles['diferencia_precio'], 0, ',', '.') }}
                                </span>
                                @endif
                            </div>
                            
                            @if($cambio->motivo)
                            <div class="timeline-motivo">
                                <div class="timeline-motivo-label">
                                    <i class="fas fa-comment-alt"></i> Motivo
                                </div>
                                <p class="timeline-motivo-text">"{{ $cambio->motivo }}"</p>
                            </div>
                            @endif
                            
                            <div class="timeline-footer">
                                <div class="timeline-user">
                                    <i class="fas fa-user-shield"></i>
                                    @if($cambio->usuario)
                                        Realizado por: <strong>{{ $cambio->usuario->name }}</strong>
                                    @else
                                        Sistema
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if($cambiosPlan->hasPages())
        <div class="pagination-wrapper">
            {{ $cambiosPlan->withQueryString()->links() }}
        </div>
        @endif
    </div>

    @elseif($tab == 'pagos')
    {{-- TIMELINE DE PAGOS --}}
    <div class="timeline-container">
        <div class="timeline-header">
            <h5>
                <i class="fas fa-stream mr-2"></i>
                Línea de Tiempo - Pagos
            </h5>
            <span class="badge bg-light text-dark">{{ $pagos->total() }} registros</span>
        </div>
        <div class="timeline-body">
            @if($pagos->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-dollar-sign"></i>
                    <h5>Sin pagos</h5>
                    <p>No hay pagos registrados que coincidan con los filtros.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($pagos as $pago)
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <span class="dia">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d') : $pago->created_at->format('d') }}</span>
                            {{ $pago->fecha_pago ? $pago->fecha_pago->translatedFormat('M') : $pago->created_at->translatedFormat('M') }}
                        </div>
                        <div class="timeline-marker pago {{ $pago->id_estado == 201 ? 'pagado' : ($pago->id_estado == 202 ? 'parcial' : 'pendiente') }}">
                            <i class="fas fa-{{ $pago->id_estado == 201 ? 'check' : ($pago->id_estado == 202 ? 'clock' : 'hourglass-half') }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-content-header">
                                @php
                                    $estadoClass = match($pago->id_estado) {
                                        201 => 'background: linear-gradient(135deg, var(--success), #2dd4bf); color: white;',
                                        202 => 'background: linear-gradient(135deg, var(--warning), #fbbf24); color: #333;',
                                        200 => 'background: linear-gradient(135deg, var(--accent), #f472b6); color: white;',
                                        default => 'background: var(--gray-500); color: white;'
                                    };
                                    $estadoNombre = match($pago->id_estado) {
                                        201 => 'Pagado',
                                        202 => 'Parcial',
                                        200 => 'Pendiente',
                                        203 => 'Vencido',
                                        204 => 'Cancelado',
                                        205 => 'Traspasado',
                                        default => 'Desconocido'
                                    };
                                @endphp
                                <span class="timeline-type" style="{{ $estadoClass }}">
                                    <i class="fas fa-money-bill-wave"></i>
                                    {{ $estadoNombre }}
                                </span>
                                <span class="timeline-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $pago->created_at->format('H:i') }} hrs
                                </span>
                            </div>
                            
                            <div class="timeline-details" style="display: block;">
                                <div class="timeline-person" style="margin-bottom: 1rem;">
                                    <div class="timeline-person-avatar origen">
                                        @if($pago->cliente)
                                            {{ strtoupper(substr($pago->cliente->nombres, 0, 1)) }}{{ strtoupper(substr($pago->cliente->apellido_paterno, 0, 1)) }}
                                        @else
                                            ??
                                        @endif
                                    </div>
                                    <div class="timeline-person-info">
                                        <h6>
                                            @if($pago->cliente)
                                                {{ $pago->cliente->nombres }} {{ $pago->cliente->apellido_paterno }}
                                            @else
                                                Cliente eliminado
                                            @endif
                                        </h6>
                                        <span>Cliente</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="timeline-meta">
                                <span class="timeline-meta-item pagado">
                                    <i class="fas fa-dollar-sign"></i>
                                    ${{ number_format($pago->monto_abonado, 0, ',', '.') }}
                                </span>
                                @if($pago->metodoPago)
                                <span class="timeline-meta-item" style="background: rgba(67,97,238,0.1); color: var(--info);">
                                    <i class="fas fa-credit-card"></i>
                                    {{ $pago->metodoPago->nombre }}
                                </span>
                                @endif
                                @if($pago->inscripcion && $pago->inscripcion->membresia)
                                <span class="timeline-meta-item membresia">
                                    <i class="fas fa-dumbbell"></i>
                                    {{ $pago->inscripcion->membresia->nombre }}
                                </span>
                                @endif
                            </div>
                            
                            @if($pago->observaciones)
                            <div class="timeline-motivo">
                                <div class="timeline-motivo-label">
                                    <i class="fas fa-comment-alt"></i> Observaciones
                                </div>
                                <p class="timeline-motivo-text">"{{ $pago->observaciones }}"</p>
                            </div>
                            @endif
                            
                            <div class="timeline-footer">
                                <div class="timeline-user">
                                    <i class="fas fa-hashtag"></i>
                                    Pago #{{ $pago->id }}
                                </div>
                                <a href="{{ route('admin.pagos.show', $pago) }}" class="btn-ver-detalle">
                                    <i class="fas fa-eye"></i>
                                    Ver detalle
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if($pagos->hasPages())
        <div class="pagination-wrapper">
            {{ $pagos->withQueryString()->links() }}
        </div>
        @endif
    </div>
    @endif
@endsection
