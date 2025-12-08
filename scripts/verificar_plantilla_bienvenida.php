<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inscripcion;
use App\Services\NotificacionService;

// Obtener la inscripción más reciente
$inscripcion = Inscripcion::with(['cliente', 'membresia', 'pagos'])
    ->orderBy('id', 'desc')
    ->first();

if (!$inscripcion) {
    echo "❌ No hay inscripciones en la base de datos\n";
    exit(1);
}

echo "═══════════════════════════════════════════════\n";
echo "📧 VERIFICACIÓN DE PLANTILLA DE BIENVENIDA\n";
echo "═══════════════════════════════════════════════\n\n";

echo "📋 Datos de la inscripción:\n";
echo "   ID: {$inscripcion->id}\n";
echo "   Cliente: {$inscripcion->cliente->nombres} {$inscripcion->cliente->apellido_paterno}\n";
echo "   Email: {$inscripcion->cliente->email}\n";
echo "   Membresía: {$inscripcion->membresia->nombre}\n";
echo "   Precio: $" . number_format($inscripcion->precio_final, 0, ',', '.') . "\n";
echo "   Fecha inicio: " . \Carbon\Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y') . "\n";
echo "   Fecha vencimiento: " . \Carbon\Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y') . "\n";

// Calcular pagos
$totalPagado = $inscripcion->pagos()->sum('monto_abonado');
$saldoPendiente = $inscripcion->precio_final - $totalPagado;

echo "   Total pagado: $" . number_format($totalPagado, 0, ',', '.') . "\n";
echo "   Saldo pendiente: $" . number_format($saldoPendiente, 0, ',', '.') . "\n";
echo "   Tipo pago: " . ($saldoPendiente > 0 ? 'Parcial' : 'Completo') . "\n\n";

// Cargar plantilla
$rutaPlantilla = storage_path('app/test_emails/preview/01_bienvenida.html');
if (!file_exists($rutaPlantilla)) {
    echo "❌ Plantilla no encontrada: {$rutaPlantilla}\n";
    exit(1);
}

$contenido = file_get_contents($rutaPlantilla);

// Extraer solo el body
if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $contenido, $matches)) {
    $contenido = $matches[1];
}

// Aplicar los mismos reemplazos que hace el servicio
$cliente = $inscripcion->cliente;
$nombreCompleto = trim($cliente->nombres . ' ' . $cliente->apellido_paterno);
$precioFinal = '$' . number_format($inscripcion->precio_final, 0, ',', '.');
$montoPagado = '$' . number_format($totalPagado, 0, ',', '.');
$saldoPendienteFormateado = '$' . number_format($saldoPendiente, 0, ',', '.');
$tipoPago = $saldoPendiente > 0 ? 'Parcial' : 'Completo';

echo "🔄 Aplicando reemplazos:\n";
echo "   'Juan Pérez' → '{$nombreCompleto}'\n";
echo "   'Trimestral' → '{$inscripcion->membresia->nombre}'\n";
echo "   '\$65.000' → '{$precioFinal}'\n";
echo "   '06/12/2025' → '" . \Carbon\Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y') . "'\n";
echo "   '06/03/2026' → '" . \Carbon\Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y') . "'\n";
echo "   'Parcial' → '{$tipoPago}'\n";
echo "   '\$40.000' → '{$montoPagado}'\n";
echo "   '\$25.000' → '{$saldoPendienteFormateado}'\n\n";

// Hacer los reemplazos
$contenido = str_replace('Juan Pérez', $nombreCompleto, $contenido);
$contenido = str_replace('Trimestral', $inscripcion->membresia->nombre, $contenido);
$contenido = str_replace('$65.000', $precioFinal, $contenido);
$contenido = str_replace('06/12/2025', \Carbon\Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y'), $contenido);
$contenido = str_replace('06/03/2026', \Carbon\Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'), $contenido);
$contenido = str_replace('Parcial', $tipoPago, $contenido);
$contenido = str_replace('$40.000', $montoPagado, $contenido);
$contenido = str_replace('$25.000', $saldoPendienteFormateado, $contenido);

// Extraer la sección de confirmación para verificar
preg_match('/¡Bienvenido\/a ([^!]+)!/', $contenido, $nombreMatch);
preg_match('/Membresía:<\/strong><\/td>\s+<td[^>]*>([^<]+)</', $contenido, $membresiaMatch);
preg_match('/Valor membresía:<\/strong><\/td>\s+<td[^>]*>([^<]+)</', $contenido, $precioMatch);
preg_match('/Tipo de pago:<\/strong><\/td>\s+<td[^>]*>([^<]+)</', $contenido, $tipoMatch);
preg_match('/Monto pagado:<\/strong><\/td>\s+<td[^>]*>([^<]+)</', $contenido, $pagoMatch);
preg_match('/Saldo pendiente:<\/strong><\/td>\s+<td[^>]*>([^<]+)</', $contenido, $saldoMatch);

echo "═══════════════════════════════════════════════\n";
echo "✅ VERIFICACIÓN DE CONTENIDO PROCESADO:\n";
echo "═══════════════════════════════════════════════\n\n";

echo "Saludo: " . ($nombreMatch[1] ?? '❌ NO ENCONTRADO') . "\n";
echo "Membresía: " . ($membresiaMatch[1] ?? '❌ NO ENCONTRADO') . "\n";
echo "Precio: " . ($precioMatch[1] ?? '❌ NO ENCONTRADO') . "\n";
echo "Tipo pago: " . ($tipoMatch[1] ?? '❌ NO ENCONTRADO') . "\n";
echo "Monto pagado: " . ($pagoMatch[1] ?? '❌ NO ENCONTRADO') . "\n";
echo "Saldo: " . ($saldoMatch[1] ?? '❌ NO ENCONTRADO') . "\n\n";

// Guardar HTML procesado para inspección
$archivoSalida = storage_path('app/test_emails/preview/test_bienvenida_procesada.html');
file_put_contents($archivoSalida, "<!DOCTYPE html>\n<html>\n<head><meta charset=\"UTF-8\"></head>\n<body>\n{$contenido}\n</body>\n</html>");

echo "📄 HTML procesado guardado en:\n";
echo "   {$archivoSalida}\n\n";

echo "═══════════════════════════════════════════════\n";
echo "✅ VERIFICACIÓN COMPLETADA\n";
echo "═══════════════════════════════════════════════\n";
