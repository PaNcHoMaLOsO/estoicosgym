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
                'descripcion' => 'Se envía X días antes de que venza la membresía (soporte apoderados)',
                'asunto_email' => '⏰ {nombre}, la membresía de {nombre_cliente} en PROGYM vence en {dias_restantes} días',
                'plantilla_email' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;"><div style="background: #101010; color: white; padding: 30px 20px; text-align: center;"><h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;"><span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span></h1><p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p></div><div style="padding: 25px 20px; background: #FFFFFF;"><h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">Hola {nombre} 👋</h2><p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">La membresía <strong style="color: #101010;">{membresia}</strong> vence en <strong style="color: #FFC107;">{dias_restantes} días</strong>.</p><div style="background: #fffbf0; border: 2px solid #FFC107; padding: 18px; margin: 20px 0; border-radius: 8px; text-align: center;"><h3 style="margin: 0 0 8px 0; color: #101010; font-size: 20px; font-weight: bold;">⏳ Vence: {fecha_vencimiento}</h3><p style="margin: 0; color: #505050; font-size: 14px;">Renueva para seguir entrenando sin interrupciones 💪</p></div><div style="text-align: center; margin: 20px 0 15px 0;"><a href="tel:+56950963143" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">📞 Llámanos: +56 9 5096 3143</a></div><p style="color: #505050; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">También en recepción: progymlosangeles@gmail.com</p></div><div style="background: #101010; color: #C7C7C7; padding: 20px; text-align: center;"><p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p><p style="margin: 0 0 10px 0; font-size: 12px; line-height: 1.5;">📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym/data=!4m2!3m1!1s0x0:0xcd2de1ceea2bbcf1?sa=X&ved=1t:2428&ictx=111" style="color: #C7C7C7; text-decoration: none;">Ver ubicación en Google Maps</a><br>📧 progymlosangeles@gmail.com | 📞 +56 9 5096 3143</p><p style="margin: 0; font-size: 13px;"><a href="https://www.instagram.com/progym_losangeles" style="color: #E0001A; text-decoration: none; font-weight: bold;">📸 @progym_losangeles</a></p><p style="margin: 15px 0 0 0; font-size: 11px; color: #808080;">Este es un correo automático, por favor no responder directamente.</p></div></div>',
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
            Tu membresía <strong style="color: #101010;">{membresia}</strong> venció el <strong style="color: #E0001A;">{fecha_vencimiento}</strong>. ¡Te extrañamos!
        </p>
        
        <!-- ALERTA VENCIDA (ROJO) -->
        <div style="background: #fff5f5; border-left: 4px solid #E0001A; padding: 18px; margin: 20px 0; border-radius: 6px;">
            <h3 style="margin: 0 0 8px 0; color: #E0001A; font-size: 18px; font-weight: bold;">⚠️ Membresía Vencida</h3>
            <p style="margin: 0; color: #505050; font-size: 14px;">Renueva para seguir entrenando 💪</p>
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
    <div style="background: #101010; color: #C7C7C7; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6;">
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
        <h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;">
            <span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span>
        </h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p>
    </div>
    
    <!-- BIENVENIDA -->
    <div style="padding: 25px 20px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">¡Bienvenido/a {nombre}! 🎉</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
            Tu inscripción ha sido confirmada. ¡Comienza tu transformación hoy!
        </p>
        
        <!-- CONFIRMACIÓN INSCRIPCIÓN -->
        <div style="background: #f8fff8; border: 2px solid #2EB872; padding: 18px; margin: 20px 0; border-radius: 8px;">
            <h3 style="margin: 0 0 12px 0; color: #2EB872; font-size: 18px; font-weight: bold;">✅ Inscripción Confirmada</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px; width: 45%;"><strong style="color: #101010;">Membresía:</strong></td>
                    <td style="padding: 5px 0; color: #101010; font-size: 14px; font-weight: 600;">{membresia}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px;"><strong style="color: #101010;">Valor membresía:</strong></td>
                    <td style="padding: 5px 0; color: #101010; font-size: 15px; font-weight: 600;">${monto_total}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px;"><strong style="color: #101010;">Fecha inicio:</strong></td>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px;">{fecha_inicio}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px;"><strong style="color: #101010;">Válida hasta:</strong></td>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px;">{fecha_vencimiento}</td>
                </tr>
            </table>
            
            <!-- DETALLES DE PAGO -->
            <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #c7e6c7;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px 0; color: #505050; font-size: 14px; width: 45%;"><strong style="color: #101010;">Tipo de pago:</strong></td>
                        <td style="padding: 5px 0; color: #505050; font-size: 14px;">{tipo_pago}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #505050; font-size: 14px;"><strong style="color: #101010;">Monto pagado:</strong></td>
                        <td style="padding: 5px 0; color: #2EB872; font-size: 16px; font-weight: bold;">${monto_pagado}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #505050; font-size: 14px;"><strong style="color: #101010;">Saldo pendiente:</strong></td>
                        <td style="padding: 5px 0; color: {color_saldo}; font-size: 15px; font-weight: 600;">${monto_pendiente}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- HORARIOS -->
        <div style="background: #f5f5f5; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #2EB872;">
            <p style="margin: 0 0 8px 0; color: #101010; font-size: 15px; font-weight: bold;">⏰ Horarios de atención:</p>
            <p style="margin: 3px 0; color: #505050; font-size: 14px;">📅 Lunes a Viernes: 07:00 - 22:30 hrs</p>
            <p style="margin: 3px 0; color: #505050; font-size: 14px;">📅 Sábado: 10:00 - 14:00 hrs</p>
            <p style="margin: 3px 0 0 0; color: #E0001A; font-size: 14px; font-weight: 600;">🚫 Domingo: Cerrado</p>
        </div>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 20px 0 15px 0;">
            <a href="tel:+56950963143" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold; box-shadow: 0 4px 8px rgba(224, 0, 26, 0.3);">📞 Llámanos: +56 9 5096 3143</a>
        </div>
        
        <p style="color: #505050; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">
            ¿Dudas? Visítanos en recepción o escríbenos a <strong style="color: #101010;">progymlosangeles@gmail.com</strong>
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: #C7C7C7; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6;">
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
            Tienes un pago pendiente por tu membresía <strong style="color: #101010;">{membresia}</strong>.
        </p>
        
        <!-- DETALLE PAGO -->
        <div style="background: #FFFFFF; border-left: 6px solid #E0001A; padding: 18px; margin: 20px 0; border-radius: 6px; border: 1px solid #C7C7C7; border-left: 6px solid #E0001A;">
            <h3 style="margin: 0 0 8px 0; color: #101010; font-size: 18px; font-weight: bold;">💰 Saldo Pendiente</h3>
            <p style="margin: 0; color: #E0001A; font-size: 26px; font-weight: bold;">${monto_pendiente}</p>
            <p style="margin: 8px 0 0 0; color: #505050; font-size: 13px;">Total: <strong style="color: #101010;">${monto_total}</strong> • Vence: <strong style="color: #E0001A;">{fecha_vencimiento}</strong></p>
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
    <div style="background: #101010; color: #C7C7C7; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #FFFFFF;">PROGYM - Los Ángeles</p>
        <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6;">
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
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'pausa_inscripcion',
                'nombre' => 'Pausa de Inscripción - Confirmación',
                'descripcion' => 'Se envía cuando el cliente pausa temporalmente su membresía',
                'asunto_email' => '⏸️ {nombre}, tu membresía en PROGYM ha sido pausada',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 50px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;">
            <span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span>
        </h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 25px 20px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">Hola {nombre} 👋</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            Hemos pausado tu membresía <strong style="color: #101010;">{membresia}</strong>.
        </p>
        
        <!-- INFO PAUSA (AMARILLO) -->
        <div style="background: #fffbf0; border-left: 4px solid #FFC107; padding: 18px; margin: 20px 0; border-radius: 6px;">
            <h3 style="margin: 0 0 10px 0; color: #FFC107; font-size: 18px; font-weight: bold;">⏸️ Pausada</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #505050; font-size: 15px;">Fecha de pausa:</td>
                    <td style="padding: 8px 0; color: #101010; font-weight: bold; font-size: 15px; text-align: right;">{fecha_pausa}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #505050; font-size: 15px;">Fecha de reactivación:</td>
                    <td style="padding: 8px 0; color: #E0001A; font-weight: bold; font-size: 15px; text-align: right;">{fecha_reactivacion}</td>
                </tr>
            </table>
        </div>
        
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 25px 0;">
            Durante este período tu acceso al gimnasio estará <strong style="color: #E0001A;">suspendido temporalmente</strong>. La fecha de vencimiento de tu membresía se extenderá automáticamente según los días pausados.
        </p>
        
        <div style="background: #F5F5F5; border-radius: 6px; padding: 18px; margin: 20px 0;">
            <h3 style="margin: 0 0 10px 0; color: #101010; font-size: 16px; font-weight: bold;">📋 Información Importante</h3>
            <p style="color: #505050; font-size: 15px; line-height: 1.7; margin: 0;">✅ Tu cupo quedará reservado durante la pausa</p>
            <p style="color: #505050; font-size: 15px; line-height: 1.7; margin: 10px 0 0 0;">✅ Los días pausados se agregarán a tu membresía</p>
            <p style="color: #505050; font-size: 15px; line-height: 1.7; margin: 10px 0 0 0;">✅ Podrás reactivar antes de la fecha programada</p>
        </div>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 20px 0 15px 0;">
            <a href="tel:+56950963143" style="display: inline-block; background: #FFC107; color: #101010; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">📞 Llámanos: +56 9 5096 3143</a>
        </div>
        
        <p style="color: #707070; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0; text-align: center;">
            ¿Necesitas reactivar antes? Escríbenos y con gusto te ayudamos. 💪
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: white; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold;">PROGYM Los Ángeles</p>
        <p style="margin: 0; font-size: 13px;">
            📧 <a href="mailto:progymlosangeles@gmail.com" style="color: #E0001A; text-decoration: none;">progymlosangeles@gmail.com</a> | 
            📞 <a href="tel:+56950963143" style="color: #E0001A; text-decoration: none;">+56 9 5096 3143</a>
        </p>
        <p style="margin: 10px 0 0 0; font-size: 13px;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym/@-37.4697593,-72.3540993,17z/data=!3m1!4b1!4m6!3m5!1s0x966bdc168e646463:0xcd2de1ceea2bbcf1!8m2!3d-37.4697593!4d-72.3540993!16s%2Fg%2F11j8kvn_sx?entry=ttu&g_ep=EgoyMDI0MTExMy4xIKXMDSoASAFQAw%3D%3D" style="color: #2EB872; text-decoration: none;">Ubicación en Google Maps</a>
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
                'codigo' => 'activacion_inscripcion',
                'nombre' => 'Activación de Inscripción - Confirmación',
                'descripcion' => 'Se envía cuando el cliente reactiva su membresía pausada',
                'asunto_email' => '▶️ {nombre}, tu membresía en PROGYM ha sido reactivada',
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
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">¡Bienvenido de vuelta, {nombre}! 🎉</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            Tu membresía <strong style="color: #101010;">{membresia}</strong> ha sido <strong style="color: #2EB872;">reactivada</strong>. ¡Te esperamos!
        </p>
        
        <!-- INFO ACTIVACION (VERDE) -->
        <div style="background: #f0fdf4; border-left: 4px solid #2EB872; padding: 18px; margin: 20px 0; border-radius: 6px;">
            <h3 style="margin: 0 0 10px 0; color: #2EB872; font-size: 18px; font-weight: bold;">▶️ Activa</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #505050; font-size: 15px;">Fecha de activación:</td>
                    <td style="padding: 8px 0; color: #2EB872; font-weight: bold; font-size: 15px; text-align: right;">{fecha_activacion}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #505050; font-size: 15px;">Nueva fecha de vencimiento:</td>
                    <td style="padding: 8px 0; color: #101010; font-weight: bold; font-size: 15px; text-align: right;">{fecha_vencimiento}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #505050; font-size: 15px;">Días extendidos:</td>
                    <td style="padding: 8px 0; color: #E0001A; font-weight: bold; font-size: 15px; text-align: right;">{dias_pausados} días</td>
                </tr>
            </table>
        </div>
        
        <p style="color: #505050; font-size: 16px; line-height: 1.7; margin: 25px 0;">
            Tu acceso al gimnasio está <strong style="color: #2EB872;">disponible inmediatamente</strong>. Los días que estuviste en pausa se han agregado a tu fecha de vencimiento.
        </p>
        
        <!-- HORARIOS COMPACTOS -->
        <div style="background: #f5f5f5; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #2EB872;">
            <p style="margin: 0 0 8px 0; font-size: 15px; font-weight: bold;">⏰ Horarios de atención:</p>
            <p style="margin: 3px 0; font-size: 14px; color: #505050;">📅 Lunes a Viernes: 07:00 - 22:30 hrs</p>
            <p style="margin: 3px 0; font-size: 14px; color: #505050;">📅 Sábado: 10:00 - 14:00 hrs</p>
            <p style="margin: 3px 0 0 0; color: #E0001A; font-size: 14px; font-weight: 600;">🚫 Domingo: Cerrado</p>
        </div>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 20px 0 15px 0;">
            <a href="tel:+56950963143" style="display: inline-block; background: #2EB872; color: white; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">📞 Llámanos: +56 9 5096 3143</a>
        </div>
        
        <p style="color: #707070; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0; text-align: center;">
            ¡Te esperamos con todo! 🏋️‍♂️ <strong style="color: #E0001A;">#SeguimosEntrando</strong>
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: white; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold;">PROGYM Los Ángeles</p>
        <p style="margin: 0; font-size: 13px;">
            📧 <a href="mailto:progymlosangeles@gmail.com" style="color: #E0001A; text-decoration: none;">progymlosangeles@gmail.com</a> | 
            📞 <a href="tel:+56950963143" style="color: #E0001A; text-decoration: none;">+56 9 5096 3143</a>
        </p>
        <p style="margin: 10px 0 0 0; font-size: 13px;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym/@-37.4697593,-72.3540993,17z/data=!3m1!4b1!4m6!3m5!1s0x966bdc168e646463:0xcd2de1ceea2bbcf1!8m2!3d-37.4697593!4d-72.3540993!16s%2Fg%2F11j8kvn_sx?entry=ttu&g_ep=EgoyMDI0MTExMy4xIKXMDSoASAFQAw%3D%3D" style="color: #2EB872; text-decoration: none;">Ubicación en Google Maps</a>
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
                'codigo' => 'pago_completado',
                'nombre' => 'Pago Completado - Confirmación',
                'descripcion' => 'Se envía cuando el cliente completa el pago de un saldo pendiente o parcial',
                'asunto_email' => '✅ {nombre}, tu pago en PROGYM ha sido confirmado',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #101010; color: white; padding: 50px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;">
            <span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span>
        </h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 25px 20px; background: #FFFFFF;">
        <h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">¡Pago Registrado, {nombre}! ✅</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
            Hemos registrado tu pago de <strong style="color: #2EB872;">${monto_ultimo_pago}</strong> para la membresía <strong style="color: #101010;">{membresia}</strong>.
        </p>
        
        <!-- CONFIRMACIÓN DE PAGO -->
        <div style="background: #f0fdf4; border-left: 4px solid #2EB872; padding: 18px; margin: 20px 0; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px; width: 45%;">Fecha de pago:</td>
                    <td style="padding: 5px 0; color: #101010; font-weight: bold; font-size: 14px; text-align: right;">{fecha_pago}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px;">Pago de hoy:</td>
                    <td style="padding: 5px 0; color: #2EB872; font-weight: bold; font-size: 15px; text-align: right;">${monto_ultimo_pago}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px;">Total pagado:</td>
                    <td style="padding: 5px 0; color: #101010; font-weight: bold; font-size: 15px; text-align: right;">${total_pagado}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #505050; font-size: 14px;">Valor total:</td>
                    <td style="padding: 5px 0; color: #101010; font-size: 14px; text-align: right;">${precio_base}</td>
                </tr>
                <tr style="border-top: 2px solid #2EB872;">
                    <td style="padding: 10px 0 5px 0; color: #505050; font-size: 15px; font-weight: bold;">Saldo:</td>
                    <td style="padding: 10px 0 5px 0; color: {color_saldo}; font-weight: bold; font-size: 17px; text-align: right;">${saldo_pendiente}</td>
                </tr>
            </table>
        </div>
        
        <!-- MENSAJE SEGÚN ESTADO -->
        <div style="background: {color_saldo}; background: #f0fdf4; border-radius: 6px; padding: 15px; margin: 20px 0; text-align: center;">
            <p style="color: #2EB872; font-size: 16px; line-height: 1.5; margin: 0; font-weight: bold;">
                🎉 {mensaje_estado}
            </p>
        </div>
        
        <div style="background: #f9fafb; border-radius: 6px; padding: 15px; margin: 20px 0; border: 1px solid #e5e7eb;">
            <p style="color: #505050; font-size: 14px; margin: 3px 0;">📅 Vence: <strong style="color: #101010;">{fecha_vencimiento}</strong></p>
            <p style="color: #505050; font-size: 14px; margin: 3px 0;">💳 Pagos: <strong style="color: #101010;">{cantidad_pagos}</strong></p>
        </div>
        
        <!-- CTA BUTTON -->
        <div style="text-align: center; margin: 20px 0 15px 0;">
            <a href="tel:+56950963143" style="display: inline-block; background: #2EB872; color: white; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">📞 Llámanos: +56 9 5096 3143</a>
        </div>
        
        <p style="color: #707070; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">
            Conserva este correo como comprobante 💪
        </p>
    </div>
    
    <!-- FOOTER -->
    <div style="background: #101010; color: white; padding: 30px 20px; text-align: center;">
        <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold;">PROGYM Los Ángeles</p>
        <p style="margin: 0; font-size: 13px;">
            📧 <a href="mailto:progymlosangeles@gmail.com" style="color: #E0001A; text-decoration: none;">progymlosangeles@gmail.com</a> | 
            📞 <a href="tel:+56950963143" style="color: #E0001A; text-decoration: none;">+56 9 5096 3143</a>
        </p>
        <p style="margin: 10px 0 0 0; font-size: 13px;">
            📍 <a href="https://www.google.com/maps/place/Gimnasio+ProGym/@-37.4697593,-72.3540993,17z/data=!3m1!4b1!4m6!3m5!1s0x966bdc168e646463:0xcd2de1ceea2bbcf1!8m2!3d-37.4697593!4d-72.3540993!16s%2Fg%2F11j8kvn_sx?entry=ttu&g_ep=EgoyMDI0MTExMy4xIKXMDSoASAFQAw%3D%3D" style="color: #2EB872; text-decoration: none;">Ubicación en Google Maps</a>
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

