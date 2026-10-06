/**
 * El texto de un botón que cambia mientras trabaja —«Guardar» / «Guardando…»—
 * SIN CAMBIAR DE ANCHO.
 *
 * Los dos textos ocupan la misma celda y uno queda invisible: el botón mide
 * siempre lo que el más largo. Antes, al pulsar, el botón se ensanchaba y
 * empujaba al de al lado justo cuando el dedo o el mouse todavía estaban ahí.
 */
export default function TextoQueCambia({ ocupado, mientras, children }) {
    return (
        <span className="inline-grid justify-items-center">
            <span className={`col-start-1 row-start-1 ${ocupado ? 'invisible' : ''}`}>{children}</span>
            <span className={`col-start-1 row-start-1 ${ocupado ? '' : 'invisible'}`} aria-hidden={! ocupado}>
                {mientras}
            </span>
        </span>
    );
}
