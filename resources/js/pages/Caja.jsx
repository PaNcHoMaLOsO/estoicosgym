import { Head, Link, usePage } from '@inertiajs/react';
import { TrendingDownIcon, TrendingUpIcon } from 'lucide-react';

import Barras from '@/components/Barras';
import Columnas from '@/components/Columnas';
import { Cifra, Panel, pesos } from '@/components/Tablero';
import { Reservado } from '@/Privado';

/**
 * La caja: lo que entró, de dónde vino, lo que se debe y cómo va el gimnasio.
 *
 * EL GIMNASIO COBRA POR TRES LADOS —las membresías, lo que se le factura al
 * colegio por el arriendo de la sala y lo que se cobra del mesón— y la caja
 * contaba uno. Ahora cada cifra se ve junta y por partes: el total dice cómo va
 * el negocio, y las partes dicen cuánto pesa cada cosa. Sin las partes no se
 * sabe si el mes subió porque vinieron más socios o porque pagó el colegio.
 *
 * Solo entra quien ve los informes, y aun así las cifras salen TAPADAS si así
 * se pidió: en el mesón se sienta gente detrás de quien mira la pantalla.
 *
 * CADA CIFRA CON CON QUÉ COMPARARLA. «Entró $2.070.034 este mes» no dice si el
 * mes va bien; «un 12% más que a esta altura del mes pasado» sí.
 */

/** El color de cada fuente, el mismo en las tarjetas, la tabla y las barras. */
const COLOR = {
    membresias: 'bg-volt',
    talleres: 'bg-sky-400',
    meson: 'bg-amber-400',
};

/** Cuánto cambió respecto al periodo anterior, en palabras y con su flecha. */
function Comparacion({ ahora, antes, cuando }) {
    if (! antes) {
        // Sin periodo anterior con qué comparar, el porcentaje sería inventado.
        return <>{ahora > 0 ? `nada que comparar ${cuando}` : `sin movimiento ${cuando}`}</>;
    }

    const cambio = Math.round(((ahora - antes) / antes) * 100);

    if (cambio === 0) {
        return <>igual que {cuando}</>;
    }

    const Icono = cambio > 0 ? TrendingUpIcon : TrendingDownIcon;

    return (
        <span className={`inline-flex items-center gap-1 ${cambio > 0 ? 'text-ok' : 'text-warn'}`}>
            <Icono className="size-3.5" aria-hidden="true" />
            {cambio > 0 ? '+' : ''}
            {cambio}% que {cuando}
        </span>
    );
}

/**
 * La barra que reparte el total entre las fuentes.
 *
 * Una sola línea que se lee de un vistazo: cuánto del mes es membresías y
 * cuánto lo trajo el colegio. Los números exactos están al lado.
 */
function Reparto({ partes, fuentes }) {
    const total = Object.keys(fuentes).reduce((s, f) => s + (partes[f] ?? 0), 0);

    if (total === 0) {
        return <div className="h-2 rounded-pill bg-surface-2" aria-hidden="true" />;
    }

    return (
        <div className="flex h-2 overflow-hidden rounded-pill bg-surface-2" aria-hidden="true">
            {Object.keys(fuentes).map((f) =>
                partes[f] > 0 ? (
                    <span key={f} className={COLOR[f]} style={{ width: `${(partes[f] / total) * 100}%` }} />
                ) : null,
            )}
        </div>
    );
}

/** Una fuente del mes: su cifra, cuánto pesa del total y cómo va. */
function Fuente({ clave, nombre, mes, mesPasado, total, href }) {
    const parte = total > 0 ? Math.round((mes / total) * 100) : 0;

    return (
        <Link href={href} className="block rounded-panel border border-line bg-surface p-4 transition-colors hover:border-line-strong">
            <p className="flex items-center gap-2 text-sm text-fog">
                <span className={`size-2 rounded-full ${COLOR[clave]}`} aria-hidden="true" />
                {nombre}
            </p>
            <p className="mt-2 text-xl font-semibold tabular-nums text-chalk">
                <Reservado ancho="w-24">{pesos.format(mes)}</Reservado>
            </p>
            <p className="apoyo mt-1 text-fog">
                {parte}% del mes · <Comparacion ahora={mes} antes={mesPasado} cuando="el mes pasado" />
            </p>
        </Link>
    );
}

