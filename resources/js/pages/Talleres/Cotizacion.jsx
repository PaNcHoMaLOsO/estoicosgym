import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, CalendarPlusIcon, PlusIcon, PrinterIcon, TrashIcon } from 'lucide-react';

import { Celda, Fila, Tabla } from '@/components/Tabla';
import { Panel, pesos } from '@/components/Tablero';
import { Reservado } from '@/Privado';

/**
 * Una cotización de taller.
 *
 * ES EL PAPEL QUE SE LE MANDA AL COLEGIO antes del mes: «abril son 36 horas,
 * $1.080.000». Se hacía copiando el Word del mes anterior y cambiando a mano el
 * número, las fechas y la cifra, después de contar las clases con el calendario
 * de Windows al lado.
 *
 * LO IMPORTANTE ES QUE SE PUEDE CORREGIR, que es lo que de verdad pasa: el
 * colegio suspende una semana, cae un feriado. Se destildan esas clases, la
 * cuenta se rehace sola y se vuelve a imprimir. La cifra de arriba cambia
 * mientras se tilda, sin guardar: así se le puede decir por teléfono «sin esa
 * semana te quedan 32 horas» sin tocar nada todavía.
 */

const IVA = 0.19;

/** La misma cuenta que hace el servidor, para verla mientras se tilda. */
function cuentaDe(lineas, precioHora) {
    const horas = Math.round(lineas.reduce((suma, l) => suma + (l.incluida ? Number(l.horas) || 0 : 0), 0) * 100) / 100;
    // Se parte del total con IVA dentro, como el cobro: al revés el total se
    // iría un peso por el redondeo y el papel dejaría de cuadrar.
    const total = Math.round(horas * (Number(precioHora) || 0));
    const neto = Math.round(total / (1 + IVA));

    return { horas, total, neto, iva: total - neto };
}

function fechaLegible(fecha) {
    if (! fecha) {
        return '—';
    }

    const [anio, mes, dia] = fecha.split('-');

    return `${dia}/${mes}/${anio}`;
}

