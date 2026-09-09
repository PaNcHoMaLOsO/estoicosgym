import { Head, Link } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Estado from '@/components/Estado';
import Paginacion from '@/components/Paginacion';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Socio', 'Fecha', 'Método', 'Tipo', 'Estado', 'Total', 'Abonado', 'Pendiente'];

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Cabecera({ etiqueta, valor, destacada = false }) {
    return (
        <div
            className={`rounded-panel border p-3 ${
                destacada && valor > 0 ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface'
            }`}
        >
            <p className="rotulo">{etiqueta}</p>
            <p
                className={`mt-0.5 text-lg font-semibold tabular-nums ${
                    destacada && valor > 0 ? 'text-warn' : 'text-chalk'
                }`}
            >
                {typeof valor === 'number' && etiqueta !== 'Completados' ? pesos.format(valor) : valor}
            </p>
        </div>
    );
}

export default function Index({ pagos, filtros, resumen }) {
    return (
        <>
            <Head title="Pagos" />

            <header className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <h1 className="text-lg font-semibold text-chalk">Pagos</h1>

                <Link
                    href="/admin/pagos/create"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Registrar pago
                </Link>
            </header>

            <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Cabecera etiqueta="Recaudado hoy" valor={resumen.recaudado_hoy} />
                <Cabecera etiqueta="Recaudado este mes" valor={resumen.recaudado_mes} />
                {/* La unica cifra accionable de las cuatro. */}
                <Cabecera etiqueta="Por cobrar" valor={resumen.por_cobrar} destacada />
                <Cabecera etiqueta="Completados" valor={resumen.completados} />
            </div>

            <div className="mb-3 flex flex-wrap gap-2">
                <Buscador ruta="/panel/pagos" valor={filtros.buscar} etiqueta="Buscar por socio o RUT" />
            </div>

            <Tabla
                columnas={COLUMNAS}
                vacia={pagos.data.length === 0}
                mensajeVacio={
                    filtros.buscar
                        ? `Ningún pago coincide con «${filtros.buscar}».`
                        : 'Todavía no hay pagos registrados.'
                }
            >
                {pagos.data.map((pago) => (
                    <Fila key={pago.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link href={`/admin/pagos/${pago.uuid}`} className="hover:underline">
                                {pago.socio}
                            </Link>
                        </Celda>
                        <Celda className="tabular-nums">{pago.fecha ?? '—'}</Celda>
                        <Celda>{pago.metodo ?? '—'}</Celda>
                        <Celda>{pago.tipo}</Celda>
                        <Celda>
                            <Estado codigo={pago.id_estado} />
                        </Celda>
                        <Cifra>{pesos.format(pago.total)}</Cifra>
                        <Cifra className="text-chalk">{pesos.format(pago.abonado)}</Cifra>
                        <Cifra className={pago.pendiente > 0 ? 'font-medium text-warn' : ''}>
                            {pago.pendiente > 0 ? pesos.format(pago.pendiente) : '—'}
                        </Cifra>
                    </Fila>
                ))}
            </Tabla>

            <Paginacion paginador={pagos} />
        </>
    );
}
