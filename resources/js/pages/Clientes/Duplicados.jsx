import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeftIcon, CheckIcon } from 'lucide-react';

import Dialogo from '@/components/Dialogo';

/**
 * Posibles duplicados: fichas que pueden ser de la misma persona.
 *
 * Las planillas traían fichas sin RUT, y la misma persona quedó con una ficha
 * sin RUT y otra con él. Aquí se ven lado a lado —RUT, celular, membresías,
 * pagos— para decidir: «es la misma, juntar» o «son personas distintas».
 * Lo segundo se recuerda y ese grupo no vuelve a salir.
 */

/** La que conviene dejar: la que tiene RUT, y si no, la que tiene más historia. */
function laQueQueda(fichas) {
    return [...fichas].sort((a, b) => Number(Boolean(b.rut)) - Number(Boolean(a.rut)) || b.membresias - a.membresias)[0].uuid;
}

const FILAS = [
    ['RUT', (f) => f.rut ?? <span className="text-warn">Sin RUT</span>],
    ['Celular', (f) => f.celular || '-'],
    ['Correo', (f) => f.email || '-'],
    ['Estado', (f) => (f.activo ? 'Activo' : 'De baja')],
    ['Membresías', (f) => f.membresias],
    ['Última vence', (f) => f.ultima_vence ?? '-'],
    ['Pagos', (f) => f.pagos],
    ['Fiado', (f) => f.fiado],
    ['Registrada', (f) => f.registrada ?? '-'],
];

