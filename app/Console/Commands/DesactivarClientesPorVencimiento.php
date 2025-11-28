<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Inscripcion;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DesactivarClientesPorVencimiento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientes:desactivar-vencidos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Desactiva automáticamente clientes cuya membresía ha vencido';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoy = Carbon::now();

        // Obtener inscripciones vencidas
        $inscripcionesVencidas = Inscripcion::where('fecha_vencimiento', '<', $hoy)
            ->where('id_estado', 102) // Estado VENCIDA
            ->get();

        $clientesDesactivados = 0;

        foreach ($inscripcionesVencidas as $inscripcion) {
            $cliente = $inscripcion->cliente;

            // Solo desactivar si está activo
            if ($cliente->activo) {
                $cliente->update(['activo' => false]);
                $clientesDesactivados++;

                $this->line("✓ Cliente desactivado: {$cliente->nombres} (ID: {$cliente->id})");
            }
        }

        $this->info("\n✅ Proceso completado: {$clientesDesactivados} cliente(s) desactivado(s)");

        return Command::SUCCESS;
    }
}
