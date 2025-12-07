<?php
/**
 * Script de auditoría completa del sistema de notificaciones
 * Ejecutar con: php auditoria_notificaciones.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║      AUDITORÍA DEL SISTEMA DE NOTIFICACIONES            ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// 1. VERIFICAR MODELOS
echo "1️⃣  MODELOS\n";
echo "   ────────────────────────────────────────────────────────\n";

$modelos = [
    'Notificacion' => 'app/Models/Notificacion.php',
    'TipoNotificacion' => 'app/Models/TipoNotificacion.php',
    'LogNotificacion' => 'app/Models/LogNotificacion.php',
];

foreach ($modelos as $nombre => $ruta) {
    $existe = File::exists($ruta);
    $icono = $existe ? '✓' : '✗';
    echo "   {$icono} {$nombre}: " . ($existe ? 'OK' : 'NO EXISTE') . "\n";
    
    if ($existe) {
        $contenido = File::get($ruta);
        // Verificar fillable
        if (strpos($contenido, 'protected $fillable') !== false) {
            echo "      → fillable: ✓\n";
        }
        // Verificar relaciones
        if (strpos($contenido, 'function') !== false && strpos($contenido, 'belongsTo') !== false) {
            echo "      → Relaciones: ✓\n";
        }
        // Verificar constantes (para Notificacion)
        if ($nombre === 'Notificacion' && strpos($contenido, 'ESTADO_PENDIENTE') !== false) {
            echo "      → Constantes de estado: ✓\n";
        }
    }
}
echo "\n";

// 2. VERIFICAR CONTROLADORES
echo "2️⃣  CONTROLADORES\n";
echo "   ────────────────────────────────────────────────────────\n";

$controladores = [
    'NotificacionController' => 'app/Http/Controllers/Admin/NotificacionController.php',
];

foreach ($controladores as $nombre => $ruta) {
    $existe = File::exists($ruta);
    $icono = $existe ? '✓' : '✗';
    echo "   {$icono} {$nombre}: " . ($existe ? 'OK' : 'NO EXISTE') . "\n";
    
    if ($existe) {
        $contenido = File::get($ruta);
        $metodos = ['index', 'show', 'programar', 'enviar'];
        foreach ($metodos as $metodo) {
            if (strpos($contenido, "function {$metodo}") !== false) {
                echo "      → {$metodo}(): ✓\n";
            }
        }
    }
}
echo "\n";

// 3. VERIFICAR SERVICIOS
echo "3️⃣  SERVICIOS\n";
echo "   ────────────────────────────────────────────────────────\n";

$servicios = [
    'NotificacionService' => 'app/Services/NotificacionService.php',
];

foreach ($servicios as $nombre => $ruta) {
    $existe = File::exists($ruta);
    $icono = $existe ? '✓' : '✗';
    echo "   {$icono} {$nombre}: " . ($existe ? 'OK' : 'NO EXISTE') . "\n";
    
    if ($existe) {
        $contenido = File::get($ruta);
        $metodos = [
            'programarNotificacionesPorVencer',
            'programarNotificacionesVencidas',
            'enviarPendientes',
            'reintentarFallidas',
            'crearNotificacion'
        ];
        foreach ($metodos as $metodo) {
            if (strpos($contenido, "function {$metodo}") !== false) {
                echo "      → {$metodo}(): ✓\n";
            } else {
                echo "      → {$metodo}(): ✗ FALTA\n";
            }
        }
    }
}
echo "\n";

// 4. VERIFICAR COMANDOS ARTISAN
echo "4️⃣  COMANDOS ARTISAN\n";
echo "   ────────────────────────────────────────────────────────\n";

$comandos = [
    'GenerarNotificaciones' => 'app/Console/Commands/GenerarNotificaciones.php',
    'EnviarNotificaciones' => 'app/Console/Commands/EnviarNotificaciones.php',
];

foreach ($comandos as $nombre => $ruta) {
    $existe = File::exists($ruta);
    $icono = $existe ? '✓' : '✗';
    echo "   {$icono} {$nombre}: " . ($existe ? 'OK' : 'NO EXISTE') . "\n";
    
    if ($existe) {
        $contenido = File::get($ruta);
        if (strpos($contenido, 'protected $signature') !== false) {
            echo "      → Signature definida: ✓\n";
        }
        if (strpos($contenido, 'function handle') !== false) {
            echo "      → Método handle(): ✓\n";
        }
    }
}
echo "\n";

// 5. VERIFICAR VISTAS
echo "5️⃣  VISTAS\n";
echo "   ────────────────────────────────────────────────────────\n";

$vistas = [
    'index' => 'resources/views/admin/notificaciones/index.blade.php',
    'show' => 'resources/views/admin/notificaciones/show.blade.php',
    'crear' => 'resources/views/admin/notificaciones/crear.blade.php',
    'programar' => 'resources/views/admin/notificaciones/programar.blade.php',
    'historial' => 'resources/views/admin/notificaciones/historial.blade.php',
    'plantillas' => 'resources/views/admin/notificaciones/plantillas.blade.php',
    'editar-plantilla' => 'resources/views/admin/notificaciones/editar-plantilla.blade.php',
    'enviar-cliente' => 'resources/views/admin/notificaciones/enviar-cliente.blade.php',
];

foreach ($vistas as $nombre => $ruta) {
    $existe = File::exists($ruta);
    $icono = $existe ? '✓' : '✗';
    echo "   {$icono} {$nombre}: " . ($existe ? 'OK' : 'NO EXISTE') . "\n";
}
echo "\n";

// 6. VERIFICAR MIGRACIONES
echo "6️⃣  MIGRACIONES\n";
echo "   ────────────────────────────────────────────────────────\n";

$migraciones = File::glob('database/migrations/*notif*.php');
if (!empty($migraciones)) {
    foreach ($migraciones as $migracion) {
        $nombre = basename($migracion);
        echo "   ✓ {$nombre}\n";
    }
} else {
    echo "   ✗ No se encontraron migraciones de notificaciones\n";
}
echo "\n";

// 7. VERIFICAR TABLAS EN BD
echo "7️⃣  TABLAS EN BASE DE DATOS\n";
echo "   ────────────────────────────────────────────────────────\n";

$tablas = [
    'tipo_notificaciones',
    'notificaciones',
    'log_notificaciones'
];

foreach ($tablas as $tabla) {
    try {
        $existe = DB::select("SHOW TABLES LIKE '{$tabla}'");
        if (!empty($existe)) {
            $count = DB::table($tabla)->count();
            echo "   ✓ {$tabla}: {$count} registros\n";
            
            // Verificar estructura
            if ($tabla === 'notificaciones') {
                $columnas = DB::select("SHOW COLUMNS FROM {$tabla}");
                $camposClave = ['id', 'uuid', 'id_tipo_notificacion', 'id_cliente', 'id_estado', 'email_destino', 'asunto', 'contenido'];
                $faltantes = [];
                
                foreach ($camposClave as $campo) {
                    $encontrado = false;
                    foreach ($columnas as $col) {
                        if ($col->Field === $campo) {
                            $encontrado = true;
                            break;
                        }
                    }
                    if (!$encontrado) {
                        $faltantes[] = $campo;
                    }
                }
                
                if (empty($faltantes)) {
                    echo "      → Estructura completa: ✓\n";
                } else {
                    echo "      → Campos faltantes: " . implode(', ', $faltantes) . "\n";
                }
            }
        } else {
            echo "   ✗ {$tabla}: NO EXISTE\n";
        }
    } catch (\Exception $e) {
        echo "   ✗ {$tabla}: ERROR - " . $e->getMessage() . "\n";
    }
}
echo "\n";

// 8. VERIFICAR TIPOS DE NOTIFICACIÓN CONFIGURADOS
echo "8️⃣  TIPOS DE NOTIFICACIÓN EN BD\n";
echo "   ────────────────────────────────────────────────────────\n";

try {
    $tipos = DB::table('tipo_notificaciones')->get(['id', 'codigo', 'nombre', 'activo', 'dias_anticipacion']);
    
    if ($tipos->isEmpty()) {
        echo "   ✗ No hay tipos de notificación configurados\n";
    } else {
        foreach ($tipos as $tipo) {
            $estado = $tipo->activo ? '✓' : '✗';
            echo "   {$estado} [{$tipo->id}] {$tipo->codigo}\n";
            echo "      Nombre: {$tipo->nombre}\n";
            echo "      Días anticipación: {$tipo->dias_anticipacion}\n";
            echo "      Estado: " . ($tipo->activo ? 'Activo' : 'Inactivo') . "\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ Error al consultar tipos: {$e->getMessage()}\n";
}
echo "\n";

// 9. VERIFICAR ESTADOS DE NOTIFICACIONES
echo "9️⃣  ESTADOS DE NOTIFICACIONES EN BD\n";
echo "   ────────────────────────────────────────────────────────\n";

try {
    $estados = DB::table('estados')
        ->where('codigo', '>=', 600)
        ->where('codigo', '<', 700)
        ->get(['codigo', 'nombre', 'descripcion']);
    
    if ($estados->isEmpty()) {
        echo "   ✗ No hay estados de notificaciones configurados (rango 600-699)\n";
    } else {
        foreach ($estados as $estado) {
            echo "   ✓ [{$estado->codigo}] {$estado->nombre}\n";
            echo "      {$estado->descripcion}\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ Error al consultar estados: {$e->getMessage()}\n";
}
echo "\n";

// 10. VERIFICAR RUTAS
echo "🔟 RUTAS WEB\n";
echo "   ────────────────────────────────────────────────────────\n";

if (File::exists('routes/web.php')) {
    $contenido = File::get('routes/web.php');
    
    $rutasClave = [
        'notificaciones' => 'Route.*notificaciones',
        'notificaciones.index' => 'notificaciones.*index',
        'notificaciones.show' => 'notificaciones.*show',
    ];
    
    foreach ($rutasClave as $nombre => $patron) {
        if (preg_match("/{$patron}/i", $contenido)) {
            echo "   ✓ {$nombre}: Definida\n";
        } else {
            echo "   ✗ {$nombre}: NO ENCONTRADA\n";
        }
    }
} else {
    echo "   ✗ Archivo routes/web.php no existe\n";
}
echo "\n";

// 11. PROBAR CONEXIÓN DE MODELOS
echo "1️⃣1️⃣  PRUEBAS DE RELACIONES\n";
echo "   ────────────────────────────────────────────────────────\n";

try {
    // Intentar cargar una notificación con todas sus relaciones
    $notif = DB::table('notificaciones')->first();
    
    if ($notif) {
        echo "   ✓ Notificación de prueba cargada (ID: {$notif->id})\n";
        
        // Verificar relaciones
        $tipoNotif = DB::table('tipo_notificaciones')->where('id', $notif->id_tipo_notificacion)->first();
        echo "      → Relación con tipo_notificaciones: " . ($tipoNotif ? '✓' : '✗') . "\n";
        
        $cliente = DB::table('clientes')->where('id', $notif->id_cliente)->first();
        echo "      → Relación con clientes: " . ($cliente ? '✓' : '✗') . "\n";
        
        $estado = DB::table('estados')->where('codigo', $notif->id_estado)->first();
        echo "      → Relación con estados: " . ($estado ? '✓' : '✗') . "\n";
    } else {
        echo "   ⚠️  No hay notificaciones para probar relaciones\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error en prueba de relaciones: {$e->getMessage()}\n";
}
echo "\n";

// 12. RESUMEN FINAL
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                 RESUMEN DE AUDITORÍA                     ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$componentes = [
    'Modelos' => count($modelos),
    'Controladores' => count($controladores),
    'Servicios' => count($servicios),
    'Comandos' => count($comandos),
    'Vistas' => count($vistas),
    'Tablas BD' => count($tablas),
];

foreach ($componentes as $nombre => $total) {
    echo "   • {$nombre}: {$total} componente(s)\n";
}

echo "\n✅ AUDITORÍA COMPLETADA\n";
