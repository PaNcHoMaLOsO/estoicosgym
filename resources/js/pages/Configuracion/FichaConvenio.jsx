import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeftIcon, PencilIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo, { CAMPOS_CONVENIO, valoresDeConvenio } from '@/components/FormularioCatalogo';
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
            <dd className="mt-0.5 text-sm text-chalk">{children || <span className="text-fog">-</span>}</dd>
        </div>
    );
}

const pesos = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 });

/**
 * Lo que ESTE convenio paga por cada plan.
 *
 * Los clubes deportivos negocian el suyo: uno paga 10.000 la mensualidad y otro
 * 15.000, por venir tres veces por semana. Sin esto había que escribir un
 * descuento a mano en cada inscripción, y ese descuento no lo comprueba nadie.
 *
 * DEJARLO VACÍO NO ES CERO: significa que paga el precio con convenio del plan,
 * como el resto. Un cero sería regalar la membresía.
 */
function PreciosDelConvenio({ convenio, planes }) {
    const { data, setData, put, processing, errors, isDirty } = useForm({
        precios: planes.map((p) => ({
            id_membresia: p.id,
            precio: p.propio === '' ? '' : String(p.propio),
            condicion: p.condicion ?? '',
        })),
    });

    const cambiar = (i, campo, valor) =>
        setData(
            'precios',
            data.precios.map((fila, j) => (j === i ? { ...fila, [campo]: valor } : fila)),
        );

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                put(`/panel/convenios/${convenio.uuid}/precios`, { preserveScroll: true });
            }}
        >
            <ul className="space-y-3">
                {planes.map((plan, i) => (
                    <li key={plan.id} className="grid gap-2 border-b border-line pb-3 last:border-0 last:pb-0 sm:grid-cols-[1fr_auto]">
                        <div className="min-w-0">
                            <p className="text-sm text-chalk">{plan.nombre}</p>
                            <p className="apoyo tabular-nums text-fog">
                                {pesos.format(plan.precio)}
                                {plan.precio_convenio ? ` · con convenio ${pesos.format(plan.precio_convenio)}` : ''}
                            </p>
                            <input
                                type="text"
                                value={data.precios[i].condicion}
                                onChange={(e) => cambiar(i, 'condicion', e.target.value)}
                                placeholder="Lo acordado: 3 veces por semana, lunes a viernes…"
                                className="mt-1.5 w-full rounded-control border border-line bg-surface px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                            />
                        </div>

                        <div className="sm:w-40">
                            <label className="apoyo block text-fog" htmlFor={`precio-${plan.id}`}>
                                Paga
                            </label>
                            <input
                                id={`precio-${plan.id}`}
                                type="number"
                                min="0"
                                inputMode="numeric"
                                value={data.precios[i].precio}
                                onChange={(e) => cambiar(i, 'precio', e.target.value)}
                                placeholder="el general"
                                className={`w-full rounded-control border bg-surface px-2.5 py-1.5 text-sm tabular-nums text-chalk placeholder:text-fog focus:outline-none ${
                                    errors[`precios.${i}.precio`] ? 'border-danger' : 'border-line focus:border-line-strong'
                                }`}
                            />
                            {errors[`precios.${i}.precio`] ? (
                                <p className="apoyo mt-1 text-danger">{errors[`precios.${i}.precio`]}</p>
                            ) : null}
                        </div>
                    </li>
                ))}
            </ul>

            <div className="mt-3 flex items-center gap-3">
                <button
                    type="submit"
                    disabled={processing || ! isDirty}
                    className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                >
                    {processing ? 'Guardando…' : 'Guardar precios'}
                </button>
                <p className="apoyo text-fog">Vacío = paga el precio con convenio del plan.</p>
            </div>
        </form>
    );
}

export default function FichaConvenio({ convenio, cifras, socios, planes = [] }) {
    const [editando, setEditando] = useState(false);

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

                    <button
                        type="button"
                        onClick={() => setEditando(true)}
                        className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        <PencilIcon className="size-4" aria-hidden="true" />
                        Editar
                    </button>
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

                    {planes.length > 0 ? (
                        <Bloque titulo="Lo que paga este convenio">
                            <PreciosDelConvenio convenio={convenio} planes={planes} />
                        </Bloque>
                    ) : null}

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
                                    <Celda className="tabular-nums">{s.rut ?? '-'}</Celda>
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
            <FormularioCatalogo
                abierto={editando}
                alCerrar={() => setEditando(false)}
                titulo="Editar convenio"
                descripcion="Los socios que ya vinieron por este convenio no cambian."
                accion={`/panel/convenios/${convenio.uuid}`}
                metodo="put"
                campos={CAMPOS_CONVENIO}
                valores={valoresDeConvenio(convenio)}
            />
        </>
    );
}
