<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoNotificacion;

class ActualizarPlantillasEmailSeeder extends Seeder
{
    public function run(): void
    {
        // Plantilla base con estilos
        $estilosBase = '
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 30px; text-align: center; }
            .header img { max-width: 180px; height: auto; }
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
        <div class="header">
            <img src="https://via.placeholder.com/180x60/1a1a2e/e94560?text=ESTOICOS+GYM+LA" alt="Estoicos Gym Los Ángeles">
            <h1>Tu membresía está por vencer</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>Te recordamos que tu membresía <strong>{membresia}</strong> vencerá pronto:</p>
            
            <div class="highlight-box">
                <strong>📅 Fecha de vencimiento:</strong> {fecha_vencimiento}<br>
                <strong>⏳ Días restantes:</strong> {dias_restantes} días
            </div>
            
            <p>Para seguir entrenando sin interrupciones, te invitamos a <strong>renovar tu membresía</strong> antes de la fecha de vencimiento.</p>
            
            <p>Puedes acercarte a recepción o contactarnos para coordinar tu renovación.</p>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Quiero%20renovar%20mi%20membresía" class="btn">📱 Contactar por WhatsApp</a>
            </p>
        </div>
        
        <div class="promo-section">
            <h3>💪 ¿Necesitas suplementos?</h3>
            <p>Visita nuestra tienda y potencia tus resultados con los mejores productos.</p>
            <a href="#" class="btn btn-secondary" style="background: #fff; color: #00bf8e;">🛒 Ver Tienda de Suplementos</a>
        </div>
        
        <div class="footer">
            <p><strong>Estoicos Gym Los Ángeles</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
            <p style="margin-top: 15px; font-size: 11px; color: #666;">
                Este es un correo automático de recordatorio. Si ya renovaste, ignora este mensaje.
            </p>
        </div>
    </div>
</body>
</html>'
        ]);

        // 2. MEMBRESÍA VENCIDA
        TipoNotificacion::where('codigo', 'membresia_vencida')->update([
            'asunto_email' => '❌ {nombre}, tu membresía ha vencido - ¡Renueva hoy!',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #e94560 0%, #d63655 100%);">
            <img src="https://via.placeholder.com/180x60/e94560/ffffff?text=ESTOICOS+GYM+LA" alt="Estoicos Gym Los Ángeles">
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
                <strong>💡 ¿Sabías que?</strong><br>
                Renovando esta semana podrías acceder a promociones especiales. ¡Consulta en recepción!
            </div>
        </div>
        
        <div class="promo-section">
            <h3>💪 ¿Necesitas suplementos?</h3>
            <p>Aprovecha y pasa por nuestra tienda de suplementos junto con tu renovación.</p>
            <a href="#" class="btn btn-secondary" style="background: #fff; color: #00bf8e;">🛒 Ver Tienda de Suplementos</a>
        </div>
        
        <div class="footer">
            <p><strong>Estoicos Gym Los Ángeles</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>'
        ]);

        // 3. BIENVENIDA
        TipoNotificacion::where('codigo', 'bienvenida')->update([
            'asunto_email' => '🎉 ¡Bienvenido a la familia Estoicos, {nombre}!',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%);">
            <img src="https://via.placeholder.com/180x60/00bf8e/ffffff?text=ESTOICOS+GYM+LA" alt="Estoicos Gym Los Ángeles">
            <h1>¡Bienvenido a Estoicos Gym Los Ángeles!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>¡Felicitaciones por dar el primer paso hacia una vida más saludable! 🎉</p>
            
            <div class="success-box">
                <strong>✅ Tu membresía:</strong> {membresia}<br>
                <strong>📅 Fecha de inicio:</strong> {fecha_inicio}<br>
                <strong>📅 Válida hasta:</strong> {fecha_vencimiento}
            </div>
            
            <p><strong>Ahora eres parte de la familia Estoicos.</strong> Estamos aquí para ayudarte a alcanzar tus metas.</p>
            
            <h3>📋 Información importante:</h3>
            <ul>
                <li>Recuerda traer tu toalla y botella de agua</li>
                <li>Los instructores están disponibles para ayudarte</li>
                <li>Respeta los horarios de las máquinas</li>
                <li>¡Disfruta tu entrenamiento!</li>
            </ul>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Soy%20nuevo%20miembro%20y%20tengo%20una%20consulta" class="btn btn-secondary">💬 ¿Tienes dudas? Escríbenos</a>
            </p>
        </div>
        
        <div class="promo-section">
            <h3>🏆 ¡Potencia tus resultados!</h3>
            <p>Visita nuestra tienda de suplementos y lleva tu entrenamiento al siguiente nivel.</p>
            <a href="#" class="btn btn-secondary" style="background: #fff; color: #00bf8e;">🛒 Conoce nuestros productos</a>
        </div>
        
        <div class="footer">
            <p><strong>Estoicos Gym Los Ángeles</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📘 Facebook</a> | <a href="#">📸 Instagram</a>
            </div>
            <p style="margin-top: 15px;">¡Nos vemos en el gym! 💪</p>
        </div>
    </div>
</body>
</html>'
        ]);

        // 4. PAGO PENDIENTE
        TipoNotificacion::where('codigo', 'pago_pendiente')->update([
            'asunto_email' => '💳 {nombre}, tienes una cuota pendiente de pago',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://via.placeholder.com/180x60/1a1a2e/e94560?text=ESTOICOS+GYM" alt="Estoicos Gym">
            <h1>Recordatorio de Pago</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>Te recordamos que tienes un <strong>pago pendiente</strong> en Estoicos Gym Los Ángeles:</p>
            
            <div class="highlight-box">
                <strong>💳 Membresía:</strong> {membresia}<br>
                <strong>💰 Monto pendiente:</strong> {monto_pendiente}
            </div>
            
            <p>Para mantener tu membresía activa y seguir disfrutando de nuestras instalaciones, te invitamos a regularizar tu pago.</p>
            
            <h3>💳 Formas de pago disponibles:</h3>
            <ul>
                <li>Efectivo en recepción</li>
                <li>Transferencia bancaria</li>
                <li>Tarjeta de débito/crédito</li>
            </ul>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Quiero%20pagar%20mi%20cuota%20pendiente" class="btn">📱 Coordinar Pago</a>
            </p>
            
            <div class="info-box">
                <strong>📌 Datos para transferencia:</strong><br>
                Banco: [Nombre del banco]<br>
                Cuenta: [Número de cuenta]<br>
                RUT: [RUT del gimnasio]<br>
                Nombre: Estoicos Gym Los Ángeles
            </div>
        </div>
        
        <div class="promo-section">
            <h3>💪 ¿Ya conoces nuestra tienda?</h3>
            <p>Proteínas, creatina, aminoácidos y más. ¡Mejora tu rendimiento!</p>
            <a href="#" class="btn btn-secondary" style="background: #fff; color: #00bf8e;">🛒 Ver Suplementos</a>
        </div>
        
        <div class="footer">
            <p><strong>Estoicos Gym Los Ángeles</strong> - Los Ángeles, Chile</p>
            <p>📍 Dirección del gimnasio | 📞 Teléfono de contacto</p>
            <div class="social-links">
                <a href="#">📸 Instagram</a> | <a href="#">📱 WhatsApp</a>
            </div>
            <p style="margin-top: 15px; font-size: 11px; color: #666;">
                Si ya realizaste el pago, ignora este mensaje. Gracias.
            </p>
        </div>
    </div>
</body>
</html>'
        ]);
    }
}
