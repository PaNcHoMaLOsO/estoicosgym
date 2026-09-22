import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
import {
    AlertTriangleIcon,
    ArrowLeftIcon,
    BanknoteIcon,
    CameraIcon,
    FileTextIcon,
    PencilIcon,
    PlayIcon,
    PlusIcon,
    RefreshCwIcon,
    SendIcon,
    ShieldCheckIcon,
    ShoppingBagIcon,
    Trash2Icon,
    UserMinusIcon,
    UserPlusIcon,
} from 'lucide-react';

import Dialogo from '@/components/Dialogo';
import CamaraFoto from '@/components/CamaraFoto';
import { ApuntarFiado } from '@/components/Libreta';
import ConfirmarDinero from '@/components/ConfirmarDinero';
import ModalDePagina from '@/components/ModalDePagina';
import Estado from '@/components/Estado';
import Retrato from '@/components/Retrato';
import { Reservado } from '@/Privado';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';
import { celularLegible } from '@/lib/contacto';
import { puede } from '@/lib/permisos';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Dato({ etiqueta, children }) {
    return (
        <div>
            <dt className="rotulo">{etiqueta}</dt>
            <dd className="mt-0.5 text-sm text-chalk">{children || <span className="text-fog">-</span>}</dd>
        </div>
    );
}

function Bloque({ titulo, children, accion }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <div className="mb-3 flex items-center justify-between gap-3">
                <h2 className="rotulo">{titulo}</h2>
                {accion}
            </div>
            {children}
        </section>
    );
}

/** Cuánto le queda a la membresía vigente. Solo se pinta si exige actuar. */
function Vigencia({ dias }) {
    if (dias === null || dias === undefined) {
        return null;
    }

    if (dias < 0) {
        return <span className="font-medium text-danger">Venció hace {Math.abs(dias)} días</span>;
    }

    if (dias <= 7) {
        return <span className="font-medium text-warn">Vence en {dias} días</span>;
    }

    return <span className="text-fog">Quedan {dias} días</span>;
}

/**
 * La foto del socio, con lo justo para ponerla, cambiarla y quitarla.
 *
 * SE SUBE SOLA al elegir el archivo, sin un «guardar» de por medio: cambiar
 * una foto no tiene nada que confirmar y se hace con la persona delante
 * esperando. Quitarla SI pregunta, porque el archivo se borra del disco y eso
 * no se deshace.
 */
function FotoDelSocio({ cliente }) {
    const selector = useRef(null);
    const [subiendo, setSubiendo] = useState(false);
    const [error, setError] = useState(null);
    const [confirmandoQuitar, setConfirmandoQuitar] = useState(false);
    const [conCamara, setConCamara] = useState(false);

    // La foto sacada con la cámara sube igual que la elegida del disco: el
    // servidor no distingue de dónde salió, y así hay una sola subida.
    function subir(archivo) {
        setError(null);
        setSubiendo(true);

        router.post(
            `/panel/clientes/${cliente.uuid}/foto`,
            { foto_perfil: archivo },
            {
                preserveScroll: true,
                onError: (errores) => setError(errores.foto_perfil ?? 'No se pudo subir la foto.'),
                onFinish: () => setSubiendo(false),
            },
        );
    }

    function elegida(e) {
        const archivo = e.target.files?.[0];

        if (! archivo) {
            return;
        }

        subir(archivo);

        // Se vacía a mano: si no, volver a elegir EL MISMO archivo no dispara
        // el evento y parecería que el botón no hace nada.
        if (selector.current) {
            selector.current.value = '';
        }
    }

    return (
        <div className="flex shrink-0 flex-col items-center gap-1">
            <Retrato nombre={cliente.nombre} foto={cliente.foto} tamano="xl" ampliable />

            <div className="flex flex-col items-center gap-0.5">
                <input
                    ref={selector}
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    onChange={elegida}
                    className="hidden"
                    tabIndex={-1}
                />

                <button
                    type="button"
                    disabled={subiendo}
                    onClick={() => setConCamara(true)}
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk disabled:opacity-50"
                >
                    <CameraIcon className="size-3.5" aria-hidden="true" />
                    {subiendo ? 'Subiendo…' : cliente.foto ? 'Sacar otra foto' : 'Sacar foto'}
                </button>

                <button
                    type="button"
                    disabled={subiendo}
                    onClick={() => selector.current?.click()}
                    className="apoyo text-fog transition-colors hover:text-chalk disabled:opacity-50"
                >
                    Elegir archivo
                </button>

                <CamaraFoto
                    abierta={conCamara}
                    alCerrar={() => setConCamara(false)}
                    alSacar={subir}
                    nombre={cliente.nombre}
                />

                {cliente.foto ? (
                    <button
                        type="button"
                        onClick={() => setConfirmandoQuitar(true)}
                        className="apoyo text-fog transition-colors hover:text-danger"
                    >
                        Quitar
                    </button>
                ) : null}

                {error ? <p className="apoyo max-w-40 text-danger">{error}</p> : null}
            </div>

            <Dialogo
                abierto={confirmandoQuitar}
                alCerrar={() => setConfirmandoQuitar(false)}
                titulo="¿Quitar la foto?"
                descripcion={`Se borra la foto de ${cliente.nombre}. El archivo se elimina y no se puede recuperar.`}
                accion={`/panel/clientes/${cliente.uuid}/foto`}
                datos={{ quitar: true }}
                via="inertia"
                metodo="post"
                etiquetaConfirmar="Quitar foto"
                peligrosa
            />
        </div>
    );
}

