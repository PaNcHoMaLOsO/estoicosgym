<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pago;
use App\Models\Estado;

echo "═══════════════════════════════════════════════════════════════\n";
echo "                    ESTADOS EN LA BD\n";
echo "═══════════════════════════════════════════════════════════════\n";

$todosEstados = Estado::all();
foreach ($todosEstados as $e) {
    echo "ID: {$e->id} | Código: {$e->codigo} | Nombre: {$e->nombre} | Cat: {$e->categoria}\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "                    PAGOS POR ESTADO\n";  
echo "═══════════════════════════════════════════════════════════════\n";

$pagosPorEstado = Pago::selectRaw('id_estado, COUNT(*) as total')
    ->groupBy('id_estado')
    ->get();

foreach ($pagosPorEstado as $p) {
    $estado = Estado::find($p->id_estado);
    $nombreEstado = $estado ? $estado->nombre : 'NO EXISTE';
    $codigoEstado = $estado ? $estado->codigo : 'N/A';
    echo "Estado ID: {$p->id_estado} (código: {$codigoEstado}) | {$nombreEstado} | Total: {$p->total}\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "                    VERIFICACIÓN DE CÓDIGOS\n";
echo "═══════════════════════════════════════════════════════════════\n";

// Los estados de pago
$estadosPago = Estado::where('categoria', 'pago')->get();
foreach ($estadosPago as $e) {
    echo "Pago Estado: ID={$e->id}, código={$e->codigo}, nombre={$e->nombre}\n";
}
