import { useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

import { Area, Campo, Texto } from '@/components/Campo';
import { Botones } from '@/components/Cobro';
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

const LARGO_DESCRIPCION = 1200;

const MODALIDADES = [
    { valor: 'presencial', etiqueta: 'Presencial' },
    { valor: 'online', etiqueta: 'Online' },
    { valor: 'ambas', etiqueta: 'Las dos' },
];

const TEXTO_MODALIDAD = { presencial: 'Presencial', online: 'Online', ambas: 'Presencial y online' };

/**
 * Los temas como etiquetas: se escribe uno y Enter (o coma). Una lista es más
 * fácil de leer en el perfil que una frase larga con todo junto.
 */
function Temas({ valor, alCambiar, error }) {
    const [texto, setTexto] = useState('');

    function agregar() {
        const nuevo = texto.trim().replace(/,$/, '').trim();

        if (nuevo && valor.length < 8 && ! valor.some((t) => t.toLowerCase() === nuevo.toLowerCase())) {
            alCambiar([...valor, nuevo.charAt(0).toUpperCase() + nuevo.slice(1)]);
        }

        setTexto('');
    }

    return (
        <div className={`flex flex-wrap items-center gap-1.5 rounded-control border bg-surface px-2 py-1.5 ${error ? 'border-danger' : 'border-line focus-within:border-line-strong'}`}>
            {valor.map((tema) => (
                <span key={tema} className="inline-flex items-center gap-1 rounded-control bg-surface-2 py-0.5 pl-2 pr-1 text-sm text-chalk">
                    {tema}
                    <button
                        type="button"
                        onClick={() => alCambiar(valor.filter((t) => t !== tema))}
                        aria-label={`Quitar ${tema}`}
                        className="rounded px-1 text-fog hover:text-danger"
                    >
                        ×
                    </button>
                </span>
            ))}
            {valor.length < 8 ? (
                <input
                    id="temas"
                    value={texto}
                    onChange={(e) => setTexto(e.target.value)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ',') {
                            e.preventDefault();
                            agregar();
                        } else if (e.key === 'Backspace' && texto === '' && valor.length) {
                            alCambiar(valor.slice(0, -1));
                        }
                    }}
                    onBlur={agregar}
                    maxLength={40}
                    placeholder={valor.length ? '' : 'Nutrición deportiva, lesiones… (Enter para agregar)'}
                    className="min-w-32 flex-1 bg-transparent py-0.5 text-sm text-chalk placeholder:text-fog focus:outline-none"
                />
            ) : null}
        </div>
    );
}

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

/*
 * EL PANEL, IGUAL QUE EN LA WEB. La primera vista previa era un dibujo
 * parecido —otra letra, otra proporción— y en la web se veía distinto. Ahora se
 * arma con las mismas medidas, la misma letra (Oswald y Poppins) y los mismos
 * colores que la página, al ANCHO REAL que tendrá allá, y se achica entera para
 * caber aquí: el nombre corta en la misma palabra y la foto recorta igual.
 *
 * El ancho depende de cuántos se muestran: la web reparte la fila entre ellos
 * (uno a lo ancho, dos a media pantalla… hasta cuatro). Los embajadores van
 * siempre de a cuatro. En el celular cada panel ocupa casi toda la pantalla.
 */
const WEB = {
    // 1520 de ancho máximo menos 80 de margen a cada lado; 16 entre paneles.
    contenido: 1360,
    separacion: 16,
    // El 82% (especialistas) y el 72% (embajadores) de un celular de 375.
    celular: { especialista: 308, embajador: 270 },
};

const OSWALD = "font-['Oswald',sans-serif]";
const POPPINS = "font-['Poppins',sans-serif]";

