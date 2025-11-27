<?php
// Script para debuggear el endpoint de inscripciones
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;

echo "\n=== DEBUG ENDPOINT /api/inscripciones/search ===\n\n";

// Test 1: Ver inscripciones en DB
echo "1. Total de inscripciones en DB: " . DB::table('inscripciones')->count() . "\n";

// Test 2: Ver inscripciones con saldo pendiente
echo "2. Inscripciones cargadas:\n";
$inscripciones = Inscripcion::with(['cliente', 'estado', 'pagos'])->limit(5)->get();

foreach ($inscripciones as $i) {
    $saldo = $i->getSaldoPendiente();
    echo "   - ID: {$i->id}, Cliente: {$i->cliente->nombres}, Saldo: {$saldo}\n";
}

// Test 3: Simular búsqueda
echo "\n3. Simulando búsqueda con 'Juan':\n";
$query = 'Juan';
$resultados = Inscripcion::with(['cliente', 'estado', 'pagos'])
    ->where(function ($q) use ($query) {
        $q->whereHas('cliente', function ($clienteQuery) use ($query) {
            $clienteQuery->where('nombres', 'LIKE', "%{$query}%")
                         ->orWhere('apellido_paterno', 'LIKE', "%{$query}%")
                         ->orWhere('email', 'LIKE', "%{$query}%");
        })->orWhereHas('estado', function ($estadoQuery) use ($query) {
            $estadoQuery->where('nombre', 'LIKE', "%{$query}%");
        })->orWhere('id', $query);
    })
    ->limit(20)
    ->get();

echo "   Encontrados: " . count($resultados) . "\n";

foreach ($resultados as $ins) {
    $saldo = $ins->getSaldoPendiente();
    echo "   - #{$ins->id} - {$ins->cliente->nombres}: Saldo pendiente: {$saldo}\n";
    if ($saldo <= 0) {
        echo "     (NO se retornaría, saldo <= 0)\n";
    }
}

// Test 4: Ver formato de respuesta esperada
echo "\n4. Formato de respuesta esperada:\n";
$respuesta = $resultados
    ->filter(function ($inscripcion) {
        return $inscripcion->getSaldoPendiente() > 0;
    })
    ->values()
    ->map(function ($inscripcion) {
        $saldo = $inscripcion->getSaldoPendiente();
        return [
            'id' => $inscripcion->id,
            'text' => "#{$inscripcion->id} - {$inscripcion->cliente->nombres} {$inscripcion->cliente->apellido_paterno}",
            'nombre' => "{$inscripcion->cliente->nombres} {$inscripcion->cliente->apellido_paterno}",
            'cliente_id' => $inscripcion->id_cliente,
            'saldo' => $saldo,
            'total_a_pagar' => $inscripcion->precio_final ?? $inscripcion->precio_base,
            'total_abonado' => $inscripcion->getTotalAbonado(),
        ];
    });

echo json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n=== FIN DEBUG ===\n";
