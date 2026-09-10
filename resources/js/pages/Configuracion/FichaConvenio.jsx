import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon, PencilIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import { Celda, Fila, Tabla } from '@/components/Tabla';

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

export default function FichaConvenio({ convenio, cifras, socios }) {
    return (
        <>
            <Head title={convenio.nombre} />

            <header className="mb-5">
                <Link
                    href="/panel/convenios"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Convenios
                </Link>

                <div className="mt-1 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="flex flex-wrap items-center gap-2 text-lg font-semibold text-chalk">
                            {convenio.nombre}
                            <Activo valor={convenio.activo} />
                        </h1>
                        <p className="apoyo text-fog">
                            {convenio.tipo ?? 'Sin tipo'} · descuento {convenio.descuento}
                        </p>
                    </div>

                    <a
                        href={`/admin/convenios/${convenio.uuid}/edit`}
                        className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        <PencilIcon className="size-4" aria-hidden="true" />
                        Editar
                    </a>
                </div>
            </header>

            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Socios acogidos</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">{cifras.socios}</p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">De ellos, activos</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">{cifras.activos}</p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Membresías al día</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">{cifras.vigentes}</p>
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-3">
                <div className="space-y-3">
                    <Bloque titulo="Contacto en la empresa">
                        <dl className="space-y-3">
                            <Dato etiqueta="Nombre">{convenio.contacto_nombre}</Dato>
                            <Dato etiqueta="Correo">
                                {convenio.contacto_email ? (
                                    <a
                                        href={`mailto:${convenio.contacto_email}`}
                                        className="hover:underline"
                                    >
                                        {convenio.contacto_email}
                                    </a>
                                ) : null}
                            </Dato>
                            <Dato etiqueta="Teléfono">{convenio.contacto_telefono}</Dato>
                        </dl>
                    </Bloque>

                    {convenio.descripcion ? (
                        <Bloque titulo="Descripción">
                            <p className="text-sm whitespace-pre-line text-fog">{convenio.descripcion}</p>
                        </Bloque>
                    ) : null}
                </div>

                <div className="lg:col-span-2">
                    <Bloque titulo="Socios con este convenio">
                        <Tabla
                            columnas={['Socio', 'RUT', 'Estado']}
                            vacia={socios.length === 0}
                            mensajeVacio="Todavía no hay socios acogidos a este convenio."
                        >
                            {socios.map((s) => (
                                <Fila key={s.uuid}>
                                    <Celda className="font-medium text-chalk">
                                        <Link href={`/panel/clientes/${s.uuid}`} className="hover:underline">
                                            {s.nombre}
                                        </Link>
                                    </Celda>
                                    <Celda className="tabular-nums">{s.rut ?? '—'}</Celda>
                                    <Celda>
                                        <Activo valor={s.activo} />
                                    </Celda>
                                </Fila>
                            ))}
                        </Tabla>

                        {socios.length >= 50 ? (
                            <p className="apoyo mt-3 text-fog">
                                Se muestran los primeros 50.
                            </p>
                        ) : null}
                    </Bloque>
                </div>
            </div>
        </>
    );
}
