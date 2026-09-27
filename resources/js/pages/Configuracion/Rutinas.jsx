import { Head, Link, router } from '@inertiajs/react';
import { CopyIcon, ExternalLinkIcon, PencilIcon, PlusIcon, TrashIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import { confirmar } from '@/components/Confirmar';

/**
 * Las rutinas de la sala: las que ve quien escanea el QR «Qué entrenar hoy».
 *
 * Agrupadas por objetivo, que es la primera pregunta que responde quien las
 * busca. Cada una se edita entera, se duplica para armar otra variante (con
 * más días, para otro nivel) o se apaga sin borrarla.
 */
export default function Rutinas({ rutinas, objetivos, ejercicios }) {
    async function eliminar(rutina) {
        if (await confirmar({ titulo: `¿Eliminar «${rutina.nombre}»?`, mensaje: 'Se borran sus días y ejercicios. No se puede deshacer.', confirmar: 'Eliminar', peligrosa: true })) {
            router.delete(`/panel/rutinas/${rutina.uuid}`, { preserveScroll: true });
        }
    }

    const grupos = Object.entries(objetivos)
        .map(([clave, nombre]) => ({ clave, nombre, rutinas: rutinas.filter((r) => r.objetivo === clave) }))
        .filter((g) => g.rutinas.length > 0);

    return (
        <>
            <Head title="Rutinas de la sala" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Rutinas de la sala</h1>
                    <p className="apoyo text-fog">
                        Las que ve quien escanea el QR «Qué entrenar hoy». ·{' '}
                        <Link href="/panel/ejercicios" className="underline-offset-2 hover:text-chalk hover:underline">
                            Catálogo de ejercicios ({ejercicios})
                        </Link>
                    </p>
                </div>

                <Link
                    href="/panel/rutinas/crear"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nueva rutina
                </Link>
            </header>

            {rutinas.length === 0 ? (
                <div className="rounded-panel border border-dashed border-line px-4 py-10 text-center text-sm text-fog">
                    Todavía no hay rutinas. Crea una, o carga las de ejemplo con <code className="text-chalk">php artisan rutinas:ejemplos</code>.
                </div>
            ) : (
                <div className="space-y-6">
                    {grupos.map((g) => (
                        <section key={g.clave}>
                            <h2 className="rotulo mb-2">
                                {g.nombre} ({g.rutinas.length})
                            </h2>
                            <ul className="divide-y divide-line overflow-hidden rounded-panel border border-line bg-surface">
                                {g.rutinas.map((r) => (
                                    <li key={r.uuid} className="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3">
                                        <Link href={`/panel/rutinas/${r.uuid}/editar`} className="min-w-0 flex-1">
                                            <span className={`block truncate text-sm font-medium ${r.activa ? 'text-chalk' : 'text-fog'}`}>{r.nombre}</span>
                                            <span className="apoyo block text-fog">
                                                {r.nivel} · {r.dias} {r.dias === 1 ? 'día' : 'días'}
                                            </span>
                                        </Link>

                                        <button
                                            type="button"
                                            onClick={() => router.patch(`/panel/rutinas/${r.uuid}/alternar`, {}, { preserveScroll: true })}
                                            title={r.activa ? 'Sale en la web: clic para apagarla' : 'Apagada: clic para que salga en la web'}
                                        >
                                            <Activo valor={r.activa} />
                                        </button>

                                        <div className="flex items-center gap-3 text-fog">
                                            <a href={r.ver} target="_blank" rel="noopener" aria-label={`Ver ${r.nombre} en la web`} className="hover:text-chalk">
                                                <ExternalLinkIcon className="size-4" aria-hidden="true" />
                                            </a>
                                            <Link href={`/panel/rutinas/${r.uuid}/editar`} aria-label={`Editar ${r.nombre}`} className="hover:text-chalk">
                                                <PencilIcon className="size-4" aria-hidden="true" />
                                            </Link>
                                            <button
                                                type="button"
                                                onClick={() => router.post(`/panel/rutinas/${r.uuid}/duplicar`)}
                                                aria-label={`Duplicar ${r.nombre}`}
                                                title="Duplicar para armar otra variante"
                                                className="hover:text-chalk"
                                            >
                                                <CopyIcon className="size-4" aria-hidden="true" />
                                            </button>
                                            <button type="button" onClick={() => eliminar(r)} aria-label={`Eliminar ${r.nombre}`} className="hover:text-danger">
                                                <TrashIcon className="size-4" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    ))}
                </div>
            )}
        </>
    );
}
