@extends('adminlte::page')

@section('title', 'Pagos - EstóicosGym')

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
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-600: #6c757d;
            --gray-800: #343a40;
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

        .stat-number {
            font-size: 2.5em;
            font-weight: 800;
            color: var(--info);
        }

        .stat-card.success .stat-number { color: var(--success); }
        .stat-card.warning .stat-number { color: var(--warning); }
        .stat-card.danger .stat-number { color: var(--accent); }

        .stat-label {
            color: var(--gray-600);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* ===== BUSCADOR ===== */
        .search-box {
            background: white;
            border: none;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .search-box input,
        .search-box select {
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus,
        .search-box select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
        }

        /* ===== TABLA ===== */
        .table-responsive {
            border-radius: 0 0 16px 16px;
            overflow: hidden;
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
            padding: 1rem 0.75rem;
            border: 0;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--gray-200);
        }

        .table tbody tr:hover {
            background-color: var(--gray-100);
        }

        .table tbody td {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
        }

        .client-name {
            font-weight: 700;
            color: var(--gray-800);
            font-size: 0.95rem;
        }

        .membership-badge {
            font-size: 0.8rem;
            color: var(--accent);
            font-weight: 600;
        }

        /* ===== STATUS BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pagado {
            background: rgba(0, 191, 142, 0.15);
            color: var(--success);
            border: 2px solid var(--success);
        }

        .status-parcial {
            background: rgba(240, 165, 0, 0.15);
            color: var(--warning);
            border: 2px solid var(--warning);
        }

        .status-pendiente {
            background: rgba(233, 69, 96, 0.15);
            color: var(--accent);
            border: 2px solid var(--accent);
        }

        /* ===== PRECIOS ===== */
        .monto-total {
            color: var(--gray-800);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .monto-abonado {
            color: var(--success);
            font-weight: 600;
            font-size: 0.85rem;
        }

        .monto-pendiente {
            color: var(--accent);
            font-size: 0.8rem;
        }

        /* ===== PROGRESS BAR ===== */
        .progress-mini {
            height: 4px;
            border-radius: 2px;
            background-color: var(--gray-200);
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
            padding: 0.4rem 0.65rem;
            font-size: 0.8rem;
            border-radius: 8px;
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-view {
            background: var(--primary);
            color: white;
        }

        .btn-edit {
            background: var(--warning);
            color: white;
        }

        .btn-delete {
            background: var(--accent);
            color: white;
        }

        /* ===== CARD HEADER ===== */
        .card-header {
            background: var(--primary);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            border-bottom: none;
        }

        .card-header .card-title {
            color: white;
        }

        .card {
            border: 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            border-radius: 16px;
        }

        /* ===== HERO HEADER ===== */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 16px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
        }

        .page-header h1 {
            color: white;
            margin: 0;
            font-weight: 700;
        }

        .page-header h1 i {
            color: var(--accent);
        }

        .btn-new-pago {
            background: var(--success);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-new-pago:hover {
            background: var(--success-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 191, 142, 0.3);
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
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1>
                    <i class="fas fa-money-bill-wave"></i> Gestión de Pagos
                </h1>
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('admin.pagos.create') }}" class="btn btn-new-pago">
                    <i class="fas fa-plus"></i> Nuevo Pago
                </a>
            </div>
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
        $totalPagos = $pagos->total();
        $totalRecaudado = $pagos->sum('monto_abonado');
        $pagosCompletados = $pagos->filter(fn($p) => $p->estado?->codigo == 201)->count();
        $pagosPendientes = $pagos->filter(fn($p) => $p->estado?->codigo != 201)->count();
    @endphp

    <!-- ESTADÍSTICAS RÁPIDAS -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="stat-number">{{ $totalPagos }}</div>
                    <div class="stat-label">Total Pagos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body text-center">
                    <div class="stat-number">${{ number_format($totalRecaudado, 0, ',', '.') }}</div>
                    <div class="stat-label">Recaudado</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body text-center">
                    <div class="stat-number">{{ $pagosCompletados }}</div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card danger">
                <div class="card-body text-center">
                    <div class="stat-number">{{ $pagosPendientes }}</div>
                    <div class="stat-label">Parciales</div>
                </div>
            </div>
        </div>
    </div>

    <!-- BUSCADOR -->
    <div class="search-box">
        <div class="row">
            <div class="col-md-8">
                <input type="text" class="form-control form-control-lg" id="searchInput" placeholder="🔍 Buscar por cliente o referencia...">
            </div>
            <div class="col-md-4">
                <select class="form-control form-control-lg" id="filterEstado">
                    <option value="">-- Todos los Estados --</option>
                    <option value="pagado">Pagado</option>
                    <option value="parcial">Parcial</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TABLA DE PAGOS -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Listado de Pagos
            </h3>
            <div class="card-tools">
                <span class="badge badge-light" id="resultCount">{{ $pagos->count() }} de {{ $pagos->total() }}</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 20%;">Cliente / Membresía</th>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 15%;">Montos</th>
                        <th style="width: 12%;">Estado Pago</th>
                        <th style="width: 12%;">Método</th>
                        <th style="width: 10%;">Referencia</th>
                        <th style="width: 14%; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                        @php
                            $total = $pago->monto_total ?? 0;
                            $abonado = $pago->monto_abonado ?? 0;
                            $pendiente = $pago->monto_pendiente ?? 0;
                            $porcentaje = $total > 0 ? ($abonado / $total) * 100 : 0;
                            $estadoPago = $pago->estado?->codigo == 201 ? 'pagado' : 'parcial';
                        @endphp
                        <tr data-estado="{{ $estadoPago }}">
                            <td>
                                <span class="badge badge-secondary">#{{ $pago->id }}</span>
                            </td>
                            <td>
                                <div class="client-name">
                                    {{ $pago->cliente?->nombres ?? 'Sin cliente' }} {{ $pago->cliente?->apellido_paterno ?? '' }}
                                </div>
                                <div class="membership-badge">
                                    <i class="fas fa-dumbbell"></i> {{ $pago->inscripcion?->membresia?->nombre ?? 'Sin membresía' }}
                                </div>
                            </td>
                            <td>
                                <div class="text-muted" style="font-size: 0.85rem;">
                                    <i class="fas fa-calendar"></i> {{ $pago->fecha_pago?->format('d/m/Y') ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div class="monto-total">${{ number_format($total, 0, ',', '.') }}</div>
                                <div class="monto-abonado">${{ number_format($abonado, 0, ',', '.') }}</div>
                                @if($pendiente > 0)
                                    <div class="monto-pendiente">
                                        <i class="fas fa-clock"></i> Pend: ${{ number_format($pendiente, 0, ',', '.') }}
                                    </div>
                                @endif
                                <div class="progress progress-mini">
                                    <div class="progress-bar" style="width: {{ min($porcentaje, 100) }}%; 
                                         background: {{ $porcentaje >= 100 ? 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)' : 'linear-gradient(135deg, #f5af19 0%, #f12711 100%)' }};">
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($estadoPago === 'pagado')
                                    <span class="status-badge status-pagado">
                                        <i class="fas fa-check-circle"></i> Pagado
                                    </span>
                                @else
                                    <span class="status-badge status-parcial">
                                        <i class="fas fa-hourglass-half"></i> Parcial
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <i class="fas fa-credit-card"></i> {{ $pago->metodoPago?->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $pago->referencia_pago ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.pagos.show', $pago) }}" 
                                       class="btn-action btn-view" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.pagos.edit', $pago) }}" 
                                       class="btn-action btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Eliminar"
                                                onclick="return confirm('¿Estás seguro de eliminar este pago?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>No hay pagos registrados</h5>
                                <p>Crea un nuevo pago para comenzar</p>
                                <a href="{{ route('admin.pagos.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Nuevo Pago
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pagos->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $pagos->appends(request()->query())->links('pagination::bootstrap-4') }}
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
                    var referencia = row.find('td:nth-child(7)').text().toLowerCase();
                    var estadoRow = row.data('estado');
                    
                    var matchesSearch = clientName.includes(searchText) || 
                                        membership.includes(searchText) || 
                                        referencia.includes(searchText);
                    var matchesEstado = filterEstado === '' || estadoRow === filterEstado;

                    if (matchesSearch && matchesEstado) {
                        row.show();
                        visibleCount++;
                    } else {
                        row.hide();
                    }
                });

                $('#resultCount').text(visibleCount + ' de {{ $pagos->total() }}');
            }

            $('#searchInput').on('keyup', filterTable);
            $('#filterEstado').on('change', filterTable);
        });
    </script>
@stop

