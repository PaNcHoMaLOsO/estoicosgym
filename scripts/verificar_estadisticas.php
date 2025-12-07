<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pago;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       ESTADÍSTICAS CON CÓDIGOS DIRECTOS (corregido)\n";
echo "═══════════════════════════════════════════════════════════════\n";

$totalRecaudado = Pago::sum('monto_abonado');
$totalPagos = Pago::count();
$completados = Pago::where('id_estado', 201)->count();
$parciales = Pago::whereIn('id_estado', [200, 202])->count();
$traspasados = Pago::where('id_estado', 205)->count();
$vencidos = Pago::where('id_estado', 203)->count();

echo "Total Pagos:     {$totalPagos}\n";
echo "Total Recaudado: \$" . number_format($totalRecaudado, 0, ',', '.') . "\n";
echo "Completados:     {$completados} (estado 201)\n";
echo "Parciales:       {$parciales} (estados 200 + 202)\n";
echo "Traspasados:     {$traspasados} (estado 205)\n";
echo "Vencidos:        {$vencidos} (estado 203)\n";
echo "═══════════════════════════════════════════════════════════════\n";

echo "\n✅ Las cards ahora deberían mostrar:\n";
echo "   - Total Pagos: {$totalPagos}\n";
echo "   - Recaudado: \$" . number_format($totalRecaudado, 0, ',', '.') . "\n";
echo "   - Completados: {$completados}\n";
echo "   - Parciales: {$parciales}\n";