function anchoEnLaWeb(tipo, pantalla, columnas) {
    if (pantalla === 'celular') {
        return WEB.celular[tipo === 'embajador' ? 'embajador' : 'especialista'];
    }

    const cuantas = tipo === 'embajador' ? 4 : Math.min(Math.max(columnas, 1), 4);

    return (WEB.contenido - WEB.separacion * (cuantas - 1)) / cuantas;
}

function IconoWhatsapp() {
    return (
        <svg viewBox="0 0 24 24" className="size-[1em]" fill="currentColor" aria-hidden="true">
            <path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.98L2 22l5.16-1.5A9.9 9.9 0 1 0 12.04 2Zm0 18.1a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.07.9.92-3-.2-.31a8.2 8.2 0 1 1 6.83 3.74Zm4.5-6.14c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.13-.16.24-.63.8-.78.96-.14.16-.29.18-.53.06a6.7 6.7 0 0 1-3.34-2.92c-.25-.43.25-.4.72-1.34.08-.16.04-.3-.02-.42l-.75-1.8c-.2-.48-.4-.41-.55-.42h-.47a.9.9 0 0 0-.65.3 2.74 2.74 0 0 0-.86 2.04 4.77 4.77 0 0 0 1 2.53 10.9 10.9 0 0 0 4.180 3.69c1.55.67 2.16.72 2.94.61.47-.07 1.46-.6 1.66-1.18.2-.57.2-1.07.14-1.170-.06-.1-.22-.16-.46-.28Z" />
        </svg>
    );
}

function IconoInstagram() {
    return (
        <svg viewBox="0 0 24 24" className="size-[1em]" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="17.5" cy="6.5" r="0.6" fill="currentColor" />
        </svg>
    );
}

/** La inicial en hueco que llena el panel cuando no hay foto, como en la web. */
function Inicial({ nombre, embajador }) {
    return (
        <span
            className={`absolute select-none ${OSWALD} leading-none text-transparent [-webkit-text-stroke:1.5px_rgba(221,42,50,0.35)] ${
                embajador ? '-right-4 -top-6 text-[14rem]' : '-right-6 -top-10 text-[18rem]'
            }`}
            aria-hidden="true"
        >
            {(nombre[0] ?? '?').toUpperCase()}
        </span>
    );
}

/** El panel de un especialista, copiado de landing/especialistas.blade.php. */
function PanelEspecialista({ data, foto, celular, ancho }) {
    const nombre = data.nombre.trim();

    return (
        <article
            style={{ width: ancho }}
            className={`group relative flex flex-col justify-end overflow-hidden bg-[#121214] ${celular ? 'min-h-[22rem]' : 'min-h-[26rem]'}`}
        >
            {foto ? (
                <img src={foto} alt="" className="absolute inset-0 h-full w-full object-cover object-top grayscale transition duration-700 group-hover:grayscale-0" />
            ) : (
                <Inicial nombre={nombre} />
            )}

            <div className="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-[#0a0a0b] via-[#0a0a0b]/70 to-transparent" aria-hidden="true" />

            <div className={`relative ${celular ? 'p-5' : 'p-7'}`}>
                <span className="block h-0.5 w-8 bg-[#dd2a32]" aria-hidden="true" />
                <p className={`mt-4 line-clamp-2 ${POPPINS} text-xs uppercase tracking-[0.2em] ${data.especialidad.trim() ? 'text-[#ef4a51]' : 'text-white/25'}`}>
                    {data.especialidad.trim() || 'Especialidad'}
                </p>
                <p className={`mt-1 ${OSWALD} ${celular ? 'text-3xl' : 'text-4xl'} uppercase leading-none ${nombre ? 'text-[#f2f2f4]' : 'text-white/25'}`}>
                    {nombre || 'Nombre'}
                </p>
                {data.modalidad ? <p className={`mt-2 ${POPPINS} text-xs text-[#f2f2f4]/60`}>{TEXTO_MODALIDAD[data.modalidad]}</p> : null}

                <div className={`mt-5 flex items-center justify-between gap-4 ${POPPINS} text-sm`}>
                    <span className="inline-flex items-center gap-2 border-b border-[#f2f2f4]/30 pb-1 text-[#f2f2f4]">Ver perfil →</span>
                    {data.whatsapp ? (
                        <span className="flex size-10 items-center justify-center rounded-full border border-[#f2f2f4]/25 text-lg text-[#f2f2f4]">
                            <IconoWhatsapp />
                        </span>
                    ) : null}
                </div>
            </div>
        </article>
    );
}

