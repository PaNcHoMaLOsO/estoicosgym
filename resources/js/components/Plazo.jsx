/**
 * Cuánto le queda a una membresía, dicho en palabras y con color solo cuando
 * pide hacer algo: vencida en rojo, la última semana en amarillo, el resto gris.
 */
export default function Plazo({ dias, estado }) {
    if (dias === null || dias === undefined) {
        return null;
    }

    // Una pausada no corre: decir «quedan 12 días» confundiría.
    if (estado === 101) {
        return <span className="apoyo text-fog">en pausa</span>;
    }

    if (dias < 0) {
        const hace = Math.abs(dias);

        return <span className="apoyo font-medium text-danger">venció hace {hace} {hace === 1 ? 'día' : 'días'}</span>;
    }

    if (dias === 0) {
        return <span className="apoyo font-medium text-danger">vence hoy</span>;
    }

    if (dias <= 7) {
        return <span className="apoyo font-medium text-warn">vence en {dias} {dias === 1 ? 'día' : 'días'}</span>;
    }

    return <span className="apoyo text-fog">quedan {dias} días</span>;
}