/**
 * El contrato y los dos permisos.
 *
 * EL CONTRATO SE FIRMA EN PAPEL: aquí solo queda la constancia de que se firmó,
 * qué día y qué versión. La versión importa el día que cambie el texto: sin
 * ella, la respuesta a «¿qué firmó este socio?» es «alguna de las dos».
 *
 * Los dos permisos van SEPARADOS porque son cosas distintas. «Tu foto la ve
 * quien atiende el mesón» y «tu foto sale en nuestro Instagram» no se autorizan
 * con la misma firma, y juntarlas dejaría la segunda sin valer.
 */
function ContratoDelSocio({ cliente }) {
    const contrato = cliente.contrato;
    const [editando, setEditando] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        contrato_version: contrato.version ?? contrato.version_vigente,
        contrato_firmado_en: contrato.firmado_iso ?? '',
        consentimiento_imagen: contrato.imagen,
        consentimiento_difusion: contrato.difusion,
    });

    const firmado = Boolean(contrato.firmado_en);
    const versionVieja = firmado && contrato.version !== contrato.version_vigente;
    // Hay cara guardada pero no consta que dijera que sí.
    const fotoSinPermiso = Boolean(cliente.foto) && ! contrato.imagen;
    // Desmarcar el permiso teniendo foto la borra: hay que decirlo antes.
    const vaABorrarLaFoto = Boolean(cliente.foto) && contrato.imagen && ! data.consentimiento_imagen;

    function guardar(e) {
        e.preventDefault();

        post(`/panel/clientes/${cliente.uuid}/contrato`, {
            preserveScroll: true,
            onSuccess: () => setEditando(false),
        });
    }

    if (! editando) {
        return (
            <Bloque
                titulo="Contrato y permisos"
                accion={
                    cliente.datos_borrados ? null : (
                        <button
                            type="button"
                            onClick={() => setEditando(true)}
                            className="apoyo text-fog transition-colors hover:text-chalk"
                        >
                            {firmado ? 'Cambiar' : 'Anotar'}
                        </button>
                    )
                }
            >
                <dl className="space-y-3">
                    <Dato etiqueta="Contrato">
                        {firmado ? (
                            <>
                                Firmado el {contrato.firmado_en} · versión {contrato.version}
                                {versionVieja ? (
                                    <span className="mt-0.5 flex items-start gap-1 text-warn">
                                        <AlertTriangleIcon
                                            className="mt-0.5 size-3 shrink-0"
                                            aria-hidden="true"
                                        />
                                        Hoy se firma la {contrato.version_vigente}.
                                    </span>
                                ) : null}
                            </>
                        ) : (
                            <span className="text-warn">No consta que haya firmado</span>
                        )}
                    </Dato>

                    <Dato etiqueta="Foto en su ficha">
                        {contrato.imagen ? (
                            'Autorizada'
                        ) : fotoSinPermiso ? (
                            <span className="flex items-start gap-1 text-warn">
                                <AlertTriangleIcon
                                    className="mt-0.5 size-3 shrink-0"
                                    aria-hidden="true"
                                />
                                Tiene foto, pero no consta que la autorizara.
                            </span>
                        ) : (
                            <span className="text-fog">No autorizada</span>
                        )}
                    </Dato>

                    <Dato etiqueta="Redes sociales">
                        {contrato.difusion ? (
                            'Autorizada'
                        ) : (
                            <span className="text-fog">No autorizada</span>
                        )}
                    </Dato>
                </dl>

                {cliente.datos_borrados ? null : (
                    <>
                        {/* El contrato con sus datos: para leerlo con él delante,
                            o imprimirlo y firmarlo en el mesón. Se abre aparte
                            porque es una hoja para imprimir, no una pantalla. */}
                        <a
                            href={`/panel/clientes/${cliente.uuid}/contrato/ver`}
                            target="_blank"
                            rel="noopener"
                            className="mt-3 inline-flex items-center gap-1.5 rounded-control border border-line px-2.5 py-1 text-sm text-chalk transition-colors hover:bg-surface-2"
                        >
                            <FileTextIcon className="size-3.5" aria-hidden="true" />
                            Ver e imprimir el contrato
                        </a>

                        <FirmaPorCorreo cliente={cliente} />
                    </>
                )}
            </Bloque>
        );
    }

    return (
        <Bloque titulo="Contrato y permisos">
            <form onSubmit={guardar} className="space-y-3">
                <div>
                    <label htmlFor="contrato_firmado_en" className="rotulo">
                        Firmado el
                    </label>
                    <input
                        id="contrato_firmado_en"
                        type="date"
                        value={data.contrato_firmado_en}
                        onChange={(e) => setData('contrato_firmado_en', e.target.value)}
                        className={`mt-0.5 w-full rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:outline-none ${
                            errors.contrato_firmado_en
                                ? 'border-danger'
                                : 'border-line focus:border-line-strong'
                        }`}
                    />
                    {errors.contrato_firmado_en ? (
                        <p className="apoyo mt-0.5 text-danger">{errors.contrato_firmado_en}</p>
                    ) : null}
                </div>

                <div>
                    <label htmlFor="contrato_version" className="rotulo">
                        Versión
                    </label>
                    <input
                        id="contrato_version"
                        type="text"
                        value={data.contrato_version}
                        onChange={(e) => setData('contrato_version', e.target.value)}
                        className="mt-0.5 w-24 rounded-control border border-line bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                    />
                    <p className="apoyo mt-0.5 text-fog">
                        Hoy se firma la {contrato.version_vigente}.
                    </p>
                </div>

                <label className="flex items-start gap-2 text-sm text-chalk">
                    <input
                        type="checkbox"
                        checked={data.consentimiento_imagen}
                        onChange={(e) => setData('consentimiento_imagen', e.target.checked)}
                        className="mt-0.5"
                    />
                    <span>
                        Autoriza su foto en la ficha
                        <span className="apoyo block text-fog">
                            La ve solo el personal, dentro del panel.
                        </span>
                    </span>
                </label>

                <label className="flex items-start gap-2 text-sm text-chalk">
                    <input
                        type="checkbox"
                        checked={data.consentimiento_difusion}
                        onChange={(e) => setData('consentimiento_difusion', e.target.checked)}
                        className="mt-0.5"
                    />
                    <span>
                        Autoriza su imagen en redes sociales
                        <span className="apoyo block text-fog">
                            Este sistema no la usa. Queda anotado para quien publique.
                        </span>
                    </span>
                </label>

                {/* Se avisa ANTES de guardar, no después: quien desmarca la
                    casilla no tiene por qué saber que además borra un archivo. */}
                {vaABorrarLaFoto ? (
                    <p className="apoyo flex items-start gap-1 text-warn">
                        <AlertTriangleIcon className="mt-0.5 size-3 shrink-0" aria-hidden="true" />
                        Al guardar se borrará su foto, porque retiró el permiso.
                    </p>
                ) : null}

                <div className="flex items-center gap-3 pt-1">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                    >
                        {processing ? 'Guardando…' : 'Guardar'}
                    </button>
                    <button
                        type="button"
                        onClick={() => {
                            reset();
                            setEditando(false);
                        }}
                        className="apoyo text-fog transition-colors hover:text-chalk"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </Bloque>
    );
}

