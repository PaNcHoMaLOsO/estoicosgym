import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';

import Barras from '@/components/Barras';

/**
 * Lo que entró en un año: junto y por de dónde vino.
 *
 * CONTABA SOLO LAS MEMBRESÍAS. Lo que pagó el colegio por el arriendo y lo
 * cobrado del mesón no salía, así que el «total del año» decía menos de lo que
 * entró. Ahora el total va con sus tres partes, mes a mes, y cada parte trae
 * su propio detalle: los socios por plan y medio de pago, los talleres por
 * colegio y el mesón por producto. Es la misma cuenta que la Caja.
 */

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

const COLOR = {
    membresias: 'bg-volt',
    talleres: 'bg-sky-400',
    meson: 'bg-amber-400',
};

function Panel({ titulo, descripcion, children }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo">{titulo}</h2>
            {descripcion ? <p className="apoyo mb-3 text-fog">{descripcion}</p> : <div className="mb-3" />}
            {children}
        </section>
    );
}

function Punto({ fuente }) {
    return <span className={`inline-block size-2 rounded-full ${COLOR[fuente]}`} aria-hidden="true" />;
}

export default function Ingresos({
    anio,
    anios,
    meses,
    total,
    fuentes,
    totalesPorFuente,
    porMetodo,
    porMembresia,
    porInstitucion = [],
    porConcepto = [],
}) {
    return (
        <>
            <Head title={`Ingresos ${anio}`} />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <Link
                        href="/panel/reportes"
                        className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                    >
                        <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                        Reportes
                    </Link>
                    <h1 className="mt-1 text-lg font-semibold text-chalk">Ingresos {anio}</h1>
                    <p className="apoyo text-fog">
                        Membresías, talleres y mesón, juntos y por separado. Solo lo que ya entró: lo que se debe no cuenta.
                    </p>
                </div>

                <label className="flex items-center gap-2 text-sm text-fog">
                    Año
                    <select
                        value={anio}
                        onChange={(e) =>
                            router.get('/panel/reportes/ingresos', { anio: e.target.value }, {
                                preserveState: true,
                                preserveScroll: true,
                            })
                        }
                        className="rounded-control border border-line bg-surface px-2 py-1 text-sm text-chalk focus:border-line-strong focus:outline-none"
                    >
                        {anios.map((a) => (
                            <option key={a} value={a}>
                                {a}
                            </option>
                        ))}
                    </select>
                </label>
            </header>

            {/* ===== El año: junto y por fuente ===== */}
            <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-panel border border-line bg-surface p-4">
                    <p className="rotulo">Total del año</p>
                    <p className="mt-0.5 text-2xl font-semibold tabular-nums text-chalk">{pesos.format(total)}</p>
                </div>
                {Object.entries(fuentes).map(([clave, nombre]) => (
                    <div key={clave} className="rounded-panel border border-line bg-surface p-4">
                        <p className="rotulo flex items-center gap-1.5">
                            <Punto fuente={clave} />
                            {nombre}
                        </p>
                        <p className="mt-0.5 text-xl font-semibold tabular-nums text-chalk">
                            {pesos.format(totalesPorFuente[clave] ?? 0)}
                        </p>
                        <p className="apoyo text-fog">
                            {total > 0 ? Math.round(((totalesPorFuente[clave] ?? 0) / total) * 100) : 0}% del año
                        </p>
                    </div>
                ))}
            </div>

            <div className="grid gap-3 lg:grid-cols-2">
                <div className="lg:col-span-2">
                    <Panel titulo="Mes a mes" descripcion="Cada mes partido por de dónde vino la plata, y su total.">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-line text-left">
                                        <th className="rotulo py-2 pr-3 font-normal">Mes</th>
                                        {Object.entries(fuentes).map(([clave, nombre]) => (
                                            <th key={clave} className="rotulo px-3 py-2 text-right font-normal">
                                                <span className="inline-flex items-center gap-1.5">
                                                    <Punto fuente={clave} />
                                                    {nombre}
                                                </span>
                                            </th>
                                        ))}
                                        <th className="rotulo py-2 pl-3 text-right font-normal">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {meses.map((m) => (
                                        <tr key={m.mes_largo} className="border-b border-line last:border-0">
                                            <td className="py-1.5 pr-3 capitalize text-chalk">{m.mes_largo}</td>
                                            {Object.keys(fuentes).map((clave) => (
                                                <td key={clave} className="px-3 py-1.5 text-right tabular-nums text-fog">
                                                    {m.partes[clave] > 0 ? pesos.format(m.partes[clave]) : '—'}
                                                </td>
                                            ))}
                                            <td className="py-1.5 pl-3 text-right font-medium tabular-nums text-chalk">
                                                {m.total > 0 ? pesos.format(m.total) : '—'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot>
                                    <tr className="border-t border-line-strong">
                                        <td className="py-2 pr-3 font-medium text-chalk">Año</td>
                                        {Object.keys(fuentes).map((clave) => (
                                            <td key={clave} className="px-3 py-2 text-right font-medium tabular-nums text-chalk">
                                                {pesos.format(totalesPorFuente[clave] ?? 0)}
                                            </td>
                                        ))}
                                        <td className="py-2 pl-3 text-right font-semibold tabular-nums text-chalk">{pesos.format(total)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </Panel>
                </div>

                <Panel titulo="Membresías, por plan" descripcion="Lo que pagaron los socios, según el plan que compraron.">
                    <Barras filas={porMembresia} formato={(v) => pesos.format(v)} vacio="Ninguna membresía cobrada este año." />
                </Panel>

                <Panel titulo="Membresías, por medio de pago" descripcion="Un pago repartido cuenta en cada medio.">
                    <Barras filas={porMetodo} formato={(v) => pesos.format(v)} vacio="Ninguna membresía cobrada este año." />
                </Panel>

                <Panel titulo="Talleres, por colegio o empresa" descripcion="Lo facturado y ya pagado, con IVA incluido.">
                    <Barras filas={porInstitucion} formato={(v) => pesos.format(v)} vacio="Ningún taller pagado este año." />
                </Panel>

                <Panel titulo="Mesón, por producto" descripcion="Lo fiado que ya se cobró. Los diez que más plata dejaron.">
                    <Barras filas={porConcepto} formato={(v) => pesos.format(v)} vacio="Nada cobrado del mesón este año." />
                </Panel>
            </div>
        </>
    );
}
