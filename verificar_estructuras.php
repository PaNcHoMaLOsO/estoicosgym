<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ESTRUCTURA TABLA CLIENTES ===\n";
$clientes = DB::select("DESCRIBE clientes");
foreach ($clientes as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== ESTRUCTURA TABLA MEMBRESIAS ===\n";
$membresias = DB::select("DESCRIBE membresias");
foreach ($membresias as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== ESTRUCTURA TABLA CONVENIOS ===\n";
$convenios = DB::select("DESCRIBE convenios");
foreach ($convenios as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== ESTRUCTURA TABLA METODOS_PAGO ===\n";
$metodos = DB::select("DESCRIBE metodos_pago");
foreach ($metodos as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== ESTRUCTURA TABLA PRECIOS_MEMBRESIAS ===\n";
$precios = DB::select("DESCRIBE precios_membresias");
foreach ($precios as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== ESTRUCTURA TABLA PAGOS ===\n";
$pagos = DB::select("DESCRIBE pagos");
foreach ($pagos as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== MUESTRA DE DATOS ===\n";
echo "Clientes: " . DB::table('clientes')->count() . "\n";
echo "Membresías: " . DB::table('membresias')->count() . "\n";
echo "Convenios: " . DB::table('convenios')->count() . "\n";
echo "Métodos pago: " . DB::table('metodos_pago')->count() . "\n";
echo "Precios membresías: " . DB::table('precios_membresias')->count() . "\n";
