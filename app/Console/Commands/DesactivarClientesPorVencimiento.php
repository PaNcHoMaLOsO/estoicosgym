<?php

namespace App\Console\Commands;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\HistorialTraspaso;
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
         *
         * Aunque deba: la baja no borra la deuda. El saldo de una membresia
         * vencida se sigue cobrando desde Cobrar, que encuentra al socio
         * inactivo si tiene saldo (PagoCrearController y RegistroPagoService).
         */
        /*
         * Y TAMBIEN A QUIEN YA NO TIENE NINGUNA ABIERTA. Solo se miraba la
         * vencida, y quien se quedo sin membresia por otra via no tiene
         * ninguna vencida: seguia activo para siempre, sin plan y contando
         * como socio. Son dos casos:
         *
         * - Una membresia finalizada (cancelada, cambiada de plan): cuenta
         *   igual que una vencida.
         * - El traspaso: la membresia CAMBIA DE DUENO, no se cierra con un
         *   estado, asi que a quien la traspaso no le queda ninguna fila. Se
         *   le reconoce por el historial de traspasos.
         *
         * La regla de «ninguna vigente» no cambia, asi que quien cambio de
         * plan —la nueva queda activa— o recibio otra despues no se toca. Y un
         * socio recien registrado que aun no compra plan tampoco: no tiene ni
         * membresia cerrada ni traspaso.
         */
        $clientes = \App\Models\Cliente::where('activo', true)
            ->where(fn ($sinPlan) => $sinPlan
                ->whereHas('inscripciones', fn ($q) => $q
                    ->where(fn ($cerrada) => $cerrada
                        ->where(fn ($vencida) => $vencida
                            ->where('id_estado', EstadosCodigo::INSCRIPCION_VENCIDA)
                            ->where('fecha_vencimiento', '<', $hoy))
                        ->orWhereIn('id_estado', EstadosCodigo::INSCRIPCION_FINALIZADOS)))
                ->orWhereIn('id', HistorialTraspaso::select('cliente_origen_id')))
            // Activa, pausada o suspendida: las que piden al socio activo.
            ->whereDoesntHave('inscripciones', fn ($q) => $q
                ->whereIn('id_estado', EstadosCodigo::INSCRIPCION_REQUIERE_CLIENTE_ACTIVO))
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
