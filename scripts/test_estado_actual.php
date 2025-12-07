<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Estado;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              📊 ESTADO ACTUAL DEL SISTEMA - ESTOICOSGYM                      ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============ RESUMEN GENERAL ============
echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 📋 RESUMEN GENERAL                                                          │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";
echo "│ Total Clientes:        " . str_pad(Cliente::count(), 5) . "                                              │\n";
echo "│ Total Inscripciones:   " . str_pad(Inscripcion::count(), 5) . "                                              │\n";
echo "│ Total Pagos:           " . str_pad(Pago::count(), 5) . "                                              │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

// ============ ESTADÍSTICAS DE PAGOS ============
$totalRecaudado = Pago::sum('monto_abonado');
$pagosCompletados = Pago::whereHas('estado', fn($q) => $q->where('codigo', 201))->count();
$pagosParciales = Pago::whereHas('estado', fn($q) => $q->whereIn('codigo', [200, 202]))->count();

echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 💰 ESTADÍSTICAS DE PAGOS                                                    │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";
echo "│ Total Recaudado:       $" . str_pad(number_format($totalRecaudado, 0, ',', '.'), 15) . "                           │\n";
echo "│ Pagos Completados:     " . str_pad($pagosCompletados, 5) . " (estado: Pagado)                              │\n";
echo "│ Pagos Parciales:       " . str_pad($pagosParciales, 5) . " (estado: Parcial/Pendiente)                   │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

// ============ ESTADOS DE INSCRIPCIONES ============
echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 📋 INSCRIPCIONES POR ESTADO                                                 │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";

$estadosInsc = Inscripcion::selectRaw('id_estado, count(*) as total')
    ->groupBy('id_estado')
    ->get();

foreach ($estadosInsc as $ei) {
    $estado = Estado::where('codigo', $ei->id_estado)->first();
    $nombre = str_pad($estado->nombre ?? "Código {$ei->id_estado}", 20);
    $total = str_pad($ei->total, 5);
    echo "│   {$nombre}: {$total}                                            │\n";
}
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

// ============ INSCRIPCIONES CON PAGOS PARCIALES ============
echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ ⚠️  INSCRIPCIONES CON PAGOS PARCIALES (saldo pendiente)                     │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";

$inscConParciales = Inscripcion::with(['cliente', 'membresia', 'pagos'])
    ->get()
    ->filter(function($insc) {
        $total = $insc->precio_final ?? $insc->precio_base;
        $pagado = $insc->pagos->sum('monto_abonado');
        return $pagado > 0 && $pagado < $total;
    });

if ($inscConParciales->isEmpty()) {
    echo "│   No hay inscripciones con pagos parciales                               │\n";
} else {
    echo "│ ID  │ Cliente                    │ Membresía      │ Pagado    │ Pendiente │\n";
    echo "├─────────────────────────────────────────────────────────────────────────────┤\n";
    foreach ($inscConParciales->take(10) as $insc) {
        $total = $insc->precio_final ?? $insc->precio_base;
        $pagado = $insc->pagos->sum('monto_abonado');
        $pendiente = $total - $pagado;
        
        $id = str_pad($insc->id, 3);
        $cliente = str_pad(mb_substr($insc->cliente->nombres . ' ' . $insc->cliente->apellido_paterno, 0, 25), 25);
        $memb = str_pad(mb_substr($insc->membresia->nombre ?? 'N/A', 0, 14), 14);
        $pagadoStr = str_pad('$' . number_format($pagado, 0, ',', '.'), 9);
        $pendienteStr = str_pad('$' . number_format($pendiente, 0, ',', '.'), 9);
        
        echo "│ {$id} │ {$cliente} │ {$memb} │ {$pagadoStr} │ {$pendienteStr} │\n";
    }
}
echo "│ Total con pagos parciales: " . str_pad($inscConParciales->count(), 3) . "                                         │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

