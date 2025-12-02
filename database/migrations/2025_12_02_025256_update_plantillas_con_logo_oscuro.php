<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Actualiza todas las plantillas de email con el logo sobre fondo oscuro (azul marino)
     */
    public function up(): void
    {
        // Header estándar con logo sobre fondo oscuro
        $headerTemplate = '
<div style="font-family: \'Segoe UI\', Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
    <!-- Header con logo sobre fondo oscuro -->
    <div style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 35px 30px; text-align: center;">
        <img src="https://estoicosgym.cl/images/estoicos_gym_logo.png" alt="Estoicos Gym" style="max-width: 150px; height: auto;" onerror="this.style.display=\'none\'">
        <h1 style="color: #ffffff; margin: 15px 0 0; font-size: 20px; font-weight: 600; letter-spacing: 1px;">ESTOICOS GYM</h1>
    </div>';

        $footerTemplate = '
    <!-- Footer -->
    <div style="background: #1a1a2e; padding: 25px; text-align: center;">
        <p style="color: #888; font-size: 12px; margin: 0 0 10px;">
            <strong style="color: #e94560;">Estoicos Gym</strong> - Los Angeles, Chile
        </p>
        <p style="color: #666; font-size: 11px; margin: 0;">
            Este es un correo automatico. Si tienes dudas, contactanos en recepcion.
        </p>
    </div>
</div>';

        // ========== MEMBRESIA POR VENCER ==========
        $membresiaPorVencer = $headerTemplate . '
    <!-- Contenido -->
    <div style="padding: 35px 30px;">
        <h2 style="color: #1a1a2e; margin: 0 0 20px; font-size: 22px;">
            Hola {nombre}!
        </h2>
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 20px;">
            Te recordamos que tu membresia <strong style="color: #1a1a2e;">{membresia}</strong> esta proxima a vencer.
        </p>
        
        <!-- Alerta amarilla -->
        <div style="background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%); border-left: 4px solid #f0a500; padding: 20px; margin: 25px 0; border-radius: 0 12px 12px 0;">
            <p style="margin: 0; color: #856404; font-size: 15px;">
                <strong>Fecha de vencimiento:</strong> {fecha_vencimiento}<br>
                <strong>Dias restantes:</strong> {dias_restantes} dias
            </p>
        </div>
        
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 25px;">
            No pierdas tu ritmo! Renueva ahora y sigue alcanzando tus metas.
        </p>
        
        <!-- Boton -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="#" style="display: inline-block; background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3);">
                RENOVAR AHORA
            </a>
        </div>
    </div>
' . $footerTemplate;

        // ========== MEMBRESIA VENCIDA ==========
        $membresiaVencida = $headerTemplate . '
    <!-- Contenido -->
    <div style="padding: 35px 30px;">
        <h2 style="color: #1a1a2e; margin: 0 0 20px; font-size: 22px;">
            Te extranamos, {nombre}!
        </h2>
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 20px;">
            Tu membresia <strong style="color: #1a1a2e;">{membresia}</strong> ha vencido.
        </p>
        
        <!-- Alerta roja -->
        <div style="background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); border-left: 4px solid #e94560; padding: 20px; margin: 25px 0; border-radius: 0 12px 12px 0;">
            <p style="margin: 0; color: #c62828; font-size: 15px;">
                <strong>Tu membresia vencio el:</strong> {fecha_vencimiento}<br>
                No podras acceder al gimnasio hasta renovar.
            </p>
        </div>
        
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 25px;">
            Vuelve a entrenar con nosotros! Tu progreso te esta esperando.
        </p>
        
        <!-- Boton -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="#" style="display: inline-block; background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 15px rgba(0, 191, 142, 0.3);">
                RENOVAR MEMBRESIA
            </a>
        </div>
    </div>
' . $footerTemplate;

        // ========== BIENVENIDA ==========
        $bienvenida = $headerTemplate . '
    <!-- Banner de bienvenida -->
    <div style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); padding: 25px; text-align: center;">
        <h2 style="color: #ffffff; margin: 0; font-size: 24px;">BIENVENIDO/A!</h2>
    </div>
    
    <!-- Contenido -->
    <div style="padding: 35px 30px;">
        <h2 style="color: #1a1a2e; margin: 0 0 20px; font-size: 22px;">
            Hola {nombre}! 
        </h2>
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 20px;">
            Nos alegra mucho que te hayas unido a la familia <strong style="color: #e94560;">Estoicos Gym</strong>. 
            Estamos emocionados de acompanarte en tu camino hacia una vida mas saludable.
        </p>
        
        <!-- Info de membresia -->
        <div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left: 4px solid #00bf8e; padding: 20px; margin: 25px 0; border-radius: 0 12px 12px 0;">
            <p style="margin: 0; color: #2e7d32; font-size: 15px;">
                <strong>Membresia:</strong> {membresia}<br>
                <strong>Valida hasta:</strong> {fecha_vencimiento}
            </p>
        </div>
        
        <p style="color: #1a1a2e; font-size: 16px; font-weight: 600; margin: 25px 0 15px;">
            Tips para comenzar:
        </p>
        <ul style="color: #555; font-size: 14px; line-height: 2; margin: 0; padding-left: 20px;">
            <li>Llega 10 minutos antes para calentar</li>
            <li>Siempre trae tu botella de agua</li>
            <li>Consulta con nuestros instructores si tienes dudas</li>
            <li>Disfruta el proceso y se constante!</li>
        </ul>
        
        <!-- Boton -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="#" style="display: inline-block; background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3);">
                VER HORARIOS
            </a>
        </div>
    </div>
