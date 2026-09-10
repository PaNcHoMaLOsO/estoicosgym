import { Head, Link, useForm } from '@inertiajs/react';
import { AlertTriangleIcon, ChevronRightIcon, Trash2Icon } from 'lucide-react';

/**
 * Configuracion: una sola puerta.
 *
 * Antes eran cinco entradas sueltas en el menu —planes, convenios, metodos,
 * motivos y papelera— sin nada que dijera que van juntas.
 *
 * LOS CATALOGOS SE ENLAZAN, no se meten aqui dentro: cada uno es una tabla con
 * su alta y su edicion, y apilar cuatro tablas haria esta pantalla el doble de
 * larga sin que nada se encuentre antes. Lo que si vive aqui son los ajustes,
 * que son formularios cortos y hasta ahora no tenian sitio en ninguna parte:
 * estaban escritos a mano dentro del codigo.
 */
export default function Configuracion({ grupos, catalogos }) {
    // Un solo formulario para todos los ajustes: se cambian dos o tres cosas de
    // golpe y guardarlas una a una serian tres viajes al servidor.
    const valoresIniciales = Object.fromEntries(
        grupos.flatMap((g) => g.ajustes.map((a) => [a.clave, a.valor])),
    );

    const { data, setData, put, processing, errors, isDirty } = useForm(valoresIniciales);

    function guardar(e) {
        e.preventDefault();
        put('/panel/configuracion', { preserveScroll: true });
    }

    return (
        <>
            <Head title="Configuración" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Configuración</h1>
                <p className="apoyo text-fog">Lo que se toca de tarde en tarde</p>
            </header>

            {/* LOS CATALOGOS PRIMERO: es a lo que se viene la mayoria de las
                veces —cambiar un precio, añadir un método—. Los ajustes de
                abajo se tocan una vez y no se vuelven a mirar. */}
            <div className="mb-6 grid gap-3 sm:grid-cols-2">
                {catalogos.map((c) => (
                    <Link
                        key={c.href}
                        href={c.href}
                        className={`group flex items-start justify-between gap-3 rounded-panel border bg-surface p-4 transition-colors hover:bg-surface-2 ${
                            c.aviso ? 'border-warn/40' : 'border-line hover:border-line-strong'
                        }`}
                    >
                        <div className="min-w-0">
                            <p className="text-sm font-medium text-chalk">{c.titulo}</p>
                            <p className="apoyo mt-0.5 text-fog">{c.descripcion}</p>

                            <p className="apoyo mt-1 text-fog">
                                {c.activos} en uso
                                {/* Lo desactivado se dice solo cuando lo hay: un
                                    «y 0 desactivados» es ruido. */}
                                {c.total > c.activos ? ` · ${c.total - c.activos} desactivados` : ''}
                            </p>

                            {/* Un plan sin precio no se puede vender y un
                                gimnasio sin metodos no puede cobrar: eso hay que
                                verlo aqui, no con el socio delante. */}
                            {c.aviso ? (
                                <p className="apoyo mt-1 flex items-start gap-1 text-warn">
                                    <AlertTriangleIcon
                                        className="mt-0.5 size-3 shrink-0"
                                        aria-hidden="true"
                                    />
                                    {c.aviso}
                                </p>
                            ) : null}
                        </div>

                        <ChevronRightIcon
                            className="mt-0.5 size-4 shrink-0 text-fog transition-transform group-hover:translate-x-0.5"
                            aria-hidden="true"
                        />
                    </Link>
                ))}
            </div>

            <form onSubmit={guardar} className="max-w-3xl space-y-5">
                {grupos.map((grupo) => (
                    <section key={grupo.clave} className="rounded-panel border border-line bg-surface p-4">
                        <div className="mb-3">
                            <h2 className="rotulo">{grupo.titulo}</h2>
                            <p className="apoyo mt-0.5 text-fog">{grupo.descripcion}</p>
                        </div>

                        <div className="space-y-3">
                            {grupo.ajustes.map((ajuste) => (
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

                <div className="flex items-center gap-3">
                    {/* Deshabilitado mientras no se cambie nada: un boton que
                        siempre se puede pulsar invita a guardar sin haber
                        tocado nada y a dudar de si se guardo. */}
                    <button
                        type="submit"
                        disabled={processing || ! isDirty}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                    >
                        {processing ? 'Guardando…' : 'Guardar'}
                    </button>

                    {isDirty ? (
                        <span className="apoyo text-warn">Hay cambios sin guardar.</span>
                    ) : null}
                </div>
            </form>

            <section className="mt-6 rounded-panel border border-line bg-surface p-4">
                <h2 className="rotulo mb-1">Papelera</h2>
                <p className="apoyo mb-3 text-fog">
                    Lo que se borró y todavía se puede recuperar.
                </p>

                <Link
                    href="/panel/papelera"
                    className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                >
                    <Trash2Icon className="size-4" aria-hidden="true" />
                    Ver la papelera
                </Link>
            </section>
        </>
    );
}

/** Un ajuste: su etiqueta, su campo y por qué existe. */
function Ajuste({ ajuste, valor, error, alCambiar }) {
    const cambiado = String(valor) !== String(ajuste.defecto);

    return (
        <div className="grid gap-1 sm:grid-cols-[16rem_1fr] sm:items-start sm:gap-4">
            <label htmlFor={ajuste.clave} className="pt-1.5 text-sm text-chalk">
                {ajuste.etiqueta}
            </label>

            <div className="min-w-0">
                <div className="flex items-center gap-2">
                    <input
                        id={ajuste.clave}
                        name={ajuste.clave}
                        type={ajuste.tipo === 'numero' ? 'number' : 'text'}
                        min={ajuste.min ?? undefined}
                        max={ajuste.max ?? undefined}
                        value={valor ?? ''}
                        onChange={(e) => alCambiar(e.target.value)}
                        aria-invalid={error ? 'true' : undefined}
                        className={`min-w-0 rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:outline-none ${
                            ajuste.tipo === 'numero' ? 'w-28 tabular-nums' : 'w-full'
                        } ${error ? 'border-danger' : 'border-line focus:border-line-strong'}`}
                    />

                    {ajuste.unidad ? (
                        <span className="apoyo shrink-0 text-fog">{ajuste.unidad}</span>
                    ) : null}

                    {/* Decir cual era el valor de fabrica ayuda a volver atras
                        sin tener que buscarlo en ninguna parte. */}
                    {cambiado ? (
                        <button
                            type="button"
                            onClick={() => alCambiar(String(ajuste.defecto))}
                            className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                        >
                            volver a {ajuste.defecto || '(vacío)'}
                        </button>
                    ) : null}
                </div>

                {error ? (
                    <p className="apoyo mt-0.5 text-danger">{error}</p>
                ) : ajuste.ayuda ? (
                    <p className="apoyo mt-0.5 text-fog">{ajuste.ayuda}</p>
                ) : null}
            </div>
        </div>
    );
}
