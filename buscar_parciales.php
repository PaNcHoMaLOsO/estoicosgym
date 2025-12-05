<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Inscripcion;

echo "┌──────────────────────────────────────────────────────────────┐\n";
echo "│ INSCRIPCIONES ACTIVAS CON PAGO PARCIAL                      │\n";
echo "├──────────────────────────────────────────────────────────────┤\n";

$parciales = Inscripcion::with(['cliente', 'membresia', 'pagos', 'estado'])
    ->where('id_estado', 100) // Activa
    ->get()
    ->filter(function($i) {
        $pagado = $i->pagos->sum('monto_abonado');
        return $pagado > 0 && $pagado < $i->total && $i->total > 0;
    })
    ->take(5);

if ($parciales->isEmpty()) {
    echo "│ No hay inscripciones con pago parcial activas              │\n";
} else {
    foreach ($parciales as $i) {
        $pagado = $i->pagos->sum('monto_abonado');
        $pendiente = $i->total - $pagado;
        echo "│ ID {$i->id}: {$i->cliente->nombre} {$i->cliente->apellido}\n";
        echo "│    Membresía: {$i->membresia->nombre}\n";
        echo "│    Pagado: \$" . number_format($pagado, 0, ',', '.') . " de \$" . number_format($i->total, 0, ',', '.') . "\n";
        echo "│    Pendiente: \$" . number_format($pendiente, 0, ',', '.') . "\n";
        echo "│\n";
    }
}
echo "└──────────────────────────────────────────────────────────────┘\n";
