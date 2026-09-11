import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { AlertTriangleIcon, CheckIcon, CopyIcon } from 'lucide-react';

import { haceCuanto } from '@/lib/tiempo';

/**
 * Un tema de ajustes: sus campos y un solo «Guardar».
 *
 * Cada tema tiene su dirección —/panel/configuracion/horario— y se ve dentro
 * del marco de Configuración, con el menú de secciones a la izquierda. Antes
 * eran pestañas de una misma pantalla: no dejaban rastro en el historial y
 * «atrás» sacaba de Configuración entera.
 *
 * IRSE CON CAMBIOS SIN GUARDAR PREGUNTA: con un formulario por página, pasar a
 * otra sección se llevaría lo escrito sin avisar.
 */
export default function Configuracion({ grupo, extra }) {
    const inicial = Object.fromEntries(grupo.ajustes.map((a) => [a.clave, a.valor ?? '']));
    const { data, setData, put, processing, errors } = useForm(inicial);

    // Contra lo guardado y no contra lo que había al abrir: después de guardar
    // la página recibe los valores nuevos y el aviso se apaga solo.
    const sinGuardar = grupo.ajustes.some((a) => String(data[a.clave] ?? '') !== String(a.valor ?? ''));

    useAvisoAlSalir(sinGuardar && !processing);

    function guardar(e) {
        e.preventDefault();
        put('/panel/configuracion', { preserveScroll: true });
    }

    // Los campos de un tema largo, bajo su subtítulo.
    const bloques = [];

    for (const ajuste of grupo.ajustes) {
        const ultimo = bloques[bloques.length - 1];

        if (ultimo && ultimo.seccion === ajuste.seccion) {
            ultimo.ajustes.push(ajuste);
        } else {
            bloques.push({ seccion: ajuste.seccion, ajustes: [ajuste] });
        }
    }

    return (
        <form onSubmit={guardar} className="max-w-3xl">
            <Head title={grupo.titulo} />

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">{grupo.titulo}</h1>
                <p className="apoyo text-fog">{grupo.descripcion}</p>
            </header>

            {grupo.clave === 'tareas' && extra ? (
                <EstadoDeTareas tareas={extra.tareas} correoConfigurado={extra.correoConfigurado} />
            ) : null}

            {grupo.clave === 'web' && extra ? (
                <VistaEnGoogle vista={extra.vistaGoogle} descripcion={data['web.descripcion']} />
            ) : null}

            <div className="space-y-4">
                {bloques.map((bloque, i) => (
                    <section key={bloque.seccion ?? i} className="rounded-panel border border-line bg-surface p-4">
                        {bloque.seccion ? <h2 className="rotulo mb-3">{bloque.seccion}</h2> : null}

                        <div className="space-y-3">
                            {bloque.ajustes.map((ajuste) => (
                                <Ajuste
                                    key={ajuste.clave}
                                    ajuste={ajuste}
                                    valor={data[ajuste.clave]}
                                    error={errors[ajuste.clave]}
                                    alCambiar={(v) => setData(ajuste.clave, v)}
                                />
                            ))}
                        </div>
                    </section>
                ))}
            </div>

            {/* Pegada abajo: en un tema largo, como Google y redes, el botón
                no se pierde al bajar. */}
            <div className="sticky bottom-0 z-10 mt-4 flex flex-wrap items-center gap-3 rounded-panel border border-line bg-surface/95 px-4 py-3 backdrop-blur">
                <button
                    type="submit"
                    disabled={processing || !sinGuardar}
                    className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                >
                    {processing ? 'Guardando…' : 'Guardar'}
                </button>

                {sinGuardar ? (
                    <span className="apoyo text-warn">Hay cambios sin guardar.</span>
                ) : (
                    <span className="apoyo text-fog">Todo guardado.</span>
                )}

                {errors.ajustes ? <span className="apoyo text-danger">{errors.ajustes}</span> : null}
            </div>
        </form>
    );
}

/**
 * Pregunta antes de irse con cambios sin guardar: al pasar a otra sección y al
 * cerrar la pestaña. Guardar no pregunta: es un PUT, no es irse.
 */
