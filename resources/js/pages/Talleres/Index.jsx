import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { ChevronLeftIcon, ChevronRightIcon, PlusIcon } from 'lucide-react';

import { Celda, Fila, Tabla } from '@/components/Tabla';
import { Cifra, pesos } from '@/components/Tablero';
import { Reservado } from '@/Privado';

/**
 * Talleres y arriendos.
 *
 * ES LA OTRA MITAD DEL NEGOCIO: la sala que se le presta a un colegio y se le
 * factura por hora a fin de mes. Vivía fuera del sistema —un horario en Excel y
 * el calendario de Windows al lado para contar los días—, así que cada mes
 * había que rehacer la misma cuenta a mano.
 */

/** Ir al mes anterior o al siguiente sin escribir la fecha. */
function otroMes(periodo, cuantos) {
    const [anio, mes] = periodo.split('-').map(Number);
    const fecha = new Date(anio, mes - 1 + cuantos, 1);

    return `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}`;
}

function NuevoTaller({ instituciones, alCerrar }) {
    const { data, setData, post, processing, errors } = useForm({
        institucion_uuid: '',
        institucion_nombre: '',
        institucion_rut: '',
        nombre: '',
        descripcion_factura: '',
        precio_hora: '',
    });

    const campo =
        'w-full rounded-control border border-line bg-surface-2 px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none';

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                post('/panel/talleres', { onSuccess: alCerrar });
            }}
            className="mb-4 space-y-3 rounded-panel border border-line bg-surface p-4"
        >
            <h2 className="rotulo">Nuevo taller o arriendo</h2>

            <div className="grid gap-3 sm:grid-cols-2">
                <label className="block">
                    <span className="rotulo">A quién se le factura</span>
                    {/* Se elige una institución que ya esté o se escribe una
                        nueva: pedir primero «crear institución» y después
                        «crear taller» son dos pantallas para una sola cosa. */}
                    <select
                        value={data.institucion_uuid}
                        onChange={(e) => setData('institucion_uuid', e.target.value)}
                        className={`${campo} mt-1`}
                    >
                        <option value="">Una nueva…</option>
                        {instituciones.map((i) => (
                            <option key={i.uuid} value={i.uuid}>
                                {i.nombre}
                                {i.rut ? ` · ${i.rut}` : ''}
                            </option>
                        ))}
                    </select>
                </label>

                {data.institucion_uuid === '' ? (
                    <div className="grid grid-cols-[1fr_9rem] gap-2">
                        <label className="block">
                            <span className="rotulo">Nombre</span>
                            <input
                                type="text"
                                value={data.institucion_nombre}
                                onChange={(e) => setData('institucion_nombre', e.target.value)}
                                placeholder="Corporación Educacional…"
                                className={`${campo} mt-1`}
                            />
                        </label>
                        <label className="block">
                            <span className="rotulo">RUT</span>
                            <input
                                type="text"
                                value={data.institucion_rut}
                                onChange={(e) => setData('institucion_rut', e.target.value)}
                                placeholder="65.154.436-K"
                                className={`${campo} mt-1`}
                            />
                        </label>
                    </div>
                ) : null}
            </div>

            {errors.institucion_nombre ? <p className="apoyo text-danger">{errors.institucion_nombre}</p> : null}

            <div className="grid gap-3 sm:grid-cols-[1fr_1fr_10rem]">
                <label className="block">
                    <span className="rotulo">Qué es</span>
                    <input
                        type="text"
                        value={data.nombre}
                        onChange={(e) => setData('nombre', e.target.value)}
                        placeholder="Clases grupales del colegio"
                        className={`${campo} mt-1`}
                    />
                </label>
                <label className="block">
                    <span className="rotulo">Cómo va en la factura</span>
                    <input
                        type="text"
                        value={data.descripcion_factura}
                        onChange={(e) => setData('descripcion_factura', e.target.value)}
                        placeholder="Uso instalaciones para clase grupal"
                        className={`${campo} mt-1`}
                    />
                </label>
                <label className="block">
                    <span className="rotulo">La hora (con IVA)</span>
                    <input
                        type="number"
                        min="1"
                        value={data.precio_hora}
                        onChange={(e) => setData('precio_hora', e.target.value)}
                        placeholder="30000"
                        className={`${campo} mt-1 tabular-nums`}
                    />
                </label>
            </div>

            {errors.nombre ? <p className="apoyo text-danger">{errors.nombre}</p> : null}
            {errors.precio_hora ? <p className="apoyo text-danger">{errors.precio_hora}</p> : null}

            <div className="flex items-center gap-3">
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                >
                    Crear
                </button>
                <button type="button" onClick={alCerrar} className="apoyo text-fog transition-colors hover:text-chalk">
                    Cancelar
                </button>
            </div>
        </form>
    );
}

