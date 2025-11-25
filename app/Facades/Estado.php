<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Estado extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'estado-helper';
    }
}
