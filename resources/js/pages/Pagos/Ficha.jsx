import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon, BanknoteIcon, PencilIcon, ReceiptTextIcon } from 'lucide-react';

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

export default function Ficha({
    pago,
    metodos,
    requiere_comprobante,
    socio,
    inscripcion,
    otrosPagos,
}) {
    return (
        <>
            <Head title={`Pago ${pago.fecha ?? ''}`} />

            <header className="mb-5">
                <Link
                    href="/panel/pagos"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Pagos
                </Link>

                <div className="mt-1 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="flex flex-wrap items-center gap-2 text-lg font-semibold text-chalk">
                            {pago.tipo}
                            <Estado codigo={pago.id_estado} />
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
                            {pago.fecha ? ` · ${pago.fecha}` : ''}
                        </p>
                    </div>

                    <div className="flex gap-2">
                        {/* Corregir el error de tecleo: 40.000 donde iba 4.000,
                            la tarjeta donde iba el efectivo. */}
                        <Link
                            href={`/panel/pagos/${pago.uuid}/editar`}
                            className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                        >
                            <PencilIcon className="size-4" aria-hidden="true" />
                            Corregir
                        </Link>

                        {pago.pendiente > 0 && inscripcion ? (
                            <Link
                                href={`/panel/pagos/cobrar?inscripcion=${inscripcion.uuid}`}
                                className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                            >
                                <BanknoteIcon className="size-4" aria-hidden="true" />
                                Cobrar el saldo
                            </Link>
                        ) : null}
                    </div>
                </div>
            </header>

            {/* El monto primero: la ficha se abre casi siempre para responder
                «¿esto se pagó, y cuánto?». */}
            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Se cobró</p>
                    <p className="mt-0.5 text-xl font-semibold tabular-nums text-chalk">
                        {pesos.format(pago.abonado)}
                    </p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Precio de la membresía</p>
                    <p className="mt-0.5 text-xl font-semibold tabular-nums text-fog">
                        {pesos.format(pago.total)}
                    </p>
                </div>
                <div
                    className={`rounded-panel border p-3 ${
                        pago.pendiente > 0 ? 'border-warn/40 bg-warn/5' : 'border-ok/40 bg-ok/5'
                    }`}
                >
                    <p className="rotulo">{pago.pendiente > 0 ? 'Quedó debiendo' : 'Sin saldo'}</p>
                    <p
                        className={`mt-0.5 text-xl font-semibold tabular-nums ${
                            pago.pendiente > 0 ? 'text-warn' : 'text-ok'
                        }`}
                    >
                        {pago.pendiente > 0 ? pesos.format(pago.pendiente) : '—'}
                    </p>
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-3">
                <div className="space-y-3">
                    <Bloque titulo="Cómo se pagó">
                        {metodos.length === 0 ? (
                            <p className="text-sm text-fog">Sin método registrado.</p>
                        ) : (
                            <ul className="space-y-2">
                                {metodos.map((m, i) => (
                                    <li
                                        key={`${m.nombre}-${i}`}
                                        className="flex items-center justify-between gap-3 text-sm"
                                    >
                                        <span className="text-chalk">{m.nombre}</span>
                                        <span className="tabular-nums text-fog">
                                            {pesos.format(m.monto)}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}

                        {/* Si el método exige comprobante y no hay referencia,
                            falta un dato que alguien tendrá que buscar después. */}
                        {requiere_comprobante && ! pago.referencia ? (
                            <p className="apoyo mt-3 flex items-start gap-1.5 text-warn">
                                <ReceiptTextIcon className="mt-0.5 size-3.5 shrink-0" aria-hidden="true" />
                                Este método requiere comprobante y no se registró ninguna referencia.
                            </p>
                        ) : null}
                    </Bloque>

                    <Bloque titulo="Detalle">
                        <dl className="space-y-3">
                            <Dato etiqueta="Referencia">{pago.referencia}</Dato>
                            <Dato etiqueta="Registrado">{pago.registrado}</Dato>
                            {pago.cuotas > 1 ? <Dato etiqueta="Cuotas">{pago.cuotas}</Dato> : null}
                            <Dato etiqueta="Periodo cubierto">
                                {pago.periodo_inicio && pago.periodo_fin
                                    ? `${pago.periodo_inicio} — ${pago.periodo_fin}`
                                    : null}
                            </Dato>
                        </dl>
                    </Bloque>

                    {socio ? (
                        <Bloque titulo="Socio">
                            <dl className="space-y-3">
                                <Dato etiqueta="RUT">{socio.rut}</Dato>
                                <Dato etiqueta="Correo">{socio.email}</Dato>
                                <Dato etiqueta="Celular">{socio.celular}</Dato>
                            </dl>
                        </Bloque>
                    ) : null}

                    {pago.observaciones ? (
                        <Bloque titulo="Observaciones">
                            <p className="text-sm whitespace-pre-line text-fog">{pago.observaciones}</p>
                        </Bloque>
                    ) : null}
                </div>

                <div className="space-y-3 lg:col-span-2">
                    {inscripcion ? (
                        <Bloque titulo="Membresía que paga">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <Link
                                        href={`/panel/inscripciones/${inscripcion.uuid}`}
                                        className="text-sm font-medium text-chalk hover:underline"
                                    >
                                        {inscripcion.membresia ?? 'Membresía'}
                                    </Link>
                                    <p className="apoyo text-fog">
                                        {inscripcion.inicio} — {inscripcion.vence}
                                    </p>
                                </div>
                                <Estado codigo={inscripcion.id_estado} />
                            </div>
                        </Bloque>
                    ) : null}

                    <Bloque titulo="Otros cobros de esta membresía">
                        <Tabla
                            columnas={['Fecha', 'Método', 'Estado', 'Abonado']}
                            vacia={otrosPagos.length === 0}
                            mensajeVacio="Este es el único cobro registrado de esta membresía."
                        >
                            {otrosPagos.map((p) => (
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
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>
                </div>
            </div>
        </>
    );
}
