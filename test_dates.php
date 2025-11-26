<?php
// Test script para verificar cálculos de fecha con Pase Diario

// Escenario 1: Pase Diario inicio 26 nov = vence 26 nov
$fechaInicio = new DateTime('2025-11-26');
$duracionMeses = 0;
$duracionDias = 1;

$fechaVencimiento = clone $fechaInicio;

if ($duracionMeses === 0) {
    // Para Pase Diario: el mismo día (duracionDias - 1 = 0 días de diferencia)
    $fechaVencimiento->modify('+' . ($duracionDias - 1) . ' days');
} else {
    // Para membresías de meses
    $fechaVencimiento->modify('+' . $duracionMeses . ' months');
    $fechaVencimiento->modify('-1 day');
}

echo "=== PASE DIARIO ===\n";
echo "Fecha Inicio: " . $fechaInicio->format('Y-m-d (l)') . "\n";
echo "Duración: $duracionMeses meses, $duracionDias días\n";
echo "Fecha Vencimiento: " . $fechaVencimiento->format('Y-m-d (l)') . "\n";
echo "\n";

// Escenario 2: Mensual inicio 26 nov = vence 25 dic
$fechaInicio2 = new DateTime('2025-11-26');
$duracionMeses2 = 1;
$duracionDias2 = 0;

$fechaVencimiento2 = clone $fechaInicio2;
$fechaVencimiento2->modify('+' . $duracionMeses2 . ' months');
$fechaVencimiento2->modify('-1 day');

echo "=== MENSUAL ===\n";
echo "Fecha Inicio: " . $fechaInicio2->format('Y-m-d (l)') . "\n";
echo "Duración: $duracionMeses2 meses, $duracionDias2 días\n";
echo "Fecha Vencimiento: " . $fechaVencimiento2->format('Y-m-d (l)') . "\n";
echo "\n";

// Escenario 3: Trimestral inicio 26 nov = vence 25 feb
$fechaInicio3 = new DateTime('2025-11-26');
$duracionMeses3 = 3;
$duracionDias3 = 0;

$fechaVencimiento3 = clone $fechaInicio3;
$fechaVencimiento3->modify('+' . $duracionMeses3 . ' months');
$fechaVencimiento3->modify('-1 day');

echo "=== TRIMESTRAL ===\n";
echo "Fecha Inicio: " . $fechaInicio3->format('Y-m-d (l)') . "\n";
echo "Duración: $duracionMeses3 meses, $duracionDias3 días\n";
echo "Fecha Vencimiento: " . $fechaVencimiento3->format('Y-m-d (l)') . "\n";
