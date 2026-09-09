import { Head, Link } from '@inertiajs/react';
import { UserPlusIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Estado from '@/components/Estado';
import Paginacion from '@/components/Paginacion';
import { Celda, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Socio', 'RUT', 'Contacto', 'Membresía', 'Estado', 'Vence'];

export default function Index({ clientes, filtros, resumen }) {
    return (
        <>
            <Head title="Clientes" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Clientes</h1>
                    <p className="apoyo text-fog">
                        {resumen.total} socios · {resumen.activos} al día · {resumen.pausados} pausados ·{' '}
                        {resumen.vencidos} vencidos
                    </p>
                </div>

                <Link
                    href="/admin/clientes/create"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <UserPlusIcon className="size-4" aria-hidden="true" />
                    Nuevo cliente
                </Link>
            </header>

            <div className="mb-3 flex flex-wrap gap-2">
                <Buscador
                    ruta="/panel/clientes"
                    valor={filtros.buscar}
                    etiqueta="Buscar por nombre, RUT, correo o celular"
                />
            </div>

            <Tabla
                columnas={COLUMNAS}
                vacia={clientes.data.length === 0}
                mensajeVacio={
                    filtros.buscar
                        ? `Ningún socio coincide con «${filtros.buscar}».`
                        : 'Todavía no hay clientes registrados.'
                }
            >
                {clientes.data.map((cliente) => (
                    <Fila key={cliente.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link href={`/admin/clientes/${cliente.uuid}`} className="hover:underline">
                                {cliente.nombre}
                            </Link>
                        </Celda>
                        <Celda className="tabular-nums">{cliente.run_pasaporte ?? '—'}</Celda>
                        <Celda>{cliente.email ?? cliente.celular ?? '—'}</Celda>
                        <Celda>{cliente.membresia ?? '—'}</Celda>
                        <Celda>
                            <Estado codigo={cliente.id_estado} vacio="Sin membresía" />
                        </Celda>
                        <Celda className="tabular-nums">{cliente.vence ?? '—'}</Celda>
                    </Fila>
                ))}
            </Tabla>

            <Paginacion paginador={clientes} />
        </>
    );
}
