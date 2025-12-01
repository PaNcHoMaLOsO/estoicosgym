<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\TipoNotificacion;

class EnviarPlantillasPrueba extends Command
{
    protected $signature = 'email:plantillas {email : Email de destino}';
    protected $description = 'Envía todas las plantillas de notificación como prueba';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('');
        $this->info('📧 Enviando plantillas de prueba a: ' . $email);
        $this->info('═══════════════════════════════════════════════');
        
        $plantillas = TipoNotificacion::where('activo', true)->get();
        
        if ($plantillas->isEmpty()) {
            $this->error('No hay plantillas activas');
            return Command::FAILURE;
        }

        // Datos de ejemplo para renderizar
        $datosEjemplo = [
            'nombre' => 'Juan Pérez',
            'membresia' => 'Premium Mensual',
            'fecha_vencimiento' => now()->addDays(5)->format('d/m/Y'),
            'dias_restantes' => '5',
            'fecha_inicio' => now()->subMonth()->format('d/m/Y'),
            'monto_pendiente' => '$45.000',
        ];

        $enviadas = 0;
        $fallidas = 0;

        foreach ($plantillas as $plantilla) {
            $this->line('');
            $this->info("📤 Enviando: {$plantilla->nombre}");
            
            try {
                // Renderizar la plantilla
                $renderizado = $plantilla->renderizar($datosEjemplo);
                
                Mail::html($renderizado['contenido'], function ($message) use ($email, $renderizado, $plantilla) {
                    $message->to($email)
                            ->subject("[PRUEBA] {$renderizado['asunto']}");
                });

                $this->line("   ✅ Enviado correctamente");
                $enviadas++;
                
                // Pequeña pausa para no saturar
                sleep(1);
                
            } catch (\Exception $e) {
                $this->error("   ❌ Error: " . $e->getMessage());
                $fallidas++;
            }
        }

        $this->line('');
        $this->info('═══════════════════════════════════════════════');
        $this->info("📊 Resumen: {$enviadas} enviadas, {$fallidas} fallidas");
        $this->info("📬 Revisa tu bandeja de entrada en: {$email}");
        $this->warn("⚠️  Los correos pueden llegar a SPAM");
        
        return Command::SUCCESS;
    }
}
