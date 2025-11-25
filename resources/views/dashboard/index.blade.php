@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Tarjeta: Clientes Totales -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalClientes }}</h3>
                    <p>Clientes Registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('clientes.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Tarjeta: Inscripciones Activas -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalInscripciones }}</h3>
                    <p>Inscripciones Activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('inscripciones.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Tarjeta: Pagos del Mes -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($pagosDelMes, 2) }}</h3>
                    <p>Pagos este Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('pagos.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Tarjeta: Ingresos Totales -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($ingresosTotales, 2) }}</h3>
                    <p>Ingresos Totales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Segunda Fila: Gráficos y Tablas -->
    <div class="row">
        <!-- Tabla: Últimos Pagos -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Últimos Pagos</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosPagos as $pago)
                                <tr>
                                    <td>{{ $pago->inscripcion->cliente->nombres ?? 'N/A' }}</td>
                                    <td>${{ number_format($pago->monto_abonado, 2) }}</td>
                                    <td>{{ $pago->metodoPago->nombre ?? 'N/A' }}</td>
                                    <td>{{ $pago->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay pagos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabla: Inscripciones Recientes -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Inscripciones Recientes</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Membresía</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inscripcionesRecientes as $inscripcion)
                                <tr>
                                    <td>{{ $inscripcion->cliente->nombre ?? 'N/A' }}</td>
                                    <td>{{ $inscripcion->membresia->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $inscripcion->estado->activo ? 'success' : 'danger' }}">
                                            {{ $inscripcion->estado->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $inscripcion->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay inscripciones registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tercera Fila: Estadísticas -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Membresías Más Vendidas</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @forelse($membresiasVendidas as $membresia)
                            <li class="mb-2">
                                <strong>{{ $membresia->nombre }}</strong>
                                <span class="float-right badge badge-primary">{{ $membresia->count }}</span>
                            </li>
                        @empty
                            <li class="text-muted">Sin datos</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Métodos de Pago</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @forelse($metodosPago as $metodo)
                            <li class="mb-2">
                                <strong>{{ $metodo->nombre }}</strong>
                                <span class="float-right badge badge-info">{{ $metodo->count }}</span>
                            </li>
                        @empty
                            <li class="text-muted">Sin datos</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Estados de Inscripciones</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @forelse($inscripcionesPorEstado as $item)
                            <li class="mb-2">
                                <strong>{{ $item->estado->nombre ?? 'N/A' }}</strong>
                                <span class="float-right badge badge-secondary">{{ $item->total }}</span>
                            </li>
                        @empty
                            <li class="text-muted">Sin datos</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@stop

@section('js')
    <script>
        console.log('Dashboard AdminLTE v3 cargado correctamente');
    </script>
@stop
