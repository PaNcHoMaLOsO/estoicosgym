<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Trait para validar tokens de formulario y prevenir envíos duplicados
 */
trait ValidatesFormToken
{
    /**
     * Validar que no sea un doble envío usando cache
     *
     * @param Request $request
     * @param string $action Identificador único de la acción
     * @return bool
     */
    protected function validateFormToken(Request $request, string $action): bool
    {
        $token = $request->input('form_submit_token');
        
        if (!$token) {
            return false;
        }
        
        // Crear clave única en cache con tiempo de vida de 10 segundos
        $userId = optional(auth('web')->user())->id ?? session()->getId();
        $cacheKey = 'form_submit_' . $userId . '_' . $action . '_' . substr($token, 0, 20);
        
        // Si el token existe en cache, es un doble envío
        if (Cache::has($cacheKey)) {
            return false;
        }
        
        // Guardar token en cache
        Cache::put($cacheKey, true, 10);
        
        return true;
    }
}
