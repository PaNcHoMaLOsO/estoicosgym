import { confirmar } from '@/components/Confirmar';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeftIcon, CalendarPlusIcon, PlusIcon, PrinterIcon, TrashIcon } from 'lucide-react';

import { Panel, pesos } from '@/components/Tablero';
import { Reservado } from '@/Privado';

/**
 * Una cotización de taller.
 *
 * ES EL PAPEL QUE SE LE MANDA AL COLEGIO antes del mes: «octubre son 42 horas,
 * $1.260.000». Se hacía copiando el Word del mes anterior y cambiando a mano el
 * número, las fechas y la cifra, después de contar las clases con el calendario
 * de Windows al lado.
 *
 * LLEGA HECHA Y LO ÚNICO QUE SE HACE AQUÍ ES QUITAR. Esa es toda la pantalla:
 * las clases vienen del horario, agrupadas POR SEMANA —que es como se suspenden
 * de verdad: «la semana del 20 no hay, están de pruebas»—, y se quita la semana
 * entera de un botón. El total cambia mientras se quita, antes de guardar, para
 * poder decirlo por teléfono.
 *
 * El número, las fechas y el precio vienen puestos y casi nunca se tocan, así
 * que están guardados en «Cambiar los datos del papel» y no estorbando delante.
 */

const IVA = 0.19;

const DIAS = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

/** La misma cuenta que hace el servidor, para verla mientras se quita. */
function cuentaDe(lineas, precioHora) {
    const horas = Math.round(lineas.reduce((suma, l) => suma + (l.incluida ? Number(l.horas) || 0 : 0), 0) * 100) / 100;
    // Se parte del total con IVA dentro, como el cobro: al revés el total se
    // iría un peso por el redondeo y el papel dejaría de cuadrar.
    const total = Math.round(horas * (Number(precioHora) || 0));
    const neto = Math.round(total / (1 + IVA));

    return { horas, total, neto, iva: total - neto };
}

function comoFecha(texto) {
    if (! texto) {
        return null;
    }

    const [anio, mes, dia] = texto.split('-').map(Number);

    return new Date(anio, mes - 1, dia);
}

/**
 * Las clases repartidas por semana.
 *
 * POR SEMANA Y NO POR DÍA porque así se suspenden: el colegio dice «la semana
 * del 20 no hay clases». Con cuarenta y dos casillas sueltas hay que ir
 * buscándolas una por una y es donde se destilda la que no era.
 */
function porSemanas(lineas) {
    const grupos = new Map();

    lineas.forEach((linea, i) => {
        const fecha = comoFecha(linea.fecha);

        if (! fecha) {
            const sueltas = grupos.get('sueltas') ?? { clave: 'sueltas', titulo: 'Escritas a mano', indices: [] };
            sueltas.indices.push(i);
            grupos.set('sueltas', sueltas);

            return;
        }

        // El lunes de su semana: es la clave que las junta.
        const lunes = new Date(fecha);
        lunes.setDate(fecha.getDate() - ((fecha.getDay() + 6) % 7));
        const clave = lunes.toISOString().slice(0, 10);

        const grupo = grupos.get(clave) ?? {
            clave,
            titulo: `Semana del ${lunes.getDate()} de ${MESES[lunes.getMonth()]}`,
            indices: [],
        };
        grupo.indices.push(i);
        grupos.set(clave, grupo);
    });

    // Las sueltas, al final: son la excepción.
    return [...grupos.values()].sort((a, b) => (a.clave === 'sueltas' ? 1 : b.clave === 'sueltas' ? -1 : a.clave.localeCompare(b.clave)));
}

/** Una clase: el día, el horario y si va o no. */
function Clase({ linea, alCambiar, alQuitar }) {
    const fecha = comoFecha(linea.fecha);

    return (
        <li className="flex flex-wrap items-center gap-2 border-t border-line px-3 py-1.5 first:border-t-0">
            <label className="flex flex-1 cursor-pointer items-center gap-2.5">
                <input
                    type="checkbox"
                    checked={linea.incluida}
                    onChange={(e) => alCambiar({ incluida: e.target.checked })}
                    className="size-4 shrink-0 accent-[var(--color-volt)]"
                />
                <span className={`w-28 shrink-0 text-sm tabular-nums ${linea.incluida ? 'text-chalk' : 'text-fog line-through'}`}>
                    {fecha ? `${DIAS[fecha.getDay()]} ${fecha.getDate()}/${fecha.getMonth() + 1}` : 'Sin fecha'}
                </span>
                <span className={`text-sm ${linea.incluida ? 'text-fog' : 'text-fog line-through'}`}>
                    {linea.detalle || 'Sin horario'}
                </span>
            </label>

            <span className="flex items-center gap-1">
                <input
                    type="number"
                    step="0.25"
                    min="0"
                    max="24"
                    value={linea.horas}
                    onChange={(e) => alCambiar({ horas: e.target.value })}
                    aria-label="Horas de esta clase"
                    className="w-16 rounded-control border border-line bg-surface-2 px-2 py-0.5 text-right text-sm tabular-nums text-chalk focus:border-line-strong focus:outline-none"
                />
                <span className="apoyo text-fog">h</span>
                <button
                    type="button"
                    onClick={alQuitar}
                    aria-label="Borrar esta línea"
                    className="rounded-control p-1 text-fog transition-colors hover:text-danger"
                >
                    <TrashIcon className="size-3.5" aria-hidden="true" />
                </button>
            </span>
        </li>
    );
}