export default function Cotizacion({ cotizacion, taller, estados }) {
    const { data, setData, patch, processing, errors } = useForm({
        numero: cotizacion.numero,
        fecha: cotizacion.fecha,
        valido_hasta: cotizacion.valido_hasta,
        descripcion: cotizacion.descripcion,
        precio_hora: cotizacion.precio_hora,
        estado: cotizacion.estado,
        notas: cotizacion.notas ?? '',
        detalle: cotizacion.detalle.map((l) => ({ ...l })),
    });

    const cuenta = cuentaDe(data.detalle, data.precio_hora);
    const quitadas = data.detalle.filter((l) => ! l.incluida).length;

    const campo =
        'w-full rounded-control border border-line bg-surface-2 px-2 py-1 text-sm text-chalk focus:border-line-strong focus:outline-none';

    function cambiarLinea(i, cambios) {
        setData(
            'detalle',
            data.detalle.map((linea, j) => (i === j ? { ...linea, ...cambios } : linea)),
        );
    }

    function guardar(e) {
        e?.preventDefault();
        patch(`/panel/talleres/cotizaciones/${cotizacion.uuid}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title={`Cotización N° ${cotizacion.numero}`} />

            <header className="mb-4">
                <Link
                    href={`/panel/talleres/${taller.uuid}${cotizacion.periodo ? `?periodo=${cotizacion.periodo}` : ''}`}
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    {taller.nombre}
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">
                    Cotización N° {cotizacion.numero}
                    {cotizacion.mes ? <span className="font-normal text-fog"> · {cotizacion.mes}</span> : null}
                </h1>
                <p className="apoyo text-fog">
                    {taller.institucion?.nombre ?? 'Sin institución'}
                    {cotizacion.vencida && cotizacion.estado !== 'aceptada' ? (
                        <span className="text-warn"> · venció el {fechaLegible(cotizacion.valido_hasta)}</span>
                    ) : null}
                </p>
            </header>

            <div className="grid items-start gap-4 xl:grid-cols-[1fr_22rem]">
                <div className="min-w-0 space-y-3">
                    <Panel
                        titulo="Las clases que se cotizan"
                        descripcion="Destilda las que no va a haber: feriados, semanas suspendidas. La cuenta se rehace sola."
                        enlace={
                            <span className="flex shrink-0 items-center gap-3">
                                {cotizacion.periodo ? (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            router.post(
                                                `/panel/talleres/cotizaciones/${cotizacion.uuid}/refrescar`,
                                                {},
                                                { preserveScroll: true },
                                            )
                                        }
                                        className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                                    >
                                        <CalendarPlusIcon className="size-3.5" aria-hidden="true" />
                                        Traer las del horario
                                    </button>
                                ) : null}
                                <button
                                    type="button"
                                    onClick={() =>
                                        setData('detalle', [
                                            ...data.detalle,
                                            { fecha: null, dia: null, detalle: '', horas: 1, incluida: true },
                                        ])
                                    }
                                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                                >
                                    <PlusIcon className="size-3.5" aria-hidden="true" />
                                    Una línea
                                </button>
                            </span>
                        }
                    >
                        <Tabla
                            columnas={[
                                { titulo: 'Va', className: 'w-10' },
                                { titulo: 'Día', className: 'whitespace-nowrap' },
                                { titulo: 'Horario', className: 'w-full' },
                                { titulo: 'Horas', className: 'text-right' },
                                { titulo: '', className: 'text-right' },
                            ]}
                            vacia={data.detalle.length === 0}
                            mensajeVacio="Ninguna clase. Usa «Traer las del horario» o añade una línea a mano."
                        >
                            {data.detalle.map((linea, i) => (
                                <Fila key={i}>
                                    <Celda>
                                        <input
                                            type="checkbox"
                                            checked={linea.incluida}
                                            onChange={(e) => cambiarLinea(i, { incluida: e.target.checked })}
                                            aria-label="Se cobra esta clase"
                                            className="size-4 accent-[var(--color-volt)]"
                                        />
                                    </Celda>
                                    <Celda className={`whitespace-nowrap tabular-nums ${linea.incluida ? 'text-chalk' : 'text-fog line-through'}`}>
                                        {linea.fecha ? (
                                            <>
                                                {fechaLegible(linea.fecha)}
                                                {linea.dia ? <span className="apoyo ml-1 text-fog">{linea.dia}</span> : null}
                                            </>
                                        ) : (
                                            <input
                                                type="date"
                                                value={linea.fecha ?? ''}
                                                onChange={(e) => cambiarLinea(i, { fecha: e.target.value || null })}
                                                className={`${campo} w-36 tabular-nums`}
                                            />
                                        )}
                                    </Celda>
                                    <Celda>
                                        <input
                                            type="text"
                                            value={linea.detalle ?? ''}
                                            onChange={(e) => cambiarLinea(i, { detalle: e.target.value })}
                                            placeholder="15:00 a 16:00"
                                            className={`${campo} ${linea.incluida ? '' : 'text-fog'}`}
                                        />
                                    </Celda>
                                    <Celda className="text-right">
                                        <input
                                            type="number"
                                            step="0.25"
                                            min="0"
                                            max="24"
                                            value={linea.horas}
                                            onChange={(e) => cambiarLinea(i, { horas: e.target.value })}
                                            className={`${campo} w-20 text-right tabular-nums`}
                                        />
                                    </Celda>
                                    <Celda className="text-right">
                                        <button
                                            type="button"
                                            onClick={() => setData('detalle', data.detalle.filter((_, j) => j !== i))}
                                            aria-label="Quitar esta línea"
                                            className="rounded-control p-1 text-fog transition-colors hover:text-danger"
                                        >
                                            <TrashIcon className="size-3.5" aria-hidden="true" />
                                        </button>
                                    </Celda>
                                </Fila>
                            ))}
                        </Tabla>

                        {quitadas > 0 ? (
                            <p className="apoyo mt-2 text-fog">
                                {quitadas === 1 ? '1 clase destildada' : `${quitadas} clases destildadas`}: salen en el papel
                                tachadas, para que el colegio vea por qué el mes sale más barato.
                            </p>
                        ) : null}
                    </Panel>

                    <Panel titulo="Lo que dice el papel" descripcion="Tal cual lo lee el colegio.">
                        <form onSubmit={guardar} className="space-y-3">
                            <div className="grid gap-3 sm:grid-cols-[1fr_10rem]">
                                <label className="block">
                                    <span className="rotulo">Descripción</span>
                                    <input
                                        type="text"
                                        value={data.descripcion}
                                        onChange={(e) => setData('descripcion', e.target.value)}
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
                                        className={`${campo} mt-1 tabular-nums`}
                                    />
                                </label>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3">
                                <label className="block">
                                    <span className="rotulo">Número</span>
                                    <input
                                        type="number"
                                        min="1"
                                        value={data.numero}
                                        onChange={(e) => setData('numero', e.target.value)}
                                        className={`${campo} mt-1 tabular-nums`}
                                    />
                                    {errors.numero ? <span className="apoyo text-danger">{errors.numero}</span> : null}
                                </label>
                                <label className="block">
                                    <span className="rotulo">Fecha</span>
                                    <input
                                        type="date"
                                        value={data.fecha}
                                        onChange={(e) => setData('fecha', e.target.value)}
                                        className={`${campo} mt-1 tabular-nums`}
                                    />
                                </label>
                                <label className="block">
                                    <span className="rotulo">Válida hasta</span>
                                    <input
                                        type="date"
                                        value={data.valido_hasta}
                                        onChange={(e) => setData('valido_hasta', e.target.value)}
                                        className={`${campo} mt-1 tabular-nums`}
                                    />
                                    {errors.valido_hasta ? (
                                        <span className="apoyo text-danger">{errors.valido_hasta}</span>
                                    ) : null}
                                </label>
                            </div>

                            <label className="block">
                                <span className="rotulo">Nota para el colegio</span>
                                <textarea
                                    rows="2"
                                    value={data.notas}
                                    onChange={(e) => setData('notas', e.target.value)}
                                    placeholder="Las clases del 1 al 5 de mayo quedan suspendidas por vacaciones."
                                    className={`${campo} mt-1`}
                                />
                            </label>

                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                Guardar
                            </button>
                        </form>
                    </Panel>
                </div>

                <div className="space-y-3 xl:sticky xl:top-4">
                    <Panel titulo="La cuenta" descripcion="Cambia mientras tildas, antes de guardar.">
                        <dl className="space-y-1">
                            <div className="flex justify-between gap-3 text-sm">
                                <dt className="text-fog">Horas</dt>
                                <dd className="tabular-nums text-chalk">{cuenta.horas}</dd>
                            </div>
                            <div className="flex justify-between gap-3 text-sm">
                                <dt className="text-fog">La hora</dt>
                                <dd className="tabular-nums text-chalk">
                                    <Reservado ancho="w-20">{pesos.format(Number(data.precio_hora) || 0)}</Reservado>
                                </dd>
                            </div>
                            <div className="flex justify-between gap-3 border-t border-line pt-1 text-sm">
                                <dt className="text-fog">Neto</dt>
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

                        {cuenta.total !== cotizacion.total ? (
                            <p className="apoyo mt-2 text-warn">
                                Sin guardar. El papel todavía dice {pesos.format(cotizacion.total)}.
                            </p>
                        ) : null}

                        <button
                            type="button"
                            onClick={guardar}
                            disabled={processing}
                            className="mt-3 w-full rounded-control bg-volt px-3 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            Guardar la cotización
                        </button>

                        {/* El papel siempre sale de lo guardado, no de lo que
                            hay en pantalla: por eso se abre aparte y después de
                            guardar. */}
                        <a
                            href={`/panel/talleres/cotizaciones/${cotizacion.uuid}/imprimir`}
                            target="_blank"
                            rel="noopener"
                            className="mt-2 flex w-full items-center justify-center gap-1.5 rounded-control border border-line px-3 py-2 text-sm text-chalk transition-colors hover:border-line-strong"
                        >
                            <PrinterIcon className="size-4" aria-hidden="true" />
                            Imprimir o guardar como PDF
                        </a>
                    </Panel>

                    <Panel titulo="En qué va">
                        <select
                            value={data.estado}
                            onChange={(e) => setData('estado', e.target.value)}
                            className={campo}
                        >
                            {Object.entries(estados).map(([valor, texto]) => (
                                <option key={valor} value={valor}>
                                    {texto}
                                </option>
                            ))}
                        </select>
                        <p className="apoyo mt-2 text-fog">
                            Se guarda con el botón de arriba. Sirve para saber qué se mandó y qué quedó a medias.
                        </p>

                        <button
                            type="button"
                            onClick={() => {
                                if (window.confirm('¿Tirar esta cotización?')) {
                                    router.delete(`/panel/talleres/cotizaciones/${cotizacion.uuid}`);
                                }
                            }}
                            className="apoyo mt-3 text-fog transition-colors hover:text-danger"
                        >
                            Eliminar la cotización
                        </button>
                    </Panel>
                </div>
            </div>
        </>
    );
}
