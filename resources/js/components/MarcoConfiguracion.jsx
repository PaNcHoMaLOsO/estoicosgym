import { Link, router, usePage } from '@inertiajs/react';
import {
    AlertTriangleIcon,
    Building2Icon,
    ChevronRightIcon,
    GlobeIcon,
    ListChecksIcon,
    MailIcon,
    ScaleIcon,
    ServerCogIcon,
    WalletIcon,
} from 'lucide-react';
import { createContext, useContext } from 'react';

import { SECCIONES_CONFIGURACION, seccionActiva } from '@/lib/configuracion';
import { puede } from '@/lib/permisos';

const EnMarco = createContext(false);

// El icono de cada grupo: en un menú de veinte enlaces, el dibujo es lo que el ojo
// encuentra primero; el rótulo en mayúsculas chicas solo no bastaba.
const ICONOS = {
    gimnasio: Building2Icon,
    cobros: WalletIcon,
    correos: MailIcon,
    web: GlobeIcon,
    legal: ScaleIcon,
    sistema: ServerCogIcon,
};

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
    const grupoActual = grupos.find((g) => g.secciones.includes(actual));
    const pendientes = Object.keys(avisos).length;

    return (
        <EnMarco.Provider value>
            <div className="lg:grid lg:grid-cols-[14.5rem_minmax(0,1fr)] lg:gap-6">
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
                    <div className="sticky top-6 max-h-[calc(100dvh-3rem)] space-y-4 overflow-y-auto rounded-panel border border-line bg-surface p-2 pb-3">
                        <p className="flex items-center justify-between gap-2 px-2 pt-1 text-sm font-semibold text-chalk">
                            Configuración
                            {pendientes > 0 ? (
                                <span
                                    className="rounded-full bg-warn/15 px-1.5 py-0.5 text-[11px] font-semibold tabular-nums text-warn"
                                    title={pendientes === 1 ? 'Una sección tiene algo pendiente' : `${pendientes} secciones tienen algo pendiente`}
                                >
                                    {pendientes}
                                </span>
                            ) : null}
                        </p>

                        {grupos.map((g) => (
                            <div key={g.titulo ?? 'inicio'}>
                                {/* El título del grupo NO es un enlace y tiene que
                                    notarse: claro, en negrita y con su raya hasta el
                                    borde. Lo que cuelga de él va sangrado bajo una
                                    línea, para que un apartado no se confunda con el
                                    título de su grupo. */}
                                {g.titulo ? (
                                    <p className={`mb-1 flex items-center gap-2 px-2 text-[11px] font-semibold tracking-[0.1em] uppercase ${g === grupoActual ? 'text-chalk' : 'text-fog'}`}>
                                        <IconoDeGrupo icono={g.icono} activo={g === grupoActual} />
                                        <span className="truncate">{g.titulo}</span>
                                    </p>
                                ) : null}

                                <div className={g.titulo ? 'ml-[0.95rem] space-y-0.5 border-l border-line pl-2' : 'space-y-0.5'}>
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
                                            <span className="flex min-w-0 items-center gap-2">
                                                {g.titulo ? null : <ListChecksIcon className="size-4 shrink-0" aria-hidden="true" />}
                                                <span className="truncate">{s.etiqueta}</span>
                                            </span>
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

                <div className="min-w-0">
                    {/* DÓNDE SE ESTÁ, en una línea. Con veinte pantallas parecidas —una
                        tabla o un formulario cada una— el título solo no decía de qué
                        parte de la configuración colgaba. En el celular, donde el menú
                        es un selector cerrado, es lo único que lo dice. */}
                    {actual && grupoActual?.titulo ? (
                        <p className="apoyo mb-2 flex flex-wrap items-center gap-1 text-fog">
                            <Link href="/panel/configuracion" className="transition-colors hover:text-chalk">
                                Configuración
                            </Link>
                            <ChevronRightIcon className="size-3 shrink-0" aria-hidden="true" />
                            <span>{grupoActual.titulo}</span>
                            <ChevronRightIcon className="size-3 shrink-0" aria-hidden="true" />
                            <span className="text-chalk">{actual.etiqueta}</span>
                        </p>
                    ) : null}

                    {children}
                </div>
            </div>
        </EnMarco.Provider>
    );
}

function IconoDeGrupo({ icono, activo }) {
    const Icono = ICONOS[icono];

    return Icono ? <Icono className={`size-3.5 shrink-0 ${activo ? 'text-volt' : ''}`} aria-hidden="true" /> : null;
}
