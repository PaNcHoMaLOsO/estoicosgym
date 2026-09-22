/**
 * El enlace de WhatsApp para un celular escrito de cualquier forma.
 *
 * Los chilenos se guardan con 9 dígitos (912345678) y se les pone el 56; los
 * extranjeros ya traen su código de país.
 *
 * Con `texto`, el mensaje queda escrito en el cuadro y lo manda la persona:
 * nada sale solo desde el panel.
 */
export function whatsapp(celular, texto = '') {
    const digitos = String(celular).replace(/\D/g, '');
    const numero = digitos.length === 9 && digitos.startsWith('9') ? `56${digitos}` : digitos;

    // El mensaje va escrito pero NO enviado: WhatsApp lo deja en el cuadro de
    // texto y lo manda la persona. Un cobro se relee antes de salir.
    return texto ? `https://wa.me/${numero}?text=${encodeURIComponent(texto)}` : `https://wa.me/${numero}`;
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