export default function Index({ periodo, mesLegible, talleres, instituciones, porCobrar }) {
    const [creando, setCreando] = useState(false);

    const delMes = talleres.reduce((suma, t) => suma + t.total, 0);
    const horas = talleres.reduce((suma, t) => suma + t.horas, 0);

    return (
        <>
            <Head title="Talleres y arriendos" />

            <header className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Talleres y arriendos</h1>
                    <p className="apoyo text-fog">
                        Las horas que se le prestan a un colegio o a una empresa, y lo que se les factura a fin de mes.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => setCreando((c) => ! c)}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nuevo
                </button>
            </header>

            {creando ? <NuevoTaller instituciones={instituciones} alCerrar={() => setCreando(false)} /> : null}

            {/* El mes que se está mirando: las horas son de un mes, y el mes
                pasado se revisa tanto como el que corre. */}
            <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div className="inline-flex items-center gap-1">
                    <Link
                        href={`/panel/talleres?periodo=${otroMes(periodo, -1)}`}
                        aria-label="Mes anterior"
                        className="rounded-control border border-line p-1 text-fog transition-colors hover:text-chalk"
                    >
                        <ChevronLeftIcon className="size-4" aria-hidden="true" />
                    </Link>
                    <span className="px-2 text-sm font-medium text-chalk capitalize">{mesLegible}</span>
                    <Link
                        href={`/panel/talleres?periodo=${otroMes(periodo, 1)}`}
                        aria-label="Mes siguiente"
                        className="rounded-control border border-line p-1 text-fog transition-colors hover:text-chalk"
                    >
                        <ChevronRightIcon className="size-4" aria-hidden="true" />
                    </Link>
                </div>
            </div>

            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <Cifra etiqueta="Horas del mes" valor={horas} pie="entre todos los talleres" />
                <Cifra
                    etiqueta="Se facturará"
                    valor={<Reservado ancho="w-24">{pesos.format(delMes)}</Reservado>}
                    pie="con IVA incluido"
                />
                <Cifra
                    etiqueta="Facturado sin pagar"
                    valor={<Reservado ancho="w-24">{pesos.format(porCobrar.total)}</Reservado>}
                    tono={porCobrar.total > 0 ? 'aviso' : 'normal'}
                    siempreTono={porCobrar.total > 0}
                    pie={porCobrar.cuantos === 1 ? '1 factura' : `${porCobrar.cuantos} facturas`}
                />
            </div>

            <Tabla
                columnas={[
                    { titulo: 'Taller', className: 'w-full' },
                    { titulo: 'La hora', className: 'text-right' },
                    { titulo: 'Horas del mes', className: 'text-right' },
                    { titulo: 'Se factura', className: 'text-right' },
                    'Estado',
                ]}
                vacia={talleres.length === 0}
                mensajeVacio="Todavía no hay ningún taller ni arriendo. Créalo con el botón de arriba."
            >
                {talleres.map((t) => (
                    <Fila key={t.uuid} href={`/panel/talleres/${t.uuid}?periodo=${periodo}`}>
                        <Celda>
                            <Link href={`/panel/talleres/${t.uuid}?periodo=${periodo}`} className="font-medium text-chalk hover:underline">
                                {t.nombre}
                            </Link>
                            <span className="apoyo block text-fog">{t.institucion}</span>
                        </Celda>
                        <Celda className="text-right tabular-nums">
                            <Reservado ancho="w-16">{pesos.format(t.precio_hora)}</Reservado>
                        </Celda>
                        <Celda className="text-right tabular-nums text-chalk">{t.horas || '—'}</Celda>
                        <Celda className="text-right tabular-nums text-chalk">
                            <Reservado ancho="w-20">{pesos.format(t.total)}</Reservado>
                        </Celda>
                        <Celda>
                            {! t.activo ? (
                                <span className="apoyo text-fog">Cerrado</span>
                            ) : t.cerrado ? (
                                <span className="apoyo text-ok">Mes cerrado</span>
                            ) : (
                                <span className="apoyo text-fog">Abierto</span>
                            )}
                        </Celda>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
