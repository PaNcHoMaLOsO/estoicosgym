import { Head, Link, router, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { AlertTriangleIcon, CheckIcon, CopyIcon } from 'lucide-react';

import Dialogo from '@/components/Dialogo';
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

    const salida = useAvisoAlSalir(sinGuardar && !processing);

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

            {grupo.clave === 'correo' && extra?.correo ? <CorreoDeSalida correo={extra.correo} /> : null}

            {grupo.clave === 'web' && extra ? (
                <VistaEnGoogle vista={extra.vistaGoogle} descripcion={data['web.descripcion']} />
            ) : null}

            <div className="space-y-4">
                {bloques.map((bloque, i) => (
                    <section key={bloque.seccion ?? i} className="overflow-hidden rounded-panel border border-line bg-surface">
                        {/* La misma cabecera que las tarjetas de «Lo que falta»: todas las
                            pantallas de Configuración se leen igual. Y las filas, separadas
                            por una línea: con solo aire entre ellas, en un tema de quince
                            ajustes no se sabía qué ayuda era de qué casilla. */}
                        {bloque.seccion ? <h2 className="rotulo border-b border-line px-4 py-2">{bloque.seccion}</h2> : null}

                        <div className="divide-y divide-line [&>*]:px-4 [&>*]:py-3">
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

            <Dialogo
                abierto={salida.pendiente !== null}
                alCerrar={salida.quedarse}
                titulo="Hay cambios sin guardar"
                descripcion="Si sales ahora, lo que cambiaste en esta sección se pierde."
                etiquetaConfirmar="Salir sin guardar"
                peligrosa
                via="local"
                alConfirmar={salida.salir}
            />

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
    // La navegacion que se detuvo para preguntar; se retoma si se confirma.
    const [pendiente, setPendiente] = useState(null);
    const confirmada = useRef(false);

    useEffect(() => {
        if (!activo) {
            return undefined;
        }

        const quitar = router.on('before', (evento) => {
            const visita = evento.detail.visit;

            if (String(visita.method).toLowerCase() !== 'get' || confirmada.current) {
                return;
            }

            evento.preventDefault();
            setPendiente(visita.url.href ?? String(visita.url));
        });

        // Cerrar la pestaña sí usa el cuadro del navegador: ahí no hay otro.
        const alCerrar = (e) => {
            e.preventDefault();
        };
        window.addEventListener('beforeunload', alCerrar);

        return () => {
            quitar();
            window.removeEventListener('beforeunload', alCerrar);
        };
    }, [activo]);

    return {
        pendiente,
        quedarse: () => setPendiente(null),
        salir: () => {
            confirmada.current = true;
            const destino = pendiente;
            setPendiente(null);
            router.visit(destino);
        },
    };
}

/** Un ajuste: su etiqueta, su campo y por qué existe. */
function Ajuste({ ajuste, valor, error, alCambiar }) {
    const texto = String(valor ?? '');
    const sinGuardar = texto !== String(ajuste.valor ?? '');
    // En los secretos el valor siempre llega vacío, así que compararlo con su
    // defecto diría «sin cambios» aunque haya una contraseña guardada.
    const cambiado = ajuste.tipo !== 'secreto' && texto !== String(ajuste.defecto ?? '');
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
            <label htmlFor={ajuste.clave} className="flex items-center gap-1.5 pt-1.5 text-sm text-chalk">
                {ajuste.etiqueta}
                {/* Un punto en lo que se cambió y todavía no se guarda. */}
                {sinGuardar ? <span className="size-1.5 shrink-0 rounded-full bg-warn" title="Cambiado, sin guardar" /> : null}
            </label>

            <div className="min-w-0">
                <div className="flex items-center gap-2">
                    {ajuste.tipo === 'secreto' ? (
                        /*
                         * LA CONTRASEÑA NO SE PINTA, NI SIQUIERA EN PUNTOS.
                         *
                         * El servidor no la manda: el campo llega vacío y al
                         * lado se dice si hay una guardada. Dejarlo en blanco
                         * conserva la que está; para quitarla hay que escribir
                         * BORRAR, que no se teclea sin querer.
                         */
                        <input
                            {...comun}
                            type="password"
                            autoComplete="new-password"
                            maxLength={ajuste.largo ?? 255}
                            placeholder={ajuste.guardado ? 'Hay una guardada: escribe una nueva para cambiarla' : 'Sin contraseña'}
                            className={`w-full min-w-0 rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:outline-none ${borde}`}
                        />
                    ) : ajuste.tipo === 'si_no' ? (
                        /* Un interruptor y no una casilla: lo que se enciende y
                           se apaga de golpe —los correos automáticos— tiene que
                           verse encendido o apagado desde lejos. */
                        <button
                            type="button"
                            role="switch"
                            id={ajuste.clave}
                            aria-checked={String(valor) === '1'}
                            aria-describedby={`${ajuste.clave}-ayuda`}
                            onClick={() => alCambiar(String(valor) === '1' ? '0' : '1')}
                            className="inline-flex items-center gap-2 text-sm text-chalk"
                        >
                            <span
                                className={`relative inline-block h-5 w-9 rounded-full transition-colors ${
                                    String(valor) === '1' ? 'bg-volt' : 'bg-surface-2 ring-1 ring-line-strong'
                                }`}
                                aria-hidden="true"
                            >
                                <span
                                    className={`absolute top-0.5 size-4 rounded-full bg-chalk transition-all ${
                                        String(valor) === '1' ? 'left-[18px]' : 'left-0.5'
                                    }`}
                                />
                            </span>
                            {String(valor) === '1' ? 'Encendidos' : 'Apagados'}
                        </button>
                    ) : ajuste.tipo === 'opciones' ? (
                        <select
                            {...comun}
                            className={`min-w-0 rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:outline-none ${borde}`}
                        >
                            {Object.entries(ajuste.opciones ?? {}).map(([valorOpcion, etiqueta]) => (
                                <option key={valorOpcion} value={valorOpcion}>
                                    {etiqueta}
                                </option>
                            ))}
                        </select>
                    ) : ajuste.tipo === 'area' ? (
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

                    {ajuste.tipo === 'secreto' ? (
                        <span className={`apoyo shrink-0 ${ajuste.guardado ? 'text-ok' : 'text-warn'}`}>
                            {ajuste.guardado ? 'guardada' : 'sin guardar'}
                        </span>
                    ) : null}

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
                <span>
                    {correoConfigurado
                        ? 'El correo de salida está configurado.'
                        : 'El correo de salida no está configurado: los avisos no le llegan a nadie.'}{' '}
                    {/* La cuenta se cambia en su propia pantalla: aquí solo se
                        dice si hay una, y desde dónde llegar a ella. */}
                    <Link href="/panel/configuracion/correo" className="underline underline-offset-4 hover:text-chalk">
                        Ver o cambiar la cuenta de correo
                    </Link>
                </span>
            </Estado>
        </section>
    );
}

/**
 * Cómo está el correo de salida, y la prueba que lo confirma.
 *
 * «Configurado» solo dice que hay usuario y clave escritos. Que la clave valga,
 * que el servidor acepte y que el mensaje llegue recién se sabe mandando uno:
 * por eso el botón de prueba está aquí y no en un manual.
 *
 * LAS CLAVES NO SE VEN NI SE ESCRIBEN AQUÍ. Viven en el archivo de
 * configuración del equipo; esta pantalla la abre cualquiera con permiso de
 * configuración, y una clave de aplicación a la vista es una cuenta regalada.
 */
function CorreoDeSalida({ correo }) {
    const { data, setData, post, processing, errors } = useForm({ para: '' });

    const VIAS = {
        smtp: 'el servidor de correo del gimnasio',
        resend: 'la API de Resend',
    };

    return (
        <section className="mb-4 space-y-3 rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo">Correo de salida</h2>

            <dl className="grid gap-x-4 gap-y-1 text-sm sm:grid-cols-[14rem_1fr]">
                <dt className="text-fog">Sale por</dt>
                <dd className="text-chalk">
                    {VIAS[correo.via] ?? correo.via}
                    <span className="apoyo block text-fog">{correo.via_descripcion}</span>
                </dd>

                <dt className="text-fog">Si falla, reintenta por</dt>
                <dd className="text-chalk">
                    {correo.respaldo ? (VIAS[correo.respaldo] ?? correo.respaldo) : 'nada: se da por perdido'}
                </dd>

                <dt className="text-fog">Escribe desde</dt>
                <dd className="text-chalk">
                    {correo.remitente || 'sin dirección'}
                    {correo.nombre_remitente ? <span className="apoyo block text-fog">como «{correo.nombre_remitente}»</span> : null}
                </dd>
            </dl>

            {/* Elegir una vía sin credenciales dejaría al gimnasio sin avisos:
                se avisa antes, no cuando un socio no reciba su recordatorio. */}
            {correo.listas && ! correo.listas.resend ? (
                <p className="apoyo text-fog">
                    La API de Resend no tiene clave cargada, así que no se puede usar todavía. La clave se saca en
                    resend.com y se pega más abajo, en «API de Resend».
                </p>
            ) : null}

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    post('/panel/configuracion/correo/probar', { preserveScroll: true, onSuccess: () => setData('para', '') });
                }}
                className="flex flex-wrap items-start gap-2 border-t border-line pt-3"
            >
                <div className="min-w-0 flex-1">
                    <label htmlFor="correo-prueba" className="apoyo block text-fog">
                        Mandar un correo de prueba a
                    </label>
                    <input
                        id="correo-prueba"
                        type="email"
                        value={data.para}
                        onChange={(e) => setData('para', e.target.value)}
                        placeholder="tu-correo@gmail.com"
                        className={`w-full rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:outline-none ${
                            errors.para ? 'border-danger' : 'border-line focus:border-line-strong'
                        }`}
                    />
                    {errors.para ? <p className="apoyo mt-1 text-danger">{errors.para}</p> : null}
                </div>

                <button
                    type="submit"
                    disabled={processing || ! data.para}
                    className="mt-[1.15rem] shrink-0 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                >
                    {processing ? 'Mandando…' : 'Mandar prueba'}
                </button>
            </form>
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
