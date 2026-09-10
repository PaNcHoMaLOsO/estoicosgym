import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    ArrowRightLeftIcon,
    BanknoteIcon,
    PauseIcon,
    PlayIcon,
    RefreshCwIcon,
} from 'lucide-react';

import Estado from '@/components/Estado';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Bloque({ titulo, children }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo mb-3">{titulo}</h2>
            {children}
        </section>
    );
}

function Dato({ etiqueta, children }) {
    return (
        <div>
            <dt className="rotulo">{etiqueta}</dt>
            <dd className="mt-0.5 text-sm text-chalk">{children || <span className="text-fog">—</span>}</dd>
        </div>
    );
}

/** Los días que quedan, con color solo cuando exigen actuar. */
function Vigencia({ dias }) {
    if (dias === null || dias === undefined) {
        return <span className="text-fog">Sin fecha de vencimiento</span>;
    }

    if (dias < 0) {
        return <span className="font-medium text-danger">Venció hace {Math.abs(dias)} días</span>;
    }

    if (dias === 0) {
        return <span className="font-medium text-danger">Vence hoy</span>;
    }

    if (dias <= 7) {
        return <span className="font-medium text-warn">Quedan {dias} días</span>;
    }

    return <span className="text-fog">Quedan {dias} días</span>;
}

