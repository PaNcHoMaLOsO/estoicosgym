<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pago;
use App\Models\Inscripcion;

$stats = Pago::selectRaw('
    COUNT(*) as total_pagos,
    SUM(monto_abonado) as total_recaudado,
    SUM(CASE WHEN id_estado = 201 THEN 1 ELSE 0 END) as completados,
    SUM(CASE WHEN id_estado = 205 THEN 1 ELSE 0 END) as traspasados
')->first();

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║      ESTADO ACTUAL (después del 1er traspaso)                ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Pagos:       " . str_pad($stats->total_pagos, 40) . "║\n";
echo "║ Total Recaudado:   $" . str_pad(number_format($stats->total_recaudado, 0, ',', '.'), 39) . "║\n";
echo "║ Completados (201): " . str_pad($stats->completados, 40) . "║\n";
echo "║ Traspasados (205): " . str_pad($stats->traspasados, 40) . "║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";

// Verificar inscripción 40
echo "\n┌──────────────────────────────────────────────────────────────┐\n";
echo "│ INSCRIPCIÓN 40 (para próximo traspaso parcial)              │\n";
echo "├──────────────────────────────────────────────────────────────┤\n";

$insc = Inscripcion::with(['cliente', 'membresia', 'pagos', 'estado'])->find(40);
if ($insc) {
    $pagado = $insc->pagos->sum('monto_abonado');
    echo "│ Cliente:    " . str_pad($insc->cliente->nombre . ' ' . $insc->cliente->apellido, 48) . "│\n";
    echo "│ Membresía:  " . str_pad($insc->membresia->nombre, 48) . "│\n";
    echo "│ Total:      $" . str_pad(number_format($insc->total, 0, ',', '.'), 47) . "│\n";
    echo "│ Pagado:     $" . str_pad(number_format($pagado, 0, ',', '.'), 47) . "│\n";
    echo "│ Pendiente:  $" . str_pad(number_format($insc->total - $pagado, 0, ',', '.'), 47) . "│\n";
    echo "│ Estado:     " . str_pad($insc->estado->nombre, 48) . "│\n";
} else {
    echo "│ ⚠️  Inscripción 40 no encontrada o ya traspasada           │\n";
    
    // Buscar otra inscripción con pago parcial
    echo "├──────────────────────────────────────────────────────────────┤\n";
    echo "│ Buscando otra inscripción con pago parcial...               │\n";
    echo "├──────────────────────────────────────────────────────────────┤\n";
    
    $parciales = Inscripcion::with(['cliente', 'membresia', 'pagos', 'estado'])
        ->where('id_estado', 100) // Activa
        ->get()
        ->filter(function($i) {
            $pagado = $i->pagos->sum('monto_abonado');
            return $pagado > 0 && $pagado < $i->total;
        })
        ->take(3);
    
    foreach ($parciales as $p) {
        $pagado = $p->pagos->sum('monto_abonado');
        echo "│ ID {$p->id}: {$p->cliente->nombre} - \${$pagado} de \${$p->total}" . str_repeat(' ', 20) . "│\n";
    }
}
echo "└──────────────────────────────────────────────────────────────┘\n";

// Comparación
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "📊 COMPARACIÓN:\n";
echo "   ANTES del 1er traspaso:  $10.619.658 (127 pagos)\n";
echo "   AHORA:                   \$" . number_format($stats->total_recaudado, 0, ',', '.') . " ({$stats->total_pagos} pagos)\n";
$diferencia = $stats->total_recaudado - 10619658;
echo "   Diferencia:              \$" . number_format($diferencia, 0, ',', '.') . " (+" . ($stats->total_pagos - 127) . " pagos)\n";
echo "═══════════════════════════════════════════════════════════════\n";
