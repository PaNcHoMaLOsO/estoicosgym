import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { AlertTriangleIcon, ShieldCheckIcon } from 'lucide-react';

import TextoQueCambia from '@/components/TextoQueCambia';
import useAvisoAlSalir from '@/lib/useAvisoAlSalir';
import { haceCuanto } from '@/lib/tiempo';

/**
 * Qué puede hacer cada perfil.
 *
 * Un interruptor por permiso, agrupados por la parte del gimnasio a la que
 * tocan y con lo que abre cada uno dicho en una línea. Los nombres los manda
 * el servidor (CatalogoDePermisos): si viviera una copia aquí, el permiso que
 * se sumara mañana tendría casilla en un lado y no en el otro.
 *
 * LO QUE SE NECESITA SE ENCIENDE SOLO, Y A LA VISTA. Encender «Corregir sus
 * cobros de hoy» enciende «Cobrar» y «Ver pagos»; apagar «Ver pagos» apaga
 * todo lo de pagos. Si no, quedaría un perfil con un botón de corregir que no
 * puede abrir la pantalla del pago. El servidor completa lo mismo al guardar.
 */

/** Todo lo que depende de `permiso`, directa o indirectamente. */
function dependientes(permiso, necesita) {
    const fuera = new Set([permiso]);
    let cambio = true;

    while (cambio) {
        cambio = false;

        for (const [otro, previos] of Object.entries(necesita)) {
            if (!fuera.has(otro) && previos.some((p) => fuera.has(p))) {
                fuera.add(otro);
                cambio = true;
            }
        }
    }

    return fuera;
}

/** `permiso` y todo lo que necesita para servir. */
function conLoQueNecesita(permiso, necesita) {
    const dentro = new Set();
    const pendientes = [permiso];

    while (pendientes.length > 0) {
        const p = pendientes.pop();

        if (!dentro.has(p)) {
            dentro.add(p);
            pendientes.push(...(necesita[p] ?? []));
        }
    }

    return dentro;
}

/** El mismo interruptor que los ajustes de Configuración. */
function Interruptor({ id, encendido, alCambiar, describe }) {
    return (
        <button
            type="button"
            role="switch"
            id={id}
            aria-checked={encendido}
            aria-describedby={describe}
            onClick={() => alCambiar(!encendido)}
            className="mt-0.5 shrink-0"
        >
            <span
                className={`relative inline-block h-5 w-9 rounded-full transition-colors ${
                    encendido ? 'bg-volt' : 'bg-surface-2 ring-1 ring-line-strong'
                }`}
                aria-hidden="true"
            >
                <span
                    className={`absolute top-0.5 size-4 rounded-full bg-chalk transition-all ${
                        encendido ? 'left-[18px]' : 'left-0.5'
                    }`}
                />
            </span>
            <span className="sr-only">{encendido ? 'Encendido' : 'Apagado'}</span>
        </button>
    );
}

function EditorDePerfil({ perfil, areas, necesita, orden, etiquetas }) {
    const { data, setData, put, processing, isDirty, setDefaults, errors } = useForm({
        permisos: perfil.permisos,
    });
    const tocar = useAvisoAlSalir(isDirty && !processing);

    const marcados = new Set(data.permisos);

    function cambiar(permiso, encender) {
        const nuevos = new Set(marcados);

        if (encender) {
            conLoQueNecesita(permiso, necesita).forEach((p) => nuevos.add(p));
        } else {
            dependientes(permiso, necesita).forEach((p) => nuevos.delete(p));
        }

        // En el orden del catálogo: así el «hay cambios» no se enciende por
        // haber marcado lo mismo en otro orden.
        setData('permisos', orden.filter((p) => nuevos.has(p)));
    }

    function guardar(e) {
        e.preventDefault();
        put(`/panel/usuarios/perfiles/${perfil.id}`, {
            preserveScroll: true,
            onSuccess: () => setDefaults(),
        });
    }

    return (
        <form onSubmit={guardar} {...tocar} className="space-y-3">
            <p className="apoyo text-fog">
                {perfil.cuentas === 1 ? '1 cuenta activa' : `${perfil.cuentas} cuentas activas`} con este perfil.
                {perfil.ultimo_cambio ? (
                    <>
                        {' '}
                        Último cambio {haceCuanto(perfil.ultimo_cambio.cuando)}
                        {perfil.ultimo_cambio.quien ? `, por ${perfil.ultimo_cambio.quien}` : ''}.
                    </>
                ) : null}
            </p>

            {areas.map((area) => (
                <section key={area.clave} className="rounded-panel border border-line bg-surface p-4">
                    <h2 className="text-sm font-semibold text-chalk">{area.titulo}</h2>
                    <p className="apoyo text-fog">{area.descripcion}</p>

                    {area.cuidado ? (
                        <p className="apoyo mt-2 flex items-start gap-2 rounded-control border border-warn/40 bg-warn/5 p-2.5 text-warn">
                            <AlertTriangleIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            {area.cuidado}
                        </p>
                    ) : null}

                    <ul className="mt-3 space-y-3">
                        {area.permisos.map((p) => {
                            const id = `p-${perfil.id}-${p.permiso}`;
                            // Lo que enciende con él, aparte del «ver» de su
                            // propio módulo, que se da por sabido.
                            const arrastra = (necesita[p.permiso] ?? [])
                                .filter((previo) => previo.split('.')[0] !== p.permiso.split('.')[0] || !previo.endsWith('.ver'))
                                .map((previo) => etiquetas[previo]);

                            return (
                                <li key={p.permiso} className="flex items-start gap-3">
                                    <Interruptor
                                        id={id}
                                        encendido={marcados.has(p.permiso)}
                                        alCambiar={(encender) => cambiar(p.permiso, encender)}
                                        describe={`${id}-ayuda`}
                                    />
                                    <div className="min-w-0">
                                        <label htmlFor={id} className="text-sm text-chalk">
                                            {p.etiqueta}
                                            {p.cuidado ? <span className="apoyo ml-1.5 text-warn">· con cuidado</span> : null}
                                        </label>
                                        <p id={`${id}-ayuda`} className="apoyo text-fog">
                                            {p.explicacion}
                                            {arrastra.length > 0 ? ` Enciende también «${arrastra.join('», «')}».` : ''}
                                        </p>
                                    </div>
                                </li>
                            );
                        })}
                    </ul>
                </section>
            ))}

            {/* Pegada abajo, como en los ajustes: la lista es larga y el botón
                no se pierde al bajar. */}
            <div className="sticky bottom-0 z-10 flex flex-wrap items-center gap-3 rounded-panel border border-line bg-surface/95 px-4 py-3 backdrop-blur">
                <button
                    type="submit"
                    disabled={processing || !isDirty}
                    className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                >
                    <TextoQueCambia ocupado={processing} mientras="Guardando…">
                        Guardar {perfil.nombre}
                    </TextoQueCambia>
                </button>

                {isDirty ? (
                    <span className="apoyo text-warn">Hay cambios sin guardar.</span>
                ) : (
                    <span className="apoyo text-fog">Todo guardado.</span>
                )}

                {errors.permisos || errors['permisos.0'] ? (
                    <span className="apoyo text-danger">{errors.permisos ?? errors['permisos.0']}</span>
                ) : null}
            </div>
        </form>
    );
}

