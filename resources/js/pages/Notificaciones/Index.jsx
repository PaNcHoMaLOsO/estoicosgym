import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { FileTextIcon, SendIcon, UsersIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Dialogo from '@/components/Dialogo';
import Estado from '@/components/Estado';
import Paginacion from '@/components/Paginacion';
import { Celda, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Destinatario', 'Asunto', 'Tipo', 'Envío', 'Estado', 'Programada', 'Enviada', ''];

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
    // Cual se esta reintentando, para no dejar el boton pulsable dos veces:
    // la segunda pulsacion mandaria el mismo correo otra vez.
    const [enCurso, setEnCurso] = useState(null);

    /*
     * Reenviar SE CONFIRMA; cancelar no.
     *
     * Mandar un correo no se deshace: le llega al socio y ya esta. Cancelar, en
     * cambio, solo evita que salga uno que todavia no ha salido, asi que el
     * peor caso de equivocarse es que haya que volver a mandarlo.
     */
    const [reenviando, setReenviando] = useState(null);

    function actuar(uuid, accion) {
        setEnCurso(uuid);

        router.post(`/panel/notificaciones/${uuid}/${accion}`, {}, {
            preserveScroll: true,
            onFinish: () => setEnCurso(null),
        });
    }

    return (
        <>
            <Head title="Notificaciones" />

            <header className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <h1 className="text-lg font-semibold text-chalk">Notificaciones</h1>

                <div className="flex gap-2">
                <Link
                    href="/panel/notificaciones/plantillas"
                    className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                >
                    <FileTextIcon className="size-4" aria-hidden="true" />
                    Plantillas
                </Link>

                <Link
                    href="/panel/notificaciones/masivo"
                    className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                >
                    <UsersIcon className="size-4" aria-hidden="true" />
                    Aviso a un grupo
                </Link>

                <Link
                    href="/panel/notificaciones/enviar"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <SendIcon className="size-4" aria-hidden="true" />
                    Escribir a un socio
                </Link>
                </div>
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
                    extra={filtros.estado ? { estado: filtros.estado } : undefined}
                    etiqueta="Buscar por correo o asunto"
                />
                {/* Las que no salieron son lo único que pide hacer algo: un toque las deja solas. */}
                <Link
                    href={filtros.estado ? '/panel/notificaciones' : '/panel/notificaciones?estado=fallidas'}
                    className={`inline-flex items-center rounded-control border px-3 py-1.5 text-sm transition-colors ${filtros.estado ? 'border-danger/50 bg-danger/5 text-danger' : 'border-line text-fog hover:text-chalk'}`}
                >
                    {filtros.estado ? 'Viendo solo las fallidas · ver todas' : 'Ver solo las fallidas'}
                </Link>
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
                            <Link href={`/panel/notificaciones/${n.uuid}`} className="hover:underline">
                                {n.socio}
                            </Link>
                            <span className="apoyo block text-fog">{n.email}</span>
                        </Celda>
                        <Celda className="max-w-xs truncate">{n.asunto}</Celda>
                        <Celda>{n.tipo ?? '-'}</Celda>
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
                        <Celda className="tabular-nums">{n.programada ?? '-'}</Celda>
                        <Celda className="tabular-nums">{n.enviada ?? '-'}</Celda>
                        <Celda className="text-right">
                            {/* Reintentar una que no salio, o parar una que
                                todavia no ha salido. Sobre una ya enviada no
                                hay nada que hacer: un correo no se recoge. */}
                            {n.puede_reenviar ? (
                                <button
                                    type="button"
                                    onClick={() => setReenviando(n)}
                                    disabled={enCurso === n.uuid}
                                    className="apoyo text-fog transition-colors hover:text-chalk disabled:opacity-50"
                                >
                                    {enCurso === n.uuid ? 'Enviando…' : 'Reintentar'}
                                </button>
                            ) : n.puede_cancelar ? (
                                <button
                                    type="button"
                                    onClick={() => actuar(n.uuid, 'cancelar')}
                                    disabled={enCurso === n.uuid}
                                    className="apoyo text-fog transition-colors hover:text-danger disabled:opacity-50"
                                >
                                    No enviarlo
                                </button>
                            ) : null}
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <Paginacion paginador={notificaciones} />

            {/* Dice A QUIEN y QUE, no «¿seguro?»: el error de este boton es
                pulsar en la fila de al lado, y ahi es donde se nota. */}
            <Dialogo
                abierto={reenviando !== null}
                alCerrar={() => setReenviando(null)}
                titulo="Volver a mandar el correo"
                descripcion={
                    reenviando
                        ? `Se le manda otra vez a ${reenviando.email}. Un correo no se puede recoger.`
                        : ''
                }
                accion={reenviando ? `/panel/notificaciones/${reenviando.uuid}/reenviar` : ''}
                via="inertia"
                metodo="post"
                etiquetaConfirmar="Mandarlo"
            >
                {reenviando ? (
                    <div className="rounded-control border border-line bg-surface-2 px-3 py-2">
                        <p className="text-sm text-chalk">{reenviando.asunto}</p>
                        <p className="apoyo text-fog">para {reenviando.socio}</p>
                    </div>
                ) : null}
            </Dialogo>
        </>
    );
}
