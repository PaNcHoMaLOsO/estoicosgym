@extends('adminlte::page')

@section('title', 'Detalle de Traspaso - EstóicosGym')

@section('css')
    <style>
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
        }

        .detail-card {
            border: 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            border-radius: 16px;
            background: white;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .detail-card .card-header {
            background: var(--primary);
            color: white;
            border-bottom: none;
            padding: 1rem 1.25rem;
        }

        .detail-card .card-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .detail-card .card-body {
            padding: 1.5rem;
        }

        /* ===== FLOW VISUAL ===== */
        .traspaso-visual {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--gray-100), white);
            border-radius: 16px;
            margin-bottom: 1.5rem;
        }

        .cliente-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            min-width: 200px;
        }

        .cliente-card.origen {
            border-top: 4px solid var(--warning);
        }

        .cliente-card.destino {
            border-top: 4px solid var(--success);
        }

        .cliente-card .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .cliente-card.origen .avatar {
            background: linear-gradient(135deg, var(--warning), #ffcc00);
        }

        .cliente-card.destino .avatar {
            background: linear-gradient(135deg, var(--success), #00d9a0);
        }

        .cliente-card h6 {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .cliente-card .rut {
            color: var(--gray-600);
            font-size: 0.85rem;
        }

        .transfer-arrow {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .transfer-arrow .arrow {
            font-size: 2.5rem;
            color: var(--accent);
        }

        .transfer-arrow .label {
            background: var(--accent);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* ===== DETALLE ITEMS ===== */
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--gray-600);
            font-weight: 500;
        }

        .detail-value {
            color: var(--primary);
            font-weight: 600;
            text-align: right;
        }

        .detail-value.money {
            font-size: 1.1rem;
            color: var(--success);
        }

        .detail-value.deuda {
            color: var(--accent);
        }

        /* ===== BADGES ===== */
        .badge-membresia {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .badge-deuda-si {
            background: var(--accent);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .badge-deuda-no {
            background: var(--success);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        /* ===== INSCRIPCIONES ===== */
        .inscripcion-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--info);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .inscripcion-link:hover {
            background: #3651ce;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(67, 97, 238, 0.3);
        }

        /* ===== MOTIVO ===== */
        .motivo-box {
            background: var(--gray-100);
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid var(--info);
            font-style: italic;
            color: var(--gray-600);
        }

        /* ===== USUARIO ===== */
        .usuario-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .usuario-info .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        /* ===== BOTONES ===== */
        .btn-estoicos {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border: none;
            color: white;
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-estoicos:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 69, 96, 0.3);
            color: white;
        }

        .btn-outline-estoicos {
            border: 2px solid var(--accent);
            color: var(--accent);
            background: transparent;
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-estoicos:hover {
            background: var(--accent);
            color: white;
        }

        .page-title {
            color: var(--primary);
            font-weight: 700;
        }
    </style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="fas fa-exchange-alt text-danger mr-2"></i>
                Detalle de Traspaso
            </h1>
            <p class="text-muted">Traspaso realizado el {{ $traspaso->fecha_traspaso->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.historial.index') }}" class="btn btn-outline-estoicos">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Historial
        </a>
    </div>
@endsection

@section('content')
    <!-- Visualización del Traspaso -->
    <div class="traspaso-visual">
        <div class="cliente-card origen">
            <div class="avatar">
                <i class="fas fa-user-minus"></i>
            </div>
            @if($traspaso->clienteOrigen)
                <h6>{{ $traspaso->clienteOrigen->nombres }} {{ $traspaso->clienteOrigen->apellido_paterno }}</h6>
                <span class="rut">{{ $traspaso->clienteOrigen->rut }}</span>
            @else
                <h6 class="text-muted">Cliente eliminado</h6>
            @endif
            <div class="mt-2">
                <small class="text-muted">Cliente Origen</small>
            </div>
        </div>

        <div class="transfer-arrow">
            <span class="arrow"><i class="fas fa-arrow-right"></i></span>
            <span class="label">TRASPASO</span>
        </div>

        <div class="cliente-card destino">
            <div class="avatar">
                <i class="fas fa-user-plus"></i>
            </div>
            @if($traspaso->clienteDestino)
                <h6>{{ $traspaso->clienteDestino->nombres }} {{ $traspaso->clienteDestino->apellido_paterno }}</h6>
                <span class="rut">{{ $traspaso->clienteDestino->rut }}</span>
            @else
                <h6 class="text-muted">Cliente eliminado</h6>
            @endif
            <div class="mt-2">
                <small class="text-muted">Cliente Destino</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información del Traspaso -->
        <div class="col-lg-6">
            <div class="detail-card card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle mr-2"></i>Información del Traspaso</h5>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label">Membresía</span>
                        <span class="detail-value">
                            <span class="badge-membresia">{{ $traspaso->membresia->nombre ?? 'N/A' }}</span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha del Traspaso</span>
                        <span class="detail-value">{{ $traspaso->fecha_traspaso->format('d/m/Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Días Traspasados</span>
                        <span class="detail-value">{{ $traspaso->dias_restantes_traspasados }} días</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Vencimiento Original</span>
                        <span class="detail-value">{{ $traspaso->fecha_vencimiento_original->format('d/m/Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hora de Registro</span>
                        <span class="detail-value">{{ $traspaso->created_at->format('H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Financiera -->
        <div class="col-lg-6">
            <div class="detail-card card">
                <div class="card-header">
                    <h5><i class="fas fa-dollar-sign mr-2"></i>Información Financiera</h5>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label">Monto Pagado</span>
                        <span class="detail-value money">${{ number_format($traspaso->monto_pagado, 0, ',', '.') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">¿Se Transfirió Deuda?</span>
                        <span class="detail-value">
                            @if($traspaso->se_transfirio_deuda)
                                <span class="badge-deuda-si"><i class="fas fa-exclamation-triangle mr-1"></i>Sí</span>
                            @else
                                <span class="badge-deuda-no"><i class="fas fa-check mr-1"></i>No</span>
                            @endif
                        </span>
                    </div>
                    @if($traspaso->se_transfirio_deuda)
                        <div class="detail-row">
                            <span class="detail-label">Deuda Transferida</span>
                            <span class="detail-value deuda">${{ number_format($traspaso->deuda_transferida, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Motivo del Traspaso -->
    <div class="detail-card card">
        <div class="card-header">
            <h5><i class="fas fa-comment-alt mr-2"></i>Motivo del Traspaso</h5>
        </div>
        <div class="card-body">
            <div class="motivo-box">
                "{{ $traspaso->motivo }}"
            </div>
        </div>
    </div>

    <!-- Inscripciones Relacionadas -->
    <div class="row">
        <div class="col-lg-6">
            <div class="detail-card card">
                <div class="card-header bg-warning">
                    <h5><i class="fas fa-file-alt mr-2"></i>Inscripción Origen</h5>
                </div>
                <div class="card-body">
                    @if($traspaso->inscripcionOrigen)
                        <div class="detail-row">
                            <span class="detail-label">Estado</span>
                            <span class="detail-value">
                                <span class="badge" style="background: {{ $traspaso->inscripcionOrigen->estado->color ?? '#6c757d' }}; color: white;">
                                    {{ $traspaso->inscripcionOrigen->estado->nombre ?? 'N/A' }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">ID Inscripción</span>
                            <span class="detail-value">#{{ $traspaso->inscripcionOrigen->id }}</span>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="{{ route('admin.inscripciones.show', $traspaso->inscripcionOrigen) }}" class="inscripcion-link">
                                <i class="fas fa-external-link-alt"></i>
                                Ver Inscripción Origen
                            </a>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Inscripción no disponible</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="detail-card card">
                <div class="card-header bg-success">
                    <h5><i class="fas fa-file-alt mr-2"></i>Inscripción Destino</h5>
                </div>
                <div class="card-body">
                    @if($traspaso->inscripcionDestino)
                        <div class="detail-row">
                            <span class="detail-label">Estado</span>
                            <span class="detail-value">
                                <span class="badge" style="background: {{ $traspaso->inscripcionDestino->estado->color ?? '#6c757d' }}; color: white;">
                                    {{ $traspaso->inscripcionDestino->estado->nombre ?? 'N/A' }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">ID Inscripción</span>
                            <span class="detail-value">#{{ $traspaso->inscripcionDestino->id }}</span>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="{{ route('admin.inscripciones.show', $traspaso->inscripcionDestino) }}" class="inscripcion-link">
                                <i class="fas fa-external-link-alt"></i>
                                Ver Inscripción Destino
                            </a>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Inscripción no disponible</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Usuario que realizó el traspaso -->
    @if($traspaso->usuario)
        <div class="detail-card card">
            <div class="card-header">
                <h5><i class="fas fa-user-shield mr-2"></i>Realizado por</h5>
            </div>
            <div class="card-body">
                <div class="usuario-info">
                    <div class="avatar">
                        {{ strtoupper(substr($traspaso->usuario->name, 0, 1)) }}
                    </div>
                    <div>
                        <strong>{{ $traspaso->usuario->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $traspaso->usuario->email }}</small>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
