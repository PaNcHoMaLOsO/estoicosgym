import { Head, Link, useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';
import Nota from '@/components/Nota';
import { AlertTriangleIcon, ExternalLinkIcon, EyeIcon } from 'lucide-react';

/**
 * El contrato, los términos y condiciones y la política de privacidad.
 *
 * Un texto por pestaña, con sus {variables} al lado —se ponen con un toque— y
 * una vista previa con datos de muestra antes de guardar.
 *
 * LO QUE YA FIRMÓ ALGUIEN NO SE PISA: al guardar un texto firmado se crea la
 * versión siguiente, y lo que aceptó cada socio sigue siendo lo que aceptó.
 * La pantalla lo dice ANTES de guardar, no después.
 */
export default function TextosLegales({ tipo, tipos, titulo, descripcion, texto, variables, publica, historial }) {
    const area = useRef(null);
    const [vista, setVista] = useState(null);
    const [componiendo, setComponiendo] = useState(false);

    const { data, setData, put, processing, errors, isDirty } = useForm({ contenido: texto.contenido });

    function insertar(nombre) {
        const campo = area.current;
        const marca = `{${nombre}}`;

        if (!campo) {
            setData('contenido', `${data.contenido}${marca}`);

            return;
        }

        const inicio = campo.selectionStart ?? data.contenido.length;
        const fin = campo.selectionEnd ?? inicio;

        setData('contenido', data.contenido.slice(0, inicio) + marca + data.contenido.slice(fin));

        // El cursor queda después de lo puesto, para seguir escribiendo.
        requestAnimationFrame(() => {
            campo.focus();
            campo.setSelectionRange(inicio + marca.length, inicio + marca.length);
        });
    }

    async function verVistaPrevia() {
        setComponiendo(true);

        try {
            const respuesta = await fetch(`/panel/textos-legales/${tipo}/vista-previa`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                },
                body: JSON.stringify({ contenido: data.contenido }),
            });

            setVista(await respuesta.json());
        } catch (e) {
            setVista({ error: 'No se pudo armar la vista previa.' });
        } finally {
            setComponiendo(false);
        }
    }

    function guardar(e) {
        e.preventDefault();

        put(`/panel/textos-legales/${tipo}`, {
            preserveScroll: true,
            onSuccess: () => setVista(null),
        });
    }

    const firmas = texto.firmas;

    return (
        <>
            <Head title={titulo} />

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">{titulo}</h1>
                <p className="apoyo text-fog">{descripcion}</p>
            </header>

            {/* Los tres textos, uno al lado del otro: se editan juntos y se
                aceptan juntos al firmar. */}
            <nav aria-label="Textos legales" className="mb-4 flex flex-wrap gap-1 border-b border-line">
                {tipos.map((t) => (
                    <Link
                        key={t.clave}
                        href={`/panel/textos-legales/${t.clave}`}
                        preserveScroll
                        className={`-mb-px border-b-2 px-3 py-2 text-sm transition-colors ${
                            t.clave === tipo
                                ? 'border-volt font-medium text-chalk'
                                : 'border-transparent text-fog hover:text-chalk'
                        }`}
                    >
                        {t.titulo}
                    </Link>
                ))}
            </nav>

            {texto.base ? (
                <Nota titulo="Es el texto base que trae el sistema" className="mb-4">
                    Léelo y ajústalo a tu gimnasio, idealmente con un abogado, antes de mandar
                    contratos a firmar. Al guardarlo queda como revisado.
                </Nota>
            ) : null}

            <p className="apoyo mb-3 text-fog">
                Versión {texto.version} ·{' '}
                {texto.por ? `guardada por ${texto.por} el ${texto.guardado}` : 'texto base, sin revisar'} ·{' '}
                {firmas === 0
                    ? 'nadie la ha firmado todavía'
                    : firmas === 1
                      ? 'la firmó 1 socio'
                      : `la firmaron ${firmas} socios`}
                {publica ? (
                    <>
                        {' · '}
                        <a
                            href={publica}
                            target="_blank"
                            rel="noopener"
                            className="inline-flex items-center gap-1 text-chalk hover:underline"
                        >
                            Ver en la web
                            <ExternalLinkIcon className="size-3" aria-hidden="true" />
                        </a>
                    </>
                ) : null}
            </p>

            <form onSubmit={guardar} className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_17rem]">
                <div className="min-w-0">
                    <label htmlFor="contenido" className="sr-only">
                        {titulo}
                    </label>
                    <textarea
                        id="contenido"
                        ref={area}
                        value={data.contenido}
                        onChange={(e) => setData('contenido', e.target.value)}
                        rows={26}
                        className={`w-full rounded-control border bg-surface px-3 py-2.5 font-mono text-[13px] leading-relaxed text-chalk focus:outline-none ${
                            errors.contenido ? 'border-danger' : 'border-line focus:border-line-strong'
                        }`}
                    />
                    {errors.contenido ? <p className="apoyo mt-1 text-danger">{errors.contenido}</p> : null}

                    <p className="apoyo mt-2 text-fog">
                        Formato: <code className="text-chalk">## Título</code> ·{' '}
                        <code className="text-chalk">**negrita**</code> ·{' '}
                        <code className="text-chalk">- un punto de una lista</code>. Deja una línea en blanco
                        entre párrafos.
                    </p>

                    {/* Se dice ANTES de guardar qué va a pasar con la versión. */}
                    <p className="apoyo mt-1 text-fog">
                        {firmas > 0
                            ? `Esta versión ya la firmaron. Al guardar se crea la versión ${texto.version + 1}, y lo que firmó cada uno no cambia.`
                            : 'Como nadie la ha firmado, al guardar se corrige esta misma versión.'}
                    </p>

                    <div className="mt-3 flex flex-wrap items-center gap-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-control bg-volt px-3.5 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                        >
                            {processing ? 'Guardando…' : 'Guardar'}
                        </button>
                        <button
                            type="button"
                            onClick={verVistaPrevia}
                            disabled={componiendo}
                            className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-2 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                        >
                            <EyeIcon className="size-4" aria-hidden="true" />
                            {componiendo ? 'Armando…' : 'Vista previa'}
                        </button>
                        {isDirty ? <span className="apoyo text-warn">Hay cambios sin guardar.</span> : null}
                    </div>
                </div>

                {/* La lista, al lado y siempre a la vista: es la única forma de
                    saber qué se puede escribir entre llaves. */}
                <aside className="self-start rounded-panel border border-line bg-surface p-3">
                    <h2 className="rotulo mb-1">Variables</h2>
                    <p className="apoyo mb-2 text-fog">
                        {tipo === 'contrato'
                            ? 'Se cambian por los datos del gimnasio, del socio y de su plan.'
                            : 'Se cambian por los datos del gimnasio.'}{' '}
                        Toca una para ponerla donde está el cursor.
                    </p>

                    <ul className="max-h-[30rem] space-y-0.5 overflow-y-auto">
                        {Object.entries(variables).map(([nombre, que]) => (
                            <li key={nombre}>
                                <button
                                    type="button"
                                    onClick={() => insertar(nombre)}
                                    className="w-full rounded-control px-1.5 py-1 text-left transition-colors hover:bg-surface-2"
                                >
                                    <span className="block font-mono text-xs text-volt">{`{${nombre}}`}</span>
                                    <span className="apoyo block text-fog">{que}</span>
                                </button>
                            </li>
                        ))}
                    </ul>
                </aside>
            </form>

            {vista ? (
                <section className="mt-5">
                    <h2 className="rotulo mb-2">
                        Vista previa{tipo === 'contrato' ? ' · con un socio de muestra' : ''}
                    </h2>

                    {vista.error ? (
                        <Nota tono="peligro">{vista.error}</Nota>
                    ) : (
                        <>
                            {vista.desconocidas?.length ? (
                                <p className="mb-2 flex items-start gap-1.5 text-sm text-danger">
                                    <AlertTriangleIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                                    No se conocen {vista.desconocidas.map((v) => `{${v}}`).join(', ')}: saldrían
                                    con las llaves puestas.
                                </p>
                            ) : null}

                            {/* Viene del servidor ya limpio: el HTML escrito a
                                mano se quita y los datos se escapan. */}
                            <div
                                className="rounded-panel border border-line bg-[#fff] px-6 py-7 text-[15px] leading-relaxed text-[#16161a] [&_a]:underline [&_h1]:mb-4 [&_h1]:text-xl [&_h1]:font-semibold [&_h1]:uppercase [&_h2]:mt-6 [&_h2]:mb-2 [&_h2]:font-semibold [&_h3]:mt-4 [&_h3]:font-semibold [&_li]:my-1 [&_ol]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-3 [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5"
                                dangerouslySetInnerHTML={{ __html: vista.html }}
                            />
                        </>
                    )}
                </section>
            ) : null}

            {historial.length > 1 ? (
                <section className="mt-6">
                    <h2 className="rotulo mb-2">Versiones</h2>
                    <ul className="divide-y divide-line overflow-hidden rounded-panel border border-line">
                        {historial.map((v) => (
                            <li
                                key={v.version}
                                className="flex flex-wrap items-center justify-between gap-2 bg-surface px-3 py-2 text-sm"
                            >
                                <span className="text-chalk">
                                    Versión {v.version}
                                    {v.version === texto.version ? ' · la vigente' : ''}
                                </span>
                                <span className="apoyo text-fog">
                                    {v.por ? `${v.por}, ` : 'Texto base, '}
                                    {v.guardado} · {v.firmas === 1 ? '1 firma' : `${v.firmas} firmas`}
                                </span>
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}
        </>
    );
}
