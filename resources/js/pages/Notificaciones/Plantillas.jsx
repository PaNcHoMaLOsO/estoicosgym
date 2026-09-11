import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { AlertTriangleIcon, ArrowLeftIcon, EyeIcon } from 'lucide-react';

import { Area, Campo, Texto } from '@/components/Campo';
import { useEnConfiguracion } from '@/components/MarcoConfiguracion';

/**
 * Los textos de los correos que manda el gimnasio.
 *
 * Cada plantilla lleva {variables} entre llaves que se cambian por los datos de
 * cada socio al mandarla. La lista de las que se saben rellenar esta a la vista
 * MIENTRAS SE ESCRIBE, porque escribir una que no exista hace que el correo le
 * llegue al socio con las llaves puestas, y eso ha pasado de verdad: dos
 * plantillas de vencimiento decian «la membresia de {nombre_cliente} vence».
 */
export default function Plantillas({ plantillas, variables }) {
    const [abierta, setAbierta] = useState(null);
    // Dentro de Configuración el menú de la izquierda ya dice dónde se está.
    const enConfiguracion = useEnConfiguracion();

    const rotas = plantillas.filter((p) => p.rotas.length > 0);

    return (
        <>
            <Head title="Plantillas de correo" />

            <header className="mb-5">
                {enConfiguracion ? null : (
                    <Link
                        href="/panel/notificaciones"
                        className="apoyo mb-1 inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                    >
                        <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                        Notificaciones
                    </Link>
                )}
                <h1 className="text-lg font-semibold text-chalk">Plantillas de correo</h1>
                <p className="apoyo text-fog">Lo que se le escribe al socio en cada ocasión</p>
            </header>

            {/* Las rotas, arriba y contadas: son las que estan mandando correos
                con las llaves puestas ahora mismo. */}
            {rotas.length > 0 ? (
                <p className="mb-4 flex items-start gap-2 rounded-panel border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
                    <AlertTriangleIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    {rotas.length === 1
                        ? `«${rotas[0].nombre}» usa una variable que no existe: sale con las llaves puestas en el correo del socio.`
                        : `${rotas.length} plantillas usan variables que no existen: salen con las llaves puestas en el correo del socio.`}
                </p>
            ) : null}

            <div className="grid gap-3 lg:grid-cols-2">
                {plantillas.map((plantilla) => (
                    <article
                        key={plantilla.id}
                        className={`rounded-panel border bg-surface p-4 ${
                            plantilla.rotas.length ? 'border-danger/40' : 'border-line'
                        }`}
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <h2 className="text-sm font-medium text-chalk">{plantilla.nombre}</h2>
                                {plantilla.descripcion ? (
                                    <p className="apoyo mt-0.5 text-fog">{plantilla.descripcion}</p>
                                ) : null}
                            </div>

                            <button
                                type="button"
                                onClick={() => setAbierta(plantilla)}
                                className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                            >
                                Editar
                            </button>
                        </div>

                        <p className="apoyo mt-2 truncate text-fog">
                            Asunto: <span className="text-chalk">{plantilla.asunto_email}</span>
                        </p>

                        <dl className="apoyo mt-2 flex flex-wrap gap-x-4 text-fog">
                            <div>
                                <dt className="inline">Enviada </dt>
                                <dd className="inline text-chalk">
                                    {plantilla.usos} {plantilla.usos === 1 ? 'vez' : 'veces'}
                                </dd>
                            </div>

                            {plantilla.dias_anticipacion > 0 ? (
                                <div>
                                    <dt className="inline">Se manda </dt>
                                    <dd className="inline text-chalk">
                                        {plantilla.dias_anticipacion} días antes
                                    </dd>
                                </div>
                            ) : null}

                            <div>
                                <dt className="inline">Estado: </dt>
                                <dd className="inline text-chalk">
                                    {plantilla.activo && plantilla.enviar_email ? 'En uso' : 'Apagada'}
                                </dd>
                            </div>
                        </dl>

                        {plantilla.rotas.length ? (
                            <p className="apoyo mt-2 text-danger">
                                Usa {plantilla.rotas.map((v) => `{${v}}`).join(', ')}, que no existe.
                            </p>
                        ) : null}
                    </article>
                ))}
            </div>

            {abierta ? (
                <Editor
                    plantilla={abierta}
                    variables={{ ...variables, ...(abierta.extras ?? {}) }}
                    alCerrar={() => setAbierta(null)}
                />
            ) : null}
        </>
    );
}

