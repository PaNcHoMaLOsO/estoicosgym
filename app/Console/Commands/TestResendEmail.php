<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestResendEmail extends Command
{
    protected $signature = 'email:test {email : Email de destino para la prueba}';
    protected $description = 'Envía un correo de prueba usando Resend';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("📧 Enviando correo de prueba a: {$email}");
        $this->info("Usando mailer: " . config('mail.default'));
        
        try {
            Mail::raw('¡Hola! Este es un correo de prueba desde EstóicosGym usando Resend. 🏋️‍♂️', function ($message) use ($email) {
                $message->to($email)
                        ->subject('✅ Prueba de Correo - EstóicosGym');
            });

            $this->info('');
            $this->info('✅ ¡Correo enviado exitosamente!');
            $this->info('Revisa tu bandeja de entrada (y spam) en: ' . $email);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('');
            $this->error('❌ Error al enviar el correo:');
            $this->error($e->getMessage());
            
            if (str_contains($e->getMessage(), 'API key')) {
                $this->warn('');
                $this->warn('💡 Verifica que RESEND_API_KEY esté correctamente configurado en .env');
            }
            
            return Command::FAILURE;
        }
    }
}
