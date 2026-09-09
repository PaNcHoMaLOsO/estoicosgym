/**
 * Envoltura de tabla: el marco, la cabecera y el estado vacio.
 *
 * `columnas` es una lista de textos; las filas las pinta quien la usa, que es
 * lo unico que cambia de una pantalla a otra.
 */
export function Tabla({ columnas, children, vacia = false, mensajeVacio = 'No hay nada que mostrar.' }) {
    return (
        <div className="overflow-x-auto rounded-panel border border-line bg-surface">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b border-line text-left">
                        {columnas.map((columna) => (
                            <th key={columna} className="rotulo px-3 py-2 font-medium whitespace-nowrap">
                                {columna}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {vacia ? (
                        <tr>
                            <td colSpan={columnas.length} className="px-3 py-8 text-center text-fog">
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

export function Fila({ children }) {
    return <tr className="border-b border-line last:border-0 hover:bg-surface-2">{children}</tr>;
}

export function Celda({ children, className = '' }) {
    return <td className={`px-3 py-2 text-fog ${className}`}>{children}</td>;
}

/** Cifra alineada por el punto decimal: sin esto las columnas de dinero bailan. */
export function Cifra({ children, className = '' }) {
    return <td className={`px-3 py-2 text-right tabular-nums text-fog ${className}`}>{children}</td>;
}
