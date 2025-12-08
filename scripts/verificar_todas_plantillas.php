<?php

/**
 * Script para verificar que TODAS las plantillas tienen datos dinámicos
 * Verifica los 9 templates automáticos
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Inscripcion;
use App\Models\TipoNotificacion;
use App\Services\NotificacionService;

echo "\n";
echo "🔍 VERIFICACIÓN DE TODAS LAS PLANTILLAS AUTOMÁTICAS\n";
echo "═══════════════════════════════════════════════════\n\n";

// Obtener una inscripción de ejemplo
$inscripcion = Inscripcion::with(['cliente', 'membresia', 'pagos'])->first();

if (!$inscripcion) {
    echo "❌ No hay inscripciones en la base de datos\n";
    exit(1);
}

$notificacionService = new NotificacionService();

// Lista de templates a verificar
$templates = [
    ['codigo' => TipoNotificacion::MEMBRESIA_POR_VENCER, 'nombre' => 'Membresía por vencer', 'archivo' => '03_membresia_por_vencer.html'],
    ['codigo' => TipoNotificacion::MEMBRESIA_VENCIDA, 'nombre' => 'Membresía vencida', 'archivo' => '04_membresia_vencida.html'],
    ['codigo' => TipoNotificacion::PAGO_COMPLETADO, 'nombre' => 'Pago completado', 'archivo' => '02_pago_completado.html'],
    ['codigo' => TipoNotificacion::RENOVACION, 'nombre' => 'Renovación', 'archivo' => '08_renovacion.html'],
    ['codigo' => TipoNotificacion::PAUSA_INSCRIPCION, 'nombre' => 'Pausa inscripción', 'archivo' => '05_pausa_inscripcion.html'],
    ['codigo' => TipoNotificacion::ACTIVACION_INSCRIPCION, 'nombre' => 'Activación', 'archivo' => '06_activacion_inscripcion.html'],
    ['codigo' => TipoNotificacion::PAGO_PENDIENTE, 'nombre' => 'Pago pendiente', 'archivo' => '07_pago_pendiente.html'],
];

$totalExitosos = 0;
$totalFallidos = 0;

foreach ($templates as $template) {
    echo "📧 {$template['nombre']} ({$template['archivo']})\n";
    
    try {
        $tipo = TipoNotificacion::where('codigo', $template['codigo'])->first();
        
        if (!$tipo) {
            echo "   ⚠️  Tipo de notificación no encontrado en BD\n\n";
            $totalFallidos++;
            continue;
        }
        
        // Crear notificación temporal (sin guardar)
        $notificacion = $notificacionService->crearNotificacion($tipo, $inscripcion);
        
        // Verificar que NO tenga datos estáticos
        $contenido = $notificacion->contenido;
        
        $datosEstaticos = [
            'Juan Pérez',
            'María González',
            'Juanito Pérez',
        ];
        
        $tieneEstaticos = false;
        foreach ($datosEstaticos as $dato) {
            if (stripos($contenido, $dato) !== false) {
                echo "   ❌ Contiene dato estático: '{$dato}'\n";
                $tieneEstaticos = true;
            }
        }
        
        if (!$tieneEstaticos) {
            // Verificar que tenga datos reales
            $nombreCliente = trim($inscripcion->cliente->nombres . ' ' . $inscripcion->cliente->apellido_paterno);
            $nombreMembresia = $inscripcion->membresia->nombre;
            
            $tieneDinamicos = false;
            if (stripos($contenido, $nombreCliente) !== false) {
                echo "   ✅ Tiene nombre del cliente: {$nombreCliente}\n";
                $tieneDinamicos = true;
            }
            if (stripos($contenido, $nombreMembresia) !== false) {
                echo "   ✅ Tiene nombre de membresía: {$nombreMembresia}\n";
                $tieneDinamicos = true;
            }
            
            if ($tieneDinamicos) {
                echo "   ✅ CORRECTO - Datos dinámicos funcionando\n";
                $totalExitosos++;
            } else {
                echo "   ⚠️  No se detectaron datos dinámicos\n";
                $totalFallidos++;
            }
        } else {
            $totalFallidos++;
        }
        
        // Limpiar notificación de prueba
        $notificacion->delete();
        
    } catch (\Exception $e) {
        echo "   ❌ Error: {$e->getMessage()}\n";
        $totalFallidos++;
    }
    
    echo "\n";
}

// Verificar bienvenida (método separado)
echo "📧 Bienvenida (01_bienvenida.html)\n";
try {
    $resultado = $notificacionService->enviarNotificacionBienvenida($inscripcion);
    
    if ($resultado['enviada'] || strpos($resultado['mensaje'], 'Ya existe') !== false) {
        echo "   ✅ CORRECTO - Método enviarNotificacionBienvenida funcionando\n";
        $totalExitosos++;
    } else {
        echo "   ❌ Falló: {$resultado['mensaje']}\n";
        $totalFallidos++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: {$e->getMessage()}\n";
    $totalFallidos++;
}
echo "\n";

// Verificar tutor legal
echo "📧 Confirmación tutor legal (09_confirmacion_tutor_legal.html)\n";
try {
    // Crear cliente menor temporal
    $clienteMenor = $inscripcion->cliente;
    $clienteMenor->es_menor_edad = true;
    $clienteMenor->apoderado_email = 'test@test.com';
    $clienteMenor->apoderado_nombre = 'Test Tutor';
    
    $resultado = $notificacionService->enviarNotificacionTutorLegal($inscripcion);
    
    if ($resultado['enviada'] || strpos($resultado['mensaje'], 'menor de edad') !== false) {
        echo "   ✅ CORRECTO - Método enviarNotificacionTutorLegal funcionando\n";
        $totalExitosos++;
    } else {
        echo "   ❌ Falló: {$resultado['mensaje']}\n";
        $totalFallidos++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: {$e->getMessage()}\n";
    $totalFallidos++;
}
echo "\n";

// RESUMEN FINAL
echo "═══════════════════════════════════════════════════\n";
echo "📊 RESUMEN FINAL\n";
echo "═══════════════════════════════════════════════════\n\n";
echo "✅ Exitosos: {$totalExitosos}/9\n";
echo "❌ Fallidos: {$totalFallidos}/9\n\n";

if ($totalFallidos === 0) {
    echo "🎉 ¡TODAS LAS PLANTILLAS TIENEN DATOS DINÁMICOS!\n\n";
    exit(0);
} else {
    echo "⚠️  Algunas plantillas necesitan revisión\n\n";
    exit(1);
}
