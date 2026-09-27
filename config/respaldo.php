<?php

return [
    /*
     * El contenedor de Docker donde corre PostgreSQL, si pg_dump no está
     * instalado en el equipo: en este, «estoicosgym-pg». En el servidor, vacío.
     * Ver App\Console\Commands\RespaldarBase.
     */
    'contenedor' => env('RESPALDO_CONTENEDOR'),
];
