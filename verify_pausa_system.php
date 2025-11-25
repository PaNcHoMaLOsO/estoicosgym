<?php

// Cargar autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Cargar bootstrap de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== VERIFICACIÓN DEL SISTEMA DE PAUSAS ===\n\n";

// Verificar tabla inscripciones
$inscripcion = \App\Models\Inscripcion::first();
if ($inscripcion) {
    echo "✓ Tabla inscripciones accesible\n";
    echo "  Total de inscripciones: " . \App\Models\Inscripcion::count() . "\n";
    echo "  Inscripciones pausadas: " . \App\Models\Inscripcion::where('pausada', true)->count() . "\n";
} else {
    echo "✗ No hay inscripciones\n";
}

echo "\n=== ESTADOS DE MEMBRESÍA ===\n";
$estados = \App\Models\Estado::where('categoria', 'membresia')->orderBy('codigo')->get();
echo "Total: " . $estados->count() . " estados\n\n";
foreach ($estados as $e) {
    $badge = match($e->color) {
        'success' => '✓',
        'danger' => '✗',
        'warning' => '⚠',
        'info' => 'ℹ',
        'primary' => '★',
        default => '•'
    };
    echo "  $badge {$e->codigo} - {$e->nombre} ({$e->color})\n";
}

echo "\n=== ESTADOS DE PAGO ===\n";
$estados_pago = \App\Models\Estado::where('categoria', 'pago')->orderBy('codigo')->get();
echo "Total: " . $estados_pago->count() . " estados\n\n";
foreach ($estados_pago as $e) {
    $badge = match($e->color) {
        'success' => '✓',
        'danger' => '✗',
        'warning' => '⚠',
        'info' => 'ℹ',
        'primary' => '★',
        default => '•'
    };
    echo "  $badge {$e->codigo} - {$e->nombre} ({$e->color})\n";
}

echo "\n=== ESTADÍSTICAS DE TEST DATA ===\n";
echo "Clientes: " . \App\Models\Cliente::count() . "\n";
echo "Inscripciones: " . \App\Models\Inscripcion::count() . "\n";
echo "Pagos: " . \App\Models\Pago::count() . "\n";
echo "Convenios: " . \App\Models\Convenio::count() . "\n";

echo "\n✅ Verificación completada\n";
