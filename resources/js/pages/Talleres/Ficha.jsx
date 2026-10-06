import { confirmar } from '@/components/Confirmar';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import {
    ArrowLeftIcon,
    CalendarPlusIcon,
    CheckIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    CopyIcon,
    FileTextIcon,
    LockIcon,
    PrinterIcon,
    PlusIcon,
    TrashIcon,
} from 'lucide-react';

import { Celda, Fila, Tabla } from '@/components/Tabla';
import { Panel, pesos } from '@/components/Tablero';
import { Reservado } from '@/Privado';
import { puede } from '@/lib/permisos';
import { hoyEnChile } from '@/lib/tiempo';
import { useTokenDeEnvio } from '@/lib/tokenDeEnvio';

/**
 * Un taller, mes a mes.
 *
 * EL TRABAJO DE VERDAD ES CONTAR LAS HORAS, y antes se hacía con el calendario
 * de Windows abierto al lado. Aquí el horario propone las clases del mes, se
 * quitan las que no se hicieron y al cerrar queda la cuenta —horas, neto, IVA y
 * total— que es justo lo que hay que escribir en la factura.
 */

const DIAS = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

const CAMPOS_INSTITUCION = [
    ['nombre', 'Nombre'],
    ['rut', 'RUT'],
    ['giro', 'Giro'],
    ['direccion', 'Dirección'],
    ['comuna', 'Comuna y región'],
    ['contacto_nombre', 'Contacto'],
    ['contacto_email', 'Correo del contacto'],
    ['contacto_telefono', 'Teléfono del contacto'],
];

function otroMes(periodo, cuantos) {
    const [anio, mes] = periodo.split('-').map(Number);
    const fecha = new Date(anio, mes - 1 + cuantos, 1);

    return `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}`;
}

/** Un dato suelto, para quien mira sin poder cambiarlo. */
function Dato({ etiqueta, children }) {
    return (
        <div>
            <dt className="rotulo">{etiqueta}</dt>
            <dd className="mt-0.5 text-sm text-chalk">{children || <span className="text-fog">—</span>}</dd>
        </div>
    );
}

/**
 * El taller y su horario, solo para mirar.
 *
 * Quien anota las clases necesita saber qué días tocan, pero el precio de la
 * hora y el horario los cambia quien factura: se enseñan como texto y no como
 * un formulario que el servidor rechazaría al guardar.
 */
function HorarioLeido({ taller }) {
    const dias = DIAS.filter((dia) => (taller.horario?.[dia] ?? []).length > 0);

    return (
        <div className="space-y-3">
            <dl className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <Dato etiqueta="Nombre">{taller.nombre}</Dato>
                <Dato etiqueta="Cómo va en la factura">{taller.descripcion_factura}</Dato>
                <Dato etiqueta="La hora (con IVA)">
                    <Reservado ancho="w-16">{pesos.format(taller.precio_hora)}</Reservado>
                </Dato>
            </dl>

            <div>
                <p className="rotulo mb-1">Horario semanal</p>
                {dias.length === 0 ? (
                    <p className="apoyo text-fog">Sin horario fijo.</p>
                ) : (
                    <ul className="space-y-0.5">
                        {dias.map((dia) => (
                            <li key={dia} className="flex flex-wrap gap-2 text-sm">
                                <span className="w-20 shrink-0 capitalize text-fog">{dia}</span>
                                <span className="tabular-nums text-chalk">
                                    {taller.horario[dia].map((t) => `${t[0]} a ${t[1]}`).join(' · ')}
                                </span>
                            </li>
                        ))}
                    </ul>
                )}
            </div>

            <p className="apoyo border-t border-line pt-3 text-fog">
                {taller.activo ? 'Sigue abierto: se le siguen anotando horas.' : 'Cerrado: queda solo para consultar.'}
            </p>
        </div>
    );
}

/** El folio y las fechas de una factura ya emitida, solo para mirar. */
function DatosDelCobroLeidos({ cobro }) {
    return (
        <dl className="mt-3 grid grid-cols-1 gap-2 border-t border-line pt-3 sm:grid-cols-3">
            <Dato etiqueta="Folio de la factura">{cobro.folio}</Dato>
            <Dato etiqueta="Emitida el">{cobro.emitido_en?.split('-').reverse().join('/')}</Dato>
            <Dato etiqueta="Pagada el">{cobro.pagado_en?.split('-').reverse().join('/')}</Dato>
            {cobro.observaciones ? (
                <div className="sm:col-span-3">
                    <Dato etiqueta="Observaciones">{cobro.observaciones}</Dato>
                </div>
            ) : null}
        </dl>
    );
}

