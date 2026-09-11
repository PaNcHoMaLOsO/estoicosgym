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

        /*
         * SE MIRA AL SOCIO, NO A CADA MEMBRESIA VENCIDA.
         *
         * Se recorrian las inscripciones vencidas una por una y se desactivaba
         * a su socio sin mirar nada mas. Pero una membresia vencida es lo
         * normal en quien RENUEVA: la de antes se cierra como vencida y la
         * nueva queda activa. Asi, quien acababa de pagar amanecia dado de
         * baja —fuera del listado, fuera del buscador del meson—, y si alguien
         * lo reactivaba a mano, la noche siguiente volvia a pasar. Lo mismo
         * con quien tiene la membresia en pausa.
         *
         * Se da de baja a quien tiene alguna vencida Y ninguna vigente.
         */
        $clientes = \App\Models\Cliente::where('activo', true)
            ->whereHas('inscripciones', fn ($q) => $q
                ->where('id_estado', 102) // Vencida
                ->where('fecha_vencimiento', '<', $hoy))
            ->whereDoesntHave('inscripciones', fn ($q) => $q
                ->whereIn('id_estado', [100, 101])) // Activa o pausada
            ->get();

        $clientesDesactivados = 0;

        foreach ($clientes as $cliente) {

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
