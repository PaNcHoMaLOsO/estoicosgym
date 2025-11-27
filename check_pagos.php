<?php
// Script para verificar pagos sin método asignado

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pago;

$pagossinmetodo = Pago::whereNull('id_metodo_pago_principal')->count();
$pagosTotales = Pago::count();

echo "Total de pagos: $pagosTotales\n";
echo "Pagos sin método asignado: $pagossinmetodo\n";

if ($pagossinmetodo > 0) {
    echo "\nPrimeros 5 pagos sin método:\n";
    Pago::whereNull('id_metodo_pago_principal')
        ->with('inscripcion.cliente')
        ->limit(5)
        ->get()
        ->each(function ($pago) {
            echo "ID: {$pago->id}, Inscripción: {$pago->id_inscripcion}\n";
        });
}
