import { Link } from '@inertiajs/react';

/**
 * Paginacion de un paginador de Laravel.
 *
 * Recibe `links` tal como los emite `paginate()`; las etiquetas vienen con
 * entidades HTML («&laquo; Anterior»), de ahi el dangerouslySetInnerHTML: el
 * contenido lo genera Laravel, no el usuario.
 */
export default function Paginacion({ paginador }) {
    if (!paginador || paginador.last_page <= 1) {
        return null;
    }

    return (
        <nav className="mt-3 flex flex-wrap items-center gap-1" aria-label="Paginación">
            {paginador.links.map((enlace, i) => (
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
    );
}