function useAvisoAlSalir(activo) {
    useEffect(() => {
        if (!activo) {
            return undefined;
        }

        const quitar = router.on('before', (evento) => {
            if (String(evento.detail.visit.method).toLowerCase() !== 'get') {
                return;
            }

            if (!window.confirm('Hay cambios sin guardar. ¿Salir igual y perderlos?')) {
                evento.preventDefault();
            }
        });

        const alCerrar = (e) => {
            e.preventDefault();
            e.returnValue = '';
        };

        window.addEventListener('beforeunload', alCerrar);

        return () => {
            quitar();
            window.removeEventListener('beforeunload', alCerrar);
        };
    }, [activo]);
}

/** Un ajuste: su etiqueta, su campo y por qué existe. */
function Ajuste({ ajuste, valor, error, alCambiar }) {
    const texto = String(valor ?? '');
    const cambiado = texto !== String(ajuste.defecto ?? '');
    const borde = error ? 'border-danger' : 'border-line focus:border-line-strong';

    const comun = {
        id: ajuste.clave,
        name: ajuste.clave,
        value: valor ?? '',
        onChange: (e) => alCambiar(e.target.value),
        placeholder: ajuste.ejemplo ?? undefined,
        'aria-invalid': error ? 'true' : undefined,
        'aria-describedby': `${ajuste.clave}-ayuda`,
    };

    return (
        <div className="grid gap-1 sm:grid-cols-[14rem_1fr] sm:items-start sm:gap-4">
            <label htmlFor={ajuste.clave} className="pt-1.5 text-sm text-chalk">
                {ajuste.etiqueta}
            </label>

            <div className="min-w-0">
                <div className="flex items-center gap-2">
                    {ajuste.tipo === 'area' ? (
                        <textarea
                            {...comun}
                            rows={3}
                            className={`w-full min-w-0 rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:outline-none ${borde}`}
                        />
                    ) : (
                        <input
                            {...comun}
                            type={{ numero: 'number', fecha: 'date', hora: 'time' }[ajuste.tipo] ?? 'text'}
                            min={ajuste.min ?? undefined}
                            max={ajuste.max ?? undefined}
                            maxLength={ajuste.tipo === 'texto' && ajuste.largo ? ajuste.largo : undefined}
                            className={`min-w-0 rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:outline-none ${
                                ajuste.tipo === 'numero'
                                    ? 'w-28 tabular-nums'
                                    : ajuste.tipo === 'hora' || ajuste.tipo === 'fecha'
                                      ? 'w-40 tabular-nums'
                                      : 'w-full'
                            } ${borde}`}
                        />
                    )}

                    {ajuste.unidad ? <span className="apoyo shrink-0 text-fog">{ajuste.unidad}</span> : null}

                    {/* Decir cuál era el valor de fábrica ahorra tener que
                        buscarlo en otra parte para volver atrás. */}
                    {cambiado && ajuste.tipo !== 'area' ? (
                        <button
                            type="button"
                            onClick={() => alCambiar(String(ajuste.defecto ?? ''))}
                            className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                        >
                            {ajuste.defecto === '' || ajuste.defecto === null ? 'vaciar' : `volver a ${ajuste.defecto}`}
                        </button>
                    ) : null}
                </div>

                <div id={`${ajuste.clave}-ayuda`} className="mt-0.5 flex items-start justify-between gap-3">
                    {error ? (
                        <p className="apoyo text-danger">{error}</p>
                    ) : ajuste.ayuda ? (
                        <p className="apoyo text-fog">{ajuste.ayuda}</p>
                    ) : (
                        <span />
                    )}

                    {ajuste.tipo === 'area' && ajuste.largo ? (
                        <span
                            className={`apoyo shrink-0 tabular-nums ${texto.length > ajuste.largo ? 'text-danger' : 'text-fog'}`}
                        >
                            {texto.length}/{ajuste.largo}
                        </span>
                    ) : null}
                </div>
            </div>
        </div>
    );
}

/**
 * Cómo están las tareas automáticas y, si no corren, cómo activarlas.
 *
 * Sin esto no hay forma de saberlo: una tarea que no corre no da ningún error,
 * simplemente no pasa nada —los vencimientos no se marcan, los avisos no
 * salen— y parece que el sistema funciona.
 */
