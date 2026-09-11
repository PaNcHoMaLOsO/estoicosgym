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
