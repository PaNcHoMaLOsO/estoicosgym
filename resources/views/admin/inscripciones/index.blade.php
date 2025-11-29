@extends('adminlte::page')

@section('title', 'Inscripciones - EstóicosGym')

@section('css')
    <style>
        /* ===== CARDS ESTADÍSTICAS ===== */
        .stat-card {
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card.success::before {
            background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-card.warning::before {
            background: linear-gradient(90deg, #f5af19 0%, #f12711 100%);
        }

        .stat-card.danger::before {
            background: linear-gradient(90deg, #ff6b6b 0%, #ff8787 100%);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #667eea;
        }

        .stat-card.success .stat-number {
            color: #11998e;
        }

        .stat-card.warning .stat-number {
            color: #f5af19;
        }

        .stat-card.danger .stat-number {
            color: #ff6b6b;
        }

        .stat-label {
            color: #495057;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== BUSCADOR ===== */
        .search-box {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .search-box input,
        .search-box select {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus,
        .search-box select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* ===== TABLA ===== */
        .table-responsive {
            border-radius: 0 0 0.75rem 0.75rem;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            border-bottom: 2px solid #667eea;
        }

        .table thead th {
            color: #2c3e50;
            font-weight: 700;
            padding: 1rem 0.75rem;
            border: 0;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            box-shadow: inset 0 0 5px rgba(102, 126, 234, 0.1);
        }

        .table tbody td {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
        }

        .client-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .membership-badge {
            font-size: 0.8rem;
            color: #667eea;
            font-weight: 600;
        }

        /* ===== STATUS BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .status-partial {
            background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);
            color: white;
        }

        .status-pending {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            color: white;
        }

        .status-paused {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        /* ===== PLAZO BADGES ===== */
        .plazo-badge {
            display: inline-block;
            padding: 0.35rem 0.6rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .plazo-ok {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .plazo-warning {
            background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);
            color: white;
        }

        .plazo-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            color: white;
        }

        /* ===== PRECIOS ===== */
        .precio-base {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .precio-final {
            color: #667eea;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .precio-descuento {
            color: #ff6b6b;
            font-size: 0.8rem;
        }

        /* ===== PROGRESS BAR ===== */
        .progress-mini {
            height: 4px;
            border-radius: 2px;
            background-color: #e9ecef;
            margin-top: 0.3rem;
        }

        /* ===== ACCIONES ===== */
        .action-buttons {
            display: flex;
            gap: 0.35rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-action {
            padding: 0.35rem 0.6rem;
            font-size: 0.8rem;
            border-radius: 0.4rem;
            border: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            white-space: nowrap;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn-view {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-edit {
            background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
            color: white;
        }

        .btn-pay {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-history {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        /* ===== CARD HEADER ===== */
        .card-header {
            border-bottom: 2px solid #667eea;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 0.75rem 0.75rem 0 0 !important;
        }

        .card {
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
        }

        /* ===== FILTROS AVANZADOS ===== */
        .filters-card {
            border: 2px solid #dee2e6;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .filters-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.65rem 0.65rem 0 0 !important;
            border-bottom: 0;
        }

        .filters-card .card-header .card-title {
            color: white;
        }

        .filters-card .card-header .btn-tool {
            color: white;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .table thead th,
            .table tbody td {
                padding: 0.65rem 0.5rem;
                font-size: 0.85rem;
            }

            .stat-number {
                font-size: 1.8em;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.8rem;
            }

            .btn-action {
                padding: 0.3rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
@endsection

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-clipboard-list text-primary"></i> Gestión de Inscripciones
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.inscripciones.create') }}" class="btn btn-success btn-lg">
                <i class="fas fa-plus"></i> Nueva Inscripción
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-lg" role="alert" style="border-left: 5px solid #28a745;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading mb-2">
                <i class="fas fa-check-circle"></i> ¡Éxito!
            </h5>
            {{ $message }}
        </div>
    @endif

    @php
        // Calcular estadísticas
        $totalInscripciones = $inscripciones->total();
        $activas = $inscripciones->filter(fn($i) => $i->estado?->nombre === 'Activa')->count();
        $vencidas = $inscripciones->filter(fn($i) => $i->fecha_vencimiento < now())->count();
        $pausadas = $inscripciones->filter(fn($i) => $i->estaPausada())->count();
    @endphp

    <!-- ESTADÍSTICAS RÁPIDAS -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="stat-number">{{ $totalInscripciones }}</div>
                    <div class="stat-label">Total Inscripciones</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body text-center">
                    <div class="stat-number">{{ $activas }}</div>
                    <div class="stat-label">Activas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card danger">
                <div class="card-body text-center">
                    <div class="stat-number">{{ $vencidas }}</div>
                    <div class="stat-label">Vencidas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body text-center">
                    <div class="stat-number">{{ $pausadas }}</div>
                    <div class="stat-label">Pausadas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- BUSCADOR -->
    <div class="search-box">
        <div class="row">
            <div class="col-md-8">
                <input type="text" class="form-control form-control-lg" id="searchInput" placeholder="🔍 Buscar por nombre de cliente...">
            </div>
            <div class="col-md-4">
                <select class="form-control form-control-lg" id="filterEstado">
                    <option value="">-- Todos los Estados --</option>
                    @foreach($estados as $estado)
                        <option value="{{ strtolower($estado->nombre) }}">{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- TABLA DE INSCRIPCIONES -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Listado de Inscripciones
            </h3>
            <div class="card-tools">
                <span class="badge badge-light" id="resultCount">{{ $inscripciones->count() }} de {{ $inscripciones->total() }}</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 18%;">Cliente / Membresía</th>
                        <th style="width: 10%;">Plazo</th>
                        <th style="width: 12%;">Precios</th>
                        <th style="width: 12%;">Estado Pago</th>
                        <th style="width: 8%;">Convenio</th>
                        <th style="width: 8%;">Estado</th>
                        <th style="width: 7%;">Pausa</th>
                        <th style="width: 20%; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inscripciones as $inscripcion)
                        <tr>
                            <td>
                                <span class="badge badge-secondary">#{{ $inscripcion->id }}</span>
                            </td>
                            <td>
                                <div class="client-name">
                                    {{ $inscripcion->cliente?->nombres ?? 'Sin cliente' }} {{ $inscripcion->cliente?->apellido_paterno ?? '' }}
                                </div>
                                <div class="membership-badge">
                                    <i class="fas fa-dumbbell"></i> {{ $inscripcion->membresia?->nombre ?? 'Sin membresía' }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                @endphp
                                @if($diasRestantes > 30)
                                    <span class="plazo-badge plazo-ok">
                                        <i class="fas fa-calendar-check"></i> {{ $diasRestantes }}d
                                    </span>
                                @elseif($diasRestantes > 7)
                                    <span class="plazo-badge plazo-warning">
                                        <i class="fas fa-clock"></i> {{ $diasRestantes }}d
                                    </span>
                                @elseif($diasRestantes > 0)
                                    <span class="plazo-badge plazo-warning">
                                        <i class="fas fa-exclamation"></i> {{ $diasRestantes }}d
                                    </span>
                                @else
                                    <span class="plazo-badge plazo-danger">
                                        <i class="fas fa-times-circle"></i> Vencida
                                    </span>
                                @endif
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $inscripcion->fecha_vencimiento?->format('d/m/Y') ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div class="precio-base">
                                    Base: ${{ number_format($inscripcion->precio_base ?? 0, 0, '.', '.') }}
                                </div>
                                @if($inscripcion->descuento_aplicado > 0)
                                    <div class="precio-descuento">
                                        <i class="fas fa-tag"></i> -${{ number_format($inscripcion->descuento_aplicado, 0, '.', '.') }}
                                    </div>
                                @endif
                                <div class="precio-final">
                                    ${{ number_format($inscripcion->precio_final ?? $inscripcion->precio_base ?? 0, 0, '.', '.') }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $estadoPago = $inscripcion->obtenerEstadoPago();
                                    $estado = $estadoPago['estado'];
                                    $totalAbonado = $estadoPago['total_abonado'];
                                    $pendiente = $estadoPago['pendiente'];
                                    $porcentaje = $estadoPago['porcentaje_pagado'];
                                @endphp
                                
                                @if($estado === 'pagado')
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check-circle"></i> Pagado
                                    </span>
                                @elseif($estado === 'parcial')
                                    <span class="status-badge status-partial">
                                        <i class="fas fa-hourglass-half"></i> Parcial
                                    </span>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        ${{ number_format($totalAbonado, 0, '.', '.') }} / ${{ number_format($totalAbonado + $pendiente, 0, '.', '.') }}
                                    </div>
                                @else
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-exclamation-circle"></i> Pendiente
                                    </span>
                                @endif
                                
                                <div class="progress progress-mini">
                                    <div class="progress-bar" style="width: {{ min($porcentaje, 100) }}%; 
                                         background: {{ $porcentaje >= 100 ? 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)' : ($porcentaje >= 50 ? 'linear-gradient(135deg, #f5af19 0%, #f12711 100%)' : 'linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%)') }};">
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($inscripcion->id_convenio)
                                    <span class="badge badge-primary">
                                        <i class="fas fa-handshake"></i> Sí
                                    </span>
                                    @if($inscripcion->convenio)
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            {{ Str::limit($inscripcion->convenio->nombre, 12) }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-minus"></i> No
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                {!! $inscripcion->estado?->badge ?? '<span class="badge bg-secondary">N/A</span>' !!}
                            </td>
                            <td class="text-center">
                                @if($inscripcion->estaPausada())
                                    <span class="status-badge status-paused" title="Pausada - {{ $inscripcion->razon_pausa }}">
                                        <i class="fas fa-pause"></i> {{ $inscripcion->dias_pausa }}d
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-play"></i> Activa
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" 
                                       class="btn-action btn-view" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" 
                                       class="btn-action btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.pagos.create', ['inscripcion_id' => $inscripcion->id]) }}" 
                                       class="btn-action btn-pay" title="Nuevo Pago">
                                        <i class="fas fa-dollar-sign"></i>
                                    </a>
                                    <a href="{{ route('admin.pagos.index', ['id_inscripcion' => $inscripcion->id]) }}" 
                                       class="btn-action btn-history" title="Ver Pagos">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>No hay inscripciones registradas</h5>
                                <p>Crea una nueva inscripción para comenzar</p>
                                <a href="{{ route('admin.inscripciones.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Nueva Inscripción
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($inscripciones->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $inscripciones->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Filtro en tiempo real
            function filterTable() {
                var searchText = $('#searchInput').val().toLowerCase();
                var filterEstado = $('#filterEstado').val().toLowerCase();
                var visibleCount = 0;

                $('tbody tr').each(function() {
                    var row = $(this);
                    var clientName = row.find('.client-name').text().toLowerCase();
                    var membership = row.find('.membership-badge').text().toLowerCase();
                    var estadoBadge = row.find('td:nth-child(7)').text().toLowerCase().trim();
                    
                    var matchesSearch = clientName.includes(searchText) || membership.includes(searchText);
                    var matchesEstado = filterEstado === '' || estadoBadge.includes(filterEstado);

                    if (matchesSearch && matchesEstado) {
                        row.show();
                        visibleCount++;
                    } else {
                        row.hide();
                    }
                });

                $('#resultCount').text(visibleCount + ' de {{ $inscripciones->total() }}');
            }

            $('#searchInput').on('keyup', filterTable);
            $('#filterEstado').on('change', filterTable);

            // Confirmación de eliminación
            $('.btn-delete').on('click', function(e) {
                if (!confirm('¿Estás seguro de que deseas eliminar esta inscripción?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@stop
