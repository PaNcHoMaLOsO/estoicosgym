<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inscripcion;
use App\Models\TipoNotificacion;
use App\Services\NotificacionService;
use Carbon\Carbon;

class TestPlantillasAutomaticas extends Command
{
    protected $signature = 'test:plantillas-automaticas {email}';
    protected $description = 'Prueba el envío de todas las plantillas automáticas con datos dinámicos';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('');
        $this->info('🧪 TEST: Plantillas Automáticas con Datos Dinámicos');
        $this->info('═══════════════════════════════════════════════════');
        $this->info('');

        // Obtener una inscripción de ejemplo
        $inscripcion = Inscripcion::with(['cliente', 'membresia', 'pagos'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$inscripcion) {
            $this->error('❌ No hay inscripciones en la base de datos');
            return 1;
        }

        $this->line("📋 Usando inscripción de ejemplo:");
        $this->line("   • ID: {$inscripcion->id}");
        $this->line("   • Cliente: {$inscripcion->cliente->nombre_completo}");
        $this->line("   • Membresía: {$inscripcion->membresia->nombre}");
        $this->line("   • Vencimiento: " . Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'));
        $this->info('');

        $notificacionService = app(NotificacionService::class);
        $resultados = [];

        // TEST 1: Membresía Por Vencer
        $this->info('📧 TEST 1: Membresía por Vencer');
        try {
            $tipoPorVencer = TipoNotificacion::where('codigo', TipoNotificacion::MEMBRESIA_POR_VENCER)
                ->where('activo', true)
                ->first();

            if ($tipoPorVencer) {
                $notificacion = $notificacionService->crearNotificacion($tipoPorVencer, $inscripcion);
                
                // Cambiar email de destino para el test
                $notificacion->update(['email_destino' => $email]);
                
                // Enviar
                $resultado = \Resend\Laravel\Facades\Resend::emails()->send([
                    'from' => 'PROGYM <onboarding@resend.dev>',
                    'to' => [$email],
                    'subject' => $notificacion->asunto,
                    'html' => $notificacion->contenido,
                ]);

                $this->line("   ✅ Enviado: {$notificacion->asunto}");
                $this->line("   📧 ID: {$resultado->id}");
                $resultados[] = '✅ Membresía por vencer';
                
                // Limpiar
                $notificacion->delete();
            } else {
                $this->warn('   ⚠️  Tipo no encontrado o inactivo');
                $resultados[] = '⚠️  Membresía por vencer - NO CONFIGURADO';
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: {$e->getMessage()}");
            $resultados[] = '❌ Membresía por vencer - ERROR';
        }
        $this->info('');

        // TEST 2: Membresía Vencida
        $this->info('📧 TEST 2: Membresía Vencida');
        try {
            $tipoVencida = TipoNotificacion::where('codigo', TipoNotificacion::MEMBRESIA_VENCIDA)
                ->where('activo', true)
                ->first();

            if ($tipoVencida) {
                $notificacion = $notificacionService->crearNotificacion($tipoVencida, $inscripcion);
                
                // Cambiar email de destino
                $notificacion->update(['email_destino' => $email]);
                
                // Enviar
                $resultado = \Resend\Laravel\Facades\Resend::emails()->send([
                    'from' => 'PROGYM <onboarding@resend.dev>',
                    'to' => [$email],
                    'subject' => $notificacion->asunto,
                    'html' => $notificacion->contenido,
                ]);

                $this->line("   ✅ Enviado: {$notificacion->asunto}");
                $this->line("   📧 ID: {$resultado->id}");
                $resultados[] = '✅ Membresía vencida';
                
                // Limpiar
                $notificacion->delete();
            } else {
                $this->warn('   ⚠️  Tipo no encontrado o inactivo');
                $resultados[] = '⚠️  Membresía vencida - NO CONFIGURADO';
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: {$e->getMessage()}");
            $resultados[] = '❌ Membresía vencida - ERROR';
        }
        $this->info('');

        // RESUMEN
        $this->info('═══════════════════════════════════════════════════');
        $this->info('📊 RESUMEN DE PRUEBAS');
        $this->info('═══════════════════════════════════════════════════');
        $this->info('');
        foreach ($resultados as $resultado) {
            $this->line($resultado);
        }
        $this->info('');
        $this->line("📧 Emails enviados a: {$email}");
        $this->line("🔍 Verifica tu bandeja de entrada y spam");
        $this->info('');
        $this->info('═══════════════════════════════════════════════════');

        return 0;
    }
}
