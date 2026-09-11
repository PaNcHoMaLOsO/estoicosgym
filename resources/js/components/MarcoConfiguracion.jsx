import { Link, router, usePage } from '@inertiajs/react';
import { AlertTriangleIcon } from 'lucide-react';
import { createContext, useContext } from 'react';

import { SECCIONES_CONFIGURACION, seccionActiva } from '@/lib/configuracion';
import { puede } from '@/lib/permisos';

const EnMarco = createContext(false);

/** ¿La pantalla se está viendo dentro de Configuración, con su menú? */
export function useEnConfiguracion() {
    return useContext(EnMarco);
}

/**
 * El marco de Configuración: el menú de secciones a la izquierda y la
 * pantalla a la derecha.
 *
 * Envuelve TODAS las pantallas de Configuración —ajustes, catálogos, página
 * web, usuarios, papelera— y no se desmonta al pasar de una a otra. Antes cada
 * catálogo era una pantalla suelta: se entraba desde unas tarjetas, no había
 * cómo volver y el carril principal no marcaba nada. Aquí siempre se ve dónde
 * se está y a qué se puede ir.
 *
 * En el celular el menú es un selector: veinte enlaces apilados empujarían la
 * pantalla hacia abajo antes de dejar ver nada.
 */
export default function MarcoConfiguracion({ children }) {
    const { props, url } = usePage();
    const { auth } = props;
    const avisos = props.configuracion?.avisos ?? {};

    // Solo lo que se puede abrir: un enlace que termina en «no autorizado»
    // enseña una puerta cerrada.
    const grupos = SECCIONES_CONFIGURACION.map((g) => ({
        ...g,
        secciones: g.secciones.filter((s) => !s.permiso || puede(auth, s.permiso)),
    })).filter((g) => g.secciones.length > 0);

    // Recepción mirando las plantillas de correo no ve Configuración: para
    // ella esa pantalla va sola, sin un menú de un único enlace.
    if (!puede(auth, 'configuracion.ver')) {
        return children;
    }

    const actual = grupos.flatMap((g) => g.secciones).find((s) => seccionActiva(s, url));

    return (
        <EnMarco.Provider value>
            <div className="lg:grid lg:grid-cols-[13rem_minmax(0,1fr)] lg:gap-8">
                <div className="mb-4 lg:hidden">
                    <label htmlFor="seccion-de-configuracion" className="rotulo mb-1 block">
                        Configuración
                    </label>
                    <select
                        id="seccion-de-configuracion"
                        value={actual?.href ?? ''}
                        onChange={(e) => router.visit(e.target.value)}
                        className="w-full rounded-control border border-line bg-surface px-2.5 py-2 text-sm text-chalk focus:border-line-strong focus:outline-none"
                    >
                        {actual ? null : <option value="">Elige una sección…</option>}
                        {grupos.map((g) => (
                            <optgroup key={g.titulo ?? 'inicio'} label={g.titulo ?? 'Configuración'}>
                                {g.secciones.map((s) => (
                                    <option key={s.href} value={s.href}>
                                        {s.etiqueta}
                                        {avisos[s.href] ? ' (pendiente)' : ''}
                                    </option>
                                ))}
                            </optgroup>
                        ))}
                    </select>
                </div>

                <nav aria-label="Secciones de la configuración" className="hidden lg:block">
                    {/* Con sus seis grupos el menú es más alto que la pantalla:
                        se desplaza por su cuenta, o lo de abajo —Usuarios,
                        Papelera— quedaría fuera de alcance en una pantalla corta. */}
                    <div className="sticky top-6 max-h-[calc(100dvh-3rem)] space-y-5 overflow-y-auto pr-1 pb-4">
                        <p className="px-2 text-sm font-semibold text-chalk">Configuración</p>

                        {grupos.map((g) => (
                            <div key={g.titulo ?? 'inicio'}>
                                {/* El título del grupo NO es un enlace y tiene que
                                    notarse: claro, en negrita y con su raya hasta el
                                    borde. Lo que cuelga de él va sangrado bajo una
                                    línea, para que un apartado no se confunda con el
                                    título de su grupo. */}
                                {g.titulo ? (
                                    <p className="mb-1.5 flex items-center gap-2 px-2 text-[11px] font-semibold tracking-[0.12em] text-chalk uppercase">
                                        <span className="shrink-0">{g.titulo}</span>
                                        <span className="h-px flex-1 bg-line" aria-hidden="true" />
                                    </p>
                                ) : null}

                                <div className={g.titulo ? 'ml-2.5 space-y-0.5 border-l border-line pl-2' : 'space-y-0.5'}>
                                {g.secciones.map((s) => {
                                    const activa = seccionActiva(s, url);
                                    const aviso = avisos[s.href];

                                    return (
                                        <Link
                                            key={s.href}
                                            href={s.href}
                                            aria-current={activa ? 'page' : undefined}
                                            title={aviso ?? undefined}
                                            className={`relative flex items-center justify-between gap-2 rounded-control py-1.5 pr-2 pl-3 text-sm transition-colors ${
                                                activa
                                                    ? 'bg-surface-2 font-medium text-chalk before:absolute before:inset-y-1 before:left-0 before:w-0.5 before:rounded-full before:bg-volt'
                                                    : 'text-fog hover:bg-surface-2 hover:text-chalk'
                                            }`}
                                        >
                                            <span className="truncate">{s.etiqueta}</span>
                                            {aviso ? (
                                                <AlertTriangleIcon
                                                    className="size-3.5 shrink-0 text-warn"
                                                    aria-label="Tiene algo pendiente"
                                                />
                                            ) : null}
                                        </Link>
                                    );
                                })}
                                </div>
                            </div>
                        ))}
                    </div>
                </nav>

                <div className="min-w-0">{children}</div>
            </div>
        </EnMarco.Provider>
    );
}