/** Los datos de facturación de la institución, solo para mirar. */
function InstitucionLeida({ institucion }) {
    return (
        <dl className="space-y-2">
            {CAMPOS_INSTITUCION.map(([clave, etiqueta]) => (
                <Dato key={clave} etiqueta={etiqueta}>
                    {institucion[clave]}
                </Dato>
            ))}
        </dl>
    );
}

/** El horario semanal: de aquí salen propuestas las clases de cada mes. */
function Horario({ taller }) {
    const { data, setData, patch, processing, errors } = useForm({
        nombre: taller.nombre,
        descripcion_factura: taller.descripcion_factura ?? '',
        precio_hora: taller.precio_hora,
        activo: taller.activo,
        horario: DIAS.reduce(
            (acc, dia) => ({
                ...acc,
                [dia]: (taller.horario?.[dia] ?? []).map((t) => [t[0], t[1]]),
            }),
            {},
        ),
    });

    function cambiarTramo(dia, i, extremo, valor) {
        const tramos = [...(data.horario[dia] ?? [])];
        tramos[i] = extremo === 0 ? [valor, tramos[i]?.[1] ?? ''] : [tramos[i]?.[0] ?? '', valor];
        setData('horario', { ...data.horario, [dia]: tramos });
    }

    const campo =
        'rounded-control border border-line bg-surface-2 px-2 py-1 text-sm text-chalk tabular-nums focus:border-line-strong focus:outline-none';

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                patch(`/panel/talleres/${taller.uuidRuta}`, { preserveScroll: true });
            }}
            className="space-y-3"
        >
            <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <label className="block">
                    <span className="rotulo">Nombre</span>
                    <input
                        type="text"
                        value={data.nombre}
                        onChange={(e) => setData('nombre', e.target.value)}
                        placeholder="Clases grupales del colegio"
                        className={`${campo} mt-1 w-full`}
                    />
                </label>
                <label className="block">
                    <span className="rotulo">Cómo va en la factura</span>
                    <input
                        type="text"
                        value={data.descripcion_factura}
                        onChange={(e) => setData('descripcion_factura', e.target.value)}
                        placeholder="Uso instalaciones para clase grupal"
                        className={`${campo} mt-1 w-full`}
                    />
                </label>
                <label className="block sm:max-w-40">
                    <span className="rotulo">La hora (con IVA)</span>
                    <input
                        type="number"
                        min="1"
                        value={data.precio_hora}
                        onChange={(e) => setData('precio_hora', e.target.value)}
                        className={`${campo} mt-1 w-full`}
                    />
                </label>
            </div>

            <div>
                <p className="rotulo mb-1">Horario semanal</p>
                <ul className="space-y-1">
                    {DIAS.map((dia) => {
                        const tramos = data.horario[dia] ?? [];

                        return (
                            <li key={dia} className="flex flex-wrap items-center gap-2">
                                <span className="w-20 shrink-0 text-sm capitalize text-fog">{dia}</span>

                                {[...tramos, ['', '']].map((tramo, i) => (
                                    <span key={i} className="inline-flex items-center gap-1">
                                        <input
                                            type="time"
                                            value={tramo[0] ?? ''}
                                            onChange={(e) => cambiarTramo(dia, i, 0, e.target.value)}
                                            className={campo}
                                        />
                                        <span className="apoyo text-fog">a</span>
                                        <input
                                            type="time"
                                            value={tramo[1] ?? ''}
                                            onChange={(e) => cambiarTramo(dia, i, 1, e.target.value)}
                                            className={campo}
                                        />
                                    </span>
                                ))}
                            </li>
                        );
                    })}
                </ul>
                <p className="apoyo mt-1 text-fog">
                    El último par vacío de cada día sirve para añadir otro tramo. Los que se dejen en blanco no se
                    guardan.
                </p>
            </div>

            {/* Cerrarlo no es borrarlo: el colegio que dejó de venir se
                apaga y sus cobros siguen en las cuentas. Se apaga aquí porque
                es donde está lo demás del taller. */}
            <label className="flex items-center gap-2 border-t border-line pt-3 text-sm text-chalk">
                <input
                    type="checkbox"
                    checked={data.activo}
                    onChange={(e) => setData('activo', e.target.checked)}
                    className="size-4 accent-[var(--color-volt)]"
                />
                Sigue abierto
                <span className="apoyo text-fog">
                    {data.activo ? 'se le siguen anotando horas' : 'cerrado: queda solo para consultar'}
                </span>
            </label>

            <Errores errores={errors} />

            <button
                type="submit"
                disabled={processing}
                className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
            >
                Guardar el horario
            </button>
        </form>
    );
}

