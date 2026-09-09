<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ActualizarEstadosInscripciones extends Command
{
    protected $signature = 'inscripciones:actualizar-estados 
                            {--dry-run : Mostrar cambios sin ejecutarlos}';
    
    protected $description = 'Actualiza automáticamente el estado de inscripciones vencidas (Activa → Vencida)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $hoy = Carbon::now();

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║       ACTUALIZACIÓN DE ESTADOS DE INSCRIPCIONES                             ║');
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        if ($dryRun) {
            $this->warn('⚠ MODO DRY-RUN: No se realizarán cambios');
        }

        // 1. Inscripciones ACTIVAS con fecha_vencimiento pasada → VENCIDA
        $this->line('');
        $this->comment('🔍 Buscando inscripciones activas vencidas...');
        
        $activasVencidas = Inscripcion::where('id_estado', 100) // Activa
            ->where('fecha_vencimiento', '<', $hoy->format('Y-m-d'))
            ->where('pausada', false) // No pausadas
            ->with(['cliente', 'membresia'])
            ->get();

        if ($activasVencidas->count() > 0) {
            $this->warn("   → Encontradas {$activasVencidas->count()} inscripciones activas vencidas");
            
            foreach ($activasVencidas as $insc) {
                // De dia a dia y entero: fecha_vencimiento se guarda a medianoche,
                // asi que restarle la hora actual dejaba decimales en pantalla.
                $diasVencida = (int) $insc->fecha_vencimiento->startOfDay()->diffInDays($hoy->copy()->startOfDay());
                $this->line("     - ID #{$insc->id}: {$insc->cliente->nombres} {$insc->cliente->apellido_paterno}");
                $this->line("       Membresía: {$insc->membresia->nombre}, Venció hace {$diasVencida} días");
                
                if (!$dryRun) {
                    $insc->update([
                        'id_estado' => 102, // Vencida
                        'observaciones' => ($insc->observaciones ? $insc->observaciones . "\n" : '') 
                            . "[" . now()->format('d/m/Y H:i') . "] Marcada como vencida automáticamente (vencimiento: {$insc->fecha_vencimiento->format('d/m/Y')})",
                    ]);
                }
            }
            
            if (!$dryRun) {
                $this->info("   ✅ Actualizadas {$activasVencidas->count()} inscripciones a estado VENCIDA");
            }
        } else {
            $this->info('   ✓ No hay inscripciones activas vencidas');
        }

        // 2. Verificar pausas que deberían terminar
        $this->line('');
        $this->comment('🔍 Buscando pausas que deberían terminar...');
        
        $pausasTerminadas = Inscripcion::where('id_estado', 101) // Pausada
            ->where('pausada', true)
            ->where('pausa_indefinida', false)
            ->whereNotNull('fecha_pausa_fin')
            ->where('fecha_pausa_fin', '<', $hoy->format('Y-m-d'))
            ->with(['cliente', 'membresia'])
            ->get();

        if ($pausasTerminadas->count() > 0) {
            $this->warn("   → Encontradas {$pausasTerminadas->count()} pausas que deberían terminar");
            
            foreach ($pausasTerminadas as $insc) {
                $diasPasados = (int) $insc->fecha_pausa_fin->startOfDay()->diffInDays($hoy->copy()->startOfDay());
                $this->line("     - ID #{$insc->id}: {$insc->cliente->nombres} {$insc->cliente->apellido_paterno}");
                $this->line("       Pausa terminó hace {$diasPasados} días (fecha_pausa_fin: {$insc->fecha_pausa_fin->format('d/m/Y')})");
                
                if (!$dryRun) {
                    // Reanudar automáticamente (el modelo ya ajusta la fecha de vencimiento)
                    $insc->reanudar();
                }
            }
            
            if (!$dryRun) {
                $this->info("   ✅ Reanudadas {$pausasTerminadas->count()} inscripciones automáticamente");
            }
        } else {
            $this->info('   ✓ No hay pausas pendientes de terminar');
        }

        // 3. Resumen
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════════════════');
        $totalCambios = $activasVencidas->count() + $pausasTerminadas->count();
        
        if ($dryRun) {
            $this->warn("Modo dry-run: Se habrían actualizado {$totalCambios} inscripciones");
            $this->line('Ejecute sin --dry-run para aplicar los cambios');
        } else {
            $this->info("✅ Proceso completado: {$totalCambios} inscripciones actualizadas");
        }

        return 0;
    }
}
