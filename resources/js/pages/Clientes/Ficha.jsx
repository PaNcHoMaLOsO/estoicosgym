import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
import {
    AlertTriangleIcon,
    ArrowLeftIcon,
    CameraIcon,
    FileTextIcon,
    PencilIcon,
    PlusIcon,
    SendIcon,
    ShieldCheckIcon,
    ShoppingBagIcon,
    Trash2Icon,
    UserMinusIcon,
    UserPlusIcon,
} from 'lucide-react';

import Dialogo from '@/components/Dialogo';
import Estado from '@/components/Estado';
import Retrato from '@/components/Retrato';
import { Reservado } from '@/Privado';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';
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
            <dd className="mt-0.5 text-sm text-chalk">{children || <span className="text-fog">—</span>}</dd>
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

    function elegida(e) {
        const archivo = e.target.files?.[0];

        if (! archivo) {
            return;
        }

        setError(null);
        setSubiendo(true);

        router.post(
            `/panel/clientes/${cliente.uuid}/foto`,
            { foto_perfil: archivo },
            {
                preserveScroll: true,
                onError: (errores) => setError(errores.foto_perfil ?? 'No se pudo subir la foto.'),
                onFinish: () => {
                    setSubiendo(false);
                    // Se vacía a mano: si no, volver a elegir EL MISMO archivo
                    // no dispara el evento y parecería que el botón no hace nada.
                    if (selector.current) {
                        selector.current.value = '';
                    }
                },
            },
        );
    }

    return (
        <div className="flex shrink-0 flex-col items-center gap-1">
            <Retrato nombre={cliente.nombre} foto={cliente.foto} tamano="lg" />

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
                    onClick={() => selector.current?.click()}
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk disabled:opacity-50"
                >
                    <CameraIcon className="size-3.5" aria-hidden="true" />
                    {subiendo ? 'Subiendo…' : cliente.foto ? 'Cambiar foto' : 'Poner foto'}
                </button>

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

                {cliente.datos_borrados ? null : <FirmaPorCorreo cliente={cliente} />}
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

    const vigente = inscripciones.find((i) => i.vigente);

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
                                {cliente.rut ?? 'Sin RUT'} · socio desde {cliente.desde ?? '—'}
                                {vigente ? <> · <Vigencia dias={vigente.dias} /></> : null}
                            </p>
                        </div>
                    </div>

                    {/* Una ficha borrada no se edita, no se reactiva y no se cobra. */}
                    {cliente.datos_borrados ? null : (
                    <div className="flex gap-2">
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

                        <Link
                            href={`/panel/pagos/cobrar?inscripcion=${vigente?.uuid ?? ''}`}
                            className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                        >
                            <PlusIcon className="size-4" aria-hidden="true" />
                            Cobrar
                        </Link>
                    </div>
                    )}
                </div>
            </header>

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

                    <Link
                        href="/panel/fiados"
                        className="apoyo shrink-0 rounded-control border border-warn/40 px-2.5 py-1 text-warn transition-colors hover:bg-warn/10"
                    >
                        Cobrarlo
                    </Link>
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
                        {pesos.format(resumen.pagado)}
                    </p>
                </div>
                {/* La deuda es lo único accionable de las tres. */}
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
                        {pesos.format(resumen.debe)}
                    </p>
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-3">
                <div className="space-y-3 lg:col-span-1">
                    <Bloque titulo="Contacto">
                        <dl className="space-y-3">
                            <Dato etiqueta="Correo">
                                {cliente.email ? (
                                    <a href={`mailto:${cliente.email}`} className="hover:underline">
                                        {cliente.email}
                                    </a>
                                ) : null}
                            </Dato>
                            <Dato etiqueta="Celular">{cliente.celular}</Dato>
                            <Dato etiqueta="Dirección">{cliente.direccion}</Dato>
                            <Dato etiqueta="Nacimiento">
                                {cliente.nacimiento
                                    ? `${cliente.nacimiento}${cliente.edad !== null ? ` · ${cliente.edad} años` : ''}`
                                    : null}
                            </Dato>
                            <Dato etiqueta="Convenio">{cliente.convenio}</Dato>
                        </dl>
                    </Bloque>

                    {/* En urgencia se busca este dato con prisa: va en su propio
                        bloque y no perdido entre los demás. */}
                    <Bloque titulo="En caso de emergencia">
                        <dl className="space-y-3">
                            <Dato etiqueta="Contacto">{cliente.contacto_emergencia}</Dato>
                            <Dato etiqueta="Teléfono">{cliente.telefono_emergencia}</Dato>
                        </dl>
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

                    <ContratoDelSocio cliente={cliente} />

                    {cliente.observaciones ? (
                        <Bloque titulo="Observaciones">
                            <p className="text-sm whitespace-pre-line text-fog">{cliente.observaciones}</p>
                        </Bloque>
                    ) : null}

                    {/* Solo quien puede borrar para siempre: recepción da de baja,
                        pero esto no se deshace. */}
                    {! cliente.datos_borrados && puede(auth, 'clientes.eliminar') ? (
                        <BorrarDatos cliente={cliente} />
                    ) : null}
                </div>

                <div className="space-y-3 lg:col-span-2">
                    <Bloque titulo="Membresías">
                        <Tabla
                            columnas={['Plan', 'Estado', 'Inicio', 'Vence', 'Total', 'Debe']}
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
                                            {i.membresia ?? '—'}
                                        </Link>
                                    </Celda>
                                    <Celda>
                                        <Estado codigo={i.id_estado} />
                                    </Celda>
                                    <Celda className="tabular-nums">{i.inicio ?? '—'}</Celda>
                                    <Celda className="tabular-nums">{i.vence ?? '—'}</Celda>
                                    <Cifra>{pesos.format(i.total)}</Cifra>
                                    <Cifra className={i.pendiente > 0 ? 'font-medium text-warn' : ''}>
                                        {i.pendiente > 0 ? pesos.format(i.pendiente) : '—'}
                                    </Cifra>
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>

                    <Bloque titulo="Últimos pagos">
                        <Tabla
                            columnas={['Fecha', 'Método', 'Estado', 'Abonado', 'Pendiente']}
                            vacia={pagos.length === 0}
                            mensajeVacio="Todavía no ha pagado nada."
                        >
                            {pagos.map((p) => (
                                <Fila key={p.uuid}>
                                    <Celda className="tabular-nums text-chalk">
                                        <Link href={`/panel/pagos/${p.uuid}`} className="hover:underline">
                                            {p.fecha ?? '—'}
                                        </Link>
                                    </Celda>
                                    <Celda>{p.metodo ?? '—'}</Celda>
                                    <Celda>
                                        <Estado codigo={p.id_estado} />
                                    </Celda>
                                    <Cifra className="text-chalk">{pesos.format(p.abonado)}</Cifra>
                                    <Cifra className={p.pendiente > 0 ? 'text-warn' : ''}>
                                        {p.pendiente > 0 ? pesos.format(p.pendiente) : '—'}
                                    </Cifra>
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>
                </div>
            </div>
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
