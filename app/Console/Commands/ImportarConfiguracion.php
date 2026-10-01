<?php

namespace App\Console\Commands;

use App\Support\Ajustes;
use App\Support\EstadoDeConfiguracion;
use App\Support\LlevarLaConfiguracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Trae al equipo la configuración que sacó configuracion:exportar en otro.
 *
 * SIN --confirmar NO ESCRIBE NADA: dice, tabla por tabla, qué filas son
 * nuevas, cuáles cambiarían y cuáles ya están iguales. En el servidor de
 * verdad hay socios pagando; mirar antes de tocar cuesta un comando.
 *
 * CON --confirmar: respaldo primero (si hay PostgreSQL), y después todo en una
 * transacción: o entra entera o no entra nada. Nunca borra: lo que el
 * servidor tenga y el archivo no, se queda como está.
 */
class ImportarConfiguracion extends Command
{
    protected $signature = 'configuracion:importar
        {archivo : El zip que sacó configuracion:exportar}
        {--confirmar : Escribir de verdad; sin esto solo muestra lo que cambiaría}';

    protected $description = 'Importa la configuración de un zip de configuracion:exportar (sin --confirmar solo muestra qué cambiaría)';

    public function handle(): int
    {
        $archivo = $this->argument('archivo');

        if (! is_file($archivo) && is_file(base_path($archivo))) {
            $archivo = base_path($archivo);
        }

        try {
            $contenido = LlevarLaConfiguracion::leer($archivo);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line("Exportado el {$contenido['manifiesto']['creado']} desde " . ($contenido['manifiesto']['desde'] ?? '¿?') . '.');

        if (! $this->option('confirmar')) {
            $this->mostrar(LlevarLaConfiguracion::importar($contenido, false));
            $this->warn('No se cambió nada. Para importar de verdad, lo mismo con --confirmar.');

            return self::SUCCESS;
        }

        $this->respaldar();

        try {
            $resultado = DB::transaction(fn () => LlevarLaConfiguracion::importar($contenido, true));
        } catch (Throwable $e) {
            $this->error('No se importó nada, la base quedó como estaba: ' . $e->getMessage());

            return self::FAILURE;
        }

        // Los ajustes y los avisos del menú («falta el precio de…») están en
        // caché: sin olvidarlos, el panel seguiría mostrando lo de antes.
        Ajustes::olvidar();
        Cache::memo()->forget(EstadoDeConfiguracion::CACHE_AVISOS);
        Cache::forget(EstadoDeConfiguracion::CACHE_AVISOS);

        $this->mostrar($resultado);
        $this->info('Configuración importada.');

        return self::SUCCESS;
    }

    /**
     * Un respaldo antes de escribir, si se puede.
     *
     * Solo con PostgreSQL: es lo que hay en el servidor, y base:respaldar con
     * un SQLite en memoria no tiene qué guardar. Si falla se avisa y se sigue:
     * quien puso --confirmar ya decidió importar, y la importación va en una
     * transacción que no borra nada.
     */
    private function respaldar(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! array_key_exists('base:respaldar', Artisan::all())) {
            return;
        }

        $this->line('Respaldando la base antes de importar…');

        try {
            if (Artisan::call('base:respaldar') !== self::SUCCESS) {
                throw new RuntimeException(trim(Artisan::output()));
            }

            $this->line(trim(Artisan::output()));
        } catch (Throwable $e) {
            $this->warn('No se pudo hacer el respaldo (' . $e->getMessage() . '). Se sigue igual porque pusiste --confirmar.');
        }
    }

    private function mostrar(array $resultado): void
    {
        $this->table(
            ['Tabla', 'Nuevas', 'Se actualizan', 'Iguales', 'Omitidas'],
            collect($resultado['tablas'])->map(fn ($c, $tabla) => [
                $tabla, $c['nuevas'], $c['actualiza'], $c['iguales'], $c['omitidas'],
            ])->values()->all()
        );

        $this->line("Imágenes: {$resultado['imagenes']['nuevas']} nuevas, {$resultado['imagenes']['ya_estaban']} ya estaban.");
    }
}
