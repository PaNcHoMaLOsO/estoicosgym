import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { PencilIcon, PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo, { CAMPOS_PLAN, valoresDePlan } from '@/components/FormularioCatalogo';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Membresía', 'Duración', 'Precio', 'Pausas', 'Inscripciones', 'Estado', ''];

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

const NUEVA = {
    nombre: '',
    descripcion: '',
    duracion_meses: 1,
    duracion_dias: 0,
    max_pausas: 1,
    precio: '',
    precio_convenio: '',
    activo: true,
};

export default function Membresias({ membresias }) {
    // null = cerrado; una fila = editando esa; NUEVA = creando.
    const [editando, setEditando] = useState(null);

    // Desactivar, no borrar: las inscripciones vendidas apuntan al plan y
    // borrarlo las dejaria sin decir que se vendio.
    function alternar(membresia) {
        router.patch(`/panel/catalogos/membresias/${membresia.uuid}/alternar`, {}, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Membresías" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Membresías</h1>
                    <p className="apoyo text-fog">Los planes que se pueden vender</p>
                </div>

                <button
                    type="button"
                    onClick={() => setEditando(NUEVA)}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nueva membresía
                </button>
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
                                href={`/panel/membresias/${membresia.uuid}`}
                                className="hover:underline"
                            >
                                {membresia.nombre}
                            </Link>
                            {/* Se vende en el mesón pero no se anuncia: dicho aquí,
                                nadie lo busca en la web creyendo que se perdió. */}
                            {membresia.en_la_web === false ? (
                                <span className="apoyo ml-2 font-normal text-fog">· no sale en la web</span>
                            ) : null}
                            {membresia.descripcion ? (
                                <span className="apoyo block text-fog">{membresia.descripcion}</span>
                            ) : null}
                        </Celda>
                        <Celda>{membresia.duracion}</Celda>
                        <Cifra className="text-chalk">
                            {membresia.precio > 0 ? pesos.format(membresia.precio) : (
                                // Sin precio no se puede vender: el alta lo rechaza.
                                <span className="text-warn">Sin precio</span>
                            )}
                        </Cifra>
                        <Cifra>{membresia.max_pausas}</Cifra>
                        <Cifra>{membresia.inscripciones}</Cifra>
                        <Celda>
                            <Activo valor={membresia.activo} />
                        </Celda>
                        <Celda className="text-right">
                            <div className="inline-flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setEditando(membresia)}
                                    aria-label={`Editar ${membresia.nombre}`}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    <PencilIcon className="size-4" aria-hidden="true" />
                                </button>

                                <button
                                    type="button"
                                    onClick={() => alternar(membresia)}
                                    className="apoyo text-fog transition-colors hover:text-chalk"
                                >
                                    {membresia.activo ? 'Desactivar' : 'Activar'}
                                </button>
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <FormularioCatalogo
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                titulo={editando?.uuid ? 'Editar plan' : 'Nuevo plan'}
                descripcion={
                    editando?.uuid
                        ? 'Las inscripciones ya vendidas conservan su precio.'
                        : 'Aparecerá como opción al inscribir.'
                }
                accion={editando?.uuid ? `/panel/membresias/${editando.uuid}` : '/panel/membresias'}
                metodo={editando?.uuid ? 'put' : 'post'}
                campos={CAMPOS_PLAN}
                valores={valoresDePlan(editando)}
            />
        </>
    );
}
