/**
 * Comparativa simple: una fila por concepto, con la barra proporcional al
 * mayor de todos.
 *
 * Se dibuja con un div de ancho porcentual y no con una librería de gráficos:
 * son cinco o seis filas y lo que se responde de un vistazo es «cuál manda»,
 * no un valor exacto que ya está escrito al lado. Una librería de 300 KB para
 * esto sería peor.
 */
export default function Barras({ filas, formato = (v) => v, vacio = 'Sin movimiento en este periodo.' }) {
    if (! filas || filas.length === 0) {
        return <p className="apoyo px-1 py-6 text-center text-fog">{vacio}</p>;
    }

    const mayor = Math.max(...filas.map((f) => f.total), 1);

    return (
        <ul className="space-y-2">
            {filas.map((fila) => (
                <li key={fila.nombre ?? fila.mes}>
                    <div className="flex items-baseline justify-between gap-3 text-sm">
                        <span className="truncate text-chalk">{fila.nombre ?? fila.mes}</span>
                        <span className="shrink-0 tabular-nums text-fog">
                            {formato(fila.total)}
                            {fila.cantidad !== undefined ? (
                                <span className="apoyo"> · {fila.cantidad}</span>
                            ) : null}
                        </span>
                    </div>
                    <div className="mt-1 h-1.5 overflow-hidden rounded-pill bg-surface-2">
                        <div
                            className="h-full rounded-pill bg-volt"
                            style={{ width: `${Math.round((fila.total / mayor) * 100)}%` }}
                        />
                    </div>
                </li>
            ))}
        </ul>
    );
}
