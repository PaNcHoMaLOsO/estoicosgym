<?php

namespace App\Console\Commands;

use App\Services\CorreoService;
use Illuminate\Console\Command;

/**
 * Comprueba la configuracion de correo SIN enviar nada.
 *
 * Existe aparte de `test:email` a proposito: aquel manda un mensaje de verdad,
 * y para saber si la clave quedo bien puesta no hace falta molestar a nadie ni
 * gastar cuota.
 */
class VerificarCorreoCommand extends Command
{
    protected $signature = 'correo:verificar {via? : smtp o resend; por defecto, las configuradas}';

    protected $description = 'Verifica la configuración de correo sin enviar nada';

    public function handle(CorreoService $correo): int
    {
        $principal = $correo->nombrePrincipal();
        $respaldo = $correo->nombreRespaldo();

        $vias = $this->argument('via')
            ? [$this->argument('via')]
            : array_values(array_unique(array_filter([$principal, $respaldo])));

        $this->line('Remitente : ' . config('mail.from.address'));
        $this->line('Principal : ' . $principal);
        $this->line('Respaldo  : ' . ($respaldo ?: 'ninguno'));
        $this->newLine();

        $fallos = 0;

        foreach ($vias as $via) {
            try {
                $motivo = $correo->comprobar($via);
                $descripcion = $correo->descripcion($via);
            } catch (\Throwable $e) {
                $this->error("  {$via}: {$e->getMessage()}");
                $fallos++;

                continue;
            }

            if ($motivo === null) {
                $this->info("  ✓ {$descripcion} — operativo");

                continue;
            }

            $this->error("  ✗ {$descripcion} — {$motivo}");
            $fallos++;
        }

        $this->newLine();

        if ($fallos === 0) {
            $this->info('El envío de correos está operativo.');

            return self::SUCCESS;
        }

        // Un respaldo caido no deja al sistema sin correo, pero conviene saberlo.
        if ($fallos < count($vias)) {
            $this->warn('Hay una vía disponible, pero revisa la que falla.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('Recordatorios:');
        $this->line('  · Gmail exige verificación en 2 pasos y una contraseña de aplicación');
        $this->line('    de 16 caracteres, pegada SIN los espacios con que la muestra.');
        $this->line('  · Resend solo envía desde un dominio verificado en su panel.');

        return self::FAILURE;
    }
}
