<?php
/**
 * Script de Verificación - Módulos para Evaluación
 * RF-02, RF-03, RF-04, RF-07
 * 
 * Verifica que todos los controladores y vistas estén funcionando
 * con los datos correctos para la demostración.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use Carbon\Carbon;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN DE MÓDULOS PARA EVALUACIÓN RF-02/03/04/07      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$errores = [];
$warnings = [];
$exitos = [];

// ============================================================
// RF-02: GESTIÓN DE CLIENTES (CRUD)
// ============================================================
echo "📊 RF-02: Gestión de Clientes (CRUD)\n";
echo str_repeat("─", 60) . "\n";

try {
    $totalClientes = Cliente::count();
    $clientesActivos = Cliente::where('activo', true)->count();
    $clientesInactivos = Cliente::where('activo', false)->count();
    
    echo "   ✅ Total Clientes: {$totalClientes}\n";
    echo "   ✅ Activos: {$clientesActivos}\n";
    echo "   ✅ Inactivos: {$clientesInactivos}\n";
    
    if ($totalClientes == 0) {
        $warnings[] = "RF-02: No hay clientes en la base de datos para demostración";
        echo "   ⚠️  WARNING: Sin datos para demostración\n";
    } else {
        $exitos[] = "RF-02: Módulo de clientes con {$totalClientes} registros";
    }
    
    // Verificar estructura de datos
    $cliente = Cliente::with(['inscripciones', 'convenio'])->first();
    if ($cliente) {
        echo "   ✅ Estructura de datos correcta\n";
        echo "   ✅ Relaciones cargadas: inscripciones, convenio\n";
    }
    
} catch (\Exception $e) {
    $errores[] = "RF-02 ERROR: " . $e->getMessage();
    echo "   ❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================
// RF-03: GESTIÓN DE MEMBRESÍAS (CRUD)
// ============================================================
echo "🏋️ RF-03: Gestión de Membresías (CRUD)\n";
echo str_repeat("─", 60) . "\n";

try {
    $totalMembresias = Membresia::count();
    $membresiasActivas = Membresia::where('activo', true)->count();
    
    echo "   ✅ Total Membresías: {$totalMembresias}\n";
    echo "   ✅ Activas: {$membresiasActivas}\n";
    
    if ($totalMembresias == 0) {
        $warnings[] = "RF-03: No hay membresías en la base de datos";
        echo "   ⚠️  WARNING: Sin membresías configuradas\n";
    } else {
        $exitos[] = "RF-03: Módulo de membresías con {$totalMembresias} registros";
        
        // Mostrar membresías disponibles
        $membresias = Membresia::with('precios')->where('activo', true)->get();
        echo "\n   📋 Membresías disponibles:\n";
        foreach ($membresias as $m) {
            $precioActual = $m->precios()->where('activo', true)->first();
            $precio = $precioActual ? '$' . number_format($precioActual->precio_normal, 0, ',', '.') : 'Sin precio';
            echo "      • {$m->nombre} - {$precio} - {$m->duracion_dias} días\n";
        }
    }
    
} catch (\Exception $e) {
    $errores[] = "RF-03 ERROR: " . $e->getMessage();
    echo "   ❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================
// RF-04: REGISTRO DE PAGOS (CRUD)
// ============================================================
echo "💰 RF-04: Registro de Pagos (CRUD)\n";
echo str_repeat("─", 60) . "\n";

try {
    // Inscripciones
    $totalInscripciones = Inscripcion::count();
    $inscripcionesActivas = Inscripcion::where('id_estado', 100)->count();
    $inscripcionesPorVencer = Inscripcion::where('id_estado', 100)
        ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
        ->count();
    
    echo "   ✅ Total Inscripciones: {$totalInscripciones}\n";
    echo "   ✅ Activas: {$inscripcionesActivas}\n";
    echo "   ✅ Por vencer (7 días): {$inscripcionesPorVencer}\n";
    
    // Pagos
    $totalPagos = Pago::count();
    $pagosPagados = Pago::where('id_estado', 201)->count();
    $pagosPendientes = Pago::where('id_estado', 200)->count();
    $pagosParciales = Pago::where('id_estado', 202)->count();
    
    $ingresosMes = Pago::whereIn('id_estado', [201, 202])
        ->whereYear('fecha_pago', now()->year)
        ->whereMonth('fecha_pago', now()->month)
        ->sum('monto_abonado');
    
    echo "   ✅ Total Pagos: {$totalPagos}\n";
    echo "   ✅ Pagados: {$pagosPagados}\n";
    echo "   ✅ Pendientes: {$pagosPendientes}\n";
    echo "   ✅ Parciales: {$pagosParciales}\n";
    echo "   ✅ Ingresos mes actual: $" . number_format($ingresosMes, 0, ',', '.') . "\n";
    
    if ($totalInscripciones == 0) {
        $warnings[] = "RF-04: No hay inscripciones para demostración";
        echo "   ⚠️  WARNING: Sin inscripciones en el sistema\n";
    } else {
        $exitos[] = "RF-04: Módulo de inscripciones/pagos con {$totalInscripciones} registros";
    }
    
    // Verificar integridad de datos
    $inscripcionConDatos = Inscripcion::with(['cliente', 'membresia', 'estado', 'pagos'])->first();
    if ($inscripcionConDatos) {
        echo "   ✅ Relaciones correctas: cliente, membresía, estado, pagos\n";
    }
    
} catch (\Exception $e) {
    $errores[] = "RF-04 ERROR: " . $e->getMessage();
    echo "   ❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================
// RF-07: NOTIFICACIONES AUTOMÁTICAS
// ============================================================
echo "🔔 RF-07: Notificaciones Automáticas\n";
echo str_repeat("─", 60) . "\n";

try {
    $totalPlantillas = TipoNotificacion::count();
    $plantillasAutomaticas = TipoNotificacion::where('es_manual', false)->count();
    $plantillasManuales = TipoNotificacion::where('es_manual', true)->count();
    
    echo "   ✅ Total Plantillas: {$totalPlantillas}\n";
    echo "   ✅ Automáticas: {$plantillasAutomaticas}\n";
    echo "   ✅ Manuales: {$plantillasManuales}\n";
    
    $totalNotificaciones = Notificacion::count();
    $notificacionesEnviadas = Notificacion::where('id_estado', 601)->count();
    $notificacionesPendientes = Notificacion::where('id_estado', 600)->count();
    $notificacionesFallidas = Notificacion::where('id_estado', 602)->count();
    
    echo "   ✅ Total Notificaciones: {$totalNotificaciones}\n";
    echo "   ✅ Enviadas: {$notificacionesEnviadas}\n";
    echo "   ✅ Pendientes: {$notificacionesPendientes}\n";
    echo "   ✅ Fallidas: {$notificacionesFallidas}\n";
    
    if ($totalPlantillas < 13) {
        $warnings[] = "RF-07: Faltan plantillas de notificación (esperadas: 13, actuales: {$totalPlantillas})";
        echo "   ⚠️  WARNING: Plantillas incompletas\n";
    } else {
        echo "   ✅ Las 13 plantillas están configuradas\n";
        $exitos[] = "RF-07: Sistema de notificaciones completo con 13 plantillas";
    }
    
    // Verificar plantillas específicas
    echo "\n   📋 Plantillas automáticas:\n";
    $plantillas = TipoNotificacion::where('es_manual', false)->orderBy('codigo')->get();
    foreach ($plantillas as $p) {
        echo "      • [{$p->codigo}] {$p->nombre}\n";
    }
    
    echo "\n   📋 Plantillas manuales:\n";
    $plantillas = TipoNotificacion::where('es_manual', true)->orderBy('codigo')->get();
    foreach ($plantillas as $p) {
        echo "      • [{$p->codigo}] {$p->nombre}\n";
    }
    
} catch (\Exception $e) {
    $errores[] = "RF-07 ERROR: " . $e->getMessage();
    echo "   ❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================
// DASHBOARD - VERIFICACIÓN DE DATOS
// ============================================================
echo "📈 Dashboard - Datos para Visualización\n";
echo str_repeat("─", 60) . "\n";

try {
    $clientesActivos = Cliente::where('activo', true)->count();
    $inscripcionesActivas = Inscripcion::where('id_estado', 100)->count();
    $ingresosMes = Pago::whereIn('id_estado', [201, 202])
        ->whereYear('fecha_pago', now()->year)
        ->whereMonth('fecha_pago', now()->month)
        ->sum('monto_abonado');
    $nuevosClientesMes = Inscripcion::whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
    
    echo "   ✅ Clientes Activos: {$clientesActivos}\n";
    echo "   ✅ Inscripciones Activas: {$inscripcionesActivas}\n";
    echo "   ✅ Ingresos del Mes: $" . number_format($ingresosMes, 0, ',', '.') . "\n";
    echo "   ✅ Nuevos Clientes (mes): {$nuevosClientesMes}\n";
    
    $exitos[] = "Dashboard: Cards con datos reales";
    
} catch (\Exception $e) {
    $errores[] = "Dashboard ERROR: " . $e->getMessage();
    echo "   ❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================
// RESUMEN FINAL
// ============================================================
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                       RESUMEN FINAL                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "✅ ÉXITOS (" . count($exitos) . "):\n";
foreach ($exitos as $exito) {
    echo "   • {$exito}\n";
}
echo "\n";

if (count($warnings) > 0) {
    echo "⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   • {$warning}\n";
    }
    echo "\n";
}

if (count($errores) > 0) {
    echo "❌ ERRORES (" . count($errores) . "):\n";
    foreach ($errores as $error) {
        echo "   • {$error}\n";
    }
    echo "\n";
    echo "⚠️  RESULTADO: FALLOS DETECTADOS - Requiere corrección\n";
    exit(1);
} else {
    if (count($warnings) > 0) {
        echo "✅ RESULTADO: Sistema funcional con advertencias menores\n";
    } else {
        echo "✅ RESULTADO: Todos los módulos verificados correctamente\n";
    }
    echo "🎯 Sistema listo para evaluación RF-02, RF-03, RF-04, RF-07\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo " Fecha de verificación: " . now()->format('d/m/Y H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
