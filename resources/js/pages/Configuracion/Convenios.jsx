import { Head, Link } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';

import Externo from '@/components/Externo';
import Activo from '@/components/Activo';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Convenio', 'Tipo', 'Descuento', 'Contacto', 'Socios', 'Estado'];

export default function Convenios({ convenios }) {
    return (
        <>
            <Head title="Convenios" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Convenios</h1>
                    <p className="apoyo text-fog">Empresas e instituciones con descuento</p>
                </div>

                <Externo
                    href="/admin/convenios/create"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nuevo convenio
                </Externo>
            </header>

            <Tabla
                columnas={COLUMNAS}
                vacia={convenios.length === 0}
                mensajeVacio="Todavía no hay convenios registrados."
            >
                {convenios.map((convenio) => (
                    <Fila key={convenio.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link href={`/panel/convenios/${convenio.uuid}`} className="hover:underline">
                                {convenio.nombre}
                            </Link>
                        </Celda>
                        <Celda>{convenio.tipo ?? '—'}</Celda>
                        <Celda className="tabular-nums">{convenio.descuento}</Celda>
                        <Celda>{convenio.contacto ?? '—'}</Celda>
                        <Cifra>{convenio.clientes}</Cifra>
                        <Celda>
                            <Activo valor={convenio.activo} />
                        </Celda>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
