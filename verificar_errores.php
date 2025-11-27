<?php
/**
 * Script para verificar errores en toda la app
 * Busca referencias incorrectas, relaciones faltantes, helpers eliminados, etc.
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\File;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║          VERIFICACIÓN DE ERRORES EN LA APP                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$errores = [];

// 1. Buscar referencias a helpers eliminados
echo "🔍 Verificando referencias a helpers eliminados...\n";
$helperEliminados = ['EstadoHelper', 'PrecioHelper', 'RutValido'];
foreach ($helperEliminados as $helper) {
    $archivos = shell_exec("grep -r '$helper' app/ resources/ --include='*.php' --include='*.blade.php' 2>/dev/null");
    if ($archivos) {
        $errores[$helper] = explode("\n", trim($archivos));
    }
}

// 2. Buscar referencias a metodoPago incorrecto
echo "🔍 Verificando referencias a metodoPago (debe ser metodoPagoPrincipal)...\n";
$archivos = shell_exec("grep -r \"->metodoPago\" app/ --include='*.php' 2>/dev/null");
if ($archivos && $archivos !== '') {
    $errores['metodoPago incorrecto'] = explode("\n", trim($archivos));
}

// 3. Buscar referencias a id_metodo_pago (debe ser id_metodo_pago_principal)
echo "🔍 Verificando referencias a id_metodo_pago...\n";
$archivos = shell_exec("grep -r \"id_metodo_pago'\" app/ --include='*.php' 2>/dev/null | grep -v id_metodo_pago_principal");
if ($archivos && $archivos !== '') {
    $errores['id_metodo_pago'] = explode("\n", trim($archivos));
}

// 4. Buscar referencias a facades eliminadas
echo "🔍 Verificando referencias a facades eliminadas (Precio::)...\n";
$archivos = shell_exec("grep -r \"Precio::\" app/ resources/ --include='*.php' --include='*.blade.php' 2>/dev/null");
if ($archivos && $archivos !== '') {
    $errores['Precio facade'] = explode("\n", trim($archivos));
}

// 5. Buscar relaciones sin retorno
echo "🔍 Verificando relaciones vacías...\n";
$models = glob('app/Models/*.php');
foreach ($models as $modelFile) {
    $content = file_get_contents($modelFile);
    if (preg_match('/public function \w+\(\)\s*\{\s*\}/m', $content, $matches)) {
        $errores['Relación vacía: ' . basename($modelFile)] = [$matches[0]];
    }
}

// 6. Listar errores encontrados
if (empty($errores)) {
    echo "\n✅ NO SE ENCONTRARON ERRORES DETECTABLES\n";
} else {
    echo "\n\n⚠️  ERRORES ENCONTRADOS:\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    foreach ($errores as $tipo => $items) {
        echo "\n❌ $tipo:\n";
        foreach (array_slice($items, 0, 5) as $item) {
            if (trim($item)) {
                echo "   $item\n";
            }
        }
        if (count($items) > 5) {
            echo "   ... y " . (count($items) - 5) . " más\n";
        }
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n\n";
