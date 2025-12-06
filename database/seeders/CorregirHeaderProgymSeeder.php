<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoNotificacion;

class CorregirHeaderProgymSeeder extends Seeder
{
    /**
     * CORRECCIÓN: Header con fondo negro, "PRO" en blanco y "GYM" en rojo
     * SIN logo de imagen, solo texto HTML
     */
    public function run(): void
    {
        // Header con texto: PRO (blanco) + GYM (rojo) en fondo negro
        $headerProgym = '<div style="background: #000000; padding: 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 32px; font-weight: bold;">
                <span style="color: #ffffff;">PRO</span><span style="color: #e94560;">GYM</span>
            </h1>
        </div>';

        // Estilos base actualizados (sin referencia a logo img)
        $estilosBase = '
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 30px; text-align: center; }
            .header h1 { color: #ffffff; margin: 15px 0 0; font-size: 24px; }
            .content { padding: 30px; color: #333; line-height: 1.6; }
            .highlight-box { background: #fff3cd; border-left: 4px solid #f0a500; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; }
            .danger-box { background: #f8d7da; border-left: 4px solid #e94560; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; }
            .success-box { background: #d4edda; border-left: 4px solid #00bf8e; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; }
            .info-box { background: #e7f1ff; border-left: 4px solid #4361ee; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; }
            .btn { display: inline-block; background: #e94560; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; margin: 10px 0; }
            .btn-secondary { background: #00bf8e; }
            .promo-section { background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); padding: 25px; margin-top: 20px; text-align: center; color: #fff; }
            .promo-section h3 { margin: 0 0 10px; font-size: 18px; }
            .promo-section p { margin: 0 0 15px; opacity: 0.95; }
            .footer { background: #1a1a2e; color: #aaa; padding: 20px; text-align: center; font-size: 12px; }
            .footer a { color: #e94560; text-decoration: none; }
            .social-links { margin: 15px 0; }
            .social-links a { display: inline-block; margin: 0 8px; color: #fff; font-size: 18px; }
        </style>';

        // 1. MEMBRESÍA POR VENCER
        TipoNotificacion::where('codigo', 'membresia_por_vencer')->update([
            'asunto_email' => '⏰ {nombre}, tu membresía vence en {dias_restantes} días',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        ' . $headerProgym . '
        <div class="header">
            <h1>Tu membresía está por vencer</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>Te recordamos que tu membresía <strong>{membresia}</strong> vence pronto:</p>
            
            <div class="highlight-box">
                <strong>⏰ Fecha de vencimiento:</strong> {fecha_vencimiento}<br>
                <strong>⏳ Días restantes:</strong> {dias_restantes} días
            </div>
            
            <p><strong>¡No dejes que tu entrenamiento se detenga!</strong> Renueva tu membresía y sigue disfrutando de nuestras instalaciones.</p>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Quiero%20renovar%20mi%20membresía" class="btn">📱 Renovar por WhatsApp</a>
            </p>
            
            <div class="info-box">
                <strong>💡 Beneficios de renovar ahora:</strong><br>
                ✓ Sin interrupción en tu rutina<br>
                ✓ Mantén tu progreso<br>
                ✓ Posibles promociones disponibles
            </div>
        </div>
        
        <div class="footer">
            <p><strong>ProGym</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>'
        ]);

        // 2. MEMBRESÍA VENCIDA
        TipoNotificacion::where('codigo', 'membresia_vencida')->update([
            'asunto_email' => '❌ {nombre}, tu membresía en ProGym ha vencido',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        ' . $headerProgym . '
        <div class="header" style="background: linear-gradient(135deg, #e94560 0%, #d63655 100%);">
            <h1>Tu membresía ha vencido</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>Te informamos que tu membresía <strong>{membresia}</strong> ha vencido:</p>
            
            <div class="danger-box">
                <strong>❌ Membresía vencida desde:</strong> {fecha_vencimiento}<br>
                <strong>🚫 Estado:</strong> Sin acceso al gimnasio
            </div>
            
            <p><strong>¡Te extrañamos!</strong> No dejes que tu progreso se detenga. Renueva tu membresía y vuelve a entrenar con nosotros.</p>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Quiero%20renovar%20mi%20membresía%20vencida" class="btn">📱 Renovar por WhatsApp</a>
            </p>
            
            <div class="info-box">
                <strong>💪 ¿Por qué volver?</strong><br>
                ✓ Equipamiento de calidad<br>
                ✓ Ambiente motivador<br>
                ✓ Entrenadores profesionales
            </div>
        </div>
        
        <div class="footer">
            <p><strong>ProGym</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>'
        ]);

        // 3. PAGO PENDIENTE
        TipoNotificacion::where('codigo', 'pago_pendiente')->update([
            'asunto_email' => '💰 {nombre}, tienes un pago pendiente en ProGym',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        ' . $headerProgym . '
        <div class="header" style="background: linear-gradient(135deg, #f0a500 0%, #e69500 100%);">
            <h1>Pago Pendiente</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>Te recordamos que tienes un pago pendiente por tu membresía <strong>{membresia}</strong>:</p>
            
            <div class="highlight-box">
                <strong>💰 Monto pendiente:</strong> ${monto_pendiente}<br>
                <strong>💳 Monto total:</strong> ${monto_total}<br>
                <strong>📅 Fecha de vencimiento:</strong> {fecha_vencimiento}
            </div>
            
            <p><strong>¡Regulariza tu situación!</strong> Completa tu pago para seguir disfrutando sin interrupciones.</p>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Quiero%20regularizar%20mi%20pago%20pendiente" class="btn">📱 Contactar por WhatsApp</a>
            </p>
            
            <div class="info-box">
                <strong>💡 Métodos de pago disponibles:</strong><br>
                ✓ Transferencia bancaria<br>
                ✓ Efectivo en recepción<br>
                ✓ Tarjetas de débito/crédito
            </div>
        </div>
        
        <div class="footer">
            <p><strong>ProGym</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>'
        ]);

        // 4. BIENVENIDA
        TipoNotificacion::where('codigo', 'bienvenida')->update([
            'asunto_email' => '🎉 ¡Bienvenido a ProGym, {nombre}!',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        ' . $headerProgym . '
        <div class="header" style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%);">
            <h1>¡Bienvenido a ProGym!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>¡Estamos felices de tenerte con nosotros! 🎉 Tu membresía <strong>{membresia}</strong> ha sido activada exitosamente.</p>
            
            <div class="success-box">
                <strong>✅ Membresía activa:</strong> {membresia}<br>
                <strong>📅 Fecha de inicio:</strong> {fecha_inicio}<br>
                <strong>⏰ Válida hasta:</strong> {fecha_vencimiento}<br>
                <strong>💰 Inversión:</strong> ${precio}
            </div>
            
            <p><strong>¡Comienza tu transformación hoy!</strong> Nuestras instalaciones y entrenadores están listos para ayudarte a alcanzar tus metas.</p>
            
            <div class="info-box">
                <strong>💪 Lo que incluye tu membresía:</strong><br>
                ✓ Acceso a todas las máquinas<br>
                ✓ Área de pesas libres<br>
                ✓ Vestuarios y duchas<br>
                ✓ Asesoría de entrenadores
            </div>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Soy%20nuevo%20miembro" class="btn btn-secondary">📱 ¿Tienes dudas? Contáctanos</a>
            </p>
            
            <div class="promo-section">
                <h3>🎁 Trae a un amigo</h3>
                <p>Recomienda ProGym y obtén beneficios especiales</p>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>ProGym</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>'
        ]);

        // 5. RENOVACIÓN EXITOSA
        TipoNotificacion::where('codigo', 'renovacion_exitosa')->update([
            'asunto_email' => '✅ {nombre}, tu renovación en ProGym fue exitosa',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        ' . $headerProgym . '
        <div class="header" style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%);">
            <h1>¡Renovación Exitosa!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>¡Excelente noticia! 🎉 Tu membresía <strong>{membresia}</strong> ha sido renovada con éxito.</p>
            
            <div class="success-box">
                <strong>✅ Membresía renovada:</strong> {membresia}<br>
                <strong>📅 Nueva fecha de vencimiento:</strong> {fecha_vencimiento}<br>
                <strong>💰 Monto pagado:</strong> ${precio}
            </div>
            
            <p><strong>¡Sigue adelante con tu entrenamiento!</strong> Tu compromiso con tu salud es admirable.</p>
            
            <div class="info-box">
                <strong>💡 Tips para aprovechar tu membresía:</strong><br>
                ✓ Mantén una rutina constante<br>
                ✓ Consulta con nuestros entrenadores<br>
                ✓ Establece metas alcanzables<br>
                ✓ Hidrátate adecuadamente
            </div>
        </div>
        
        <div class="footer">
            <p><strong>ProGym</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>'
        ]);

        // 6. NOTIFICACIÓN MANUAL (plantilla base para envíos personalizados)
        TipoNotificacion::updateOrCreate(
            ['codigo' => 'notificacion_manual'],
            [
                'nombre' => 'Notificación Manual',
                'descripcion' => 'Plantilla para envíos personalizados desde el panel de administración',
                'asunto_email' => '{asunto}',
                'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        ' . $headerProgym . '
        <div class="header">
            <h1>{asunto}</h1>
        </div>
        <div class="content">
            {mensaje}
        </div>
        
        <div class="footer">
            <p><strong>ProGym</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>',
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
            ]
        );

        echo "✅ Headers corregidos: Fondo negro con PRO (blanco) + GYM (rojo)\n";
        echo "✅ Total plantillas actualizadas: 6\n";
        echo "✅ Sin logos de imagen, solo texto HTML\n";
    }
}
