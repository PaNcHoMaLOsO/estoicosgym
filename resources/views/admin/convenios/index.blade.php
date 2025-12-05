@extends('adminlte::page')

@section('title', 'Convenios - EstóicosGym')

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-50: #fafbfc;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --radius-md: 12px;
        --radius-lg: 16px;
    }

    /* ===== HERO HEADER ===== */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: var(--radius-lg);
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
    }

    .page-header h1 {
        color: white;
        margin: 0;
        font-weight: 700;
    }

    .page-header h1 i {
        color: var(--success);
    }

    .page-header small {
        color: rgba(255,255,255,0.7);
    }

    .header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-header {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-header-primary {
        background: var(--success);
        border: none;
        color: white;
    }

    .btn-header-primary:hover {
        background: #00a67d;
        transform: translateY(-2px);
        color: white;
    }

    .btn-header-secondary {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
    }

    .btn-header-secondary:hover {
        background: rgba(255,255,255,0.25);
        color: white;
    }

    /* ===== STAT CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--radius-md);
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
        border-left: 4px solid var(--info);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .stat-card.success { border-left-color: var(--success); }
    .stat-card.warning { border-left-color: var(--warning); }
    .stat-card.danger { border-left-color: var(--accent); }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--info);
        line-height: 1;
    }

    .stat-card.success .stat-number { color: var(--success); }
    .stat-card.warning .stat-number { color: var(--warning); }
    .stat-card.danger .stat-number { color: var(--accent); }

    .stat-label {
        color: var(--gray-600);
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.25rem;
    }

    /* ===== CONVENIO CARDS ===== */
    .convenios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }

    .convenio-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid var(--gray-200);
    }

    .convenio-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .convenio-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 1.25rem;
        color: white;
        position: relative;
    }

    .convenio-header.institucion_educativa { background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%); }
    .convenio-header.empresa { background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); }
    .convenio-header.organizacion { background: linear-gradient(135deg, #f0a500 0%, #d99500 100%); }
    .convenio-header.otro { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); }

    .convenio-nombre {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 0.25rem 0;
    }

    .convenio-tipo {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .convenio-estado {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 3px rgba(0,191,142,0.3);
    }

    .convenio-estado.inactivo {
        background: var(--gray-600);
        box-shadow: 0 0 0 3px rgba(108,117,125,0.3);
    }

    .convenio-body {
        padding: 1.25rem;
    }

    .convenio-info {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .convenio-info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--gray-600);
        font-size: 0.9rem;
    }

    .convenio-info-item i {
        width: 20px;
        color: var(--info);
    }

    .convenio-info-item a {
        color: var(--info);
        text-decoration: none;
    }

    .convenio-info-item a:hover {
        text-decoration: underline;
    }

    .convenio-clientes {
        background: var(--gray-50);
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 0.5rem;
    }

    .convenio-clientes-count {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
    }

    .convenio-clientes-label {
        color: var(--gray-600);
        font-size: 0.8rem;
    }

    .convenio-footer {
        padding: 1rem 1.25rem;
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 0.5rem 0.875rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-action-view {
        background: rgba(67,97,238,0.1);
        color: var(--info);
    }

    .btn-action-view:hover {
        background: var(--info);
        color: white;
        text-decoration: none;
    }

    .btn-action-edit {
        background: rgba(240,165,0,0.1);
        color: var(--warning);
    }

    .btn-action-edit:hover {
        background: var(--warning);
        color: white;
        text-decoration: none;
    }

    .btn-action-delete {
        background: rgba(233,69,96,0.1);
        color: var(--accent);
    }

    .btn-action-delete:hover {
        background: var(--accent);
        color: white;
    }

    /* Form inline fix */
    .convenio-footer form {
        display: inline-flex;
        margin: 0;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--gray-200);
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-600);
        margin-bottom: 1.5rem;
    }

    /* ===== ALERTS ===== */
    .alert-modern {
        border: none;
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .alert-success-modern {
        background: rgba(0,191,142,0.1);
        color: #00a67d;
        border-left: 4px solid var(--success);
    }

    .alert-error-modern {
        background: rgba(233,69,96,0.1);
        color: #d63050;
        border-left: 4px solid var(--accent);
    }

    /* ===== PAGINATION ===== */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }
