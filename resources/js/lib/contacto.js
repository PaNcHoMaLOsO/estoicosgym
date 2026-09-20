/**
 * El enlace de WhatsApp para un celular escrito de cualquier forma.
 *
 * Los chilenos se guardan con 9 dígitos (912345678) y se les pone el 56; los
 * extranjeros ya traen su código de país.
 */
export function whatsapp(celular) {
    const digitos = String(celular).replace(/\D/g, '');
    const numero = digitos.length === 9 && digitos.startsWith('9') ? `56${digitos}` : digitos;

    return `https://wa.me/${numero}`;
}

/** El celular chileno legible: 912345678 → +56 9 1234 5678. */
export function celularLegible(celular) {
    const texto = String(celular ?? '');
    const digitos = texto.replace(/\D/g, '');

    if (digitos.length === 9 && digitos.startsWith('9')) {
        return `+56 9 ${digitos.slice(1, 5)} ${digitos.slice(5)}`;
    }

    return texto;
}
