import { confirmar } from '@/components/Confirmar';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { ChevronLeftIcon, ChevronRightIcon, PencilIcon, PlusIcon, TrashIcon } from 'lucide-react';

import { Campo, Texto } from '@/components/Campo';
import { Celda, Fila, Tabla } from '@/components/Tabla';
import { formatearRut } from '@/lib/socio';
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

/**
 * Un taller nuevo: a quién se le factura, qué es y cuánto vale la hora.
 *
 * Cada error va junto a su campo (antes se juntaban abajo y el del RUT no se
 * mostraba nunca), el RUT se escribe con puntos y guion solo, y la hora con
 * $ y puntos de miles.
 */
function NuevoTaller({ instituciones, alCerrar }) {
    const { data, setData, post, processing, errors } = useForm({
        institucion_uuid: '',
        institucion_nombre: '',
        institucion_rut: '',
        nombre: '',
        descripcion_factura: '',
        precio_hora: '',
    });

    const nueva = data.institucion_uuid === '';

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                post('/panel/talleres', { onSuccess: alCerrar });
            }}
            className="mb-4 space-y-4 rounded-panel border border-line bg-surface p-4"
        >
            <h2 className="text-sm font-semibold text-chalk">Nuevo taller o arriendo</h2>

            <div className="grid gap-3 sm:grid-cols-2">
                {/* Se elige una institución que ya esté o se escribe una nueva:
                    «crear institución» y después «crear taller» eran dos
                    pantallas para una sola cosa. */}
                <Campo etiqueta="A quién se le factura" nombre="institucion_uuid" error={errors.institucion_uuid}>
                    <select
                        id="institucion_uuid"
                        value={data.institucion_uuid}
                        onChange={(e) => setData('institucion_uuid', e.target.value)}
                        className="w-full rounded-control border border-line bg-surface px-2.5 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                    >
                        <option value="">Una nueva…</option>
                        {instituciones.map((i) => (
                            <option key={i.uuid} value={i.uuid}>
                                {i.nombre}
                                {i.rut ? ` · ${i.rut}` : ''}
                            </option>
                        ))}
                    </select>
                </Campo>

                {nueva ? (
                    <div className="grid grid-cols-[1fr_10rem] gap-2">
                        <Campo etiqueta="Nombre" nombre="institucion_nombre" error={errors.institucion_nombre} requerido>
                            <Texto
                                nombre="institucion_nombre"
                                valor={data.institucion_nombre}
                                alCambiar={(v) => setData('institucion_nombre', v)}
                                error={errors.institucion_nombre}
                                placeholder="Corporación Educacional…"
                                autoFocus
                            />
                        </Campo>
                        <Campo etiqueta="RUT" nombre="institucion_rut" error={errors.institucion_rut}>
                            <Texto
                                nombre="institucion_rut"
                                valor={data.institucion_rut}
                                alCambiar={(v) => setData('institucion_rut', formatearRut(v))}
                                error={errors.institucion_rut}
                                placeholder="65.154.436-K"
                            />
                        </Campo>
                    </div>
                ) : null}
            </div>

            <div className="grid gap-3 sm:grid-cols-[1fr_1fr_11rem]">
                <Campo etiqueta="Qué es" nombre="nombre" error={errors.nombre} requerido>
                    <Texto nombre="nombre" valor={data.nombre} alCambiar={(v) => setData('nombre', v)} error={errors.nombre} placeholder="Clases grupales del colegio" />
                </Campo>
                <Campo etiqueta="Cómo va en la factura" nombre="descripcion_factura" error={errors.descripcion_factura}>
                    <Texto
                        nombre="descripcion_factura"
                        valor={data.descripcion_factura}
                        alCambiar={(v) => setData('descripcion_factura', v)}
                        error={errors.descripcion_factura}
                        placeholder="Uso instalaciones para clase grupal"
                    />
                </Campo>
                <Campo etiqueta="La hora, con IVA" nombre="precio_hora" error={errors.precio_hora} requerido>
                    <div className="flex">
                        <span className="flex items-center rounded-l-control border border-r-0 border-line bg-surface-2 px-2.5 text-sm text-fog">$</span>
                        <input
                            id="precio_hora"
                            inputMode="numeric"
                            value={data.precio_hora === '' ? '' : Number(data.precio_hora).toLocaleString('es-CL')}
                            onChange={(e) => {
                                const digitos = e.target.value.replace(/\D/g, '');
                                setData('precio_hora', digitos === '' ? '' : Number(digitos));
                            }}
                            placeholder="30.000"
                            aria-invalid={errors.precio_hora ? 'true' : undefined}
                            className={`w-full min-w-0 rounded-r-control border bg-surface px-2.5 py-1.5 text-sm tabular-nums text-chalk placeholder:text-fog focus:outline-none ${
                                errors.precio_hora ? 'border-danger' : 'border-line focus:border-line-strong'
                            }`}
                        />
                    </div>
                </Campo>
            </div>

            <div className="flex items-center gap-3">
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                >
                    {processing ? 'Creando…' : 'Crear'}
                </button>
                <button type="button" onClick={alCerrar} className="text-sm text-fog transition-colors hover:text-chalk">
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
                    { titulo: '', className: 'text-right' },
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
                                <span className="apoyo whitespace-nowrap text-ok">Mes cerrado</span>
                            ) : (
                                <span className="apoyo text-fog">Abierto</span>
                            )}
                        </Celda>
                        {/* Abrir y tirar desde la lista: entrar en la ficha
                            para darse cuenta de que ahí estaba el botón es un
                            paso que nadie adivina. */}
                        <Celda className="text-right">
                            <span className="inline-flex items-center gap-1">
                                <Link
                                    href={`/panel/talleres/${t.uuid}?periodo=${periodo}`}
                                    aria-label={`Abrir ${t.nombre}`}
                                    className="inline-flex rounded-control p-1 text-fog transition-colors hover:text-chalk"
                                >
                                    <PencilIcon className="size-3.5" aria-hidden="true" />
                                </Link>
                                <button
                                    type="button"
                                    onClick={async (e) => {
                                        e.stopPropagation();

                                        if (await confirmar({
                                            titulo: `¿Mandar «${t.nombre}» a la papelera?`,
                                            mensaje: 'Sus horas y cobros se van con él y se recuperan desde ahí.',
                                            confirmar: 'Mandar a la papelera',
                                            peligrosa: true,
                                        })) {
                                            router.delete(`/panel/talleres/${t.uuid}`, { preserveScroll: true });
                                        }
                                    }}
                                    aria-label={`Eliminar ${t.nombre}`}
                                    className="rounded-control p-1 text-fog transition-colors hover:text-danger"
                                >
                                    <TrashIcon className="size-3.5" aria-hidden="true" />
                                </button>
                            </span>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
