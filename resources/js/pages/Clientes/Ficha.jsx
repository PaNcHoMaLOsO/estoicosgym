import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    ArrowLeftIcon,
    PencilIcon,
    PlusIcon,
    ShoppingBagIcon,
    UserMinusIcon,
    UserPlusIcon,
} from 'lucide-react';

import Dialogo from '@/components/Dialogo';
import Estado from '@/components/Estado';
import { Reservado } from '@/Privado';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Dato({ etiqueta, children }) {
    return (
        <div>
            <dt className="rotulo">{etiqueta}</dt>
            <dd className="mt-0.5 text-sm text-chalk">{children || <span className="text-fog">—</span>}</dd>
        </div>
    );
}

function Bloque({ titulo, children, accion }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <div className="mb-3 flex items-center justify-between gap-3">
                <h2 className="rotulo">{titulo}</h2>
                {accion}
            </div>
            {children}
        </section>
    );
}

/** Cuánto le queda a la membresía vigente. Solo se pinta si exige actuar. */
function Vigencia({ dias }) {
    if (dias === null || dias === undefined) {
        return null;
    }

    if (dias < 0) {
        return <span className="font-medium text-danger">Venció hace {Math.abs(dias)} días</span>;
    }

    if (dias <= 7) {
        return <span className="font-medium text-warn">Vence en {dias} días</span>;
    }

    return <span className="text-fog">Quedan {dias} días</span>;
}

