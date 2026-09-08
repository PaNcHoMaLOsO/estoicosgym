<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CorreoService;
use Illuminate\Support\Facades\DB;
use App\Mail\NotificacionMail;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email 
                            {email : Email del destinatario}
                            {tipo=bienvenida : Tipo de notificación (bienvenida, membresia_por_vencer, membresia_vencida, pago_pendiente)}';

    protected $description = 'Enviar un correo de prueba con las plantillas PROGYM';

    public function handle()
    {
        $email = $this->argument('email');
        $tipo = $this->argument('tipo');

        $this->info("📧 Preparando envío de correo de prueba...");
        $this->info("Destinatario: {$email}");
        $this->info("Tipo: {$tipo}");

        // Obtener la plantilla de la base de datos
        $plantilla = DB::table('tipo_notificaciones')
            ->where('codigo', $tipo)
            ->first();

        if (!$plantilla) {
            $this->error("❌ No se encontró la plantilla: {$tipo}");
            $this->info("Plantillas disponibles:");
            $plantillas = DB::table('tipo_notificaciones')->pluck('codigo');
            foreach ($plantillas as $p) {
                $this->line("  - {$p}");
            }
            return 1;
        }

        $this->info("✅ Plantilla encontrada: {$plantilla->nombre}");

        // Variables de prueba según el tipo
        $variables = $this->getVariablesPrueba($tipo);

        // Reemplazar variables en la plantilla
        $htmlContent = $this->reemplazarVariables($plantilla->plantilla_email, $variables);
        $asunto = $this->reemplazarVariables($plantilla->asunto_email, $variables);

        $this->info("📨 Enviando correo...");

        try {
            (new CorreoService())->enviar($email, $asunto, $htmlContent);

            $this->info("✅ ¡Correo enviado exitosamente!");
            $this->info("📬 Revisa la bandeja de entrada de: {$email}");
            
            if (config('mail.default') === 'log') {
                $this->warn("⚠️  El mailer está configurado como 'log'. El correo se guardó en storage/logs/laravel.log");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error al enviar el correo:");
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function getVariablesPrueba($tipo)
    {
        $baseVars = [
            'nombre' => 'Juan Pérez',
            'email' => 'progymlosangeles@gmail.com',
            'telefono' => '+56 9 5096 3143',
            'whatsapp_url' => 'https://wa.me/56950963143',
            'logo_url' => 'https://raw.githubusercontent.com/PaNcHoMaLOsO/estoicosgym/main/public/images/progym_logo.svg',
        ];

        switch ($tipo) {
            case 'bienvenida':
                return array_merge($baseVars, [
                    'membresia' => 'Mensual Full',
                    'fecha_inicio' => '01/12/2025',
                    'fecha_vencimiento' => '01/01/2026',
                    'precio' => '35.000',
                ]);

            case 'membresia_por_vencer':
                return array_merge($baseVars, [
                    'membresia' => 'Mensual Full',
                    'dias_restantes' => '3',
                    'fecha_vencimiento' => '08/12/2025',
                ]);

            case 'membresia_vencida':
                return array_merge($baseVars, [
                    'membresia' => 'Mensual Full',
                    'fecha_vencimiento' => '01/12/2025',
                ]);

            case 'pago_pendiente':
                return array_merge($baseVars, [
                    'membresia' => 'Mensual Full',
                    'monto_pendiente' => '15.000',
                    'monto_total' => '35.000',
                    'fecha_vencimiento' => '15/12/2025',
                ]);

            default:
                return $baseVars;
        }
    }

    private function reemplazarVariables($texto, $variables)
    {
        foreach ($variables as $key => $value) {
            $texto = str_replace("{{$key}}", $value, $texto);
        }
        return $texto;
    }
}
