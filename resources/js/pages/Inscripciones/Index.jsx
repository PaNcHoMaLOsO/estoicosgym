import { Head, Link } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Estado from '@/components/Estado';
import Paginacion from '@/components/Paginacion';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Socio', 'RUT', 'Membresía', 'Estado', 'Inicio', 'Vence', 'Restan', 'Precio'];

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/**
 * Los dias restantes se pintan con color solo cuando exigen actuar.
 *
 * Vencida (negativo) y «quedan 7 dias o menos» son las dos situaciones en las
 * que hay que llamar al socio; el resto va en gris para no competir con ellas.
 */
function Restantes({ dias }) {
    if (dias === null || dias === undefined) {
        return <span className="text-fog">—</span>;
    }

    if (dias < 0) {
        return <span className="font-medium text-danger">Venció hace {Math.abs(dias)} d</span>;
    }

    if (dias <= 7) {
        return <span className="font-medium text-warn">{dias} d</span>;
    }

    return <span className="text-fog">{dias} d</span>;
}

export default function Index({ inscripciones, filtros, resumen }) {
    return (
        <>
            <Head title="Inscripciones" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Inscripciones</h1>
                    <p className="apoyo text-fog">
                        {resumen.total} en total · {resumen.activas} al día · {resumen.pausadas} pausadas ·{' '}
                        {resumen.vencidas} vencidas
                    </p>
                </div>

                <Link
                    href="/panel/inscripciones/crear"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nueva inscripción
                </Link>
            </header>

            <div className="mb-3 flex flex-wrap gap-2">
                <Buscador
                    ruta="/panel/inscripciones"
                    valor={filtros.buscar}
                    etiqueta="Buscar por socio o RUT"
                />
            </div>

            <Tabla
                columnas={COLUMNAS}
                vacia={inscripciones.data.length === 0}
                mensajeVacio={
                    filtros.buscar
                        ? `Ninguna inscripción coincide con «${filtros.buscar}».`
                        : 'Todavía no hay inscripciones.'
                }
            >
                {inscripciones.data.map((inscripcion) => (
                    <Fila key={inscripcion.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link
                                href={`/panel/inscripciones/${inscripcion.uuid}`}
                                className="hover:underline"
                            >
                                {inscripcion.socio}
                            </Link>
                        </Celda>
                        <Celda className="tabular-nums">{inscripcion.rut ?? '—'}</Celda>
                        <Celda>{inscripcion.membresia ?? '—'}</Celda>
                        <Celda>
                            <Estado codigo={inscripcion.id_estado} />
                        </Celda>
                        <Celda className="tabular-nums">{inscripcion.inicio ?? '—'}</Celda>
                        <Celda className="tabular-nums">{inscripcion.vence ?? '—'}</Celda>
                        <Celda>
                            <Restantes dias={inscripcion.dias_restantes} />
                        </Celda>
                        <Cifra>{pesos.format(inscripcion.precio_final)}</Cifra>
                    </Fila>
                ))}
            </Tabla>

            <Paginacion paginador={inscripciones} />
        </>
    );
}
