import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';
import { useState } from 'react';

import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Socio', 'Membresía', 'Método', 'Fecha', 'Total', 'Abonado', 'Debe'];

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

const sinTildes = (t) => String(t ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

export default function Pendientes({ pagos, total, abonado }) {
    // BUSCAR EN LA LISTA: con decenas de cobros abiertos, encontrar a quien
    // llegó al mesón a pagar era recorrerla a ojo.
    const [buscar, setBuscar] = useState('');
    const q = sinTildes(buscar.trim());
    const visibles = q ? pagos.filter((p) => sinTildes(`${p.socio} ${p.membresia ?? ''} ${p.metodo ?? ''}`).includes(q)) : pagos;

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

            {pagos.length > 5 ? (
                <div className="mb-3 flex flex-wrap items-center gap-3">
                    <input
                        type="search"
                        value={buscar}
                        onChange={(e) => setBuscar(e.target.value)}
                        placeholder="Buscar socio o plan"
                        aria-label="Buscar en lo por cobrar"
                        className="w-full max-w-sm rounded-control border border-line bg-surface px-3 py-1.5 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                    />
                    {q ? <span className="apoyo text-fog">{visibles.length} de {pagos.length}</span> : null}
                </div>
            ) : null}

            {/* Ordenados por lo que se debe, de mayor a menor: si hay que
                empezar a llamar por alguien, es por el de arriba. */}
            <Tabla
                columnas={COLUMNAS}
                vacia={visibles.length === 0}
                mensajeVacio={q ? `Nadie con «${buscar}» en lo por cobrar.` : 'No hay nada por cobrar. Todas las membresías están al día.'}
            >
                {visibles.map((p) => (
                    <Fila key={p.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link href={`/panel/inscripciones/${p.uuid}`} className="hover:underline">
                                {p.socio}
                            </Link>
                        </Celda>
                        <Celda>{p.membresia ?? '-'}</Celda>
                        <Celda>{p.metodo ?? '-'}</Celda>
                        <Celda className="tabular-nums">{p.fecha ?? '-'}</Celda>
                        <Cifra>{pesos.format(p.total)}</Cifra>
                        <Cifra>{pesos.format(p.abonado)}</Cifra>
                        <Cifra className="font-medium text-warn">{pesos.format(p.pendiente)}</Cifra>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