export default function Cotizacion({ cotizacion, taller, estados }) {
    // El detalle de las horas son dos hojas más: hay veces —el colegio ya
    // conoce el horario— en que sobra y basta la hoja de siempre.
    const [conDetalle, setConDetalle] = useState(true);
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
    const guardada = cuentaDe(cotizacion.detalle, cotizacion.precio_hora);
    const sinGuardar = cuenta.total !== guardada.total;
    const semanas = porSemanas(data.detalle);
    const quitadas = data.detalle.filter((l) => ! l.incluida).length;

    const campo =
        'w-full rounded-control border border-line bg-surface-2 px-2 py-1 text-sm text-chalk focus:border-line-strong focus:outline-none';

    function cambiarLinea(i, cambios) {
        setData('detalle', data.detalle.map((linea, j) => (i === j ? { ...linea, ...cambios } : linea)));
    }

    /** Quitar o devolver una semana entera: es como las suspende el colegio. */
    function cambiarSemana(indices, incluida) {
        setData('detalle', data.detalle.map((linea, j) => (indices.includes(j) ? { ...linea, incluida } : linea)));
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
                        <span className="text-warn"> · venció el {cotizacion.valido_hasta.split('-').reverse().join('/')}</span>
                    ) : null}
                </p>
            </header>

            {/* Qué hay que hacer aquí, en una línea: la cotización llega hecha
                y lo único que se hace es quitar lo que no va a haber. */}
            <p className="mb-3 rounded-panel border border-line bg-surface-2/40 px-3 py-2 text-sm text-fog">
                Ya está hecha con las clases del horario. Quita las semanas o los días que no va a haber, guarda y
                mándala.
            </p>

            <div className="grid items-start gap-4 xl:grid-cols-[1fr_22rem]">
                <div className="min-w-0 space-y-3">
                    <Panel
                        titulo={cotizacion.mes ? `Clases de ${cotizacion.mes}` : 'Clases'}
                        descripcion="Lo que no se hace no se cobra: quita la semana entera o solo el día."
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
                                    Una clase
                                </button>
                            </span>
                        }
                    >
                        {data.detalle.length === 0 ? (
                            <p className="apoyo py-6 text-center text-fog">
                                Ninguna clase. Usa «Traer las del horario» o añade una a mano.
                            </p>
                        ) : (
                            <div className="space-y-3">
                                {semanas.map((semana) => {
                                    const lineas = semana.indices.map((i) => data.detalle[i]);
                                    const horas = lineas.reduce((s, l) => s + (l.incluida ? Number(l.horas) || 0 : 0), 0);
                                    const algunaVa = lineas.some((l) => l.incluida);

                                    return (
                                        <div key={semana.clave} className="overflow-hidden rounded-panel border border-line">
                                            <div className="flex flex-wrap items-center justify-between gap-2 bg-surface-2 px-3 py-2">
                                                <span className={`text-sm font-medium ${algunaVa ? 'text-chalk' : 'text-fog'}`}>
                                                    {semana.titulo}
                                                    <span className="apoyo ml-2 text-fog">
                                                        {algunaVa ? `${Math.round(horas * 100) / 100} horas` : 'suspendida'}
                                                    </span>
                                                </span>

                                                {/* El botón de la semana entera: es como las
                                                    suspende el colegio, no día por día. */}
                                                <button
                                                    type="button"
                                                    onClick={() => cambiarSemana(semana.indices, ! algunaVa)}
                                                    className="apoyo rounded-control border border-line px-2 py-0.5 text-fog transition-colors hover:text-chalk"
                                                >
                                                    {algunaVa ? 'Quitar la semana' : 'Devolverla'}
                                                </button>
                                            </div>

                                            <ul>
                                                {semana.indices.map((i) => (
                                                    <Clase
                                                        key={i}
                                                        linea={data.detalle[i]}
                                                        alCambiar={(cambios) => cambiarLinea(i, cambios)}
                                                        alQuitar={() => setData('detalle', data.detalle.filter((_, j) => j !== i))}
                                                    />
                                                ))}
                                            </ul>
                                        </div>
                                    );
                                })}
                            </div>
                        )}

                        {quitadas > 0 ? (
                            <p className="apoyo mt-3 text-fog">
                                {quitadas === 1 ? '1 clase quitada' : `${quitadas} clases quitadas`}: salen tachadas en el
                                papel, para que el colegio vea por qué el mes sale más barato.
                            </p>
                        ) : null}
                    </Panel>

                    {/* Los datos del papel vienen puestos y casi nunca se tocan:
                        delante solo estorbarían a lo que sí se hace. */}
                    <details className="overflow-hidden rounded-panel border border-line bg-surface">
                        <summary className="cursor-pointer px-4 py-3 text-sm font-medium text-chalk">
                            Cambiar los datos del papel
                            <span className="apoyo ml-2 font-normal text-fog">
                                N° {data.numero} · {data.descripcion} · {pesos.format(Number(data.precio_hora) || 0)} la hora
                            </span>
                        </summary>

                        <form onSubmit={guardar} className="space-y-3 border-t border-line p-4">
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

                            <label className="block">
                                <span className="rotulo">En qué va</span>
                                <select
                                    value={data.estado}
                                    onChange={(e) => setData('estado', e.target.value)}
                                    className={`${campo} mt-1`}
                                >
                                    {Object.entries(estados).map(([valor, texto]) => (
                                        <option key={valor} value={valor}>
                                            {texto}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                Guardar
                            </button>
                        </form>
                    </details>

                    <button
                        type="button"
                        onClick={async () => {
                            if (await confirmar({ titulo: '¿Tirar esta cotización?', mensaje: 'Queda en la papelera.', confirmar: 'Tirar', peligrosa: true })) {
                                router.delete(`/panel/talleres/cotizaciones/${cotizacion.uuid}`);
                            }
                        }}
                        className="apoyo text-fog transition-colors hover:text-danger"
                    >
                        Eliminar la cotización
                    </button>
                </div>

                <div className="space-y-3 xl:sticky xl:top-4">
                    <Panel titulo="Lo que se le cobra" descripcion="Cambia mientras quitas clases.">
                        <p className="text-2xl font-semibold tabular-nums text-chalk">
                            <Reservado ancho="w-28">{pesos.format(cuenta.total)}</Reservado>
                        </p>
                        <p className="apoyo mt-0.5 text-fog">
                            {cuenta.horas} horas a {pesos.format(Number(data.precio_hora) || 0)} · IVA incluido
                        </p>

                        <dl className="mt-3 space-y-1 border-t border-line pt-2">
                            <div className="flex justify-between gap-3 text-sm">
                                <dt className="text-fog">Neto (para la factura)</dt>
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
                        </dl>

                        {/* El papel sale de lo guardado, no de lo que hay en
                            pantalla: si no se avisa, se manda el total viejo. */}
                        {sinGuardar ? (
                            <p className="apoyo mt-3 text-warn">
                                Sin guardar: el papel todavía dice {pesos.format(guardada.total)}.
                            </p>
                        ) : null}

                        <button
                            type="button"
                            onClick={guardar}
                            disabled={processing}
                            className="mt-3 w-full rounded-control bg-volt px-3 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            {sinGuardar ? 'Guardar los cambios' : 'Guardado'}
                        </button>

                        <a
                            href={`/panel/talleres/cotizaciones/${cotizacion.uuid}/imprimir${conDetalle ? '' : '?detalle=no'}`}
                            target="_blank"
                            rel="noopener"
                            className="mt-2 flex w-full items-center justify-center gap-1.5 rounded-control border border-line px-3 py-2 text-sm text-chalk transition-colors hover:border-line-strong"
                        >
                            <PrinterIcon className="size-4" aria-hidden="true" />
                            Imprimir o guardar como PDF
                        </a>

                        <label className="apoyo mt-2 flex items-center gap-2 text-fog">
                            <input
                                type="checkbox"
                                checked={conDetalle}
                                onChange={(e) => setConDetalle(e.target.checked)}
                                className="size-3.5 accent-[var(--color-volt)]"
                            />
                            Con el detalle de las horas
                        </label>
                    </Panel>
                </div>
            </div>
        </>
    );
}
