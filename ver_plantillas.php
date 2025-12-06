<?php
/**
 * Ver plantillas de emails configuradas
 * Ejecutar: php ver_plantillas.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║           PLANTILLAS DE EMAIL CONFIGURADAS               ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$tipos = DB::table('tipo_notificaciones')
    ->orderBy('id')
    ->get(['id', 'codigo', 'nombre', 'asunto_email', 'plantilla_email', 'dias_anticipacion', 'activo']);

foreach ($tipos as $tipo) {
    $estado = $tipo->activo ? '✅' : '❌';
    echo "{$estado} [{$tipo->id}] {$tipo->codigo}\n";
    echo "   ────────────────────────────────────────────────────────\n";
    echo "   Nombre: {$tipo->nombre}\n";
    echo "   Asunto: {$tipo->asunto_email}\n";
    
    if ($tipo->dias_anticipacion > 0) {
        echo "   Días anticipación: {$tipo->dias_anticipacion}\n";
    }
    
    echo "\n   📧 PLANTILLA HTML:\n";
    echo "   " . str_repeat("─", 56) . "\n";
    
    // Mostrar plantilla con indentación
    $lineas = explode("\n", $tipo->plantilla_email);
    foreach ($lineas as $linea) {
        echo "   " . $linea . "\n";
    }
    
    echo "\n   📝 VARIABLES DISPONIBLES:\n";
    echo "   " . str_repeat("─", 56) . "\n";
    
    // Detectar variables en la plantilla
    preg_match_all('/\{([^}]+)\}/', $tipo->plantilla_email, $matches);
    $variables = array_unique($matches[1]);
    
    if (!empty($variables)) {
        foreach ($variables as $var) {
            echo "   • {{$var}}\n";
        }
    } else {
        echo "   (Sin variables dinámicas)\n";
    }
    
    echo "\n\n";
}

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                    UBICACIÓN                             ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "📍 Tabla: tipo_notificaciones\n";
echo "📍 Campos:\n";
echo "   • asunto_email: Asunto del correo\n";
echo "   • plantilla_email: HTML del cuerpo\n\n";

echo "📝 Para editar plantillas:\n";
echo "   1. Panel web: /admin/notificaciones/plantillas\n";
echo "   2. Base de datos: UPDATE tipo_notificaciones...\n";
echo "   3. Modelo: TipoNotificacion::find(id)->update(...)\n\n";

echo "🔧 Las plantillas se renderizan con variables usando el método:\n";
echo "   TipoNotificacion::renderizar(\$datos)\n\n";
