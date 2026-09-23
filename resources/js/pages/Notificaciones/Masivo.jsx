import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ArrowLeftIcon, SendIcon, UsersIcon } from 'lucide-react';

import Nota from '@/components/Nota';
import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

/**
 * Un mismo aviso a un grupo de socios.
 *
 * Lo que mas importa de esta pantalla es que se vea A CUANTA GENTE se le va a
 * escribir antes de pulsar el boton, y quienes son. Un correo a doscientas
 * personas no se puede recoger, y «182 socios» es un numero: los numeros no
 * dejan ver que ahi dentro esta quien se dio de baja ayer.
 */
const hoy = new Date().toISOString().slice(0, 10);

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
        // Vacío = sale ya. Con fecha, lo manda el comando de esa mañana.
        cuando: '',
    });

    const programado = data.cuando !== '' && data.cuando > hoy;

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

    /*
     * El tope SOLO aplica a lo que sale ya.
     *
     * Existe porque los correos salen uno a uno dentro de esta misma petición y
     * pasado ese número el servidor corta a mitad de la lista. Programado no
     * pasa por aquí: se escriben las filas y las manda el comando después, sin
     * navegador de por medio. Bloquearlo también sería negarse por un motivo
     * que en ese caso no existe.
     */
    const sePasa = ! programado && cuantos !== null && cuantos !== undefined && cuantos > tope;

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
                        <Nota tono="peligro">
                            Son {cuantos} y de una vez caben {tope}. Acota el grupo por plan,
                            escoge uno más pequeño, o prográmalo para otro día: así los manda
                            el sistema por la mañana y el tope no aplica.
                        </Nota>
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
                    <Grupo titulo="¿Cuándo sale?">
                        {/* SIN HORA, a propósito. El sistema manda los correos
                            programados una vez al día, a las 08:00. Pedir una
                            hora sería prometer algo que no se cumple: es lo que
                            hacía el panel viejo, con un campo de hora
                            obligatorio que ni siquiera se guardaba. */}
                        <div className="sm:col-span-2">
                            <Campo
                                etiqueta="Día"
                                nombre="cuando"
                                error={errors.cuando}
                            >
                                <input
                                    id="cuando"
                                    name="cuando"
                                    type="date"
                                    min={hoy}
                                    value={data.cuando}
                                    onChange={(e) => setData('cuando', e.target.value)}
                                    className={`w-full rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:outline-none ${
                                        errors.cuando
                                            ? 'border-danger'
                                            : 'border-line focus:border-line-strong'
                                    }`}
                                />
                                <p className="apoyo mt-1 text-fog">
                                    {programado
                                        ? 'Salen esa mañana, a las 08:00. Hasta entonces se pueden cancelar desde el listado.'
                                        : 'Déjalo vacío y sale ahora mismo.'}
                                </p>
                            </Campo>
                        </div>
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
                                ? programado
                                    ? 'Programando…'
                                    : 'Enviando…'
                                : programado
                                  ? `Programar${cuantos ? ` ${cuantos}` : ''} para el ${data.cuando.split('-').reverse().join('/')}`
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
