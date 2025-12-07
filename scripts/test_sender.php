<?php
/**
 * Script de prueba para verificar conexión con Sender.net
 * Ejecutar con: php test_sender.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║        PRUEBA DE CONEXIÓN CON SENDER.NET                ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// 1. VERIFICAR CONFIGURACIÓN
echo "1️⃣  VERIFICANDO CONFIGURACIÓN\n";
echo "   ────────────────────────────────────────────────────────\n";

$config = [
    'MAIL_MAILER' => config('mail.default'),
    'MAIL_HOST' => config('mail.mailers.smtp.host'),
    'MAIL_PORT' => config('mail.mailers.smtp.port'),
    'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
    'MAIL_PASSWORD' => config('mail.mailers.smtp.password') ? '***' . substr(config('mail.mailers.smtp.password'), -4) : 'NO CONFIGURADO',
    'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
    'MAIL_FROM_ADDRESS' => config('mail.from.address'),
    'MAIL_FROM_NAME' => config('mail.from.name'),
];

foreach ($config as $key => $value) {
    $status = $value ? '✓' : '✗';
    echo "   {$status} {$key}: {$value}\n";
}
echo "\n";

// Verificar si es Sender.net
$isSender = strpos(config('mail.mailers.smtp.host'), 'sender.net') !== false;
if ($isSender) {
    echo "   ✅ Configuración detectada: SENDER.NET\n\n";
} else {
    echo "   ⚠️  El host no es sender.net\n";
    echo "   Host actual: " . config('mail.mailers.smtp.host') . "\n\n";
}

// 2. PRUEBA DE ENVÍO
echo "2️⃣  ENVIANDO EMAIL DE PRUEBA\n";
echo "   ────────────────────────────────────────────────────────\n";

$emailDestino = config('mail.from.address');
echo "   📧 Destinatario: {$emailDestino}\n";
echo "   ⏳ Enviando...\n\n";

try {
    $inicio = microtime(true);
    
    Mail::raw('✅ ¡Felicitaciones! Tu configuración de Sender.net está funcionando correctamente.

Este es un email de prueba enviado desde tu sistema de notificaciones de Estoicos Gym.

Detalles del envío:
• Fecha: ' . now()->format('d/m/Y H:i:s') . '
• Servidor: ' . config('mail.mailers.smtp.host') . '
• Puerto: ' . config('mail.mailers.smtp.port') . '
• Encriptación: ' . config('mail.mailers.smtp.encryption') . '

Si recibiste este email, significa que:
✓ Las credenciales SMTP son correctas
✓ La conexión con Sender.net funciona
✓ Puedes enviar notificaciones a tus clientes

Próximos pasos:
1. Verificar que el email llegó a tu bandeja de entrada
2. Revisar el panel de Sender.net: https://app.sender.net/
3. Ejecutar: php artisan notificaciones:enviar --enviar

---
Sistema de Notificaciones - Estoicos Gym
' . config('app.url'), function ($message) use ($emailDestino) {
        $message->to($emailDestino)
                ->subject('✅ Prueba Exitosa - Sender.net Configurado');
    });
    
    $tiempo = round((microtime(true) - $inicio) * 1000, 2);
    
    echo "   ✅ EMAIL ENVIADO EXITOSAMENTE\n";
    echo "   ⏱️  Tiempo: {$tiempo}ms\n\n";
    
    echo "3️⃣  VERIFICACIONES\n";
    echo "   ────────────────────────────────────────────────────────\n";
    echo "   ✓ Conexión SMTP establecida\n";
    echo "   ✓ Autenticación exitosa\n";
    echo "   ✓ Email enviado al servidor\n\n";
    
    echo "4️⃣  PRÓXIMOS PASOS\n";
    echo "   ────────────────────────────────────────────────────────\n";
    echo "   1. Revisa tu bandeja: {$emailDestino}\n";
    echo "   2. Si no llegó, revisa spam/correo no deseado\n";
    echo "   3. Ve al panel: https://app.sender.net/campaigns\n";
    echo "   4. Prueba con notificaciones reales:\n";
    echo "      php artisan notificaciones:enviar --enviar\n\n";
    
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║               ✅ PRUEBA COMPLETADA                       ║\n";
    echo "║                                                          ║\n";
    echo "║  Tu configuración de Sender.net está funcionando         ║\n";
    echo "║  correctamente. Ya puedes enviar notificaciones a        ║\n";
    echo "║  tus clientes sin restricciones.                         ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    
} catch (\Exception $e) {
    echo "   ❌ ERROR AL ENVIAR EMAIL\n\n";
    
    echo "4️⃣  DIAGNÓSTICO DEL ERROR\n";
    echo "   ────────────────────────────────────────────────────────\n";
    echo "   Mensaje: " . $e->getMessage() . "\n\n";
    
    // Diagnóstico común
    if (strpos($e->getMessage(), 'Connection could not be established') !== false) {
        echo "   🔍 POSIBLES CAUSAS:\n";
        echo "   • Host incorrecto (debe ser: smtp.sender.net)\n";
        echo "   • Puerto incorrecto (debe ser: 587 o 465)\n";
        echo "   • Firewall bloqueando puerto SMTP\n";
        echo "   • Sin conexión a internet\n\n";
        
        echo "   🔧 SOLUCIONES:\n";
        echo "   1. Verifica MAIL_HOST=smtp.sender.net\n";
        echo "   2. Prueba MAIL_PORT=587 con MAIL_ENCRYPTION=tls\n";
        echo "   3. O prueba MAIL_PORT=465 con MAIL_ENCRYPTION=ssl\n";
        echo "   4. Ejecuta: php artisan config:clear\n\n";
        
    } elseif (strpos($e->getMessage(), 'Authentication failed') !== false || 
              strpos($e->getMessage(), 'Invalid credentials') !== false) {
        echo "   🔍 POSIBLES CAUSAS:\n";
        echo "   • Username incorrecto (debe ser tu email de Sender.net)\n";
        echo "   • Password incorrecto (debe ser el token SMTP que empieza con SND_)\n";
        echo "   • Token SMTP no generado en Sender.net\n\n";
        
        echo "   🔧 SOLUCIONES:\n";
        echo "   1. Ve a: https://app.sender.net/settings/smtp\n";
        echo "   2. Activa SMTP si no está activo\n";
        echo "   3. Copia las credenciales exactas\n";
        echo "   4. Actualiza tu archivo .env:\n";
        echo "      MAIL_USERNAME=tu_email@ejemplo.com\n";
        echo "      MAIL_PASSWORD=SND_tu_token_aqui\n";
        echo "   5. Ejecuta: php artisan config:clear\n\n";
        
    } else {
        echo "   🔍 ERROR NO IDENTIFICADO\n";
        echo "   Por favor revisa:\n";
        echo "   • Archivo .env tiene las credenciales correctas\n";
        echo "   • SMTP está activado en Sender.net\n";
        echo "   • No hay espacios extras en las credenciales\n\n";
    }
    
    echo "   📚 RECURSOS:\n";
    echo "   • Panel Sender.net: https://app.sender.net/\n";
    echo "   • Configuración SMTP: https://app.sender.net/settings/smtp\n";
    echo "   • Documentación: CONFIGURACION_SENDER_NET.md\n\n";
    
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║               ❌ PRUEBA FALLIDA                          ║\n";
    echo "║                                                          ║\n";
    echo "║  Revisa las soluciones arriba y vuelve a intentar.      ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    
    exit(1);
}
