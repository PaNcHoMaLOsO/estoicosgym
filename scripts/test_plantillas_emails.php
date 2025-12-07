<?php

/**
 * TEST DE PLANTILLAS DE EMAIL
 * Verifica que las variables se reemplacen correctamente en cada plantilla
 * NO registra datos en la base de datos
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Facade;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Facade::setFacadeApplication($app);

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          TEST DE PLANTILLAS DE EMAIL - PROGYM                ║\n";
echo "║       Verificación de variables sin registrar en DB          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Datos de prueba
$datosPrueba = [
    // Datos del cliente titular
    'nombre_cliente' => 'Juan Pérez',
    'nombre' => 'Juan Pérez',
    'email_cliente' => 'juan.perez@gmail.com',
    'run_cliente' => '12.345.678-9',
    
    // Datos del apoderado (para menores)
    'nombre_apoderado' => 'María González',
    'email_apoderado' => 'maria.gonzalez@gmail.com',
    'run_apoderado' => '11.222.333-4',
    
    // Datos del menor
    'nombre_menor' => 'Juanito Pérez',
    'run_menor' => '25.555.666-7',
    'fecha_nacimiento_menor' => '15/03/2010',
    
    // Datos de membresía
    'nombre_membresia' => 'Trimestral',
    'membresia' => 'Trimestral',
    'precio_membresia' => '$65.000',
    'precio_total' => '$65.000',
    'fecha_inicio' => '06/12/2025',
    'fecha_vencimiento' => '06/03/2026',
    'fecha_registro' => '06/12/2025 14:30',
    
    // Datos de pago
    'tipo_pago' => 'Parcial',
    'monto_pagado' => '$40.000',
    'monto_pendiente' => '$25.000',
    'monto_total' => '$65.000',
    'metodo_pago' => 'Transferencia',
    'fecha_pago' => '06/12/2025',
    
    // Datos de vencimiento
    'dias_restantes' => '5',
    
    // Datos de pausa
    'fecha_pausa' => '06/12/2025',
    'motivo_pausa' => 'Viaje por trabajo',
    'fecha_reactivacion' => '15/01/2026',
    
    // Datos de activación
    'fecha_activacion' => '06/12/2025',
];

// Plantillas a probar
$plantillas = [
    [
        'numero' => 1,
        'nombre' => 'Bienvenida',
        'archivo' => '01_bienvenida.html',
        'variables' => ['nombre', 'nombre_membresia', 'precio_membresia', 'fecha_inicio', 'fecha_vencimiento', 'tipo_pago', 'monto_pagado', 'monto_pendiente'],
    ],
    [
        'numero' => 2,
        'nombre' => 'Pago Completado',
        'archivo' => '05_pago_completado.html',
        'variables' => ['nombre', 'nombre_membresia', 'monto_total', 'metodo_pago', 'fecha_pago', 'fecha_vencimiento'],
    ],
    [
        'numero' => 3,
        'nombre' => 'Membresía por Vencer',
        'archivo' => '06_membresia_por_vencer.html',
        'variables' => ['nombre_cliente', 'dias_restantes', 'fecha_vencimiento'],
    ],
    [
        'numero' => 4,
        'nombre' => 'Membresía Vencida',
        'archivo' => '07_membresia_vencida.html',
        'variables' => ['nombre_cliente', 'fecha_vencimiento'],
    ],
    [
        'numero' => 5,
        'nombre' => 'Pausa Inscripción',
        'archivo' => '09_pausa_inscripcion.html',
        'variables' => ['nombre', 'fecha_pausa', 'motivo_pausa', 'fecha_reactivacion'],
    ],
    [
        'numero' => 6,
        'nombre' => 'Activación Inscripción',
        'archivo' => '10_activacion_inscripcion.html',
        'variables' => ['nombre', 'fecha_activacion', 'nombre_membresia', 'fecha_vencimiento'],
    ],
    [
        'numero' => 7,
        'nombre' => 'Pago Pendiente',
        'archivo' => '11_pago_pendiente.html',
        'variables' => ['nombre', 'monto_pendiente', 'monto_total', 'fecha_vencimiento'],
    ],
    [
        'numero' => 8,
        'nombre' => 'Renovación',
        'archivo' => '12_renovacion.html',
        'variables' => ['nombre', 'membresia', 'fecha_inicio', 'fecha_vencimiento'],
    ],
    [
        'numero' => 9,
        'nombre' => 'Confirmación Tutor Legal',
        'archivo' => '13_confirmacion_tutor_legal.html',
        'variables' => ['nombre_apoderado', 'run_apoderado', 'nombre_menor', 'run_menor', 'fecha_nacimiento_menor', 'membresia', 'fecha_inicio', 'fecha_vencimiento', 'precio_total', 'fecha_registro'],
    ],
];

$totalPruebas = 0;
$pruebasExitosas = 0;
$pruebasFallidas = 0;

foreach ($plantillas as $plantilla) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📧 PLANTILLA {$plantilla['numero']}: {$plantilla['nombre']}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $rutaArchivo = storage_path("app/test_emails/{$plantilla['archivo']}");
    
    // Verificar que el archivo existe
    if (!file_exists($rutaArchivo)) {
        echo "❌ ERROR: Archivo no encontrado: {$plantilla['archivo']}\n\n";
        $pruebasFallidas++;
        continue;
    }
    
    echo "✅ Archivo: {$plantilla['archivo']}\n";
    echo "📏 Tamaño: " . number_format(filesize($rutaArchivo)) . " bytes\n";
    
    // Cargar contenido
    $contenido = file_get_contents($rutaArchivo);
    
    // Reemplazar variables
    $contenidoProcesado = $contenido;
    foreach ($datosPrueba as $variable => $valor) {
        $contenidoProcesado = str_replace("{{$variable}}", $valor, $contenidoProcesado);
    }
    
    // Verificar que se reemplazaron las variables esperadas
    echo "\n🔍 Verificando variables:\n";
    $variablesNoReemplazadas = [];
    $variablesReemplazadas = 0;
    
    foreach ($plantilla['variables'] as $variable) {
        $marcador = "{{$variable}}";
        if (strpos($contenido, $marcador) !== false) {
            if (strpos($contenidoProcesado, $marcador) === false) {
                echo "   ✅ {$marcador} → {$datosPrueba[$variable]}\n";
                $variablesReemplazadas++;
                $totalPruebas++;
                $pruebasExitosas++;
            } else {
                echo "   ❌ {$marcador} NO fue reemplazada\n";
                $variablesNoReemplazadas[] = $variable;
                $totalPruebas++;
                $pruebasFallidas++;
            }
        } else {
            echo "   ⚠️  {$marcador} no existe en la plantilla\n";
            $totalPruebas++;
            $pruebasFallidas++;
        }
    }
    
    // Buscar variables que quedaron sin reemplazar
    preg_match_all('/\{([a-z_]+)\}/', $contenidoProcesado, $matches);
    $variablesSinReemplazar = array_unique($matches[1]);
    
    if (!empty($variablesSinReemplazar)) {
        echo "\n⚠️  Variables sin reemplazar detectadas:\n";
        foreach ($variablesSinReemplazar as $var) {
            echo "   - {{$var}}\n";
        }
    }
    
    // Resumen de la plantilla
    echo "\n📊 Resumen:\n";
    echo "   • Variables esperadas: " . count($plantilla['variables']) . "\n";
    echo "   • Variables reemplazadas: {$variablesReemplazadas}\n";
    
    if (empty($variablesNoReemplazadas) && empty($variablesSinReemplazar)) {
        echo "   • Estado: ✅ TODAS LAS VARIABLES OK\n";
    } else {
        echo "   • Estado: ⚠️  HAY PROBLEMAS\n";
    }
    
    echo "\n";
}

// Resumen final
echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                     RESUMEN FINAL                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "📊 Estadísticas:\n";
echo "   • Total de plantillas probadas: " . count($plantillas) . "\n";
echo "   • Total de verificaciones: {$totalPruebas}\n";
echo "   • Exitosas: {$pruebasExitosas} ✅\n";
echo "   • Fallidas: {$pruebasFallidas} ❌\n";

$porcentajeExito = $totalPruebas > 0 ? round(($pruebasExitosas / $totalPruebas) * 100, 1) : 0;
echo "   • Tasa de éxito: {$porcentajeExito}%\n";

if ($pruebasFallidas === 0) {
    echo "\n✅ ¡TODAS LAS PLANTILLAS FUNCIONAN CORRECTAMENTE!\n";
} else {
    echo "\n⚠️  Hay {$pruebasFallidas} verificaciones que requieren atención\n";
}

echo "\n";
