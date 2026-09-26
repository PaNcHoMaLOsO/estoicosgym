import { useRef } from 'react';
import { router } from '@inertiajs/react';

/**
 * Envoltura de tabla: el marco, la cabecera y el estado vacio.
 *
 * `columnas` es una lista de textos, o de { titulo, className } cuando una
 * columna tiene que esconderse en pantalla chica ('hidden md:table-cell') o
 * alinearse a la derecha. La celda de esa columna lleva la misma clase.
 */
export function Tabla({ columnas, children, vacia = false, mensajeVacio = 'No hay nada que mostrar.' }) {
    const cols = columnas.map((c) => (typeof c === 'string' ? { titulo: c, className: '' } : c));

    return (
        <div className="overflow-x-auto rounded-panel border border-line bg-surface">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b border-line bg-surface-2/40 text-left">
                        {cols.map((columna, i) => (
                            <th key={`${columna.titulo}-${i}`} className={`rotulo px-3 py-2 font-medium whitespace-nowrap ${columna.className ?? ''}`}>
                                {columna.titulo}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {vacia ? (
                        <tr>
                            <td colSpan={cols.length} className="px-3 py-10 text-center text-fog">
                                {mensajeVacio}
                            </td>
                        </tr>
                    ) : (
                        children
                    )}
                </tbody>
            </table>
        </div>
    );
}

/**
 * Una fila. Con `href`, TODA la fila abre la ficha: apuntarle al nombre con el
 * mouse era lo único que funcionaba, y en una fila ancha no se adivina.
 *
 * Los enlaces y botones de dentro (el WhatsApp, por ejemplo) siguen haciendo lo
 * suyo: el clic en ellos no abre la ficha. Para el teclado sigue estando el
 * enlace del nombre.
 */
export function Fila({ children, href }) {
    const espera = useRef(null);

    // Se pide la ficha si el mouse se queda un momento sobre la fila; pasar
    // de largo por la tabla no pide nada.
    function precargar() {
        clearTimeout(espera.current);
        espera.current = setTimeout(() => router.prefetch(href, { method: 'get' }, { cacheFor: '30s' }), 120);
    }

    function abrir(evento) {
        if (!href || evento.target.closest('a, button, input, label')) {
            return;
        }

        if (evento.metaKey || evento.ctrlKey) {
            window.open(href, '_blank');

            return;
        }

        router.visit(href);
    }

    return (
        <tr
            onClick={href ? abrir : undefined}
            onMouseEnter={href ? precargar : undefined}
            onMouseLeave={href ? () => clearTimeout(espera.current) : undefined}
            className={`border-b border-line last:border-0 hover:bg-surface-2 ${href ? 'cursor-pointer' : ''}`}
        >
            {children}
        </tr>
    );
}

export function Celda({ children, className = '' }) {
    return <td className={`px-3 py-2.5 align-middle text-fog ${className}`}>{children}</td>;
}

/** Cifra alineada por el punto decimal: sin esto las columnas de dinero bailan. */
export function Cifra({ children, className = '' }) {
    return <td className={`px-3 py-2.5 text-right tabular-nums text-fog ${className}`}>{children}</td>;
}

/** Dos líneas en una celda: lo principal arriba y el detalle en chico debajo. */
export function DosLineas({ arriba, abajo, className = '' }) {
    return (
        <div className={`flex min-w-0 flex-col ${className}`}>
            <span className="truncate">{arriba}</span>
            {abajo ? <span className="apoyo truncate text-fog">{abajo}</span> : null}
        </div>
    );
}