/** Lo que hay que escribir en la factura, para copiarlo tal cual. */
function ParaLaFactura({ taller, cuenta, periodo, mesLegible }) {
    const [copiado, setCopiado] = useState(false);

    const linea = `${(taller.descripcion_factura || taller.nombre).toUpperCase()}, ${cuenta.horas} HRS, MES DE ${mesLegible.toUpperCase()}`;

    function copiar() {
        navigator.clipboard?.writeText(linea).then(
            () => {
                setCopiado(true);
                setTimeout(() => setCopiado(false), 2000);
            },
            () => {},
        );
    }

    return (
        <div className="rounded-panel border border-line bg-surface-2/40 p-3">
            <div className="mb-2 flex items-start justify-between gap-3">
                <p className="apoyo text-fog">Para escribir en la factura</p>
                <button
                    type="button"
                    onClick={copiar}
                    className="apoyo inline-flex shrink-0 items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    {copiado ? <CheckIcon className="size-3.5 text-ok" aria-hidden="true" /> : <CopyIcon className="size-3.5" aria-hidden="true" />}
                    {copiado ? 'Copiado' : 'Copiar'}
                </button>
            </div>

            <p className="text-sm text-chalk">{linea}</p>

            <dl className="mt-3 space-y-1 border-t border-line pt-2">
                <div className="flex justify-between gap-3 text-sm">
                    <dt className="text-fog">Cantidad</dt>
                    <dd className="tabular-nums text-chalk">{cuenta.horas} hrs</dd>
                </div>
                <div className="flex justify-between gap-3 text-sm">
                    <dt className="text-fog">Precio unitario neto</dt>
                    <dd className="tabular-nums text-chalk">
                        <Reservado ancho="w-20">
                            {cuenta.horas > 0 ? pesos.format(Math.round(cuenta.neto / cuenta.horas)) : pesos.format(0)}
                        </Reservado>
                    </dd>
                </div>
                <div className="flex justify-between gap-3 text-sm">
                    <dt className="text-fog">Monto neto</dt>
                    <dd className="tabular-nums text-chalk">
                        <Reservado ancho="w-20">{pesos.format(cuenta.neto)}</Reservado>
                    </dd>
                </div>
                <div className="flex justify-between gap-3 text-sm">
                    <dt className="text-fog">IVA 19%</dt>
                    <dd className="tabular-nums text-chalk">
                        <Reservado ancho="w-20">{pesos.format(cuenta.iva)}</Reservado>
                    </dd>
                </div>
                <div className="flex justify-between gap-3 border-t border-line pt-1 text-sm font-semibold">
                    <dt className="text-chalk">Total</dt>
                    <dd className="tabular-nums text-chalk">
                        <Reservado ancho="w-20">{pesos.format(cuenta.total)}</Reservado>
                    </dd>
                </div>
            </dl>
        </div>
    );
}

/**
 * Lo que el servidor rechazó. Estos formularios no mostraban nada: se apretaba
 * «Guardar», no se guardaba y no decía por qué.
 */
function Errores({ errores }) {
    const lista = Object.values(errores ?? {});

    if (lista.length === 0) {
        return null;
    }

    return (
        <ul className="apoyo space-y-0.5 text-danger" role="alert">
            {lista.map((e) => (
                <li key={e}>{e}</li>
            ))}
        </ul>
    );
}

/** El folio y las fechas de una factura ya emitida. */
function DatosDelCobro({ cobro }) {
    const { data, setData, patch, processing, errors } = useForm({
        folio: cobro.folio ?? '',
        emitido_en: cobro.emitido_en ?? '',
        pagado_en: cobro.pagado_en ?? '',
        observaciones: cobro.observaciones ?? '',
    });

    const campo =
        'w-full rounded-control border border-line bg-surface-2 px-2 py-1 text-sm text-chalk tabular-nums focus:border-line-strong focus:outline-none';

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                patch(`/panel/talleres/cobros/${cobro.uuid}`, { preserveScroll: true });
            }}
            className="mt-3 space-y-2 border-t border-line pt-3"
        >
            <div className="grid grid-cols-1 gap-2 sm:grid-cols-3">
                <label className="block">
                    <span className="rotulo">Folio de la factura</span>
                    <input type="text" value={data.folio} onChange={(e) => setData('folio', e.target.value)} className={`${campo} mt-1`} />
                </label>
                <label className="block">
                    <span className="rotulo">Emitida el</span>
                    <input type="date" value={data.emitido_en} onChange={(e) => setData('emitido_en', e.target.value)} className={`${campo} mt-1`} />
                </label>
                <label className="block">
                    <span className="rotulo">Pagada el</span>
                    {/* Hasta hoy: un pago con fecha de mañana no entra a la caja de hoy. */}
                    <input type="date" max={hoyEnChile()} value={data.pagado_en} onChange={(e) => setData('pagado_en', e.target.value)} className={`${campo} mt-1`} />
                </label>
            </div>

            <label className="block">
                <span className="rotulo">Observaciones</span>
                <textarea
                    rows={2}
                    value={data.observaciones}
                    onChange={(e) => setData('observaciones', e.target.value)}
                    className={`${campo} mt-1`}
                />
            </label>

            <Errores errores={errors} />

            <button
                type="submit"
                disabled={processing}
                className="apoyo rounded-control border border-line px-2.5 py-1 text-fog transition-colors hover:text-chalk disabled:opacity-50"
            >
                Guardar
            </button>
        </form>
    );
}

