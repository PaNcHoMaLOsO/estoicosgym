import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeftIcon, InfoIcon, MessageCircleIcon, SendIcon } from 'lucide-react';

import Retrato from '@/components/Retrato';
import { celularLegible } from '@/lib/contacto';

/**
 * El chat con los socios: la lista y la conversación abierta.
 *
 * DOS SITIOS, UNA PIEZA. Se ve en la columna lateral del resumen —que es donde
 * se mira todo el día— y a pantalla completa en su propia sección. Con dos
 * copias, la primera corrección dejaría una distinta de la otra.
 *
 * `compacto` es lo único que cambia entre las dos: en la lateral no caben la
 * lista y la conversación al mismo tiempo, así que se ve una u otra.
 *
 * POR AHORA ES UNA MAQUETA y lo dice: el botón de enviar está apagado. Una
 * maqueta que parezca funcionar es peor que no tenerla, porque alguien
 * escribiría creyendo que salió y el socio nunca sabría que lo buscaban.
 */
export default function ChatWhatsapp({ conversaciones, plantillas, esMaqueta = true, compacto = false }) {
    const [abierta, setAbierta] = useState(compacto ? null : conversaciones[0]?.id ?? null);
    const [texto, setTexto] = useState('');

    const conversacion = conversaciones.find((c) => c.id === abierta) ?? null;

    /** La plantilla, con los huecos rellenos con lo que se sabe del socio. */
    function usar(plantilla) {
        if (! conversacion) {
            return;
        }

        setTexto(
            plantilla.texto
                .replace('{nombre}', conversacion.nombre.split(' ')[0])
                .replace('{plan}', 'membresía')
                .replace('{vence}', conversacion.motivo.toLowerCase()),
        );
    }

    const sinResponder = conversaciones.filter((c) => c.sin_responder).length;

    const aviso = esMaqueta ? (
        <p className="flex items-start gap-1.5 border-b border-line bg-warn/5 px-3 py-2 text-xs text-warn">
            <InfoIcon className="mt-0.5 size-3.5 shrink-0" aria-hidden="true" />
            Maqueta para decidir: todavía no manda ni recibe nada.
        </p>
    ) : null;

    const lista = (
        <ul className={`divide-y divide-line overflow-y-auto ${compacto ? 'max-h-80' : 'max-h-[32rem]'}`}>
            {conversaciones.map((c) => {
                const ultimo = c.mensajes[c.mensajes.length - 1];

                return (
                    <li key={c.id}>
                        <button
                            type="button"
                            onClick={() => setAbierta(c.id)}
                            className={`flex w-full items-start gap-2.5 px-3 py-2.5 text-left transition-colors ${
                                c.id === abierta ? 'bg-surface-2' : 'hover:bg-surface-2/60'
                            }`}
                        >
                            <Retrato nombre={c.nombre} tamano="sm" />
                            <span className="min-w-0 flex-1">
                                <span className="flex items-baseline justify-between gap-2">
                                    <span className="truncate text-sm font-medium text-chalk">{c.nombre}</span>
                                    <span className="apoyo shrink-0 text-fog">{ultimo?.hora?.slice(0, 5)}</span>
                                </span>
                                <span className="apoyo block truncate text-fog">{ultimo?.texto}</span>
                                <span className={`apoyo block ${c.urgente ? 'text-danger' : 'text-warn'}`}>
                                    {c.motivo}
                                    {c.sin_responder ? ' · sin responder' : ''}
                                </span>
                            </span>
                        </button>
                    </li>
                );
            })}
        </ul>
    );

    const conversacionAbierta = conversacion ? (
        <>
            <header className="flex flex-wrap items-center justify-between gap-2 border-b border-line px-3 py-2">
                <div className="flex min-w-0 items-center gap-2">
                    {compacto ? (
                        <button
                            type="button"
                            onClick={() => setAbierta(null)}
                            aria-label="Volver a las conversaciones"
                            className="rounded-control p-1 text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                        >
                            <ArrowLeftIcon className="size-4" aria-hidden="true" />
                        </button>
                    ) : null}

                    <div className="min-w-0">
                        <Link
                            href={`/panel/clientes/${conversacion.socio_uuid}`}
                            className="truncate text-sm font-medium text-chalk hover:underline"
                        >
                            {conversacion.nombre}
                        </Link>
                        <p className="apoyo truncate tabular-nums text-fog">
                            {celularLegible(conversacion.celular)} · {conversacion.motivo}
                        </p>
                    </div>
                </div>

                {compacto ? null : (
                    <Link
                        href={`/panel/clientes/${conversacion.socio_uuid}`}
                        className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                    >
                        Abrir su ficha
                    </Link>
                )}
            </header>

            <div className={`flex-1 space-y-2 overflow-y-auto p-3 ${compacto ? 'max-h-64' : ''}`}>
                {conversacion.mensajes.map((m, i) => (
                    <div key={i} className={`flex ${m.mio ? 'justify-end' : 'justify-start'}`}>
                        <div
                            className={`max-w-[85%] rounded-panel px-3 py-2 text-sm ${
                                m.mio ? 'bg-[#25D366]/15 text-chalk' : 'border border-line bg-surface-2 text-chalk'
                            }`}
                        >
                            <p className="whitespace-pre-line">{m.texto}</p>
                            <p className="apoyo mt-1 text-right text-fog">
                                {m.hora}
                                {m.estado ? ` · ${m.estado}` : ''}
                            </p>
                        </div>
                    </div>
                ))}
            </div>

            {/* LO QUE SE ESCRIBE VEINTE VECES AL MES, en un clic. Aquí está el
                ahorro de verdad, no en poder chatear. */}
            <div className="border-t border-line px-3 py-2">
                <div className="flex flex-wrap gap-1.5">
                    {plantillas.map((p) => (
                        <button
                            key={p.nombre}
                            type="button"
                            onClick={() => usar(p)}
                            className="rounded-pill border border-line px-2.5 py-1 text-xs text-fog transition-colors hover:border-line-strong hover:text-chalk"
                        >
                            {p.nombre}
                        </button>
                    ))}
                </div>
            </div>

            <form onSubmit={(e) => e.preventDefault()} className="flex items-end gap-2 border-t border-line p-2">
                <textarea
                    value={texto}
                    onChange={(e) => setTexto(e.target.value)}
                    rows={compacto ? 2 : 2}
                    placeholder="Escribe el mensaje…"
                    className="min-w-0 flex-1 rounded-control border border-line bg-surface-2 px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                />
                <button
                    type="submit"
                    disabled
                    title="La maqueta todavía no manda mensajes"
                    className="inline-flex shrink-0 items-center gap-1.5 rounded-control bg-[#25D366] px-3 py-2 text-sm font-medium text-black opacity-50"
                >
                    <SendIcon className="size-4" aria-hidden="true" />
                    {compacto ? '' : 'Enviar'}
                </button>
            </form>
        </>
    ) : null;

    if (compacto) {
        return (
            <section className="flex flex-col overflow-hidden rounded-panel border border-line bg-surface">
                <div className="flex items-baseline justify-between gap-2 border-b border-line px-3 py-2">
                    <h2 className="rotulo">
                        WhatsApp
                        {sinResponder > 0 ? (
                            <span className="ml-2 text-base font-semibold tabular-nums text-warn">{sinResponder}</span>
                        ) : null}
                    </h2>
                    <Link href="/panel/whatsapp" className="apoyo text-fog transition-colors hover:text-chalk">
                        Ver todo
                    </Link>
                </div>

                {aviso}

                {conversaciones.length === 0 ? (
                    <p className="apoyo p-3 text-fog">No hay a quién escribirle estos días.</p>
                ) : conversacion ? (
                    conversacionAbierta
                ) : (
                    lista
                )}
            </section>
        );
    }

    return (
        <div className="grid gap-3 lg:grid-cols-[20rem_1fr]">
            <section className="overflow-hidden rounded-panel border border-line bg-surface">
                <h2 className="rotulo border-b border-line px-3 py-2">Conversaciones</h2>
                {aviso}
                {conversaciones.length === 0 ? (
                    <p className="apoyo p-3 text-fog">No hay a quién escribirle estos días.</p>
                ) : (
                    lista
                )}
            </section>

            <section className="flex min-h-[32rem] flex-col overflow-hidden rounded-panel border border-line bg-surface">
                {conversacion ? (
                    conversacionAbierta
                ) : (
                    <div className="flex flex-1 flex-col items-center justify-center gap-2 p-10 text-center">
                        <MessageCircleIcon className="size-8 text-fog" aria-hidden="true" />
                        <p className="text-sm text-fog">Elige una conversación de la izquierda.</p>
                    </div>
                )}
            </section>
        </div>
    );
}