/**
 * El contrato por correo: le llega un enlace, lo lee en su celular y lo firma
 * con el dedo. Aquí se ve cómo va el último que se mandó y se manda otro.
 *
 * Mandar uno nuevo deja sin efecto el enlace anterior: un solo contrato
 * pendiente por socio, para que no firme dos versiones distintas.
 */
function FirmaPorCorreo({ cliente }) {
    const firma = cliente.firma_digital;
    const ultimo = firma.ultimo;
    const [confirmando, setConfirmando] = useState(false);

    const estados = {
        pendiente: {
            texto: `Enviado a ${ultimo?.enviado_a} el ${ultimo?.enviado_el}. Falta que lo firme: el enlace sirve hasta el ${ultimo?.vence}.`,
            clase: 'text-warn',
        },
        vencido: { texto: `El enlace venció el ${ultimo?.vence} sin que lo firmara.`, clase: 'text-warn' },
        firmado: { texto: `Lo firmó ${ultimo?.firmante} el ${ultimo?.firmado_el}.`, clase: 'text-chalk' },
        anulado: { texto: 'El último enlace se anuló.', clase: 'text-fog' },
        fallido: { texto: `El correo no salió: ${ultimo?.error}`, clase: 'text-danger' },
        borrado: { texto: 'Del contrato queda solo su huella.', clase: 'text-fog' },
    };
    const estado = ultimo ? estados[ultimo.estado] : null;
    const pendiente = ultimo?.estado === 'pendiente';

    return (
        <div className="mt-4 border-t border-line pt-3">
            <p className="rotulo">Firma por correo</p>

            <p className={`mt-0.5 text-sm ${estado ? estado.clase : 'text-fog'}`}>
                {estado ? estado.texto : 'Todavía no se le ha mandado.'}
            </p>

            <div className="mt-2 flex flex-wrap items-center gap-2">
                {ultimo?.estado === 'firmado' ? (
                    <a
                        href={`/panel/contratos/${ultimo.uuid}`}
                        target="_blank"
                        rel="noopener"
                        className="inline-flex items-center gap-1.5 rounded-control border border-line px-2.5 py-1 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        <FileTextIcon className="size-3.5" aria-hidden="true" />
                        Ver el contrato firmado
                    </a>
                ) : null}

                {firma.no_se_puede ? null : (
                    <button
                        type="button"
                        onClick={() => setConfirmando(true)}
                        className="inline-flex items-center gap-1.5 rounded-control border border-line px-2.5 py-1 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        <SendIcon className="size-3.5" aria-hidden="true" />
                        {ultimo ? 'Mandar otro' : 'Mandar para firmar'}
                    </button>
                )}

                {pendiente ? (
                    <button
                        type="button"
                        onClick={() => router.post(`/panel/contratos/${ultimo.uuid}/anular`, {}, { preserveScroll: true })}
                        className="apoyo text-fog transition-colors hover:text-danger"
                    >
                        Anular el enlace
                    </button>
                ) : null}
            </div>

            {firma.no_se_puede ? <p className="apoyo mt-1 text-fog">{firma.no_se_puede}</p> : null}

            <Dialogo
                abierto={confirmando}
                alCerrar={() => setConfirmando(false)}
                titulo="Mandar el contrato para firmar"
                descripcion={`Le llega a ${firma.destino}${
                    firma.para_apoderado ? ', su apoderado, que es quien firma' : ''
                }: un enlace para leer el contrato con sus datos y firmarlo con el dedo.${
                    pendiente ? ' El enlace anterior deja de servir.' : ''
                }`}
                accion={`/panel/clientes/${cliente.uuid}/contrato/enviar`}
                via="inertia"
                metodo="post"
                etiquetaConfirmar="Mandar"
            />
        </div>
    );
}

