<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Inscripcion;
use App\Models\Cliente;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║      INSCRIPCIONES ACTIVAS DISPONIBLES PARA TRASPASO        ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";

// Inscripciones activas con pagos
$activas = Inscripcion::with(['cliente', 'membresia', 'pagos', 'estado'])
    ->where('id_estado', 100) // Activa
    ->get()
    ->take(10);

foreach ($activas as $i) {
    $pagado = $i->pagos->sum('monto_abonado');
    $cliente = $i->cliente;
    
    echo "║ ID: " . str_pad($i->id, 3) . " | ";
    echo str_pad($cliente->nombres ?? 'N/A', 15) . " | ";
    echo str_pad($i->membresia->nombre, 12) . " | ";
    echo "Pagado: $" . str_pad(number_format($pagado, 0, ',', '.'), 10) . " ║\n";
}

echo "╚══════════════════════════════════════════════════════════════╝\n";

// Clientes sin membresía activa (para recibir traspaso)
echo "\n┌──────────────────────────────────────────────────────────────┐\n";
echo "│ CLIENTES SIN MEMBRESÍA ACTIVA (pueden recibir traspaso)     │\n";
echo "├──────────────────────────────────────────────────────────────┤\n";

$clientesSinMembresia = Cliente::where('activo', true)
    ->whereDoesntHave('inscripciones', function($q) {
        $q->where('id_estado', 100); // Sin inscripción activa
    })
    ->take(5)
    ->get();

foreach ($clientesSinMembresia as $c) {
    echo "│ ID: " . str_pad($c->id, 3) . " | {$c->nombres} {$c->apellido_paterno}\n";
}
echo "└──────────────────────────────────────────────────────────────┘\n";

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "📝 PARA PROBAR:\n";
echo "   1. Ir a http://127.0.0.1:8000/admin/inscripciones\n";
echo "   2. Buscar una inscripción activa de la lista arriba\n";
echo "   3. Hacer clic en Editar y luego en 'Traspasar'\n";
echo "   4. Seleccionar un cliente de la lista de abajo\n";
echo "   5. Verificar que las estadísticas NO se dupliquen\n";
echo "═══════════════════════════════════════════════════════════════\n";
