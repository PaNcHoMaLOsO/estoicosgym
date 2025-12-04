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
     * El token solo se invalida si el proceso fue exitoso (llamar a invalidateFormToken)
     *
     * @param Request $request
     * @param string $action Identificador único de la acción
     * @return bool
     */
    protected function validateFormToken(Request $request, string $action): bool
    {
        $token = $request->input('form_submit_token');
        
        if (!$token) {
            return true; // Si no hay token, permitir (para retrocompatibilidad)
        }
        
        // Crear clave única en cache
        $userId = optional(auth('web')->user())->id ?? session()->getId();
        $cacheKey = 'form_completed_' . $userId . '_' . $action . '_' . substr($token, 0, 20);
        
        // Si el token ya está marcado como COMPLETADO, es un doble envío
        if (Cache::has($cacheKey)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Marcar el token como usado/completado después de un proceso exitoso
     * Llamar este método DESPUÉS de que la operación haya sido exitosa
     *
     * @param Request $request
     * @param string $action Identificador único de la acción
     * @return void
     */
    protected function invalidateFormToken(Request $request, string $action): void
    {
        $token = $request->input('form_submit_token');
        
        if (!$token) {
            return;
        }
        
        $userId = optional(auth('web')->user())->id ?? session()->getId();
        $cacheKey = 'form_completed_' . $userId . '_' . $action . '_' . substr($token, 0, 20);
        
        // Marcar como completado por 60 segundos (suficiente para evitar doble click)
        Cache::put($cacheKey, true, 60);
    }
}
