import { Head, Link, router } from '@inertiajs/react';
import { SearchIcon, UserPlusIcon } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

/**
 * Estados de membresia. Los codigos son los de la tabla `estados`: id_estado
 * guarda el CODIGO (100 activa, 101 pausada, 102 vencida), no el id de la fila.
 */
const ESTADOS = {
    100: { texto: 'Al día', clase: 'border-ok/40 bg-ok/5 text-ok' },
    101: { texto: 'Pausada', clase: 'border-warn/40 bg-warn/5 text-warn' },
    102: { texto: 'Vencida', clase: 'border-danger/40 bg-danger/5 text-danger' },
    103: { texto: 'Cancelada', clase: 'border-line bg-surface-2 text-fog' },
};

function Estado({ codigo }) {
    if (!codigo) {
        return <span className="apoyo text-fog">Sin membresía</span>;
    }

    const estado = ESTADOS[codigo] ?? { texto: '—', clase: 'border-line bg-surface-2 text-fog' };

    return (
        <span className={`inline-flex rounded-pill border px-2 py-0.5 text-xs ${estado.clase}`}>
            {estado.texto}
        </span>
    );
}

export default function Index({ clientes, filtros, resumen }) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    // El primer render no debe disparar una peticion: ya llega servida.
    const montado = useRef(false);

    useEffect(() => {
        if (!montado.current) {
            montado.current = true;

            return undefined;
        }

        // Se espera a que deje de escribir: sin esto cada tecla seria una
        // consulta y la lista parpadearia mientras escribe el apellido.
        const temporizador = setTimeout(() => {
            router.get('/panel/clientes', buscar ? { buscar } : {}, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(temporizador);
    }, [buscar]);

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
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-ink transition-opacity hover:opacity-90"
                >
                    <UserPlusIcon className="size-4" aria-hidden="true" />
                    Nuevo cliente
                </Link>
            </header>

            <div className="relative mb-3 max-w-sm">
                <SearchIcon
                    className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-fog"
                    aria-hidden="true"
                />
                <input
                    type="search"
                    value={buscar}
                    onChange={(e) => setBuscar(e.target.value)}
                    placeholder="Buscar por nombre, RUT, correo o celular"
                    aria-label="Buscar clientes"
                    className="w-full rounded-control border border-line bg-surface py-1.5 pr-3 pl-8 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                />
            </div>

            <div className="overflow-x-auto rounded-panel border border-line bg-surface">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b border-line text-left">
                            <th className="rotulo px-3 py-2 font-medium">Socio</th>
                            <th className="rotulo px-3 py-2 font-medium">RUT</th>
                            <th className="rotulo px-3 py-2 font-medium">Contacto</th>
                            <th className="rotulo px-3 py-2 font-medium">Membresía</th>
                            <th className="rotulo px-3 py-2 font-medium">Estado</th>
                            <th className="rotulo px-3 py-2 font-medium">Vence</th>
                        </tr>
                    </thead>
                    <tbody>
                        {clientes.data.length === 0 ? (
                            <tr>
                                <td colSpan={6} className="px-3 py-8 text-center text-fog">
                                    {buscar
                                        ? `Ningún socio coincide con «${buscar}».`
                                        : 'Todavía no hay clientes registrados.'}
                                </td>
                            </tr>
                        ) : (
                            clientes.data.map((cliente) => (
                                <tr
                                    key={cliente.uuid}
                                    className="border-b border-line last:border-0 hover:bg-surface-2"
                                >
                                    <td className="px-3 py-2 font-medium text-chalk">
                                        <Link
                                            href={`/admin/clientes/${cliente.uuid}`}
                                            className="hover:underline"
                                        >
                                            {cliente.nombre}
                                        </Link>
                                    </td>
                                    <td className="px-3 py-2 tabular-nums text-fog">
                                        {cliente.run_pasaporte ?? '—'}
                                    </td>
                                    <td className="px-3 py-2 text-fog">
                                        {cliente.email ?? cliente.celular ?? '—'}
                                    </td>
                                    <td className="px-3 py-2 text-fog">{cliente.membresia ?? '—'}</td>
                                    <td className="px-3 py-2">
                                        <Estado codigo={cliente.id_estado} />
                                    </td>
                                    <td className="px-3 py-2 tabular-nums text-fog">
                                        {cliente.vence ?? '—'}
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {clientes.last_page > 1 ? (
                <nav className="mt-3 flex flex-wrap items-center gap-1" aria-label="Paginación">
                    {clientes.links.map((enlace, i) => (
                        <Link
                            key={i}
                            href={enlace.url ?? '#'}
                            preserveScroll
                            aria-disabled={!enlace.url}
                            aria-current={enlace.active ? 'page' : undefined}
                            className={`rounded-control px-2.5 py-1 text-sm transition-colors ${
                                enlace.active
                                    ? 'bg-surface-2 font-medium text-chalk'
                                    : enlace.url
                                      ? 'text-fog hover:bg-surface-2 hover:text-chalk'
                                      : 'cursor-default text-fog/40'
                            }`}
                            dangerouslySetInnerHTML={{ __html: enlace.label }}
                        />
                    ))}
                </nav>
            ) : null}
        </>
    );
}
