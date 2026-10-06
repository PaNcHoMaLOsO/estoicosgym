<?php

namespace Database\Seeders;

use App\Support\PlantillasDeFabrica;
use Illuminate\Database\Seeder;

/**
 * Las 13 plantillas de correo de PRO GYM: 9 automáticas y 4 manuales.
 *
 * Los HTML están en database/seeders/plantillas, en el repositorio. Antes se
 * leían de storage/app/test_emails, que no se sube, y en el servidor esta
 * siembra no podía correr.
 *
 * Crea las que faltan y actualiza SOLO las que siguen como venían de fábrica:
 * una plantilla que alguien corrigió en Configuración no se pisa. Es lo mismo
 * que hace `php artisan plantillas:actualizar --confirmar`.
 */
class PlantillasProgymSeeder extends Seeder
{
    public function run(): void
    {
        $informe = PlantillasDeFabrica::actualizar(true);

        $cuantas = fn (string $resultado) => count(array_filter($informe, fn ($f) => $f['resultado'] === $resultado));

        $this->command?->info(sprintf(
            '📧 Plantillas de correo: %d creadas, %d actualizadas, %d ya al día, %d editadas (se dejaron como estaban)',
            $cuantas('creada'),
            $cuantas('actualizada'),
            $cuantas('al_dia'),
            $cuantas('editada')
        ));
    }
}
