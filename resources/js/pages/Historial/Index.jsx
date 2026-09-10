import { Head, Link } from '@inertiajs/react';
import { ArrowRightIcon, PencilIcon, RepeatIcon } from 'lucide-react';

/**
 * Un nombre que lleva a su ficha, si se sabe de quien es.
 *
 * Leer que hubo un traspaso es la mitad: lo que se viene a hacer despues es
 * mirar como quedaron los dos socios, y eso esta en sus fichas.
 */
function Socio({ nombre, uuid }) {
    if (!nombre) {
        return <span className="text-fog">—</span>;
    }

    if (!uuid) {
        return <>{nombre}</>;
    }

    return (
        <Link href={`/panel/clientes/${uuid}`} className="hover:underline">
            {nombre}
        </Link>
    );
}

const CLASES = {
    cambio: { Icono: PencilIcon, color: 'text-info' },
    traspaso: { Icono: RepeatIcon, color: 'text-volt' },
};

export default function Index({ movimientos }) {
    return (
        <>
            <Head title="Historial" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Historial</h1>
                <p className="apoyo text-fog">Los últimos 100 movimientos del sistema</p>
            </header>

            {movimientos.length === 0 ? (
                <div className="rounded-panel border border-line bg-surface px-3 py-8 text-center text-fog">
                    Todavía no hay movimientos registrados.
                </div>
            ) : (
                <ol className="overflow-hidden rounded-panel border border-line bg-surface">
                    {movimientos.map((m) => {
                        const { Icono, color } = CLASES[m.clase] ?? CLASES.cambio;

                        return (
                            <li
                                key={m.id}
                                className="flex gap-3 border-b border-line px-3 py-2.5 last:border-0 hover:bg-surface-2"
                            >
                                <Icono
                                    className={`mt-0.5 size-4 shrink-0 ${color}`}
                                    aria-hidden="true"
                                />

                                <div className="min-w-0 flex-1">
                                    <p className="text-sm text-chalk">
                                        <span className="font-medium">{m.titulo}</span>
                                        {m.socio ? (
                                            <span className="text-fog">
                                                {' · '}
                                                <Socio nombre={m.socio} uuid={m.socio_uuid} />
                                            </span>
                                        ) : null}
                                    </p>

                                    {/* El «de → a» es lo que de verdad se viene a
                                        leer: que cambio, no solo que hubo un cambio.

                                        Si los dos lados dicen lo mismo —una
                                        renovacion va de Activa a Activa— no hay
                                        cambio que enseñar y la flecha sobra. */}
                                    {(m.de || m.a) && m.de !== m.a ? (
                                        <p className="apoyo mt-0.5 flex flex-wrap items-center gap-1 text-fog">
                                            {/* En un traspaso los dos extremos son
                                                personas y llevan a su ficha; en un
                                                cambio de estado son solo nombres de
                                                estado y no llevan a ninguna parte. */}
                                            <span>
                                                <Socio nombre={m.de} uuid={m.de_uuid} />
                                            </span>
                                            <ArrowRightIcon className="size-3" aria-hidden="true" />
                                            <span className="text-chalk">
                                                <Socio nombre={m.a} uuid={m.a_uuid} />
                                            </span>
                                        </p>
                                    ) : null}

                                    {m.detalle ? (
                                        <p className="apoyo mt-0.5 text-fog">{m.detalle}</p>
                                    ) : null}
                                </div>

                                <div className="shrink-0 text-right">
                                    <p className="apoyo tabular-nums text-fog">{m.cuando ?? '—'}</p>
                                    {m.usuario ? (
                                        <p className="apoyo text-fog">{m.usuario}</p>
                                    ) : null}
                                </div>
                            </li>
                        );
                    })}
                </ol>
            )}
        </>
    );
}
