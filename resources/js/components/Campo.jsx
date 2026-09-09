/**
 * Un campo de formulario: etiqueta, control y su error.
 *
 * El error va PEGADO al campo y no en un aviso arriba del formulario: con
 * quince campos, un banner que dice «hay errores» obliga a buscar cual. Ademas
 * el control queda marcado con aria-invalid para quien navega con lector.
 */
export function Campo({ etiqueta, nombre, error, requerido = false, ayuda, children }) {
    return (
        <div className="flex flex-col gap-1">
            <label htmlFor={nombre} className="text-sm font-medium text-chalk">
                {etiqueta}
                {requerido ? (
                    <span className="text-danger" aria-hidden="true">
                        {' '}
                        *
                    </span>
                ) : null}
            </label>

            {children}

            {ayuda && !error ? <p className="apoyo text-fog">{ayuda}</p> : null}
            {error ? (
                <p className="apoyo text-danger" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}

const BASE =
    'w-full rounded-control border bg-surface px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:outline-none disabled:opacity-50';

const borde = (error) =>
    error ? 'border-danger focus:border-danger' : 'border-line focus:border-line-strong';

export function Texto({ nombre, error, valor, alCambiar, tipo = 'text', ...resto }) {
    return (
        <input
            id={nombre}
            name={nombre}
            type={tipo}
            value={valor ?? ''}
            onChange={(e) => alCambiar(e.target.value)}
            aria-invalid={error ? 'true' : undefined}
            className={`${BASE} ${borde(error)}`}
            {...resto}
        />
    );
}

export function Area({ nombre, error, valor, alCambiar, filas = 3, ...resto }) {
    return (
        <textarea
            id={nombre}
            name={nombre}
            rows={filas}
            value={valor ?? ''}
            onChange={(e) => alCambiar(e.target.value)}
            aria-invalid={error ? 'true' : undefined}
            className={`${BASE} ${borde(error)}`}
            {...resto}
        />
    );
}

export function Seleccion({ nombre, error, valor, alCambiar, opciones, vacio = 'Seleccione…' }) {
    return (
        <select
            id={nombre}
            name={nombre}
            value={valor ?? ''}
            onChange={(e) => alCambiar(e.target.value)}
            aria-invalid={error ? 'true' : undefined}
            className={`${BASE} ${borde(error)}`}
        >
            <option value="">{vacio}</option>
            {opciones.map((o) => (
                <option key={o.valor} value={o.valor}>
                    {o.etiqueta}
                </option>
            ))}
        </select>
    );
}

/** Bloque con titulo para agrupar campos que van juntos. */
export function Grupo({ titulo, descripcion, children }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <h2 className="text-sm font-semibold text-chalk">{titulo}</h2>
            {descripcion ? <p className="apoyo mb-3 text-fog">{descripcion}</p> : <div className="mb-3" />}
            <div className="grid gap-3 sm:grid-cols-2">{children}</div>
        </section>
    );
}
