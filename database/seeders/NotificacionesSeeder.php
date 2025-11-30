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
                'nombre' => 'Membresía Próxima a Vencer',
                'descripcion' => 'Se envía cuando la membresía está por vencer en los próximos días',
                'asunto_email' => '⏰ {nombre}, tu membresía en Estoicos Gym vence pronto',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">🏋️ Estoicos Gym</h1>
    </div>
    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #1a1a2e; margin-top: 0;">Hola {nombre} 👋</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            Te recordamos que tu membresía <strong>{membresia}</strong> vence el <strong>{fecha_vencimiento}</strong>.
        </p>
        <div style="background: #fff3cd; border-left: 4px solid #f0a500; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <p style="margin: 0; color: #856404;">
                <strong>⏳ Te quedan {dias_restantes} días</strong> para renovar y seguir entrenando sin interrupciones.
            </p>
        </div>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            Acércate a recepción o contáctanos para renovar tu plan y mantener tu progreso. 💪
        </p>
        <div style="text-align: center; margin-top: 30px;">
            <a href="#" style="background: #e94560; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold;">Renovar Ahora</a>
        </div>
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        <p style="color: #888; font-size: 12px; text-align: center;">
            Este es un correo automático de Estoicos Gym. Si tienes dudas, contáctanos.
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
                'nombre' => 'Membresía Vencida',
                'descripcion' => 'Se envía el día que la membresía vence',
                'asunto_email' => '❌ {nombre}, tu membresía en Estoicos Gym ha vencido',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">🏋️ Estoicos Gym</h1>
    </div>
    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #1a1a2e; margin-top: 0;">Hola {nombre} 👋</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            Te informamos que tu membresía <strong>{membresia}</strong> ha vencido el día <strong>{fecha_vencimiento}</strong>.
        </p>
        <div style="background: #f8d7da; border-left: 4px solid #e94560; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <p style="margin: 0; color: #721c24;">
                <strong>⚠️ Tu membresía está vencida.</strong> No podrás acceder al gimnasio hasta renovar.
            </p>
        </div>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            ¡Te extrañamos! Renueva hoy y retoma tu entrenamiento. Recuerda que cada día cuenta para alcanzar tus metas. 🎯
        </p>
        <div style="text-align: center; margin-top: 30px;">
            <a href="#" style="background: #00bf8e; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold;">Renovar Mi Membresía</a>
        </div>
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        <p style="color: #888; font-size: 12px; text-align: center;">
            Este es un correo automático de Estoicos Gym. Si tienes dudas, contáctanos.
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
                'nombre' => 'Bienvenida Nuevo Cliente',
                'descripcion' => 'Se envía cuando un cliente se inscribe por primera vez',
                'asunto_email' => '🎉 ¡Bienvenido a Estoicos Gym, {nombre}!',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">🏋️ Estoicos Gym</h1>
    </div>
    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #1a1a2e; margin-top: 0;">¡Bienvenido/a {nombre}! 🎉</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            Nos alegra que te hayas unido a la familia <strong>Estoicos Gym</strong>. Estamos emocionados de acompañarte en tu camino hacia una vida más saludable.
        </p>
        <div style="background: #d4edda; border-left: 4px solid #00bf8e; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <p style="margin: 0; color: #155724;">
                <strong>✅ Tu membresía {membresia}</strong> está activa hasta el <strong>{fecha_vencimiento}</strong>.
            </p>
        </div>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            <strong>Tips para comenzar:</strong>
        </p>
        <ul style="color: #555; font-size: 16px; line-height: 1.8;">
            <li>Llega 10 minutos antes para calentar</li>
            <li>Hidratación: siempre trae tu botella de agua</li>
            <li>Consulta con nuestros instructores si tienes dudas</li>
            <li>¡Disfruta el proceso!</li>
        </ul>
        <div style="text-align: center; margin-top: 30px;">
            <a href="#" style="background: #e94560; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold;">Ver Horarios</a>
        </div>
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        <p style="color: #888; font-size: 12px; text-align: center;">
            ¿Dudas? Responde a este correo o visítanos en recepción.
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
                'nombre' => 'Recordatorio de Pago Pendiente',
                'descripcion' => 'Se envía cuando hay un pago pendiente',
                'asunto_email' => '💳 {nombre}, tienes un pago pendiente en Estoicos Gym',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">🏋️ Estoicos Gym</h1>
    </div>
    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #1a1a2e; margin-top: 0;">Hola {nombre} 👋</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            Te recordamos que tienes un pago pendiente por tu membresía <strong>{membresia}</strong>.
        </p>
        <div style="background: #fff3cd; border-left: 4px solid #f0a500; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <p style="margin: 0; color: #856404;">
                <strong>💰 Monto pendiente: ${monto_pendiente}</strong>
            </p>
        </div>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            Acércate a recepción para regularizar tu pago y continuar disfrutando de nuestras instalaciones.
        </p>
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        <p style="color: #888; font-size: 12px; text-align: center;">
            Este es un correo automático de Estoicos Gym.
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
