<?php

namespace App\Helpers;

/**
 * Clase para manejar formateo de precios
 * Utiliza punto (.) como separador de miles
 * Ejemplo: 40000 → 40.000 | 1500000 → 1.500.000
 */
class PrecioHelper
{
    /**
     * Formatea un precio con separador de miles (punto)
     * Sin decimales para visualización
     * 
     * @param float|int $precio
     * @return string
     */
    public static function formato($precio): string
    {
        return number_format($precio, 0, '.', '.');
    }

    /**
     * Formatea un precio con separador de miles y decimales
     * Ejemplo: 40000.50 → 40.000,50
     * 
     * @param float $precio
     * @param int $decimales
     * @return string
     */
    public static function formatoConDecimales($precio, $decimales = 2): string
    {
        return number_format($precio, $decimales, ',', '.');
    }

    /**
     * Convierte un precio formateado de vuelta a número
     * Ejemplo: "40.000" → 40000 | "40.000,50" → 40000.50
     * 
     * @param string $precioFormateado
     * @return float
     */
    public static function desformato($precioFormateado): float
    {
        // Remover puntos (separadores de miles)
        $precio = str_replace('.', '', $precioFormateado);
        // Reemplazar coma por punto (decimal)
        $precio = str_replace(',', '.', $precio);
        return (float) $precio;
    }

    /**
     * Formatea con símbolo de moneda
     * Ejemplo: 40000 → $40.000
     * 
     * @param float|int $precio
     * @return string
     */
    public static function formatoConMoneda($precio): string
    {
        return '$' . self::formato($precio);
    }

    /**
     * Formatea con símbolo de moneda y decimales
     * Ejemplo: 40000.50 → $40.000,50
     * 
     * @param float $precio
     * @return string
     */
    public static function formatoConMonedaYDecimales($precio): string
    {
        return '$' . self::formatoConDecimales($precio);
    }

    /**
     * Valida si una cadena es un precio válido
     * 
     * @param string $precio
     * @return bool
     */
    public static function esValido($precio): bool
    {
        // Remover símbolo $ si existe
        $precio = trim(str_replace('$', '', $precio));
        
        // Convertir a número
        $numero = self::desformato($precio);
        
        // Validar que sea un número positivo
        return is_numeric($numero) && $numero > 0;
    }
}
