import { Head, Link } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Estado from '@/components/Estado';
import Filtros from '@/components/Filtros';
import Paginacion from '@/components/Paginacion';
import Plazo from '@/components/Plazo';
import { Celda, DosLineas, Fila, Tabla } from '@/components/Tabla';
import { pesos } from '@/components/Tablero';

/*
 * Una fila, una membresía: de quién es, qué plan, cuándo corre, en qué está y
 * si está pagada. Ocho columnas sueltas pasaron a cinco que se leen de una vez.
 */
const COLUMNAS = [
    'Socio',
    { titulo: 'Plan', className: 'hidden sm:table-cell' },
    { titulo: 'Periodo', className: 'hidden lg:table-cell' },
    'Estado',
    { titulo: 'Pago', className: 'text-right' },
];

/** Pagada, o lo que falta: la pregunta que se hace al mirar una membresía. */
function Pago({ debe, precio }) {
    if (precio === 0) {
        return <span className="apoyo text-fog">Sin costo</span>;
    }

    if (debe > 0) {
        return (
            <DosLineas
                className="items-end"
                arriba={<span className="font-medium tabular-nums text-warn">Debe {pesos.format(debe)}</span>}
                abajo={<span className="tabular-nums">de {pesos.format(precio)}</span>}
            />
        );
    }

    return (
        <DosLineas
            className="items-end"
            arriba={<span className="text-ok">Pagada</span>}
            abajo={<span className="tabular-nums">{pesos.format(precio)}</span>}
        />
    );
}

export default function Index({ inscripciones, filtros, resumen }) {
    const opciones = [
        { valor: '', etiqueta: 'Todas', cantidad: resumen.total },
        { valor: 'al_dia', etiqueta: 'Vigentes', cantidad: resumen.activas },
        { valor: 'por_vencer', etiqueta: 'Vencen esta semana', cantidad: resumen.por_vencer, tono: 'warn' },
        { valor: 'con_deuda', etiqueta: 'Con deuda', cantidad: resumen.con_deuda, tono: 'warn' },
        { valor: 'vencidas', etiqueta: 'Vencidas', cantidad: resumen.vencidas, tono: 'danger' },
        { valor: 'pausadas', etiqueta: 'Pausadas', cantidad: resumen.pausadas },
        { valor: 'pases', etiqueta: 'Pases diarios', cantidad: resumen.pases, aparte: true },
    ];

    return (
        <>
            <Head title="Inscripciones" />

            <header className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Inscripciones</h1>
                    <p className="apoyo text-fog">Cada membresía vendida, con su plazo y su pago</p>
                </div>

                <Link
                    href="/panel/inscripciones/crear"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nueva inscripción
                </Link>
            </header>

            <div className="mb-3 flex flex-col gap-3">
                <Buscador
                    ruta="/panel/inscripciones"
                    valor={filtros.buscar}
                    etiqueta="Buscar por socio o RUT"
                    extra={filtros.filtro ? { filtro: filtros.filtro } : {}}
                />
                <Filtros
                    ruta="/panel/inscripciones"
                    actual={filtros.filtro}
                    opciones={opciones}
                    extra={filtros.buscar ? { buscar: filtros.buscar } : {}}
                />
            </div>

            <Tabla
                columnas={COLUMNAS}
                vacia={inscripciones.data.length === 0}
                mensajeVacio={
                    filtros.buscar
                        ? `Ninguna inscripción coincide con «${filtros.buscar}».`
                        : filtros.filtro
                          ? 'No hay inscripciones en este grupo.'
                          : 'Todavía no hay inscripciones.'
                }
            >
                {inscripciones.data.map((inscripcion) => (
                    <Fila key={inscripcion.uuid} href={`/panel/inscripciones/${inscripcion.uuid}`}>
                        <Celda>
                            <DosLineas
                                arriba={
                                    <Link
                                        href={`/panel/inscripciones/${inscripcion.uuid}`}
                                        className="font-medium text-chalk hover:underline"
                                    >
                                        {inscripcion.socio}
                                    </Link>
                                }
                                abajo={
                                    <span className="tabular-nums">
                                        {/* En celular no hay columna de plan: va aquí. */}
                                        <span className="sm:hidden">{inscripcion.membresia ?? 'Sin plan'} · </span>
                                        {inscripcion.rut ?? 'Sin RUT'}
                                    </span>
                                }
                            />
                        </Celda>
                        <Celda className="hidden text-chalk sm:table-cell">{inscripcion.membresia ?? 'Sin plan'}</Celda>
                        <Celda className="hidden tabular-nums whitespace-nowrap lg:table-cell">
                            {inscripcion.inicio ?? '?'} <span aria-hidden="true">→</span>
                            <span className="sr-only">hasta</span> {inscripcion.vence ?? '?'}
                        </Celda>
                        <Celda>
                            <div className="flex flex-col items-start gap-0.5">
                                {/* Un pase no «vence»: se usa un día. Pintarlo
                                    «Vencida» en rojo era alarmar por nada. */}
                                {inscripcion.es_pase && inscripcion.id_estado === 102 ? (
                                    <span className="inline-flex rounded-pill border border-line bg-surface-2 px-2 py-0.5 text-xs text-fog">
                                        Usado
                                    </span>
                                ) : (
                                    <Estado codigo={inscripcion.id_estado} />
                                )}
                                {/* El plazo solo importa mientras corre: en una
                                    vencida, cancelada o cambiada ya no dice nada. */}
                                {[100, 101].includes(inscripcion.id_estado) ? (
                                    <Plazo dias={inscripcion.dias_restantes} estado={inscripcion.id_estado} />
                                ) : null}
                            </div>
                        </Celda>
                        <Celda className="text-right">
                            <Pago debe={inscripcion.debe} precio={inscripcion.precio_final} />
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <Paginacion paginador={inscripciones} />
        </>
    );
}
