<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     📊 VERIFICACIÓN DE CARGA INICIAL DE BASE DE DATOS      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Usuarios
echo "👥 USUARIOS:\n";
$users = DB::table('users')->select('name', 'email')->get();
foreach ($users as $user) {
    echo "   ✓ {$user->name} ({$user->email})\n";
}
echo "\n";

// Membresías
echo "🏋️ MEMBRESÍAS:\n";
$membresias = DB::table('membresias')->select('nombre', 'duracion_dias')->get();
foreach ($membresias as $m) {
    echo "   ✓ {$m->nombre} ({$m->duracion_dias} días)\n";
}
echo "\n";

// Estados
$totalEstados = DB::table('estados')->count();
echo "🎯 ESTADOS: {$totalEstados} registros\n";
$estadosPorCategoria = DB::table('estados')
    ->selectRaw('categoria, COUNT(*) as total')
    ->groupBy('categoria')
    ->get();
foreach ($estadosPorCategoria as $cat) {
    echo "   ✓ {$cat->categoria}: {$cat->total}\n";
}
echo "\n";

// Métodos de Pago
echo "💵 MÉTODOS DE PAGO:\n";
$metodos = DB::table('metodos_pago')->select('nombre')->get();
foreach ($metodos as $m) {
    echo "   ✓ {$m->nombre}\n";
}
echo "\n";

// Convenios
$totalConvenios = DB::table('convenios')->count();
echo "🤝 CONVENIOS: {$totalConvenios} registros\n\n";

// Plantillas de Notificación
echo "📧 PLANTILLAS DE NOTIFICACIÓN:\n";
$automaticas = DB::table('tipo_notificaciones')->where('es_manual', 0)->count();
$manuales = DB::table('tipo_notificaciones')->where('es_manual', 1)->count();
echo "   ✓ Automáticas: {$automaticas}\n";
echo "   ✓ Manuales: {$manuales}\n";
echo "   ✓ Total: " . ($automaticas + $manuales) . "\n\n";

echo "📋 PLANTILLAS MANUALES:\n";
$plantillasManuales = DB::table('tipo_notificaciones')
    ->where('es_manual', 1)
    ->select('codigo', 'nombre', 'asunto_email')
    ->get();
foreach ($plantillasManuales as $p) {
    echo "   ✓ {$p->nombre}\n";
    echo "     Código: {$p->codigo}\n";
    echo "     Asunto: {$p->asunto_email}\n\n";
}

// Datos operacionales
echo "📈 DATOS OPERACIONALES:\n";
echo "   • Clientes: " . DB::table('clientes')->count() . "\n";
echo "   • Inscripciones: " . DB::table('inscripciones')->count() . "\n";
echo "   • Pagos: " . DB::table('pagos')->count() . "\n";
echo "   • Notificaciones: " . DB::table('notificaciones')->count() . "\n";
echo "\n";

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║              ✅ VERIFICACIÓN COMPLETADA                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";
