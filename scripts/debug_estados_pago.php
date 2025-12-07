<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pago;
use App\Models\Estado;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║           DISTRIBUCIÓN DE ESTADOS DE PAGOS                  ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";

$estados = Pago::selectRaw('id_estado, COUNT(*) as total')
    ->groupBy('id_estado')
    ->get();

$mapeoEstado = [
    201 => 'pagado',
    205 => 'traspasado', 
    203 => 'vencido',
    204 => 'cancelado',
];

foreach ($estados as $e) {
    $estado = Estado::find($e->id_estado);
    $codigo = $estado->codigo ?? 'N/A';
    $nombre = $estado->nombre ?? 'N/A';
    $estadoJs = $mapeoEstado[$codigo] ?? 'parcial';
    
    echo "║ Código: " . str_pad($codigo, 3) . " | ";
    echo str_pad($nombre, 15) . " | ";
    echo "JS: " . str_pad($estadoJs, 10) . " | ";
    echo "Total: " . str_pad($e->total, 5) . "║\n";
}

echo "╚══════════════════════════════════════════════════════════════╝\n";

// Verificar estadísticas actuales
echo "\n┌──────────────────────────────────────────────────────────────┐\n";
echo "│ ESTADÍSTICAS ACTUALES                                        │\n";
echo "├──────────────────────────────────────────────────────────────┤\n";

$totalRecaudado = Pago::sum('monto_abonado');
$totalPagos = Pago::count();
$completados = Pago::where('id_estado', Estado::where('codigo', 201)->first()->id)->count();
$parciales = Pago::whereIn('id_estado', [
    Estado::where('codigo', 200)->first()->id ?? 0,
    Estado::where('codigo', 202)->first()->id ?? 0,
])->count();

echo "│ Total Pagos:     " . str_pad($totalPagos, 40) . "│\n";
echo "│ Total Recaudado: $" . str_pad(number_format($totalRecaudado, 0, ',', '.'), 39) . "│\n";
echo "│ Completados:     " . str_pad($completados, 40) . "│\n";
echo "│ Parciales:       " . str_pad($parciales, 40) . "│\n";
echo "└──────────────────────────────────────────────────────────────┘\n";
