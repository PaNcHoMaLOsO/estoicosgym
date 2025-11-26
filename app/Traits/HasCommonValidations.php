<?php

namespace App\Traits;

trait HasCommonValidations
{
    /**
     * Validaciones comunes para métodos simples de CRUD
     */
    public function getCommonRules($model, $id = null): array
    {
        // Validaciones por defecto - Override en el controlador si es necesario
        return [];
    }

    /**
     * Mensaje de éxito genérico
     */
    protected function successMessage(string $action): string
    {
        return ucfirst($action) . ' completado exitosamente';
    }
}
