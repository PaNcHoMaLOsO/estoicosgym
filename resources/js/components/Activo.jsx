/**
 * Marca de activo/inactivo para los catalogos de configuracion.
 *
 * No usa <Estado>: aquello pinta los codigos de la tabla `estados` (100, 201…)
 * y esto es un booleano de otra cosa. Mezclarlos haria creer que un metodo de
 * pago «inactivo» comparte significado con una membresia «cancelada».
 */
export default function Activo({ valor }) {
    return (
        <span
            className={`inline-flex rounded-pill border px-2 py-0.5 text-xs ${
                valor ? 'border-ok/40 bg-ok/5 text-ok' : 'border-line bg-surface-2 text-fog'
            }`}
        >
            {valor ? 'Activo' : 'Inactivo'}
        </span>
    );
}
