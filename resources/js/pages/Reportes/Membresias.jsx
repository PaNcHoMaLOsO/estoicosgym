import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';

import Barras from '@/components/Barras';
import Estado from '@/components/Estado';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

export default function Membresias({ porPlan, porEstado, cifras }) {
    return (
        <>
            <Head title="Membresías" />

            <header className="mb-5">
                <Link
                    href="/panel/reportes"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Reportes
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Membresías</h1>
                <p className="apoyo text-fog">
                    {cifras.activas} al día · {cifras.pausadas} pausadas · {cifras.vencidas} vencidas
                </p>
            </header>

            <div className="grid gap-3 lg:grid-cols-2">
                <section className="rounded-panel border border-line bg-surface p-4">
                    <h2 className="rotulo mb-1">Planes vigentes</h2>
                    <p className="apoyo mb-3 text-fog">
                        Cuántas membresías al día hay de cada plan, y cuánto valen juntas.
                    </p>
                    <Barras
                        filas={porPlan.map((p) => ({
                            nombre: p.nombre,
                            total: p.total,
                            cantidad: undefined,
                        }))}
                        formato={(v) => `${v}`}
                        vacio="No hay membresías al día."
                    />

                    {porPlan.length > 0 ? (
                        <dl className="mt-4 space-y-1 border-t border-line pt-3">
                            {porPlan.map((p) => (
                                <div key={p.nombre} className="flex justify-between text-sm">
                                    <dt className="text-fog">{p.nombre}</dt>
                                    <dd className="tabular-nums text-chalk">{pesos.format(p.valor)}</dd>
                                </div>
                            ))}
                        </dl>
                    ) : null}
                </section>

                <section className="rounded-panel border border-line bg-surface p-4">
                    <h2 className="rotulo mb-1">Todas por estado</h2>
                    <p className="apoyo mb-3 text-fog">
                        Incluye las que ya terminaron: es el histórico completo.
                    </p>

                    {porEstado.length === 0 ? (
                        <p className="apoyo py-6 text-center text-fog">Todavía no hay inscripciones.</p>
                    ) : (
                        <ul className="space-y-2">
                            {porEstado.map((e) => (
                                <li key={e.codigo} className="flex items-center justify-between gap-3">
                                    <Estado codigo={e.codigo} />
                                    <span className="tabular-nums text-chalk">{e.total}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}