</style>
@stop

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1><i class="fas fa-handshake"></i> Convenios</h1>
                <small>Gestiona los convenios institucionales y empresariales</small>
            </div>
            <div class="col-md-6 text-right">
                <div class="header-actions justify-content-end">
                    <a href="{{ route('admin.convenios.trashed') }}" class="btn btn-header btn-header-secondary" title="Ver papelera">
                        <i class="fas fa-trash-alt"></i> Papelera
                    </a>
                    <a href="{{ route('admin.convenios.create') }}" class="btn btn-header btn-header-primary">
                        <i class="fas fa-plus"></i> Nuevo Convenio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if ($message = Session::get('success'))
        <div class="alert-modern alert-success-modern">
            <i class="fas fa-check-circle"></i> {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert-modern alert-error-modern">
            <i class="fas fa-exclamation-circle"></i> {{ $message }}
        </div>
    @endif

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $convenios->total() }}</div>
            <div class="stat-label">Total Convenios</div>
        </div>
        <div class="stat-card success">
            <div class="stat-number">{{ $convenios->where('activo', true)->count() }}</div>
            <div class="stat-label">Activos</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-number">{{ $convenios->where('activo', false)->count() }}</div>
            <div class="stat-label">Inactivos</div>
        </div>
        <div class="stat-card danger">
            @php
                $totalClientes = $convenios->sum(function($c) { return $c->clientes_count ?? 0; });
            @endphp
            <div class="stat-number">{{ $totalClientes }}</div>
            <div class="stat-label">Clientes Asociados</div>
        </div>
    </div>

    <!-- Grid de Convenios -->
    @if($convenios->count() > 0)
        <div class="convenios-grid">
            @foreach($convenios as $convenio)
                @php
                    $tipos = [
                        'institucion_educativa' => 'Institución Educativa',
                        'empresa' => 'Empresa',
                        'organizacion' => 'Organización',
                        'otro' => 'Otro'
                    ];
                @endphp
                <div class="convenio-card">
                    <div class="convenio-header {{ $convenio->tipo }}">
                        <div class="convenio-estado {{ $convenio->activo ? '' : 'inactivo' }}" 
                             title="{{ $convenio->activo ? 'Activo' : 'Inactivo' }}"></div>
                        <h3 class="convenio-nombre">{{ $convenio->nombre }}</h3>
                        <span class="convenio-tipo">{{ $tipos[$convenio->tipo] ?? $convenio->tipo }}</span>
                    </div>
                    <div class="convenio-body">
                        <div class="convenio-info">
                            @if($convenio->contacto_nombre)
                                <div class="convenio-info-item">
                                    <i class="fas fa-user"></i>
                                    <span>{{ $convenio->contacto_nombre }}</span>
                                </div>
                            @endif
                            @if($convenio->contacto_telefono)
                                <div class="convenio-info-item">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $convenio->contacto_telefono }}</span>
                                </div>
                            @endif
                            @if($convenio->contacto_email)
                                <div class="convenio-info-item">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:{{ $convenio->contacto_email }}">{{ $convenio->contacto_email }}</a>
                                </div>
                            @endif
                            @if(!$convenio->contacto_nombre && !$convenio->contacto_telefono && !$convenio->contacto_email)
                                <div class="convenio-info-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span class="text-muted">Sin información de contacto</span>
                                </div>
                            @endif
                        </div>
                        <div class="convenio-clientes">
                            <div>
                                <div class="convenio-clientes-count">{{ $convenio->clientes_count ?? 0 }}</div>
                                <div class="convenio-clientes-label">Clientes asociados</div>
                            </div>
                            <i class="fas fa-users" style="font-size: 2rem; opacity: 0.1;"></i>
                        </div>
                    </div>
                    <div class="convenio-footer">
                        <a href="{{ route('admin.convenios.show', $convenio) }}" class="btn-action btn-action-view" title="Ver detalles">
                            <i class="fas fa-eye"></i> <span>Ver</span>
                        </a>
                        <a href="{{ route('admin.convenios.edit', $convenio) }}" class="btn-action btn-action-edit" title="Editar">
                            <i class="fas fa-edit"></i> <span>Editar</span>
                        </a>
                        <form action="{{ route('admin.convenios.destroy', $convenio) }}" method="POST" 
                              onsubmit="return confirm('¿Estás seguro de enviar este convenio a la papelera?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action-delete" title="Eliminar">
                                <i class="fas fa-trash"></i> <span>Eliminar</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginación -->
        <div class="pagination-wrapper">
            {{ $convenios->links('pagination::bootstrap-4') }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-handshake"></i>
            <h3>No hay convenios registrados</h3>
            <p>Crea tu primer convenio para comenzar a gestionar descuentos institucionales</p>
            <a href="{{ route('admin.convenios.create') }}" class="btn btn-header btn-header-primary">
                <i class="fas fa-plus"></i> Crear Convenio
            </a>
        </div>
    @endif
</div>
@stop
