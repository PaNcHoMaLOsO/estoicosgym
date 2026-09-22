import { router } from '@inertiajs/react';

/**
 * Un desplegable que filtra u ordena la lista, al lado de los botones.
 *
 * LOS BOTONES NO SIRVEN PARA TODO. Los grupos —vigentes, vencidas, con deuda—
 * son cinco y se miran de un vistazo; los planes son ocho y crecerán, y las
 * formas de ordenar no son un estado de la membresía sino otra pregunta. En
 * botones, esa fila se volvía una pared de veinte pastillas.
 *
 * Cambia la dirección igual que los filtros, así que la lista ordenada se
 * puede compartir, guardar y volver con «atrás».
 */
export default function Selector({ etiqueta, nombre, valor = '', opciones, ruta, extra = {} }) {
    function elegir(nuevo) {
        router.get(
            ruta,
            { ...extra, ...(nuevo ? { [nombre]: nuevo } : {}) },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    }

    return (
        <label className="inline-flex items-center gap-1.5">
            <span className="apoyo text-fog">{etiqueta}</span>
            <select
                value={valor ?? ''}
                onChange={(e) => elegir(e.target.value)}
                className="rounded-control border border-line bg-surface py-1 pr-7 pl-2.5 text-sm text-chalk transition-colors hover:border-line-strong focus:border-line-strong focus:outline-none"
            >
                {opciones.map((o) => (
                    <option key={o.valor || 'todos'} value={o.valor}>
                        {o.etiqueta}
                    </option>
                ))}
            </select>
        </label>
    );
}