export default function Perfiles({ perfiles, areas, necesita }) {
    const editables = perfiles.filter((p) => !p.es_admin);
    const administradores = perfiles.filter((p) => p.es_admin);
    const [elegido, setElegido] = useState(editables[0]?.id ?? null);

    const orden = areas.flatMap((a) => a.permisos.map((p) => p.permiso));
    const etiquetas = Object.fromEntries(areas.flatMap((a) => a.permisos.map((p) => [p.permiso, p.etiqueta])));

    return (
        <>
            <Head title="Qué puede cada perfil" />

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">Qué puede hacer cada perfil</h1>
                <p className="apoyo max-w-2xl text-fog">
                    Lo que se apaga aquí desaparece de la pantalla de todas las cuentas con ese perfil. Las cuentas se
                    asignan en{' '}
                    <Link href="/panel/usuarios" className="underline underline-offset-2 hover:text-chalk">
                        Usuarios del panel
                    </Link>
                    .
                </p>
            </header>

            <div className="max-w-3xl space-y-4">
                {administradores.map((a) => (
                    <div key={a.id} className="flex items-start gap-3 rounded-panel border border-line bg-surface p-4">
                        <ShieldCheckIcon className="mt-0.5 size-5 shrink-0 text-volt" aria-hidden="true" />
                        <div>
                            <p className="text-sm font-medium text-chalk">{a.nombre}: puede todo</p>
                            <p className="apoyo text-fog">
                                También lo que se agregue más adelante. No se cambia desde aquí, para que siempre quede
                                alguien que pueda entrar a Configuración.
                            </p>
                        </div>
                    </div>
                ))}

                {editables.length === 0 ? (
                    <p className="apoyo text-fog">No hay otros perfiles.</p>
                ) : (
                    <>
                        {editables.length > 1 ? (
                            <div role="tablist" aria-label="Perfil" className="inline-flex flex-wrap rounded-control border border-line p-0.5 text-sm">
                                {editables.map((p) => (
                                    <button
                                        key={p.id}
                                        type="button"
                                        role="tab"
                                        aria-selected={elegido === p.id}
                                        onClick={() => setElegido(p.id)}
                                        className={`rounded-control px-3 py-1 transition-colors ${
                                            elegido === p.id ? 'bg-surface-2 font-medium text-chalk' : 'text-fog hover:text-chalk'
                                        }`}
                                    >
                                        {p.nombre}
                                        {p.activo ? '' : ' (inactivo)'}
                                    </button>
                                ))}
                            </div>
                        ) : (
                            <h2 className="text-base font-semibold text-chalk">{editables[0].nombre}</h2>
                        )}

                        {/* Todos montados y solo uno a la vista: pasar de un
                            perfil a otro no borra lo que se estaba cambiando. */}
                        {editables.map((p) => (
                            <div key={p.id} hidden={elegido !== p.id}>
                                <EditorDePerfil
                                    perfil={p}
                                    areas={areas}
                                    necesita={necesita}
                                    orden={orden}
                                    etiquetas={etiquetas}
                                />
                            </div>
                        ))}
                    </>
                )}
            </div>
        </>
    );
}