// ============ INSCRIPCIONES COMPLETAMENTE PAGADAS ============
echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ ✅ INSCRIPCIONES COMPLETAMENTE PAGADAS                                       │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";

$inscPagadas = Inscripcion::with(['cliente', 'membresia', 'pagos', 'estado'])
    ->get()
    ->filter(function($insc) {
        $total = $insc->precio_final ?? $insc->precio_base;
        $pagado = $insc->pagos->sum('monto_abonado');
        return $pagado >= $total && $total > 0;
    });

echo "│ ID  │ Cliente                    │ Membresía      │ Total     │ Estado     │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";
foreach ($inscPagadas->take(10) as $insc) {
    $total = $insc->precio_final ?? $insc->precio_base;
    
    $id = str_pad($insc->id, 3);
    $cliente = str_pad(mb_substr($insc->cliente->nombres . ' ' . $insc->cliente->apellido_paterno, 0, 25), 25);
    $memb = str_pad(mb_substr($insc->membresia->nombre ?? 'N/A', 0, 14), 14);
    $totalStr = str_pad('$' . number_format($total, 0, ',', '.'), 9);
    $estado = str_pad(mb_substr($insc->estado->nombre ?? 'N/A', 0, 10), 10);
    
    echo "│ {$id} │ {$cliente} │ {$memb} │ {$totalStr} │ {$estado} │\n";
}
echo "│ Total completamente pagadas: " . str_pad($inscPagadas->count(), 3) . "                                       │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

// ============ INSCRIPCIONES VENCIDAS ============
echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ ⏰ INSCRIPCIONES VENCIDAS                                                    │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";

$vencidas = Inscripcion::with(['cliente', 'membresia'])
    ->where('id_estado', 102) // Vencida
    ->get();

if ($vencidas->isEmpty()) {
    echo "│   No hay inscripciones vencidas                                           │\n";
} else {
    foreach ($vencidas->take(5) as $insc) {
        $cliente = mb_substr($insc->cliente->nombres . ' ' . $insc->cliente->apellido_paterno, 0, 30);
        $memb = $insc->membresia->nombre ?? 'N/A';
        $venc = $insc->fecha_vencimiento?->format('d/m/Y') ?? 'N/A';
        echo "│   • {$cliente} - {$memb} (Venció: {$venc})\n";
    }
}
echo "│ Total vencidas: " . str_pad($vencidas->count(), 3) . "                                                    │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

// ============ INSCRIPCIONES ACTIVAS ============
echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 🟢 INSCRIPCIONES ACTIVAS                                                     │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";

$activas = Inscripcion::with(['cliente', 'membresia', 'pagos'])
    ->where('id_estado', 100) // Activa
    ->get();

echo "│ ID  │ Cliente                    │ Membresía      │ Pagado    │ Vence      │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";
foreach ($activas->take(10) as $insc) {
    $pagado = $insc->pagos->sum('monto_abonado');
    
    $id = str_pad($insc->id, 3);
    $cliente = str_pad(mb_substr($insc->cliente->nombres . ' ' . $insc->cliente->apellido_paterno, 0, 25), 25);
    $memb = str_pad(mb_substr($insc->membresia->nombre ?? 'N/A', 0, 14), 14);
    $pagadoStr = str_pad('$' . number_format($pagado, 0, ',', '.'), 9);
    $vence = str_pad($insc->fecha_vencimiento?->format('d/m/Y') ?? 'N/A', 10);
    
    echo "│ {$id} │ {$cliente} │ {$memb} │ {$pagadoStr} │ {$vence} │\n";
}
echo "│ Total activas: " . str_pad($activas->count(), 3) . "                                                     │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

// ============ CANDIDATOS PARA TEST ============
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║ 🧪 CANDIDATOS PARA PRUEBAS                                                   ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

// Inscripción activa completamente pagada (para traspaso)
$candidatoTraspasoPagado = Inscripcion::with(['cliente', 'membresia', 'pagos'])
    ->where('id_estado', 100)
    ->get()
    ->filter(function($insc) {
        $total = $insc->precio_final ?? $insc->precio_base;
        $pagado = $insc->pagos->sum('monto_abonado');
        return $pagado >= $total && $total > 0;
    })->first();

