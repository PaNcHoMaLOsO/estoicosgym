@extends('adminlte::page')

@section('title', 'Clientes por Vencer')

@section('css')
    <style>
        :root {
            --primary: #1e293b;
            --warning: #f59e0b;
        }

        .report-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
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
        }

        .dias-selector {
            position: absolute;
            top: 1.5rem;
            right: 8rem;
        }

        .dias-selector select {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
        }

        .total-box {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .total-box h2 {
            font-size: 4rem;
            font-weight: 800;
            color: var(--warning);
            margin-bottom: 0.5rem;
        }

        .report-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .cliente-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .cliente-row:hover { background: #f8fafc; }
        .cliente-row:last-child { border-bottom: none; }

        .cliente-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cliente-avatar {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .cliente-data h5 { margin: 0; font-weight: 700; }
        .cliente-data .membresia { font-size: 0.85rem; color: #64748b; }
        .cliente-data .telefono { font-size: 0.8rem; color: #94a3b8; }

        .vencimiento {
            text-align: right;
        }

        .vencimiento .fecha {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .vencimiento .dias-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .vencimiento .dias-badge.urgente { background: #fef2f2; color: #ef4444; }
        .vencimiento .dias-badge.pronto { background: #fffbeb; color: #f59e0b; }
        .vencimiento .dias-badge.ok { background: #ecfdf5; color: #10b981; }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }

        .empty-state i { font-size: 4rem; margin-bottom: 1rem; color: #10b981; }
    </style>
@endsection

@section('content')
    <div class="report-header">
        <a href="{{ route('admin.reportes.index') }}" class="btn-back">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <div class="dias-selector">
            <select onchange="window.location.href='{{ route('admin.reportes.predefinido', 'clientes-por-vencer') }}?dias=' + this.value">
                <option value="3" {{ $dias == 3 ? 'selected' : '' }}>3 días</option>
                <option value="7" {{ $dias == 7 ? 'selected' : '' }}>7 días</option>
                <option value="15" {{ $dias == 15 ? 'selected' : '' }}>15 días</option>
                <option value="30" {{ $dias == 30 ? 'selected' : '' }}>30 días</option>
            </select>
        </div>
        <h1><i class="fas fa-clock mr-2"></i> Clientes por Vencer</h1>
        <p>Membresías que vencen en los próximos {{ $dias }} días</p>
    </div>

    <div class="total-box">
        <h2>{{ $clientesPorVencer->count() }}</h2>
        <p class="text-muted mb-0">Clientes con membresía por vencer</p>
    </div>

    <div class="report-card">
        @if($clientesPorVencer->isEmpty())
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h4>¡Excelente!</h4>
                <p>No hay membresías por vencer en los próximos {{ $dias }} días</p>
            </div>
        @else
            @foreach($clientesPorVencer as $inscripcion)
            @php
                $diasRestantes = now()->diffInDays($inscripcion->fecha_vencimiento, false);
                $badgeClass = $diasRestantes <= 2 ? 'urgente' : ($diasRestantes <= 5 ? 'pronto' : 'ok');
            @endphp
            <div class="cliente-row">
                <div class="cliente-info">
                    <div class="cliente-avatar">
                        {{ strtoupper(substr($inscripcion->cliente->nombres ?? 'N', 0, 1)) }}{{ strtoupper(substr($inscripcion->cliente->apellido_paterno ?? 'A', 0, 1)) }}
                    </div>
                    <div class="cliente-data">
                        <h5>{{ $inscripcion->cliente->nombres ?? 'N/A' }} {{ $inscripcion->cliente->apellido_paterno ?? '' }}</h5>
                        <div class="membresia">
                            <i class="fas fa-id-card mr-1"></i>
                            {{ $inscripcion->membresia->nombre ?? 'Sin membresía' }}
                        </div>
                        <div class="telefono">
                            <i class="fas fa-phone mr-1"></i>
                            {{ $inscripcion->cliente->telefono ?? 'Sin teléfono' }}
                        </div>
                    </div>
                </div>
                <div class="vencimiento">
                    <div class="fecha">{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</div>
                    <span class="dias-badge {{ $badgeClass }}">
                        @if($diasRestantes == 0)
                            Vence hoy
                        @elseif($diasRestantes == 1)
                            Vence mañana
                        @else
                            {{ $diasRestantes }} días
                        @endif
                    </span>
                </div>
            </div>
            @endforeach
        @endif
    </div>
@endsection
