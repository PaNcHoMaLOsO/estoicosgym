<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pago;
use App\Models\Inscripcion;
use Carbon\Carbon;

class SincronizarEstadosPagos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagos:sincronizar-estados {--limit=100 : Máximo de pagos a procesar}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sincronizar estados de pagos: actualizar Vencido, Pendiente, Parcial según fechas y montos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $hoy = now();

        $this->info("🔄 Iniciando sincronización de estados de pagos...");
        $this->info("📊 Procesando hasta {$limit} pagos");

        // 1. ACTUALIZAR PAGOS VENCIDOS
        // Si fecha_vencimiento_cuota < hoy y monto_pendiente > 0
        $pagosVencidos = Pago::where('fecha_vencimiento_cuota', '<', $hoy)
            ->where('monto_pendiente', '>', 0)
            ->where('id_estado', '!=', 203) // No es vencido aún
            ->limit($limit)
            ->get();

        $countVencidos = 0;
        foreach ($pagosVencidos as $pago) {
            $pago->id_estado = 203; // Vencido
            $pago->save();
            $countVencidos++;
        }

        if ($countVencidos > 0) {
            $this->line("✅ {$countVencidos} pagos marcados como <fg=red>VENCIDO</>");
        }

        // 2. ACTUALIZAR PAGOS PENDIENTES
        // Si monto_abonado = 0 y monto_pendiente > 0
        $pagosPendientes = Pago::where('monto_abonado', 0)
            ->where('monto_pendiente', '>', 0)
            ->where('id_estado', '!=', 200) // No es pendiente aún
            ->limit($limit)
            ->get();

        $countPendientes = 0;
        foreach ($pagosPendientes as $pago) {
            $pago->id_estado = 200; // Pendiente
            $pago->save();
            $countPendientes++;
        }

        if ($countPendientes > 0) {
            $this->line("✅ {$countPendientes} pagos marcados como <fg=yellow>PENDIENTE</>");
        }

        // 3. ACTUALIZAR PAGOS PARCIALES
        // Si monto_abonado > 0 y monto_abonado < monto_total
        $pagosParc = Pago::whereRaw('monto_abonado > 0 AND monto_pendiente > 0')
            ->where('id_estado', '!=', 202) // No es parcial aún
            ->limit($limit)
            ->get();

        $countParciales = 0;
        foreach ($pagosParc as $pago) {
            $pago->id_estado = 202; // Parcial
            $pago->save();
            $countParciales++;
        }

        if ($countParciales > 0) {
            $this->line("✅ {$countParciales} pagos marcados como <fg=cyan>PARCIAL</>");
        }

        // 4. ACTUALIZAR PAGOS COMPLETADOS
        // Si monto_pendiente <= 0
        $pagosCompletados = Pago::where('monto_pendiente', '<=', 0)
            ->where('id_estado', '!=', 201) // No es pagado aún
            ->limit($limit)
            ->get();

        $countCompletados = 0;
        foreach ($pagosCompletados as $pago) {
            $pago->id_estado = 201; // Pagado
            $pago->save();
            $countCompletados++;
        }

        if ($countCompletados > 0) {
            $this->line("✅ {$countCompletados} pagos marcados como <fg=green>PAGADO</>");
        }

        // 5. SINCRONIZAR ESTADO DE INSCRIPCIONES
        // Si inscripción no tiene pagos pendientes → está pagada
        $inscripciones = Inscripcion::where('id_estado', 100) // Activa
            ->limit($limit)
            ->get();

        $countInscripcionesPagadas = 0;
        foreach ($inscripciones as $inscripcion) {
            $saldoPendiente = $inscripcion->getSaldoPendiente();
            if ($saldoPendiente <= 0 && $inscripcion->fecha_vencimiento->isFuture()) {
                // Inscripción pagada al día
                // TODO: Actualizar estado si existe estado "Pagada al día"
                $countInscripcionesPagadas++;
            }
        }

        if ($countInscripcionesPagadas > 0) {
            $this->line("✅ {$countInscripcionesPagadas} inscripciones verificadas como <fg=green>PAGADAS AL DÍA</>");
        }

        // Resumen
        $this->newLine();
        $this->info("✨ Sincronización completada");
        $this->table(
            ['Estado', 'Cantidad'],
            [
                ['Vencidos', $countVencidos],
                ['Pendientes', $countPendientes],
                ['Parciales', $countParciales],
                ['Pagados', $countCompletados],
                ['Inscripciones verificadas', $countInscripcionesPagadas],
            ]
        );

        $this->info("✅ Proceso finalizado exitosamente");
        return Command::SUCCESS;
    }
}
