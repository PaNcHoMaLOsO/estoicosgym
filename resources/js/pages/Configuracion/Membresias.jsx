import { Head, Link } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Membresía', 'Duración', 'Precio', 'Pausas', 'Inscripciones', 'Estado'];

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

export default function Membresias({ membresias }) {
    return (
        <>
            <Head title="Membresías" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Membresías</h1>
                    <p className="apoyo text-fog">Los planes que se pueden vender</p>
                </div>

                <Link
                    href="/admin/membresias/create"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nueva membresía
                </Link>
            </header>

            <Tabla
                columnas={COLUMNAS}
                vacia={membresias.length === 0}
                mensajeVacio="Todavía no hay membresías configuradas."
            >
                {membresias.map((membresia) => (
                    <Fila key={membresia.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link
                                href={`/admin/membresias/${membresia.uuid}`}
                                className="hover:underline"
                            >
                                {membresia.nombre}
                            </Link>
                            {membresia.descripcion ? (
                                <span className="apoyo block text-fog">{membresia.descripcion}</span>
                            ) : null}
                        </Celda>
                        <Celda>{membresia.duracion}</Celda>
                        <Cifra className="text-chalk">
                            {membresia.precio > 0 ? pesos.format(membresia.precio) : '—'}
                        </Cifra>
                        <Cifra>{membresia.max_pausas}</Cifra>
                        <Cifra>{membresia.inscripciones}</Cifra>
                        <Celda>
                            <Activo valor={membresia.activo} />
                        </Celda>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
