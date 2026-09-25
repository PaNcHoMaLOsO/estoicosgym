import { useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

import { Area, Campo, Texto } from '@/components/Campo';
import { valoresDeEspecialista } from '@/components/FormularioCatalogo';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import achicarFoto from '@/lib/achicarFoto';

/**
 * El formulario de un especialista o un embajador, con la web al lado.
 *
 * Era el mismo diálogo de los planes y los convenios: una columna de campos y
 * la foto en un cuadrito de 64 píxeles. No se veía cómo iba a quedar hasta
 * abrir la web, y la foto del celular no se podía subir porque pesaba más de
 * 2 MB. Ahora:
 *
 * - AL LADO SE VE EL PANEL COMO SALE EN LA WEB, y cambia mientras se escribe.
 *   El panel es también donde se pone la foto: se hace clic o se arrastra.
 * - LA FOTO SE ACHICA SOLA antes de subirse (`achicarFoto`).
 * - EL WHATSAPP SE ESCRIBE COMO SE LEE: con el +56 puesto y los espacios solos.
 *   Si se pega el número entero, con +56, también sirve.
 * - EL INSTAGRAM acepta el @usuario o el enlace del perfil, y deja el usuario.
 * - AL CREAR, «Guardar y agregar otro» deja el formulario limpio para el
 *   siguiente, sin cerrar y volver a abrir.
 */

const SUGERENCIAS = {
    especialista: ['Nutricionista', 'Personal trainer', 'Kinesiólogo', 'Kinesióloga', 'Preparador físico', 'Psicólogo deportivo', 'Masoterapeuta'],
    embajador: ['Powerlifting', 'CrossFit', 'Culturismo', 'Halterofilia', 'Calistenia', 'Fitness', 'Atletismo'],
};

const LARGO_DESCRIPCION = 300;

/** «+56 9 1234 5678», «912345678» o «9 1234 5678» → «9 1234 5678». */
export function formatearCelular(valor) {
    let digitos = String(valor ?? '').replace(/\D/g, '');

    if (digitos.startsWith('56') && digitos.length > 9) {
        digitos = digitos.slice(2);
    }

    digitos = digitos.slice(0, 9);

    return [digitos.slice(0, 1), digitos.slice(1, 5), digitos.slice(5, 9)].filter(Boolean).join(' ');
}

/** Lo que le falta al celular, o nada si está bien o vacío. */
function reparoDelCelular(valor) {
    const digitos = String(valor ?? '').replace(/\D/g, '');

    if (digitos === '') {
        return null;
    }

    if (digitos[0] !== '9') {
        return 'Un celular empieza con 9.';
    }

    const faltan = 9 - digitos.length;

    return faltan > 0 ? `Faltan ${faltan} ${faltan === 1 ? 'número' : 'números'}.` : null;
}

/** «https://instagram.com/usuario?igsh=…» o «@usuario» → «usuario». */
export function limpiarInstagram(valor) {
    const texto = String(valor ?? '').trim();
    const enlace = texto.match(/instagram\.com\/([^/?#\s]+)/i);

    return (enlace ? enlace[1] : texto).replace(/^@+/, '').replace(/\s/g, '');
}

/** Un campo con algo fijo delante: el +56, la @. */
function ConPrefijo({ prefijo, error, ...resto }) {
    return (
        <div className="flex">
            <span className="flex items-center rounded-l-control border border-r-0 border-line bg-surface-2 px-2.5 text-sm text-fog">
                {prefijo}
            </span>
            <input
                {...resto}
                aria-invalid={error ? 'true' : undefined}
                className={`w-full min-w-0 rounded-r-control border bg-surface px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:outline-none ${
                    error ? 'border-danger' : 'border-line focus:border-line-strong'
                }`}
            />
        </div>
    );
}

/**
 * El panel tal como sale en la web: la foto a todo el alto, el fundido negro
 * abajo y encima la especialidad, el nombre y los enlaces.
 */
function VistaPrevia({ tipo, data, foto, preparando, alElegir, alSoltar }) {
    const [encima, setEncima] = useState(false);
    const nombre = data.nombre.trim();
    const especialidad = data.especialidad.trim();
    const usuario = limpiarInstagram(data.instagram);

    return (
        <button
            type="button"
            onClick={alElegir}
            onDragOver={(e) => {
                e.preventDefault();
                setEncima(true);
            }}
            onDragLeave={() => setEncima(false)}
            onDrop={(e) => {
                e.preventDefault();
                setEncima(false);
                alSoltar(e.dataTransfer.files?.[0]);
            }}
            aria-label={foto ? 'Cambiar la foto' : 'Elegir una foto'}
            className={`group relative flex aspect-[3/4] w-full flex-col justify-end overflow-hidden bg-[#161616] text-left outline-offset-2 ${
                encima ? 'outline outline-2 outline-[#dd2a32]' : ''
            }`}
        >
            {foto ? (
                <img
                    src={foto}
                    alt=""
                    className="absolute inset-0 size-full object-cover object-top grayscale transition duration-500 group-hover:grayscale-0"
                />
            ) : (
                <span
                    className="absolute -right-4 -top-8 select-none text-[13rem] font-black leading-none text-transparent [-webkit-text-stroke:1.5px_rgba(221,42,50,0.35)]"
                    aria-hidden="true"
                >
                    {(nombre[0] ?? '?').toUpperCase()}
                </span>
            )}

            <span className="absolute inset-x-0 bottom-0 h-3/4 bg-gradient-to-t from-[#0b0b0b] via-[#0b0b0b]/80 to-transparent" aria-hidden="true" />

            {/* Lo que se hace con la foto, a la vista solo al pasar por encima. */}
            <span className="absolute inset-x-0 top-0 bg-black/60 py-1.5 text-center text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">
                {preparando ? 'Preparando la foto…' : foto ? 'Cambiar foto' : 'Elegir o arrastrar una foto'}
            </span>

            {! data.activo ? (
                <span className="absolute left-2 top-2 bg-black/70 px-2 py-0.5 text-[11px] uppercase tracking-wider text-white/80">
                    Oculto
                </span>
            ) : null}

            <span className="relative block p-4">
                <span className="block h-0.5 w-8 bg-[#dd2a32]" aria-hidden="true" />
                <span className={`mt-3 block text-[11px] uppercase tracking-[0.2em] ${especialidad ? 'text-[#ef5b62]' : 'text-white/25'}`}>
                    {especialidad || (tipo === 'embajador' ? 'Disciplina' : 'Especialidad')}
                </span>
                <span className={`mt-1 block text-2xl font-black uppercase leading-none ${nombre ? 'text-[#f4f1ea]' : 'text-white/25'}`}>
                    {nombre || 'Nombre'}
                </span>

                {tipo !== 'embajador' && data.descripcion.trim() ? (
                    <span className="mt-2 line-clamp-4 block text-xs leading-relaxed text-[#f4f1ea]/70">{data.descripcion}</span>
                ) : null}

                {tipo === 'embajador' ? (
                    usuario ? <span className="mt-2 block text-xs text-[#f4f1ea]/70">@{usuario}</span> : null
                ) : data.whatsapp || usuario ? (
                    <span className="mt-3 flex gap-4 text-xs text-[#f4f1ea]">
                        {data.whatsapp ? <span className="border-b border-white/30 pb-0.5">WhatsApp</span> : null}
                        {usuario ? <span className="border-b border-white/30 pb-0.5">Instagram</span> : null}
                    </span>
                ) : null}
            </span>
        </button>
    );
}

export default function FormularioPersona({ abierto, alCerrar, tipo, persona, existentes = [] }) {
    const valores = valoresDeEspecialista(persona, tipo);
    const { data, setData, post, put, processing, errors, clearErrors, setError, transform } = useForm(valores);
    const [vista, setVista] = useState(null);
    const [preparando, setPreparando] = useState(false);
    const elegir = useRef(null);
    const primerCampo = useRef(null);
    const otroDespues = useRef(false);
    const editando = Boolean(persona?.uuid);
    const esEmbajador = tipo === 'embajador';

    // Al abrir se rellena con lo que toque: el diálogo no se desmonta al
    // cerrarse y conservaría lo de la persona anterior.
    useEffect(() => {
        if (! abierto) {
            return;
        }

        clearErrors();
        Object.entries(valores).forEach(([clave, valor]) => setData(clave, valor));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [abierto, persona?.uuid]);

    // La foto nueva se ve antes de subirla; cada vista reserva memoria y se suelta.
    useEffect(() => {
        if (! (data.foto instanceof File)) {
            setVista(null);

            return undefined;
        }

        const url = URL.createObjectURL(data.foto);
        setVista(url);

        return () => URL.revokeObjectURL(url);
    }, [data.foto]);

    async function ponerFoto(archivo) {
        if (! archivo) {
            return;
        }

        if (! /^image\/(jpeg|png|webp)$/.test(archivo.type)) {
            setError('foto', 'Tiene que ser una foto JPG, PNG o WEBP.');

            return;
        }

        clearErrors('foto');
        setPreparando(true);
        setData('foto', await achicarFoto(archivo));
        setData('quitar_foto', false);
        setPreparando(false);
    }

    function enviar(e, otro = false) {
        e?.preventDefault();
        otroDespues.current = otro;

        const opciones = {
            preserveScroll: true,
            onSuccess: () => {
                if (! otroDespues.current) {
                    alCerrar();

                    return;
                }

                // Limpio para el siguiente, sin cerrar.
                Object.entries(valoresDeEspecialista(null, tipo)).forEach(([clave, valor]) => setData(clave, valor));
                primerCampo.current?.focus();
            },
        };

        // Con foto viaja como multipart, que PHP no lee en un PUT: va como POST
        // diciendo que es un PUT.
        if (editando && data.foto instanceof File) {
            transform((datos) => ({ ...datos, _method: 'put' }));
            post(`/panel/especialistas/${persona.uuid}`, opciones);

            return;
        }

        transform((datos) => datos);

        if (editando) {
            put(`/panel/especialistas/${persona.uuid}`, opciones);
        } else {
            post('/panel/especialistas', opciones);
        }
    }

    const foto = vista ?? (data.quitar_foto ? null : data.foto_url);
    const sugerencias = [...new Set([...existentes, ...SUGERENCIAS[esEmbajador ? 'embajador' : 'especialista']])];
    const reparo = reparoDelCelular(data.whatsapp);

    return (
        <Dialog open={abierto} onOpenChange={(v) => (! v && ! processing ? alCerrar() : null)}>
            <DialogContent
                className="sm:max-w-3xl"
                onOpenAutoFocus={(e) => {
                    e.preventDefault();
                    primerCampo.current?.focus();
                }}
            >
                <DialogHeader>
                    <DialogTitle>
                        {editando ? `Editar a ${persona.nombre}` : esEmbajador ? 'Agregar embajador' : 'Agregar especialista'}
                    </DialogTitle>
                    <DialogDescription>Su foto y su contacto quedan a la vista de cualquiera: súbelos con su permiso.</DialogDescription>
                </DialogHeader>

                <form onSubmit={enviar} className="grid gap-5 md:grid-cols-[1fr_16rem]">
                    <div className="space-y-3">
                        <Campo etiqueta="Nombre" nombre="nombre" error={errors.nombre} requerido>
                            <Texto
                                ref={primerCampo}
                                nombre="nombre"
                                valor={data.nombre}
                                alCambiar={(v) => setData('nombre', v)}
                                error={errors.nombre}
                                placeholder={esEmbajador ? 'Diego Riquelme' : 'Camila Rojas'}
                                maxLength={100}
                                autoComplete="off"
                            />
                        </Campo>

                        <Campo etiqueta={esEmbajador ? 'Disciplina' : 'Especialidad'} nombre="especialidad" error={errors.especialidad} requerido>
                            <Texto
                                nombre="especialidad"
                                valor={data.especialidad}
                                alCambiar={(v) => setData('especialidad', v)}
                                error={errors.especialidad}
                                list="especialidades-sugeridas"
                                placeholder={esEmbajador ? 'Powerlifting' : 'Nutricionista'}
                                maxLength={100}
                                autoComplete="off"
                            />
                            <datalist id="especialidades-sugeridas">
                                {sugerencias.map((s) => (
                                    <option key={s} value={s} />
                                ))}
                            </datalist>
                        </Campo>

                        {! esEmbajador ? (
                            <Campo etiqueta="Descripción" nombre="descripcion" error={errors.descripcion}>
                                <Area
                                    nombre="descripcion"
                                    valor={data.descripcion}
                                    alCambiar={(v) => setData('descripcion', v)}
                                    error={errors.descripcion}
                                    filas={3}
                                    maxLength={LARGO_DESCRIPCION}
                                    placeholder="En qué te puede ayudar, en una o dos líneas."
                                />
                                <span className="apoyo -mt-0.5 self-end tabular-nums text-fog">
                                    {data.descripcion.length}/{LARGO_DESCRIPCION}
                                </span>
                            </Campo>
                        ) : null}

                        <div className={esEmbajador ? '' : 'grid gap-3 sm:grid-cols-2'}>
                            {! esEmbajador ? (
                                <Campo etiqueta="WhatsApp" nombre="whatsapp" error={errors.whatsapp} ayuda={reparo}>
                                    <ConPrefijo
                                        prefijo="+56"
                                        id="whatsapp"
                                        name="whatsapp"
                                        inputMode="numeric"
                                        autoComplete="off"
                                        placeholder="9 1234 5678"
                                        value={data.whatsapp}
                                        onChange={(e) => setData('whatsapp', formatearCelular(e.target.value))}
                                        error={errors.whatsapp}
                                    />
                                </Campo>
                            ) : null}

                            <Campo etiqueta="Instagram" nombre="instagram" error={errors.instagram}>
                                <ConPrefijo
                                    prefijo="@"
                                    id="instagram"
                                    name="instagram"
                                    autoComplete="off"
                                    placeholder="usuario o enlace del perfil"
                                    value={limpiarInstagram(data.instagram)}
                                    onChange={(e) => setData('instagram', limpiarInstagram(e.target.value))}
                                    error={errors.instagram}
                                />
                            </Campo>
                        </div>

                        <label className="flex items-center gap-2 pt-1 text-sm text-chalk">
                            <input
                                type="checkbox"
                                checked={Boolean(data.activo)}
                                onChange={(e) => setData('activo', e.target.checked)}
                                className="size-4 accent-[var(--color-volt)]"
                            />
                            {esEmbajador ? 'Se muestra en la portada' : 'Se muestra en la web'}
                        </label>
                    </div>

                    {/* En el celular, la foto primero y más chica. */}
                    <div className="order-first mx-auto w-44 md:order-none md:w-full">
                        <VistaPrevia
                            tipo={tipo}
                            data={data}
                            foto={foto}
                            preparando={preparando}
                            alElegir={() => elegir.current?.click()}
                            alSoltar={ponerFoto}
                        />

                        <input
                            ref={elegir}
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            className="hidden"
                            onChange={(e) => {
                                ponerFoto(e.target.files?.[0]);
                                e.target.value = '';
                            }}
                        />

                        <div className="apoyo mt-2 flex justify-between gap-2 text-fog">
                            <button type="button" onClick={() => elegir.current?.click()} className="hover:text-chalk">
                                {foto ? 'Cambiar foto' : 'Elegir foto'}
                            </button>

                            {foto ? (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setData('foto', null);
                                        setData('quitar_foto', Boolean(data.foto_url));
                                    }}
                                    className="hover:text-danger"
                                >
                                    Quitar
                                </button>
                            ) : null}
                        </div>

                        {errors.foto ? (
                            <p className="apoyo mt-1 text-danger" role="alert">
                                {errors.foto}
                            </p>
                        ) : null}
                    </div>

                    <div className="sticky -bottom-4 -mx-4 -mb-4 flex flex-wrap items-center justify-end gap-3 border-t border-line bg-raise px-4 py-3 md:col-span-2">
                        <button
                            type="button"
                            onClick={alCerrar}
                            disabled={processing}
                            className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                        >
                            Cancelar
                        </button>

                        {! editando ? (
                            <button
                                type="button"
                                onClick={() => enviar(null, true)}
                                disabled={processing || preparando}
                                className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                            >
                                Guardar y agregar otro
                            </button>
                        ) : null}

                        <button
                            type="submit"
                            disabled={processing || preparando}
                            className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            {processing ? 'Guardando…' : 'Guardar'}
                        </button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
