/**
 * Serie temporal: una columna por periodo, en orden.
 *
 * Va aparte de <Barras> porque responde otra pregunta. Las barras horizontales
 * comparan cosas SIN orden entre si —qué plan manda, qué método se usa más— y
 * ahí lo natural es ordenar de mayor a menor. Una serie de meses tiene un orden
 * que no se puede tocar, y lo que se busca es la FORMA: si sube, si baja, si el
 * último mes se cayó. Eso se ve en vertical y no en una lista ordenada.
 *
 * Sin librería: son seis columnas y un div con altura porcentual las dibuja.
 */
export default function Columnas({ datos, etiqueta = 'total' }) {
    if (! datos || datos.length === 0) {
        return <p className="apoyo py-6 text-center text-fog">Todavía no hay datos.</p>;
    }

    const mayor = Math.max(...datos.map((d) => d.total), 1);
    const total = datos.reduce((suma, d) => suma + d.total, 0);

    return (
        <div>
            <div className="flex h-32 items-end gap-2" role="img" aria-label={`${etiqueta} por mes`}>
                {datos.map((d) => (
                    <div key={d.mes} className="flex min-w-0 flex-1 flex-col items-center gap-1">
                        {/* El número va encima y no dentro: dentro se pierde en
                            las columnas cortas, que son justo las que interesan. */}
                        <span className="apoyo tabular-nums text-fog">{d.total}</span>
                        <div className="flex w-full flex-1 items-end">
                            <div
                                className={`w-full rounded-t-control ${
                                    d.total > 0 ? 'bg-volt' : 'bg-surface-2'
                                }`}
                                style={{
                                    // Un mínimo visible para que un mes en cero
                                    // se vea como una base y no como un hueco.
                                    height: d.total > 0 ? `${Math.max(6, (d.total / mayor) * 100)}%` : '2px',
                                }}
                            />
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-1 flex gap-2">
                {datos.map((d) => (
                    <span key={d.mes} className="apoyo min-w-0 flex-1 truncate text-center text-fog">
                        {d.mes}
                    </span>
                ))}
            </div>

            <p className="apoyo mt-2 text-fog">
                {total} en los últimos {datos.length} meses
            </p>
        </div>
    );
}
