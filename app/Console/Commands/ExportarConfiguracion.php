<?php

namespace App\Console\Commands;

use App\Support\LlevarLaConfiguracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Saca la configuración de este equipo a un zip, para llevarla a otro.
 *
 * Solo LEE la base. Lo que va y lo que no está en LlevarLaConfiguracion: ni
 * socios, ni usuarios, ni la cuenta de correo; sí planes, convenios, web,
 * plantillas, rutinas, textos legales y ajustes, con sus fotos.
 */
class ExportarConfiguracion extends Command
{
    protected $signature = 'configuracion:exportar
        {--archivo= : Dónde dejar el zip (por defecto storage/app/private/configuracion/configuracion-AAAAMMDD-HHMM.zip)}';

    protected $description = 'Guarda en un zip la configuración (planes, convenios, web, plantillas, ajustes…) para importarla en otro equipo';

    public function handle(): int
    {
        $destino = $this->option('archivo')
            ?: Storage::disk('local')->path('configuracion/configuracion-' . now()->format('Ymd-Hi') . '.zip');

        $resumen = LlevarLaConfiguracion::exportar($destino);

        $this->table(
            ['Tabla', 'Filas'],
            collect($resumen['tablas'])->map(fn ($filas, $tabla) => [$tabla, $filas])->values()->all()
        );

        $this->info("Imágenes: {$resumen['imagenes']}");

        foreach ($resumen['faltantes'] as $ruta) {
            $this->warn("La foto {$ruta} está en la base pero no en el disco: no va en el zip.");
        }

        $this->info("Listo: {$destino}");
        $this->line('Cópialo al servidor y ahí: php artisan configuracion:importar <archivo> (primero sin --confirmar, para ver qué cambia).');

        return self::SUCCESS;
    }
}
