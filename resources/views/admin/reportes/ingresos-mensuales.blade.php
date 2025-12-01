@extends('adminlte::page')

@section('title', 'Ingresos Mensuales')

@section('css')
    <style>
        :root {
            --primary: #1e293b;
            --purple: #7c3aed;
            --success: #10b981;
        }

        .report-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .report-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .report-header h1 { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; }
        .report-header p { opacity: 0.9; margin-bottom: 0; }

        .btn-back {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-back:hover { background: rgba(255,255,255,0.3); color: white; }

        .year-selector {
            position: absolute;
            top: 1.5rem;
            right: 8rem;
        }

        .year-selector select {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            cursor: pointer;
        }

        .total-anual {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .total-anual h2 {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--success);
            margin-bottom: 0.5rem;
        }

        .report-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .report-card-header {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .report-card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .report-card-body { padding: 1.5rem; }

        .meses-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .mes-item {
            text-align: center;
            padding: 1.25rem;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .mes-item:hover {
            background: #e0f2fe;
            transform: translateY(-2px);
        }

        .mes-item .nombre { font-weight: 600; color: #64748b; margin-bottom: 0.5rem; }
        .mes-item .monto { font-size: 1.25rem; font-weight: 800; color: var(--success); }
        .mes-item .cantidad { font-size: 0.8rem; color: #94a3b8; }

        .metodo-item, .membresia-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 0.75rem;
        }

        .metodo-item:last-child, .membresia-item:last-child { margin-bottom: 0; }

        .metodo-item .nombre, .membresia-item .nombre { font-weight: 600; }
        .metodo-item .valores, .membresia-item .valores { text-align: right; }
        .metodo-item .monto, .membresia-item .monto { font-weight: 700; color: var(--success); }
        .metodo-item .cantidad, .membresia-item .cantidad { font-size: 0.8rem; color: #94a3b8; }

        @media (max-width: 768px) {
            .meses-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
@endsection

@section('content')
    <div class="report-header">
        <a href="{{ route('admin.reportes.index') }}" class="btn-back">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <div class="year-selector">
            <select onchange="window.location.href='{{ route('admin.reportes.predefinido', 'ingresos-mensuales') }}?year=' + this.value">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <h1><i class="fas fa-chart-line mr-2"></i> Ingresos Mensuales</h1>
        <p>Análisis detallado de ingresos - Año {{ $year }}</p>
    </div>

    <!-- Total Anual -->
    <div class="total-anual">
        <h2>${{ number_format($totalAnual, 0, ',', '.') }}</h2>
        <p class="text-muted mb-0">Total Ingresos {{ $year }}</p>
    </div>

    <!-- Ingresos por Mes -->
    <div class="report-card">
        <div class="report-card-header">
            <h3><i class="fas fa-calendar-alt mr-2" style="color: rgba(16, 185, 129, 0.7);"></i> Ingresos por Mes</h3>
        </div>
        <div class="report-card-body">
            <div class="meses-grid">
                @php
                    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                @endphp
                @for($i = 1; $i <= 12; $i++)
                    @php
                        $datoMes = $ingresosPorMes->firstWhere('mes', $i);
                    @endphp
                    <div class="mes-item">
                        <div class="nombre">{{ $meses[$i-1] }}</div>
                        <div class="monto">${{ number_format($datoMes->total ?? 0, 0, ',', '.') }}</div>
                        <div class="cantidad">{{ $datoMes->cantidad ?? 0 }} pagos</div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Por Método de Pago -->
        <div class="col-lg-6">
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-credit-card mr-2" style="color: rgba(59, 130, 246, 0.7);"></i> Por Método de Pago</h3>
                </div>
                <div class="report-card-body">
                    @forelse($ingresosPorMetodo as $metodo)
                    <div class="metodo-item">
                        <div class="nombre">{{ $metodo->nombre }}</div>
                        <div class="valores">
                            <div class="monto">${{ number_format($metodo->total, 0, ',', '.') }}</div>
                            <div class="cantidad">{{ $metodo->cantidad }} pagos</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">Sin datos</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Por Membresía -->
        <div class="col-lg-6">
            <div class="report-card">
                <div class="report-card-header">
                    <h3><i class="fas fa-id-card mr-2" style="color: rgba(124, 58, 237, 0.7);"></i> Por Membresía</h3>
                </div>
                <div class="report-card-body">
                    @forelse($ingresosPorMembresia as $membresia)
                    <div class="membresia-item">
                        <div class="nombre">{{ $membresia->nombre }}</div>
                        <div class="valores">
                            <div class="monto">${{ number_format($membresia->total, 0, ',', '.') }}</div>
                            <div class="cantidad">{{ $membresia->cantidad }} pagos</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">Sin datos</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
