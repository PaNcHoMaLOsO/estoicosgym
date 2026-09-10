import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';

import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Socio', 'Membresía', 'Método', 'Fecha', 'Total', 'Abonado', 'Debe'];

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

export default function Pendientes({ pagos, total, abonado }) {
    return (
        <>
            <Head title="Por cobrar" />

            <header className="mb-5">
                <Link
                    href="/panel/reportes"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Reportes
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Por cobrar</h1>
                <p className="apoyo text-fog">Quién debe y cuánto</p>
            </header>

            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <div className="rounded-panel border border-warn/40 bg-warn/5 p-3">
                    <p className="rotulo">Falta por cobrar</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-warn">
                        {pesos.format(total)}
                    </p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Ya abonado</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">
                        {pesos.format(abonado)}
                    </p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Cobros abiertos</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">{pagos.length}</p>
                </div>
            </div>

            {/* Ordenados por lo que se debe, de mayor a menor: si hay que
                empezar a llamar por alguien, es por el de arriba. */}
            <Tabla
                columnas={COLUMNAS}
                vacia={pagos.length === 0}
                mensajeVacio="No hay nada por cobrar. Todas las membresías están al día."
            >
                {pagos.map((p) => (
                    <Fila key={p.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link href={`/admin/pagos/${p.uuid}`} className="hover:underline">
                                {p.socio}
                            </Link>
                        </Celda>
                        <Celda>{p.membresia ?? '—'}</Celda>
                        <Celda>{p.metodo ?? '—'}</Celda>
                        <Celda className="tabular-nums">{p.fecha ?? '—'}</Celda>
                        <Cifra>{pesos.format(p.total)}</Cifra>
                        <Cifra>{pesos.format(p.abonado)}</Cifra>
                        <Cifra className="font-medium text-warn">{pesos.format(p.pendiente)}</Cifra>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
