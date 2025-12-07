<?php

/**
 * PREVIEW DE PLANTILLAS DE EMAIL
 * Genera archivos HTML de prueba con datos de ejemplo
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Facade;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Facade::setFacadeApplication($app);

echo "\n📧 Generando previews HTML de plantillas...\n\n";

// Datos de prueba
$datosPrueba = [
    'nombre' => 'Juan Pérez',
    'nombre_cliente' => 'Juan Pérez',
    'email_cliente' => 'juan.perez@gmail.com',
    'run_cliente' => '12.345.678-9',
    'nombre_apoderado' => 'María González',
    'email_apoderado' => 'maria.gonzalez@gmail.com',
    'run_apoderado' => '11.222.333-4',
    'nombre_menor' => 'Juanito Pérez',
    'run_menor' => '25.555.666-7',
    'fecha_nacimiento_menor' => '15/03/2010',
    'nombre_membresia' => 'Trimestral',
    'membresia' => 'Trimestral',
    'precio_membresia' => '$65.000',
    'precio_total' => '$65.000',
    'fecha_inicio' => '06/12/2025',
    'fecha_vencimiento' => '06/03/2026',
    'fecha_registro' => '06/12/2025 14:30',
    'tipo_pago' => 'Parcial',
    'monto_pagado' => '$40.000',
    'monto_pendiente' => '$25.000',
    'monto_total' => '$65.000',
    'metodo_pago' => 'Transferencia',
    'fecha_pago' => '06/12/2025',
    'dias_restantes' => '5',
    'fecha_pausa' => '06/12/2025',
    'motivo_pausa' => 'Viaje por trabajo',
    'fecha_reactivacion' => '15/01/2026',
    'fecha_activacion' => '06/12/2025',
];

// Plantillas a procesar
$plantillas = [
    '01_bienvenida.html' => 'Bienvenida',
    '05_pago_completado.html' => 'Pago Completado',
    '06_membresia_por_vencer.html' => 'Membresía por Vencer',
    '07_membresia_vencida.html' => 'Membresía Vencida',
    '09_pausa_inscripcion.html' => 'Pausa Inscripción',
    '10_activacion_inscripcion.html' => 'Activación Inscripción',
    '11_pago_pendiente.html' => 'Pago Pendiente',
    '12_renovacion.html' => 'Renovación',
    '13_confirmacion_tutor_legal.html' => 'Confirmación Tutor Legal',
];

$carpetaPreview = storage_path('app/test_emails/preview');
if (!file_exists($carpetaPreview)) {
    mkdir($carpetaPreview, 0755, true);
}

foreach ($plantillas as $archivo => $nombre) {
    $rutaOrigen = storage_path("app/test_emails/{$archivo}");
    
    if (!file_exists($rutaOrigen)) {
        echo "⚠️  Saltando: {$archivo} (no existe)\n";
        continue;
    }
    
    // Cargar y procesar plantilla
    $contenido = file_get_contents($rutaOrigen);
    
    // Reemplazar variables
    foreach ($datosPrueba as $variable => $valor) {
        $contenido = str_replace("{{$variable}}", $valor, $contenido);
    }
    
    // Agregar DOCTYPE y HTML completo
    $htmlCompleto = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROGYM - {$nombre}</title>
    <style>
        body { margin: 0; padding: 20px; background: #f0f0f0; font-family: Arial, sans-serif; }
        .container { max-width: 650px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        {$contenido}
    </div>
</body>
</html>
HTML;
    
    // Guardar preview
    $rutaDestino = "{$carpetaPreview}/{$archivo}";
    file_put_contents($rutaDestino, $htmlCompleto);
    
    echo "✅ {$archivo} → preview/{$archivo}\n";
}

echo "\n✅ Previews generados en: storage/app/test_emails/preview/\n";
echo "📂 Abre los archivos en tu navegador para visualizarlos\n\n";
