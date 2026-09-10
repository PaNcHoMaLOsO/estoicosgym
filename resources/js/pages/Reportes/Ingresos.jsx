import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';

import Barras from '@/components/Barras';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Panel({ titulo, children }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo mb-3">{titulo}</h2>
            {children}
        </section>
    );
}

export default function Ingresos({ anio, anios, meses, total, porMetodo, porMembresia }) {
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
                        Incluye los abonos parciales: es dinero que ya entró a la caja.
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

            <div className="mb-4 rounded-panel border border-line bg-surface p-4">
                <p className="rotulo">Total del año</p>
                <p className="mt-0.5 text-2xl font-semibold tabular-nums text-chalk">
                    {pesos.format(total)}
                </p>
            </div>

            <div className="grid gap-3 lg:grid-cols-2">
                <div className="lg:col-span-2">
                    <Panel titulo="Mes a mes">
                        <Barras filas={meses} formato={(v) => pesos.format(v)} />
                    </Panel>
                </div>

                <Panel titulo="Por método de pago">
                    <Barras filas={porMetodo} formato={(v) => pesos.format(v)} />
                </Panel>

                <Panel titulo="Por plan">
                    <Barras filas={porMembresia} formato={(v) => pesos.format(v)} />
                </Panel>
            </div>
        </>
    );
}
