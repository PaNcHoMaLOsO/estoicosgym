const relativo = new Intl.RelativeTimeFormat('es', { numeric: 'auto' });

const PASOS = [
    ['year', 31536000],
    ['month', 2592000],
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
];

/** «hace 3 minutos», «ayer», «hace 2 semanas»… a partir de una fecha ISO. */
export function haceCuanto(iso) {
    const segundos = Math.round((new Date(iso).getTime() - Date.now()) / 1000);

    for (const [unidad, tamano] of PASOS) {
        if (Math.abs(segundos) >= tamano) {
            return relativo.format(Math.round(segundos / tamano), unidad);
        }
    }

    return 'recién';
}

/**
 * La fecha de hoy en Chile, como «2026-09-28».
 *
 * NO toISOString(): esa da la fecha de Greenwich, y desde las 21:00 (20:00 en
 * verano) en Chile ya es mañana allá. Los formularios de cobro proponían la
 * fecha de mañana y el pago caía en el día o el mes equivocado. Se llama al
 * usarla, no al cargar el archivo: una pestaña abierta desde ayer no se queda
 * con la fecha de ayer.
 */
export function hoyEnChile() {
    return new Date().toLocaleDateString('en-CA', { timeZone: 'America/Santiago' });
}
