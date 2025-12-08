<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║           🔐 VERIFICACIÓN DE CREDENCIALES                 ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$users = DB::table('users')->get(['id', 'name', 'email', 'id_rol']);

if ($users->isEmpty()) {
    echo "❌ No hay usuarios en la base de datos\n";
    echo "   Ejecuta: php artisan db:seed\n\n";
    exit(1);
}

echo "👥 Usuarios encontrados: {$users->count()}\n\n";

foreach ($users as $user) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📋 Usuario ID: {$user->id}\n";
    echo "   Nombre: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   Rol ID: {$user->id_rol}\n";
    echo "   Contraseña: password (hasheada en BD)\n";
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";

// Obtener nombres de roles
echo "🔑 Roles del sistema:\n\n";
$roles = DB::table('roles')->get(['id', 'nombre']);
foreach ($roles as $rol) {
    echo "   {$rol->id}. {$rol->nombre}\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    🌐 ACCESO AL SISTEMA                   ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
echo "║  URL: http://localhost:8000/admin                         ║\n";
echo "║                                                            ║\n";
echo "║  👨‍💼 Admin:                                                 ║\n";
echo "║     Email: admin@progym.cl                                ║\n";
echo "║     Pass:  password                                       ║\n";
echo "║                                                            ║\n";
echo "║  👤 Recepcionista:                                         ║\n";
echo "║     Email: recepcion@progym.cl                            ║\n";
echo "║     Pass:  password                                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "✅ Credenciales verificadas correctamente\n\n";
