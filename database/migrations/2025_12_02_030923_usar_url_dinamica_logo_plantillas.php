<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Actualiza todas las plantillas para usar la URL del APP_URL configurado
     */
    public function up(): void
    {
        // Obtener la URL base de la app
        $appUrl = rtrim(config('app.url'), '/');
        $logoUrl = $appUrl . '/images/estoicos_gym_logo.png';
        
        // Reemplazar en todas las plantillas la URL hardcodeada por la dinámica
        $plantillas = DB::table('tipo_notificaciones')->get();
        
        foreach ($plantillas as $plantilla) {
            $html = $plantilla->plantilla_email;
            
            // Reemplazar URL antigua por la nueva
            $html = str_replace(
                'https://estoicosgym.cl/images/estoicos_gym_logo.png',
                $logoUrl,
                $html
            );
            
            // También reemplazar la de suplementos si existe
            $html = str_replace(
                'https://estoicosgym.cl/images/estoicos_splementos_logo.png',
                $appUrl . '/images/estoicos_splementos_logo.png',
                $html
            );
            
            DB::table('tipo_notificaciones')
                ->where('id', $plantilla->id)
                ->update(['plantilla_email' => $html]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se revierte
    }
};
