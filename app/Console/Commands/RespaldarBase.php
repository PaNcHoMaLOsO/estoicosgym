<?php

namespace App\Console\Commands;

use App\Support\Programador;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * El respaldo diario de la base, completo.
 *
 * LOS RESPALDOS ERAN A MANO: si nadie se acordaba, el día que se rompiera el
 * disco o se borrara algo por error no había a qué volver. Ahora se hace uno
 * por día —de madrugada con el programador, o con la revisión del día si no
 * lo hay— y se guardan los últimos 14 en storage/app/private/respaldos/diarios.
 *
 * Con PostgreSQL usa pg_dump: el del servidor, o el del contenedor de Docker
 * si RESPALDO_CONTENEDOR dice cuál (en este equipo, progym-pg). Con SQLite
 * copia el archivo. Si falla, queda en el registro de fallas del panel.
 *
 * Para volver a un respaldo:
 *   gunzip -c respaldo.sql.gz | psql -U usuario base
 */
class RespaldarBase extends Command
{
    protected $signature = 'base:respaldar {--guardar=14 : Cuántos respaldos diarios se guardan}';

    protected $description = 'Respaldo completo de la base (pg_dump), guardando los últimos 14';

    private const CARPETA = 'respaldos/diarios';

    public function handle(): int
    {
        $conexion = config('database.default');
        $config = config("database.connections.{$conexion}");
        $disco = Storage::disk('local');
        $disco->makeDirectory(self::CARPETA);
        $nombre = self::CARPETA . '/respaldo-' . now()->format('Y-m-d-His');

        if ($config['driver'] === 'sqlite') {
            $archivo = $config['database'];

            if (! is_file($archivo)) {
                $this->warn('La base SQLite es en memoria: no hay nada que respaldar.');

                return self::SUCCESS;
            }

            $disco->put("{$nombre}.sqlite.gz", gzencode(file_get_contents($archivo), 6));
        } elseif ($config['driver'] === 'pgsql') {
            $sql = $this->pgDump($config);
            $disco->put("{$nombre}.sql.gz", gzencode($sql, 6));
        } else {
            $this->error("No se sabe respaldar la conexión «{$config['driver']}».");

            return self::FAILURE;
        }

        // Los más viejos, fuera: se guardan los últimos N.
        $guardar = max(1, (int) $this->option('guardar'));
        $todos = collect($disco->files(self::CARPETA))->filter(fn ($f) => str_contains($f, 'respaldo-'))->sort()->values();
        $todos->slice(0, max(0, $todos->count() - $guardar))->each(fn ($f) => $disco->delete($f));

        Programador::registrar('respaldo');
        $this->info('Respaldo guardado en storage/app/private/' . self::CARPETA . ' ('  . $todos->count() . ' en total, se guardan ' . $guardar . ').');

        return self::SUCCESS;
    }

    private function pgDump(array $config): string
    {
        $contenedor = config('respaldo.contenedor');
        $argumentos = ['pg_dump', '--no-owner', '--no-privileges', '-U', $config['username'], $config['database']];

        // En este equipo PostgreSQL va en Docker y pg_dump vive dentro.
        $proceso = $contenedor
            ? new Process(['docker', 'exec', '-e', 'PGPASSWORD=' . $config['password'], $contenedor, ...$argumentos])
            : new Process([...$argumentos, '-h', (string) $config['host'], '-p', (string) $config['port']], null, ['PGPASSWORD' => (string) $config['password']]);

        $proceso->setTimeout(300);
        $proceso->run();

        if (! $proceso->isSuccessful() || trim($proceso->getOutput()) === '') {
            // Sin la clave en el mensaje: el error de pg_dump ya no la trae.
            throw new \RuntimeException('El respaldo de la base falló: ' . trim($proceso->getErrorOutput() ?: 'pg_dump no devolvió nada.'));
        }

        return $proceso->getOutput();
    }
}