export default function Ficha({ inscripcion, socio, pago, pausa, puede, pagos, movimientos }) {
    // Las acciones siguen en Blade. Se ofrecen SOLO las que el servidor
    // aceptaría ahora mismo: un botón que lleva a un error enseña a desconfiar
    // de lo que hay en pantalla.
    const acciones = [
        puede.cobrar && {
            href: `/panel/pagos/cobrar?inscripcion=${inscripcion.uuid}`,
            etiqueta: 'Cobrar',
            Icono: BanknoteIcon,
            primaria: true,
        },
        puede.renovar && {
            href: `/admin/inscripciones/${inscripcion.uuid}/renovar`,
            etiqueta: 'Renovar',
            Icono: RefreshCwIcon,
        },
        puede.pausar && {
            href: `/admin/inscripciones/${inscripcion.uuid}`,
            etiqueta: 'Pausar',
            Icono: PauseIcon,
        },
        puede.reanudar && {
            href: `/admin/inscripciones/${inscripcion.uuid}`,
            etiqueta: 'Reanudar',
            Icono: PlayIcon,
        },
        puede.traspasar && {
            href: `/admin/inscripciones/${inscripcion.uuid}`,
            etiqueta: 'Traspasar',
            Icono: ArrowRightLeftIcon,
        },
    ].filter(Boolean);

    return (
        <>
            <Head title={`${socio?.nombre ?? 'Inscripción'} · ${inscripcion.membresia ?? ''}`} />

            <header className="mb-5">
                <Link
                    href="/panel/inscripciones"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Inscripciones
                </Link>

                <div className="mt-1 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="flex flex-wrap items-center gap-2 text-lg font-semibold text-chalk">
                            {inscripcion.membresia ?? 'Membresía'}
                            <Estado codigo={inscripcion.id_estado} />
                        </h1>
                        <p className="apoyo text-fog">
                            {socio ? (
                                <Link
                                    href={`/panel/clientes/${socio.uuid}`}
                                    className="text-chalk hover:underline"
                                >
                                    {socio.nombre}
                                </Link>
                            ) : (
                                'Socio eliminado'
                            )}
                            {' · '}
                            <Vigencia dias={inscripcion.dias} />
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {acciones.map(({ href, etiqueta, Icono, primaria }) => (
                            <a
                                key={etiqueta}
                                href={href}
                                className={`inline-flex items-center gap-1.5 rounded-control px-3 py-1.5 text-sm transition-colors ${
                                    primaria
                                        ? 'bg-volt font-medium text-on-volt hover:opacity-90'
                                        : 'border border-line text-chalk hover:bg-surface-2'
                                }`}
                            >
                                <Icono className="size-4" aria-hidden="true" />
                                {etiqueta}
                            </a>
                        ))}
                    </div>
                </div>
            </header>

            {/* Lo primero: cuánto se debe. Es lo que se mira con el socio
                delante, antes que cualquier otro dato de la ficha. */}
            <div className="mb-4 rounded-panel border border-line bg-surface p-4">
                <div className="flex flex-wrap items-baseline justify-between gap-3">
                    <div>
                        <p className="rotulo">
                            {pago.pendiente > 0 ? 'Falta por pagar' : 'Membresía pagada'}
                        </p>
                        <p
                            className={`mt-0.5 text-2xl font-semibold tabular-nums ${
                                pago.pendiente > 0 ? 'text-warn' : 'text-ok'
                            }`}
                        >
                            {pago.pendiente > 0 ? pesos.format(pago.pendiente) : pesos.format(pago.total)}
                        </p>
                    </div>
                    <p className="apoyo text-fog">
                        {pesos.format(pago.abonado)} de {pesos.format(pago.total)} · {pago.porcentaje}%
                    </p>
                </div>

                <div className="mt-3 h-1.5 overflow-hidden rounded-pill bg-surface-2">
                    <div
                        className={`h-full rounded-pill ${pago.pendiente > 0 ? 'bg-warn' : 'bg-ok'}`}
                        style={{ width: `${pago.porcentaje}%` }}
                    />
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-3">
                <div className="space-y-3">
                    <Bloque titulo="Membresía">
                        <dl className="space-y-3">
                            <Dato etiqueta="Inicio">{inscripcion.inicio}</Dato>
                            <Dato etiqueta="Vence">{inscripcion.vence}</Dato>
                            <Dato etiqueta="Convenio">{inscripcion.convenio}</Dato>
                            {inscripcion.descuento > 0 ? (
                                <Dato etiqueta="Descuento">
                                    {pesos.format(inscripcion.descuento)}
                                    {inscripcion.motivo_descuento ? ` · ${inscripcion.motivo_descuento}` : ''}
                                </Dato>
                            ) : null}
                        </dl>
                    </Bloque>

                    <Bloque titulo="Pausas">
                        {pausa.pausada ? (
                            <div className="rounded-panel border border-warn/40 bg-warn/5 p-3 text-sm text-warn">
                                <p className="font-medium">Pausada desde el {pausa.desde}</p>
                                {pausa.hasta ? <p className="apoyo">Se reanuda el {pausa.hasta}</p> : null}
                                {pausa.razon ? <p className="apoyo mt-1">{pausa.razon}</p> : null}
                            </div>
                        ) : (
                            <p className="text-sm text-fog">No está pausada.</p>
                        )}

                        <p className="apoyo mt-3 text-fog">
                            {pausa.permitidas === 0
                                ? 'Este plan no admite pausas.'
                                : `Usadas ${pausa.usadas} de ${pausa.permitidas} · quedan ${pausa.disponibles}`}
                        </p>
                    </Bloque>

                    {socio ? (
                        <Bloque titulo="Contacto del socio">
                            <dl className="space-y-3">
                                <Dato etiqueta="RUT">{socio.rut}</Dato>
                                <Dato etiqueta="Correo">{socio.email}</Dato>
                                <Dato etiqueta="Celular">{socio.celular}</Dato>
                            </dl>
                        </Bloque>
                    ) : null}

                    {inscripcion.observaciones ? (
                        <Bloque titulo="Observaciones">
                            <p className="text-sm whitespace-pre-line text-fog">
                                {inscripcion.observaciones}
                            </p>
                        </Bloque>
                    ) : null}
                </div>

                <div className="space-y-3 lg:col-span-2">
                    <Bloque titulo="Pagos de esta membresía">
                        <Tabla
                            columnas={['Fecha', 'Método', 'Tipo', 'Estado', 'Abonado']}
                            vacia={pagos.length === 0}
                            mensajeVacio="Todavía no se ha cobrado nada de esta membresía."
                        >
                            {pagos.map((p) => (
                                <Fila key={p.uuid}>
                                    <Celda className="tabular-nums text-chalk">
                                        <a href={`/panel/pagos/${p.uuid}`} className="hover:underline">
                                            {p.fecha ?? '—'}
                                        </a>
                                    </Celda>
                                    <Celda>{p.metodo ?? '—'}</Celda>
                                    <Celda>{p.tipo}</Celda>
                                    <Celda>
                                        <Estado codigo={p.id_estado} />
                                    </Celda>
                                    <Cifra className="text-chalk">{pesos.format(p.abonado)}</Cifra>
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>

                    <Bloque titulo="Qué le ha pasado">
                        {movimientos.length === 0 ? (
                            <p className="apoyo py-4 text-center text-fog">
                                Sin movimientos registrados.
                            </p>
                        ) : (
                            <ol className="space-y-2">
                                {movimientos.map((m) => (
                                    <li
                                        key={m.id}
                                        className="flex justify-between gap-3 border-b border-line pb-2 text-sm last:border-0 last:pb-0"
                                    >
                                        <div>
                                            <p className="text-chalk">{m.que}</p>
                                            {m.motivo ? (
                                                <p className="apoyo text-fog">{m.motivo}</p>
                                            ) : null}
                                        </div>
                                        <div className="shrink-0 text-right">
                                            <p className="apoyo tabular-nums text-fog">{m.cuando ?? '—'}</p>
                                            {m.quien ? <p className="apoyo text-fog">{m.quien}</p> : null}
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </Bloque>
                </div>
            </div>
        </>
    );
}
