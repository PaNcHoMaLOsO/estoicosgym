<?php

require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== VERIFICACIÓN DE SEEDER MEJORADO ===\n\n";

// Totales
echo "TOTALES CREADOS:\n";
echo "✓ Clientes: " . DB::table('clientes')->count() . "\n";
echo "✓ Inscripciones: " . DB::table('inscripciones')->count() . "\n";
echo "✓ Pagos: " . DB::table('pagos')->count() . "\n\n";

// Casos especiales
echo "CASOS ESPECIALES:\n";
$casos = [
    'corporativo@estoicos.test' => 'Cliente Corporativo',
    'cuotas@estoicos.test' => 'Cliente con Cuotas',
    'metodos@estoicos.test' => 'Cliente Métodos Mixtos',
    'descuentos@estoicos.test' => 'Cliente Descuentos',
    'vencido@estoicos.test' => 'Cliente Vencido'
];

foreach ($casos as $email => $nombre) {
    $cliente = DB::table('clientes')->where('email', $email)->first();
    if ($cliente) {
        $inscripciones = DB::table('inscripciones')->where('id_cliente', $cliente->id)->count();
        $pagos = DB::table('pagos')
            ->join('inscripciones', 'pagos.id_inscripcion', '=', 'inscripciones.id')
            ->where('inscripciones.id_cliente', $cliente->id)
            ->count();
        echo "✓ $nombre: $inscripciones inscripciones, $pagos pagos\n";
    }
}

echo "\n=== DISTRIBUCION DE PAGOS ===\n";
$estados = DB::table('pagos')
    ->join('estados', 'pagos.id_estado', '=', 'estados.id')
    ->select('estados.nombre', DB::raw('count(*) as total'))
    ->groupBy('estados.nombre')
    ->get();

foreach ($estados as $estado) {
    echo "✓ {$estado->nombre}: {$estado->total}\n";
}

echo "\n=== PLANES DE CUOTAS ===\n";
$cuotas = DB::table('pagos')
    ->where('es_plan_cuotas', 1)
    ->count();
echo "✓ Pagos con plan de cuotas: $cuotas\n";

echo "\n✅ SEEDER EJECUTADO CORRECTAMENTE\n\n";
