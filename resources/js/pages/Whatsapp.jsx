import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { InfoIcon, MessageCircleIcon, SendIcon } from 'lucide-react';

import Retrato from '@/components/Retrato';
import { celularLegible } from '@/lib/contacto';

/**
 * El chat de WhatsApp dentro del panel. POR AHORA, UNA MAQUETA.
 *
 * No manda ni recibe nada: las conversaciones son de mentira, armadas con
 * socios de verdad, para poder decidir si esta pantalla sirve antes de elegir
 * por dónde se conecta WhatsApp —la API oficial de Meta o la vía no oficial—.
 *
 * SE DICE EN TODO MOMENTO QUE NO MANDA. Una maqueta que parezca funcionar es
 * peor que no tenerla: alguien escribiría un mensaje creyendo que salió, y el
 * socio nunca sabría que lo estaban buscando.
 */
export default function Whatsapp({ conversaciones, plantillas, esMaqueta }) {
    const [abierta, setAbierta] = useState(conversaciones[0]?.id ?? null);
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

    return (
        <>
            <Head title="WhatsApp" />

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">WhatsApp</h1>
                <p className="apoyo text-fog">Escribirle a un socio sin salir del panel</p>
            </header>

            {esMaqueta ? (
                <div className="mb-4 flex items-start gap-2 rounded-panel border border-warn/40 bg-warn/5 px-4 py-3">
                    <InfoIcon className="mt-0.5 size-4 shrink-0 text-warn" aria-hidden="true" />
                    <p className="text-sm text-warn">
                        Esto es una maqueta para decidir: <span className="font-medium">no manda ni recibe nada</span>.
                        Las conversaciones son de mentira, con socios de verdad. Cuando elijas por dónde se conecta
                        WhatsApp, esta misma pantalla empieza a funcionar.
                    </p>
                </div>
            ) : null}

            <div className="grid gap-3 lg:grid-cols-[20rem_1fr]">
                {/* La lista: a quién hay algo que decirle, y quién no ha contestado. */}
                <section className="overflow-hidden rounded-panel border border-line bg-surface">
                    <h2 className="rotulo border-b border-line px-3 py-2">Conversaciones</h2>

                    <ul className="max-h-[32rem] divide-y divide-line overflow-y-auto">
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
                </section>

                {/* La conversación, con el socio a un clic: lo que se mira antes
                    de escribirle es su plan y cuánto debe. */}
                <section className="flex min-h-[32rem] flex-col overflow-hidden rounded-panel border border-line bg-surface">
                    {conversacion ? (
                        <>
                            <header className="flex flex-wrap items-center justify-between gap-2 border-b border-line px-4 py-2.5">
                                <div className="min-w-0">
                                    <Link
                                        href={`/panel/clientes/${conversacion.socio_uuid}`}
                                        className="text-sm font-medium text-chalk hover:underline"
                                    >
                                        {conversacion.nombre}
                                    </Link>
                                    <p className="apoyo tabular-nums text-fog">
                                        {celularLegible(conversacion.celular)} · {conversacion.motivo}
                                    </p>
                                </div>

                                <Link
                                    href={`/panel/clientes/${conversacion.socio_uuid}`}
                                    className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                                >
                                    Abrir su ficha
                                </Link>
                            </header>

                            <div className="flex-1 space-y-2 overflow-y-auto p-4">
                                {conversacion.mensajes.map((m, i) => (
                                    <div key={i} className={`flex ${m.mio ? 'justify-end' : 'justify-start'}`}>
                                        <div
                                            className={`max-w-[80%] rounded-panel px-3 py-2 text-sm ${
                                                m.mio
                                                    ? 'bg-[#25D366]/15 text-chalk'
                                                    : 'border border-line bg-surface-2 text-chalk'
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

                            {/* LO QUE SE ESCRIBE VEINTE VECES AL MES, en un clic.
                                Aquí está el ahorro de verdad, no en el chat. */}
                            <div className="border-t border-line px-4 py-2">
                                <p className="apoyo mb-1.5 text-fog">Mensajes de siempre</p>
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

                            <form
                                onSubmit={(e) => e.preventDefault()}
                                className="flex items-end gap-2 border-t border-line p-3"
                            >
                                <textarea
                                    value={texto}
                                    onChange={(e) => setTexto(e.target.value)}
                                    rows={2}
                                    placeholder="Escribe el mensaje…"
                                    className="min-w-0 flex-1 rounded-control border border-line bg-surface-2 px-3 py-2 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                                />
                                <button
                                    type="submit"
                                    disabled
                                    title="La maqueta no manda mensajes"
                                    className="inline-flex shrink-0 items-center gap-1.5 rounded-control bg-[#25D366] px-4 py-2 text-sm font-medium text-black opacity-50"
                                >
                                    <SendIcon className="size-4" aria-hidden="true" />
                                    Enviar
                                </button>
                            </form>
                        </>
                    ) : (
                        <div className="flex flex-1 flex-col items-center justify-center gap-2 p-10 text-center">
                            <MessageCircleIcon className="size-8 text-fog" aria-hidden="true" />
                            <p className="text-sm text-fog">
                                No hay socios con membresías por vencer estos días, así que la maqueta no tiene con
                                quién armar conversaciones.
                            </p>
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}