/**
 * Borrar sus datos personales, como da derecho la ley (Ley 21.719).
 *
 * Sus membresías y pagos se quedan —sin nombre— para que las cuentas no se
 * muevan hacia atrás. No se deshace: se confirma escribiendo BORRAR.
 */
function BorrarDatos({ cliente }) {
    const [abierto, setAbierto] = useState(false);
    const [motivo, setMotivo] = useState('solicitud');
    const [confirmacion, setConfirmacion] = useState('');
    const bloqueado = cliente.borrar_bloqueado;

    return (
        <Bloque titulo="Datos personales">
            <p className="apoyo text-fog">
                Si pide que se borren sus datos, se borran aquí: nombre, RUT, contacto, foto, correos y
                contratos. Sus membresías y pagos se quedan en las cuentas, sin nombre.
            </p>

            {/* Lo que lo impide se dice ANTES, no al intentarlo. */}
            {bloqueado ? (
                <p className="apoyo mt-2 flex items-start gap-1 text-warn">
                    <AlertTriangleIcon className="mt-0.5 size-3 shrink-0" aria-hidden="true" />
                    {bloqueado}
                </p>
            ) : null}

            <button
                type="button"
                disabled={Boolean(bloqueado)}
                onClick={() => setAbierto(true)}
                className="mt-3 inline-flex items-center gap-1.5 rounded-control border border-danger/40 px-2.5 py-1 text-sm text-danger transition-colors hover:bg-danger/10 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <Trash2Icon className="size-3.5" aria-hidden="true" />
                Borrar sus datos personales
            </button>

            <Dialogo
                abierto={abierto}
                alCerrar={() => {
                    setAbierto(false);
                    setConfirmacion('');
                }}
                titulo="Borrar sus datos personales"
                descripcion={`Se borran para siempre el nombre, el RUT, el contacto, la foto, los correos y los contratos de ${cliente.nombre}. Sus membresías y pagos se quedan en las cuentas como «Socio Borrado». No se puede deshacer.`}
                accion={`/panel/clientes/${cliente.uuid}/borrar-datos`}
                datos={{ motivo, confirmacion }}
                via="inertia"
                metodo="post"
                etiquetaConfirmar="Borrar para siempre"
                peligrosa
                puedeConfirmar={confirmacion.trim().toUpperCase() === 'BORRAR'}
            >
                <fieldset className="space-y-1.5">
                    <legend className="rotulo mb-1">Por qué</legend>
                    <label className="flex items-center gap-2 text-sm text-chalk">
                        <input
                            type="radio"
                            name="motivo"
                            value="solicitud"
                            checked={motivo === 'solicitud'}
                            onChange={() => setMotivo('solicitud')}
                        />
                        Lo pidió la persona
                    </label>
                    <label className="flex items-center gap-2 text-sm text-chalk">
                        <input
                            type="radio"
                            name="motivo"
                            value="plazo"
                            checked={motivo === 'plazo'}
                            onChange={() => setMotivo('plazo')}
                        />
                        Ya no hacía falta guardarlos
                    </label>
                </fieldset>

                <label className="mt-3 block text-sm text-chalk">
                    Escribe <strong>BORRAR</strong> para confirmar
                    <input
                        type="text"
                        value={confirmacion}
                        onChange={(e) => setConfirmacion(e.target.value)}
                        autoComplete="off"
                        className="mt-1 w-full rounded-control border border-line bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                    />
                </label>
            </Dialogo>
        </Bloque>
    );
}

