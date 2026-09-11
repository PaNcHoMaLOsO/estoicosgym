import { Head, Link } from '@inertiajs/react';
import { UserPlusIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Estado from '@/components/Estado';
import Paginacion from '@/components/Paginacion';
import Retrato from '@/components/Retrato';
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
                        {filtros.bajas
                            ? `${resumen.bajas} ${resumen.bajas === 1 ? 'socio dado' : 'socios dados'} de baja`
                            : `${resumen.total} socios · ${resumen.activos} al día · ${resumen.pausados} pausados · ${resumen.vencidos} vencidos`}
                    </p>
                </div>

                <Link
                    href="/panel/clientes/crear"
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
                    /* Se mantiene mientras se busca: si no, escribir un nombre
                       devolveria al listado de activos y el socio dado de baja
                       que se estaba buscando desapareceria. */
                    extra={filtros.bajas ? { bajas: 1 } : {}}
                />

                {/* Sin esto, dar de baja a alguien lo hace desaparecer del panel
                    entero y no hay forma de reactivarlo salvo sabiendose la URL
                    de su ficha. Solo se ofrece cuando hay alguno. */}
                {filtros.bajas ? (
                    <Link
                        href="/panel/clientes"
                        className="inline-flex items-center rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        Ver los activos
                    </Link>
                ) : resumen.bajas > 0 ? (
                    <Link
                        href="/panel/clientes?bajas=1"
                        className="inline-flex items-center rounded-control border border-line px-3 py-1.5 text-sm text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                    >
                        Ver {resumen.bajas} {resumen.bajas === 1 ? 'dado' : 'dados'} de baja
                    </Link>
                ) : null}
            </div>

            <Tabla
                columnas={COLUMNAS}
                vacia={clientes.data.length === 0}
                mensajeVacio={
                    filtros.buscar
                        ? `Ningún socio coincide con «${filtros.buscar}».`
                        : filtros.bajas
                          ? 'No hay ningún socio dado de baja.'
                          : 'Todavía no hay clientes registrados.'
                }
            >
                {clientes.data.map((cliente) => (
                    <Fila key={cliente.uuid}>
                        <Celda className="font-medium text-chalk">
                            {/* La cara en la lista, no solo en la ficha: es
                                buscando donde hace falta distinguir entre dos
                                socios que se llaman casi igual. */}
                            <Link
                                href={`/panel/clientes/${cliente.uuid}`}
                                className="flex items-center gap-2 hover:underline"
                            >
                                <Retrato
                                    nombre={cliente.nombre}
                                    foto={cliente.foto}
                                    tamano="sm"
                                />
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
