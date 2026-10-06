import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { AlertTriangleIcon, MessageCircleIcon, SearchIcon, TrashIcon, UndoIcon, UserCheckIcon } from 'lucide-react';

import ConfirmarDinero from '@/components/ConfirmarDinero';
import { ApuntarFiado } from '@/components/Libreta';
import Retrato from '@/components/Retrato';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { whatsapp } from '@/lib/contacto';
import { puede } from '@/lib/permisos';
import { Reservado } from '@/Privado';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Cifra({ etiqueta, valor, pie, alerta = false }) {
    return (
        <div
            className={`rounded-panel border px-3 py-2.5 ${
                alerta ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface'
            }`}
        >
            <p className="rotulo">{etiqueta}</p>
            <p className={`mt-0.5 text-xl font-semibold tabular-nums ${alerta ? 'text-warn' : 'text-chalk'}`}>
                {valor}
            </p>
            {pie ? <p className="apoyo text-fog">{pie}</p> : null}
        </div>
    );
}

/**
 * Recordarle la cuenta por WhatsApp.
 *
 * COBRAR LO FIADO ES LO INCOMODO DEL MESON: nadie quiere pedirle plata de
 * frente a alguien que viene a entrenar. Un mensaje con la cifra escrita lo
 * resuelve sin la escena, y por eso esta cuenta se cobra sola mas veces.
 *
 * El mensaje se deja ESCRITO, no enviado: se relee y lo manda la persona.
 */
function Recordar({ cuenta }) {
    if (! cuenta.celular) {
        return null;
    }

    const texto = `Hola ${cuenta.quien}, te cuento que tienes ${pesos.format(cuenta.total)} pendientes del mesón del gimnasio (${cuenta.lineas.map((l) => l.concepto).join(', ')}). ¡Gracias!`;

    return (
        <a
            href={whatsapp(cuenta.celular, texto)}
            target="progym-whatsapp"
            rel="noopener"
            title="Escribirle recordándole la cuenta"
            className="inline-flex items-center gap-1.5 rounded-control border border-[#25D366]/40 bg-[#25D366]/10 px-2 py-1 text-sm text-[#25D366] transition-colors hover:bg-[#25D366]/20"
        >
            <MessageCircleIcon className="size-3.5" aria-hidden="true" />
            Recordar
        </a>
    );
}

/**
 * Pasar la cuenta de un nombre suelto a un socio: «Pedro» se anotó de visita
 * y después se inscribió. Lo que debía (y lo que pagó) pasa a su ficha.
 */
