<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoNotificacion;

class ActualizarLogosPlantillasSeeder extends Seeder
{
    public function run(): void
    {
        $logoGym = url('/images/estoicos_gym_logo.png');
        $logoSuplementos = url('/images/estoicos_splementos_logo.png');
        
        // Actualizar todas las plantillas reemplazando los placeholders
        $plantillas = TipoNotificacion::all();
        
        foreach ($plantillas as $plantilla) {
            $contenido = $plantilla->plantilla_email;
            
            // Reemplazar logos placeholder por los reales
            $contenido = str_replace(
                'https://via.placeholder.com/180x60/1a1a2e/e94560?text=ESTOICOS+GYM',
                $logoGym,
                $contenido
            );
            $contenido = str_replace(
                'https://via.placeholder.com/180x60/e94560/ffffff?text=ESTOICOS+GYM',
                $logoGym,
                $contenido
            );
            $contenido = str_replace(
                'https://via.placeholder.com/180x60/00bf8e/ffffff?text=ESTOICOS+GYM',
                $logoGym,
                $contenido
            );
            
            $plantilla->update(['plantilla_email' => $contenido]);
        }
        
        echo "Logos actualizados correctamente\n";
    }
}
