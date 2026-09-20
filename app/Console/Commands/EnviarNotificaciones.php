<?php

namespace App\Console\Commands;

use App\Services\NotificacionService;
use App\Support\Ajustes;
use Illuminate\Console\Command;

class EnviarNotificaciones extends Command
{
    protected $signature = 'notificaciones:enviar 
                            {--programar : Programar notificaciones para hoy}
                            {--enviar : Enviar notificaciones pendientes}
                            {--reintentar : Reintentar notificaciones fallidas}
                            {--todo : Ejecutar todas las acciones}';

    protected $description = 'Gestiona el envío de notificaciones automáticas por correo';

    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        parent::__construct();
        $this->notificacionService = $notificacionService;
    }

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║       🔔 SISTEMA DE NOTIFICACIONES - ESTOICOS GYM        ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');

        /*
         * EL INTERRUPTOR DE CONFIGURACIÓN MANDA.
         *
         * Apagado, esta orden no programa ni envía nada, aunque Windows la
         * llame a su hora. Es lo que permite estrenar el sistema con socios de
         * verdad sin que les llegue un correo de prueba, y cortar en seco un
         * domingo si algo sale mal. Lo que se manda A MANO desde el panel no
         * pasa por aquí y sigue funcionando.
         */
        if (! Ajustes::activo('tareas.correos_automaticos')) {
            $this->warn('Los correos automáticos están apagados en Configuración, en Avisos automáticos.');
            $this->line('No se programó ni se envió nada.');

            return self::SUCCESS;
        }

        $todo = $this->option('todo');
        $programar = $this->option('programar') || $todo;
        $enviar = $this->option('enviar') || $todo;
        $reintentar = $this->option('reintentar') || $todo;

        // Si no se especifica ninguna opción, ejecutar todo
        if (!$programar && !$enviar && !$reintentar) {
            $todo = true;
            $programar = $enviar = $reintentar = true;
        }

        // 1. Programar notificaciones
        if ($programar) {
            $this->info('📅 Programando notificaciones...');
            $this->newLine();

            // Membresías por vencer
            $resultado = $this->notificacionService->programarNotificacionesPorVencer();
            $this->line("   • Por vencer: {$resultado['programadas']} programadas");

            // Membresías vencidas
            $resultado = $this->notificacionService->programarNotificacionesVencidas();
            $this->line("   • Vencidas: {$resultado['programadas']} programadas");

            $this->newLine();
        }

        // 2. Enviar pendientes
        if ($enviar) {
            $this->info('📧 Enviando notificaciones pendientes...');
            $this->newLine();

            $resultado = $this->notificacionService->enviarPendientes();
            
            if ($resultado['total'] > 0) {
                $this->line("   • Total procesadas: {$resultado['total']}");
                $this->line("   • Enviadas exitosamente: <fg=green>{$resultado['enviadas']}</>");
                if ($resultado['fallidas'] > 0) {
                    $this->line("   • Fallidas: <fg=red>{$resultado['fallidas']}</>");
                }
            } else {
                $this->line("   • No hay notificaciones pendientes para enviar");
            }

            $this->newLine();
        }

        // 3. Reintentar fallidas
        if ($reintentar) {
            $this->info('🔄 Reintentando notificaciones fallidas...');
            $this->newLine();

            $resultado = $this->notificacionService->reintentarFallidas();
            
            if ($resultado['reenviadas'] > 0 || $resultado['fallidas'] > 0) {
                $this->line("   • Reenviadas: <fg=green>{$resultado['reenviadas']}</>");
                if ($resultado['fallidas'] > 0) {
                    $this->line("   • Siguen fallando: <fg=red>{$resultado['fallidas']}</>");
                }
            } else {
                $this->line("   • No hay notificaciones fallidas para reintentar");
            }

            $this->newLine();
        }

        // Mostrar estadísticas
        $this->mostrarEstadisticas();

        $this->info('✅ Proceso completado');
        $this->newLine();

        return Command::SUCCESS;
    }

    protected function mostrarEstadisticas(): void
    {
        $stats = $this->notificacionService->obtenerEstadisticas();

        $this->info('📊 Estadísticas:');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Pendientes', $stats['pendientes']],
                ['Enviadas hoy', $stats['enviadas_hoy']],
                ['Enviadas este mes', $stats['enviadas_mes']],
                ['Fallidas (pendientes reintento)', $stats['fallidas']],
                ['Total histórico', $stats['total']],
            ]
        );
    }
}
