<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificacionesSeeder extends Seeder
{
    public function run(): void
    {
        // Los estados de notificaciones ya fueron creados en la migración
        // Solo insertamos los tipos de notificación si no existen

        // ===== TIPOS DE NOTIFICACIONES =====
        $tipos = [
            [
                'codigo' => 'membresia_por_vencer',
                'nombre' => 'Membresía por Vencer - Recordatorio',
                'descripcion' => 'Se envía X días antes de que venza la membresía (configurable)',
                'asunto_email' => '⏰ {nombre}, tu membresía en PROGYM vence en {dias_restantes} días',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 50px 20px; text-align: center;">
        <img src="https://raw.githubusercontent.com/PaNcHoMaLOsO/estoicosgym/main/public/images/progym_logo.svg" alt="PROGYM" style="max-width: 220px; height: auto;">
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 40px 30px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 20px 0; font-size: 24px; font-weight: bold;">Hola {nombre} 👋</h2>
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 0 0 25px 0;">
            Te recordamos que tu membresía <strong style="color: #101010;">{membresia}</strong> está próxima a vencer.
        </p>
        
        <!-- RECORDATORIO -->
        <div style="background: #FFFFFF; border-left: 6px solid #FFC107; padding: 25px; margin: 30px 0; border-radius: 6px; border: 1px solid #C7C7C7; border-left: 6px solid #FFC107;">
            <h3 style="margin: 0 0 12px 0; color: #101010; font-size: 20px; font-weight: bold;">⏳ Te quedan {dias_restantes} días</h3>
            <p style="margin: 0; color: #505050; font-size: 15px;">Fecha de vencimiento: <strong style="color: #101010;">{fecha_vencimiento}</strong></p>
        </div>
        
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 25px 0;">
            Renueva ahora para seguir entrenando sin interrupciones. <strong style="color: #E0001A;">¡No pierdas tu ritmo!</strong> 💪
        </p>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 35px 0;">
            <a href="https://wa.me/56950963143?text=Hola%2C%20quiero%20renovar%20mi%20membres%C3%ADa" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 16px 40px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">💬 Renovar por WhatsApp</a>
        </div>
        
        <p style="color: #505050; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0; text-align: center;">
            También puedes acercarte a recepción o llamarnos al <strong style="color: #101010;">+56 9 5096 3143</strong>
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>
            📧 progymlosangeles@gmail.com | 📞 +56 9 5096 3143
        </p>
        <p style="margin: 0; font-size: 13px;">
            <a href="https://www.instagram.com/progym_losangeles" style="color: #E0001A; text-decoration: none; font-weight: bold;">📸 @progym_losangeles</a>
        </p>
        <p style="margin: 15px 0 0 0; font-size: 11px; color: #808080;">
            Este es un correo automático, por favor no responder directamente.
        </p>
    </div>
</div>',
                'dias_anticipacion' => 5,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'membresia_vencida',
                'nombre' => 'Membresía Vencida - Recordatorio',
                'descripcion' => 'Se envía inicio y fin de mes para membresías vencidas',
                'asunto_email' => '❗ {nombre}, tu membresía en PROGYM ha vencido',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 50px 20px; text-align: center;">
        <img src="https://raw.githubusercontent.com/PaNcHoMaLOsO/estoicosgym/main/public/images/progym_logo.svg" alt="PROGYM" style="max-width: 220px; height: auto;">
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 40px 30px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 20px 0; font-size: 24px; font-weight: bold;">Hola {nombre} 👋</h2>
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 0 0 25px 0;">
            Te informamos que tu membresía <strong style="color: #101010;">{membresia}</strong> venció el <strong>{fecha_vencimiento}</strong>.
        </p>
        
        <!-- ALERTA ROJA -->
        <div style="background: #E0001A; color: #FFFFFF; padding: 25px; margin: 30px 0; border-radius: 6px;">
            <h3 style="margin: 0 0 12px 0; color: #FFFFFF; font-size: 20px; font-weight: bold;">⚠️ Membresía Vencida</h3>
            <p style="margin: 0; color: #FFFFFF; font-size: 15px;">No podrás acceder al gimnasio hasta renovar.</p>
        </div>
        
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 25px 0;">
            <strong style="color: #E0001A;">¡Te extrañamos!</strong> Renueva hoy y retoma tu entrenamiento. Cada día cuenta para alcanzar tus metas. 💪
        </p>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 35px 0;">
            <a href="https://wa.me/56950963143?text=Hola%2C%20quiero%20renovar%20mi%20membres%C3%ADa" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 16px 40px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">💬 Renovar Ahora</a>
        </div>
        
        <p style="color: #505050; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0; text-align: center;">
            Acércate a recepción o contáctanos al <strong style="color: #101010;">+56 9 5096 3143</strong>
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>
            📧 progymlosangeles@gmail.com | 📞 +56 9 5096 3143
        </p>
        <p style="margin: 0; font-size: 13px;">
            <a href="https://www.instagram.com/progym_losangeles" style="color: #E0001A; text-decoration: none; font-weight: bold;">📸 @progym_losangeles</a>
        </p>
        <p style="margin: 15px 0 0 0; font-size: 11px; color: #808080;">
            Este es un correo automático, por favor no responder directamente.
        </p>
    </div>
</div>',
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'bienvenida',
                'nombre' => 'Bienvenida y Confirmación',
                'descripcion' => 'Se envía cuando un cliente se inscribe - Incluye confirmación de inscripción y pago',
                'asunto_email' => '🎉 ¡Bienvenido a PROGYM, {nombre}! - Inscripción Confirmada',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 50px 20px; text-align: center;">
        <img src="https://raw.githubusercontent.com/PaNcHoMaLOsO/estoicosgym/main/public/images/progym_logo.svg" alt="PROGYM" style="max-width: 220px; height: auto;">
    </div>
    
    <!-- BIENVENIDA -->
    <div style="padding: 40px 30px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 20px 0; font-size: 24px; font-weight: bold;">¡Bienvenido/a {nombre}! 🎉</h2>
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 0 0 25px 0;">
            Nos alegra que te hayas unido a <strong style="color: #101010;">PROGYM</strong>. Tu inscripción ha sido confirmada exitosamente y tu transformación comienza hoy.
        </p>
        
        <!-- CONFIRMACIÓN INSCRIPCIÓN -->
        <div style="background: #FFFFFF; border-left: 6px solid #2EB872; padding: 25px; margin: 30px 0; border-radius: 6px; border: 1px solid #C7C7C7; border-left: 6px solid #2EB872;">
            <h3 style="margin: 0 0 15px 0; color: #2EB872; font-size: 20px; font-weight: bold;">✅ Inscripción Confirmada</h3>
            <p style="margin: 8px 0; color: #505050; font-size: 15px;"><strong style="color: #101010;">Membresía:</strong> {membresia}</p>
            <p style="margin: 8px 0; color: #505050; font-size: 15px;"><strong style="color: #101010;">Fecha inicio:</strong> {fecha_inicio}</p>
            <p style="margin: 8px 0; color: #505050; font-size: 15px;"><strong style="color: #101010;">Válida hasta:</strong> {fecha_vencimiento}</p>
            <p style="margin: 8px 0; color: #505050; font-size: 15px;"><strong style="color: #101010;">Precio pagado:</strong> ${precio}</p>
        </div>
        
        <!-- TIPS -->
        <h3 style="color: #101010; margin: 30px 0 20px 0; font-size: 20px;">💪 Consejos para empezar:</h3>
        <ul style="color: #505050; font-size: 15px; line-height: 1.9; padding-left: 25px; margin: 0 0 30px 0;">
            <li style="margin-bottom: 10px;">Llega 10-15 minutos antes para prepararte</li>
            <li style="margin-bottom: 10px;">Trae tu botella de agua y toalla</li>
            <li style="margin-bottom: 10px;">Consulta con nuestros instructores cualquier duda</li>
            <li style="margin-bottom: 10px;">Escucha a tu cuerpo y avanza a tu ritmo</li>
            <li>La constancia es clave para el éxito</li>
        </ul>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 35px 0;">
            <a href="https://wa.me/56950963143?text=Hola%2C%20soy%20nuevo%20miembro%20de%20PROGYM" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 16px 40px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">💬 Únete a WhatsApp</a>
        </div>
        
        <p style="color: #505050; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0; text-align: center;">
            ¿Dudas? Llámanos al <strong style="color: #101010;">+56 9 5096 3143</strong> o visítanos en recepción
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>
            📧 progymlosangeles@gmail.com | 📞 +56 9 5096 3143
        </p>
        <p style="margin: 0; font-size: 13px;">
            <a href="https://www.instagram.com/progym_losangeles" style="color: #E0001A; text-decoration: none; font-weight: bold;">📸 @progym_losangeles</a>
        </p>
        <p style="margin: 15px 0 0 0; font-size: 11px; color: #808080;">
            Este es un correo automático, por favor no responder directamente.
        </p>
    </div>
</div>',
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'pago_pendiente',
                'nombre' => 'Pago Pendiente/Parcial - Recordatorio',
                'descripcion' => 'Se envía cada 15 días cuando hay pago pendiente o parcial',
                'asunto_email' => '💳 {nombre}, tienes un pago pendiente en PROGYM',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 50px 20px; text-align: center;">
        <img src="https://raw.githubusercontent.com/PaNcHoMaLOsO/estoicosgym/main/public/images/progym_logo.svg" alt="PROGYM" style="max-width: 220px; height: auto;">
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 40px 30px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 20px 0; font-size: 24px; font-weight: bold;">Hola {nombre} 👋</h2>
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 0 0 25px 0;">
            Te recordamos que tienes un pago pendiente por tu membresía <strong style="color: #101010;">{membresia}</strong>.
        </p>
        
        <!-- DETALLE PAGO -->
        <div style="background: #FFFFFF; border-left: 6px solid #E0001A; padding: 25px; margin: 30px 0; border-radius: 6px; border: 1px solid #C7C7C7; border-left: 6px solid #E0001A;">
            <h3 style="margin: 0 0 12px 0; color: #101010; font-size: 20px; font-weight: bold;">💰 Monto Pendiente</h3>
            <p style="margin: 0; color: #E0001A; font-size: 28px; font-weight: bold;">${monto_pendiente}</p>
            <p style="margin: 12px 0 0 0; color: #505050; font-size: 14px;">Monto total: <strong style="color: #101010;">${monto_total}</strong></p>
        </div>
        
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 25px 0;">
            Regulariza tu pago para continuar entrenando sin interrupciones. Tu membresía vence el <strong style="color: #E0001A;">{fecha_vencimiento}</strong>.
        </p>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 35px 0;">
            <a href="https://wa.me/56950963143?text=Hola%2C%20quiero%20pagar%20mi%20membres%C3%ADa" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 16px 40px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">💬 Contactar para Pagar</a>
        </div>
        
        <p style="color: #505050; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0; text-align: center;">
            También puedes acercarte a recepción o llamarnos al <strong style="color: #101010;">+56 9 5096 3143</strong>
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>
            📧 progymlosangeles@gmail.com | 📞 +56 9 5096 3143
        </p>
        <p style="margin: 0; font-size: 13px;">
            <a href="https://www.instagram.com/progym_losangeles" style="color: #E0001A; text-decoration: none; font-weight: bold;">📸 @progym_losangeles</a>
        </p>
        <p style="margin: 15px 0 0 0; font-size: 11px; color: #808080;">
            Este es un correo automático, por favor no responder directamente.
        </p>
    </div>
</div>',
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar solo si no existen
        foreach ($tipos as $tipo) {
            DB::table('tipo_notificaciones')->updateOrInsert(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}
