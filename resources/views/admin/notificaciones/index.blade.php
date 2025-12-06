@extends('adminlte::page')

@section('title', 'Notificaciones - EstóicosGym')

@section('css')
<style>
    .content-wrapper {
        background: #f8f9fa !important;
    }

    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --accent-light: #ff6b6b;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 16px;
        margin-bottom: 25px;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(233, 69, 96, 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    .page-header h1 {
        margin: 0;
        font-weight: 700;
        font-size: 1.6rem;
    }

    .page-header h1 i {
        color: var(--accent);
        margin-right: 10px;
    }

    .stat-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .stat-card .icon.pending { background: rgba(240, 165, 0, 0.15); color: var(--warning); }
    .stat-card .icon.sent { background: rgba(0, 191, 142, 0.15); color: var(--success); }
    .stat-card .icon.failed { background: rgba(233, 69, 96, 0.15); color: var(--accent); }
    .stat-card .icon.total { background: rgba(67, 97, 238, 0.15); color: var(--info); }

    .stat-card .info .number {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--gray-800);
    }

    .stat-card .info .label {
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    .action-bar {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .btn-execute {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        border: none;
        color: white;
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-execute:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(233, 69, 96, 0.3);
        color: white;
    }

    .btn-plantillas {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
        border: none;
        color: white;
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-plantillas:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(67, 97, 238, 0.3);
        color: white;
    }

    .main-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .main-card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 18px 25px;
    }

    .main-card-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .main-card-header h3 i {
        color: var(--accent);
        margin-right: 8px;
    }

    .filters-row {
        padding: 20px;
        background: var(--gray-100);
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: end;
    }

    .filter-group {
        flex: 1;
        min-width: 150px;
    }

    .filter-group label {
        display: block;
        font-size: 0.75rem;
        color: var(--gray-600);
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        border-color: var(--accent);
        outline: none;
    }

    .table-container {
        padding: 0;
    }

    .table {
        margin: 0;
    }

    .table thead th {
        background: var(--gray-100);
        border: none;
        padding: 15px;
        font-weight: 600;
        color: var(--gray-800);
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-color: var(--gray-100);
    }

    .badge-estado {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-pendiente { background: rgba(240, 165, 0, 0.15); color: #b37a00; }
    .badge-enviada { background: rgba(0, 191, 142, 0.15); color: #008060; }
    .badge-fallida { background: rgba(233, 69, 96, 0.15); color: #c23655; }
    .badge-cancelada { background: rgba(108, 117, 125, 0.15); color: #495057; }

    .cliente-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cliente-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .cliente-avatar.avatar-menor {
        background: linear-gradient(135deg, #f0a500 0%, #ffc107 100%);
        border: 2px solid #ff9800;
    }

    .cliente-nombre {
        font-weight: 600;
        color: var(--gray-800);
    }

    .cliente-email {
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    .tipo-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .tipo-por-vencer { background: rgba(240, 165, 0, 0.1); color: var(--warning); }
    .tipo-vencida { background: rgba(233, 69, 96, 0.1); color: var(--accent); }
    .tipo-bienvenida { background: rgba(0, 191, 142, 0.1); color: var(--success); }
    .tipo-pago { background: rgba(67, 97, 238, 0.1); color: var(--info); }

    .btn-action {
        padding: 6px 10px;
        border-radius: 6px;
        border: none;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }

    .btn-action.view {
        background: rgba(67, 97, 238, 0.1);
        color: var(--info);
    }

    .btn-action.resend {
        background: rgba(0, 191, 142, 0.1);
        color: var(--success);
    }

    .btn-action.cancel {
        background: rgba(233, 69, 96, 0.1);
        color: var(--accent);
    }

    .btn-action:hover {
        transform: scale(1.1);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--gray-600);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--gray-200);
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .stat-cards {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-bar {
            flex-direction: column;
        }
    }
</style>
@stop

@section('content_header')
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-bell"></i> Notificaciones Automáticas</h1>
                <small class="text-white-50">Gestión de correos automáticos para membresías</small>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Alertas --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Estadísticas --}}
    <div class="stat-cards">
        <div class="stat-card">
            <div class="icon pending"><i class="fas fa-clock"></i></div>
            <div class="info">
                <div class="number">{{ $estadisticas['pendientes'] }}</div>
                <div class="label">Pendientes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon sent"><i class="fas fa-check-circle"></i></div>
            <div class="info">
                <div class="number">{{ $estadisticas['enviadas_hoy'] }}</div>
                <div class="label">Enviadas Hoy</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon failed"><i class="fas fa-times-circle"></i></div>
            <div class="info">
                <div class="number">{{ $estadisticas['fallidas'] }}</div>
                <div class="label">Fallidas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon total"><i class="fas fa-envelope"></i></div>
            <div class="info">
                <div class="number">{{ $estadisticas['enviadas_mes'] }}</div>
                <div class="label">Este Mes</div>
            </div>
        </div>
    </div>

    {{-- Barra de Acciones --}}
    <div class="action-bar">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.notificaciones.crear') }}" class="btn btn-success" style="background: linear-gradient(135deg, #00bf8e, #00a67d); border: none; border-radius: 10px; padding: 12px 25px; font-weight: 600;">
                <i class="fas fa-plus"></i> Nueva Notificación
            </a>
            <form action="{{ route('admin.notificaciones.ejecutar') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="accion" value="todo">
                <button type="submit" class="btn btn-execute">
                    <i class="fas fa-play"></i> Ejecutar Automáticas
                </button>
            </form>
        </div>
        <a href="{{ route('admin.notificaciones.plantillas') }}" class="btn btn-plantillas">
            <i class="fas fa-file-alt"></i> Gestionar Plantillas
        </a>
    </div>

    {{-- Tabla de Notificaciones --}}
    <div class="main-card">
        <div class="main-card-header">
            <h3><i class="fas fa-list"></i> Historial de Notificaciones</h3>
        </div>

        {{-- Filtros --}}
        <form action="{{ route('admin.notificaciones.index') }}" method="GET">
            <div class="filters-row">
                <div class="filter-group">
                    <label>Estado</label>
                    <select name="estado" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="600" {{ request('estado') == '600' ? 'selected' : '' }}>Pendientes</option>
                        <option value="601" {{ request('estado') == '601' ? 'selected' : '' }}>Enviadas</option>
                        <option value="602" {{ request('estado') == '602' ? 'selected' : '' }}>Fallidas</option>
                        <option value="603" {{ request('estado') == '603' ? 'selected' : '' }}>Canceladas</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Tipo</label>
                    <select name="tipo" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach ($tiposNotificacion as $tipo)
                            <option value="{{ $tipo->id }}" {{ request('tipo') == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o email...">
                </div>
                <div class="filter-group" style="flex: 0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <div class="table-container">
            @if ($notificaciones->count() > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Asunto</th>
                            <th>Estado</th>
                            <th>Programada</th>
                            <th>Enviada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notificaciones as $notificacion)
                            <tr>
                                <td>
                                    <div class="cliente-info">
                                        <div class="cliente-avatar {{ $notificacion->cliente->es_menor_edad ? 'avatar-menor' : '' }}">
                                            {{ substr($notificacion->cliente->nombres ?? 'N', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="cliente-nombre">
                                                {{ $notificacion->cliente->nombre_completo ?? 'N/A' }}
                                                @if($notificacion->cliente->es_menor_edad)
                                                    <span class="badge badge-warning badge-sm ml-1">
                                                        <i class="fas fa-child"></i> Menor
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="cliente-email">
                                                {{ $notificacion->email_destino }}
                                                @if($notificacion->cliente->es_menor_edad && $notificacion->email_destino === $notificacion->cliente->apoderado_email)
                                                    <small class="text-muted ml-1">(Apoderado)</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $tipoClase = match($notificacion->tipoNotificacion->codigo ?? '') {
                                            'membresia_por_vencer' => 'tipo-por-vencer',
                                            'membresia_vencida' => 'tipo-vencida',
                                            'bienvenida' => 'tipo-bienvenida',
                                            'pago_pendiente' => 'tipo-pago',
                                            default => ''
                                        };
                                    @endphp
                                    <span class="tipo-badge {{ $tipoClase }}">
                                        {{ $notificacion->tipoNotificacion->nombre ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span title="{{ $notificacion->asunto }}">
                                        {{ Str::limit($notificacion->asunto, 40) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $estadoClase = match($notificacion->id_estado) {
                                            600 => 'badge-pendiente',
                                            601 => 'badge-enviada',
                                            602 => 'badge-fallida',
                                            603 => 'badge-cancelada',
                                            default => ''
                                        };
                                    @endphp
                                    <span class="badge-estado {{ $estadoClase }}">
                                        {{ $notificacion->estado->nombre ?? 'N/A' }}
                                    </span>
                                    @if ($notificacion->intentos > 0)
                                        <small class="text-muted d-block">
                                            Intentos: {{ $notificacion->intentos }}/{{ $notificacion->max_intentos }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    {{ $notificacion->fecha_programada->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if ($notificacion->fecha_envio)
                                        {{ $notificacion->fecha_envio->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.notificaciones.show', $notificacion) }}" 
                                       class="btn-action view" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if ($notificacion->id_estado == 602)
                                        <form action="{{ route('admin.notificaciones.reenviar', $notificacion) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-action resend" title="Reenviar">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if ($notificacion->id_estado == 600)
                                        <form action="{{ route('admin.notificaciones.cancelar', $notificacion) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-action cancel" title="Cancelar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-3">
                    {{ $notificaciones->withQueryString()->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No hay notificaciones</h4>
                    <p>Las notificaciones aparecerán aquí cuando se programen automáticamente.</p>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Confirmación para ejecutar
    document.querySelectorAll('form[action*="ejecutar"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Ejecutar notificaciones?',
                text: 'Se programarán y enviarán las notificaciones pendientes',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e94560',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ejecutar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });

    // Confirmación para cancelar
    document.querySelectorAll('form[action*="cancelar"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cancelar notificación?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e94560',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@stop
