<?php

namespace App\Console\Commands;

use App\Support\Permisos;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

/**
 * Enseña que permiso exige cada ruta del panel.
 *
 * Un agujero de permisos no falla: simplemente deja pasar, y no se nota hasta
 * que alguien entra donde no debia. Este comando lo hace visible: lista las
 * rutas protegidas agrupadas por permiso y avisa de las que quedaron sin
 * clasificar.
 */
class RevisarPermisosCommand extends Command
{
    protected $signature = 'permisos:revisar {--rol= : Id del rol para ver a que NO llega}';

    protected $description = 'Revisa que permiso exige cada ruta del panel';

    public function handle(): int
    {
        $porPermiso = [];
        $sinCubrir = [];

        foreach (Route::getRoutes() as $ruta) {
            $nombre = $ruta->getName();

            if (! $nombre || ! preg_match('/^(admin|panel)\./', $nombre)) {
                continue;
            }

            $permiso = Permisos::para($nombre);

            if ($permiso === null) {
                $sinCubrir[] = $nombre;

                continue;
            }

            $porPermiso[$permiso][] = $nombre;
        }

        ksort($porPermiso);

        $total = 0;

        foreach ($porPermiso as $permiso => $rutas) {
            $total += count($rutas);
            $this->line(sprintf('  %-28s %d rutas', $permiso, count($rutas)));
        }

        $this->newLine();
        $this->info("Rutas del panel protegidas: {$total}");

        if ($sinCubrir !== []) {
            $this->newLine();
            $this->error('Rutas SIN clasificar (nadie les exige permiso):');
            foreach ($sinCubrir as $nombre) {
                $this->line("  {$nombre}");
            }

            return self::FAILURE;
        }

        if ($rolId = $this->option('rol')) {
            $this->mostrarRol((int) $rolId, $porPermiso);
        }

        return self::SUCCESS;
    }

    private function mostrarRol(int $rolId, array $porPermiso): void
    {
        $rol = \App\Models\Rol::find($rolId);

        if (! $rol) {
            $this->error("No existe el rol {$rolId}.");

            return;
        }

        $usuario = new \App\Models\User();
        $usuario->setRelation('rol', $rol);

        $this->newLine();
        $this->info("Rol «{$rol->nombre}»:");

        foreach ($porPermiso as $permiso => $rutas) {
            $puede = $usuario->puede($permiso);
            $this->line(sprintf(
                '  %s %-28s %d rutas',
                $puede ? '  sí' : '  NO',
                $permiso,
                count($rutas),
            ));
        }
    }
}
