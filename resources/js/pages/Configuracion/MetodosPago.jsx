import { Head, Link } from '@inertiajs/react';
import { CheckIcon, PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Método', 'Comprobante', 'Pagos registrados', 'Estado'];

export default function MetodosPago({ metodos }) {
    return (
        <>
            <Head title="Métodos de pago" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Métodos de pago</h1>
                    <p className="apoyo text-fog">Cómo se puede pagar en el mesón</p>
                </div>

                <Link
                    href="/admin/metodos-pago/create"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nuevo método
                </Link>
            </header>

            <Tabla
                columnas={COLUMNAS}
                vacia={metodos.length === 0}
                mensajeVacio="Todavía no hay métodos de pago configurados."
            >
                {metodos.map((metodo) => (
                    <Fila key={metodo.id}>
                        <Celda className="font-medium text-chalk">
                            <Link href={`/admin/metodos-pago/${metodo.id}`} className="hover:underline">
                                {metodo.nombre}
                            </Link>
                            {metodo.descripcion ? (
                                <span className="apoyo block text-fog">{metodo.descripcion}</span>
                            ) : null}
                        </Celda>
                        <Celda>
                            {/* Glifo mas texto: el icono solo no dice cual de los dos es. */}
                            {metodo.requiere_comprobante ? (
                                <span className="inline-flex items-center gap-1 text-chalk">
                                    <CheckIcon className="size-3.5" aria-hidden="true" />
                                    Requiere
                                </span>
                            ) : (
                                <span className="text-fog">No requiere</span>
                            )}
                        </Celda>
                        <Cifra>{metodo.pagos}</Cifra>
                        <Celda>
                            <Activo valor={metodo.activo} />
                        </Celda>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
