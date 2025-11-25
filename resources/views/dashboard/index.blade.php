<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Estoicos Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
        }

        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: var(--primary-color);
            min-height: 100vh;
            padding: 20px 0;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-left-color: var(--secondary-color);
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
            padding-bottom: 20px !important;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .stat-card {
            padding: 20px;
            text-align: center;
            border-left: 4px solid var(--secondary-color);
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .badge-success {
            background-color: #27ae60;
        }

        .badge-warning {
            background-color: #f39c12;
        }

        .badge-danger {
            background-color: #e74c3c;
        }

        table {
            font-size: 14px;
        }

        .text-truncate-2 {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .progress-bar-dynamic {
            background-color: #007bff;
        }

        .main-content {
            padding: 30px;
        }

        .alert-info {
            background-color: #ecf0f1;
            border: 1px solid #bdc3c7;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="navbar-brand px-3">
                    <i class="fas fa-dumbbell"></i> Estoicos Gym
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="#dashboard">
                        <i class="fas fa-chart-line me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="#clientes">
                        <i class="fas fa-users me-2"></i> Clientes
                    </a>
                    <a class="nav-link" href="#inscripciones">
                        <i class="fas fa-id-card me-2"></i> Inscripciones
                    </a>
                    <a class="nav-link" href="#pagos">
                        <i class="fas fa-credit-card me-2"></i> Pagos
                    </a>
                    <a class="nav-link" href="#membresias">
                        <i class="fas fa-ticket me-2"></i> Membresías
                    </a>
                    <hr class="bg-secondary">
                    <a class="nav-link" href="#configuracion">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Dashboard</h1>
                    <div>
                        <button class="btn btn-outline-secondary me-2">
                            <i class="fas fa-bell"></i>
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-user-circle"></i>
                        </button>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-label">Total Clientes</div>
                            <div class="stat-value">{{ $totalClientes }}</div>
                            <small class="text-muted">Registrados en el sistema</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card" style="border-left-color: #27ae60;">
                            <div class="stat-label">Clientes Activos</div>
                            <div class="stat-value">{{ $clientesActivos }}</div>
                            <small class="text-muted">Con membresía vigente</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card" style="border-left-color: #2ecc71;">
                            <div class="stat-label">Ingresos Mes</div>
                            <div class="stat-value">${{ number_format($ingresosMesActual, 0) }}</div>
                            <small class="text-muted">Noviembre 2024</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card" style="border-left-color: #e67e22;">
                            <div class="stat-label">Pagos Pendientes</div>
                            <div class="stat-value">${{ number_format($pagosPendientes, 0) }}</div>
                            <small class="text-muted">Por cobrar</small>
                        </div>
                    </div>
                </div>

                <!-- Main Charts Row -->
                <div class="row mb-4">
                    <!-- Membresías por Vencer -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Membresías por Vencer (Próximos 7 días)
                            </div>
                            <div class="card-body">
                                @if($membresiasProximas->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Membresía</th>
                                                    <th>Vencimiento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($membresiasProximas as $membresia)
                                                    <tr>
                                                        <td>{{ $membresia->cliente->nombre_completo }}</td>
                                                        <td><span class="badge bg-warning">{{ $membresia->membresia->nombre }}</span></td>
                                                        <td>{{ $membresia->fecha_vencimiento->format('d/m/Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        No hay membresías por vencer en los próximos 7 días.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Ingresos por Método -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-2"></i>
                                Ingresos por Método de Pago
                            </div>
                            <div class="card-body">
                                @if($ingresosPorMetodo->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Método</th>
                                                    <th class="text-end">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($ingresosPorMetodo as $ingreso)
                                                    <tr>
                                                        <td>{{ $ingreso['metodo'] }}</td>
                                                        <td class="text-end">${{ number_format($ingreso['total'], 0) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">Sin ingresos registrados este mes.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Row -->
                <div class="row">
                    <!-- Pagos Recientes -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-history me-2"></i>
                                Últimos Pagos
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($pagosRecientes as $pago)
                                                <tr>
                                                    <td>{{ $pago->cliente->nombre_completo }}</td>
                                                    <td>${{ number_format($pago->monto_abonado, 0) }}</td>
                                                    <td>
                                                        <span class="badge {{ $pago->estado->codigo == 302 ? 'bg-success' : 'bg-warning' }}">
                                                            {{ $pago->estado->nombre }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">
                                                        Sin pagos registrados
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clientes Recientes -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-user-plus me-2"></i>
                                Clientes Recientes
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Teléfono</th>
                                                <th>Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($clientesRecientes as $cliente)
                                                <tr>
                                                    <td>{{ $cliente->nombre_completo }}</td>
                                                    <td>{{ $cliente->celular }}</td>
                                                    <td>{{ $cliente->created_at->format('d/m/Y') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">
                                                        Sin clientes registrados
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membresías Más Vendidas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-fire me-2"></i>
                                Membresías Más Vendidas (Noviembre)
                            </div>
                            <div class="card-body">
                                @if($membresiasVendidas->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Membresía</th>
                                                    <th>Cantidad Vendida</th>
                                                    <th>Ingresos</th>
                                                    <th>% del Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalIngresos = $membresiasVendidas->sum('ingresos');
                                                @endphp
                                                @foreach($membresiasVendidas as $membresia)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $membresia['membresia'] }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary">{{ $membresia['cantidad'] }}</span>
                                                        </td>
                                                        <td>${{ number_format($membresia['ingresos'], 0) }}</td>
                                                        <td>
                                                            @php
                                                                $porcentaje = $totalIngresos > 0 ? ($membresia['ingresos'] / $totalIngresos * 100) : 0;
                                                            @endphp
                                                            {{ number_format($porcentaje, 1) }}%
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">Sin membresías vendidas este mes.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
