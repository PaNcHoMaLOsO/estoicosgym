<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Cargar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Email de destino
$emailDestino = 'estoicosgymlosangeles@gmail.com';

// Obtener un cliente real de ejemplo
$cliente = DB::table('clientes')
    ->join('inscripciones', 'clientes.id', '=', 'inscripciones.id_cliente')
    ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
    ->leftJoin('precios_membresias', function($join) {
        $join->on('membresias.id', '=', 'precios_membresias.id_membresia')
             ->whereNull('precios_membresias.deleted_at')
             ->orderBy('precios_membresias.created_at', 'desc')
             ->limit(1);
    })
    ->select(
        'clientes.*',
        'inscripciones.id as inscripcion_id',
        'inscripciones.fecha_inicio',
        'inscripciones.fecha_vencimiento',
        'membresias.nombre as membresia_nombre',
        'precios_membresias.precio'
    )
    ->where('clientes.email', '!=', '')
    ->whereNotNull('clientes.email')
    ->first();

if (!$cliente) {
    echo "❌ No hay clientes con email registrados\n";
    exit(1);
}

echo "📧 Cliente de prueba: {$cliente->nombres} {$cliente->apellido_paterno}\n";
echo "📧 Email de destino: {$emailDestino}\n\n";

// Configurar Resend
$resend = Resend::client(env('RESEND_API_KEY'));

// Plantillas a enviar
$plantillas = [
    '01_bienvenida.html',
    '02_pago_completado.html',
    '03_membresia_por_vencer.html',
    '04_membresia_vencida.html',
    '05_pausa_inscripcion.html',
    '06_activacion_inscripcion.html',
    '07_pago_pendiente.html',
    '08_renovacion.html',
    '09_confirmacion_tutor_legal.html',
    '10_horario_especial.html',
    '11_promocion.html',
    '12_anuncio.html',
    '13_evento.html',
];

$enviados = 0;
$errores = 0;

foreach ($plantillas as $plantilla) {
    $ruta = storage_path('app/test_emails/preview/' . $plantilla);
    
    if (!file_exists($ruta)) {
        echo "⚠️  No se encontró: {$plantilla}\n";
        continue;
    }
    
    // Leer contenido
    $contenido = file_get_contents($ruta);
    
    // Extraer solo el contenido del body
    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $contenido, $matches)) {
        $contenido = $matches[1];
    }
    
    // Si hay div.container, extraer su contenido
    if (preg_match('/<div\s+class="container"[^>]*>(.*?)<\/div>\s*$/is', $contenido, $matches)) {
        $contenido = $matches[1];
    }
    
    // Reemplazar variables con datos reales del cliente
    $contenido = str_replace('{nombre}', $cliente->nombres, $contenido);
    $contenido = str_replace('{apellido}', $cliente->apellido_paterno, $contenido);
    $contenido = str_replace('{nombre_completo}', $cliente->nombres . ' ' . $cliente->apellido_paterno, $contenido);
    $contenido = str_replace('{email}', $cliente->email, $contenido);
    $contenido = str_replace('{telefono}', $cliente->telefono ?? 'No registrado', $contenido);
    $contenido = str_replace('{membresia}', $cliente->membresia_nombre, $contenido);
    $contenido = str_replace('{precio}', number_format($cliente->precio, 0, ',', '.'), $contenido);
    $contenido = str_replace('{fecha_inicio}', date('d/m/Y', strtotime($cliente->fecha_inicio)), $contenido);
    $contenido = str_replace('{fecha_vencimiento}', date('d/m/Y', strtotime($cliente->fecha_vencimiento)), $contenido);
    
    // Calcular días restantes
    $diasRestantes = max(0, floor((strtotime($cliente->fecha_vencimiento) - time()) / 86400));
    $contenido = str_replace('{dias_restantes}', $diasRestantes, $contenido);
    
    // Nombre de la plantilla para el asunto
    $nombrePlantilla = str_replace(['_', '.html'], [' ', ''], $plantilla);
    $nombrePlantilla = ucwords($nombrePlantilla);
    
    try {
        // Enviar email
        $resultado = $resend->emails->send([
            'from' => 'PROGYM Los Ángeles <onboarding@resend.dev>',
            'to' => [$emailDestino],
            'subject' => "TEST - {$nombrePlantilla}",
            'html' => $contenido,
        ]);
        
        echo "✅ Enviado: {$plantilla} (ID: {$resultado->id})\n";
        $enviados++;
        
        // Esperar 2 segundos entre envíos para no saturar
        sleep(2);
        
    } catch (Exception $e) {
        echo "❌ Error en {$plantilla}: {$e->getMessage()}\n";
        $errores++;
    }
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "📊 RESUMEN:\n";
echo "   ✅ Enviados: {$enviados}\n";
echo "   ❌ Errores: {$errores}\n";
echo "   📧 Total plantillas: " . count($plantillas) . "\n";
echo "   📬 Email destino: {$emailDestino}\n";
echo str_repeat('=', 50) . "\n";
