@extends('adminlte::page')

@section('title', 'Reportes')

@section('css')
    <style>
        :root {
            --primary: #1e293b;
            --purple: #7c3aed;
            --info: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --pink: #ec4899;
        }

        .page-header {
            background: linear-gradient(135deg, var(--purple) 0%, #4f46e5 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.blue { background: rgba(59, 130, 246, 0.7) !important; color: #3b82f6 !important; }
        .stat-icon.green { background: rgba(16, 185, 129, 0.7) !important; color: #10b981 !important; }
        .stat-icon.purple { background: rgba(124, 58, 237, 0.7) !important; color: #7c3aed !important; }
        .stat-icon.yellow { background: rgba(245, 158, 11, 0.7) !important; color: #f59e0b !important; }

        .stat-icon i {
            color: inherit !important;
            font-size: 1.5rem !important;
        }

        .stat-info h3 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            color: var(--primary);
        }

        .stat-info span {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Section Title */
        .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .section-title i {
            font-size: 1.25rem;
            color: var(--purple);
        }

        .section-title h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            color: var(--primary);
        }

        /* Quick Reports */
        .quick-reports {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .report-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            text-decoration: none;
            color: inherit;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .report-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--card-color, var(--purple));
        }

        .report-card:hover {
            border-color: var(--card-color, var(--purple));
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
        }

        .report-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            background: rgba(124, 58, 237, 0.1);
            color: #7c3aed;
        }

        .report-card h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .report-card p {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0;
            line-height: 1.5;
        }

        .report-card .arrow {
            position: absolute;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .report-card:hover .arrow {
            background: var(--card-color, var(--purple));
            color: white;
            transform: translateX(3px);
        }

        /* Builder Card */
        .builder-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 20px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .builder-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(124,58,237,0.3) 0%, transparent 70%);
            border-radius: 50%;
        }

        .builder-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(59,130,246,0.2) 0%, transparent 70%);
            border-radius: 50%;
        }

        .builder-card-content {
            position: relative;
            z-index: 1;
        }

        .builder-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .builder-card h3 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
        }

        .builder-card p {
            opacity: 0.85;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .builder-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .builder-feature {
            background: rgba(255,255,255,0.15);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .builder-feature i {
            color: #10b981;
        }

        .btn-builder {
            background: linear-gradient(135deg, var(--purple) 0%, #6366f1 100%);
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-builder:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(124,58,237,0.4);
            color: white;
            text-decoration: none;
        }

        /* Modulos Grid */
        .modulos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .modulo-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .modulo-card:hover {
            border-color: var(--modulo-color, var(--purple));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-decoration: none;
            color: inherit;
        }

        .modulo-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin: 0 auto 0.75rem;
        }

        .modulo-card h5 {
            font-weight: 600;
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .builder-card {
                padding: 1.5rem;
            }

            .builder-features {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-chart-bar mr-2"></i> Centro de Reportes</h1>
        <p>Genera reportes personalizados y analiza los datos de tu gimnasio</p>
    </div>

    <!-- Stats Rápidos -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>{{ number_format($stats['clientes']) }}</h3>
                <span>Total Clientes</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3>{{ number_format($stats['inscripciones_activas']) }}</h3>
                <span>Inscripciones Activas</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-info">
                <h3>{{ number_format($stats['pagos_mes']) }}</h3>
                <span>Pagos este mes</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h3>${{ number_format($stats['ingresos_mes'], 0, ',', '.') }}</h3>
                <span>Ingresos del mes</span>
            </div>
        </div>
    </div>

    <!-- Builder Card -->
    <div class="builder-card mb-4">
        <div class="builder-card-content">
            <div class="builder-icon">
                <i class="fas fa-magic"></i>
            </div>
            <h3>Constructor de Reportes Dinámico</h3>
            <p>Crea reportes totalmente personalizados. Selecciona los módulos, campos, filtros y formato de exportación que necesites.</p>
            
            <div class="builder-features">
                <span class="builder-feature">
                    <i class="fas fa-check"></i> Selección de campos
                </span>
                <span class="builder-feature">
                    <i class="fas fa-check"></i> Filtros avanzados
                </span>
                <span class="builder-feature">
                    <i class="fas fa-check"></i> Exportar Excel/PDF
                </span>
                <span class="builder-feature">
                    <i class="fas fa-check"></i> Gráficos interactivos
                </span>
            </div>
            
            <a href="{{ route('admin.reportes.builder') }}" class="btn-builder">
                <i class="fas fa-tools"></i>
                Abrir Constructor
            </a>
        </div>
    </div>

    <!-- Reportes Predefinidos -->
    <div class="section-title">
        <i class="fas fa-file-alt"></i>
        <h2>Reportes Predefinidos</h2>
    </div>

    <div class="quick-reports">
        <a href="{{ route('admin.reportes.predefinido', 'resumen-general') }}" class="report-card" style="--card-color: #7c3aed;">
            <div class="report-card-icon" style="background: rgba(124,58,237,0.1); color: #7c3aed;">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <h4>Resumen General</h4>
            <p>Vista general del estado del gimnasio con métricas clave y tendencias.</p>
            <span class="arrow"><i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="{{ route('admin.reportes.predefinido', 'ingresos-mensuales') }}" class="report-card" style="--card-color: #10b981;">
            <div class="report-card-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                <i class="fas fa-chart-line"></i>
            </div>
            <h4>Ingresos Mensuales</h4>
            <p>Análisis detallado de ingresos por mes, método de pago y membresía.</p>
            <span class="arrow"><i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="{{ route('admin.reportes.predefinido', 'membresias-activas') }}" class="report-card" style="--card-color: #3b82f6;">
            <div class="report-card-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                <i class="fas fa-id-card"></i>
            </div>
            <h4>Membresías Activas</h4>
            <p>Distribución de membresías activas y estadísticas por tipo de plan.</p>
            <span class="arrow"><i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="{{ route('admin.reportes.predefinido', 'clientes-por-vencer') }}" class="report-card" style="--card-color: #f59e0b;">
            <div class="report-card-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                <i class="fas fa-clock"></i>
            </div>
            <h4>Clientes por Vencer</h4>
            <p>Lista de clientes cuyas membresías están próximas a vencer.</p>
            <span class="arrow"><i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="{{ route('admin.reportes.predefinido', 'pagos-pendientes') }}" class="report-card" style="--card-color: #ef4444;">
            <div class="report-card-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h4>Pagos Pendientes</h4>
            <p>Pagos pendientes y parciales que requieren seguimiento.</p>
            <span class="arrow"><i class="fas fa-arrow-right"></i></span>
        </a>
    </div>

    <!-- Módulos Disponibles -->
    <div class="section-title">
        <i class="fas fa-database"></i>
        <h2>Módulos Disponibles para Reportes</h2>
    </div>

    <div class="modulos-grid">
        @foreach($modulos as $key => $modulo)
        <a href="{{ route('admin.reportes.builder', ['modulo' => $key]) }}" class="modulo-card" style="--modulo-color: {{ $modulo['color'] }};">
            <div class="modulo-icon" style="background: {{ $modulo['color'] }}20; color: {{ $modulo['color'] }};">
                <i class="{{ $modulo['icono'] }}"></i>
            </div>
            <h5>{{ $modulo['nombre'] }}</h5>
        </a>
        @endforeach
    </div>
@endsection