function EstadoDeTareas({ tareas, correoConfigurado }) {
    const [copiado, setCopiado] = useState(false);

    async function copiar() {
        try {
            await navigator.clipboard.writeText(tareas.comando);
            setCopiado(true);
            setTimeout(() => setCopiado(false), 2500);
        } catch {
            // Sin permiso para el portapapeles: la orden queda a la vista para
            // copiarla a mano.
        }
    }

    return (
        <section className="mb-4 space-y-3 rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo">Cómo están</h2>

            <Estado bien={tareas.corriendo}>
                {tareas.corriendo
                    ? `Funcionando. La última vuelta fue ${haceCuanto(tareas.ultimo_latido)}.`
                    : tareas.ultimo_latido
                      ? `No están corriendo: la última vuelta fue ${haceCuanto(tareas.ultimo_latido)}.`
                      : 'Nunca han corrido en este computador: no se marcan los vencimientos ni salen los avisos por correo.'}
            </Estado>

            <ul className="divide-y divide-line rounded-control border border-line">
                {tareas.tareas.map((t) => (
                    <li key={t.clave} className="flex flex-wrap items-center justify-between gap-x-3 gap-y-0.5 px-3 py-2">
                        <span className="text-sm text-chalk">{t.nombre}</span>
                        <span className="apoyo text-fog">
                            todos los días a las {t.hora} ·{' '}
                            {t.ultima ? `última vez ${haceCuanto(t.ultima)}` : 'todavía no ha corrido'}
                        </span>
                    </li>
                ))}
            </ul>

            {tareas.corriendo ? null : (
                <div className="space-y-2 rounded-control border border-warn/40 bg-warn/5 p-3">
                    <p className="text-sm text-chalk">Para activarlas, una sola vez en el computador del mesón:</p>
                    <ol className="apoyo list-decimal space-y-1 pl-5 text-fog">
                        <li>
                            Abre el menú Inicio, escribe «cmd» y, en «Símbolo del sistema», elige «Ejecutar como
                            administrador».
                        </li>
                        <li>Pega esta orden y presiona Enter:</li>
                    </ol>

                    <div className="flex items-start gap-2">
                        <code className="block min-w-0 flex-1 overflow-x-auto rounded-control bg-surface-2 px-2 py-1.5 font-mono text-xs whitespace-pre text-chalk">
                            {tareas.comando}
                        </code>
                        <button
                            type="button"
                            onClick={copiar}
                            className="inline-flex shrink-0 items-center gap-1.5 rounded-control border border-line px-2.5 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                        >
                            {copiado ? (
                                <CheckIcon className="size-4 text-ok" aria-hidden="true" />
                            ) : (
                                <CopyIcon className="size-4" aria-hidden="true" />
                            )}
                            {copiado ? 'Copiada' : 'Copiar'}
                        </button>
                    </div>

                    <p className="apoyo text-fog">
                        En un par de minutos esta página debería decir «Funcionando». Si el computador se apaga de
                        noche, cambia abajo la hora de la revisión a una en que esté prendido.
                    </p>
                </div>
            )}

            <Estado bien={correoConfigurado}>
                {correoConfigurado
                    ? 'El correo de salida está configurado.'
                    : 'El correo de salida no está configurado: los avisos no le llegan a nadie. Lo configura quien instaló el sistema.'}
            </Estado>
        </section>
    );
}

function Estado({ bien, children }) {
    return (
        <p className={`flex items-start gap-2 text-sm ${bien ? 'text-ok' : 'text-warn'}`}>
            {bien ? (
                <CheckIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            ) : (
                <AlertTriangleIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            )}
            <span>{children}</span>
        </p>
    );
}

/**
 * La portada tal como sale en los resultados de Google, mientras se escribe.
 *
 * Los colores son los de Google a propósito, sobre blanco: se trata de
 * reconocerlo de un vistazo, no de que combine con el panel.
 */
function VistaEnGoogle({ vista, descripcion }) {
    const escrita = String(descripcion ?? '').trim();
    const texto = escrita || vista.descripcionAutomatica;
    const corto = texto.length > 158 ? `${texto.slice(0, 155).trimEnd()}…` : texto;
    const sitio = vista.url.replace(/^https?:\/\//, '').replace(/\/$/, '');

    return (
        <section className="mb-4 rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo mb-2">Así sale la portada en Google</h2>

            <div className="max-w-xl rounded-control bg-white p-3">
                <p className="truncate text-xs text-[#202124]">{sitio}</p>
                <p className="truncate text-lg leading-snug text-[#1a0dab]">{vista.titulo}</p>
                <p className="text-sm text-[#4d5156]">{corto}</p>
            </div>

            <p className="apoyo mt-2 text-fog">
                {escrita
                    ? 'Con la descripción que escribiste abajo.'
                    : 'Con la descripción que se arma sola; abajo puedes escribir la tuya.'}{' '}
                Google a veces elige otro texto de la página.
            </p>
        </section>
    );
}
