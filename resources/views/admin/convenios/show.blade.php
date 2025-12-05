@extends('adminlte::page')

@section('title', 'Detalle Convenio - EstóicosGym')

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
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --radius-md: 12px;
        --radius-lg: 16px;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: var(--radius-lg);
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
        position: relative;
    }

    .page-header.institucion_educativa { background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%); }
    .page-header.empresa { background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); }
    .page-header.organizacion { background: linear-gradient(135deg, #f0a500 0%, #d99500 100%); }
    .page-header.otro { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); }

    .page-header h1 { color: white; margin: 0 0 0.25rem 0; font-weight: 700; }
    .page-header small { color: rgba(255,255,255,0.8); }

    .header-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 0.5rem;
    }

    .header-status {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--success);
    }

    .status-dot.inactivo { background: var(--gray-600); }

    .header-actions {
        display: flex;
        gap: 10px;
        margin-top: 1rem;
    }

    .btn-header {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-header-light {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
    }
    .btn-header-light:hover { background: rgba(255,255,255,0.3); color: white; }

    .btn-header-warning {
        background: var(--warning);
        border: none;
        color: white;
    }
    .btn-header-warning:hover { background: #d99500; color: white; }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .info-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        border: 1px solid var(--gray-200);
    }

    .info-card-header {
        background: var(--gray-50);
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gray-200);
        font-weight: 700;
        color: var(--primary);
    }

    .info-card-header i { color: var(--info); margin-right: 0.5rem; }

    .info-card-body { padding: 1.25rem; }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .info-item:last-child { border-bottom: none; }

    .info-icon {
        width: 40px;
        height: 40px;
        background: rgba(67,97,238,0.1);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--info);
        flex-shrink: 0;
    }

    .info-content { flex: 1; }
    .info-label { font-size: 0.8rem; color: var(--gray-600); margin-bottom: 0.25rem; }
    .info-value { font-weight: 600; color: var(--primary); }
    .info-value a { color: var(--info); text-decoration: none; }
    .info-value a:hover { text-decoration: underline; }

    .description-box {
        background: var(--gray-50);
        border-radius: var(--radius-md);
        padding: 1rem;
        color: var(--gray-600);
        line-height: 1.6;
    }

    .clientes-count {
        text-align: center;
        padding: 2rem;
    }

    .clientes-number {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
    }

    .clientes-label {
        color: var(--gray-600);
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .action-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid var(--gray-200);
    }

    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .btn-action-back {
        background: var(--gray-100);
        color: var(--gray-600);
    }
    .btn-action-back:hover { background: var(--gray-200); color: var(--gray-600); }

    .btn-action-edit {
        background: var(--warning);
        color: white;
    }
    .btn-action-edit:hover { background: #d99500; color: white; }

    .btn-action-delete {
        background: rgba(233,69,96,0.1);
        color: var(--accent);
    }
    .btn-action-delete:hover { background: var(--accent); color: white; }

    .action-group { display: flex; gap: 0.75rem; }
</style>
@stop

@section('content')
<div class="container-fluid">
    @php
        $tipos = [
            'institucion_educativa' => 'Institución Educativa',
            'empresa' => 'Empresa',
            'organizacion' => 'Organización',
            'otro' => 'Otro'
        ];
    @endphp

    <!-- Header -->
    <div class="page-header {{ $convenio->tipo }}">
        <div class="header-status">
            <span class="status-dot {{ $convenio->activo ? '' : 'inactivo' }}"></span>
            <span>{{ $convenio->activo ? 'Activo' : 'Inactivo' }}</span>
        </div>
        <h1><i class="fas fa-handshake"></i> {{ $convenio->nombre }}</h1>
        <span class="header-badge">{{ $tipos[$convenio->tipo] ?? $convenio->tipo }}</span>
        <div class="header-actions">
            <a href="{{ route('admin.convenios.index') }}" class="btn btn-header btn-header-light">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('admin.convenios.edit', $convenio) }}" class="btn btn-header btn-header-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <!-- Información General -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-info-circle"></i> Información General
            </div>
            <div class="info-card-body">
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-tag"></i></div>
                    <div class="info-content">
                        <div class="info-label">Nombre</div>
                        <div class="info-value">{{ $convenio->nombre }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="info-content">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">{{ $tipos[$convenio->tipo] ?? $convenio->tipo }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-calendar"></i></div>
                    <div class="info-content">
                        <div class="info-label">Fecha de Registro</div>
                        <div class="info-value">{{ $convenio->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacto -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-user-tie"></i> Información de Contacto
            </div>
            <div class="info-card-body">
                @if($convenio->contacto_nombre || $convenio->contacto_telefono || $convenio->contacto_email)
                    @if($convenio->contacto_nombre)
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-user"></i></div>
                        <div class="info-content">
                            <div class="info-label">Nombre</div>
                            <div class="info-value">{{ $convenio->contacto_nombre }}</div>
                        </div>
                    </div>
                    @endif
                    @if($convenio->contacto_telefono)
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-phone"></i></div>
                        <div class="info-content">
                            <div class="info-label">Teléfono</div>
                            <div class="info-value">{{ $convenio->contacto_telefono }}</div>
                        </div>
                    </div>
                    @endif
                    @if($convenio->contacto_email)
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="info-content">
                            <div class="info-label">Email</div>
                            <div class="info-value"><a href="mailto:{{ $convenio->contacto_email }}">{{ $convenio->contacto_email }}</a></div>
                        </div>
                    </div>
                    @endif
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-address-card fa-2x mb-2" style="opacity: 0.3;"></i>
                        <p class="mb-0">Sin información de contacto</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Clientes -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-users"></i> Clientes Asociados
            </div>
            <div class="info-card-body">
                <div class="clientes-count">
                    <div class="clientes-number">{{ $convenio->clientes->count() }}</div>
                    <div class="clientes-label">clientes con este convenio</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Descripción -->
    @if($convenio->descripcion)
    <div class="info-card mb-4">
        <div class="info-card-header">
            <i class="fas fa-align-left"></i> Descripción
        </div>
        <div class="info-card-body">
            <div class="description-box">{{ $convenio->descripcion }}</div>
        </div>
    </div>
    @endif

    <!-- Acciones -->
    <div class="action-card">
        <a href="{{ route('admin.convenios.index') }}" class="btn-action btn-action-back">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
        <div class="action-group">
            <a href="{{ route('admin.convenios.edit', $convenio) }}" class="btn-action btn-action-edit">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form action="{{ route('admin.convenios.destroy', $convenio) }}" method="POST" style="display:inline;"
                  onsubmit="return confirm('¿Estás seguro de enviar este convenio a la papelera?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-action-delete">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
@stop