/** El de un embajador, copiado de landing/partes/embajadores.blade.php. */
function PanelEmbajador({ data, foto, celular, ancho }) {
    const nombre = data.nombre.trim();
    const usuario = limpiarInstagram(data.instagram);

    return (
        <figure style={{ width: ancho }} className="group relative aspect-[3/4] overflow-hidden bg-[#121214]">
            {foto ? (
                <img src={foto} alt="" className="absolute inset-0 h-full w-full object-cover grayscale transition duration-700 group-hover:grayscale-0" />
            ) : (
                <Inicial nombre={nombre} embajador />
            )}

            <div className="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-[#0a0a0b] via-[#0a0a0b]/70 to-transparent" aria-hidden="true" />

            <figcaption className={`absolute inset-x-0 bottom-0 ${celular ? 'p-4' : 'p-5'}`}>
                <span className="block h-0.5 w-8 bg-[#dd2a32]" aria-hidden="true" />
                <p className={`mt-3 ${POPPINS} text-xs uppercase tracking-[0.2em] ${data.especialidad.trim() ? 'text-[#ef4a51]' : 'text-white/25'}`}>
                    {data.especialidad.trim() || 'Disciplina'}
                </p>
                <p className={`mt-1 ${OSWALD} ${celular ? 'text-2xl' : 'text-3xl'} uppercase leading-none ${nombre ? 'text-[#f2f2f4]' : 'text-white/25'}`}>
                    {nombre || 'Nombre'}
                </p>
                {usuario ? (
                    <span className={`mt-2 inline-flex items-center gap-1.5 ${POPPINS} text-sm text-[#f2f2f4]/70`}>
                        <IconoInstagram />@{usuario}
                    </span>
                ) : null}
            </figcaption>
        </figure>
    );
}

/**
 * La vista previa: el panel al ancho real, achicado para caber. Es también
 * donde se pone la foto, con un clic o arrastrándola encima.
 */