/**
 * Los datos con los que se le cotiza y se le factura a la institución.
 *
 * NO LOS PIDE EL ALTA A PROPÓSITO: cuando se crea el taller lo que importa es
 * empezar a contar horas, no rellenar una ficha. Pero el papel que se le manda
 * al colegio lleva giro, dirección y a quién escribirle, así que se corrigen
 * aquí, que es donde se miran.
 */
function DatosDeLaInstitucion({ institucion }) {
    const { data, setData, patch, processing, errors } = useForm({
        nombre: institucion.nombre ?? '',
        rut: institucion.rut ?? '',
        giro: institucion.giro ?? '',
        direccion: institucion.direccion ?? '',
        comuna: institucion.comuna ?? '',
        contacto_nombre: institucion.contacto_nombre ?? '',
        contacto_email: institucion.contacto_email ?? '',
        contacto_telefono: institucion.contacto_telefono ?? '',
    });

    const campo =
        'w-full rounded-control border border-line bg-surface-2 px-2 py-1 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none';

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                patch(`/panel/talleres/instituciones/${institucion.uuid}`, { preserveScroll: true });
            }}
            className="space-y-2"
        >
            {CAMPOS_INSTITUCION.map(([clave, etiqueta]) => (
                <label key={clave} className="block">
                    <span className="rotulo">{etiqueta}</span>
                    <input
                        type="text"
                        value={data[clave]}
                        onChange={(e) => setData(clave, e.target.value)}
                        className={`${campo} mt-0.5`}
                    />
                </label>
            ))}

            <Errores errores={errors} />

            <button
                type="submit"
                disabled={processing}
                className="apoyo rounded-control border border-line px-2.5 py-1 text-fog transition-colors hover:text-chalk disabled:opacity-50"
            >
                Guardar
            </button>
        </form>
    );
}

/**
 * Las cotizaciones del taller.
 *
 * ES EL PAPEL CON EL QUE EMPIEZA EL MES: el colegio pregunta cuánto sale abril
 * y hay que mandarle las horas y el total. Se hacía copiando el Word del mes
 * anterior y cambiándole a mano el número, las fechas y la cifra. Desde aquí se
 * cotiza el mes que se está mirando, con las clases del horario ya puestas.
 */
