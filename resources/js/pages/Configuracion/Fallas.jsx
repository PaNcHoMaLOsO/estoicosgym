import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { CheckIcon, ChevronDownIcon, MonitorIcon, RotateCcwIcon, ServerIcon, TrashIcon } from 'lucide-react';

import Paginacion from '@/components/Paginacion';

/**
 * El registro de fallas: lo que salió mal en el sistema.
 *
 * Cada falla sale una vez, con cuántas veces pasó: la misma repetida no llena
 * la lista. Se abre para ver dónde fue y la traza. «Resuelta» la saca de la
 * lista; si vuelve a pasar, aparece de nuevo sola.
 */
const ORIGEN = {
    servidor: { Icono: ServerIcon, texto: 'Servidor' },
    navegador: { Icono: MonitorIcon, texto: 'Navegador' },
};

function Filtro({ href, activo, children }) {
    return (
        <Link
            href={href}
            preserveScroll
            className={`rounded-control px-3 py-1.5 text-sm transition-colors ${
                activo ? 'bg-surface-2 font-medium text-chalk' : 'text-fog hover:text-chalk'
            }`}
        >
            {children}
        </Link>
    );
}

function Falla({ falla }) {
    const [abierta, setAbierta] = useState(false);
    const { Icono, texto } = ORIGEN[falla.origen] ?? ORIGEN.servidor;

    return (
        <li>
            <button
                type="button"
                onClick={() => setAbierta((v) => ! v)}
                aria-expanded={abierta}
                className="flex w-full items-start gap-3 px-5 py-4 text-left transition-colors hover:bg-surface-2"
            >
                <Icono className={`mt-0.5 size-4 shrink-0 ${falla.resuelta ? 'text-fog' : 'text-danger'}`} aria-label={texto} />

                <span className="min-w-0 flex-1">
                    <span className={`line-clamp-2 text-sm ${falla.resuelta ? 'text-fog' : 'text-chalk'}`}>
                        {falla.tipo ? <span className="font-medium">{falla.tipo}: </span> : null}
                        {falla.mensaje}
                    </span>
                    <span className="apoyo mt-1 block truncate text-fog">
                        {[falla.lugar, falla.url ? `${falla.metodo ?? ''} ${falla.url}`.trim() : null, falla.hace].filter(Boolean).join(' · ')}
                    </span>
                </span>

                <span className="flex shrink-0 items-center gap-3">
                    {falla.veces > 1 ? (
                        <span className="rounded-pill bg-surface-2 px-2 py-0.5 text-xs tabular-nums text-chalk" title="Veces que pasó">
                            ×{falla.veces}
                        </span>
                    ) : null}
                    <ChevronDownIcon className={`size-4 text-fog transition-transform ${abierta ? 'rotate-180' : ''}`} aria-hidden="true" />
                </span>
            </button>

            {abierta ? (
                <div className="space-y-4 border-t border-line bg-surface-2/40 px-5 py-4 pl-12">
                    <dl className="grid gap-x-6 gap-y-1.5 text-sm sm:grid-cols-[9rem_minmax(0,1fr)]">
                        <dt className="text-fog">Mensaje</dt>
                        <dd className="break-words text-chalk">{falla.mensaje}</dd>
                        {falla.tipo_completo ? (
                            <>
                                <dt className="text-fog">Tipo</dt>
                                <dd className="break-all text-chalk">{falla.tipo_completo}</dd>
                            </>
                        ) : null}
                        {falla.lugar ? (
                            <>
                                <dt className="text-fog">Dónde</dt>
                                <dd className="break-all font-mono text-xs text-chalk">{falla.lugar}</dd>
                            </>
                        ) : null}
                        <dt className="text-fog">Pantalla o ruta</dt>
                        <dd className="break-all text-chalk">{`${falla.metodo ?? ''} ${falla.url ?? '-'}`.trim()}</dd>
                        <dt className="text-fog">Quién</dt>
                        <dd className="text-chalk">{falla.usuario ?? 'sin sesión'}</dd>
                        <dt className="text-fog">Veces</dt>
                        <dd className="text-chalk tabular-nums">
                            {falla.veces} · primera {falla.primera_vez} · última {falla.ultima_vez}
                        </dd>
                    </dl>

                    {falla.traza ? (
                        <details>
                            <summary className="cursor-pointer text-sm text-fog hover:text-chalk">Traza</summary>
                            <pre className="mt-2 max-h-72 overflow-auto rounded-control border border-line bg-surface p-3 font-mono text-[11px] leading-relaxed text-fog">
                                {falla.traza}
                            </pre>
                        </details>
                    ) : null}

                    {falla.contexto ? (
                        <details>
                            <summary className="cursor-pointer text-sm text-fog hover:text-chalk">Datos</summary>
                            <pre className="mt-2 max-h-60 overflow-auto rounded-control border border-line bg-surface p-3 font-mono text-[11px] leading-relaxed text-fog">
                                {JSON.stringify(falla.contexto, null, 2)}
                            </pre>
                        </details>
                    ) : null}

                    <div className="flex flex-wrap gap-2">
                        <button
                            type="button"
                            onClick={() => router.patch(`/panel/fallas/${falla.id}/resolver`, {}, { preserveScroll: true })}
                            className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                        >
                            {falla.resuelta ? <RotateCcwIcon className="size-4" aria-hidden="true" /> : <CheckIcon className="size-4" aria-hidden="true" />}
                            {falla.resuelta ? 'Reabrir' : 'Marcar resuelta'}
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                if (window.confirm('¿Borrar esta falla del registro?')) {
                                    router.delete(`/panel/fallas/${falla.id}`, { preserveScroll: true });
                                }
                            }}
                            className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-fog transition-colors hover:bg-surface-2 hover:text-danger"
                        >
                            <TrashIcon className="size-4" aria-hidden="true" />
                            Borrar
                        </button>
                    </div>
                </div>
            ) : null}
        </li>
    );
}

