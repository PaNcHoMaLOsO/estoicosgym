import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Undo2Icon } from 'lucide-react';

/**
 * Lo que se borro y todavia se puede recuperar.
 *
 * UNA pantalla para los siete tipos, y no siete papeleras: quien la abre no
 * viene a mirar «la papelera de convenios», viene a buscar algo que borro hace
 * un rato y muchas veces ni se acuerda de que era exactamente.
 *
 * No hay «eliminar del todo». Un socio con inscripciones, un plan con
 * membresias vendidas o un pago cuadrado en una caja de hace tres años estan
 * referenciados por otras filas, y quitarlos de verdad deja huecos en sitios
 * que nadie mira hasta que cuadran mal las cuentas.
 */
export default function Papelera({ grupos }) {
    // Se guarda cual se esta restaurando para no dejar el boton pulsable dos
    // veces: la segunda vez la fila ya no esta y responde un 404.
    const [restaurando, setRestaurando] = useState(null);

    function restaurar(fila) {
        setRestaurando(`${fila.tipo}:${fila.id}`);

        router.patch(`/panel/papelera/${fila.tipo}/${fila.id}/restaurar`, {}, {
            preserveScroll: true,
            onFinish: () => setRestaurando(null),
        });
    }

    const total = grupos.reduce((t, g) => t + g.cuantos, 0);

    return (
        <>
            <Head title="Papelera" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Papelera</h1>
                <p className="apoyo text-fog">
                    {total === 0
                        ? 'No hay nada borrado.'
                        : `${total} ${total === 1 ? 'cosa borrada' : 'cosas borradas'} que se pueden recuperar.`}
                </p>
            </header>

            {grupos.length === 0 ? (
                <div className="rounded-panel border border-dashed border-line px-4 py-12 text-center">
                    <p className="text-sm text-fog">
                        Nada borrado. Lo que se elimine aparecerá aquí para poder devolverlo.
                    </p>
                </div>
            ) : (
                <div className="space-y-5">
                    {grupos.map((grupo) => (
                        <section key={grupo.clave}>
                            <h2 className="rotulo mb-2">
                                {grupo.titulo} ({grupo.cuantos})
                            </h2>
                            {/* Borrar de verdad no se hace aquí: sus pagos se
                                quedarían apuntando a nada. Se hace en la ficha. */}
                            {grupo.clave === 'clientes' ? (
                                <p className="apoyo mb-2 text-fog">
                                    ¿Pidió que se borren sus datos? Restáuralo y usa «Borrar sus datos
                                    personales» en su ficha: sus pagos se quedan en las cuentas, sin nombre.
                                </p>
                            ) : null}

                            <ul className="divide-y divide-line overflow-hidden rounded-panel border border-line">
                                {grupo.filas.map((fila) => (
                                    <li
                                        key={`${fila.tipo}:${fila.id}`}
                                        className="flex flex-wrap items-center justify-between gap-3 bg-surface px-3 py-2.5"
                                    >
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-medium text-chalk">
                                                {fila.que}
                                            </p>
                                            {fila.detalle ? (
                                                <p className="apoyo truncate text-fog">{fila.detalle}</p>
                                            ) : null}
                                        </div>

                                        <div className="flex shrink-0 items-center gap-3">
                                            {/* Cuando se borro importa mas que la
                                                fecha exacta: se busca «lo de esta
                                                mañana», no lo del 14 de marzo. */}
                                            <span
                                                className="apoyo text-fog"
                                                title={fila.borrado ?? undefined}
                                            >
                                                {fila.hace ?? '—'}
                                            </span>

                                            <button
                                                type="button"
                                                onClick={() => restaurar(fila)}
                                                disabled={restaurando === `${fila.tipo}:${fila.id}`}
                                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-2.5 py-1 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                                            >
                                                <Undo2Icon className="size-3.5" aria-hidden="true" />
                                                {restaurando === `${fila.tipo}:${fila.id}`
                                                    ? 'Restaurando…'
                                                    : 'Restaurar'}
                                            </button>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    ))}
                </div>
            )}
        </>
    );
}
