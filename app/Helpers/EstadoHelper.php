<?php

namespace App\Helpers;

use App\Models\Estado;

class EstadoHelper
{
    private static $colores = [
        'success' => '#28a745',
        'danger' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8',
        'primary' => '#007bff',
        'secondary' => '#6c757d',
    ];

    private static $iconos = [
        'success' => '<i class="fas fa-check-circle"></i>',
        'danger' => '<i class="fas fa-times-circle"></i>',
        'warning' => '<i class="fas fa-exclamation-circle"></i>',
        'info' => '<i class="fas fa-info-circle"></i>',
        'primary' => '<i class="fas fa-circle"></i>',
        'secondary' => '<i class="fas fa-ban"></i>',
    ];

    /**
     * Obtener badge con color del estado
     */
    public static function badge($estado)
    {
        if (is_numeric($estado)) {
            $estado = Estado::find($estado);
        }

        if (!$estado) {
            return '<span class="badge badge-secondary">Desconocido</span>';
        }

        $color = $estado->color ?? 'secondary';
        return '<span class="badge badge-' . $color . '">' . $estado->nombre . '</span>';
    }

    /**
     * Obtener badge con icono y color
     */
    public static function badgeWithIcon($estado)
    {
        if (is_numeric($estado)) {
            $estado = Estado::find($estado);
        }

        if (!$estado) {
            return '<span class="badge badge-secondary" style="font-size: 0.85em;"><i class="fas fa-circle fa-fw"></i> Desconocido</span>';
        }

        $color = $estado->color ?? 'secondary';
        $icono = self::$iconos[$color] ?? '<i class="fas fa-circle"></i>';
        $icono = str_replace('fas ', 'fas fa-fw ', $icono);
        
        return '<span class="badge badge-' . $color . '" style="font-size: 0.85em;">' . $icono . ' ' . $estado->nombre . '</span>';
    }

    /**
     * Obtener clase CSS para el color
     */
    public static function getClass($estado)
    {
        if (is_numeric($estado)) {
            $estado = Estado::find($estado);
        }

        return $estado?->color ? 'badge-' . $estado->color : 'badge-secondary';
    }

    /**
     * Obtener color hexadecimal
     */
    public static function getHexColor($estado)
    {
        if (is_numeric($estado)) {
            $estado = Estado::find($estado);
        }

        $color = $estado?->color ?? 'secondary';
        return self::$colores[$color] ?? '#6c757d';
    }

    /**
     * Obtener etiqueta con estilos personalizados
     */
    public static function tag($estado, $showIcon = true)
    {
        if (is_numeric($estado)) {
            $estado = Estado::find($estado);
        }

        if (!$estado) {
            return '<span style="padding: 5px 10px; border-radius: 3px; background-color: #6c757d; color: white; font-size: 12px;">Desconocido</span>';
        }

        $color = $estado->color ?? 'secondary';
        $hexColor = self::$colores[$color] ?? '#6c757d';
        $icono = $showIcon ? (self::$iconos[$color] ?? '<i class="fas fa-circle"></i>') . ' ' : '';
        
        return '<span style="padding: 5px 10px; border-radius: 3px; background-color: ' . $hexColor . '; color: white; font-size: 12px; display: inline-block;">' . $icono . $estado->nombre . '</span>';
    }
}