/** Una línea de lo que se debe, con a dónde ir a cobrarla. */
function Deuda({ etiqueta, total, detalle, explicacion, href }) {
    return (
        <li className="flex items-baseline justify-between gap-3 border-b border-line py-2 last:border-0">
            <div>
                <Link href={href} className="text-sm text-chalk hover:underline">
                    {etiqueta}
                </Link>
                <p className="apoyo text-fog">{explicacion}</p>
            </div>
            <div className="shrink-0 text-right">
                <p className="font-medium tabular-nums text-chalk">
                    <Reservado ancho="w-16">{pesos.format(total)}</Reservado>
                </p>
                <p className="apoyo tabular-nums text-fog">{detalle}</p>
            </div>
        </li>
    );
}

export default function Caja({ caja, deuda, fiado, talleres, fuentes, porDia, porMes, porMetodo, altas, porPlan }) {
    // Quién debe, escondido desde Configuración. Son dos ajustes distintos:
    // lo fiado del mesón por un lado y lo que deben de su membresía por otro.
    const { privado } = usePage().props;
    const sinFiado = Boolean(privado?.sin_fiado);
    const sinPendientes = Boolean(privado?.sin_pendientes);
    // Tapado también dentro de los gráficos: destapar el ojito destapa todo.
    const plata = (valor) => <Reservado ancho="w-14">{pesos.format(valor)}</Reservado>;

    const DESTINO = {
        membresias: '/panel/pagos?filtro=mes',
        talleres: '/panel/talleres',
        meson: '/panel/fiados',
    };

    return (
        <>
            <Head title="Caja" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Caja</h1>
                <p className="apoyo text-fog">Lo que entra, de dónde viene y lo que se debe</p>
            </header>

            {/* ===== JUNTO: el total del mes y de hoy ===== */}
            <section className="mb-3 grid gap-3 lg:grid-cols-[2fr_1fr]">
                <div className="rounded-panel border border-line bg-surface p-5">
                    <p className="text-sm text-fog">Entró este mes, en total</p>
                    <p className="mt-1 text-3xl font-semibold tabular-nums text-chalk">
                        <Reservado ancho="w-40">{pesos.format(caja.mes.total)}</Reservado>
                    </p>
                    <p className="apoyo mt-1 text-fog">
                        <Comparacion ahora={caja.mes.total} antes={caja.mes_pasado.total} cuando="a esta altura del mes pasado" />
                    </p>

                    <div className="mt-4">
                        <Reparto partes={caja.mes} fuentes={fuentes} />
                        <ul className="mt-2 flex flex-wrap gap-x-5 gap-y-1">
                            {Object.entries(fuentes).map(([clave, nombre]) => (
                                <li key={clave} className="apoyo flex items-center gap-1.5 text-fog">
                                    <span className={`size-2 rounded-full ${COLOR[clave]}`} aria-hidden="true" />
                                    {nombre}
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                <Cifra
                    etiqueta="Entró hoy"
                    valor={<Reservado ancho="w-20">{pesos.format(caja.hoy.total)}</Reservado>}
                    pie={
                        <>
                            <Comparacion ahora={caja.hoy.total} antes={caja.ayer.total} cuando="ayer" />
                            {caja.hoy.total > 0 ? (
                                <span className="block">
                                    {Object.entries(fuentes)
                                        .filter(([clave]) => caja.hoy[clave] > 0)
                                        .map(([clave, nombre]) => `${nombre} ${pesos.format(caja.hoy[clave])}`)
                                        .join(' · ')}
                                </span>
                            ) : null}
                        </>
                    }
                />
            </section>

            {/* ===== POR SEPARADO: cada fuente con su cifra ===== */}
            <section className="mb-4 grid gap-3 sm:grid-cols-3">
                {Object.entries(fuentes).map(([clave, nombre]) => (
                    <Fuente
                        key={clave}
                        clave={clave}
                        nombre={nombre}
                        mes={caja.mes[clave]}
                        mesPasado={caja.mes_pasado[clave]}
                        total={caja.mes.total}
                        href={DESTINO[clave]}
                    />
                ))}
            </section>

            {/* ===== MES A MES: separado y junto en la misma tabla ===== */}
            <Panel titulo="Mes a mes" descripcion="Los últimos seis meses, por de dónde vino la plata y en total.">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-line text-left">
                                <th className="rotulo py-2 pr-3 font-normal">Mes</th>
                                {Object.entries(fuentes).map(([clave, nombre]) => (
                                    <th key={clave} className="rotulo px-3 py-2 text-right font-normal">
                                        <span className="inline-flex items-center gap-1.5">
                                            <span className={`size-2 rounded-full ${COLOR[clave]}`} aria-hidden="true" />
                                            {nombre}
                                        </span>
                                    </th>
                                ))}
                                <th className="rotulo py-2 pl-3 text-right font-normal">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {[...porMes].reverse().map((m) => (
                                <tr key={m.mes_largo} className="border-b border-line last:border-0">
                                    <td className="py-2 pr-3 capitalize text-chalk">{m.mes_largo}</td>
                                    {Object.keys(fuentes).map((clave) => (
                                        <td key={clave} className="px-3 py-2 text-right tabular-nums text-fog">
                                            {m.partes[clave] > 0 ? plata(m.partes[clave]) : '—'}
                                        </td>
                                    ))}
                                    <td className="py-2 pl-3 text-right font-medium tabular-nums text-chalk">{plata(m.total)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </Panel>

            <div className="my-4 grid gap-3 lg:grid-cols-2">
                <Panel
                    titulo="Cómo va el mes, día a día"
                    descripcion="Todo lo que entró cada día, sumando las tres fuentes. Los días en gris todavía no llegan."
                >
                    <Columnas
                        datos={porDia}
                        etiqueta="Ingresos"
                        formato={plata}
                        pie={<>Vamos {caja.dia_del_mes} de {porDia.length} días</>}
                    />
                </Panel>

                <Panel
                    titulo="Cómo pagan las membresías"
                    descripcion="Con qué medio entró la plata de los socios este mes. Un pago repartido cuenta en cada medio."
                >
                    <Barras filas={porMetodo} formato={plata} vacio="Todavía no se cobró ninguna membresía este mes." />
                </Panel>
            </div>

            <div className="mb-4 grid gap-3 lg:grid-cols-2">
                <Panel titulo="Lo que se debe" descripcion="Plata que ya es del gimnasio y todavía no entra, según a quién hay que cobrarle.">
                    <ul>
                        {sinPendientes ? null : (
                            <>
                                <Deuda
                                    etiqueta="Membresías de quien sigue viniendo"
                                    total={deuda.vigente.total}
                                    detalle={`${deuda.vigente.cuantas} ${deuda.vigente.cuantas === 1 ? 'membresía' : 'membresías'}`}
                                    explicacion="Se les cobra en el mostrador, cualquier día."
                                    href="/panel/inscripciones?filtro=con_deuda"
                                />
                                <Deuda
                                    etiqueta="Membresías ya vencidas"
                                    total={deuda.vencida.total}
                                    detalle={`${deuda.vencida.cuantas} ${deuda.vencida.cuantas === 1 ? 'membresía' : 'membresías'}`}
                                    explicacion="Hay que salir a buscarlos: llamarlos o escribirles."
                                    href="/panel/reportes/pendientes"
                                />
                            </>
                        )}
                        <Deuda
                            etiqueta="Talleres facturados sin pagar"
                            total={talleres.por_cobrar}
                            detalle={`${talleres.facturas} ${talleres.facturas === 1 ? 'factura' : 'facturas'}`}
                            explicacion="Lo que se le facturó al colegio y todavía no deposita."
                            href="/panel/talleres"
                        />
                        {sinFiado ? null : (
                            <Deuda
                                etiqueta="Fiado del mesón"
                                total={fiado.total}
                                detalle={`${fiado.personas} ${fiado.personas === 1 ? 'persona' : 'personas'}`}
                                explicacion="Lo anotado en la libreta y no cobrado."
                                href="/panel/fiados"
                            />
                        )}
                    </ul>
                </Panel>

                <Panel titulo="Socios nuevos" descripcion="Altas de cada mes: cuánta gente entra al gimnasio.">
                    <Columnas
                        datos={altas}
                        etiqueta="Altas"
                        pie={<>{altas.reduce((s, m) => s + m.total, 0)} en los últimos {altas.length} meses</>}
                    />
                </Panel>
            </div>

            <div className="mb-4 grid gap-3 lg:grid-cols-2">
                <Panel titulo="Qué planes se venden" descripcion="Membresías vigentes de cada plan.">
                    <Barras
                        filas={porPlan.map((p) => ({ nombre: p.nombre, total: p.total, cantidad: undefined }))}
                        formato={(v) => `${v}`}
                        vacio="No hay membresías vigentes."
                    />
                </Panel>
            </div>

            <p className="apoyo flex flex-wrap gap-x-5 gap-y-1 text-fog">
                <Link href="/panel/reportes" className="transition-colors hover:text-chalk">
                    Informes con el detalle
                </Link>
                <Link href="/panel/pagos?filtro=mes" className="transition-colors hover:text-chalk">
                    Los cobros de membresías de este mes, uno por uno
                </Link>
                <Link href="/panel/talleres" className="transition-colors hover:text-chalk">
                    Talleres y sus facturas
                </Link>
                {sinFiado ? null : (
                    <Link href="/panel/fiados" className="transition-colors hover:text-chalk">
                        Lo fiado, persona por persona
                    </Link>
                )}
            </p>
        </>
    );
}
