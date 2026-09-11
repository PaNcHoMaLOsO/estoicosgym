import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { PencilIcon, PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo, {
    CAMPOS_ESPECIALISTA,
    valoresDeEspecialista,
} from '@/components/FormularioCatalogo';
import Retrato from '@/components/Retrato';
import { Celda, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Especialista', 'Especialidad', 'Contacto', 'Orden', 'En la web', ''];

/**
 * Los especialistas que aparecen en la página pública.
 *
 * Se ocultan, no se borran: igual que los otros catálogos. Quien deja de
 * trabajar con el gimnasio desaparece de la web, y si vuelve está su ficha.
 */
export default function Especialistas({ especialistas }) {
    // null = cerrado; una fila = editando esa; {} = creando.
    const [editando, setEditando] = useState(null);

    function alternar(especialista) {
        router.patch(`/panel/catalogos/especialistas/${especialista.uuid}/alternar`, {}, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Especialistas" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Especialistas</h1>
                    <p className="apoyo text-fog">
                        Los profesionales que aparecen en la web, con su WhatsApp y su Instagram
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => setEditando({})}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nuevo especialista
                </button>
            </header>

            <Tabla
                columnas={COLUMNAS}
                vacia={especialistas.length === 0}
                mensajeVacio="Todavía no hay especialistas. Agrega al personal trainer, al preparador físico o a la nutricionista."
            >
                {especialistas.map((especialista) => (
                    <Fila key={especialista.uuid}>
                        <Celda className="font-medium text-chalk">
                            <span className="flex items-center gap-2">
                                <Retrato nombre={especialista.nombre} foto={especialista.foto_url} tamano="sm" />
                                {especialista.nombre}
                            </span>
                        </Celda>
                        <Celda>{especialista.especialidad}</Celda>
                        <Celda>
                            {[especialista.whatsapp, especialista.instagram].filter(Boolean).join(' · ') || '—'}
                        </Celda>
                        <Celda className="tabular-nums">{especialista.orden}</Celda>
                        <Celda>
                            <Activo valor={especialista.activo} />
                        </Celda>
                        <Celda className="text-right">
                            <div className="inline-flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setEditando(especialista)}
                                    aria-label={`Editar ${especialista.nombre}`}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    <PencilIcon className="size-4" aria-hidden="true" />
                                </button>

                                <button
                                    type="button"
                                    onClick={() => alternar(especialista)}
                                    className="apoyo text-fog transition-colors hover:text-chalk"
                                >
                                    {especialista.activo ? 'Ocultar' : 'Mostrar'}
                                </button>
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <FormularioCatalogo
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                titulo={editando?.uuid ? 'Editar especialista' : 'Nuevo especialista'}
                descripcion="Solo con su permiso: su foto y su teléfono quedan a la vista de cualquiera."
                accion={editando?.uuid ? `/panel/especialistas/${editando.uuid}` : '/panel/especialistas'}
                metodo={editando?.uuid ? 'put' : 'post'}
                campos={CAMPOS_ESPECIALISTA}
                valores={valoresDeEspecialista(editando)}
            />
        </>
    );
}
