<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte {{ ucfirst($modulo) }} - {{ now()->format('d/m/Y H:i') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: white;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #7c3aed;
            margin-bottom: 20px;
        }

        .logo h1 {
            font-size: 24px;
            font-weight: 800;
            color: #7c3aed;
        }

        .logo p {
            color: #666;
            font-size: 11px;
        }

        .fecha {
            text-align: right;
            color: #666;
        }

        .fecha strong {
            display: block;
            font-size: 14px;
            color: #333;
        }

        .titulo-reporte {
            background: linear-gradient(135deg, #7c3aed, #6366f1);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .titulo-reporte h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .titulo-reporte p {
            font-size: 11px;
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #f8fafc;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e5e7eb;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background: #fafafa;
        }

        .moneda {
            color: #10b981;
            font-weight: 600;
        }

        .fecha-dato {
            color: #64748b;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }

        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }

        .totales {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }

        .totales h4 {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .totales-grid {
            display: flex;
            gap: 30px;
        }

        .total-item strong {
            display: block;
            font-size: 16px;
            color: #10b981;
        }

        .total-item span {
            font-size: 10px;
            color: #64748b;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
        }

        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #7c3aed; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
            <i class="fas fa-print"></i> Imprimir / Guardar PDF
        </button>
    </div>

    <div class="header">
        <div class="logo">
            <h1>ESTOICOS GYM</h1>
            <p>Sistema de Gestión</p>
        </div>
        <div class="fecha">
            <strong>{{ now()->format('d/m/Y') }}</strong>
            {{ now()->format('H:i') }} hrs
        </div>
    </div>

    <div class="titulo-reporte">
        <h2>Reporte de {{ $config['nombre'] }}</h2>
        <p>{{ count($datos) }} registros encontrados</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($campos as $campo)
                <th>{{ $config['campos'][$campo]['label'] ?? $campo }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $row)
            <tr>
                @foreach($campos as $campo)
                @php
                    $valor = $row->{$campo} ?? '-';
                    $tipo = $config['campos'][$campo]['tipo'] ?? 'texto';
                @endphp
                <td>
                    @switch($tipo)
                        @case('moneda')
                            <span class="moneda">${{ number_format($valor, 0, ',', '.') }}</span>
                            @break
                        @case('fecha')
                            @if($valor && $valor != '-')
                                <span class="fecha-dato">{{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}</span>
                            @else
                                -
                            @endif
                            @break
                        @case('booleano')
                            <span class="badge {{ $valor ? 'badge-success' : 'badge-danger' }}">
                                {{ $valor ? 'Sí' : 'No' }}
                            </span>
                            @break
                        @case('estado')
                            @php
                                $estados = [
                                    100 => ['nombre' => 'Activa', 'class' => 'badge-success'],
                                    101 => ['nombre' => 'Pausada', 'class' => 'badge-warning'],
                                    102 => ['nombre' => 'Vencida', 'class' => 'badge-danger'],
                                    200 => ['nombre' => 'Pendiente', 'class' => 'badge-warning'],
                                    201 => ['nombre' => 'Pagado', 'class' => 'badge-success'],
                                    202 => ['nombre' => 'Parcial', 'class' => 'badge-info'],
                                ];
                                $estado = $estados[$valor] ?? ['nombre' => $valor, 'class' => 'badge-info'];
                            @endphp
                            <span class="badge {{ $estado['class'] }}">{{ $estado['nombre'] }}</span>
                            @break
                        @default
                            {{ $valor }}
                    @endswitch
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $totales = [];
        foreach($campos as $campo) {
            if(isset($config['campos'][$campo]) && $config['campos'][$campo]['tipo'] === 'moneda') {
                $totales[$campo] = $datos->sum($campo);
            }
        }
    @endphp

    @if(count($totales) > 0)
    <div class="totales">
        <h4>TOTALES</h4>
        <div class="totales-grid">
            @foreach($totales as $campo => $valor)
            <div class="total-item">
                <strong>${{ number_format($valor, 0, ',', '.') }}</strong>
                <span>{{ $config['campos'][$campo]['label'] ?? $campo }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Reporte generado automáticamente por Estoicos Gym - {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
