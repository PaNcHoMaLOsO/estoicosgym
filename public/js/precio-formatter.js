/**
 * Precio Formatter
 * Formatea automáticamente campos de precio mientras se escribe
 * Formato: 40.000 (con punto como separador de miles)
 */

class PrecioFormatter {
    /**
     * Formatea un número con punto como separador de miles
     * @param {number|string} valor - El valor a formatear
     * @param {number} decimales - Cantidad de decimales (default: 0)
     * @returns {string} - Valor formateado
     */
    static formatear(valor, decimales = 0) {
        if (!valor && valor !== 0) return '';
        
        // Convertir a número
        const numero = parseFloat(valor);
        if (isNaN(numero)) return '';
        
        // Formatear con separador de miles (punto)
        if (decimales === 0) {
            return numero.toLocaleString('es-CL', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        } else {
            return numero.toLocaleString('es-CL', {
                minimumFractionDigits: decimales,
                maximumFractionDigits: decimales
            });
        }
    }

    /**
     * Limpia un valor formateado para obtener el número puro
     * @param {string} valor - El valor formateado (ej: "40.000,50")
     * @returns {number} - El número sin formato
     */
    static limpiar(valor) {
        if (!valor) return 0;
        
        // Remover símbolo $ si existe
        valor = valor.replace('$', '').trim();
        
        // Remover puntos (separadores de miles)
        valor = valor.replace(/\./g, '');
        
        // Reemplazar coma por punto (decimal)
        valor = valor.replace(',', '.');
        
        return parseFloat(valor) || 0;
    }

    /**
     * Inicializa un campo de precio para formatear automáticamente
     * @param {string} selectorId - ID del elemento input
     * @param {boolean} conDecimales - Si debe mostrar decimales (default: false)
     */
    static iniciarCampo(selectorId, conDecimales = false) {
        const elemento = document.getElementById(selectorId);
        if (!elemento) return;

        const decimales = conDecimales ? 2 : 0;

        // Al perder el foco (blur) - formatear
        elemento.addEventListener('blur', function() {
            const valor = this.value;
            const numero = PrecioFormatter.limpiar(valor);
            
            if (numero > 0) {
                this.value = PrecioFormatter.formatear(numero, decimales);
            }
        });

        // Al ganar el foco (focus) - mostrar valor limpio para edición
        elemento.addEventListener('focus', function() {
            const valor = this.value;
            const numero = PrecioFormatter.limpiar(valor);
            if (numero > 0) {
                this.value = numero;
            }
        });

        // Al escribir (input) - permitir solo números, puntos y comas
        elemento.addEventListener('input', function(event) {
            let valor = this.value.replace(/[^\d,]/g, '');
            this.value = valor;
        });
    }

    /**
     * Inicializa múltiples campos de precio
     * @param {string} clase - Clase CSS de los elementos
     * @param {boolean} conDecimales - Si debe mostrar decimales
     */
    static iniciarTodos(clase = 'precio-input', conDecimales = false) {
        const elementos = document.querySelectorAll('.' + clase);
        elementos.forEach((elemento, index) => {
            const id = elemento.id || `precio-${index}`;
            if (!elemento.id) elemento.id = id;
            PrecioFormatter.iniciarCampo(id, conDecimales);
        });
    }

    /**
     * Obtiene el valor numérico de un campo formateado
     * @param {string} selectorId - ID del elemento
     * @returns {number} - Valor numérico limpio
     */
    static obtenerNumero(selectorId) {
        const elemento = document.getElementById(selectorId);
        if (!elemento) return 0;
        return PrecioFormatter.limpiar(elemento.value);
    }

    /**
     * Establece el valor de un campo con formato
     * @param {string} selectorId - ID del elemento
     * @param {number} valor - Valor a establecer
     * @param {boolean} conDecimales - Si debe mostrar decimales
     */
    static establecerValor(selectorId, valor, conDecimales = false) {
        const elemento = document.getElementById(selectorId);
        if (!elemento) return;
        
        const decimales = conDecimales ? 2 : 0;
        elemento.value = PrecioFormatter.formatear(valor, decimales);
    }

    /**
     * Valida que un campo de precio tenga un valor válido
     * @param {string} selectorId - ID del elemento
     * @param {number} minimo - Valor mínimo permitido (default: 0)
     * @returns {boolean} - true si es válido
     */
    static esValido(selectorId, minimo = 0) {
        const valor = PrecioFormatter.obtenerNumero(selectorId);
        return valor > minimo;
    }
}

// Exportar para uso en módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PrecioFormatter;
}
