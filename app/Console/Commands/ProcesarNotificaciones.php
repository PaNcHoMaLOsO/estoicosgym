<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificacionService;

class ProcesarNotificaciones extends Command
{
    protected $signature = 'notificaciones:procesar 
                            {--programar : Programa nuevas notificaciones}
                            {--enviar : Envía notificaciones pendientes}
                            {--reintentar : Reintenta notificaciones fallidas}
                            {--todo : Ejecuta todas las acciones}';
    
    protected $description = 'Procesa el sistema de notificaciones automáticas';

    protected NotificacionService $servicio;

    public function __construct(NotificacionService $servicio)
    {
        parent::__construct();
        $this->servicio = $servicio;
    }

    public function handle()
    {
        $this->info('');
        $this->info('🔔 Sistema de Notificaciones - EstóicosGym');
        $this->info('═══════════════════════════════════════════');
        $this->info('');

        $todo = $this->option('todo');
        $ejecutoAlgo = false;

        // 1. Programar nuevas notificaciones
        if ($todo || $this->option('programar')) {
            $ejecutoAlgo = true;
            $this->info('📅 Programando notificaciones...');
            
            // Membresías por vencer
            $resultado = $this->servicio->programarNotificacionesPorVencer();
            $this->line("   ├─ Por vencer: {$resultado['programadas']} programadas");
            
            // Membresías vencidas
            $resultado = $this->servicio->programarNotificacionesVencidas();
            $this->line("   └─ Vencidas: {$resultado['programadas']} programadas");
            $this->info('');
        }

        // 2. Enviar pendientes
        if ($todo || $this->option('enviar')) {
            $ejecutoAlgo = true;
            $this->info('📧 Enviando notificaciones pendientes...');
            
            $resultado = $this->servicio->enviarPendientes();
            $this->line("   ├─ Enviadas: {$resultado['enviadas']}");
            $this->line("   ├─ Fallidas: {$resultado['fallidas']}");
            $this->line("   └─ Total procesadas: {$resultado['total']}");
            $this->info('');
        }

        // 3. Reintentar fallidas
        if ($todo || $this->option('reintentar')) {
            $ejecutoAlgo = true;
            $this->info('🔄 Reintentando notificaciones fallidas...');
            
            $resultado = $this->servicio->reintentarFallidas();
            $this->line("   ├─ Reenviadas: {$resultado['reenviadas']}");
            $this->line("   └─ Fallidas nuevamente: {$resultado['fallidas']}");
            $this->info('');
        }

        if (!$ejecutoAlgo) {
            $this->warn('⚠️  No se especificó ninguna acción.');
            $this->info('');
            $this->info('Uso:');
            $this->line('   php artisan notificaciones:procesar --programar  # Programa nuevas');
            $this->line('   php artisan notificaciones:procesar --enviar     # Envía pendientes');
            $this->line('   php artisan notificaciones:procesar --reintentar # Reintenta fallidas');
            $this->line('   php artisan notificaciones:procesar --todo       # Ejecuta todo');
            $this->info('');
            return Command::SUCCESS;
        }

        // Mostrar estadísticas
        $stats = $this->servicio->obtenerEstadisticas();
        $this->info('📊 Estadísticas:');
        $this->line("   ├─ Pendientes: {$stats['pendientes']}");
        $this->line("   ├─ Enviadas hoy: {$stats['enviadas_hoy']}");
        $this->line("   ├─ Enviadas este mes: {$stats['enviadas_mes']}");
        $this->line("   ├─ Fallidas: {$stats['fallidas']}");
        $this->line("   └─ Total histórico: {$stats['total']}");
        $this->info('');
        
        $this->info('✅ Proceso completado');
        
        return Command::SUCCESS;
    }
}
