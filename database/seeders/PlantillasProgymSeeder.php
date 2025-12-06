<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SEEDER MAESTRO: Plantillas de notificaciones PROGYM
 * 
 * Plantillas HTML probadas y validadas de storage/app/test_emails/
 * - Diseño: PRO (blanco) + GYM (rojo) en fondo negro #101010
 * - Sin imágenes, solo texto HTML
 * - Soporte para apoderados en plantillas aplicables
 */
class PlantillasProgymSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📧 Cargando plantillas PROGYM...');

        // PLANTILLA 1: MEMBRESÍA POR VENCER
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'membresia_por_vencer'],
            [
                'nombre' => 'Membresía por Vencer',
                'descripcion' => 'Recordatorio X días antes del vencimiento (soporte apoderados)',
                'asunto_email' => '⏰ {nombre}, la membresía de {nombre_cliente} vence en {dias_restantes} días',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/06_membresia_por_vencer.html')),
                'dias_anticipacion' => 5,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 2: MEMBRESÍA VENCIDA
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'membresia_vencida'],
            [
                'nombre' => 'Membresía Vencida',
                'descripcion' => 'Notificación cuando la membresía ha vencido (soporte apoderados)',
                'asunto_email' => '❗ {nombre}, la membresía de {nombre_cliente} en PROGYM ha vencido',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/07_membresia_vencida.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 3: BIENVENIDA
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'bienvenida'],
            [
                'nombre' => 'Bienvenida',
                'descripcion' => 'Email de bienvenida al inscribirse (incluye detalles de pago)',
                'asunto_email' => '🎉 Bienvenido/a {nombre} a PROGYM - ¡Comienza tu transformación!',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/01_bienvenida.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 4: PAGO COMPLETADO
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'pago_completado'],
            [
                'nombre' => 'Pago Completado',
                'descripcion' => 'Confirmación cuando se completa el pago de la membresía',
                'asunto_email' => '✅ {nombre}, tu pago ha sido registrado - PROGYM',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/05_pago_completado.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 5: PAUSA INSCRIPCIÓN
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'pausa_inscripcion'],
            [
                'nombre' => 'Pausa de Inscripción',
                'descripcion' => 'Confirmación cuando el cliente pausa su membresía',
                'asunto_email' => '⏸️ {nombre}, tu membresía en PROGYM ha sido pausada',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/09_pausa_inscripcion.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 6: ACTIVACIÓN INSCRIPCIÓN
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'activacion_inscripcion'],
            [
                'nombre' => 'Activación de Inscripción',
                'descripcion' => 'Confirmación cuando se reactiva la membresía pausada',
                'asunto_email' => '▶️ {nombre}, ¡Bienvenido de vuelta a PROGYM!',
                'plantilla_email' => file_get_contents(storage_path('app/test_emails/10_activacion_inscripcion.html')),
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 7: PAGO PENDIENTE
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'pago_pendiente'],
            [
                'nombre' => 'Pago Pendiente',
                'descripcion' => 'Recordatorio de saldo pendiente',
                'asunto_email' => '💳 {nombre}, tienes un saldo pendiente en PROGYM',
                'plantilla_email' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;"><div style="background: #101010; color: white; padding: 30px 20px; text-align: center;"><h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;"><span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span></h1><p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p></div><div style="padding: 25px 20px; background: #FFFFFF;"><h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">Hola {nombre} 👋</h2><p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">Tienes un saldo pendiente por tu membresía <strong style="color: #101010;">{membresia}</strong>.</p><div style="background: #fff5f5; border-left: 4px solid #E0001A; padding: 18px; margin: 20px 0; border-radius: 4px;"><h3 style="margin: 0 0 8px 0; color: #E0001A; font-size: 18px; font-weight: bold;">💰 Saldo Pendiente</h3><p style="margin: 0; color: #E0001A; font-size: 26px; font-weight: bold;">${monto_pendiente}</p><p style="margin: 8px 0 0 0; color: #505050; font-size: 13px;">Total: ${monto_total} • Vence: {fecha_vencimiento}</p></div><div style="text-align: center; margin: 20px 0 15px 0;"><a href="tel:+56950963143" style="display: inline-block; background: #E0001A; color: #FFFFFF; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold;">📞 Llámanos: +56 9 5096 3143</a></div><p style="color: #505050; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">También en recepción: progymlosangeles@gmail.com</p></div><div style="background: #101010; color: white; padding: 20px; text-align: center;"><p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold;">PROGYM - Los Ángeles</p><p style="margin: 0; font-size: 13px;">📧 <a href="mailto:progymlosangeles@gmail.com" style="color: #E0001A; text-decoration: none;">progymlosangeles@gmail.com</a> | 📞 <a href="tel:+56950963143" style="color: #E0001A; text-decoration: none;">+56 9 5096 3143</a></p><p style="margin: 10px 0 0 0; font-size: 13px;"><a href="https://www.instagram.com/progym_losangeles" style="color: #E0001A; text-decoration: none; font-weight: bold;">📸 @progym_losangeles</a></p><p style="margin: 15px 0 0 0; font-size: 11px; color: #808080;">Este es un correo automático, por favor no responder directamente.</p></div></div>',
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // PLANTILLA 8: RENOVACIÓN
        DB::table('tipo_notificaciones')->updateOrInsert(
            ['codigo' => 'renovacion'],
            [
                'nombre' => 'Renovación Exitosa',
                'descripcion' => 'Confirmación de renovación de membresía',
                'asunto_email' => '🎊 {nombre}, tu membresía en PROGYM ha sido renovada',
                'plantilla_email' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;"><div style="background: #101010; color: white; padding: 30px 20px; text-align: center;"><h1 style="margin: 0; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;"><span style="color: #FFFFFF;">PRO</span><span style="color: #E0001A;">GYM</span></h1><p style="margin: 10px 0 0 0; font-size: 14px; color: #C7C7C7; letter-spacing: 2px;">LOS ÁNGELES</p></div><div style="padding: 25px 20px; background: #FFFFFF;"><h2 style="color: #101010; margin: 0 0 15px 0; font-size: 22px; font-weight: bold;">Hola {nombre} 👋</h2><p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">Tu membresía <strong style="color: #101010;">{membresia}</strong> ha sido <strong style="color: #2EB872;">renovada exitosamente</strong>.</p><div style="background: #f0fdf4; border: 2px solid #2EB872; padding: 18px; margin: 20px 0; border-radius: 8px; text-align: center;"><h3 style="margin: 0 0 8px 0; color: #2EB872; font-size: 20px; font-weight: bold;">✅ Renovación Exitosa</h3><p style="margin: 0; color: #505050; font-size: 14px;">Nueva fecha de vencimiento: <strong style="color: #101010;">{fecha_vencimiento}</strong></p></div><div style="text-align: center; margin: 20px 0 15px 0;"><a href="tel:+56950963143" style="display: inline-block; background: #2EB872; color: white; padding: 14px 35px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold;">📞 Llámanos: +56 9 5096 3143</a></div><p style="color: #505050; font-size: 13px; line-height: 1.5; margin: 15px 0 0 0; text-align: center;">También en recepción: progymlosangeles@gmail.com</p></div><div style="background: #101010; color: white; padding: 20px; text-align: center;"><p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold;">PROGYM - Los Ángeles</p><p style="margin: 0; font-size: 13px;">📧 <a href="mailto:progymlosangeles@gmail.com" style="color: #E0001A; text-decoration: none;">progymlosangeles@gmail.com</a> | 📞 <a href="tel:+56950963143" style="color: #E0001A; text-decoration: none;">+56 9 5096 3143</a></p><p style="margin: 10px 0 0 0; font-size: 13px;"><a href="https://www.instagram.com/progym_losangeles" style="color: #E0001A; text-decoration: none; font-weight: bold;">📸 @progym_losangeles</a></p><p style="margin: 15px 0 0 0; font-size: 11px; color: #808080;">Este es un correo automático, por favor no responder directamente.</p></div></div>',
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✅ Plantillas PROGYM cargadas (8 plantillas - notificacion_manual ya existe)');
    }
}