' . $footerTemplate;

        // ========== PAGO PENDIENTE ==========
        $pagoPendiente = $headerTemplate . '
    <!-- Contenido -->
    <div style="padding: 35px 30px;">
        <h2 style="color: #1a1a2e; margin: 0 0 20px; font-size: 22px;">
            Recordatorio de Pago
        </h2>
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 20px;">
            Hola <strong style="color: #1a1a2e;">{nombre}</strong>, te recordamos que tienes un pago pendiente.
        </p>
        
        <!-- Info de pago -->
        <div style="background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%); border-left: 4px solid #f0a500; padding: 20px; margin: 25px 0; border-radius: 0 12px 12px 0;">
            <p style="margin: 0; color: #856404; font-size: 15px;">
                <strong>Membresia:</strong> {membresia}<br>
                <strong>Monto pendiente:</strong> ${monto_pendiente}
            </p>
        </div>
        
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 15px;">
            Para mantener tu membresia activa, te invitamos a regularizar tu pago.
        </p>
        
        <p style="color: #1a1a2e; font-size: 15px; font-weight: 600; margin: 20px 0 10px;">
            Formas de pago:
        </p>
        <ul style="color: #555; font-size: 14px; line-height: 1.8; margin: 0; padding-left: 20px;">
            <li>Efectivo en recepcion</li>
            <li>Transferencia bancaria</li>
            <li>Tarjeta de debito/credito</li>
        </ul>
        
        <!-- Boton -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="#" style="display: inline-block; background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 15px rgba(0, 191, 142, 0.3);">
                COORDINAR PAGO
            </a>
        </div>
    </div>
' . $footerTemplate;

        // ========== HORARIO FESTIVO ==========
        $horarioFestivo = $headerTemplate . '
    <!-- Banner festivo -->
    <div style="background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%); padding: 25px; text-align: center;">
        <h2 style="color: #ffffff; margin: 0; font-size: 24px;">{nombre_festivo}</h2>
    </div>
    
    <!-- Contenido -->
    <div style="padding: 35px 30px;">
        <h2 style="color: #1a1a2e; margin: 0 0 20px; font-size: 22px;">
            Hola {nombre}! 
        </h2>
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 20px;">
            Te informamos sobre nuestro horario especial para <strong style="color: #e94560;">{nombre_festivo}</strong>.
        </p>
        
        <!-- Info de horario -->
        <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #4361ee; padding: 20px; margin: 25px 0; border-radius: 0 12px 12px 0;">
            <p style="margin: 0; color: #1565c0; font-size: 15px;">
                <strong>Fecha:</strong> {fecha_festivo}<br>
                <strong>Horario:</strong> {horarios_festivo}
            </p>
        </div>
        
        <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 25px;">
            {mensaje_adicional}
        </p>
        
        <p style="color: #555; font-size: 15px; line-height: 1.7;">
            Que disfrutes el feriado! Nos vemos pronto.
        </p>
    </div>
' . $footerTemplate;

        // Actualizar todas las plantillas
        DB::table('tipo_notificaciones')
            ->where('codigo', 'membresia_por_vencer')
            ->update([
                'plantilla_email' => $membresiaPorVencer,
                'asunto_email' => '{nombre}, tu membresia vence en {dias_restantes} dias'
            ]);

        DB::table('tipo_notificaciones')
            ->where('codigo', 'membresia_vencida')
            ->update([
                'plantilla_email' => $membresiaVencida,
                'asunto_email' => '{nombre}, tu membresia ha vencido - Renueva hoy!'
            ]);

        DB::table('tipo_notificaciones')
            ->where('codigo', 'bienvenida')
            ->update([
                'plantilla_email' => $bienvenida,
                'asunto_email' => 'Bienvenido a la familia Estoicos, {nombre}!'
            ]);

        DB::table('tipo_notificaciones')
            ->where('codigo', 'pago_pendiente')
            ->update([
                'plantilla_email' => $pagoPendiente,
                'asunto_email' => '{nombre}, tienes una cuota pendiente de pago'
            ]);

        DB::table('tipo_notificaciones')
            ->where('codigo', 'horario_festivo')
            ->update([
                'plantilla_email' => $horarioFestivo,
                'asunto_email' => '{nombre_festivo} - Horarios Especiales | Estoicos Gym'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se revierte, las plantillas anteriores no se guardan
    }
};