if ($candidatoTraspasoPagado) {
    $total = $candidatoTraspasoPagado->precio_final ?? $candidatoTraspasoPagado->precio_base;
    $pagado = $candidatoTraspasoPagado->pagos->sum('monto_abonado');
    echo "📌 TRASPASO (Pagado completo):\n";
    echo "   Inscripción ID: {$candidatoTraspasoPagado->id}\n";
    echo "   Cliente: {$candidatoTraspasoPagado->cliente->nombres} {$candidatoTraspasoPagado->cliente->apellido_paterno}\n";
    echo "   Membresía: {$candidatoTraspasoPagado->membresia->nombre}\n";
    echo "   Total: $" . number_format($total, 0, ',', '.') . " | Pagado: $" . number_format($pagado, 0, ',', '.') . "\n\n";
}

// Inscripción activa con pago parcial (para traspaso)
$candidatoTraspasoParcial = Inscripcion::with(['cliente', 'membresia', 'pagos'])
    ->where('id_estado', 100)
    ->get()
    ->filter(function($insc) {
        $total = $insc->precio_final ?? $insc->precio_base;
        $pagado = $insc->pagos->sum('monto_abonado');
        return $pagado > 0 && $pagado < $total;
    })->first();

if ($candidatoTraspasoParcial) {
    $total = $candidatoTraspasoParcial->precio_final ?? $candidatoTraspasoParcial->precio_base;
    $pagado = $candidatoTraspasoParcial->pagos->sum('monto_abonado');
    $pendiente = $total - $pagado;
    echo "📌 TRASPASO (Pago parcial):\n";
    echo "   Inscripción ID: {$candidatoTraspasoParcial->id}\n";
    echo "   Cliente: {$candidatoTraspasoParcial->cliente->nombres} {$candidatoTraspasoParcial->cliente->apellido_paterno}\n";
    echo "   Membresía: {$candidatoTraspasoParcial->membresia->nombre}\n";
    echo "   Total: $" . number_format($total, 0, ',', '.') . " | Pagado: $" . number_format($pagado, 0, ',', '.') . " | Pendiente: $" . number_format($pendiente, 0, ',', '.') . "\n\n";
}

// Cliente sin inscripción activa (receptor del traspaso)
$clienteSinActiva = Cliente::whereDoesntHave('inscripciones', function($q) {
    $q->where('id_estado', 100);
})->first();

if ($clienteSinActiva) {
    echo "📌 RECEPTOR TRASPASO (Sin membresía activa):\n";
    echo "   Cliente ID: {$clienteSinActiva->id}\n";
    echo "   Nombre: {$clienteSinActiva->nombres} {$clienteSinActiva->apellido_paterno}\n\n";
}

// Inscripción para mejora de plan
$candidatoMejora = Inscripcion::with(['cliente', 'membresia', 'pagos'])
    ->where('id_estado', 100)
    ->whereHas('membresia', function($q) {
        $q->where('nombre', 'like', '%Mensual%');
    })
    ->first();

if ($candidatoMejora) {
    $total = $candidatoMejora->precio_final ?? $candidatoMejora->precio_base;
    $pagado = $candidatoMejora->pagos->sum('monto_abonado');
    echo "📌 MEJORA DE PLAN:\n";
    echo "   Inscripción ID: {$candidatoMejora->id}\n";
    echo "   Cliente: {$candidatoMejora->cliente->nombres} {$candidatoMejora->cliente->apellido_paterno}\n";
    echo "   Membresía actual: {$candidatoMejora->membresia->nombre}\n";
    echo "   Total: $" . number_format($total, 0, ',', '.') . " | Pagado: $" . number_format($pagado, 0, ',', '.') . "\n\n";
}

echo "═══════════════════════════════════════════════════════════════════════════════\n";
