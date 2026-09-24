<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\ContenidoWeb;
use App\Models\Especialista;
use App\Support\FotoLiviana;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pasa a livianas las fotos que ya estaban subidas.
 *
 * Las nuevas se guardan livianas solas (App\Support\FotoLiviana). Esto arregla
 * las de antes: la galería, la foto de la tienda, los especialistas y las
 * caras de los socios. Cada una se reemplaza SOLO si la nueva pesa menos, y la
 * ficha que la usaba pasa a apuntar a la nueva antes de borrar la vieja.
 *
 * Sin --confirmar solo cuenta cuánto se ahorraría.
 *
 * LAS ORIGINALES SE QUEDAN, salvo que se pida --borrar-viejas. Las fotos de
 * la web viajan en el repositorio: si esto las borrara aquí, al actualizar el
 * servidor con git desaparecerían allá ANTES de correr el comando, y la
 * galería quedaría apuntando a fotos que ya no están.
 */
class AligerarFotos extends Command
{
    protected $signature = 'web:aligerar-fotos
                            {--confirmar : Hacerlo de verdad}
                            {--borrar-viejas : Borrar también las fotos originales}';

    protected $description = 'Pasa a WebP y achica las fotos ya subidas (galería, tienda, especialistas, socios)';

    private int $antes = 0;

    private int $despues = 0;

    private int $cambiadas = 0;

    public function handle(): int
    {
        $disco = Storage::disk('public');
        $hacer = (bool) $this->option('confirmar');

        foreach (ContenidoWeb::whereNotNull('imagen')->get() as $contenido) {
            if ($nueva = $this->aligerar($contenido->imagen, 'web', 1600, $hacer)) {
                $contenido->update(['imagen' => $nueva]);
                $this->borrarViejaSiSePidio();
            }
        }

        foreach (Especialista::whereNotNull('foto')->get() as $persona) {
            if ($nueva = $this->aligerar($persona->foto, 'especialistas', 1200, $hacer)) {
                $persona->update(['foto' => $nueva]);
                $this->borrarViejaSiSePidio();
            }
        }

        foreach (Cliente::withTrashed()->whereNotNull('foto_perfil')->get() as $socio) {
            if ($nueva = $this->aligerar($socio->foto_perfil, 'clientes', 800, $hacer)) {
                $socio->update(['foto_perfil' => $nueva]);
                $this->borrarViejaSiSePidio();
            }
        }

        // La foto de la tienda no está en ninguna ficha: se busca por su nombre,
        // sea cual sea la extensión, así que se guarda con el mismo nombre.
        foreach ($disco->files('web') as $archivo) {
            if (preg_match('#^web/(tienda|tienda-logo|tienda-icono)\.(jpe?g|png)$#i', $archivo, $m)) {
                if ($this->aligerar($archivo, 'web', 1600, $hacer, $m[1])) {
                    $this->borrarViejaSiSePidio();
                }
            }
        }

        $this->info(sprintf(
            '%d fotos: de %s a %s (%d%% menos).',
            $this->cambiadas,
            $this->kb($this->antes),
            $this->kb($this->despues),
            $this->antes > 0 ? round(100 - $this->despues * 100 / $this->antes) : 0
        ));

        if (! $hacer) {
            $this->warn('Esto fue solo la cuenta. Vuelve a correrlo con --confirmar para hacerlo.');
        }

        return self::SUCCESS;
    }

    private string $viejo = '';

    /**
     * La versión liviana de una foto: su ruta nueva, o null si no conviene.
     */
    private function aligerar(string $ruta, string $carpeta, int $maximo, bool $hacer, ?string $mismoNombre = null): ?string
    {
        $disco = Storage::disk('public');

        if (str_ends_with(strtolower($ruta), '.webp') || ! $disco->exists($ruta)) {
            return null;
        }

        $original = $disco->get($ruta);
        $liviana = FotoLiviana::desde($original, $maximo, 80, $disco->path($ruta));

        // Solo si de verdad pesa menos: una foto ya chica no se toca.
        if (! $liviana || strlen($liviana['bytes']) >= strlen($original)) {
            return null;
        }

        $this->antes += strlen($original);
        $this->despues += strlen($liviana['bytes']);
        $this->cambiadas++;

        if (! $hacer) {
            return null;
        }

        $nueva = $carpeta . '/' . ($mismoNombre ?? Str::random(32)) . '.' . $liviana['extension'];
        $disco->put($nueva, $liviana['bytes']);
        $this->viejo = $ruta;

        return $nueva;
    }

    private function borrarViejaSiSePidio(): void
    {
        if ($this->option('borrar-viejas') && $this->viejo !== '') {
            Storage::disk('public')->delete($this->viejo);
        }
    }

    private function kb(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1, ',', '.') . ' MB'
            : number_format($bytes / 1024, 0, ',', '.') . ' KB';
    }
}