/**
 * El editor, a pagina completa y no en un dialogo.
 *
 * Aqui se escribe HTML de varias lineas y se mira como queda: en un recuadro de
 * dialogo no cabe nada de eso sin quedar apretado.
 */
function Editor({ plantilla, variables, alCerrar }) {
    const [vista, setVista] = useState(null);
    const [componiendo, setComponiendo] = useState(false);

    const { data, setData, put, processing, errors } = useForm({
        nombre: plantilla.nombre,
        descripcion: plantilla.descripcion ?? '',
        asunto_email: plantilla.asunto_email,
        plantilla_email: plantilla.plantilla_email,
        dias_anticipacion: plantilla.dias_anticipacion,
        activo: plantilla.activo,
        enviar_email: plantilla.enviar_email,
    });

    async function ver() {
        setComponiendo(true);

        try {
            const r = await fetch(
                `/panel/notificaciones/plantillas/${plantilla.id}/vista-previa`,
                { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
            );
            setVista(await r.json());
        } catch (e) {
            setVista({ error: 'No se pudo componer la vista previa.' });
        } finally {
            setComponiendo(false);
        }
    }

    function guardar(e) {
        e.preventDefault();
        put(`/panel/notificaciones/plantillas/${plantilla.id}`, {
            preserveScroll: true,
            onSuccess: () => alCerrar(),
        });
    }

    /** Mete la variable donde esté el cursor, para no tener que escribirla bien. */
    function insertar(nombre) {
        const area = document.querySelector('[name="plantilla_email"]');

        if (!area) {
            return;
        }

        const { selectionStart: desde, selectionEnd: hasta, value } = area;
        const texto = `${value.slice(0, desde)}{${nombre}}${value.slice(hasta)}`;

        setData('plantilla_email', texto);
        area.focus();
    }

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-ink/95 p-4 sm:p-8">
            <div className="mx-auto max-w-5xl">
                <header className="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 className="text-lg font-semibold text-chalk">{plantilla.nombre}</h2>
                        <p className="apoyo text-fog">
                            Se manda cuando: {plantilla.descripcion ?? plantilla.codigo}
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={alCerrar}
                        className="apoyo text-fog transition-colors hover:text-chalk"
                    >
                        Cerrar
                    </button>
                </header>

                <form onSubmit={guardar} className="grid gap-4 lg:grid-cols-[1fr_18rem]">
                    <div className="space-y-3">
                        <Campo etiqueta="Nombre" nombre="nombre" error={errors.nombre} requerido>
                            <Texto
                                nombre="nombre"
                                valor={data.nombre}
                                alCambiar={(v) => setData('nombre', v)}
                            />
                        </Campo>

                        <Campo
                            etiqueta="Asunto"
                            nombre="asunto_email"
                            error={errors.asunto_email}
                            requerido
                            ayuda="Lo que ve el socio en su bandeja antes de abrirlo."
                        >
                            <Texto
                                nombre="asunto_email"
                                valor={data.asunto_email}
                                alCambiar={(v) => setData('asunto_email', v)}
                            />
                        </Campo>

                        <Campo
                            etiqueta="El correo"
                            nombre="plantilla_email"
                            error={errors.plantilla_email}
                            requerido
                            ayuda="HTML. Pulsa una variable de la derecha para meterla donde esté el cursor."
                        >
                            <textarea
                                id="plantilla_email"
                                name="plantilla_email"
                                rows={18}
                                value={data.plantilla_email}
                                onChange={(e) => setData('plantilla_email', e.target.value)}
                                spellCheck={false}
                                className="w-full rounded-control border border-line bg-surface px-2 py-1.5 font-mono text-xs text-chalk focus:border-line-strong focus:outline-none"
                            />
                        </Campo>

                        <Campo
                            etiqueta="Cuántos días antes se manda"
                            nombre="dias_anticipacion"
                            error={errors.dias_anticipacion}
                            ayuda="Solo para las que salen solas, como el aviso de vencimiento. Cero si no aplica."
                        >
                            <Texto
                                nombre="dias_anticipacion"
                                tipo="number"
                                min="0"
                                valor={data.dias_anticipacion}
                                alCambiar={(v) => setData('dias_anticipacion', v)}
                            />
                        </Campo>

                        <label className="flex items-center gap-2 text-sm text-chalk">
                            <input
                                type="checkbox"
                                checked={data.enviar_email}
                                onChange={(e) => setData('enviar_email', e.target.checked)}
                                className="size-4 accent-[var(--color-volt)]"
                            />
                            Se manda por correo
                        </label>

                        <label className="flex items-center gap-2 text-sm text-chalk">
                            <input
                                type="checkbox"
                                checked={data.activo}
                                onChange={(e) => setData('activo', e.target.checked)}
                                className="size-4 accent-[var(--color-volt)]"
                            />
                            En uso
                        </label>

                        <div className="flex items-center gap-3 pt-1">
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                {processing ? 'Guardando…' : 'Guardar'}
                            </button>

                            <button
                                type="button"
                                onClick={ver}
                                disabled={componiendo}
                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-2 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                            >
                                <EyeIcon className="size-4" aria-hidden="true" />
                                {componiendo ? 'Componiendo…' : 'Ver con datos reales'}
                            </button>
                        </div>
                    </div>

                    {/* La lista, al lado y siempre visible: es la unica forma de
                        saber que se puede escribir entre llaves. */}
                    <aside className="rounded-panel border border-line bg-surface p-3">
                        <h3 className="rotulo mb-2">Variables</h3>
                        <p className="apoyo mb-2 text-fog">
                            Se cambian por los datos del socio al mandar el correo.
                        </p>

                        <ul className="max-h-[28rem] space-y-0.5 overflow-y-auto">
                            {Object.entries(variables).map(([nombre, que]) => (
                                <li key={nombre}>
                                    <button
                                        type="button"
                                        onClick={() => insertar(nombre)}
                                        className="w-full rounded-control px-1.5 py-1 text-left transition-colors hover:bg-surface-2"
                                    >
                                        <span className="block font-mono text-xs text-volt">
                                            {`{${nombre}}`}
                                        </span>
                                        <span className="apoyo block text-fog">{que}</span>
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </aside>
                </form>

                {vista ? (
                    <div className="mt-4">
                        {vista.error ? (
                            <p className="rounded-panel border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
                                {vista.error}
                            </p>
                        ) : (
                            <div className="overflow-hidden rounded-panel border border-line">
                                <div className="border-b border-line bg-surface-2 px-3 py-2 text-sm">
                                    <p className="text-fog">
                                        Con los datos de{' '}
                                        <span className="text-chalk">{vista.socio}</span>
                                    </p>
                                    <p className="text-fog">
                                        Asunto: <span className="text-chalk">{vista.asunto}</span>
                                    </p>
                                </div>

                                {/* Aislado: el correo trae su propio HTML y sus
                                    estilos se pisarian con los del panel. */}
                                <iframe
                                    title="Cómo se verá el correo"
                                    srcDoc={vista.contenido}
                                    sandbox=""
                                    className="h-[32rem] w-full bg-white"
                                />
                            </div>
                        )}
                    </div>
                ) : null}
            </div>
        </div>
    );
}
