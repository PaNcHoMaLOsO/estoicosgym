import { confirmar } from '@/components/Confirmar';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { PencilIcon, PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo from '@/components/FormularioCatalogo';
import { Celda, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Motivo', 'Descripción', 'Estado', ''];

const CAMPOS = [
    {
        nombre: 'nombre',
        etiqueta: 'Motivo',
        requerido: true,
        ejemplo: 'Estudiante, Hermano de socio…',
    },
    {
        nombre: 'descripcion',
        etiqueta: 'Descripción',
        tipo: 'area',
        ayuda: 'Opcional. Cuándo corresponde aplicarlo.',
    },
    {
        nombre: 'activo',
        etiqueta: 'Disponibilidad',
        tipo: 'si-no',
        textoCasilla: 'Se puede elegir al inscribir',
    },
];

const NUEVO = { nombre: '', descripcion: '', activo: true };

export default function MotivosDescuento({ motivos }) {
    // null = cerrado; una fila = editando esa; NUEVO = creando.
    const [editando, setEditando] = useState(null);

    // No hay «eliminar»: las inscripciones que llevan este motivo lo apuntan, y
    // borrarlo dejaria sin explicar por que pagaron menos. Se desactiva.
    async function alternar(motivo) {
        if (motivo.activo && ! (await confirmar({ titulo: `¿Apagar «${motivo.nombre}»?`, mensaje: 'No se ofrece al inscribir. Los descuentos ya dados no cambian.', confirmar: 'Apagar' }))) {
            return;
        }

        router.patch(`/panel/catalogos/motivos-descuento/${motivo.id}/alternar`, {}, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Motivos de descuento" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Motivos de descuento</h1>
                    <p className="apoyo text-fog">Por qué se rebaja el precio de una inscripción</p>
                </div>

                <button
                    type="button"
                    onClick={() => setEditando(NUEVO)}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nuevo motivo
                </button>
            </header>

            <Tabla
                columnas={COLUMNAS}
                vacia={motivos.length === 0}
                mensajeVacio="Todavía no hay motivos de descuento configurados."
            >
                {motivos.map((motivo) => (
                    <Fila key={motivo.id}>
                        <Celda className="font-medium text-chalk">{motivo.nombre}</Celda>
                        <Celda>{motivo.descripcion ?? '-'}</Celda>
                        <Celda>
                            <Activo valor={motivo.activo} />
                        </Celda>
                        <Celda className="text-right">
                            <div className="inline-flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setEditando(motivo)}
                                    aria-label={`Editar ${motivo.nombre}`}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    <PencilIcon className="size-4" aria-hidden="true" />
                                </button>

                                <button
                                    type="button"
                                    onClick={() => alternar(motivo)}
                                    className="apoyo text-fog transition-colors hover:text-chalk"
                                >
                                    {motivo.activo ? 'Desactivar' : 'Activar'}
                                </button>
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <FormularioCatalogo
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                titulo={editando?.id ? 'Editar motivo' : 'Nuevo motivo de descuento'}
                descripcion={
                    editando?.id
                        ? 'Las inscripciones que ya lo llevan no cambian.'
                        : 'Aparecerá al aplicar un descuento en una inscripción.'
                }
                accion={
                    editando?.id ? `/panel/motivos-descuento/${editando.id}` : '/panel/motivos-descuento'
                }
                metodo={editando?.id ? 'put' : 'post'}
                campos={CAMPOS}
                valores={{
                    nombre: editando?.nombre ?? '',
                    descripcion: editando?.descripcion ?? '',
                    activo: editando?.id ? Boolean(editando.activo) : true,
                }}
            />
        </>
    );
}
