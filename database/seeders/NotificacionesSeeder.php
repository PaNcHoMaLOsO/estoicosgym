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
                'asunto_email' => '⏰ {nombre}, tu membresía vence en {dias_restantes} días',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #1a1a2e; color: white; padding: 40px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 28px;">🏋️ ESTOICOS GYM</h1>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 30px 20px;">
        <h2 style="color: #1a1a2e; margin: 0 0 15px 0;">Hola {nombre} 👋</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
            Te recordamos que tu membresía <strong>{membresia}</strong> está próxima a vencer.
        </p>
        
        <!-- ALERTA -->
        <div style="background: #fff3cd; border-left: 4px solid #f0a500; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 18px;">⏳ Te quedan {dias_restantes} días</h3>
            <p style="margin: 0; color: #856404;">Fecha de vencimiento: <strong>{fecha_vencimiento}</strong></p>
        </div>
        
        <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 20px 0;">
            Renueva ahora para seguir entrenando sin interrupciones. <strong>¡No pierdas tu ritmo!</strong> 💪
        </p>
        
        <p style="color: #555; font-size: 15px; line-height: 1.6; margin: 20px 0;">
            Acércate a recepción o contáctanos para renovar tu membresía.
        </p>
        
        <!-- FOOTER -->
        <p style="color: #888; font-size: 13px; text-align: center; margin: 30px 0 0 0; padding-top: 20px; border-top: 1px solid #ddd;">
            Correo automático de Estoicos Gym<br>
            <em>Si tienes dudas, contáctanos</em>
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
                'asunto_email' => '❗ {nombre}, tu membresía en Estoicos Gym ha vencido',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #1a1a2e; color: white; padding: 40px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 28px;">🏋️ ESTOICOS GYM</h1>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 30px 20px;">
        <h2 style="color: #1a1a2e; margin: 0 0 15px 0;">Hola {nombre} 👋</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
            Te informamos que tu membresía <strong>{membresia}</strong> venció el <strong>{fecha_vencimiento}</strong>.
        </p>
        
        <!-- ALERTA -->
        <div style="background: #f8d7da; border-left: 4px solid #e94560; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h3 style="margin: 0 0 10px 0; color: #721c24; font-size: 18px;">⚠️ Membresía Vencida</h3>
            <p style="margin: 0; color: #721c24;">No podrás acceder al gimnasio hasta renovar.</p>
        </div>
        
        <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 20px 0;">
            <strong>¡Te extrañamos!</strong> Renueva hoy y retoma tu entrenamiento. 💪
        </p>
        
        <p style="color: #555; font-size: 15px; line-height: 1.6; margin: 20px 0;">
            Cada día cuenta para alcanzar tus metas. Acércate a recepción para renovar tu membresía.
        </p>
        
        <!-- FOOTER -->
        <p style="color: #888; font-size: 13px; text-align: center; margin: 30px 0 0 0; padding-top: 20px; border-top: 1px solid #ddd;">
            Correo automático de Estoicos Gym<br>
            <em>Si tienes dudas, contáctanos</em>
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
                'asunto_email' => '🎉 ¡Bienvenido a Estoicos Gym, {nombre}! - Inscripción Confirmada',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #1a1a2e; color: white; padding: 40px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 28px;">🏋️ ESTOICOS GYM</h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Tu transformación comienza hoy</p>
    </div>
    
    <!-- BIENVENIDA -->
    <div style="padding: 30px 20px;">
        <h2 style="color: #1a1a2e; margin: 0 0 15px 0;">¡Bienvenido/a {nombre}! 🎉</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
            Nos alegra que te hayas unido a <strong>Estoicos Gym</strong>. Tu inscripción ha sido confirmada exitosamente.
        </p>
        
        <!-- CONFIRMACIÓN INSCRIPCIÓN -->
        <div style="background: #d4edda; border-left: 4px solid #00bf8e; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h3 style="margin: 0 0 10px 0; color: #155724; font-size: 18px;">✅ Inscripción Confirmada</h3>
            <p style="margin: 5px 0; color: #155724;"><strong>Membresía:</strong> {membresia}</p>
            <p style="margin: 5px 0; color: #155724;"><strong>Fecha inicio:</strong> {fecha_inicio}</p>
            <p style="margin: 5px 0; color: #155724;"><strong>Válida hasta:</strong> {fecha_vencimiento}</p>
            <p style="margin: 5px 0; color: #155724;"><strong>Precio pagado:</strong> ${precio}</p>
        </div>
        
        <!-- TIPS -->
        <h3 style="color: #1a1a2e; margin: 25px 0 15px 0;">💪 Consejos para empezar:</h3>
        <ul style="color: #555; font-size: 15px; line-height: 1.8; padding-left: 20px;">
            <li>Llega 10 minutos antes para prepararte</li>
            <li>Trae tu botella de agua</li>
            <li>Consulta con nuestros instructores</li>
            <li>Escucha a tu cuerpo</li>
        </ul>
        
        <!-- FOOTER -->
        <p style="color: #888; font-size: 13px; text-align: center; margin: 30px 0 0 0; padding-top: 20px; border-top: 1px solid #ddd;">
            ¿Dudas? Contáctanos o visítanos en recepción<br>
            <em>Estoicos Gym - Transformando vidas</em>
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
                'asunto_email' => '💳 {nombre}, tienes un pago pendiente en Estoicos Gym',
                'plantilla_email' => '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <!-- HEADER -->
    <div style="background: #1a1a2e; color: white; padding: 40px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 28px;">🏋️ ESTOICOS GYM</h1>
    </div>
    
    <!-- CONTENIDO -->
    <div style="padding: 30px 20px;">
        <h2 style="color: #1a1a2e; margin: 0 0 15px 0;">Hola {nombre} 👋</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
            Te recordamos que tienes un pago pendiente por tu membresía <strong>{membresia}</strong>.
        </p>
        
        <!-- DETALLE PAGO -->
        <div style="background: #fff3cd; border-left: 4px solid #f0a500; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 18px;">💰 Monto Pendiente</h3>
            <p style="margin: 0; color: #856404; font-size: 24px; font-weight: bold;">${monto_pendiente}</p>
            <p style="margin: 10px 0 0 0; color: #856404; font-size: 14px;">Total: ${monto_total}</p>
        </div>
        
        <p style="color: #555; font-size: 16px; line-height: 1.6; margin: 20px 0;">
            Acércate a recepción para regularizar tu pago y continuar entrenando sin problemas.
        </p>
        
        <p style="color: #555; font-size: 15px; line-height: 1.6; margin: 20px 0;">
            Tu membresía vence el: <strong>{fecha_vencimiento}</strong>
        </p>
        
        <!-- FOOTER -->
        <p style="color: #888; font-size: 13px; text-align: center; margin: 30px 0 0 0; padding-top: 20px; border-top: 1px solid #ddd;">
            Correo automático de Estoicos Gym<br>
            <em>Si tienes dudas, contáctanos</em>
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