export default function Ficha({ cliente, inscripciones, pagos, resumen, fiado }) {
    const { auth } = usePage().props;
    // null = ningun dialogo abierto.
    const [confirmando, setConfirmando] = useState(null);
    /*
     * RENOVAR, INSCRIBIR Y COBRAR SE HACEN AQUÍ, en una ventana sobre la ficha.
     * Son lo que se hace con el socio delante; mandarlo a otra pantalla obligaba
     * a volver a buscarlo al terminar. Siguen siendo enlaces de verdad: con
     * Ctrl+clic se abre la pantalla entera.
     */
    const [enVentana, setEnVentana] = useState(null);
    const [anotandoFiado, setAnotandoFiado] = useState(false);
    // Lo que debe de su membresía, escondido desde Configuración: aquí sale en
    // la cifra de arriba, en el botón de cobrar y en las dos tablas.
    const sinDeudas = Boolean(usePage().props.privado?.sin_pendientes);
    // Cobrar lo del mesón sin salir de su ficha: con la persona delante, un
    // salto a otra pantalla es lo que hace que esa cuenta se quede sin cobrar.
    const [cobrandoFiado, setCobrandoFiado] = useState(false);

    const abrirEnVentana = (atajo) => (e) => {
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) {
            return;
        }

        e.preventDefault();
        setEnVentana(atajo);
    };

    const vigente = inscripciones.find((i) => i.vigente);
    // Una pausada no es vigente, pero tampoco se fue: hay que poder
    // reanudarla desde aquí, con el socio delante, sin ir a buscarla.
    const pausada = vigente ? null : inscripciones.find((i) => i.id_estado === 101);
    const puedeGestionar = puede(auth, 'inscripciones.gestionar');
    // Lo que se cobra es lo que se DEBE, este o no vigente el plan: a quien se
    // le vencio debiendo plata el boton le abria el cobro en blanco, que es
    // justo el caso en que mas falta hace.
    const conSaldo = (vigente?.pendiente > 0 ? vigente : null) ?? inscripciones.find((i) => i.pendiente > 0);

    return (
        <>
            <Head title={cliente.nombre} />

            <header className="mb-5">
                <Link
                    href="/panel/clientes"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Clientes
                </Link>

                <div className="mt-1 flex flex-wrap items-start justify-between gap-3">
                    <div className="flex items-start gap-3">
                        {cliente.datos_borrados ? (
                            <Retrato nombre={cliente.nombre} foto={null} tamano="lg" />
                        ) : (
                            <FotoDelSocio cliente={cliente} />
                        )}

                        <div>
                            <h1 className="text-lg font-semibold text-chalk">
                                {cliente.nombre}
                                {! cliente.activo ? (
                                    <span className="ml-2 rounded-pill border border-line bg-surface-2 px-2 py-0.5 align-middle text-xs text-fog">
                                        {cliente.datos_borrados ? 'Datos borrados' : 'Dado de baja'}
                                    </span>
                                ) : null}
                            </h1>
                            <p className="apoyo text-fog">
                                {cliente.rut ?? 'Sin RUT'} · socio desde {cliente.desde ?? '-'}
                                {vigente ? <> · <Vigencia dias={vigente.dias} /></> : null}
                                {pausada ? (
                                    <>
                                        {' · '}
                                        <span className="font-medium text-warn">
                                            {pausada.membresia} en pausa
                                            {pausada.pausada_hasta ? ` hasta el ${pausada.pausada_hasta}` : ''}
                                        </span>
                                    </>
                                ) : null}
                            </p>
                        </div>
                    </div>

                    {/* Una ficha borrada no se edita, no se reactiva y no se cobra. */}
                    {cliente.datos_borrados ? null : (
                    <div className="flex flex-wrap gap-2">
                        <Link
                            href={`/panel/clientes/${cliente.uuid}/editar`}
                            className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                        >
                            <PencilIcon className="size-4" aria-hidden="true" />
                            Editar
                        </Link>
                        {/* Dar de baja o volver a dar de alta: nunca las dos,
                            porque solo una tiene sentido en cada momento. */}
                        {cliente.activo ? (
                            <button
                                type="button"
                                onClick={() => setConfirmando('desactivar')}
                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                            >
                                <UserMinusIcon className="size-4" aria-hidden="true" />
                                Dar de baja
                            </button>
                        ) : (
                            <button
                                type="button"
                                onClick={() => router.patch(`/panel/clientes/${cliente.uuid}/reactivar`, {}, { preserveScroll: true })}
                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                            >
                                <UserPlusIcon className="size-4" aria-hidden="true" />
                                Reactivar
                            </button>
                        )}

                        {/* Con el socio delante se hace TODO desde aqui: renovarle,
                            inscribirlo o cobrarle. Antes renovar obligaba a saltar a
                            Inscripciones y buscar su plan, e inscribirlo de nuevo,
                            a buscar al mismo socio otra vez en otra pantalla. */}
                        {cliente.activo && pausada && puedeGestionar ? (
                            <button
                                type="button"
                                onClick={() => setConfirmando('reanudar')}
                                className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                            >
                                <PlayIcon className="size-4" aria-hidden="true" />
                                Reanudar
                            </button>
                        ) : null}
                        {cliente.activo && vigente ? (
                            <a
                                href={`/panel/inscripciones/${vigente.uuid}/renovar?volver=${cliente.uuid}`}
                                onClick={abrirEnVentana({
                                    href: `/panel/inscripciones/${vigente.uuid}/renovar?volver=${cliente.uuid}`,
                                    titulo: `Renovar ${vigente.membresia ?? 'la membresía'}`,
                                })}
                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                            >
                                <RefreshCwIcon className="size-4" aria-hidden="true" />
                                Renovar
                            </a>
                        ) : null}
                        {cliente.activo && ! vigente && ! pausada ? (
                            <a
                                href={`/panel/inscripciones/crear?cliente=${cliente.uuid}&volver=${cliente.uuid}`}
                                onClick={abrirEnVentana({
                                    href: `/panel/inscripciones/crear?cliente=${cliente.uuid}&volver=${cliente.uuid}`,
                                    titulo: `Inscribir a ${cliente.nombre}`,
                                })}
                                className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                            >
                                <PlusIcon className="size-4" aria-hidden="true" />
                                Inscribir
                            </a>
                        ) : null}
                        {conSaldo ? (
                            <a
                                href={`/panel/pagos/cobrar?inscripcion=${conSaldo.uuid}&volver=${cliente.uuid}`}
                                onClick={abrirEnVentana({
                                    href: `/panel/pagos/cobrar?inscripcion=${conSaldo.uuid}&volver=${cliente.uuid}`,
                                    titulo: `Cobrar ${pesos.format(conSaldo.pendiente)} a ${cliente.nombre}`,
                                })}
                                className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                            >
                                <BanknoteIcon className="size-4" aria-hidden="true" />
                                {/* Sin la cifra cuando está escondida: el botón
                                    se queda, porque cobrar hay que poder. */}
                                Cobrar{sinDeudas ? '' : ` ${pesos.format(conSaldo.pendiente)}`}
                            </a>
                        ) : null}
                    </div>
                    )}
                </div>
            </header>

            {enVentana ? (
                <ModalDePagina
                    href={enVentana.href}
                    titulo={enVentana.titulo}
                    alCerrar={() => setEnVentana(null)}
                />
            ) : null}

            {/* Una ficha sin dueño: se abre desde un pago o una membresía
                antigua, y tiene que decir por qué no tiene nombre. */}
            {cliente.datos_borrados ? (
                <div className="mb-4 flex items-start gap-2 rounded-panel border border-line bg-surface-2 px-3 py-2.5 text-sm text-fog">
                    <ShieldCheckIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <span>
                        Sus datos personales se borraron el {cliente.datos_borrados.el}
                        {cliente.datos_borrados.por ? ` (lo hizo ${cliente.datos_borrados.por})` : ''}
                        {cliente.datos_borrados.motivo ? `: ${cliente.datos_borrados.motivo.toLowerCase()}` : ''}.
                        Sus membresías y pagos siguen en las cuentas, sin nombre.
                    </span>
                </div>
            ) : null}

            {/* EL AVISO DE LO FIADO, arriba de todo y antes de las cifras.
                Si viene a pagar su mensualidad y ademas debe tres bebidas, hay
                que saberlo con la persona delante, no dos semanas despues.

                Va aparte de lo que debe de su membresia a proposito: son dos
                deudas que se cobran por sitios distintos, y sumarlas daria una
                cifra que no se puede cobrar de una vez. */}
            {fiado ? (
                <div className="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-panel border border-warn/40 bg-warn/5 px-3 py-2.5">
                    <p className="flex items-start gap-2 text-sm text-warn">
                        <ShoppingBagIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                        <span>
                            Debe{' '}
                            <span className="font-semibold tabular-nums">
                                <Reservado ancho="w-14">{pesos.format(fiado.total)}</Reservado>
                            </span>{' '}
                            del mesón · {fiado.cuantas}{' '}
                            {fiado.cuantas === 1 ? 'cosa' : 'cosas'} desde {fiado.desde}
                            <span className="apoyo block text-fog">
                                {fiado.lineas.map((l) => l.concepto).join(', ')}
                            </span>
                        </span>
                    </p>

                    <span className="flex shrink-0 items-center gap-3">
                        {/* SE COBRA AQUÍ. Antes esto llevaba a la libreta del
                            mesón, y había que buscarlo otra vez en una lista
                            para pulsar el mismo botón. */}
                        <button
                            type="button"
                            onClick={() => setCobrandoFiado(true)}
                            className="apoyo rounded-control border border-warn/40 px-2.5 py-1 text-warn transition-colors hover:bg-warn/10"
                        >
                            Pagó
                        </button>

                        <Link href="/panel/fiados" className="apoyo text-fog transition-colors hover:text-chalk">
                            La libreta
                        </Link>
                    </span>
                </div>
            ) : null}

            {/* El aviso dice el nombre y la cantidad, no «¿seguro?»: el error
                de estos botones es pulsar el de al lado, y a eso un «¿seguro?»
                también le dice que sí. */}
            <ConfirmarDinero
                abierto={cobrandoFiado}
                alCerrar={() => setCobrandoFiado(false)}
                titulo="Cobrar lo fiado"
                quien={cliente.nombre}
                monto={fiado?.total ?? 0}
                detalle={fiado?.lineas}
                consecuencia="Su cuenta del mesón queda saldada. Esto no entra en la caja del gimnasio."
                etiquetaConfirmar="Pagó"
                accion="/panel/fiados/saldar"
                metodo="post"
                datos={{ id_cliente: cliente.id, nombre: null }}
            />

            {/* APUNTAR OTRA COSA, sin salir de su ficha: se fía con la persona
                delante, y un salto a otra pantalla es más trabajo que el fiado. */}
            {cliente.activo && ! cliente.datos_borrados ? (
                <div className="mb-4">
                    {anotandoFiado ? (
                        <div className="rounded-panel border border-line bg-surface p-4">
                            <div className="mb-2 flex items-center justify-between gap-3">
                                <h2 className="rotulo">Anotar algo fiado</h2>
                                <button
                                    type="button"
                                    onClick={() => setAnotandoFiado(false)}
                                    className="apoyo text-fog transition-colors hover:text-chalk"
                                >
                                    Cerrar
                                </button>
                            </div>
                            <ApuntarFiado alTerminar={() => setAnotandoFiado(false)} socio={{ id: cliente.id, nombre: cliente.nombre }} />
                        </div>
                    ) : (
                        <button
                            type="button"
                            onClick={() => setAnotandoFiado(true)}
                            className="apoyo inline-flex items-center gap-1.5 text-fog transition-colors hover:text-chalk"
                        >
                            <ShoppingBagIcon className="size-3.5" aria-hidden="true" />
                            Anotar algo fiado
                        </button>
                    )}
                </div>
            ) : null}

            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Membresías</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">
                        {resumen.inscripciones}
                    </p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Ha pagado</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">
                        <Reservado ancho="w-20">{pesos.format(resumen.pagado)}</Reservado>
                    </p>
                </div>
                {/* La deuda es lo único accionable de las tres. */}
                {sinDeudas ? null : (
                <div
                    className={`rounded-panel border p-3 ${
                        resumen.debe > 0 ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface'
                    }`}
                >
                    <p className="rotulo">Debe</p>
                    <p
                        className={`mt-0.5 text-lg font-semibold tabular-nums ${
                            resumen.debe > 0 ? 'text-warn' : 'text-chalk'
                        }`}
                    >
                        <Reservado ancho="w-20">{pesos.format(resumen.debe)}</Reservado>
                    </p>
                </div>
                )}
            </div>

            {/*
             * DOS COLUMNAS PAREJAS. La izquierda llevaba seis bloques y la
             * derecha dos: abajo a la derecha quedaba medio panel vacío. Ahora
             * la izquierda es quién es y cómo ubicarlo (con la emergencia dentro
             * del mismo bloque), y la derecha lo que tiene: membresías, pagos y
             * contrato. Borrar sus datos va al final, a lo ancho y aparte.
             */}
            <div className="grid items-start gap-3 lg:grid-cols-3">
                <div className="space-y-3 lg:col-span-1">
                    <Bloque titulo="Contacto">
                        <dl className="space-y-3">
                            <Dato etiqueta="Celular">{cliente.celular ? celularLegible(cliente.celular) : null}</Dato>
                            <Dato etiqueta="Correo">
                                {cliente.email ? (
                                    <a href={`mailto:${cliente.email}`} className="break-all hover:underline">
                                        {cliente.email}
                                    </a>
                                ) : null}
                            </Dato>
                            <Dato etiqueta="Dirección">{cliente.direccion}</Dato>
                            <div className="grid grid-cols-2 gap-3">
                                <Dato etiqueta="Nacimiento">
                                    {cliente.nacimiento
                                        ? `${cliente.nacimiento}${cliente.edad !== null ? ` · ${cliente.edad} años` : ''}`
                                        : null}
                                </Dato>
                                <Dato etiqueta="Convenio">{cliente.convenio}</Dato>
                            </div>
                        </dl>

                        {/* En urgencia se busca este dato con prisa: va marcado
                            y separado, aunque dentro del mismo bloque. */}
                        <div className="mt-4 border-t border-line pt-3">
                            <p className="rotulo mb-2 text-danger">En caso de emergencia</p>
                            <dl className="grid grid-cols-2 gap-3">
                                <Dato etiqueta="Avisar a">{cliente.contacto_emergencia}</Dato>
                                <Dato etiqueta="Teléfono">
                                    {cliente.telefono_emergencia ? celularLegible(cliente.telefono_emergencia) : null}
                                </Dato>
                            </dl>
                        </div>
                    </Bloque>

                    {cliente.menor && cliente.apoderado ? (
                        <Bloque titulo="Apoderado">
                            <dl className="space-y-3">
                                <Dato etiqueta="Nombre">{cliente.apoderado.nombre}</Dato>
                                <Dato etiqueta="RUT">{cliente.apoderado.rut}</Dato>
                                <Dato etiqueta="Correo">{cliente.apoderado.email}</Dato>
                                <Dato etiqueta="Teléfono">{cliente.apoderado.telefono}</Dato>
                                <Dato etiqueta="Parentesco">{cliente.apoderado.parentesco}</Dato>
                                <Dato etiqueta="Consentimiento">
                                    {cliente.apoderado.consentimiento ? (
                                        'Firmado'
                                    ) : (
                                        <span className="text-warn">Pendiente</span>
                                    )}
                                </Dato>
                            </dl>
                        </Bloque>
                    ) : null}

                    {cliente.observaciones ? (
                        <Bloque titulo="Observaciones">
                            <p className="text-sm whitespace-pre-line text-fog">{cliente.observaciones}</p>
                        </Bloque>
                    ) : null}
                </div>

                <div className="space-y-3 lg:col-span-2">
                    <Bloque titulo="Membresías">
                        <Tabla
                            columnas={sinDeudas
                                ? ['Plan', 'Estado', 'Inicio', 'Vence', 'Total']
                                : ['Plan', 'Estado', 'Inicio', 'Vence', 'Total', 'Debe']}
                            vacia={inscripciones.length === 0}
                            mensajeVacio="Este socio todavía no tiene ninguna membresía."
                        >
                            {inscripciones.map((i) => (
                                <Fila key={i.uuid}>
                                    <Celda className="font-medium text-chalk">
                                        <Link
                                            href={`/panel/inscripciones/${i.uuid}`}
                                            className="hover:underline"
                                        >
                                            {i.membresia ?? '-'}
                                        </Link>
                                    </Celda>
                                    <Celda>
                                        <Estado codigo={i.id_estado} />
                                    </Celda>
                                    <Celda className="tabular-nums">{i.inicio ?? '-'}</Celda>
                                    <Celda className="tabular-nums">{i.vence ?? '-'}</Celda>
                                    <Cifra>
                                        <Reservado ancho="w-16">{pesos.format(i.total)}</Reservado>
                                    </Cifra>
                                    {sinDeudas ? null : (
                                        <Cifra className={i.pendiente > 0 ? 'font-medium text-warn' : ''}>
                                            {i.pendiente > 0 ? (
                                                <Reservado ancho="w-16">{pesos.format(i.pendiente)}</Reservado>
                                            ) : (
                                                '-'
                                            )}
                                        </Cifra>
                                    )}
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>

                    <Bloque titulo="Últimos pagos">
                        <Tabla
                            columnas={sinDeudas
                                ? ['Fecha', 'Método', 'Estado', 'Abonado']
                                : ['Fecha', 'Método', 'Estado', 'Abonado', 'Pendiente']}
                            vacia={pagos.length === 0}
                            mensajeVacio="Todavía no ha pagado nada."
                        >
                            {pagos.map((p) => (
                                <Fila key={p.uuid}>
                                    <Celda className="tabular-nums text-chalk">
                                        <Link href={`/panel/pagos/${p.uuid}`} className="hover:underline">
                                            {p.fecha ?? '-'}
                                        </Link>
                                    </Celda>
                                    <Celda>{p.metodo ?? '-'}</Celda>
                                    <Celda>
                                        <Estado codigo={p.id_estado} />
                                    </Celda>
                                    <Cifra className="text-chalk">
                                        <Reservado ancho="w-16">{pesos.format(p.abonado)}</Reservado>
                                    </Cifra>
                                    {sinDeudas ? null : (
                                        <Cifra className={p.pendiente > 0 ? 'text-warn' : ''}>
                                            {p.pendiente > 0 ? (
                                                <Reservado ancho="w-16">{pesos.format(p.pendiente)}</Reservado>
                                            ) : (
                                                '-'
                                            )}
                                        </Cifra>
                                    )}
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>

                    <ContratoDelSocio cliente={cliente} />
                </div>
            </div>

            {/* Solo quien puede borrar para siempre: recepción da de baja,
                pero esto no se deshace. Al final y a lo ancho: es lo último que
                se hace con una ficha y no debe estar a mano. */}
            {! cliente.datos_borrados && puede(auth, 'clientes.eliminar') ? (
                <div className="mt-3">
                    <BorrarDatos cliente={cliente} />
                </div>
            ) : null}

            {pausada ? (
                <Dialogo
                    abierto={confirmando === 'reanudar'}
                    alCerrar={() => setConfirmando(null)}
                    titulo="Reanudar la membresía"
                    descripcion={
                        pausada.pausada_hasta
                            ? `${pausada.membresia} de ${cliente.nombre} estaba pausada hasta el ${pausada.pausada_hasta}. Al reanudar se le devuelven los días que le quedaban.`
                            : `Al reanudar ${pausada.membresia} de ${cliente.nombre} se le devuelven los días que le quedaban.`
                    }
                    accion={`/panel/inscripciones/${pausada.uuid}/reanudar`}
                    etiquetaConfirmar="Reanudar"
                />
            ) : null}

            <Dialogo
                abierto={confirmando === 'desactivar'}
                alCerrar={() => setConfirmando(null)}
                titulo="Dar de baja al socio"
                descripcion={`${cliente.nombre} dejará de aparecer al inscribir y al cobrar. Su ficha, su historial y sus pagos siguen ahí, y se puede reactivar cuando vuelva.`}
                accion={`/panel/clientes/${cliente.uuid}/desactivar`}
                via="inertia"
                metodo="patch"
                etiquetaConfirmar="Dar de baja"
            />

        </>
    );
}