function Grupo({ grupo, destacado, puedeJuntar }) {
    const [queda, setQueda] = useState(() => laQueQueda(grupo.fichas));
    // Con tres o más, se elige cuáles son la misma persona; con dos, es la otra.
    const [iguales, setIguales] = useState(() => grupo.fichas.map((f) => f.uuid));
    const [confirmando, setConfirmando] = useState(null);

    const salen = grupo.fichas.filter((f) => f.uuid !== queda && iguales.includes(f.uuid));
    const fichaQueQueda = grupo.fichas.find((f) => f.uuid === queda);
    const variasOpciones = grupo.fichas.length > 2;

    return (
        <section
            id={`grupo-${grupo.clave}`}
            className={`overflow-hidden rounded-panel border bg-surface ${destacado ? 'border-volt' : 'border-line'}`}
        >
            <header className="flex flex-wrap items-center justify-between gap-2 border-b border-line px-5 py-3">
                <h2 className="text-base font-semibold text-chalk">{grupo.fichas[0].nombre}</h2>
                <span className="apoyo rounded-pill border border-line px-2 py-0.5 text-fog">{grupo.porque}</span>
            </header>

            <div className="overflow-x-auto">
                <table className="w-full min-w-[32rem] text-sm">
                    <thead>
                        <tr className="border-b border-line">
                            <th scope="col" className="w-32 px-5 py-3 text-left font-normal text-fog">
                                <span className="sr-only">Dato</span>
                            </th>
                            {grupo.fichas.map((f) => (
                                <th key={f.uuid} scope="col" className="px-4 py-3 text-left font-normal">
                                    <Link href={`/panel/clientes/${f.uuid}`} className="font-medium text-chalk underline-offset-2 hover:underline">
                                        Ficha #{f.id}
                                    </Link>

                                    {puedeJuntar ? (
                                        <label className="mt-1.5 flex items-center gap-1.5 text-xs text-fog">
                                            <input
                                                type="radio"
                                                name={`queda-${grupo.clave}`}
                                                checked={queda === f.uuid}
                                                onChange={() => setQueda(f.uuid)}
                                                className="accent-[var(--color-volt)]"
                                            />
                                            Se queda esta
                                        </label>
                                    ) : null}

                                    {puedeJuntar && variasOpciones && queda !== f.uuid ? (
                                        <label className="mt-1 flex items-center gap-1.5 text-xs text-fog">
                                            <input
                                                type="checkbox"
                                                checked={iguales.includes(f.uuid)}
                                                onChange={(e) =>
                                                    setIguales((v) => (e.target.checked ? [...v, f.uuid] : v.filter((u) => u !== f.uuid)))
                                                }
                                                className="accent-[var(--color-volt)]"
                                            />
                                            Es la misma persona
                                        </label>
                                    ) : null}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-line">
                        {FILAS.map(([etiqueta, valor]) => (
                            <tr key={etiqueta}>
                                <th scope="row" className="px-5 py-2 text-left font-normal text-fog">
                                    {etiqueta}
                                </th>
                                {grupo.fichas.map((f) => (
                                    <td key={f.uuid} className={`px-4 py-2 tabular-nums ${f.uuid === queda && puedeJuntar ? 'text-chalk' : 'text-fog'}`}>
                                        {valor(f)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <footer className="flex flex-wrap items-center justify-end gap-3 border-t border-line px-5 py-3">
                {puedeJuntar ? null : <span className="apoyo mr-auto text-fog">Juntar las fichas lo hace el administrador.</span>}

                <button
                    type="button"
                    onClick={() => setConfirmando('distintos')}
                    className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                >
                    Son personas distintas
                </button>

                {puedeJuntar ? (
                    <button
                        type="button"
                        onClick={() => setConfirmando('juntar')}
                        disabled={salen.length === 0}
                        className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                    >
                        Es la misma: juntar
                    </button>
                ) : null}
            </footer>

            <Dialogo
                abierto={confirmando === 'juntar'}
                alCerrar={() => setConfirmando(null)}
                titulo="Juntar en una sola ficha"
                via="inertia"
                metodo="post"
                accion="/panel/clientes/duplicados/juntar"
                datos={{ queda, salen: salen.map((f) => f.uuid) }}
                etiquetaConfirmar="Juntar"
            >
                <p className="text-sm text-chalk">
                    Queda la ficha #{fichaQueQueda?.id}
                    {fichaQueQueda?.rut ? ` (${fichaQueQueda.rut})` : ''}.
                </p>
                <p className="apoyo mt-2 text-fog">
                    Las membresías, pagos, fiado y contratos de {salen.map((f) => `la #${f.id}`).join(' y ')} pasan a ella, y lo que le falte
                    —RUT, celular, correo— se completa con esos datos. {salen.length === 1 ? 'La otra ficha' : 'Las otras fichas'} queda en la papelera.
                </p>
            </Dialogo>

            <Dialogo
                abierto={confirmando === 'distintos'}
                alCerrar={() => setConfirmando(null)}
                titulo="Son personas distintas"
                via="inertia"
                metodo="post"
                accion="/panel/clientes/duplicados/distintos"
                datos={{ socios: grupo.fichas.map((f) => f.uuid) }}
                etiquetaConfirmar="Sí, son distintas"
            >
                <p className="apoyo text-fog">No se cambia nada en las fichas. Este grupo deja de salir en la lista.</p>
            </Dialogo>
        </section>
    );
}

export default function Duplicados({ grupos, enfocar, puedeJuntar }) {
    // Desde la ficha de un socio se llega con su grupo primero.
    const ordenados = enfocar
        ? [...grupos].sort((a, b) => Number(b.fichas.some((f) => f.uuid === enfocar)) - Number(a.fichas.some((f) => f.uuid === enfocar)))
        : grupos;

    return (
        <>
            <Head title="Posibles duplicados" />

            <header className="mb-6">
                <Link href="/panel/clientes" className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk">
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Clientes
                </Link>
                <h1 className="mt-1 text-xl font-semibold text-chalk">Posibles duplicados</h1>
                <p className="mt-1 text-sm text-fog">
                    Fichas con el mismo nombre, RUT o celular. Pueden ser la misma persona o dos que se llaman igual: compáralas y decide.
                </p>
            </header>

            {ordenados.length === 0 ? (
                <p className="flex items-center gap-2 rounded-panel border border-line bg-surface px-5 py-4 text-sm text-chalk">
                    <CheckIcon className="size-4 text-ok" aria-hidden="true" />
                    No hay fichas repetidas.
                </p>
            ) : (
                <div className="space-y-10">
                    {[
                        ['Probablemente la misma persona', 'Una ficha sin RUT y otra con RUT, con el mismo nombre. Casi siempre vienen de las planillas.', true],
                        ['Mismo nombre, RUT distinto', 'Lo más probable es que sean personas distintas que se llaman igual. Revísalas si sospechas de un RUT mal escrito.', false],
                    ].map(([titulo, bajada, probable]) => {
                        const deAqui = ordenados.filter((g) => g.probable === probable);

                        return deAqui.length === 0 ? null : (
                            <div key={titulo}>
                                <h2 className="text-base font-semibold text-chalk">
                                    {titulo} <span className="font-normal text-fog">· {deAqui.length}</span>
                                </h2>
                                <p className="mt-0.5 mb-4 text-sm text-fog">{bajada}</p>
                                <div className="space-y-5">
                                    {deAqui.map((g) => (
                                        <Grupo
                                            key={g.clave}
                                            grupo={g}
                                            puedeJuntar={puedeJuntar}
                                            destacado={Boolean(enfocar) && g.fichas.some((f) => f.uuid === enfocar)}
                                        />
                                    ))}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </>
    );
}