function Cotizaciones({ uuid, periodo, mesLegible, cotizaciones, puedeCotizar, puedeBorrar }) {
    // El doble clic creaba dos cotizaciones del mismo mes: mientras va el
    // pedido, el botón no hace nada.
    const [cotizando, setCotizando] = useState(false);

    return (
        <Panel
            titulo="Cotizaciones"
            descripcion="Lo que se le manda al colegio antes del mes. Se corrige cuando se suspende una semana."
            enlace={
                puedeCotizar ? (
                    <button
                        type="button"
                        disabled={cotizando}
                        onClick={() =>
                            router.post(
                                `/panel/talleres/${uuid}/cotizaciones`,
                                { periodo },
                                { onStart: () => setCotizando(true), onFinish: () => setCotizando(false) },
                            )
                        }
                        className="apoyo inline-flex shrink-0 items-center gap-1 text-fog transition-colors hover:text-chalk disabled:opacity-50"
                    >
                        <FileTextIcon className="size-3.5" aria-hidden="true" />
                        Cotizar {mesLegible}
                    </button>
                ) : null
            }
        >
            <Tabla
                columnas={[
                    { titulo: 'N°', className: 'whitespace-nowrap' },
                    { titulo: 'Mes', className: 'w-full' },
                    { titulo: 'Horas', className: 'text-right' },
                    { titulo: 'Total', className: 'text-right' },
                    'Estado',
                    { titulo: '', className: 'text-right' },
                ]}
                vacia={cotizaciones.length === 0}
                mensajeVacio={
                    puedeCotizar
                        ? 'Ninguna cotización todavía. «Cotizar» trae las clases del horario y hace la cuenta.'
                        : 'Ninguna cotización todavía.'
                }
            >
                {cotizaciones.map((c) => (
                    <Fila key={c.uuid} href={`/panel/talleres/cotizaciones/${c.uuid}`}>
                        <Celda className="whitespace-nowrap tabular-nums text-chalk">{c.numero}</Celda>
                        <Celda>
                            <Link href={`/panel/talleres/cotizaciones/${c.uuid}`} className="capitalize text-chalk hover:underline">
                                {c.mes ?? 'Sin mes'}
                            </Link>
                            <span className="apoyo block text-fog">escrita el {c.fecha}</span>
                        </Celda>
                        <Celda className="text-right tabular-nums text-chalk">{c.horas}</Celda>
                        <Celda className="text-right tabular-nums text-chalk">
                            <Reservado ancho="w-20">{pesos.format(c.total)}</Reservado>
                        </Celda>
                        <Celda>
                            <span className={`apoyo ${c.vencida ? 'text-warn' : 'text-fog'}`}>
                                {c.vencida ? 'Vencida' : c.estado}
                            </span>
                        </Celda>
                        <Celda className="text-right">
                            <span className="inline-flex items-center gap-1">
                                <a
                                    href={`/panel/talleres/cotizaciones/${c.uuid}/imprimir`}
                                    target="_blank"
                                    rel="noopener"
                                    aria-label={`Imprimir la cotización N° ${c.numero}`}
                                    onClick={(e) => e.stopPropagation()}
                                    className="inline-flex rounded-control p-1 text-fog transition-colors hover:text-chalk"
                                >
                                    <PrinterIcon className="size-3.5" aria-hidden="true" />
                                </a>
                                {/* A la papelera desde la lista: para tirar un
                                    borrador no hace falta entrar en él. */}
                                {puedeBorrar ? (
                                    <button
                                        type="button"
                                        onClick={async (e) => {
                                            e.stopPropagation();

                                            if (await confirmar({
                                                titulo: `¿Mandar la cotización N° ${c.numero} a la papelera?`,
                                                mensaje: 'Se recupera desde Configuración → Papelera.',
                                                confirmar: 'Mandar a la papelera',
                                                peligrosa: true,
                                            })) {
                                                router.delete(`/panel/talleres/cotizaciones/${c.uuid}`, { preserveScroll: true });
                                            }
                                        }}
                                        aria-label={`Eliminar la cotización N° ${c.numero}`}
                                        className="rounded-control p-1 text-fog transition-colors hover:text-danger"
                                    >
                                        <TrashIcon className="size-3.5" aria-hidden="true" />
                                    </button>
                                ) : null}
                            </span>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>
        </Panel>
    );
}

export default function Ficha({ taller, periodo, mesLegible, horas, propuestas, cuenta, cobro, historial, cotizaciones }) {
    const uuid = window.location.pathname.split('/').pop().split('?')[0];
    const [anotando, setAnotando] = useState(false);

    const nueva = useForm({ fecha: '', horas: '1', detalle: '' });
    // Uno por cada vez que se abre «Una suelta»: el doble clic anotaba la
    // misma clase dos veces y la factura salía con una hora de más.
    const token = useTokenDeEnvio();

    const irA = (p) => `/panel/talleres/${uuid}?periodo=${p}`;

    /*
     * Tres permisos, como en el servidor: anotar clases y cotizar es del
     * mesón (crear); el horario, el precio, cerrar el mes y la factura son de
     * quien cobra (editar); quitar una clase, reabrir y tirar, de quien puede
     * borrar. A quien no puede no se le pinta el botón: chocaba con un 403.
     */
    const { auth } = usePage().props;
    const puedeAnotar = puede(auth, 'pagos.crear');
    const puedeEditar = puede(auth, 'pagos.editar');
    const puedeBorrar = puede(auth, 'pagos.eliminar');

    return (
        <>
            <Head title={taller.nombre} />

            <header className="mb-4">
                <Link href="/panel/talleres" className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk">
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Talleres y arriendos
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">{taller.nombre}</h1>
                <p className="apoyo text-fog">
                    {taller.institucion?.nombre}
                    {taller.institucion?.rut ? ` · ${taller.institucion.rut}` : ''} ·{' '}
                    <Reservado ancho="w-16">{pesos.format(taller.precio_hora)}</Reservado> la hora
                </p>
            </header>

            <div className="mb-3 inline-flex items-center gap-1">
                <Link href={irA(otroMes(periodo, -1))} aria-label="Mes anterior" className="rounded-control border border-line p-1 text-fog transition-colors hover:text-chalk">
                    <ChevronLeftIcon className="size-4" aria-hidden="true" />
                </Link>
                <span className="px-2 text-sm font-medium capitalize text-chalk">{mesLegible}</span>
                <Link href={irA(otroMes(periodo, 1))} aria-label="Mes siguiente" className="rounded-control border border-line p-1 text-fog transition-colors hover:text-chalk">
                    <ChevronRightIcon className="size-4" aria-hidden="true" />
                </Link>

                {cobro ? (
                    <span className="apoyo ml-2 inline-flex items-center gap-1 text-ok">
                        <LockIcon className="size-3.5" aria-hidden="true" />
                        Mes cerrado
                    </span>
                ) : null}
            </div>

            <div className="grid grid-cols-1 items-start gap-4 xl:grid-cols-[1fr_22rem]">
                <div className="min-w-0 space-y-3">
                    <Panel
                        titulo="Clases del mes"
                        descripcion={
                            cobro
                                ? 'El mes está cerrado: para corregir algo hay que reabrirlo.'
                                : puedeBorrar
                                  ? 'Quita las que no se hicieron: lo que no se hizo no se cobra.'
                                  : 'Anota solo las que se hicieron: lo que no se hizo no se cobra.'
                        }
                        enlace={
                            cobro || ! puedeAnotar ? null : (
                                <span className="flex shrink-0 items-center gap-3">
                                    {propuestas.length > 0 ? (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.post(`/panel/talleres/${uuid}/horas/del-mes`, { periodo }, { preserveScroll: true })
                                            }
                                            className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                                        >
                                            <CalendarPlusIcon className="size-3.5" aria-hidden="true" />
                                            Anotar las {propuestas.length} del horario
                                        </button>
                                    ) : null}
                                    <button
                                        type="button"
                                        onClick={() => {
                                            if (! anotando) {
                                                token.renovar();
                                            }
                                            setAnotando((a) => ! a);
                                        }}
                                        className="apoyo text-fog transition-colors hover:text-chalk"
                                    >
                                        {anotando ? 'Cerrar' : '+ Una suelta'}
                                    </button>
                                </span>
                            )
                        }
                    >
                        {puedeAnotar && anotando ? (
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    nueva.transform((d) => ({ ...d, form_submit_token: token.actual() }));
                                    nueva.post(`/panel/talleres/${uuid}/horas`, {
                                        preserveScroll: true,
                                        onSuccess: () => {
                                            nueva.reset();
                                            token.renovar();
                                        },
                                    });
                                }}
                                className="mb-3 flex flex-wrap items-end gap-2 rounded-panel border border-line bg-surface-2 p-3"
                            >
                                <label className="block">
                                    <span className="rotulo">Día</span>
                                    <input
                                        type="date"
                                        value={nueva.data.fecha}
                                        onChange={(e) => nueva.setData('fecha', e.target.value)}
                                        className="mt-1 rounded-control border border-line bg-surface px-2 py-1 text-sm tabular-nums text-chalk focus:outline-none"
                                    />
                                </label>
                                <label className="block">
                                    <span className="rotulo">Horas</span>
                                    <input
                                        type="number"
                                        step="0.25"
                                        min="0.25"
                                        value={nueva.data.horas}
                                        onChange={(e) => nueva.setData('horas', e.target.value)}
                                        className="mt-1 w-20 rounded-control border border-line bg-surface px-2 py-1 text-sm tabular-nums text-chalk focus:outline-none"
                                    />
                                </label>
                                <label className="block min-w-40 flex-1">
                                    <span className="rotulo">Detalle</span>
                                    <input
                                        type="text"
                                        value={nueva.data.detalle}
                                        onChange={(e) => nueva.setData('detalle', e.target.value)}
                                        placeholder="15:00 a 16:00"
                                        className="mt-1 w-full rounded-control border border-line bg-surface px-2 py-1 text-sm text-chalk placeholder:text-fog focus:outline-none"
                                    />
                                </label>
                                <button
                                    type="submit"
                                    disabled={nueva.processing}
                                    className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt disabled:opacity-50"
                                >
                                    <PlusIcon className="size-4" aria-hidden="true" />
                                </button>

                                {/* Sin esto el «+» no hacía nada y no decía por qué:
                                    sin día, menos de un cuarto de hora, mes cerrado. */}
                                <div className="w-full">
                                    <Errores errores={nueva.errors} />
                                </div>
                            </form>
                        ) : null}

                        <Tabla
                            columnas={[
                                { titulo: 'Día', className: 'whitespace-nowrap' },
                                { titulo: 'Detalle', className: 'w-full' },
                                { titulo: 'Horas', className: 'text-right' },
                                { titulo: '', className: 'text-right' },
                            ]}
                            vacia={horas.length === 0}
                            mensajeVacio={
                                propuestas.length > 0 && puedeAnotar
                                    ? 'Ninguna clase anotada. Usa «Anotar las del horario» para ponerlas todas de una vez.'
                                    : 'Ninguna clase anotada este mes, y el horario no propone ninguna.'
                            }
                        >
                            {horas.map((h) => (
                                <Fila key={h.uuid}>
                                    <Celda className="whitespace-nowrap tabular-nums text-chalk">
                                        {h.fecha}
                                        <span className="apoyo ml-1 text-fog">{h.dia}</span>
                                    </Celda>
                                    <Celda>{h.detalle ?? '—'}</Celda>
                                    <Celda className="text-right tabular-nums text-chalk">{h.horas}</Celda>
                                    <Celda className="text-right">
                                        {h.cobrada ? (
                                            <LockIcon className="inline size-3.5 text-fog" aria-label="Ya cobrada" />
                                        ) : ! puedeBorrar ? null : (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    router.delete(`/panel/talleres/horas/${h.uuid}`, { preserveScroll: true })
                                                }
                                                aria-label="Quitar esta clase"
                                                className="rounded-control p-1 text-fog transition-colors hover:text-danger"
                                            >
                                                <TrashIcon className="size-3.5" aria-hidden="true" />
                                            </button>
                                        )}
                                    </Celda>
                                </Fila>
                            ))}
                        </Tabla>
                    </Panel>

                    <Cotizaciones
                        uuid={uuid}
                        periodo={periodo}
                        mesLegible={mesLegible}
                        cotizaciones={cotizaciones}
                        puedeCotizar={puedeAnotar}
                        puedeBorrar={puedeBorrar}
                    />

                    <Panel
                        titulo="El taller y su horario"
                        descripcion="Del horario salen propuestas las clases de cada mes. Cambiarlo no toca lo ya anotado."
                    >
                        {puedeEditar ? <Horario taller={{ ...taller, uuidRuta: uuid }} /> : <HorarioLeido taller={taller} />}
                    </Panel>

                    {historial.length > 0 ? (
                        <Panel titulo="Meses cobrados" descripcion="Lo que se facturó, con su folio y si está pagado.">
                            <Tabla
                                columnas={[
                                    { titulo: 'Mes', className: 'w-full' },
                                    { titulo: 'Horas', className: 'text-right' },
                                    { titulo: 'Total', className: 'text-right' },
                                    'Folio',
                                    'Pagada',
                                ]}
                                vacia={false}
                            >
                                {historial.map((c) => (
                                    <Fila key={c.uuid}>
                                        <Celda className="capitalize text-chalk">{c.mes}</Celda>
                                        <Celda className="text-right tabular-nums">{c.horas}</Celda>
                                        <Celda className="text-right tabular-nums text-chalk">
                                            <Reservado ancho="w-20">{pesos.format(c.total)}</Reservado>
                                        </Celda>
                                        <Celda className="tabular-nums">{c.folio ?? '—'}</Celda>
                                        <Celda>
                                            {c.pagado_en ? (
                                                <span className="apoyo tabular-nums text-ok">{c.pagado_en}</span>
                                            ) : ! puedeEditar ? (
                                                <span className="apoyo text-warn">Sin pagar</span>
                                            ) : (
                                                /* Un clic para anotar que la pagaron: es lo
                                                   único que se hace con un cobro viejo, y
                                                   antes había que abrir su formulario. */
                                                <button
                                                    type="button"
                                                    onClick={async () => {
                                                        if (await confirmar({
                                                            titulo: '¿Marcar la factura como pagada hoy?',
                                                            mensaje: 'Entra a la caja con la fecha de hoy.',
                                                            confirmar: 'Marcar pagada',
                                                        })) {
                                                            router.patch(
                                                                `/panel/talleres/cobros/${c.uuid}`,
                                                                // Hoy EN CHILE: toISOString() da la fecha de
                                                                // Greenwich, y desde las 21:00 anotaba el pago
                                                                // mañana, que la caja no cuenta hasta mañana.
                                                                { pagado_en: hoyEnChile() },
                                                                { preserveScroll: true },
                                                            );
                                                        }
                                                    }}
                                                    className="apoyo inline-flex items-center gap-1 text-warn transition-colors hover:text-ok"
                                                >
                                                    <CheckIcon className="size-3.5" aria-hidden="true" />
                                                    Marcar pagada
                                                </button>
                                            )}
                                        </Celda>
                                    </Fila>
                                ))}
                            </Tabla>
                        </Panel>
                    ) : null}
                </div>

                <div className="space-y-3 xl:sticky xl:top-4">
                    <Panel
                        titulo={cobro ? 'Cobro del mes' : 'Lo que se cobrará'}
                        descripcion={
                            cobro
                                ? 'Ya está cerrado: esta es la cuenta con la que se emitió.'
                                : 'La cuenta se cierra cuando el mes termina y ya no va a cambiar.'
                        }
                    >
                        <ParaLaFactura
                            taller={taller}
                            cuenta={cobro ? { horas: cobro.horas, neto: cobro.neto, iva: cobro.iva, total: cobro.total } : cuenta}
                            periodo={periodo}
                            mesLegible={mesLegible}
                        />

                        {cobro ? (
                            <>
                                {/* La clave lleva la fecha de pago: «Marcar pagada»
                                    en el historial cambia el cobro por fuera, y el
                                    formulario se quedaba con la fecha vacía de antes;
                                    al guardar el folio borraba el pago. */}
                                {puedeEditar ? (
                                    <DatosDelCobro key={`${cobro.uuid}-${cobro.pagado_en ?? ''}`} cobro={cobro} />
                                ) : (
                                    <DatosDelCobroLeidos cobro={cobro} />
                                )}

                                {/* Reabrir hace falta: se cierra julio y aparece
                                    una clase que no estaba anotada. Pero no si ya
                                    lo pagaron: reabrir borra el cobro y con él el
                                    ingreso de la caja. Primero se quita la fecha
                                    de pago, a sabiendas. */}
                                {! puedeBorrar ? null : cobro.pagado_en ? (
                                    <p className="apoyo mt-3 text-fog">
                                        Ya está pagado. Para reabrir el mes, quita primero la fecha de pago.
                                    </p>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={async () => {
                                            if (await confirmar({
                                                titulo: '¿Reabrir el mes?',
                                                mensaje: 'Se borra la cuenta cerrada, con su folio y las fechas de la factura, y se vuelven a poder anotar horas.',
                                                confirmar: 'Reabrir el mes',
                                                peligrosa: true,
                                            })) {
                                                router.delete(`/panel/talleres/cobros/${cobro.uuid}`, { preserveScroll: true });
                                            }
                                        }}
                                        className="apoyo mt-3 text-fog transition-colors hover:text-danger"
                                    >
                                        Reabrir el mes
                                    </button>
                                )}
                            </>
                        ) : ! puedeEditar ? (
                            <p className="apoyo mt-3 text-fog">El mes lo cierra quien emite la factura.</p>
                        ) : (
                            <button
                                type="button"
                                disabled={cuenta.horas === 0}
                                onClick={async () => {
                                    if (await confirmar({
                                        titulo: '¿Cerrar el mes?',
                                        mensaje: `Queda la cuenta con ${cuenta.horas} ${cuenta.horas === 1 ? 'hora' : 'horas'} y el precio de hoy. Si falta una clase, se puede reabrir.`,
                                        confirmar: 'Cerrar el mes',
                                    })) {
                                        router.post(`/panel/talleres/${uuid}/cerrar`, { periodo }, { preserveScroll: true });
                                    }
                                }}
                                className="mt-3 w-full rounded-control bg-volt px-3 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                            >
                                Cerrar el mes y dejar la cuenta
                            </button>
                        )}
                    </Panel>

                    {puedeBorrar ? (
                        <button
                            type="button"
                            onClick={async () => {
                                if (await confirmar({
                                    titulo: `¿Mandar «${taller.nombre}» a la papelera?`,
                                    mensaje: 'Sus horas y cobros se van con él. Se recupera desde Configuración → Papelera.',
                                    confirmar: 'Mandar a la papelera',
                                    peligrosa: true,
                                })) {
                                    router.delete(`/panel/talleres/${uuid}`);
                                }
                            }}
                            className="apoyo text-fog transition-colors hover:text-danger"
                        >
                            Eliminar este taller
                        </button>
                    ) : null}

                    {taller.institucion ? (
                        <Panel
                            titulo="A quién se le factura"
                            descripcion="Lo que sale en la cotización y en la factura."
                        >
                            {puedeEditar ? (
                                <DatosDeLaInstitucion institucion={taller.institucion} />
                            ) : (
                                <InstitucionLeida institucion={taller.institucion} />
                            )}
                        </Panel>
                    ) : null}
                </div>
            </div>
        </>
    );
}
