import { Head, Link } from '@inertiajs/react';
import { AlertTriangleIcon, ArrowLeftIcon } from 'lucide-react';

import Estado from '@/components/Estado';

function Bloque({ titulo, children }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo mb-3">{titulo}</h2>
            {children}
        </section>
    );
}

function Dato({ etiqueta, children }) {
    return (
        <div>
            <dt className="rotulo">{etiqueta}</dt>
            <dd className="mt-0.5 text-sm text-chalk">{children || <span className="text-fog">—</span>}</dd>
        </div>
    );
}

export default function Ficha({ notificacion, socio, logs }) {
    const fallida = notificacion.id_estado === 602;

    return (
        <>
            <Head title={notificacion.asunto ?? 'Notificación'} />

            <header className="mb-5">
                <Link
                    href="/panel/notificaciones"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Notificaciones
                </Link>

                <h1 className="mt-1 flex flex-wrap items-center gap-2 text-lg font-semibold text-chalk">
                    {notificacion.asunto ?? 'Sin asunto'}
                    <Estado codigo={notificacion.id_estado} />
                </h1>
                <p className="apoyo text-fog">
                    {socio ? (
                        <Link href={`/panel/clientes/${socio.uuid}`} className="text-chalk hover:underline">
                            {socio.nombre}
                        </Link>
                    ) : (
                        'Sin socio asociado'
                    )}
                    {' · '}
                    {notificacion.destino}
                </p>
            </header>

            {/* Lo primero si falló: por qué. Es la razón por la que se abre
                esta pantalla nueve de cada diez veces. */}
            {fallida && notificacion.error ? (
                <div className="mb-4 flex items-start gap-2 rounded-panel border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
                    <AlertTriangleIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <div>
                        <p className="font-medium">No se pudo enviar</p>
                        <p className="apoyo">{notificacion.error}</p>
                        <p className="apoyo mt-1">
                            Intentos: {notificacion.intentos} de {notificacion.max_intentos}
                        </p>
                    </div>
                </div>
            ) : null}

            <div className="grid gap-3 lg:grid-cols-3">
                <div className="space-y-3">
                    <Bloque titulo="Detalle">
                        <dl className="space-y-3">
                            <Dato etiqueta="Tipo">{notificacion.tipo}</Dato>
                            <Dato etiqueta="Origen">{notificacion.envio}</Dato>
                            <Dato etiqueta="Programada">{notificacion.programada}</Dato>
                            <Dato etiqueta="Enviada">{notificacion.enviada}</Dato>
                            {! fallida && notificacion.intentos > 1 ? (
                                <Dato etiqueta="Intentos">
                                    {notificacion.intentos} de {notificacion.max_intentos}
                                </Dato>
                            ) : null}
                        </dl>
                    </Bloque>

                    {notificacion.nota ? (
                        <Bloque titulo="Nota añadida al enviar">
                            <p className="text-sm whitespace-pre-line text-fog">{notificacion.nota}</p>
                        </Bloque>
                    ) : null}

                    {/* Lo que se mira cuando un socio dice que no le llegó nada. */}
                    <Bloque titulo="Qué le fue pasando">
                        {logs.length === 0 ? (
                            <p className="apoyo text-fog">Sin registro de intentos.</p>
                        ) : (
                            <ol className="space-y-2">
                                {logs.map((l, i) => (
                                    <li
                                        key={i}
                                        className="border-b border-line pb-2 text-sm last:border-0 last:pb-0"
                                    >
                                        <div className="flex items-baseline justify-between gap-2">
                                            <span className="text-chalk">{l.accion}</span>
                                            <span className="apoyo shrink-0 tabular-nums text-fog">
                                                {l.cuando ?? '—'}
                                            </span>
                                        </div>
                                        {l.detalle ? (
                                            <p className="apoyo text-fog">{l.detalle}</p>
                                        ) : null}
                                    </li>
                                ))}
                            </ol>
                        )}
                    </Bloque>
                </div>

                <div className="lg:col-span-2">
                    <Bloque titulo="Lo que se envió">
                        {/* El cuerpo es el HTML del correo, tal como lo recibe el
                            socio. Va dentro de un iframe con sandbox: es contenido
                            con estilos propios y, si se inyectara en la página,
                            sus reglas se comerían las del panel. */}
                        {notificacion.contenido ? (
                            <iframe
                                title="Vista previa del correo"
                                sandbox=""
                                srcDoc={notificacion.contenido}
                                className="h-[32rem] w-full rounded-control border border-line bg-white"
                            />
                        ) : (
                            <p className="apoyo py-6 text-center text-fog">
                                Esta notificación no tiene contenido guardado.
                            </p>
                        )}
                    </Bloque>
                </div>
            </div>
        </>
    );
}
