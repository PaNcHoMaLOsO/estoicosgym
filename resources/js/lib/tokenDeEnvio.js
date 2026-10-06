import { useRef } from 'react';

/**
 * Un token por cada vez que se abre un formulario.
 *
 * Viaja como `form_submit_token` y el servidor lo reserva al guardar
 * (ValidatesFormToken): el mismo formulario enviado dos veces —doble clic, la
 * red que reintenta— se guarda una sola. Tras cada guardado bueno se pide otro,
 * porque lo siguiente que se apunte es otra cosa, aunque sea igual: dos «agua
 * $1.000» seguidas pueden ser dos aguas.
 */
export function nuevoToken() {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    // Sin randomUUID (http sin candado en la red del gimnasio, navegadores
    // viejos): un UUID v4 armado a mano, con getRandomValues si está.
    const bytes = new Uint8Array(16);

    if (typeof crypto !== 'undefined' && typeof crypto.getRandomValues === 'function') {
        crypto.getRandomValues(bytes);
    } else {
        for (let i = 0; i < 16; i++) {
            bytes[i] = Math.floor(Math.random() * 256);
        }
    }

    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;

    const hex = Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');

    return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
}

/**
 * El token del formulario: `actual()` el de ahora, `renovar()` tras guardar.
 */
export function useTokenDeEnvio() {
    const token = useRef(null);

    if (token.current === null) {
        token.current = nuevoToken();
    }

    return {
        actual: () => token.current,
        renovar: () => {
            token.current = nuevoToken();
        },
    };
}
