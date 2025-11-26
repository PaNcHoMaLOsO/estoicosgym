<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade para acceder a PrecioHelper
 * 
 * Uso en vistas Blade:
 * {{ Precio::formato(40000) }}           // 40.000
 * {{ Precio::formatoConMoneda(40000) }}  // $40.000
 * {{ Precio::desformato('40.000') }}     // 40000
 */
class Precio extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'precio';
    }
}
