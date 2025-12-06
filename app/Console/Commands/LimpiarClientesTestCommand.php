<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarClientesTestCommand extends Command
{
    protected $signature = 'limpiar:clientes-test {--force : Ejecutar sin confirmación}';
    protected $description = 'Elimina SOLO los clientes de prueba creados con ClientesTestSeeder';

    public function handle()
    {
        $this->info("🧹 LIMPIEZA DE CLIENTES DE PRUEBA");
        $this->info("═════════════════════════════════");
        $this->newLine();

        // Lista de emails de clientes de prueba que creamos
        $emailsTest = [
            'test.nuevo@progym.test',
            'test.parcial@progym.test',
            'test.pendiente@progym.test',
            'test.mixto@progym.test',
            'test.completado@progym.test',
            'test.porvencer@progym.test',
            'test.vencido@progym.test',
            'test.deuda@progym.test',
            'test.pausado@progym.test',
            'test.reactivado@progym.test',
            'test.pasediario@progym.test',
        ];

        // RUTs de clientes de prueba
        $rutsTest = [
            '20111222-3',
            '19222333-4',
            '18333444-5',
            '21444555-6',
            '17555666-7',
            '19555666-8',
            '18666777-9',
            '20777888-0',
            '19888999-1',
            '21999000-2',
            '22000111-2',
        ];

        // Buscar clientes de prueba
        $clientesTest = DB::table('clientes')
            ->where(function($query) use ($emailsTest, $rutsTest) {
                $query->whereIn('email', $emailsTest)
                      ->orWhereIn('run_pasaporte', $rutsTest);
            })
            ->get();

        if ($clientesTest->isEmpty()) {
            $this->info("✅ No hay clientes de prueba para eliminar");
            return 0;
        }

        $this->warn("Se encontraron {$clientesTest->count()} clientes de prueba:");
        $this->newLine();

        foreach ($clientesTest as $cliente) {
            $this->line("  👤 {$cliente->nombres} {$cliente->apellido_paterno} ({$cliente->email})");
        }

        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('¿Deseas eliminar estos clientes y sus datos relacionados?', false)) {
                $this->info('Operación cancelada');
                return 0;
            }
        }

        $idsClientes = $clientesTest->pluck('id')->toArray();

        $this->info("🗑️  Eliminando datos relacionados...");

        // Obtener IDs de inscripciones
        $inscripciones = DB::table('inscripciones')
            ->whereIn('id_cliente', $idsClientes)
            ->get();
        
        $idsInscripciones = $inscripciones->pluck('id')->toArray();

        // Eliminar pagos
        if (!empty($idsInscripciones)) {
            $pagosEliminados = DB::table('pagos')
                ->whereIn('id_inscripcion', $idsInscripciones)
                ->delete();
            $this->line("  ✅ Pagos eliminados: {$pagosEliminados}");
        }

        // Eliminar inscripciones
        $inscripcionesEliminadas = DB::table('inscripciones')
            ->whereIn('id_cliente', $idsClientes)
            ->delete();
        $this->line("  ✅ Inscripciones eliminadas: {$inscripcionesEliminadas}");

        // Eliminar clientes
        $clientesEliminados = DB::table('clientes')
            ->whereIn('id', $idsClientes)
            ->delete();
        $this->line("  ✅ Clientes eliminados: {$clientesEliminados}");

        $this->newLine();
        $this->info("✨ Limpieza completada exitosamente");
        $this->info("Ahora puedes ejecutar: php artisan db:seed --class=ClientesTestSeeder");

        return 0;
    }
}