function VistaPrevia({ tipo, data, foto, columnas, preparando, alElegir, alSoltar }) {
    const [pantalla, setPantalla] = useState('computador');
    const [encima, setEncima] = useState(false);
    const [caja, setCaja] = useState(0);
    const [alto, setAlto] = useState(0);
    const marco = useRef(null);
    const panel = useRef(null);
    const celular = pantalla === 'celular';
    const ancho = anchoEnLaWeb(tipo, pantalla, columnas);
    const escala = caja ? Math.min(1, caja / ancho) : 0;

    // Se mide el hueco disponible y el alto real del panel: el alto cambia con
    // lo que se escribe (la descripción, los enlaces).
    useEffect(() => {
        const mirar = new ResizeObserver(() => {
            setCaja(marco.current?.clientWidth ?? 0);
            setAlto(panel.current?.offsetHeight ?? 0);
        });

        if (marco.current) mirar.observe(marco.current);
        if (panel.current) mirar.observe(panel.current);

        return () => mirar.disconnect();
    }, []);

    const Panel = tipo === 'embajador' ? PanelEmbajador : PanelEspecialista;

    return (
        <div>
            {/* La misma letra que la web. */}
            <link
                rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap"
                precedence="default"
            />

            <div className="mb-2 flex items-center justify-between gap-2">
                <span className="apoyo text-fog">Así se ve en la web</span>
                <div role="group" aria-label="Pantalla" className="inline-flex rounded-control border border-line p-0.5">
                    {['computador', 'celular'].map((p) => (
                        <button
                            key={p}
                            type="button"
                            onClick={() => setPantalla(p)}
                            aria-pressed={pantalla === p}
                            className={`rounded-control px-2 py-0.5 text-xs capitalize transition-colors ${
                                pantalla === p ? 'bg-surface-2 text-chalk' : 'text-fog hover:text-chalk'
                            }`}
                        >
                            {p}
                        </button>
                    ))}
                </div>
            </div>

            <div ref={marco} className={celular ? 'mx-auto w-3/4' : 'w-full'}>
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
                    style={{ height: alto * escala || undefined }}
                    className={`group/foto relative block w-full overflow-hidden text-left outline-offset-2 ${encima ? 'outline outline-2 outline-[#dd2a32]' : ''}`}
                >
                    <div ref={panel} style={{ width: ancho, transform: `scale(${escala})`, transformOrigin: 'top left' }} className="absolute left-0 top-0">
                        <Panel data={data} foto={foto} celular={celular} ancho={ancho} />
                    </div>

                    <span className="absolute inset-x-0 top-0 bg-black/60 py-1.5 text-center text-xs text-white opacity-0 transition-opacity group-hover/foto:opacity-100 group-focus-visible/foto:opacity-100">
                        {preparando ? 'Preparando la foto…' : foto ? 'Cambiar foto' : 'Elegir o arrastrar una foto'}
                    </span>

                    {! data.activo ? (
                        <span className="absolute left-2 top-2 bg-black/70 px-2 py-0.5 text-[11px] uppercase tracking-wider text-white/80">Oculto</span>
                    ) : null}
                </button>
            </div>
        </div>
    );
}

export default function FormularioPersona({ abierto, alCerrar, tipo, persona, existentes = [], otrosEnLaWeb = 0 }) {
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
                className="sm:max-w-4xl"
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

                <form onSubmit={enviar} className="grid gap-5 md:grid-cols-[1fr_20rem]">
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
                            <>
                                <Campo etiqueta="Atiende" nombre="modalidad" error={errors.modalidad}>
                                    <Botones
                                        opciones={MODALIDADES}
                                        valor={data.modalidad}
                                        alElegir={(v) => setData('modalidad', data.modalidad === v ? '' : v)}
                                        nombre="Atiende"
                                        columnas="grid-cols-3"
                                        compacto
                                    />
                                </Campo>

                                <Campo etiqueta="En qué ayuda" nombre="temas" error={errors.temas ?? errors['temas.0']}>
                                    <Temas valor={data.temas ?? []} alCambiar={(v) => setData('temas', v)} error={errors.temas} />
                                </Campo>

                                <Campo etiqueta="Presentación" nombre="descripcion" error={errors.descripcion} ayuda="Sale en su perfil, no encima de la foto.">
                                    <Area
                                        nombre="descripcion"
                                        valor={data.descripcion}
                                        alCambiar={(v) => setData('descripcion', v)}
                                        error={errors.descripcion}
                                        filas={5}
                                        maxLength={LARGO_DESCRIPCION}
                                        placeholder="Quién es, su experiencia y cómo trabaja. Una línea en blanco separa párrafos."
                                    />
                                    <span className="apoyo -mt-0.5 self-end tabular-nums text-fog">
                                        {data.descripcion.length}/{LARGO_DESCRIPCION}
                                    </span>
                                </Campo>
                            </>
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
                    <div className="order-first md:order-none">
                        <VistaPrevia
                            tipo={tipo}
                            columnas={otrosEnLaWeb + (data.activo ? 1 : 0)}
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

                        {persona?.perfil_url ? (
                            <a href={persona.perfil_url} target="_blank" rel="noopener" className="apoyo mt-2 block text-center text-fog underline-offset-2 hover:text-chalk hover:underline">
                                Abrir su perfil en la web ↗
                            </a>
                        ) : null}

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
