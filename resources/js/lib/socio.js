/**
 * El RUT y el celular de un socio, escritos siempre igual.
 *
 * Vivían dentro del alta; la edición no los usaba y ahí el RUT y el celular
 * se escribían a mano, sin formato ni comprobación. Ahora las dos pantallas
 * usan estas mismas funciones.
 */

/** Lo que trae el celular antes de escribir nada: el prefijo de un móvil chileno. */
export const PREFIJO = '+56 9 ';

/** Un teléfono que solo tiene el prefijo es un teléfono sin escribir. */
export const soloPrefijo = (telefono) => String(telefono ?? '').replace(/\D/g, '') === '569';

/**
 * El RUT con puntos y guion mientras se escribe: 123456789 → 12.345.678-9.
 *
 * El último carácter es siempre el dígito verificador, y la K solo vale ahí.
 */
export function formatearRut(texto) {
    const limpio = String(texto ?? '').toUpperCase().replace(/[^0-9K]/g, '').slice(0, 9);

    if (limpio.length < 2) {
        return limpio;
    }

    const cuerpo = limpio.slice(0, -1).replace(/K/g, '');

    return `${cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.')}-${limpio.slice(-1)}`;
}

/**
 * Si el RUT está bien escrito: el dígito verificador se calcula, no se cree.
 *
 * Se comprueba EN EL MESÓN y no al guardar: un dígito mal tecleado que se
 * descubre después del formulario entero obliga a revisar el carnet con la
 * persona ya de espaldas. Un pasaporte no lleva verificador y no pasa por aquí.
 */
export function rutValido(texto) {
    const limpio = String(texto ?? '').toUpperCase().replace(/[^0-9K]/g, '');

    if (limpio.length < 8 || limpio.length > 9) {
        return false;
    }

    const cuerpo = limpio.slice(0, -1);
    const dv = limpio.slice(-1);
    let suma = 0;
    let factor = 2;

    for (let i = cuerpo.length - 1; i >= 0; i -= 1) {
        suma += Number(cuerpo[i]) * factor;
        factor = factor > 6 ? 2 : factor + 1;
    }

    const resto = 11 - (suma % 11);

    return dv === (resto === 11 ? '0' : resto === 10 ? 'K' : String(resto));
}