export default function Ficha({ cliente, inscripciones, pagos, resumen, fiado }) {
    // null = ningun dialogo abierto.
    const [confirmando, setConfirmando] = useState(null);

    const vigente = inscripciones.find((i) => i.vigente);

    return (
        <>
            <Head title={cliente.nombre} />

            <header className="mb-5">
                <Link
                    href="/panel/clientes"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Clientes
                </Link>

                <div className="mt-1 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="text-lg font-semibold text-chalk">
                            {cliente.nombre}
                            {! cliente.activo ? (
                                <span className="ml-2 rounded-pill border border-line bg-surface-2 px-2 py-0.5 align-middle text-xs text-fog">
                                    Dado de baja
                                </span>
                            ) : null}
                        </h1>
                        <p className="apoyo text-fog">
                            {cliente.rut ?? 'Sin RUT'} · socio desde {cliente.desde ?? '—'}
                            {vigente ? <> · <Vigencia dias={vigente.dias} /></> : null}
                        </p>
                    </div>

                    <div className="flex gap-2">
                        <Link
                            href={`/panel/clientes/${cliente.uuid}/editar`}
                            className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                        >
                            <PencilIcon className="size-4" aria-hidden="true" />
                            Editar
                        </Link>
                        {/* Dar de baja o volver a dar de alta: nunca las dos,
                            porque solo una tiene sentido en cada momento. */}
                        {cliente.activo ? (
                            <button
                                type="button"
                                onClick={() => setConfirmando('desactivar')}
                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                            >
                                <UserMinusIcon className="size-4" aria-hidden="true" />
                                Dar de baja
                            </button>
                        ) : (
                            <button
                                type="button"
                                onClick={() => router.patch(`/panel/clientes/${cliente.uuid}/reactivar`, {}, { preserveScroll: true })}
                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                            >
                                <UserPlusIcon className="size-4" aria-hidden="true" />
                                Reactivar
                            </button>
                        )}

                        <Link
                            href={`/panel/pagos/cobrar?inscripcion=${vigente?.uuid ?? ''}`}
                            className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                        >
                            <PlusIcon className="size-4" aria-hidden="true" />
                            Cobrar
                        </Link>
                    </div>
                </div>
            </header>

            {/* EL AVISO DE LO FIADO, arriba de todo y antes de las cifras.
                Si viene a pagar su mensualidad y ademas debe tres bebidas, hay
                que saberlo con la persona delante, no dos semanas despues.

                Va aparte de lo que debe de su membresia a proposito: son dos
                deudas que se cobran por sitios distintos, y sumarlas daria una
                cifra que no se puede cobrar de una vez. */}
            {fiado ? (
                <div className="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-panel border border-warn/40 bg-warn/5 px-3 py-2.5">
                    <p className="flex items-start gap-2 text-sm text-warn">
                        <ShoppingBagIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                        <span>
                            Debe{' '}
                            <span className="font-semibold tabular-nums">
                                <Reservado ancho="w-14">{pesos.format(fiado.total)}</Reservado>
                            </span>{' '}
                            del mesón · {fiado.cuantas}{' '}
                            {fiado.cuantas === 1 ? 'cosa' : 'cosas'} desde {fiado.desde}
                            <span className="apoyo block text-fog">
                                {fiado.lineas.map((l) => l.concepto).join(', ')}
                            </span>
                        </span>
                    </p>

                    <Link
                        href="/panel/fiados"
                        className="apoyo shrink-0 rounded-control border border-warn/40 px-2.5 py-1 text-warn transition-colors hover:bg-warn/10"
                    >
                        Cobrarlo
                    </Link>
                </div>
            ) : null}

            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Membresías</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">
                        {resumen.inscripciones}
                    </p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Ha pagado</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">
                        {pesos.format(resumen.pagado)}
                    </p>
                </div>
                {/* La deuda es lo único accionable de las tres. */}
                <div
                    className={`rounded-panel border p-3 ${
                        resumen.debe > 0 ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface'
                    }`}
                >
                    <p className="rotulo">Debe</p>
                    <p
                        className={`mt-0.5 text-lg font-semibold tabular-nums ${
                            resumen.debe > 0 ? 'text-warn' : 'text-chalk'
                        }`}
                    >
                        {pesos.format(resumen.debe)}
                    </p>
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-3">
                <div className="space-y-3 lg:col-span-1">
                    <Bloque titulo="Contacto">
                        <dl className="space-y-3">
                            <Dato etiqueta="Correo">
                                {cliente.email ? (
                                    <a href={`mailto:${cliente.email}`} className="hover:underline">
                                        {cliente.email}
                                    </a>
                                ) : null}
                            </Dato>
                            <Dato etiqueta="Celular">{cliente.celular}</Dato>
                            <Dato etiqueta="Dirección">{cliente.direccion}</Dato>
                            <Dato etiqueta="Nacimiento">
                                {cliente.nacimiento
                                    ? `${cliente.nacimiento}${cliente.edad !== null ? ` · ${cliente.edad} años` : ''}`
                                    : null}
                            </Dato>
                            <Dato etiqueta="Convenio">{cliente.convenio}</Dato>
                        </dl>
                    </Bloque>

                    {/* En urgencia se busca este dato con prisa: va en su propio
                        bloque y no perdido entre los demás. */}
                    <Bloque titulo="En caso de emergencia">
                        <dl className="space-y-3">
                            <Dato etiqueta="Contacto">{cliente.contacto_emergencia}</Dato>
                            <Dato etiqueta="Teléfono">{cliente.telefono_emergencia}</Dato>
                        </dl>
                    </Bloque>

                    {cliente.menor && cliente.apoderado ? (
                        <Bloque titulo="Apoderado">
                            <dl className="space-y-3">
                                <Dato etiqueta="Nombre">{cliente.apoderado.nombre}</Dato>
                                <Dato etiqueta="RUT">{cliente.apoderado.rut}</Dato>
                                <Dato etiqueta="Correo">{cliente.apoderado.email}</Dato>
                                <Dato etiqueta="Teléfono">{cliente.apoderado.telefono}</Dato>
                                <Dato etiqueta="Parentesco">{cliente.apoderado.parentesco}</Dato>
                                <Dato etiqueta="Consentimiento">
                                    {cliente.apoderado.consentimiento ? (
                                        'Firmado'
                                    ) : (
                                        <span className="text-warn">Pendiente</span>
                                    )}
                                </Dato>
                            </dl>
                        </Bloque>
                    ) : null}

                    {cliente.observaciones ? (
                        <Bloque titulo="Observaciones">
                            <p className="text-sm whitespace-pre-line text-fog">{cliente.observaciones}</p>
                        </Bloque>
                    ) : null}
                </div>

                <div className="space-y-3 lg:col-span-2">
                    <Bloque titulo="Membresías">
                        <Tabla
                            columnas={['Plan', 'Estado', 'Inicio', 'Vence', 'Total', 'Debe']}
                            vacia={inscripciones.length === 0}
                            mensajeVacio="Este socio todavía no tiene ninguna membresía."
                        >
                            {inscripciones.map((i) => (
                                <Fila key={i.uuid}>
                                    <Celda className="font-medium text-chalk">
                                        <Link
                                            href={`/panel/inscripciones/${i.uuid}`}
                                            className="hover:underline"
                                        >
                                            {i.membresia ?? '—'}
                                        </Link>
                                    </Celda>
                                    <Celda>
                                        <Estado codigo={i.id_estado} />
                                    </Celda>
                                    <Celda className="tabular-nums">{i.inicio ?? '—'}</Celda>
                                    <Celda className="tabular-nums">{i.vence ?? '—'}</Celda>
                                    <Cifra>{pesos.format(i.total)}</Cifra>
                                    <Cifra className={i.pendiente > 0 ? 'font-medium text-warn' : ''}>
                                        {i.pendiente > 0 ? pesos.format(i.pendiente) : '—'}
                                    </Cifra>
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>

                    <Bloque titulo="Últimos pagos">
                        <Tabla
                            columnas={['Fecha', 'Método', 'Estado', 'Abonado', 'Pendiente']}
                            vacia={pagos.length === 0}
                            mensajeVacio="Todavía no ha pagado nada."
                        >
                            {pagos.map((p) => (
                                <Fila key={p.uuid}>
                                    <Celda className="tabular-nums text-chalk">
                                        <Link href={`/panel/pagos/${p.uuid}`} className="hover:underline">
                                            {p.fecha ?? '—'}
                                        </Link>
                                    </Celda>
                                    <Celda>{p.metodo ?? '—'}</Celda>
                                    <Celda>
                                        <Estado codigo={p.id_estado} />
                                    </Celda>
                                    <Cifra className="text-chalk">{pesos.format(p.abonado)}</Cifra>
                                    <Cifra className={p.pendiente > 0 ? 'text-warn' : ''}>
                                        {p.pendiente > 0 ? pesos.format(p.pendiente) : '—'}
                                    </Cifra>
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>
                </div>
            </div>
            <Dialogo
                abierto={confirmando === 'desactivar'}
                alCerrar={() => setConfirmando(null)}
                titulo="Dar de baja al socio"
                descripcion={`${cliente.nombre} dejará de aparecer al inscribir y al cobrar. Su ficha, su historial y sus pagos siguen ahí, y se puede reactivar cuando vuelva.`}
                accion={`/panel/clientes/${cliente.uuid}/desactivar`}
                via="inertia"
                metodo="patch"
                etiquetaConfirmar="Dar de baja"
            />

        </>
    );
}
