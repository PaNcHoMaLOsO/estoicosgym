<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Restaura el logo de imagen en todas las plantillas
     */
    public function up(): void
    {
        // URL base del logo - usar la URL de producción
        $logoUrl = 'https://estoicosgym.cl/images/estoicos_gym_logo.png';
        
        // Plantilla de Horario Festivo con logo de imagen
        $horarioFestivo = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: \'Segoe UI\', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        
        <!-- Header con logo sobre fondo oscuro -->
        <div style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 35px 30px; text-align: center;">
            <img src="' . $logoUrl . '" alt="Estoicos Gym" style="max-width: 180px; height: auto;">
        </div>
        
        <!-- Banner festivo -->
        <div style="background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%); padding: 25px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 26px; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">🎉 {nombre_festivo}</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 16px; margin: 10px 0 0;">{fecha_festivo}</p>
        </div>
        
        <!-- Contenido -->
        <div style="padding: 35px 30px;">
            <h2 style="color: #1a1a2e; margin: 0 0 20px; font-size: 22px;">
                ¡Hola {nombre}! 👋
            </h2>
            
            <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 0 0 25px;">
                Te informamos que con motivo de <strong style="color: #e94560;">{nombre_festivo}</strong>, 
                nuestros horarios serán especiales. ¡Planifica tu entrenamiento!
            </p>
            
            <!-- Caja de horarios -->
            <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 25px; margin: 25px 0; border-left: 5px solid #e94560;">
                <h3 style="color: #1a1a2e; font-size: 18px; font-weight: 700; margin: 0 0 15px;">
                    🕐 Horarios Especiales
                </h3>
                {horarios_festivo}
            </div>
            
            <p style="color: #555; font-size: 15px; line-height: 1.7; margin: 25px 0;">
                {mensaje_adicional}
            </p>
            
            <!-- Sección Instagram -->
            <div style="background: linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%); border-radius: 12px; padding: 25px; margin: 25px 0; text-align: center;">
                <h3 style="color: #ffffff; margin: 0 0 10px; font-size: 18px;">📸 Síguenos en Instagram</h3>
                <p style="color: rgba(255,255,255,0.9); margin: 0 0 15px; font-size: 14px;">
                    Tips de entrenamiento y promociones exclusivas
                </p>
                <a href="https://instagram.com/estoicosgym" style="display: inline-block; background: #ffffff; color: #833ab4; padding: 12px 30px; border-radius: 25px; text-decoration: none; font-weight: 700;">
                    @estoicosgym
                </a>
            </div>
            
            <p style="color: #555; font-size: 15px; line-height: 1.7; text-align: center;">
                ¡Que disfrutes el feriado! Nos vemos pronto 💪
            </p>
        </div>
        
        <!-- Banner suplementos -->
        <div style="background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); padding: 15px; text-align: center;">
            <p style="color: #ffffff; margin: 0; font-weight: 600; font-size: 14px;">
                🏋️ ¡Visita también nuestra tienda de suplementos! 💪
            </p>
        </div>
        
        <!-- Footer -->
        <div style="background: #1a1a2e; padding: 25px; text-align: center;">
            <img src="' . $logoUrl . '" alt="Estoicos Gym" style="max-width: 100px; height: auto; margin-bottom: 15px;">
            <p style="color: rgba(255,255,255,0.7); margin: 5px 0; font-size: 13px;">
                Tu templo del fitness - Los Ángeles, Chile
            </p>
            <p style="color: rgba(255,255,255,0.5); margin: 10px 0 0; font-size: 11px;">
                Este es un correo automático. Si tienes dudas, contáctanos en recepción.
            </p>
        </div>
    </div>
</body>
</html>';

        DB::table('tipo_notificaciones')
            ->where('codigo', 'horario_festivo')
            ->update([
                'plantilla_email' => $horarioFestivo,
                'asunto_email' => '🎉 {nombre_festivo} - Horarios Especiales | Estoicos Gym'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se revierte
    }
};
