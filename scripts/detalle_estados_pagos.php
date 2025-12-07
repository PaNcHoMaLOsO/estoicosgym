<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pago;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       DISTRIBUCIÓN DETALLADA DE ESTADOS DE PAGOS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$estados = [
    200 => ['nombre' => 'Pendiente', 'desc' => 'Registrado pero sin abono'],
    201 => ['nombre' => 'Pagado', 'desc' => 'Completamente pagado'],
    202 => ['nombre' => 'Parcial', 'desc' => 'Con abono parcial'],
    203 => ['nombre' => 'Vencido', 'desc' => 'Venció sin completarse'],
    204 => ['nombre' => 'Cancelado', 'desc' => 'Fue cancelado'],
    205 => ['nombre' => 'Traspasado', 'desc' => 'Transferido a otro cliente'],
];

foreach ($estados as $codigo => $info) {
    $count = Pago::where('id_estado', $codigo)->count();
    $monto = Pago::where('id_estado', $codigo)->sum('monto_abonado');
    
    echo "┌─────────────────────────────────────────────────────────────┐\n";
    echo "│ {$codigo} - {$info['nombre']}\n";
    echo "│ {$info['desc']}\n";
    echo "│ Cantidad: {$count} pagos\n";
    echo "│ Monto abonado: \$" . number_format($monto, 0, ',', '.') . "\n";
    echo "└─────────────────────────────────────────────────────────────┘\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "PROPUESTA DE CARDS PARA LA VISTA:\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "1. PAGADOS (201)     - Verde     - Los completados\n";
echo "2. PARCIALES (202)   - Amarillo  - Con abono pero incompletos\n";
echo "3. PENDIENTES (200)  - Naranja   - Sin ningún abono aún\n";
echo "4. VENCIDOS (203)    - Rojo      - Para seguimiento\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\nNOTA: Traspasados (205) y Cancelados (204) no se mostrarían\n";
echo "      como filtros principales pero sí en la tabla.\n";
