/**
 * Etiqueta de estado.
 *
 * Los codigos son los de la tabla `estados`: las columnas id_estado guardan el
 * CODIGO (100-106 membresia, 200-205 pago), no el id de la fila. Estan aqui en
 * un solo sitio porque los pintan cuatro pantallas y antes cada vista Blade
 * repetia su propio switch con colores que ya no coincidian entre si.
 */
const ESTADOS = {
    // --- membresia ---
    100: { texto: 'Al día', tono: 'ok' },
    101: { texto: 'Pausada', tono: 'warn' },
    102: { texto: 'Vencida', tono: 'danger' },
    103: { texto: 'Cancelada', tono: 'neutro' },
    104: { texto: 'Suspendida', tono: 'danger' },
    105: { texto: 'Cambiada', tono: 'info' },
    106: { texto: 'Traspasada', tono: 'info' },
    // --- pago ---
    200: { texto: 'Pendiente', tono: 'warn' },
    201: { texto: 'Pagado', tono: 'ok' },
    202: { texto: 'Abono', tono: 'info' },
    203: { texto: 'Vencido', tono: 'danger' },
    204: { texto: 'Cancelado', tono: 'neutro' },
    205: { texto: 'Traspasado', tono: 'info' },
    // --- notificacion ---
    600: { texto: 'Pendiente', tono: 'warn' },
    601: { texto: 'Enviada', tono: 'ok' },
    602: { texto: 'Fallida', tono: 'danger' },
    603: { texto: 'Cancelada', tono: 'neutro' },
};

const TONOS = {
    ok: 'border-ok/40 bg-ok/5 text-ok',
    warn: 'border-warn/40 bg-warn/5 text-warn',
    danger: 'border-danger/40 bg-danger/5 text-danger',
    info: 'border-info/40 bg-info/5 text-info',
    neutro: 'border-line bg-surface-2 text-fog',
};

export default function Estado({ codigo, vacio = '—' }) {
    if (!codigo) {
        return <span className="apoyo text-fog">{vacio}</span>;
    }

    const estado = ESTADOS[codigo] ?? { texto: `Estado ${codigo}`, tono: 'neutro' };

    return (
        <span className={`inline-flex rounded-pill border px-2 py-0.5 text-xs ${TONOS[estado.tono]}`}>
            {estado.texto}
        </span>
    );
}
