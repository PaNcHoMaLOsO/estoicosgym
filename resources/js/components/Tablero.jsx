/**
 * Piezas de los tableros del panel: el resumen y la caja.
 *
 * Una sola copia para las dos pantallas: si cada una tuviera la suya, la
 * primera corrección que se hiciera solo en una las dejaría distintas.
 */

export function Cifra({ etiqueta, valor, pie, tono = 'normal', siempreTono = false }) {
    /*
     * El color solo aparece cuando el número pide actuar. Si todo se pintara,
     * no destacaría nada.
     *
     * `valor` puede venir envuelto en <Reservado> para poder taparlo, y entonces
     * no se puede comparar con cero: por eso `siempreTono`, que usa quien ya sabe
     * desde fuera si hay algo que atender. El color se queda aunque la cifra esté
     * tapada, a propósito: «hay algo por cobrar» no es ningún secreto, la
     * cantidad sí.
     */
    const activo = siempreTono || valor > 0;

    const estilos = {
        normal: 'border-line bg-surface',
        aviso: activo ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface',
        alerta: activo ? 'border-danger/40 bg-danger/5' : 'border-line bg-surface',
    };

    const texto = {
        normal: 'text-chalk',
        aviso: activo ? 'text-warn' : 'text-chalk',
        alerta: activo ? 'text-danger' : 'text-chalk',
    };

    return (
        <div className={`rounded-panel border p-4 ${estilos[tono]}`}>
            <p className="rotulo">{etiqueta}</p>
            <p className={`mt-1 text-2xl font-semibold tabular-nums ${texto[tono]}`}>{valor}</p>
            {pie ? <p className="apoyo mt-0.5 text-fog">{pie}</p> : null}
        </div>
    );
}

export function Panel({ titulo, descripcion, children, enlace }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <div className="mb-3 flex items-start justify-between gap-3">
                <div>
                    <h2 className="rotulo">{titulo}</h2>
                    {descripcion ? <p className="apoyo mt-0.5 text-fog">{descripcion}</p> : null}
                </div>
                {enlace}
            </div>
            {children}
        </section>
    );
}

export const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});
