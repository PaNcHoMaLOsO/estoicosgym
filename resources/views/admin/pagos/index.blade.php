@extends('adminlte::page')

@section('title', 'Pagos - EstóicosGym')

@section('meta_tags')
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
@stop

@section('content')
<div class="pagos-container">
    <!-- Hero Header -->
    <div class="pagos-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="hero-text">
                <h1>Gestión de Pagos</h1>
                <p>Control y seguimiento de pagos de membresías</p>
            </div>
        </div>
        <div class="hero-actions">
            <a href="{{ route('admin.pagos.create') }}" class="btn-nuevo-pago">
                <i class="fas fa-plus-circle"></i>
                <span>Nuevo Pago</span>
            </a>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert-estoicos success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Éxito!</strong>
                <span>{{ $message }}</span>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @php
        $totalPagos = $pagos->total();
        $totalRecaudado = $pagos->sum('monto_abonado');
        $pagosCompletados = $pagos->filter(fn($p) => $p->estado?->codigo == 201)->count();
        $pagosPendientes = $pagos->filter(fn($p) => $p->estado?->codigo != 201)->count();
    @endphp

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $totalPagos }}</span>
                <span class="stat-label">Total Pagos</span>
            </div>
        </div>
        
        <div class="stat-card stat-recaudado">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">${{ number_format($totalRecaudado, 0, ',', '.') }}</span>
                <span class="stat-label">Recaudado</span>
            </div>
        </div>
        
        <div class="stat-card stat-completados">
            <div class="stat-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $pagosCompletados }}</span>
                <span class="stat-label">Completados</span>
            </div>
        </div>
        
        <div class="stat-card stat-pendientes">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $pagosPendientes }}</span>
                <span class="stat-label">Parciales</span>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Buscar por cliente, membresía o referencia..." autocomplete="off">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="todos">
                <i class="fas fa-list"></i> Todos
            </button>
            <button class="filter-btn" data-filter="pagado">
                <i class="fas fa-check-circle"></i> Pagados
            </button>
            <button class="filter-btn" data-filter="parcial">
                <i class="fas fa-hourglass-half"></i> Parciales
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="table-header">
            <h3><i class="fas fa-list-alt"></i> Listado de Pagos</h3>
            <span class="results-count" id="resultCount">{{ $pagos->count() }} de {{ $pagos->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="pagos-table">
                <thead>
                    <tr>
                        <th>Cliente / Membresía</th>
                        <th>Fecha</th>
                        <th>Montos</th>
                        <th>Estado</th>
                        <th>Método</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="pagosTableBody">
                    @forelse($pagos as $pago)
                        @php
                            $total = $pago->monto_total ?? 0;
                            $abonado = $pago->monto_abonado ?? 0;
                            $pendiente = $pago->monto_pendiente ?? 0;
                            $porcentaje = $total > 0 ? ($abonado / $total) * 100 : 0;
                            $estadoCodigo = $pago->estado?->codigo ?? 200;
                            $estadoPago = match($estadoCodigo) {
                                201 => 'pagado',
                                205 => 'traspasado',
                                203 => 'vencido',
                                204 => 'cancelado',
                                default => 'parcial'
                            };
                            
                            // Cliente actual de la inscripción (dueño actual de la membresía)
                            $clienteActual = $pago->inscripcion?->cliente;
                            // Cliente original que pagó (puede ser diferente si hubo traspaso)
                            $clienteOriginal = $pago->cliente;
                            // Detectar si es un traspaso (cliente actual diferente al que pagó)
                            $esTraspaso = $clienteActual && $clienteOriginal && $clienteActual->id !== $clienteOriginal->id;
                            // Usar cliente actual si existe, sino el original
                            $clienteMostrar = $clienteActual ?? $clienteOriginal;
                        @endphp
                        <tr class="pago-row" 
                            data-estado="{{ $estadoPago }}"
                            data-cliente="{{ strtolower(($clienteMostrar?->nombres ?? '') . ' ' . ($clienteMostrar?->apellido_paterno ?? '')) }}"
                            data-membresia="{{ strtolower($pago->inscripcion?->membresia?->nombre ?? '') }}"
                            data-referencia="{{ strtolower($pago->referencia_pago ?? '') }}">
                            <td data-label="Cliente / Membresía">
                                <div class="cliente-pago-info">
                                    <div class="cliente-avatar {{ $esTraspaso ? 'traspaso' : '' }}">
                                        {{ strtoupper(substr($clienteMostrar?->nombres ?? 'N', 0, 1) . substr($clienteMostrar?->apellido_paterno ?? 'A', 0, 1)) }}
                                        @if($esTraspaso)
                                        <span class="traspaso-indicator" title="Membresía traspasada">
                                            <i class="fas fa-exchange-alt"></i>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="cliente-details">
                                        <span class="cliente-nombre">
                                            {{ $clienteMostrar?->nombres ?? 'Sin cliente' }} 
                                            {{ $clienteMostrar?->apellido_paterno ?? '' }}
                                            @if($esTraspaso)
                                            <span class="badge-traspaso" title="Pagado originalmente por {{ $clienteOriginal->nombres }} {{ $clienteOriginal->apellido_paterno }}">
                                                <i class="fas fa-exchange-alt"></i> Traspaso
                                            </span>
                                            @endif
                                        </span>
                                        <span class="pago-id">
                                            <i class="fas fa-hashtag"></i> Pago #{{ $pago->id }}
                                        </span>
                                        <span class="membresia-nombre">
                                            <i class="fas fa-dumbbell"></i> {{ $pago->inscripcion?->membresia?->nombre ?? 'Sin membresía' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Fecha">
                                <div class="fecha-info">
                                    <span class="fecha-principal">
                                        <i class="fas fa-calendar-alt"></i> 
                                        {{ $pago->fecha_pago?->format('d/m/Y') ?? 'N/A' }}
                                    </span>
                                    @if($pago->referencia_pago)
                                    <span class="referencia">
                                        <i class="fas fa-file-invoice"></i> {{ $pago->referencia_pago }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Montos">
                                <div class="montos-info">
                                    <span class="monto-total">
                                        ${{ number_format($total, 0, ',', '.') }}
                                    </span>
                                    <span class="monto-abonado">
                                        <i class="fas fa-check"></i> ${{ number_format($abonado, 0, ',', '.') }}
                                    </span>
                                    @if($pendiente > 0)
                                    <span class="monto-pendiente">
                                        <i class="fas fa-clock"></i> ${{ number_format($pendiente, 0, ',', '.') }}
                                    </span>
                                    @endif
                                    <div class="progress-bar-mini">
                                        <div class="progress-fill {{ $porcentaje >= 100 ? 'complete' : 'partial' }}" 
                                             style="width: {{ min($porcentaje, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Estado">
                                @if($estadoPago === 'pagado')
                                    <span class="estado-badge pagado">
                                        <i class="fas fa-check-circle"></i> Pagado
                                    </span>
                                @elseif($estadoPago === 'traspasado')
                                    <span class="estado-badge traspasado">
                                        <i class="fas fa-exchange-alt"></i> Traspasado
                                    </span>
                                @elseif($estadoPago === 'vencido')
                                    <span class="estado-badge vencido">
                                        <i class="fas fa-exclamation-circle"></i> Vencido
                                    </span>
                                @elseif($estadoPago === 'cancelado')
                                    <span class="estado-badge cancelado">
                                        <i class="fas fa-times-circle"></i> Cancelado
                                    </span>
                                @else
                                    <span class="estado-badge parcial">
                                        <i class="fas fa-hourglass-half"></i> Parcial
                                    </span>
                                @endif
                            </td>
                            <td data-label="Método">
                                <span class="metodo-badge">
                                    @switch($pago->metodoPago?->codigo ?? '')
                                        @case('efectivo')
                                            <i class="fas fa-money-bill-wave"></i>
                                            @break
                                        @case('tarjeta')
                                            <i class="fas fa-credit-card"></i>
                                            @break
                                        @case('transferencia')
                                            <i class="fas fa-university"></i>
                                            @break
                                        @default
                                            <i class="fas fa-wallet"></i>
                                    @endswitch
                                    {{ $pago->metodoPago?->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td data-label="Acciones">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.pagos.show', $pago) }}" class="btn-action btn-view" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn-action btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <h4>No hay pagos registrados</h4>
                                <p>Crea un nuevo pago para comenzar</p>
                                <a href="{{ route('admin.pagos.create') }}" class="btn-nuevo-pago-inline">
                                    <i class="fas fa-plus"></i> Nuevo Pago
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pagos->hasPages())
            <div class="pagination-section">
                {{ $pagos->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@stop

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
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 25px -5px rgb(0 0 0 / 0.15);
    }

    .content-wrapper { background: var(--gray-50) !important; }
    .content { padding: 0 !important; }

    .pagos-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* ===== HERO HEADER ===== */
    .pagos-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }
    .pagos-hero::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 180px;
        height: 180px;
        background: var(--success);
        border-radius: 50%;
        opacity: 0.3;
    }
    .pagos-hero::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: 30%;
        width: 120px;
        height: 120px;
        background: var(--accent);
        border-radius: 50%;
        opacity: 0.3;
    }
    .hero-content {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }
    .hero-icon {
        width: 56px;
        height: 56px;
        background: rgba(0, 191, 142, 0.2);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6em;
        color: var(--success);
    }
    .hero-text h1 {
        color: white;
        font-size: 1.6em;
        font-weight: 700;
        margin: 0;
    }
    .hero-text p {
        color: rgba(255,255,255,0.8);
        margin: 4px 0 0;
        font-size: 0.9em;
    }
    .hero-actions { position: relative; z-index: 1; }
    .btn-nuevo-pago {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 191, 142, 0.3);
    }
    .btn-nuevo-pago:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 191, 142, 0.4);
        color: white;
        text-decoration: none;
    }

    /* ===== ALERT ===== */
    .alert-estoicos {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        position: relative;
    }
    .alert-estoicos.success {
        background: rgba(0, 191, 142, 0.1);
        border: 1px solid rgba(0, 191, 142, 0.3);
    }
    .alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1em;
    }
    .alert-estoicos.success .alert-icon {
        background: var(--success);
        color: white;
    }
    .alert-content strong {
        display: block;
        color: var(--gray-800);
        margin-bottom: 2px;
    }
    .alert-content span { color: var(--gray-600); font-size: 0.9em; }
    .alert-close {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        padding: 4px;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: var(--shadow);
        border-left: 4px solid var(--gray-300);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2em;
    }
    .stat-card .stat-info { display: flex; flex-direction: column; }
    .stat-card .stat-number { font-size: 1.5em; font-weight: 800; color: var(--gray-800); }
    .stat-card .stat-label { font-size: 0.75em; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
    
    .stat-total { border-left-color: var(--info); }
    .stat-total .stat-icon { background: rgba(67, 97, 238, 0.2); color: #2541b2; }
    .stat-total .stat-icon i { color: #2541b2 !important; }
    .stat-total .stat-number { color: var(--info); }
    
    .stat-recaudado { border-left-color: var(--success); }
    .stat-recaudado .stat-icon { background: rgba(0, 191, 142, 0.2); color: #00896b; }
    .stat-recaudado .stat-icon i { color: #00896b !important; }
    .stat-recaudado .stat-number { color: var(--success); }
    
    .stat-completados { border-left-color: var(--warning); }
    .stat-completados .stat-icon { background: rgba(240, 165, 0, 0.2); color: #c78500; }
    .stat-completados .stat-icon i { color: #c78500 !important; }
    .stat-completados .stat-number { color: var(--warning); }
    
    .stat-pendientes { border-left-color: var(--accent); }
    .stat-pendientes .stat-icon { background: rgba(233, 69, 96, 0.2); color: #c9304c; }
    .stat-pendientes .stat-icon i { color: #c9304c !important; }
    .stat-pendientes .stat-number { color: var(--accent); }

    /* ===== FILTERS SECTION ===== */
    .filters-section {
        background: white;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: var(--shadow);
    }
    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }
    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-500);
    }
    .search-box input {
        width: 100%;
        padding: 10px 14px 10px 42px;
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }
    .search-box input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
    }
    .filter-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-btn {
        padding: 8px 16px;
        border: 2px solid var(--gray-200);
        background: white;
        border-radius: 8px;
        font-size: 0.85em;
        font-weight: 600;
        color: var(--gray-600);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .filter-btn:hover { border-color: var(--accent); color: var(--accent); }
    .filter-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }

    /* ===== TABLE SECTION ===== */
    .table-section {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }
    .table-header {
        background: var(--primary);
        color: white;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .table-header h3 {
        margin: 0;
        font-size: 1em;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .table-header h3 i { color: var(--accent); }
    .results-count {
        background: rgba(255,255,255,0.15);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
    }
    .pagos-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pagos-table thead {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: table-header-group !important;
        visibility: visible !important;
    }
    .pagos-table thead tr {
        display: table-row !important;
    }
    .pagos-table thead th {
        padding: 14px 16px;
        text-align: left;
        font-size: 0.8em;
        font-weight: 700;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: none;
        display: table-cell !important;
    }
    .pagos-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }
    .pagos-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }
    .pagos-table tbody tr {
        border-bottom: 1px solid var(--gray-100);
        transition: all 0.2s ease;
    }
    .pagos-table tbody tr:hover { background: var(--gray-50); }
    .pagos-table tbody td { padding: 14px 16px; vertical-align: middle; }

    /* ===== CLIENTE INFO ===== */
    .cliente-pago-info { display: flex; align-items: center; gap: 12px; }
    .cliente-avatar {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.9em;
        position: relative;
    }
    .cliente-avatar.traspaso {
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
    }
    .traspaso-indicator {
        position: absolute;
        bottom: -4px;
        right: -4px;
        width: 18px;
        height: 18px;
        background: #7c3aed;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        border: 2px solid white;
        color: white;
    }
    .badge-traspaso {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.65em;
        font-weight: 600;
        margin-left: 6px;
        cursor: help;
    }
    .badge-traspaso i { font-size: 0.85em; }
    .cliente-details { display: flex; flex-direction: column; gap: 2px; }
    .cliente-nombre { font-weight: 700; color: var(--gray-800); font-size: 0.95em; display: flex; align-items: center; flex-wrap: wrap; }
    .pago-id { font-size: 0.75em; color: var(--info); font-weight: 600; }
    .pago-id i { font-size: 0.8em; }
    .membresia-nombre { font-size: 0.8em; color: var(--gray-500); }
    .membresia-nombre i { color: var(--accent); margin-right: 4px; }

    /* ===== FECHA INFO ===== */
    .fecha-info { display: flex; flex-direction: column; gap: 4px; }
    .fecha-principal { font-size: 0.9em; color: var(--gray-700); font-weight: 500; }
    .fecha-principal i { color: var(--info); margin-right: 4px; }
    .referencia { font-size: 0.75em; color: var(--gray-500); }
    .referencia i { margin-right: 4px; }

    /* ===== MONTOS INFO ===== */
    .montos-info { display: flex; flex-direction: column; gap: 3px; }
    .monto-total { font-weight: 700; color: var(--gray-800); font-size: 0.95em; }
    .monto-abonado { font-size: 0.8em; color: var(--success); font-weight: 600; }
    .monto-abonado i { margin-right: 3px; }
    .monto-pendiente { font-size: 0.75em; color: var(--accent); }
    .monto-pendiente i { margin-right: 3px; }
    .progress-bar-mini {
        height: 4px;
        background: var(--gray-200);
        border-radius: 2px;
        margin-top: 4px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.5s ease;
    }
    .progress-fill.complete { background: linear-gradient(90deg, var(--success) 0%, #38ef7d 100%); }
    .progress-fill.partial { background: linear-gradient(90deg, var(--warning) 0%, #f5af19 100%); }

    /* ===== BADGES ===== */
    .estado-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75em;
        font-weight: 700;
        text-transform: uppercase;
    }
    .estado-badge.pagado {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
        border: 1px solid rgba(0, 191, 142, 0.3);
    }
    .estado-badge.parcial {
        background: rgba(240, 165, 0, 0.12);
        color: var(--warning);
        border: 1px solid rgba(240, 165, 0, 0.3);
    }
    .estado-badge.traspasado {
        background: rgba(124, 58, 237, 0.12);
        color: #7c3aed;
        border: 1px solid rgba(124, 58, 237, 0.3);
    }
    .estado-badge.vencido {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }
    .estado-badge.cancelado {
        background: rgba(108, 117, 125, 0.12);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }
    .metodo-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(67, 97, 238, 0.1);
        color: var(--info);
        border-radius: 8px;
        font-size: 0.8em;
        font-weight: 600;
    }

    /* ===== ACTION BUTTONS ===== */
    .action-buttons { display: flex; gap: 6px; justify-content: center; }
    .btn-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.85em;
    }
    .btn-action:hover { transform: translateY(-2px); }
    .btn-view { background: var(--primary); color: white; }
    .btn-view:hover { background: var(--primary-light); color: white; box-shadow: 0 4px 12px rgba(26, 26, 46, 0.3); }
    .btn-edit { background: var(--warning); color: white; }
    .btn-edit:hover { background: #d99400; color: white; box-shadow: 0 4px 12px rgba(240, 165, 0, 0.3); }
    .btn-delete { background: var(--accent); color: white; }
    .btn-delete:hover { background: #d63650; color: white; box-shadow: 0 4px 12px rgba(233, 69, 96, 0.3); }
    .delete-form { display: inline; }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 60px 20px !important;
    }
    .empty-icon {
        width: 80px;
        height: 80px;
        background: var(--gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2em;
        color: var(--gray-400);
    }
    .empty-state h4 { color: var(--gray-700); margin-bottom: 8px; }
    .empty-state p { color: var(--gray-500); margin-bottom: 20px; }
    .btn-nuevo-pago-inline {
        background: var(--success);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-nuevo-pago-inline:hover { background: var(--success-dark); color: white; text-decoration: none; }

    /* ===== PAGINATION ===== */
    .pagination-section {
        padding: 16px 20px;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: center;
    }
    .pagination { margin: 0; }
    .page-link {
        border: none;
        color: var(--gray-600);
        padding: 8px 14px;
        margin: 0 2px;
        border-radius: 8px;
    }
    .page-link:hover { background: var(--gray-100); color: var(--accent); }
    .page-item.active .page-link {
        background: var(--accent);
        color: white;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 992px) {
        .pagos-hero { flex-direction: column; gap: 16px; text-align: center; }
        .hero-content { flex-direction: column; }
        .filters-section { flex-direction: column; }
        .search-box { width: 100%; }
        .filter-buttons { justify-content: center; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .pagos-container { padding: 12px; }
        .pagos-table thead { display: none !important; }
        .pagos-table tbody tr {
            display: block;
            margin-bottom: 12px;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            padding: 12px;
        }
        .pagos-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-100);
        }
        .pagos-table tbody td:last-child { border-bottom: none; }
        .pagos-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--gray-600);
            font-size: 0.8em;
        }
        .action-buttons { justify-content: flex-end; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Filtro de búsqueda
    function filterTable() {
        var searchText = $('#searchInput').val().toLowerCase();
        var activeFilter = $('.filter-btn.active').data('filter');
        var visibleCount = 0;

        $('.pago-row').each(function() {
            var row = $(this);
            var cliente = row.data('cliente');
            var membresia = row.data('membresia');
            var referencia = row.data('referencia');
            var estado = row.data('estado');
            
            var matchesSearch = cliente.includes(searchText) || 
                                membresia.includes(searchText) || 
                                referencia.includes(searchText);
            var matchesFilter = activeFilter === 'todos' || estado === activeFilter;

            if (matchesSearch && matchesFilter) {
                row.show();
                visibleCount++;
            } else {
                row.hide();
            }
        });

        $('#resultCount').text(visibleCount + ' de {{ $pagos->total() }}');
    }

    $('#searchInput').on('keyup', filterTable);
    
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        filterTable();
    });

    // Confirmación de eliminación con SweetAlert2
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        
        Swal.fire({
            title: '¿Eliminar pago?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e94560',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'swal-estoicos'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@stop