export default function Fallas({ fallas, filtros, cuentas }) {
    const con = (cambios) => {
        const q = new URLSearchParams({ estado: filtros.estado, ...(filtros.origen ? { origen: filtros.origen } : {}), ...cambios });

        [...q.keys()].forEach((k) => (q.get(k) ? null : q.delete(k)));

        return `/panel/fallas?${q.toString()}`;
    };

    return (
        <>
            <Head title="Registro de fallas" />

            <header className="mb-5">
                <h1 className="text-xl font-semibold text-chalk">Registro de fallas</h1>
                <p className="mt-1 text-sm text-fog">
                    {cuentas.abiertas === 0
                        ? 'No hay fallas sin resolver.'
                        : `${cuentas.abiertas} sin resolver${cuentas.hoy > 0 ? `, ${cuentas.hoy} ${cuentas.hoy === 1 ? 'pasó' : 'pasaron'} hoy` : ''}.`}
                </p>
            </header>

            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div className="inline-flex rounded-control border border-line p-0.5">
                    <Filtro href={con({ estado: 'abiertas' })} activo={filtros.estado === 'abiertas'}>
                        Sin resolver {cuentas.abiertas > 0 ? `(${cuentas.abiertas})` : ''}
                    </Filtro>
                    <Filtro href={con({ estado: 'resueltas' })} activo={filtros.estado === 'resueltas'}>
                        Resueltas
                    </Filtro>
                    <Filtro href={con({ estado: 'todas' })} activo={filtros.estado === 'todas'}>
                        Todas
                    </Filtro>
                </div>

                <div className="inline-flex rounded-control border border-line p-0.5">
                    <Filtro href={con({ origen: '' })} activo={! filtros.origen}>
                        Todo
                    </Filtro>
                    <Filtro href={con({ origen: 'servidor' })} activo={filtros.origen === 'servidor'}>
                        Servidor
                    </Filtro>
                    <Filtro href={con({ origen: 'navegador' })} activo={filtros.origen === 'navegador'}>
                        Navegador
                    </Filtro>
                </div>
            </div>

            {fallas.data.length === 0 ? (
                <p className="flex items-center gap-2 rounded-panel border border-line bg-surface px-5 py-4 text-sm text-chalk">
                    <CheckIcon className="size-4 text-ok" aria-hidden="true" />
                    {filtros.estado === 'abiertas' ? 'Nada pendiente: no hay fallas sin resolver.' : 'No hay fallas con este filtro.'}
                </p>
            ) : (
                <ul className="divide-y divide-line overflow-hidden rounded-panel border border-line bg-surface">
                    {fallas.data.map((f) => (
                        <Falla key={f.id} falla={f} />
                    ))}
                </ul>
            )}

            <div className="mt-4">
                <Paginacion paginador={fallas} />
            </div>
        </>
    );
}
