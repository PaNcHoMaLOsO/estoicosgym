<?php
/**
 * Script de validación del módulo de notificaciones
 * Ejecutar con: php validar_notificaciones.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     VALIDACIÓN DEL MÓDULO DE NOTIFICACIONES              ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// 1. Verificar clientes de prueba
echo "1️⃣  CLIENTES DE PRUEBA\n";
echo "   ────────────────────────────────────────────────────────\n";
$clientesTest = DB::table('clientes')->where('email', 'like', '%@test.com')->count();
echo "   ✓ Clientes de prueba: {$clientesTest}\n\n";

// 2. Verificar inscripciones por estado
echo "2️⃣  INSCRIPCIONES POR ESTADO\n";
echo "   ────────────────────────────────────────────────────────\n";
$estados = [
    100 => 'Activa',
    101 => 'Pausada',
    102 => 'Vencida',
    104 => 'Suspendida'
];

foreach ($estados as $codigo => $nombre) {
    $count = DB::table('inscripciones')
        ->whereIn('id_cliente', DB::table('clientes')->where('email', 'like', '%@test.com')->pluck('id'))
        ->where('id_estado', $codigo)
        ->count();
    echo "   ✓ Estado {$codigo} ({$nombre}): {$count}\n";
}
echo "\n";

// 3. Verificar pagos por estado
echo "3️⃣  PAGOS POR ESTADO\n";
echo "   ────────────────────────────────────────────────────────\n";
$estadosPago = [
    200 => 'Pendiente',
    201 => 'Pagado',
    202 => 'Parcial',
    203 => 'Vencido'
];

foreach ($estadosPago as $codigo => $nombre) {
    $count = DB::table('pagos')
        ->whereIn('id_cliente', DB::table('clientes')->where('email', 'like', '%@test.com')->pluck('id'))
        ->where('id_estado', $codigo)
        ->count();
    echo "   ✓ Estado {$codigo} ({$nombre}): {$count}\n";
}
echo "\n";

// 4. Verificar notificaciones generadas
echo "4️⃣  NOTIFICACIONES GENERADAS\n";
echo "   ────────────────────────────────────────────────────────\n";
$notifTotal = DB::table('notificaciones')->count();
$notifEnviadas = DB::table('notificaciones')->where('id_estado', 601)->count();
$notifPendientes = DB::table('notificaciones')->where('id_estado', 600)->count();
$notifFallidas = DB::table('notificaciones')->where('id_estado', 602)->count();
$notifCanceladas = DB::table('notificaciones')->where('id_estado', 603)->count();

echo "   ✓ Total notificaciones: {$notifTotal}\n";
echo "   ✓ Enviadas (601): {$notifEnviadas}\n";
echo "   ✓ Pendientes (600): {$notifPendientes}\n";
echo "   ✓ Fallidas (602): {$notifFallidas}\n";
echo "   ✓ Canceladas (603): {$notifCanceladas}\n\n";

// 5. Detalle de notificaciones por tipo
echo "5️⃣  NOTIFICACIONES POR TIPO\n";
echo "   ────────────────────────────────────────────────────────\n";
$notifPorTipo = DB::table('notificaciones')
    ->join('tipo_notificaciones', 'notificaciones.id_tipo_notificacion', '=', 'tipo_notificaciones.id')
    ->select('tipo_notificaciones.nombre', DB::raw('count(*) as total'))
    ->groupBy('tipo_notificaciones.nombre')
    ->get();

foreach ($notifPorTipo as $tipo) {
    echo "   ✓ {$tipo->nombre}: {$tipo->total}\n";
}
echo "\n";

// 6. Verificar tipos de notificación configurados
echo "6️⃣  TIPOS DE NOTIFICACIÓN CONFIGURADOS\n";
echo "   ────────────────────────────────────────────────────────\n";
$tiposNotif = DB::table('tipo_notificaciones')->where('activo', true)->get(['codigo', 'nombre']);
foreach ($tiposNotif as $tipo) {
    echo "   ✓ {$tipo->codigo}: {$tipo->nombre}\n";
}
echo "\n";

// 7. Verificar errores en notificaciones
echo "7️⃣  ERRORES EN NOTIFICACIONES\n";
echo "   ────────────────────────────────────────────────────────\n";
$errores = DB::table('notificaciones')
    ->where('id_estado', 602)
    ->whereNotNull('error_mensaje')
    ->limit(3)
    ->get(['id', 'email_destino', 'error_mensaje', 'intentos']);

if ($errores->isEmpty()) {
    echo "   ✓ No hay errores registrados\n";
} else {
    foreach ($errores as $error) {
        echo "   ✗ ID {$error->id} - Email: {$error->email_destino}\n";
        echo "     Intentos: {$error->intentos}\n";
        $errorMsg = substr($error->error_mensaje, 0, 150);
        echo "     Error: {$errorMsg}\n\n";
    }
}
echo "\n";

// 8. Verificar fechas de vencimiento próximas
echo "8️⃣  FECHAS DE VENCIMIENTO (Próximos 7 días)\n";
echo "   ────────────────────────────────────────────────────────\n";
$hoy = date('Y-m-d');
$proximos7 = date('Y-m-d', strtotime('+7 days'));

$proximasVencer = DB::table('inscripciones')
    ->join('clientes', 'inscripciones.id_cliente', '=', 'clientes.id')
    ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
    ->where('clientes.email', 'like', '%@test.com')
    ->where('inscripciones.id_estado', 100)
    ->whereBetween('inscripciones.fecha_vencimiento', [$hoy, $proximos7])
    ->select('clientes.nombres', 'clientes.apellido_paterno', 'membresias.nombre as membresia', 'inscripciones.fecha_vencimiento')
    ->orderBy('inscripciones.fecha_vencimiento')
    ->get();

if ($proximasVencer->isEmpty()) {
    echo "   ✓ No hay membresías próximas a vencer\n";
} else {
    foreach ($proximasVencer as $insc) {
        $dias = floor((strtotime($insc->fecha_vencimiento) - strtotime($hoy)) / 86400);
        echo "   ✓ {$insc->nombres} {$insc->apellido_paterno} - {$insc->membresia}\n";
        echo "     Vence: {$insc->fecha_vencimiento} (en {$dias} días)\n";
    }
}
echo "\n";

// 9. Verificar membresías vencidas
echo "9️⃣  MEMBRESÍAS VENCIDAS\n";
echo "   ────────────────────────────────────────────────────────\n";
$vencidas = DB::table('inscripciones')
    ->join('clientes', 'inscripciones.id_cliente', '=', 'clientes.id')
    ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
    ->where('clientes.email', 'like', '%@test.com')
    ->whereIn('inscripciones.id_estado', [100, 102])
    ->where('inscripciones.fecha_vencimiento', '<', $hoy)
    ->select('clientes.nombres', 'clientes.apellido_paterno', 'membresias.nombre as membresia', 'inscripciones.fecha_vencimiento', 'inscripciones.id_estado')
    ->orderBy('inscripciones.fecha_vencimiento', 'desc')
    ->get();

if ($vencidas->isEmpty()) {
    echo "   ✓ No hay membresías vencidas\n";
} else {
    foreach ($vencidas as $venc) {
        $dias = floor((strtotime($hoy) - strtotime($venc->fecha_vencimiento)) / 86400);
        $estado = $venc->id_estado == 102 ? 'VENCIDA' : 'ACTIVA';
        echo "   ✓ {$venc->nombres} {$venc->apellido_paterno} - {$venc->membresia}\n";
        echo "     Venció: {$venc->fecha_vencimiento} (hace {$dias} días) - Estado: {$estado}\n";
    }
}
echo "\n";

// 10. Verificar pagos pendientes
echo "🔟 PAGOS PENDIENTES\n";
echo "   ────────────────────────────────────────────────────────\n";
$pagosPendientes = DB::table('pagos')
    ->join('clientes', 'pagos.id_cliente', '=', 'clientes.id')
    ->where('clientes.email', 'like', '%@test.com')
    ->whereIn('pagos.id_estado', [200, 202, 203])
    ->where('pagos.monto_pendiente', '>', 0)
    ->select('clientes.nombres', 'clientes.apellido_paterno', 'pagos.monto_total', 'pagos.monto_abonado', 'pagos.monto_pendiente', 'pagos.id_estado', 'pagos.fecha_pago')
    ->get();

if ($pagosPendientes->isEmpty()) {
    echo "   ✓ No hay pagos pendientes\n";
} else {
    foreach ($pagosPendientes as $pago) {
        $estadoPago = $pago->id_estado == 200 ? 'PENDIENTE' : ($pago->id_estado == 202 ? 'PARCIAL' : 'VENCIDO');
        echo "   ✓ {$pago->nombres} {$pago->apellido_paterno}\n";
        echo "     Total: \${$pago->monto_total} | Abonado: \${$pago->monto_abonado} | Pendiente: \${$pago->monto_pendiente}\n";
        echo "     Estado: {$estadoPago} - Fecha: {$pago->fecha_pago}\n";
    }
}
echo "\n";

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║            ✅ VALIDACIÓN COMPLETADA                      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
