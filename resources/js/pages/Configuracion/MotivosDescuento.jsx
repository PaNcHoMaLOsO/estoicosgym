import { Head } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';

import Externo from '@/components/Externo';
import Activo from '@/components/Activo';
import { Celda, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Motivo', 'Descripción', 'Estado'];

export default function MotivosDescuento({ motivos }) {
    return (
        <>
            <Head title="Motivos de descuento" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Motivos de descuento</h1>
                    <p className="apoyo text-fog">Por qué se rebaja el precio de una inscripción</p>
                </div>

                <Externo
                    href="/admin/motivos-descuento/create"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nuevo motivo
                </Externo>
            </header>

            <Tabla
                columnas={COLUMNAS}
                vacia={motivos.length === 0}
                mensajeVacio="Todavía no hay motivos de descuento configurados."
            >
                {motivos.map((motivo) => (
                    <Fila key={motivo.id}>
                        <Celda className="font-medium text-chalk">
                            <Externo
                                href={`/admin/motivos-descuento/${motivo.id}`}
                                className="hover:underline"
                            >
                                {motivo.nombre}
                            </Externo>
                        </Celda>
                        <Celda>{motivo.descripcion ?? '—'}</Celda>
                        <Celda>
                            <Activo valor={motivo.activo} />
                        </Celda>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
