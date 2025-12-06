<?php

namespace Database\Seeders;

use App\Models\TipoNotificacion;
use Illuminate\Database\Seeder;

class PlantillaFestivosSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener URL base de la app para las imágenes
        $appUrl = rtrim(config('app.url'), '/');
        $logoGym = $appUrl . '/images/estoicos_gym_logo.png';
        $logoSuplementos = $appUrl . '/images/estoicos_splementos_logo.png';
        
        $plantillaHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 30px; text-align: center; }
        .header img { max-width: 180px; height: auto; }
        .festivo-banner { background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%); padding: 25px; text-align: center; }
        .festivo-banner h1 { color: #fff; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
        .festivo-banner .fecha { color: rgba(255,255,255,0.9); font-size: 16px; margin-top: 8px; }
        .content { padding: 30px; }
        .greeting { font-size: 18px; color: #1a1a2e; margin-bottom: 20px; }
        .horarios-box { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 25px; margin: 20px 0; border-left: 5px solid #e94560; }
        .horarios-title { color: #1a1a2e; font-size: 18px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .horario-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #dee2e6; }
        .horario-item:last-child { border-bottom: none; }
        .horario-dia { color: #495057; font-weight: 600; }
        .horario-hora { color: #e94560; font-weight: 700; font-size: 16px; }
        .cerrado { color: #dc3545; }
        .instagram-section { background: linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%); border-radius: 12px; padding: 25px; margin: 25px 0; text-align: center; }
        .instagram-section h3 { color: #fff; margin: 0 0 10px 0; font-size: 18px; }
        .instagram-section p { color: rgba(255,255,255,0.9); margin: 0 0 15px 0; font-size: 14px; }
        .instagram-btn { display: inline-block; background: #fff; color: #833ab4; padding: 12px 30px; border-radius: 25px; text-decoration: none; font-weight: 700; transition: transform 0.3s; }
        .instagram-btn:hover { transform: scale(1.05); }
        .mensaje { color: #495057; line-height: 1.7; margin: 20px 0; }
        .footer { background: #1a1a2e; padding: 25px; text-align: center; }
        .footer p { color: rgba(255,255,255,0.7); margin: 5px 0; font-size: 13px; }
        .social-links { margin-top: 15px; }
        .social-links a { color: #fff; text-decoration: none; margin: 0 10px; font-size: 20px; }
        .promo-banner { background: #00bf8e; padding: 15px; text-align: center; }
        .promo-banner p { color: #fff; margin: 0; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{$logoGym}" alt="Estoicos Gym">
        </div>
        
        <div class="festivo-banner">
            <h1>🎉 {nombre_festivo}</h1>
            <div class="fecha">{fecha_festivo}</div>
        </div>
        
        <div class="content">
            <p class="greeting">¡Hola <strong>{nombre}</strong>! 👋</p>
            
            <p class="mensaje">
                Te informamos que con motivo de <strong>{nombre_festivo}</strong>, 
                nuestros horarios serán especiales. ¡Planifica tu entrenamiento!
            </p>
            
            <div class="horarios-box">
                <div class="horarios-title">
                    🕐 Horarios Especiales
                </div>
                {horarios_festivo}
            </div>
            
            <p class="mensaje">
                {mensaje_adicional}
            </p>
            
            <div class="instagram-section">
                <h3>📸 Síguenos en Instagram</h3>
                <p>No te pierdas nuestras publicaciones, tips de entrenamiento y promociones exclusivas</p>
                <a href="{instagram_post_url}" class="instagram-btn">
                    @estoicosgym
                </a>
            </div>
        </div>
        
        <div class="promo-banner">
            <p>🏋️ ¡Visita también nuestra tienda de suplementos! 💪</p>
        </div>
        
        <div class="footer">
            <img src="{$logoSuplementos}" alt="Estoicos Suplementos" style="max-width: 120px; margin-bottom: 15px;">
            <p>Estoicos Gym - Tu templo del fitness</p>
            <p>📍 Dirección del gimnasio</p>
            <p>📞 +56 9 XXXX XXXX</p>
            <div class="social-links">
                <a href="https://instagram.com/estoicosgym">📷</a>
                <a href="https://facebook.com/estoicosgym">📘</a>
                <a href="https://wa.me/569XXXXXXXX">💬</a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        TipoNotificacion::updateOrCreate(
            ['codigo' => 'horario_festivo'],
            [
                'nombre' => 'Horarios Días Festivos',
                'descripcion' => 'Notificación de horarios especiales para días festivos y feriados',
                'asunto_email' => '🎉 {nombre_festivo} - Horarios Especiales | Estoicos Gym Los Ángeles',
                'plantilla_email' => $plantillaHtml,
                'dias_anticipacion' => 3,
                'activo' => true,
                'enviar_email' => true,
            ]
        );

        $this->command->info('✅ Plantilla de Horarios Festivos creada exitosamente');
        $this->command->info('');
        $this->command->info('Variables disponibles:');
        $this->command->info('  {nombre} - Nombre del cliente');
        $this->command->info('  {nombre_festivo} - Nombre del día festivo (ej: Navidad, Año Nuevo)');
        $this->command->info('  {fecha_festivo} - Fecha del festivo (ej: 25 de Diciembre)');
        $this->command->info('  {horarios_festivo} - HTML con los horarios especiales');
        $this->command->info('  {mensaje_adicional} - Mensaje personalizado');
        $this->command->info('  {instagram_post_url} - URL del post de Instagram');
    }
}
