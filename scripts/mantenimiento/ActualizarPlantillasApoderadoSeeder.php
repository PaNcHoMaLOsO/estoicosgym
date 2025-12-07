<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoNotificacion;

class ActualizarPlantillasApoderadoSeeder extends Seeder
{
    public function run(): void
    {
        // Actualizar plantilla "Membresía por Vencer" con soporte para apoderados
        TipoNotificacion::where('codigo', 'membresia_por_vencer')->update([
            'asunto_email' => '⏰ {nombre}, la membresía de {nombre_cliente} en PROGYM vence en {dias_restantes} días',
            'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 30px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;">
            <span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span>
        </h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 25px 20px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">Hola {nombre} 👋</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            La membresía <strong style="color: #101010;">{membresia}</strong> vence en <strong style="color: #FFC107;">{dias_restantes} días</strong>.
        </p>
        
        <!-- RECORDATORIO -->
        <div style="background: #fffbf0; border: 2px solid #FFC107; padding: 18px; margin: 20px 0; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0 0 8px 0; color: #101010; font-size: 20px; font-weight: bold;">⏳ Vence: {fecha_vencimiento}</h3>
            <p style="margin: 0; color: #505050; font-size: 14px;">Renueva para seguir entrenando sin interrupciones 💪</p>
        </div>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 20px 0 15px 0;">
            <a href="tel:+56950963143" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">📞 Llámanos: +56 9 5096 3143</a>
        </div>
        
        <p style="color: #505050; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">
            También en recepción: progymlosangeles@gmail.com
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 20px; text-align: center;">
        <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 10px 0; font-size: 12px; line-height: 1.5;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym/data=!4m2!3m1!1s0x0:0xcd2de1ceea2bbcf1?sa=X&ved=1t:2428&ictx=111" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>
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
        ]);

        // Actualizar plantilla "Membresía Vencida" con soporte para apoderados
        TipoNotificacion::where('codigo', 'membresia_vencida')->update([
            'asunto_email' => '❗ {nombre}, la membresía de {nombre_cliente} en PROGYM ha vencido',
            'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 30px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;">
            <span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span>
        </h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 25px 20px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">Hola {nombre} 👋</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            La membresía <strong style="color: #101010;">{membresia}</strong> venció el <strong style="color: #E0001A;">{fecha_vencimiento}</strong>. ¡Los extrañamos!
        </p>
        
        <!-- ALERTA VENCIDA (ROJO) -->
        <div style="background: #fff5f5; border-left: 4px solid #E0001A; padding: 18px; margin: 20px 0; border-radius: 6px;">
            <h3 style="margin: 0 0 8px 0; color: #E0001A; font-size: 18px; font-weight: bold;">⚠️ Membresía Vencida</h3>
            <p style="margin: 0; color: #505050; font-size: 14px;">Para reactivar, comunícate con nosotros</p>
        </div>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 20px 0 15px 0;">
            <a href="tel:+56950963143" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">📞 Renovar Ahora: +56 9 5096 3143</a>
        </div>
        
        <p style="color: #505050; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">
            También puedes escribirnos a: progymlosangeles@gmail.com
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 20px; text-align: center;">
        <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 10px 0; font-size: 12px; line-height: 1.5;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym/data=!4m2!3m1!1s0x0:0xcd2de1ceea2bbcf1?sa=X&ved=1t:2428&ictx=111" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>
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
        ]);

        // Actualizar plantilla "Bienvenida" con soporte para apoderados
        TipoNotificacion::where('codigo', 'bienvenida')->update([
            'asunto_email' => '🎉 Bienvenido/a {nombre} a PROGYM - ¡Comienza tu transformación!',
            'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 30px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;">
            <span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span>
        </h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 25px 20px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 26px; font-weight: bold; text-align: center;">¡Bienvenido/a a la familia PROGYM! 🎉</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0; text-align: center;">
            Hola <strong style="color: #101010;">{nombre}</strong>, estamos felices de tenerte con nosotros 💪
        </p>
        
        <!-- INFO MEMBRESÍA -->
        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 20px; margin: 20px 0; border-radius: 10px; border: 2px solid #101010;">
            <h3 style="margin: 0 0 12px 0; color: #101010; font-size: 18px; font-weight: bold; text-align: center;">📋 Detalles de tu Membresía</h3>
            <table style="width: 100%; color: #505050; font-size: 14px;">
                <tr>
                    <td style="padding: 8px 0;"><strong>Membresía:</strong></td>
                    <td style="padding: 8px 0; text-align: right;">{membresia}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Fecha Inicio:</strong></td>
                    <td style="padding: 8px 0; text-align: right;">{fecha_inicio}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Fecha Vencimiento:</strong></td>
                    <td style="padding: 8px 0; text-align: right; color: #E0001A; font-weight: bold;">{fecha_vencimiento}</td>
                </tr>
            </table>
        </div>
        
        <!-- MENSAJE MOTIVACIONAL -->
        <div style="background: #fff5f5; border-left: 4px solid #E0001A; padding: 18px; margin: 20px 0; border-radius: 6px;">
            <p style="margin: 0; color: #505050; font-size: 14px; line-height: 1.6; text-align: center;">
                <strong style="color: #101010;">💪 Tu transformación comienza hoy</strong><br>
                Entrena con pasión, constancia y dedicación
            </p>
        </div>
        
        <p style="color: #505050; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">
            ¿Dudas? Escríbenos: progymlosangeles@gmail.com o llámanos al +56 9 5096 3143
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 20px; text-align: center;">
        <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 10px 0; font-size: 12px; line-height: 1.5;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym/data=!4m2!3m1!1s0x0:0xcd2de1ceea2bbcf1?sa=X&ved=1t:2428&ictx=111" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>
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
        ]);

        $this->command->info('✅ Plantillas actualizadas con soporte para apoderados');
    }
}
