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
echo "║           🧪 TEST: TRASPASO DE MEMBRESÍA COMPLETAMENTE PAGADA               ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============ ANTES DEL TRASPASO ============
echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 📊 ESTADÍSTICAS ANTES DEL TRASPASO                                          │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";

$totalPagosAntes = Pago::count();
$totalRecaudadoAntes = Pago::sum('monto_abonado');
$pagosCompletadosAntes = Pago::whereHas('estado', fn($q) => $q->where('codigo', 201))->count();

echo "│ Total Pagos:           " . str_pad($totalPagosAntes, 5) . "                                              │\n";
echo "│ Total Recaudado:       $" . str_pad(number_format($totalRecaudadoAntes, 0, ',', '.'), 15) . "                           │\n";
echo "│ Pagos Completados:     " . str_pad($pagosCompletadosAntes, 5) . "                                              │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

// Inscripción a traspasar (ID 20 - Jorge Vega - Pagada completa)
$inscripcion = Inscripcion::with(['cliente', 'membresia', 'pagos'])->find(20);
$clienteDestino = Cliente::find(6); // Gonzalo Sandoval

if (!$inscripcion) {
    echo "❌ No se encontró la inscripción ID 20\n";
    exit;
}

$totalInsc = $inscripcion->precio_final ?? $inscripcion->precio_base;
$pagadoInsc = $inscripcion->pagos->sum('monto_abonado');

echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 📋 INSCRIPCIÓN A TRASPASAR                                                  │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";
echo "│ ID:                    " . str_pad($inscripcion->id, 5) . "                                              │\n";
echo "│ Cliente origen:        " . str_pad($inscripcion->cliente->nombres . ' ' . $inscripcion->cliente->apellido_paterno, 30) . "             │\n";
echo "│ Cliente destino:       " . str_pad($clienteDestino->nombres . ' ' . $clienteDestino->apellido_paterno, 30) . "             │\n";
echo "│ Membresía:             " . str_pad($inscripcion->membresia->nombre, 20) . "                         │\n";
echo "│ Total:                 $" . str_pad(number_format($totalInsc, 0, ',', '.'), 15) . "                           │\n";
echo "│ Pagado:                $" . str_pad(number_format($pagadoInsc, 0, ',', '.'), 15) . "                           │\n";
echo "│ Estado actual:         " . str_pad(Estado::where('codigo', $inscripcion->id_estado)->first()->nombre ?? 'N/A', 15) . "                          │\n";
echo "│ Pagos asociados:       " . str_pad($inscripcion->pagos->count(), 5) . "                                              │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

echo "⚠️  NOTA: Este test es de solo lectura - NO ejecuta el traspaso real.\n";
echo "   Para probar, ir a la aplicación web y hacer el traspaso desde ahí.\n\n";

// ============ SIMULACIÓN DE LO QUE PASARÍA ============
echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 🔮 SIMULACIÓN: QUÉ PASARÍA DESPUÉS DEL TRASPASO                            │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";
echo "│                                                                             │\n";
echo "│ INSCRIPCIÓN ORIGINAL (ID 20):                                               │\n";
echo "│   • Estado → Traspasada (106)                                               │\n";
echo "│   • Pagos → Estado Traspasado (205), monto_abonado=monto_total              │\n";
echo "│                                                                             │\n";
echo "│ NUEVA INSCRIPCIÓN (para Gonzalo Sandoval):                                  │\n";
echo "│   • Se crea inscripción nueva con Estado Activa (100)                       │\n";
echo "│   • Se crea NUEVO PAGO con monto_abonado = $" . number_format($pagadoInsc, 0, ',', '.') . "                      │\n";
echo "│                                                                             │\n";
echo "│ IMPACTO EN ESTADÍSTICAS:                                                    │\n";
echo "│   • Total Pagos: " . str_pad($totalPagosAntes, 3) . " → " . str_pad($totalPagosAntes + 1, 3) . " (+1 nuevo pago)                               │\n";
echo "│   • Total Recaudado: $" . str_pad(number_format($totalRecaudadoAntes, 0, ',', '.'), 12) . " → $" . str_pad(number_format($totalRecaudadoAntes + $pagadoInsc, 0, ',', '.'), 12) . "            │\n";
echo "│     ⚠️  EL DINERO SE CUENTA DOS VECES (pago original + nuevo pago)          │\n";
echo "│                                                                             │\n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n\n";

echo "═══════════════════════════════════════════════════════════════════════════════\n";
echo "📝 CONCLUSIÓN:\n";
echo "   El traspaso actual CREA un nuevo pago, lo cual duplica el monto en\n";
echo "   las estadísticas de recaudación. Opciones para corregir:\n";
echo "   \n";
echo "   1. NO crear nuevo pago, solo cambiar id_cliente en los pagos existentes\n";
echo "   2. Marcar el nuevo pago como 'tipo_pago' = 'traspaso' y excluirlo de stats\n";
echo "   3. Usar monto_abonado = 0 en el nuevo pago (solo referencia)\n";
echo "═══════════════════════════════════════════════════════════════════════════════\n\n";
