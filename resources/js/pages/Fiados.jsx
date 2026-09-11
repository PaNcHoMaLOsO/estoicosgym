import { Head, Link, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { AlertTriangleIcon, PlusIcon, SearchIcon, TrashIcon, UndoIcon } from 'lucide-react';

import ConfirmarDinero from '@/components/ConfirmarDinero';
import { ApuntarFiado } from '@/components/Libreta';
import { Reservado } from '@/Privado';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Cifra({ etiqueta, valor, pie, alerta = false }) {
    return (
        <div
            className={`rounded-panel border p-4 ${
                alerta ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface'
            }`}
        >
            <p className="rotulo">{etiqueta}</p>
            <p
                className={`mt-1 text-2xl font-semibold tabular-nums ${
                    alerta ? 'text-warn' : 'text-chalk'
                }`}
            >
                {valor}
            </p>
            {pie ? <p className="apoyo mt-0.5 text-fog">{pie}</p> : null}
        </div>
    );
}

/**
 * La libreta de lo fiado, entera.
 *
 * En el resumen esta el vistazo —quien debe y cuanto—. Aqui esta lo demas: lo
 * ya cobrado con fecha y con quien lo apunto, que no se mira todos los dias
 * pero hace falta cuando alguien discute una cifra o hay que cuadrar el mes.
 *
 * ESTO NO ES LA CAJA DEL GIMNASIO. Nada de lo que pasa en esta pantalla toca
 * los ingresos, el saldo de un socio ni ningun informe de membresias: es una
 * libreta aparte y se lleva aparte a proposito.
 */
// `diasParaInsistir`: a partir de cuántos días una cuenta se marca. Sale de
// Configuración → Mesón.
export default function Fiados({ cuentas, cobrado, cifras, diasParaInsistir = 14 }) {
    const [pestana, setPestana] = useState('deben');
    const [abierta, setAbierta] = useState(null);
    const [anotando, setAnotando] = useState(false);
    const [busqueda, setBusqueda] = useState('');

    const filtro = busqueda.trim().toLowerCase();

    const cuentasVisibles = useMemo(
        () => (filtro === '' ? cuentas : cuentas.filter((c) => c.quien.toLowerCase().includes(filtro))),
        [cuentas, filtro],
    );

    const cobradoVisible = useMemo(
        () =>
            filtro === ''
                ? cobrado
                : cobrado.filter(
                      (c) =>
                          c.quien.toLowerCase().includes(filtro)
                          || c.concepto.toLowerCase().includes(filtro),
                  ),
        [cobrado, filtro],
    );

    /*
     * Nada que mueva dinero se dispara de un clic.
     *
     * El error de estos botones es siempre el mismo —pulsar en la fila de al
     * lado— y para eso no vale un «¿seguro?»: quien se equivoco de fila tambien
     * dice que si. Lo que lo evita es que el aviso diga el nombre y la
     * cantidad, que es lo que hace <ConfirmarDinero>.
     */
    const [cobrando, setCobrando] = useState(null);
    const [quitando, setQuitando] = useState(null);
    const [reabriendo, setReabriendo] = useState(null);

    return (
        <>
            <Head title="Fiado en el mesón" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Fiado en el mesón</h1>
                    <p className="apoyo text-fog">
                        Lo que la gente se lleva y paga después. No entra en la caja del gimnasio.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => setAnotando((a) => !a)}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Anotar
                </button>
            </header>

            {anotando ? (
                <div className="mb-4 max-w-xl">
                    <ApuntarFiado alTerminar={() => setAnotando(false)} />
                </div>
            ) : null}

            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <Cifra
                    etiqueta="Se debe ahora"
                    valor={<Reservado ancho="w-24">{pesos.format(cifras.se_debe)}</Reservado>}
                    pie={
                        cifras.personas === 1
                            ? '1 persona'
                            : `${cifras.personas} personas`
                    }
                    alerta={cifras.se_debe > 0}
                />
                <Cifra
                    etiqueta="Cobrado este mes"
                    valor={<Reservado ancho="w-24">{pesos.format(cifras.cobrado_mes)}</Reservado>}
                    pie="de lo fiado, no de membresías"
                />
                <Cifra
                    etiqueta="Cuentas abiertas"
                    valor={cifras.personas}
                    pie={cifras.personas > 0 ? 'gente a la que cobrarle' : 'nadie debe nada'}
                />
            </div>

            <div className="mb-3 flex flex-wrap items-center gap-2">
                <div className="flex gap-1">
                    {[
                        ['deben', `Quién debe (${cuentas.length})`],
                        ['cobrado', 'Ya pagado'],
                    ].map(([valor, etiqueta]) => (
                        <button
                            key={valor}
                            type="button"
                            onClick={() => setPestana(valor)}
                            aria-pressed={pestana === valor}
                            className={`rounded-control border px-3 py-1.5 text-sm transition-colors ${
                                pestana === valor
                                    ? 'border-volt bg-volt text-on-volt'
                                    : 'border-line text-fog hover:text-chalk'
                            }`}
                        >
                            {etiqueta}
                        </button>
                    ))}
                </div>

                <div className="relative max-w-xs flex-1">
                    <SearchIcon
                        className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-fog"
                        aria-hidden="true"
                    />
                    {/* Se filtra en el navegador y no en la base: son las
                        cuentas abiertas y las cien ultimas cobradas, no una
                        tabla que crezca sin fin. */}
                    <input
                        type="search"
                        value={busqueda}
                        onChange={(e) => setBusqueda(e.target.value)}
                        placeholder="Buscar por nombre"
                        aria-label="Buscar por nombre"
                        className="w-full rounded-control border border-line bg-surface py-1.5 pr-2.5 pl-8 text-sm text-chalk focus:border-line-strong focus:outline-none"
                    />
                </div>
            </div>

            {pestana === 'deben' ? (
                cuentasVisibles.length === 0 ? (
                    <div className="rounded-panel border border-dashed border-line px-4 py-12 text-center">
                        <p className="text-sm text-fog">
                            {filtro !== ''
                                ? `Nadie que se llame «${busqueda}» debe nada.`
                                : 'Nadie debe nada.'}
                        </p>
                    </div>
                ) : (
                    <ul className="divide-y divide-line overflow-hidden rounded-panel border border-line bg-surface">
                        {cuentasVisibles.map((cuenta) => (
                            <li key={cuenta.clave} className="px-3 py-2.5">
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setAbierta(abierta === cuenta.clave ? null : cuenta.clave)
                                        }
                                        className="min-w-0 text-left"
                                    >
                                        <span className="block truncate text-sm font-medium text-chalk">
                                            {cuenta.quien}
                                        </span>
                                        <span className="apoyo block text-fog">
                                            {cuenta.lineas.length}{' '}
                                            {cuenta.lineas.length === 1 ? 'cosa' : 'cosas'} · desde{' '}
                                            {cuenta.desde}
                                            {/* Una cuenta de hace tres semanas no
                                                se cobra sola: conviene que se note. */}
                                            {cuenta.dias >= diasParaInsistir ? (
                                                <span className="ml-1 inline-flex items-center gap-1 text-warn">
                                                    <AlertTriangleIcon
                                                        className="size-3"
                                                        aria-hidden="true"
                                                    />
                                                    hace {cuenta.dias} días
                                                </span>
                                            ) : null}
                                        </span>
                                    </button>

                                    <div className="flex shrink-0 items-center gap-3">
                                        <span className="font-semibold tabular-nums text-warn">
                                            <Reservado ancho="w-16">
                                                {pesos.format(cuenta.total)}
                                            </Reservado>
                                        </span>

                                        {cuenta.socio_uuid ? (
                                            <Link
                                                href={`/panel/clientes/${cuenta.socio_uuid}`}
                                                className="apoyo text-fog transition-colors hover:text-chalk"
                                            >
                                                Ficha
                                            </Link>
                                        ) : null}

                                        {/* Se salda la cuenta ENTERA: quien paga
                                            en el meson paga lo que debe, no la
                                            bebida del martes. */}
                                        <button
                                            type="button"
                                            onClick={() => setCobrando(cuenta)}
                                            className="rounded-control border border-line px-2.5 py-1 text-sm text-chalk transition-colors hover:bg-surface-2"
                                        >
                                            Pagó
                                        </button>
                                    </div>
                                </div>

                                {abierta === cuenta.clave ? (
                                    <ul className="mt-2 space-y-1 border-l border-line pl-3">
                                        {cuenta.lineas.map((l) => (
                                            <li
                                                key={l.uuid}
                                                className="group flex items-center justify-between gap-2"
                                            >
                                                <span className="apoyo min-w-0 text-fog">
                                                    <span className="text-chalk">{l.concepto}</span>
                                                    {' · '}
                                                    {l.cuando}
                                                    {l.apunto ? ` · lo apuntó ${l.apunto}` : ''}
                                                </span>

                                                <span className="flex shrink-0 items-center gap-2">
                                                    <span className="apoyo tabular-nums text-fog">
                                                        <Reservado ancho="w-12">
                                                            {pesos.format(l.monto)}
                                                        </Reservado>
                                                    </span>

                                                    {/* Quitar una linea suelta es
                                                        para el error de tecleo, no
                                                        para cobrar a medias. */}
                                                    <button
                                                        type="button"
                                                        onClick={() => setQuitando({ ...l, quien: cuenta.quien })}
                                                        aria-label={`Quitar ${l.concepto}`}
                                                        className="rounded-control p-0.5 text-fog opacity-0 transition-opacity hover:text-danger focus:opacity-100 group-hover:opacity-100"
                                                    >
                                                        <TrashIcon
                                                            className="size-3.5"
                                                            aria-hidden="true"
                                                        />
                                                    </button>
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                ) : null}
                            </li>
                        ))}
                    </ul>
                )
            ) : cobradoVisible.length === 0 ? (
                <div className="rounded-panel border border-dashed border-line px-4 py-12 text-center">
                    <p className="text-sm text-fog">
                        {filtro !== '' ? 'Nada cobrado que coincida.' : 'Todavía no se ha cobrado nada.'}
                    </p>
                </div>
            ) : (
                <div className="overflow-x-auto rounded-panel border border-line">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-line bg-surface-2">
                                <th scope="col" className="px-3 py-2 text-left font-medium text-fog">
                                    Quién
                                </th>
                                <th scope="col" className="px-3 py-2 text-left font-medium text-fog">
                                    Qué
                                </th>
                                <th scope="col" className="px-3 py-2 text-left font-medium text-fog">
                                    Pagó
                                </th>
                                <th scope="col" className="px-3 py-2 text-right font-medium text-fog">
                                    Monto
                                </th>
                                <th scope="col" className="px-3 py-2 text-right font-medium text-fog">
                                    <span className="sr-only">Deshacer</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-line bg-surface">
                            {cobradoVisible.map((c) => (
                                <tr key={c.uuid} className="transition-colors hover:bg-surface-2">
                                    <td className="px-3 py-2 text-chalk">
                                        {c.socio_uuid ? (
                                            <Link
                                                href={`/panel/clientes/${c.socio_uuid}`}
                                                className="hover:underline"
                                            >
                                                {c.quien}
                                            </Link>
                                        ) : (
                                            c.quien
                                        )}
                                    </td>
                                    <td className="px-3 py-2 text-fog">{c.concepto}</td>
                                    <td className="px-3 py-2 tabular-nums text-fog">{c.cuando ?? '—'}</td>
                                    <td className="px-3 py-2 text-right tabular-nums text-chalk">
                                        <Reservado ancho="w-14">{pesos.format(c.monto)}</Reservado>
                                    </td>
                                    <td className="px-3 py-2 text-right">
                                        {/* Deshacer un «Pago» mal dado. Sin esto la
                                            deuda desaparece y hay que volver a
                                            apuntarla a mano, inventando conceptos y
                                            montos que ya nadie recuerda. */}
                                        <button
                                            type="button"
                                            onClick={() => setReabriendo(c)}
                                            aria-label={`Deshacer el cobro a ${c.quien}`}
                                            className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                                        >
                                            <UndoIcon className="size-3.5" aria-hidden="true" />
                                            Deshacer
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {cobrado.length >= 100 ? (
                        <p className="apoyo border-t border-line bg-surface px-3 py-2 text-fog">
                            Se muestran los 100 cobros más recientes.
                        </p>
                    ) : null}
                </div>
            )}

            <ConfirmarDinero
                abierto={cobrando !== null}
                alCerrar={() => setCobrando(null)}
                titulo="Cobrar lo fiado"
                quien={cobrando?.quien ?? ''}
                monto={cobrando?.total ?? 0}
                detalle={cobrando?.lineas}
                consecuencia="Su cuenta queda saldada. Esto no entra en la caja del gimnasio."
                etiquetaConfirmar="Pagó"
                accion="/panel/fiados/saldar"
                metodo="post"
                datos={{
                    id_cliente: cobrando?.id_cliente ?? null,
                    nombre: cobrando?.nombre ?? null,
                }}
            />

            <ConfirmarDinero
                abierto={quitando !== null}
                alCerrar={() => setQuitando(null)}
                titulo="Quitar de la cuenta"
                quien={quitando?.quien ?? ''}
                monto={quitando?.monto ?? 0}
                detalle={quitando ? [{ concepto: quitando.concepto, monto: quitando.monto }] : []}
                consecuencia="Esto es para lo que se apuntó por error: deja de deberlo y no queda rastro. Si lo pagó, usa «Pagó»."
                etiquetaConfirmar="Quitar"
                peligrosa
                accion={quitando ? `/panel/fiados/${quitando.uuid}` : ''}
                metodo="delete"
            />

            <ConfirmarDinero
                abierto={reabriendo !== null}
                alCerrar={() => setReabriendo(null)}
                titulo="Deshacer el cobro"
                quien={reabriendo?.quien ?? ''}
                monto={reabriendo?.monto ?? 0}
                consecuencia="Vuelve a deberlo. Se reabre todo lo que se cobró en ese mismo momento, no solo esta línea."
                etiquetaConfirmar="Vuelve a deber"
                accion={reabriendo ? `/panel/fiados/${reabriendo.uuid}/reabrir` : ''}
                metodo="patch"
            />
        </>
    );
}
