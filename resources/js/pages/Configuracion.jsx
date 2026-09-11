import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { AlertTriangleIcon, ChevronRightIcon, Trash2Icon } from 'lucide-react';

/**
 * Configuracion: una sola puerta, con sus apartados arriba.
 *
 * Antes eran cinco entradas sueltas en el menu —planes, convenios, metodos,
 * motivos y papelera— sin nada que dijera que van juntas.
 *
 * LOS CATALOGOS SE ENLAZAN, no se meten aqui dentro: cada uno es una tabla con
 * su alta y su edicion. Lo que si vive aqui son los ajustes, que son
 * formularios cortos y hasta ahora no tenian sitio: estaban escritos a mano
 * dentro del codigo.
 */
export default function Configuracion({ grupos, catalogos }) {
    const [apartado, setApartado] = useState('catalogos');

    /*
     * UN SOLO formulario para todos los apartados, aunque se vean de uno en uno.
     *
     * Las pestañas solo cambian lo que se enseña: lo escrito en «El gimnasio»
     * sigue ahi al volver de «Reglas», y un unico «Guardar» las manda todas. Con
     * un formulario por pestaña, cambiar dos cosas en dos sitios serian dos
     * viajes al servidor y dos ocasiones de irse sin guardar una.
     *
     * Por eso el <form> envuelve TAMBIEN las pestañas: si solo rodeara el
     * apartado visible, cambiar de pestaña con algo escrito se llevaria por
     * delante el boton de guardar.
     */
    const valoresIniciales = Object.fromEntries(
        grupos.flatMap((g) => g.ajustes.map((a) => [a.clave, a.valor])),
    );

    const { data, setData, put, processing, errors, isDirty } = useForm(valoresIniciales);

    /** Que apartados tienen algo sin guardar, para poder decirlo desde otro. */
    const sinGuardar = grupos
        .filter((g) => g.ajustes.some((a) => String(data[a.clave] ?? '') !== String(a.valor ?? '')))
        .map((g) => g.clave);

    // Un error de validacion puede caer en un apartado que no se esta viendo:
    // sin marcarlo en su pestaña, la pantalla no diria nada y pareceria que el
    // guardado no hizo nada.
    const conErrores = grupos
        .filter((g) => g.ajustes.some((a) => errors[a.clave]))
        .map((g) => g.clave);

    const pestanas = [
        { clave: 'catalogos', titulo: 'Catálogos' },
        ...grupos.map((g) => ({ clave: g.clave, titulo: g.titulo })),
        { clave: 'papelera', titulo: 'Papelera' },
    ];

    // Los catalogos avisan de lo que impide trabajar —un plan sin precio, un
    // gimnasio sin metodos de pago—. Desde otra pestaña eso no se ve, asi que
    // la pestaña lo lleva encima.
    const hayAvisoEnCatalogos = catalogos.some((c) => c.aviso);

    const grupoVisible = grupos.find((g) => g.clave === apartado);

    function guardar(e) {
        e.preventDefault();
        put('/panel/configuracion', { preserveScroll: true });
    }

    return (
        <form onSubmit={guardar}>
            <Head title="Configuración" />

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">Configuración</h1>
                <p className="apoyo text-fog">Lo que se toca de tarde en tarde</p>
            </header>

            {/* Los apartados, arriba. Cada uno lleva su marca si tiene algo sin
                guardar o algun aviso: estando en uno no se ve lo que pasa en los
                otros, y sin la marca se guardaria a medias sin notarlo. */}
            <div
                role="tablist"
                aria-label="Apartados de la configuración"
                className="mb-4 flex flex-wrap gap-1 border-b border-line pb-3"
            >
                {pestanas.map((p) => {
                    const activa = apartado === p.clave;
                    const alerta =
                        p.clave === 'catalogos' ? hayAvisoEnCatalogos : conErrores.includes(p.clave);
                    const tieneCambios = sinGuardar.includes(p.clave);

                    return (
                        <button
                            key={p.clave}
                            type="button"
                            role="tab"
                            aria-selected={activa}
                            onClick={() => setApartado(p.clave)}
                            className={`inline-flex items-center gap-1.5 rounded-control border px-3 py-1.5 text-sm transition-colors ${
                                activa
                                    ? 'border-volt bg-volt text-on-volt'
                                    : alerta
                                      ? 'border-warn/40 text-warn hover:bg-surface-2'
                                      : 'border-line text-fog hover:text-chalk'
                            }`}
                        >
                            {p.titulo}

                            {alerta ? (
                                <AlertTriangleIcon className="size-3.5" aria-hidden="true" />
                            ) : tieneCambios ? (
                                <span
                                    title="tiene cambios sin guardar"
                                    className={`size-1.5 rounded-full ${activa ? 'bg-on-volt' : 'bg-warn'}`}
                                />
                            ) : null}
                        </button>
                    );
                })}
            </div>

            {/* La barra de guardar va FUERA del apartado y siempre en el mismo
                sitio: lo escrito en «Reglas» se guarda igual estando en
                «Catálogos», y quien cambia de pestaña no se queda sin botón. */}
            {grupoVisible || isDirty ? (
                <div className="mb-4 flex flex-wrap items-center gap-3">
                    {/* Deshabilitado mientras no se cambie nada: un botón que
                        siempre se puede pulsar invita a guardar sin haber
                        tocado nada y a dudar de si se guardó. */}
                    <button
                        type="submit"
                        disabled={processing || ! isDirty}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                    >
                        {processing ? 'Guardando…' : 'Guardar'}
                    </button>

                    {/* Un «Guardar» manda TODOS los apartados, no solo el que se
                        está viendo: si hay cambios en otro, se dice, o
                        parecería que se guardó solo esto. */}
                    {sinGuardar.length > 0 ? (
                        <span className="apoyo text-warn">
                            {sinGuardar.length === 1 && sinGuardar[0] === apartado
                                ? 'Hay cambios sin guardar.'
                                : `Sin guardar en ${sinGuardar
                                      .map((c) => grupos.find((g) => g.clave === c)?.titulo)
                                      .join(' y ')}. Se guardan todos a la vez.`}
                        </span>
                    ) : null}
                </div>
            ) : null}

            {apartado === 'catalogos' ? (
                <div className="grid gap-3 sm:grid-cols-2">
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
                                    {/* Lo desactivado se dice solo cuando lo
                                        hay: un «y 0 desactivados» es ruido. */}
                                    {c.total > c.activos ? ` · ${c.total - c.activos} desactivados` : ''}
                                </p>

                                {/* Un plan sin precio no se puede vender y un
                                    gimnasio sin métodos no puede cobrar: eso
                                    hay que verlo aquí, no con el socio
                                    delante. */}
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
            ) : apartado === 'papelera' ? (
                <section className="max-w-3xl rounded-panel border border-line bg-surface p-4">
                    <h2 className="rotulo mb-1">Papelera</h2>
                    <p className="apoyo mb-3 text-fog">
                        Lo que se borró y todavía se puede recuperar: socios, planes, pagos.
                    </p>

                    <Link
                        href="/panel/papelera"
                        className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        <Trash2Icon className="size-4" aria-hidden="true" />
                        Ver la papelera
                    </Link>
                </section>
            ) : grupoVisible ? (
                <section className="max-w-3xl rounded-panel border border-line bg-surface p-4">
                    <p className="apoyo mb-4 text-fog">{grupoVisible.descripcion}</p>

                    <div className="space-y-3">
                        {grupoVisible.ajustes.map((ajuste) => (
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
            ) : null}
        </form>
    );
}

/** Un ajuste: su etiqueta, su campo y por qué existe. */
function Ajuste({ ajuste, valor, error, alCambiar }) {
    const cambiado = String(valor ?? '') !== String(ajuste.defecto ?? '');

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

                    {/* Decir cuál era el valor de fábrica ahorra tener que
                        buscarlo en otra parte para volver atrás. */}
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
