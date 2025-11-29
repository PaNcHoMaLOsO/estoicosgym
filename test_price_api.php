<?php
/**
 * Script de prueba para verificar que el API de precios funciona correctamente
 * Ejecutar desde la terminal: php test_price_api.php
 */

// Establecer la ruta base de la aplicación
define('LARAVEL_START', microtime(true));

// Incluir el autoload de composer
require __DIR__ . '/vendor/autoload.php';

// Crear la aplicación
$app = require_once __DIR__ . '/bootstrap/app.php';

// Resolver el kernel HTTP
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Crear una solicitud GET simulada
$request = \Illuminate\Http\Request::create('/api/precio-membresia/1', 'GET');

// Procesar la solicitud
try {
    $response = $kernel->handle($request);
    
    echo "=== TEST API PRECIOS ===\n";
    echo "Status: " . $response->status() . "\n";
    echo "Content:\n";
    echo $response->getContent() . "\n";
    echo "======================\n";
    
    // Terminar el kernel
    $kernel->terminate($request, $response);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