function PasarASocio({ cuenta, alCerrar }) {
    const [texto, setTexto] = useState('');
    const [resultados, setResultados] = useState(null);
    const [enviando, setEnviando] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        setTexto('');
        setResultados(null);
        setError(null);
    }, [cuenta?.clave]);

    useEffect(() => {
        if (texto.trim().length < 2) {
            setResultados(null);

            return undefined;
        }

        const corte = new AbortController();
        const temporizador = setTimeout(() => {
            fetch(`/panel/fiados/buscar-socio?q=${encodeURIComponent(texto)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: corte.signal,
            })
                .then((r) => r.json())
                .then((j) => setResultados(j.clientes ?? []))
                .catch((e) => (e.name === 'AbortError' ? null : setResultados([])));
        }, 250);

        return () => {
            clearTimeout(temporizador);
            corte.abort();
        };
    }, [texto]);

    function elegir(socio) {
        setEnviando(true);
        router.post('/panel/fiados/asignar', { nombre: cuenta.nombre, id_cliente: socio.id }, {
            preserveScroll: true,
            onSuccess: () => alCerrar(),
            onError: (e) => setError(Object.values(e ?? {}).join(' ')),
            onFinish: () => setEnviando(false),
        });
    }

    return (
        <Dialog open={cuenta !== null} onOpenChange={(v) => (! v && ! enviando ? alCerrar() : null)}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Pasar a un socio</DialogTitle>
                    <DialogDescription>
                        Lo anotado a «{cuenta?.quien}» pasa a la ficha del socio que elijas, con lo que debe y lo que ya pagó.
                    </DialogDescription>
                </DialogHeader>
                <input
                    type="search"
                    value={texto}
                    onChange={(e) => setTexto(e.target.value)}
                    placeholder="Busca al socio por nombre o RUT"
                    aria-label="Buscar al socio"
                    autoFocus
                    className="w-full rounded-control border border-line bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                />
                {resultados && resultados.length === 0 ? <p className="apoyo text-fog">Nadie coincide.</p> : null}
                {resultados && resultados.length > 0 ? (
                    <ul className="max-h-56 divide-y divide-line overflow-y-auto rounded-control border border-line">
                        {resultados.map((c) => (
                            <li key={c.id}>
                                <button
                                    type="button"
                                    disabled={enviando}
                                    onClick={() => elegir(c)}
                                    className="block w-full px-2.5 py-2 text-left text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                                >
                                    {c.nombre}
                                    {! c.activo ? <span className="ml-1 text-warn">· de baja</span> : null}
                                    <span className="apoyo block text-fog">{c.rut ?? 'sin RUT'}</span>
                                </button>
                            </li>
                        ))}
                    </ul>
                ) : null}
                {error ? <p className="apoyo text-danger" role="alert">{error}</p> : null}
            </DialogContent>
        </Dialog>
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
export default function Fiados({ cuentas, cobrado, cifras, mes, meses = [], registro = null, diasParaInsistir = 14 }) {
    const [pestana, setPestana] = useState('deben');
    const [abierta, setAbierta] = useState(null);
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
                          || c.lineas.some((l) => l.concepto.toLowerCase().includes(filtro)),
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
    const [pasando, setPasando] = useState(null);
    // Quitar una línea pide `clientes.eliminar` en el servidor: a recepción el
    // botón le daba un 403. Se esconde a quien no lo tiene.
    const { auth } = usePage().props;
    const puedeQuitar = puede(auth, 'clientes.eliminar');

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

            </header>

            {/*
             * DOS COLUMNAS: a la izquierda a quien cobrarle, a la derecha el
             * formulario de apuntar, SIEMPRE PUESTO.
             *
             * Estaba detras de un boton, y esta es la pantalla de lo fiado: lo
             * que se viene a hacer aqui es apuntar algo o cobrarlo. Media
             * pantalla en negro mientras el formulario se escondia.
             */}
            <div className="grid grid-cols-1 items-start gap-4 xl:grid-cols-[1fr_21rem]">
                <div className="min-w-0">
                <div className="mb-3 grid gap-2 sm:grid-cols-4">
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
                        etiqueta="Cuentas abiertas"
                        valor={cifras.personas}
                        pie={cifras.personas > 0 ? 'gente a la que cobrarle' : 'nadie debe nada'}
                    />
                    {/* Lo de HOY, que es lo que se cuadra al cerrar el turno: antes
                        habia que sumarlo a ojo de la lista de pagados. */}
                    <Cifra
                        etiqueta="Cobrado hoy"
                        valor={<Reservado ancho="w-20">{pesos.format(cifras.cobrado_hoy ?? 0)}</Reservado>}
                        pie={`anotado hoy: ${pesos.format(cifras.anotado_hoy ?? 0)}`}
                    />
                    <Cifra
                        etiqueta="Cobrado este mes"
                        valor={<Reservado ancho="w-20">{pesos.format(cifras.cobrado_mes)}</Reservado>}
                        pie="de lo fiado, no de membresías"
                    />
                </div>

                <div className="mb-3 flex flex-wrap items-center gap-2">
                    <div className="flex gap-1">
                        {[
                            ['deben', `Quién debe (${cuentas.length})`],
                            ['cobrado', 'Ya pagado'],
                            // Lo quitado y los cobros deshechos: el control del
                            // mesón, solo para quien administra.
                            ...(registro ? [['registro', 'Registro']] : []),
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

                    {pestana === 'cobrado' && meses.length > 0 ? (
                        <select
                            value={mes}
                            onChange={(e) => router.get('/panel/fiados', { mes: e.target.value }, { preserveState: true, preserveScroll: true, only: ['cobrado', 'mes'] })}
                            aria-label="Mes"
                            className="rounded-control border border-line bg-surface px-2 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                        >
                            {meses.map((m) => (
                                <option key={m.valor} value={m.valor}>{m.etiqueta}</option>
                            ))}
                        </select>
                    ) : null}
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
                                            className="flex min-w-0 items-center gap-2.5 text-left"
                                        >
                                            {/* La cara: a quien hay que cobrarle se
                                                reconoce cuando entra por la puerta,
                                                no leyendo una lista de nombres. */}
                                            <Retrato nombre={cuenta.quien} foto={cuenta.foto} tamano="sm" />
                                            <span className="min-w-0">
                                                <span className="block truncate text-sm font-medium text-chalk">
                                                    {cuenta.quien}
                                                </span>
                                                <span className="apoyo block truncate text-fog">
                                                    {/* QUE DEBE, sin abrir nada: «1 cosa»
                                                        no dice si es una bebida o un
                                                        bidon de proteina, y es lo
                                                        primero que pregunta quien paga. */}
                                                    {cuenta.lineas.map((l) => l.concepto).join(', ')} · desde{' '}
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
                                            </span>
                                        </button>

                                        <div className="flex shrink-0 items-center gap-3">
                                            <span className="font-semibold tabular-nums text-warn">
                                                <Reservado ancho="w-16">
                                                    {pesos.format(cuenta.total)}
                                                </Reservado>
                                            </span>

                                            <Recordar cuenta={cuenta} />

                                            {cuenta.socio_uuid ? (
                                                <Link
                                                    href={`/panel/clientes/${cuenta.socio_uuid}`}
                                                    className="apoyo text-fog transition-colors hover:text-chalk"
                                                >
                                                    Ficha
                                                </Link>
                                            ) : null}

                                            {! cuenta.id_cliente ? (
                                                <button
                                                    type="button"
                                                    onClick={() => setPasando(cuenta)}
                                                    title="Pasar esta cuenta a la ficha de un socio"
                                                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                                                >
                                                    <UserCheckIcon className="size-3.5" aria-hidden="true" />
                                                    A un socio
                                                </button>
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
                                                        {puedeQuitar ? (
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
                                                        ) : null}
                                                    </span>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : null}
                                </li>
                            ))}
                        </ul>
                    )
                ) : pestana === 'registro' ? (
                    <Registro filas={registro ?? []} />
                ) : cobradoVisible.length === 0 ? (
                    <div className="rounded-panel border border-dashed border-line px-4 py-12 text-center">
                        <p className="text-sm text-fog">
                            {filtro !== '' ? 'Nada cobrado que coincida.' : 'Nada cobrado ese mes.'}
                        </p>
                    </div>
                ) : (
                    /* UN RENGLÓN POR COBRO, con todo lo que pagó: antes una fila
                       por cosa, y un cobro de cinco ocupaba cinco filas. */
                    <div className="overflow-x-auto rounded-panel border border-line">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-line bg-surface-2">
                                    <th scope="col" className="px-3 py-2 text-left font-medium text-fog">Quién</th>
                                    <th scope="col" className="px-3 py-2 text-left font-medium text-fog">Qué</th>
                                    <th scope="col" className="px-3 py-2 text-left font-medium text-fog">Pagó</th>
                                    <th scope="col" className="px-3 py-2 text-right font-medium text-fog">Total</th>
                                    <th scope="col" className="px-3 py-2 text-right font-medium text-fog">
                                        <span className="sr-only">Deshacer</span>
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-line bg-surface">
                                {cobradoVisible.map((c) => (
                                    <tr key={c.uuid} className="align-top transition-colors hover:bg-surface-2">
                                        <td className="px-3 py-2 text-chalk">
                                            {c.socio_uuid ? (
                                                <Link href={`/panel/clientes/${c.socio_uuid}`} className="hover:underline">
                                                    {c.quien}
                                                </Link>
                                            ) : (
                                                c.quien
                                            )}
                                        </td>
                                        <td className="px-3 py-2 text-fog">
                                            {c.lineas.map((l) => l.concepto).join(', ')}
                                        </td>
                                        <td className="px-3 py-2 tabular-nums text-fog">
                                            {c.cuando ?? '-'}
                                            <span className="apoyo block">
                                                {[c.medio, c.cobro ? `cobró ${c.cobro}` : null].filter(Boolean).join(' · ')}
                                            </span>
                                        </td>
                                        <td className="px-3 py-2 text-right tabular-nums text-chalk">
                                            <Reservado ancho="w-14">{pesos.format(c.total)}</Reservado>
                                        </td>
                                        <td className="px-3 py-2 text-right">
                                            {/* Lo de hoy lo deshace el mesón, que es donde
                                                ocurre el error de fila. Un cobro de otro
                                                día ya está en la caja de ese día: solo
                                                quien administra. */}
                                            {c.de_hoy || puedeQuitar ? (
                                                <button
                                                    type="button"
                                                    onClick={() => setReabriendo(c)}
                                                    aria-label={`Deshacer el cobro a ${c.quien}`}
                                                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                                                >
                                                    <UndoIcon className="size-3.5" aria-hidden="true" />
                                                    Deshacer
                                                </button>
                                            ) : null}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                </div>

                {/* APUNTAR, SIEMPRE PUESTO. Es la mitad de lo que se viene a
                    hacer a esta pantalla; detras de un boton costaba un clic
                    de mas cada vez y dejaba media pantalla vacia. */}
                <div className="rounded-panel border border-line bg-surface p-4 xl:sticky xl:top-4">
                    <h2 className="rotulo mb-2">Anotar algo fiado</h2>
                    <ApuntarFiado />
                    <p className="apoyo text-fog">
                        Se puede apuntar a un socio o a un nombre suelto, para quien viene de visita.
                    </p>
                </div>
            </div>

            <ConfirmarDinero
                abierto={cobrando !== null}
                alCerrar={() => setCobrando(null)}
                titulo="Cobrar lo fiado"
                quien={cobrando?.quien ?? ''}
                monto={cobrando?.total ?? 0}
                detalle={cobrando?.lineas}
                consecuencia="Su cuenta queda saldada y entra a la caja del mesón."
                conMedio
                conAbono
                etiquetaConfirmar="Pagó"
                accion="/panel/fiados/saldar"
                metodo="post"
                datos={{
                    id_cliente: cobrando?.id_cliente ?? null,
                    nombre: cobrando?.nombre ?? null,
                    // Las líneas que se ven: se salda justo eso, aunque estén
                    // escritas con otra mayúscula o alguien apunte otra cosa
                    // mientras se cobra.
                    lineas: cobrando?.lineas?.map((l) => l.uuid) ?? [],
                    // Lo que debía en pantalla: un abono repetido (doble clic,
                    // la ficha abierta a la vez) se rechaza si ya no es eso.
                    debe_visto: cobrando?.total ?? 0,
                }}
            />

            <ConfirmarDinero
                abierto={quitando !== null}
                alCerrar={() => setQuitando(null)}
                titulo="Quitar de la cuenta"
                quien={quitando?.quien ?? ''}
                monto={quitando?.monto ?? 0}
                detalle={quitando ? [{ concepto: quitando.concepto, monto: quitando.monto }] : []}
                consecuencia="Es para lo que se anotó por error: deja de deberlo y queda en el registro. Si lo pagó, usa «Pagó»."
                etiquetaConfirmar="Quitar"
                peligrosa
                accion={quitando ? `/panel/fiados/${quitando.uuid}` : ''}
                metodo="delete"
            />

            {/* La cifra es lo que vuelve a deberse entero: se reabre todo el
                cobro, no solo la línea pulsada. */}
            <ConfirmarDinero
                abierto={reabriendo !== null}
                alCerrar={() => setReabriendo(null)}
                titulo="Deshacer el cobro"
                quien={reabriendo?.quien ?? ''}
                monto={reabriendo?.total ?? 0}
                detalle={reabriendo?.lineas}
                consecuencia={reabriendo?.de_hoy
                    ? 'Vuelve a deberlo todo lo de ese cobro.'
                    : 'Vuelve a deberlo todo lo de ese cobro. Es de otro día: cambia la caja de ese día y queda en el registro.'}
                etiquetaConfirmar="Vuelve a deber"
                accion={reabriendo ? `/panel/fiados/${reabriendo.uuid}/reabrir` : ''}
                metodo="patch"
            />

            <PasarASocio cuenta={pasando} alCerrar={() => setPasando(null)} />
        </>
    );
}

/** Lo que cambió una cuenta sin cobrarla: lo quitado, los cobros deshechos y las cuentas pasadas a un socio. */
function Registro({ filas }) {
    if (filas.length === 0) {
        return (
            <div className="rounded-panel border border-dashed border-line px-4 py-12 text-center">
                <p className="text-sm text-fog">Nada que registrar todavía.</p>
            </div>
        );
    }

    return (
        <div className="overflow-x-auto rounded-panel border border-line">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b border-line bg-surface-2">
                        <th scope="col" className="px-3 py-2 text-left font-medium text-fog">Cuándo</th>
                        <th scope="col" className="px-3 py-2 text-left font-medium text-fog">Qué</th>
                        <th scope="col" className="px-3 py-2 text-left font-medium text-fog">De quién</th>
                        <th scope="col" className="px-3 py-2 text-right font-medium text-fog">Monto</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-line bg-surface">
                    {filas.map((f) => (
                        <tr key={f.id} className="align-top">
                            <td className="px-3 py-2 tabular-nums text-fog">
                                {f.cuando}
                                {f.usuario ? <span className="apoyo block">{f.usuario}</span> : null}
                            </td>
                            <td className="px-3 py-2 text-chalk">
                                {f.que}
                                <span className="apoyo block text-fog">{f.detalle}</span>
                            </td>
                            <td className="px-3 py-2 text-fog">{f.quien}</td>
                            <td className="px-3 py-2 text-right tabular-nums text-chalk">
                                {f.monto ? <Reservado ancho="w-14">{pesos.format(f.monto)}</Reservado> : '-'}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
