<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoNotificacion;

class ActualizarBrandingProgymSeeder extends Seeder
{
    /**
     * SEEDER DEFINITIVO: Actualiza TODAS las plantillas con branding ProGym correcto
     * Logo: Fondo negro con texto blanco "PROGYM"
     * Nombre: ProGym en todos los textos
     */
    public function run(): void
    {
        // Logo oficial: Fondo negro (#000000) con texto blanco
        $logoProgym = 'https://via.placeholder.com/180x60/000000/ffffff?text=PROGYM';

        // Estilos base (sin cambios)
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
            <img src="' . $logoProgym . '" alt="ProGym">
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
        <div class="header" style="background: linear-gradient(135deg, #e94560 0%, #d63655 100%);">
            <img src="' . $logoProgym . '" alt="ProGym">
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

        // 3. BIENVENIDA
        TipoNotificacion::where('codigo', 'bienvenida')->update([
            'asunto_email' => '🎉 ¡Bienvenido a ProGym, {nombre}!',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%);">
            <img src="' . $logoProgym . '" alt="ProGym">
            <h1>¡Bienvenido a ProGym!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>¡Estamos emocionados de tenerte con nosotros! Tu inscripción se ha completado exitosamente.</p>
            
            <div class="success-box">
                <strong>✅ Membresía:</strong> {membresia}<br>
                <strong>📅 Fecha de inicio:</strong> {fecha_inicio}<br>
                <strong>⏰ Válida hasta:</strong> {fecha_vencimiento}<br>
                <strong>💰 Monto pagado:</strong> ${monto_pagado}
            </div>
            
            <p><strong>¿Qué sigue ahora?</strong></p>
            <ul>
                <li>🏋️ Presenta tu RUT en recepción para acceder</li>
                <li>📋 Solicita tu evaluación física inicial (gratis)</li>
                <li>💪 Comienza tu rutina de entrenamiento</li>
                <li>🤝 Conoce a nuestro equipo de entrenadores</li>
            </ul>
            
            <div class="info-box">
                <strong>🕐 Horarios de atención:</strong><br>
                Lunes a Viernes: 06:00 - 22:00<br>
                Sábados: 08:00 - 20:00<br>
                Domingos: 09:00 - 14:00
            </div>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Soy%20nuevo%20miembro%20y%20tengo%20consultas" class="btn">📱 ¿Tienes dudas? Escríbenos</a>
            </p>
        </div>
        
        <div class="promo-section">
            <h3>🎁 Regalo de bienvenida</h3>
            <p>Pasa por recepción y recibe tu kit de bienvenida con toalla y botella de agua.</p>
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

        // 4. PAGO PENDIENTE
        TipoNotificacion::where('codigo', 'pago_pendiente')->update([
            'asunto_email' => '💰 Recordatorio: Tienes un pago pendiente en ProGym',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoProgym . '" alt="ProGym">
            <h1>Pago Pendiente</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>Te recordamos que tienes un <strong>pago pendiente</strong> en ProGym:</p>
            
            <div class="highlight-box">
                <strong>📋 Detalle del pago:</strong><br>
                💵 Monto total: ${monto_total}<br>
                ✅ Monto pagado: ${monto_pagado}<br>
                ⚠️ <strong>Saldo pendiente: ${monto_pendiente}</strong>
            </div>
            
            <p><strong>Formas de pago disponibles:</strong></p>
            <ul>
                <li>💵 Efectivo en recepción</li>
                <li>💳 Tarjeta de débito/crédito</li>
                <li>🏦 Transferencia bancaria</li>
            </ul>
            
            <div class="info-box">
                <strong>📱 Datos para transferencia:</strong><br>
                Banco: Banco Estado<br>
                Tipo de cuenta: Cuenta Corriente<br>
                Número: 12345678<br>
                RUT: 12.345.678-9<br>
                Nombre: ProGym<br>
                Email confirmación: pagos@progym.cl
            </div>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Consulto%20por%20mi%20pago%20pendiente" class="btn">📱 Contactar por WhatsApp</a>
            </p>
            
            <p style="font-size: 12px; color: #666; text-align: center;">
                Si ya realizaste el pago, por favor envíanos el comprobante para actualizar tu estado.
            </p>
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

        // 5. PAUSA DE INSCRIPCIÓN
        TipoNotificacion::where('codigo', 'pausa_inscripcion')->update([
            'asunto_email' => '⏸️ Confirmación: Pausa de membresía - ProGym',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #4361ee 0%, #3651d4 100%);">
            <img src="' . $logoProgym . '" alt="ProGym">
            <h1>Membresía Pausada</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>Tu solicitud de pausa de membresía ha sido procesada exitosamente.</p>
            
            <div class="info-box">
                <strong>⏸️ Detalles de la pausa:</strong><br>
                📅 Fecha de inicio: {fecha_pausa_inicio}<br>
                📅 Fecha de fin: {fecha_pausa_fin}<br>
                ⏳ Días de pausa: {dias_pausa} días<br>
                🔄 Nueva fecha de vencimiento: {nueva_fecha_vencimiento}
            </div>
            
            <p><strong>¿Qué significa esto?</strong></p>
            <ul>
                <li>🚫 No tendrás acceso al gimnasio durante el período de pausa</li>
                <li>📅 Los días de pausa se suman automáticamente al final de tu membresía</li>
                <li>💰 No se generarán cobros durante la pausa</li>
                <li>✅ Tu membresía se reactivará automáticamente después de la pausa</li>
            </ul>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Consulta%20sobre%20mi%20pausa%20de%20membresía" class="btn">📱 ¿Dudas? Contáctanos</a>
            </p>
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

        // 6. ACTIVACIÓN DE INSCRIPCIÓN
        TipoNotificacion::where('codigo', 'activacion_inscripcion')->update([
            'asunto_email' => '▶️ ¡Tu membresía en ProGym está activa nuevamente!',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%);">
            <img src="' . $logoProgym . '" alt="ProGym">
            <h1>¡Membresía Reactivada!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>¡Excelentes noticias! Tu membresía ha sido reactivada y ya puedes volver a entrenar.</p>
            
            <div class="success-box">
                <strong>✅ Estado:</strong> Activa<br>
                <strong>📅 Reactivada desde:</strong> {fecha_reactivacion}<br>
                <strong>⏰ Válida hasta:</strong> {fecha_vencimiento}<br>
                <strong>⏳ Días restantes:</strong> {dias_restantes} días
            </div>
            
            <p><strong>¡Te esperamos de vuelta!</strong></p>
            <ul>
                <li>🏋️ Tu acceso está habilitado desde hoy</li>
                <li>💪 Retoma tu rutina donde la dejaste</li>
                <li>📊 Solicita una evaluación de seguimiento (gratis)</li>
            </ul>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="https://wa.me/56912345678?text=Hola!%20Mi%20membresía%20fue%20reactivada" class="btn">📱 Confirmar mi visita</a>
            </p>
        </div>
        
        <div class="promo-section">
            <h3>💪 ¿Listo para retomar?</h3>
            <p>Agenda tu primera sesión con uno de nuestros entrenadores para retomar con todo.</p>
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

        // 7. PAGO COMPLETADO
        TipoNotificacion::where('codigo', 'pago_completado')->update([
            'asunto_email' => '✅ Pago recibido - ProGym',
            'plantilla_email' => '<!DOCTYPE html>
<html>
<head>' . $estilosBase . '</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%);">
            <img src="' . $logoProgym . '" alt="ProGym">
            <h1>Pago Confirmado</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{nombre}</strong>,</p>
            
            <p>¡Hemos recibido tu pago exitosamente!</p>
            
            <div class="success-box">
                <strong>✅ Confirmación de pago</strong><br>
                💵 Monto recibido: ${monto_pagado}<br>
                📅 Fecha: {fecha_pago}<br>
                🧾 Método: {metodo_pago}<br>
                📋 Comprobante N°: {numero_comprobante}
            </div>
            
            <p><strong>Detalles de tu membresía:</strong></p>
            <ul>
                <li>🏋️ Membresía: {membresia}</li>
                <li>📅 Válida desde: {fecha_inicio}</li>
                <li>⏰ Válida hasta: {fecha_vencimiento}</li>
                <li>✅ Estado: Activa</li>
            </ul>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="#" class="btn">📄 Descargar Comprobante</a>
            </p>
            
            <p style="font-size: 12px; color: #666; text-align: center;">
                Conserva este email como comprobante de pago.
            </p>
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

        $this->command->info('✅ Todas las plantillas actualizadas con branding ProGym (logo negro)');
    }
}
