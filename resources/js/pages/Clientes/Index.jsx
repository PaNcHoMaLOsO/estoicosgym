import { Head, Link } from '@inertiajs/react';
import { MailIcon, MessageCircleIcon, UserPlusIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Estado from '@/components/Estado';
import Filtros from '@/components/Filtros';
import Paginacion from '@/components/Paginacion';
import Plazo from '@/components/Plazo';
import Retrato from '@/components/Retrato';
import { Celda, DosLineas, Fila, Tabla } from '@/components/Tabla';
import { celularLegible, whatsapp } from '@/lib/contacto';

/*
 * Cuatro columnas y no seis: cada una responde una pregunta. ¿Quién es? ¿Cómo
 * lo ubico? ¿Qué plan tiene? ¿Está al día? El RUT va bajo el nombre y la fecha
 * de vencimiento bajo el plan, que es donde se buscan.
 */
const COLUMNAS = [
    'Socio',
    { titulo: 'Contacto', className: 'hidden md:table-cell' },
    { titulo: 'Plan', className: 'hidden sm:table-cell' },
    'Estado',
];

/**
 * El convenio del socio, en pequeño bajo su RUT.
 *
 * ES LO QUE EXPLICA EL PRECIO. Sin verlo aquí, «¿por qué este paga $25.000?»
 * obliga a abrir su ficha, y en el mesón eso se pregunta todo el día. Quien no
 * tiene ninguno no ocupa sitio: no se pinta nada.
 */
function Convenio({ nombre }) {
    if (! nombre) {
        return null;
    }

    return (
        <span className="ml-1.5 inline-flex max-w-40 items-center gap-1 rounded-pill border border-line bg-surface-2 px-1.5 py-px align-middle text-[0.7rem] text-fog">
            <span className="truncate">{nombre}</span>
        </span>
    );
}

function Contacto({ celular, email }) {
    if (!celular && !email) {
        return <span className="apoyo text-fog">Sin contacto</span>;
    }

    return (
        <div className="flex flex-col gap-0.5">
            {celular ? (
                <a
                    href={whatsapp(celular)}
                    // Siempre la misma ventana, para no dejar una pestaña por socio.
                    target="progym-whatsapp"
                    rel="noopener"
                    title="Escribir por WhatsApp"
                    className="inline-flex items-center gap-1.5 whitespace-nowrap text-chalk hover:underline"
                >
                    <MessageCircleIcon className="size-3.5 text-[#25D366]" aria-hidden="true" />
                    <span className="tabular-nums">{celularLegible(celular)}</span>
                </a>
            ) : null}
            {email ? (
                <a
                    href={`mailto:${email}`}
                    className="apoyo inline-flex max-w-56 items-center gap-1.5 text-fog hover:text-chalk hover:underline"
                >
                    <MailIcon className="size-3 shrink-0" aria-hidden="true" />
                    <span className="truncate">{email}</span>
                </a>
            ) : null}
        </div>
    );
}

export default function Index({ clientes, filtros, resumen }) {
    const opciones = [
        { valor: '', etiqueta: 'Todos', cantidad: resumen.total },
        { valor: 'al_dia', etiqueta: 'Con plan vigente', cantidad: resumen.activos },
        { valor: 'por_vencer', etiqueta: 'Vencen esta semana', cantidad: resumen.por_vencer, tono: 'warn' },
        { valor: 'vencidos', etiqueta: 'Vencidos', cantidad: resumen.vencidos, tono: 'danger' },
        { valor: 'pausados', etiqueta: 'Pausados', cantidad: resumen.pausados },
        { valor: 'sin_plan', etiqueta: 'Sin plan', cantidad: resumen.sin_plan },
        // Quien solo compró pases está de paso: no es un socio más de la lista.
        { valor: 'pases', etiqueta: 'Solo pase diario', cantidad: resumen.pases, aparte: true },
    ];

    const extra = filtros.buscar ? { buscar: filtros.buscar } : {};

    return (
        <>
            <Head title="Clientes" />

            <header className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">{filtros.bajas ? 'Dados de baja' : 'Clientes'}</h1>
                    <p className="apoyo text-fog">Haz clic en un socio para abrir su ficha</p>
                </div>

                <Link
                    href="/panel/clientes/crear"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <UserPlusIcon className="size-4" aria-hidden="true" />
                    Nuevo socio
                </Link>
            </header>

            <div className="mb-3 flex flex-col gap-3">
                <Buscador
                    ruta="/panel/clientes"
                    valor={filtros.buscar}
                    etiqueta="Buscar por nombre, RUT, correo o celular"
                    /* Se mantiene mientras se busca: si no, escribir un nombre
                       devolveria al listado de activos y el socio dado de baja
                       que se estaba buscando desapareceria. */
                    extra={filtros.bajas ? { bajas: 1 } : filtros.filtro ? { filtro: filtros.filtro } : {}}
                />

                <div className="flex flex-wrap items-center justify-between gap-2">
                    {filtros.bajas ? (
                        <Link
                            href="/panel/clientes"
                            className="inline-flex items-center rounded-pill border border-line px-3 py-1 text-sm text-chalk transition-colors hover:bg-surface-2"
                        >
                            ← Volver a los activos
                        </Link>
                    ) : (
                        <Filtros ruta="/panel/clientes" actual={filtros.filtro} opciones={opciones} extra={extra} />
                    )}

                    {/* Sin esto, dar de baja a alguien lo hace desaparecer del
                        panel entero. Solo se ofrece cuando hay alguno. */}
                    {!filtros.bajas && resumen.bajas > 0 ? (
                        <Link href="/panel/clientes?bajas=1" className="apoyo text-fog transition-colors hover:text-chalk">
                            Ver {resumen.bajas} {resumen.bajas === 1 ? 'dado' : 'dados'} de baja
                        </Link>
                    ) : null}
                </div>
            </div>

            <Tabla
                columnas={COLUMNAS}
                vacia={clientes.data.length === 0}
                mensajeVacio={
                    filtros.buscar
                        ? `Ningún socio coincide con «${filtros.buscar}».`
                        : filtros.bajas
                          ? 'No hay ningún socio dado de baja.'
                          : filtros.filtro
                            ? 'No hay socios en este grupo.'
                            : 'Todavía no hay clientes registrados.'
                }
            >
                {clientes.data.map((cliente) => (
                    <Fila key={cliente.uuid} href={`/panel/clientes/${cliente.uuid}`}>
                        <Celda>
                            {/* La cara en la lista, no solo en la ficha: es
                                buscando donde hace falta distinguir entre dos
                                socios que se llaman casi igual. */}
                            <div className="flex items-center gap-2.5">
                                <Retrato nombre={cliente.nombre} foto={cliente.foto} tamano="sm" />
                                <DosLineas
                                    arriba={
                                        <Link href={`/panel/clientes/${cliente.uuid}`} className="font-medium text-chalk hover:underline">
                                            {cliente.nombre}
                                        </Link>
                                    }
                                    abajo={
                                        <span>
                                            <span className="tabular-nums">
                                                {cliente.run_pasaporte ?? 'Sin RUT'}
                                            </span>
                                            <Convenio nombre={cliente.convenio} />
                                        </span>
                                    }
                                />
                            </div>
                        </Celda>
                        <Celda className="hidden md:table-cell">
                            <Contacto celular={cliente.celular} email={cliente.email} />
                        </Celda>
                        <Celda className="hidden sm:table-cell">
                            {cliente.membresia ? (
                                <DosLineas
                                    arriba={<span className="text-chalk">{cliente.membresia}</span>}
                                    abajo={cliente.vence && !cliente.es_pase ? <span className="tabular-nums">vence el {cliente.vence}</span> : null}
                                />
                            ) : (
                                <span className="text-fog">Sin plan</span>
                            )}
                        </Celda>
                        <Celda>
                            <div className="flex flex-col items-start gap-0.5">
                                {cliente.es_pase ? (
                                    <>
                                        <span className="inline-flex rounded-pill border border-line bg-surface-2 px-2 py-0.5 text-xs text-fog">
                                            Pase diario
                                        </span>
                                        <span className="apoyo tabular-nums text-fog">vino el {cliente.pase_del}</span>
                                    </>
                                ) : (
                                    <>
                                        <Estado codigo={cliente.id_estado} vacio="Sin membresía" />
                                        <Plazo dias={cliente.dias} estado={cliente.id_estado} />
                                    </>
                                )}
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <Paginacion paginador={clientes} />
        </>
    );
}
