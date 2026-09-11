import { Head, Link } from '@inertiajs/react';
import { ChevronRightIcon, ExternalLinkIcon } from 'lucide-react';

/**
 * «Página web»: todo lo que ve el cliente, en un solo lugar.
 *
 * Arriba lo que se escribe aquí mismo —servicios, fotos, preguntas,
 * testimonios— y lo que tiene su propia pantalla —especialistas, convenios—.
 * Abajo, los ajustes de la página, que viven en Configuración.
 */
function Tarjeta({ href, titulo, descripcion, activos, total }) {
    return (
        <Link
            href={href}
            className="group flex items-start justify-between gap-3 rounded-panel border border-line bg-surface p-4 transition-colors hover:border-line-strong hover:bg-surface-2"
        >
            <div className="min-w-0">
                <p className="text-sm font-medium text-chalk">{titulo}</p>
                <p className="apoyo mt-0.5 text-fog">{descripcion}</p>
                {total !== undefined ? (
                    <p className="apoyo mt-1 text-fog">
                        {activos} en la web
                        {total > activos ? ` · ${total - activos} ocultos` : ''}
                    </p>
                ) : null}
            </div>
            <ChevronRightIcon
                className="mt-0.5 size-4 shrink-0 text-fog transition-transform group-hover:translate-x-0.5"
                aria-hidden="true"
            />
        </Link>
    );
}

export default function Inicio({ tarjetas, ajustes, urlSitio }) {
    return (
        <>
            <Head title="Página web" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Página web</h1>
                    <p className="apoyo text-fog">Lo que ven los clientes, y dónde se cambia cada cosa</p>
                </div>

                {/* Se abre en otra pestaña: el panel no se pierde. */}
                <a
                    href={urlSitio}
                    target="_blank"
                    rel="noopener"
                    className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                >
                    <ExternalLinkIcon className="size-4" aria-hidden="true" />
                    Ver la página
                </a>
            </header>

            <h2 className="rotulo mb-2">Contenido</h2>
            <div className="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {tarjetas.map((t) => (
                    <Tarjeta key={t.href} {...t} />
                ))}
            </div>

            <h2 className="rotulo mb-2">Ajustes de la página</h2>
            <div className="grid gap-3 sm:grid-cols-2">
                {ajustes.map((a) => (
                    <Tarjeta key={a.href} {...a} />
                ))}
            </div>
        </>
    );
}
