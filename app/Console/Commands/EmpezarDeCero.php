<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Deja el sistema en cero: sin socios de prueba ni nada de lo que cuelga de ellos.
 *
 * Borra los socios, sus membresías, sus pagos, los avisos por correo, los
 * contratos firmados, lo fiado, las notas del mesón y el historial de cambios
 * y traspasos, con las fotos de los socios. NO toca la configuración: planes y
 * precios, convenios, métodos de pago, motivos, especialistas, la página web,
 * los ajustes, las plantillas de correo, el contrato, los términos, la
 * privacidad ni las cuentas del panel.
 *
 * ANTES DE BORRAR GUARDA UN RESPALDO —un .sql con todo lo que se va, y las
 * fotos— en storage/app/private/respaldos. Un sistema en marcha no tiene
 * «deshacer»: esto es lo que lo reemplaza.
 *
 * Sin --confirmar solo cuenta lo que borraría. En producción pide además
 * --en-produccion: cuando el gimnasio ya tenga socios de verdad, esto no tiene
 * que poder correrse por un descuido.
 */
class EmpezarDeCero extends Command
{
    protected $signature = 'datos:empezar-de-cero
        {--confirmar : Borrar de verdad; sin esto solo muestra lo que se borraría}
        {--en-produccion : Hace falta además si el sistema está en producción}';

    protected $description = 'Borra los socios de prueba y todo lo que cuelga de ellos, deja la configuración, y antes guarda un respaldo';

    /**
     * Lo que se borra, primero lo que depende de otra cosa: así funciona también
     * en una base que revisa las claves foráneas aunque se le pida que no. El
     * respaldo va al revés —los socios primero— para importarse sin tropiezos.
     */
    public const TABLAS = [
        'log_notificaciones',
        'notificaciones',
        'historial_traspasos',
        'historial_cambios',
        // Apuntan a socios y membresías: si se quedaran, serían contratos de
        // nadie.
        'contratos',
        'pagos',
        'fiados',
        'notas',
        'inscripciones',
        'clientes',
    ];

    /** Donde viven las fotos de los socios, en el disco público. */
    private const FOTOS = 'clientes';

    public function handle(): int
    {
        $filas = collect(array_reverse(self::TABLAS))->mapWithKeys(fn (string $tabla) => [$tabla => DB::table($tabla)->count()]);
        $fotos = Storage::disk('public')->allFiles(self::FOTOS);

        $this->table(
            ['Se borra', 'Cuántos'],
            $filas->map(fn (int $n, string $tabla) => [$tabla, $n])->values()->push(['fotos de socios', count($fotos)])->all()
        );

        if (! $this->option('confirmar')) {
            $this->warn('No se borró nada. Para borrar de verdad: php artisan datos:empezar-de-cero --confirmar');

            return self::SUCCESS;
        }

        if (app()->isProduction() && ! $this->option('en-produccion')) {
            $this->error('El sistema está en producción. Si de verdad hay que borrar todo, agrega --en-produccion.');

            return self::FAILURE;
        }

        // Primero el respaldo: si algo falla aquí, no se borra nada.
        $carpeta = 'respaldos/antes-de-empezar-de-cero-' . now()->format('Y-m-d-His');
        Storage::disk('local')->put("{$carpeta}/datos.sql", $this->respaldo());

        foreach ($fotos as $foto) {
            Storage::disk('local')->put("{$carpeta}/fotos/" . basename($foto), Storage::disk('public')->get($foto));
        }

        Schema::disableForeignKeyConstraints();

        try {
            foreach (self::TABLAS as $tabla) {
                // truncate y no delete: la numeración vuelve a empezar en 1.
                DB::table($tabla)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        Storage::disk('public')->deleteDirectory(self::FOTOS);

        $this->info('Listo: el sistema quedó sin socios ni movimientos. La configuración sigue igual.');
        $this->line('Respaldo de lo borrado: ' . Storage::disk('local')->path($carpeta));
        $this->line(DB::getDriverName() === 'pgsql'
            ? 'Para devolverlo: psql -f datos.sql sobre la misma base.'
            : 'Para devolverlo, se importa datos.sql en phpMyAdmin.');

        return self::SUCCESS;
    }

    /**
     * Todo lo que se va a borrar, como sentencias INSERT que se pueden volver
     * a importar tal cual.
     */
    private function respaldo(): string
    {
        $pdo = DB::getPdo();

        /*
         * EN EL IDIOMA DE LA BASE QUE HAY. Estaba escrito para MySQL —comillas
         * invertidas, SET FOREIGN_KEY_CHECKS— y en PostgreSQL, el del servidor,
         * el respaldo no se podía volver a importar: justo cuando hace falta.
         */
        $postgres = DB::getDriverName() === 'pgsql';
        $nombre = fn (string $n) => $postgres ? '"' . $n . '"' : '`' . $n . '`';

        $sql = '-- Respaldo antes de datos:empezar-de-cero, ' . now()->toDateTimeString() . "\n"
            . ($postgres
                ? "-- Para devolverlo: psql -f datos.sql\n\nSET session_replication_role = replica;\n"
                : "-- Para devolverlo: importar este archivo en phpMyAdmin.\n\nSET FOREIGN_KEY_CHECKS=0;\n");

        foreach (array_reverse(self::TABLAS) as $tabla) {
            $sql .= "\n-- {$tabla}\n";
            $lote = [];
            $columnas = null;

            foreach (DB::table($tabla)->cursor() as $fila) {
                $fila = (array) $fila;
                $columnas ??= '(' . implode(', ', array_map($nombre, array_keys($fila))) . ')';

                $lote[] = '(' . implode(', ', array_map(fn ($valor) => match (true) {
                    $valor === null => 'NULL',
                    is_bool($valor) => $postgres ? ($valor ? 'true' : 'false') : ($valor ? '1' : '0'),
                    is_int($valor), is_float($valor) => (string) $valor,
                    default => $pdo->quote((string) $valor),
                }, $fila)) . ')';

                if (count($lote) === 100) {
                    $sql .= 'INSERT INTO ' . $nombre($tabla) . " {$columnas} VALUES\n" . implode(",\n", $lote) . ";\n";
                    $lote = [];
                }
            }

            if ($lote !== []) {
                $sql .= 'INSERT INTO ' . $nombre($tabla) . " {$columnas} VALUES\n" . implode(",\n", $lote) . ";\n";
            }
        }

        return $sql . ($postgres ? "\nSET session_replication_role = DEFAULT;\n" : "\nSET FOREIGN_KEY_CHECKS=1;\n");
    }
}
