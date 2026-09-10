import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { AlertTriangleIcon, ArrowLeftIcon, SendIcon, UsersIcon } from 'lucide-react';

import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

/**
 * Un mismo aviso a un grupo de socios.
 *
 * Lo que mas importa de esta pantalla es que se vea A CUANTA GENTE se le va a
 * escribir antes de pulsar el boton, y quienes son. Un correo a doscientas
 * personas no se puede recoger, y «182 socios» es un numero: los numeros no
 * dejan ver que ahi dentro esta quien se dio de baja ayer.
 */
export default function Masivo({ grupos, membresias, variables, tope, formToken }) {
    const [lista, setLista] = useState(null);
    const [cargandoLista, setCargandoLista] = useState(false);
    const [vista, setVista] = useState(null);

    const { data, setData, post, processing, errors } = useForm({
        form_submit_token: formToken,
        grupo: '',
        id_membresia: '',
        asunto: '',
        mensaje: '',
    });

    const elegido = grupos.find((g) => g.clave === data.grupo);

    // Al cambiar el grupo, la lista de antes ya no vale: enseñarla al lado del
    // grupo nuevo es peor que no enseñar ninguna.
    useEffect(() => {
        setLista(null);
        setVista(null);
    }, [data.grupo, data.id_membresia]);

    // Y la vista previa caduca en cuanto cambia el texto.
    useEffect(() => {
        setVista(null);
    }, [data.asunto, data.mensaje]);

    async function pedir(ruta, cuerpo) {
        const r = await fetch(ruta, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
            },
            body: JSON.stringify(cuerpo),
        });

        return r.ok ? r.json() : { error: 'No se pudo consultar.' };
    }

    async function verQuienes() {
        setCargandoLista(true);

        setLista(await pedir('/panel/notificaciones/masivo/destinatarios', {
            grupo: data.grupo,
            id_membresia: data.id_membresia || null,
        }));

        setCargandoLista(false);
    }

    async function verComoQueda() {
        setVista(await pedir('/panel/notificaciones/masivo/vista-previa', {
            grupo: data.grupo,
            id_membresia: data.id_membresia || null,
            asunto: data.asunto,
            mensaje: data.mensaje,
        }));
    }

    function enviar(e) {
        e.preventDefault();
        post('/panel/notificaciones/masivo');
    }

    // Cuantos hay de verdad: la cuenta del grupo si no se ha filtrado por plan,
    // y la de la lista pedida si si.
    const cuantos = lista?.cuantos ?? (data.id_membresia ? null : elegido?.cuantos);
    const sePasa = cuantos !== null && cuantos !== undefined && cuantos > tope;

    return (
        <>
            <Head title="Aviso a un grupo" />

            <header className="mb-5">
                <Link
                    href="/panel/notificaciones"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Notificaciones
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Aviso a un grupo</h1>
                <p className="apoyo text-fog">
                    El mismo correo a varios socios, con el nombre de cada uno.
                </p>
            </header>

            <form onSubmit={enviar} className="max-w-3xl space-y-5">
                <Grupo titulo="¿A quiénes?">
                    <Campo
                        etiqueta="Grupo"
                        nombre="grupo"
                        error={errors.grupo}
                        requerido
                        ayuda={elegido?.explicacion}
                    >
                        <Seleccion
                            nombre="grupo"
                            valor={data.grupo}
                            alCambiar={(v) => setData('grupo', v)}
                            opciones={grupos.map((g) => ({
                                valor: g.clave,
                                // La cuenta EN LA PROPIA OPCION: es el dato que
                                // decide, y tenerlo que buscar despues de elegir
                                // es tarde.
                                etiqueta: `${g.titulo} (${g.cuantos})`,
                            }))}
                            vacio="Elige a quiénes…"
                        />
                    </Campo>

                    {data.grupo ? (
                        <Campo
                            etiqueta="Solo de un plan"
                            nombre="id_membresia"
                            error={errors.id_membresia}
                            ayuda="Opcional. Acota el grupo a quienes tienen ese plan vigente."
                        >
                            <Seleccion
                                nombre="id_membresia"
                                valor={data.id_membresia}
                                alCambiar={(v) => setData('id_membresia', v)}
                                opciones={membresias.map((m) => ({
                                    valor: String(m.id),
                                    etiqueta: m.nombre,
                                }))}
                                vacio="Cualquier plan"
                            />
                        </Campo>
                    ) : null}

                    {data.grupo ? (
                        <div className="space-y-2">
                            <button
                                type="button"
                                onClick={verQuienes}
                                disabled={cargandoLista}
                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                            >
                                <UsersIcon className="size-4" aria-hidden="true" />
                                {cargandoLista ? 'Contando…' : 'Ver quiénes son'}
                            </button>

                            {lista?.error ? (
                                <p className="apoyo text-danger">{lista.error}</p>
                            ) : lista ? (
                                <div className="rounded-panel border border-line bg-surface-2 p-3">
                                    <p className="text-sm font-medium text-chalk">
                                        {lista.cuantos}{' '}
                                        {lista.cuantos === 1 ? 'socio' : 'socios'}
                                    </p>

                                    {lista.cuantos > 0 ? (
                                        <ul className="apoyo mt-1.5 max-h-48 space-y-0.5 overflow-y-auto text-fog">
                                            {lista.socios.map((s) => (
                                                <li key={s.email}>
                                                    {s.nombre} · {s.email}
                                                </li>
                                            ))}
                                        </ul>
                                    ) : null}
                                </div>
                            ) : null}
                        </div>
                    ) : null}

                    {/* El tope no es un capricho: los correos salen uno a uno en
                        la misma peticion y, pasado ese numero, el servidor corta
                        antes de terminar. */}
                    {sePasa ? (
                        <p className="flex items-start gap-2 rounded-panel border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
                            <AlertTriangleIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            Son {cuantos} y de una vez caben {tope}. Acota el grupo por plan, o
                            escoge uno más pequeño.
                        </p>
                    ) : null}
                </Grupo>

                {data.grupo ? (
                    <Grupo titulo="¿Qué se les dice?">
                        <Campo
                            etiqueta="Asunto"
                            nombre="asunto"
                            error={errors.asunto}
                            requerido
                            ayuda="Es lo único que ven en la bandeja antes de abrirlo."
                        >
                            <Texto
                                nombre="asunto"
                                valor={data.asunto}
                                alCambiar={(v) => setData('asunto', v)}
                                placeholder="Cerramos el lunes por mantención"
                            />
                        </Campo>

                        <Campo
                            etiqueta="Mensaje"
                            nombre="mensaje"
                            error={errors.mensaje}
                            requerido
                            ayuda={`Se puede usar ${Object.keys(variables).map((v) => `{${v}}`).join(', ')}, que se cambian por los datos de cada socio.`}
                        >
                            <Area
                                nombre="mensaje"
                                valor={data.mensaje}
                                alCambiar={(v) => setData('mensaje', v)}
                                filas={8}
                            />
                        </Campo>

                        {data.asunto && data.mensaje ? (
                            <div className="space-y-2">
                                <button
                                    type="button"
                                    onClick={verComoQueda}
                                    className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                                >
                                    {vista ? 'Ver de nuevo' : 'Ver cómo le llega'}
                                </button>

                                {vista?.error ? (
                                    <p className="apoyo text-danger">{vista.error}</p>
                                ) : vista ? (
                                    <div className="overflow-hidden rounded-panel border border-line">
                                        <div className="border-b border-line bg-surface-2 px-3 py-2 text-sm">
                                            <p className="text-fog">
                                                Como le llegaría a{' '}
                                                <span className="text-chalk">{vista.socio}</span>
                                            </p>
                                            <p className="text-fog">
                                                Asunto: <span className="text-chalk">{vista.asunto}</span>
                                            </p>
                                        </div>

                                        {/* Aislado: el mensaje puede traer HTML y
                                            sus estilos se pisarian con los del
                                            panel. */}
                                        <iframe
                                            title="Cómo le llega el correo"
                                            srcDoc={vista.mensaje}
                                            sandbox=""
                                            className="h-64 w-full bg-white"
                                        />
                                    </div>
                                ) : null}
                            </div>
                        ) : null}
                    </Grupo>
                ) : null}

                {data.grupo && data.asunto && data.mensaje ? (
                    <div className="flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            disabled={processing || sePasa || !vista || vista.error}
                            className="inline-flex items-center gap-1.5 rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            <SendIcon className="size-4" aria-hidden="true" />
                            {processing
                                ? 'Enviando…'
                                : cuantos
                                  ? `Enviar a ${cuantos} ${cuantos === 1 ? 'socio' : 'socios'}`
                                  : 'Enviar'}
                        </button>

                        <Link href="/panel/notificaciones" className="apoyo text-fog hover:text-chalk">
                            Cancelar
                        </Link>

                        {/* El boton lleva la cuenta escrita a proposito: es lo
                            ultimo que se lee antes de que salga. */}
                        {!vista ? (
                            <span className="apoyo text-fog">Míralo antes de mandarlo.</span>
                        ) : null}
                    </div>
                ) : null}
            </form>
        </>
    );
}
