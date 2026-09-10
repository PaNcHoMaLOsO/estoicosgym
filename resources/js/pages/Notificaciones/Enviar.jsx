import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ArrowLeftIcon, MailIcon } from 'lucide-react';

import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

/**
 * Escribirle a un socio a mano.
 *
 * El correo SE MIRA ANTES de mandarlo. Sale una sola vez y no se puede
 * recoger: la vista previa no es un adorno, es el ultimo sitio donde se puede
 * ver que la plantilla dice lo que tiene que decir y que las variables se
 * rellenaron con los datos de esta persona.
 */
export default function Enviar({ preseleccionado, plantillas, formToken }) {
    const [socio, setSocio] = useState(preseleccionado ?? null);
    const [busqueda, setBusqueda] = useState('');
    const [resultados, setResultados] = useState(null);
    const [buscando, setBuscando] = useState(false);

    const [vista, setVista] = useState(null);
    const [componiendo, setComponiendo] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        form_submit_token: formToken,
        cliente_id: preseleccionado?.id ?? '',
        plantilla_id: '',
        nota: '',
    });

    /*
     * Al socio se le BUSCA. Solo aparece quien tiene correo: a los demas no se
     * les puede escribir por aqui, y ofrecerlos solo sirve para llegar al final
     * y encontrarse con que no.
     */
    useEffect(() => {
        if (busqueda.trim().length < 2) {
            setResultados(null);

            return undefined;
        }

        setBuscando(true);

        const temporizador = setTimeout(async () => {
            try {
                const r = await fetch(
                    `/panel/notificaciones/buscar-socio?q=${encodeURIComponent(busqueda)}`,
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                );
                const j = await r.json();
                setResultados(j.clientes ?? []);
            } catch (e) {
                setResultados([]);
            } finally {
                setBuscando(false);
            }
        }, 300);

        return () => clearTimeout(temporizador);
    }, [busqueda]);

    // La vista previa caduca en cuanto cambia algo: enseñar la de antes al lado
    // de una plantilla distinta es peor que no enseñar ninguna.
    useEffect(() => {
        setVista(null);
    }, [data.cliente_id, data.plantilla_id, data.nota]);

    async function componer() {
        if (!data.cliente_id || !data.plantilla_id) {
            return;
        }

        setComponiendo(true);

        try {
            const r = await fetch('/panel/notificaciones/vista-previa', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                },
                body: JSON.stringify({
                    cliente_id: data.cliente_id,
                    plantilla_id: data.plantilla_id,
                    nota: data.nota,
                }),
            });

            setVista(r.ok ? await r.json() : { error: 'No se pudo componer el correo.' });
        } catch (e) {
            setVista({ error: 'No se pudo contactar con el servidor.' });
        } finally {
            setComponiendo(false);
        }
    }

    function elegir(cliente) {
        setSocio(cliente);
        setData('cliente_id', cliente.id);
        setBusqueda('');
        setResultados(null);
    }

    function enviar(e) {
        e.preventDefault();
        post('/panel/notificaciones/enviar', { preserveScroll: true });
    }

    const plantillaElegida = plantillas.find((p) => String(p.id) === String(data.plantilla_id));

    return (
        <>
            <Head title="Escribir a un socio" />

            <header className="mb-5">
                <Link
                    href="/panel/notificaciones"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Notificaciones
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Escribir a un socio</h1>
            </header>

            <form onSubmit={enviar} className="max-w-3xl space-y-5">
                <Grupo titulo="¿A quién?">
                    {socio ? (
                        <div className="rounded-panel border border-line bg-surface-2 p-3 text-sm">
                            <div className="flex items-start justify-between gap-3">
                                <p className="font-medium text-chalk">
                                    {socio.nombre}
                                    <span className="apoyo block text-fog">{socio.email}</span>
                                </p>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSocio(null);
                                        setData('cliente_id', '');
                                    }}
                                    className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                                >
                                    Cambiar
                                </button>
                            </div>

                            <dl className="apoyo mt-2 grid grid-cols-2 gap-x-4 text-fog">
                                <div>
                                    <dt className="inline">Plan: </dt>
                                    <dd className="inline text-chalk">{socio.plan ?? 'sin membresía'}</dd>
                                </div>
                                <div>
                                    <dt className="inline">Vence: </dt>
                                    <dd className="inline text-chalk">{socio.vence ?? '—'}</dd>
                                </div>
                            </dl>
                        </div>
                    ) : (
                        <Campo
                            etiqueta="Busca al socio"
                            nombre="buscar_socio"
                            error={errors.cliente_id}
                            requerido
                            ayuda="Por nombre, RUT o correo. Solo aparece quien tiene correo registrado."
                        >
                            <Texto
                                nombre="buscar_socio"
                                tipo="search"
                                valor={busqueda}
                                alCambiar={setBusqueda}
                                placeholder="Escribe al menos dos letras"
                                autoFocus
                            />

                            {busqueda.trim().length >= 2 ? (
                                buscando ? (
                                    <p className="apoyo mt-2 text-fog">Buscando…</p>
                                ) : resultados && resultados.length === 0 ? (
                                    <p className="apoyo mt-2 text-fog">
                                        Nadie coincide, o a quien buscas no tiene correo en su ficha.
                                    </p>
                                ) : resultados ? (
                                    <ul className="mt-2 max-h-64 divide-y divide-line overflow-y-auto rounded-control border border-line">
                                        {resultados.map((c) => (
                                            <li key={c.id}>
                                                <button
                                                    type="button"
                                                    onClick={() => elegir(c)}
                                                    className="block w-full px-3 py-2 text-left text-sm transition-colors hover:bg-surface-2"
                                                >
                                                    <span className="block truncate text-chalk">
                                                        {c.nombre}
                                                        {/* Un socio dado de baja sigue teniendo
                                                            correo, pero conviene saberlo antes
                                                            de escribirle. */}
                                                        {!c.activo ? (
                                                            <span className="ml-1 text-warn">· inactivo</span>
                                                        ) : null}
                                                    </span>
                                                    <span className="apoyo block text-fog">{c.email}</span>
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                ) : null
                            ) : null}
                        </Campo>
                    )}
                </Grupo>

                {socio ? (
                    <>
                        <Grupo titulo="¿Qué se le manda?">
                            <Campo
                                etiqueta="Plantilla"
                                nombre="plantilla_id"
                                error={errors.plantilla_id}
                                requerido
                                ayuda={plantillaElegida?.descripcion}
                            >
                                <Seleccion
                                    nombre="plantilla_id"
                                    valor={data.plantilla_id}
                                    alCambiar={(v) => setData('plantilla_id', v)}
                                    opciones={plantillas.map((p) => ({
                                        valor: String(p.id),
                                        etiqueta: p.nombre,
                                    }))}
                                    vacio="Elige qué mandarle…"
                                />
                            </Campo>

                            <Campo
                                etiqueta="Nota"
                                nombre="nota"
                                error={errors.nota}
                                ayuda="Opcional. Se añade al final del correo, en un recuadro aparte."
                            >
                                <Area
                                    nombre="nota"
                                    valor={data.nota}
                                    alCambiar={(v) => setData('nota', v)}
                                />
                            </Campo>
                        </Grupo>

                        {/* Ver el correo antes es OBLIGATORIO: sale una sola vez
                            y no se puede recoger. */}
                        {data.plantilla_id ? (
                            <div className="space-y-2">
                                <button
                                    type="button"
                                    onClick={componer}
                                    disabled={componiendo}
                                    className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                                >
                                    {componiendo ? 'Componiendo…' : vista ? 'Ver de nuevo' : 'Ver cómo queda'}
                                </button>

                                {vista?.error ? (
                                    <p className="rounded-panel border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
                                        {vista.error}
                                    </p>
                                ) : vista ? (
                                    <div className="overflow-hidden rounded-panel border border-line">
                                        <div className="border-b border-line bg-surface-2 px-3 py-2 text-sm">
                                            <p className="text-fog">
                                                Para: <span className="text-chalk">{vista.destino}</span>
                                            </p>
                                            <p className="text-fog">
                                                Asunto: <span className="text-chalk">{vista.asunto}</span>
                                            </p>
                                        </div>

                                        {/* Una variable a medio rellenar sale con
                                            las llaves y todo en el correo del
                                            socio: se avisa aqui y el servidor
                                            se niega a mandarlo. */}
                                        {vista.pendientes?.length ? (
                                            <p className="border-b border-line bg-danger/5 px-3 py-2 text-sm text-danger">
                                                Esta plantilla usa{' '}
                                                {vista.pendientes.map((v) => `{${v}}`).join(', ')} y no hay
                                                con qué rellenarlo. Corrígela en Plantillas: así no se puede
                                                mandar.
                                            </p>
                                        ) : null}

                                        {/* En un iframe aislado: el correo trae su
                                            propio HTML con sus estilos, y sueltos
                                            en la pagina se pisarian con los del
                                            panel. */}
                                        <iframe
                                            title="Cómo se verá el correo"
                                            srcDoc={vista.contenido}
                                            sandbox=""
                                            className="h-96 w-full bg-white"
                                        />
                                    </div>
                                ) : null}
                            </div>
                        ) : null}

                        {errors.envio ? (
                            <p className="rounded-panel border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
                                {errors.envio}
                            </p>
                        ) : null}

                        <div className="flex items-center gap-3">
                            <button
                                type="submit"
                                disabled={
                                    processing
                                    || !data.plantilla_id
                                    || !vista
                                    || vista.error
                                    || vista.pendientes?.length > 0
                                }
                                className="inline-flex items-center gap-1.5 rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                <MailIcon className="size-4" aria-hidden="true" />
                                {processing ? 'Enviando…' : 'Enviar'}
                            </button>

                            <Link href="/panel/notificaciones" className="apoyo text-fog hover:text-chalk">
                                Cancelar
                            </Link>

                            {!vista ? (
                                <span className="apoyo text-fog">
                                    Míralo antes de mandarlo.
                                </span>
                            ) : null}
                        </div>
                    </>
                ) : null}
            </form>
        </>
    );
}
