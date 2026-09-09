import { Head, Link } from '@inertiajs/react';
import { SendIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Estado from '@/components/Estado';
import Paginacion from '@/components/Paginacion';
import { Celda, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Destinatario', 'Asunto', 'Tipo', 'Envío', 'Estado', 'Programada', 'Enviada'];

function Cabecera({ etiqueta, valor, destacada = false }) {
    return (
        <div
            className={`rounded-panel border p-3 ${
                destacada && valor > 0 ? 'border-danger/40 bg-danger/5' : 'border-line bg-surface'
            }`}
        >
            <p className="rotulo">{etiqueta}</p>
            <p
                className={`mt-0.5 text-lg font-semibold tabular-nums ${
                    destacada && valor > 0 ? 'text-danger' : 'text-chalk'
                }`}
            >
                {valor}
            </p>
        </div>
    );
}

export default function Index({ notificaciones, filtros, resumen }) {
    return (
        <>
            <Head title="Notificaciones" />

            <header className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <h1 className="text-lg font-semibold text-chalk">Notificaciones</h1>

                <Link
                    href="/admin/notificaciones/enviar-cliente"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <SendIcon className="size-4" aria-hidden="true" />
                    Enviar a un cliente
                </Link>
            </header>

            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <Cabecera etiqueta="Pendientes" valor={resumen.pendientes} />
                <Cabecera etiqueta="Enviadas" valor={resumen.enviadas} />
                {/* Una fallida hay que reintentarla: es lo unico que pide accion. */}
                <Cabecera etiqueta="Fallidas" valor={resumen.fallidas} destacada />
            </div>

            <div className="mb-3 flex flex-wrap gap-2">
                <Buscador
                    ruta="/panel/notificaciones"
                    valor={filtros.buscar}
                    etiqueta="Buscar por correo o asunto"
                />
            </div>

            <Tabla
                columnas={COLUMNAS}
                vacia={notificaciones.data.length === 0}
                mensajeVacio={
                    filtros.buscar
                        ? `Ninguna notificación coincide con «${filtros.buscar}».`
                        : 'Todavía no se ha enviado ninguna notificación.'
                }
            >
                {notificaciones.data.map((n) => (
                    <Fila key={n.uuid}>
                        <Celda className="text-chalk">
                            <Link href={`/admin/notificaciones/${n.uuid}`} className="hover:underline">
                                {n.socio}
                            </Link>
                            <span className="apoyo block text-fog">{n.email}</span>
                        </Celda>
                        <Celda className="max-w-xs truncate">{n.asunto}</Celda>
                        <Celda>{n.tipo ?? '—'}</Celda>
                        <Celda>{n.envio}</Celda>
                        <Celda>
                            <Estado codigo={n.id_estado} />
                            {/* El motivo del fallo va bajo el estado y no en su
                                propia columna: solo lo tienen las fallidas. */}
                            {n.error ? (
                                <span className="apoyo mt-0.5 block text-danger">
                                    {n.error} ({n.intentos}/{n.max_intentos})
                                </span>
                            ) : null}
                        </Celda>
                        <Celda className="tabular-nums">{n.programada ?? '—'}</Celda>
                        <Celda className="tabular-nums">{n.enviada ?? '—'}</Celda>
                    </Fila>
                ))}
            </Tabla>

            <Paginacion paginador={notificaciones} />
        </>
    );
}
