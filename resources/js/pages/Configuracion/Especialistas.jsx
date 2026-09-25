import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { ChevronDownIcon, ChevronUpIcon, PencilIcon, PlusIcon, TrashIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioPersona from '@/components/FormularioPersona';
import Retrato from '@/components/Retrato';
import { Celda, Fila, Tabla } from '@/components/Tabla';

/**
 * Lo que cambia entre las dos pantallas. Son la misma tabla por debajo, pero
 * no la misma cosa: el especialista es un profesional al que se le escribe y
 * el embajador, un socio que representa al gimnasio.
 */
const SEGUN_TIPO = {
    especialista: {
        titulo: 'Especialistas',
        bajada: 'Los profesionales que trabajan con el gimnasio. Salen en la página Especialistas.',
        columnas: ['Nombre', 'Especialidad', 'Contacto', 'Orden', 'En la web', ''],
        nuevo: 'Agregar especialista',
        vacio: 'Todavía no hay ningún especialista. Agrega a un nutricionista, un personal trainer o una kinesióloga.',
    },
    embajador: {
        titulo: 'Embajadores',
        bajada: 'Socios que representan al gimnasio. Salen en la portada, antes del bloque final.',
        columnas: ['Nombre', 'Disciplina', 'Instagram', 'Orden', 'En la web', ''],
        nuevo: 'Agregar embajador',
        vacio: 'Todavía no hay ningún embajador. Agrega a los socios que compiten o que representan al gimnasio.',
    },
};

/**
 * Las personas que aparecen en la página pública: los especialistas, en su
 * página, y los embajadores, en la portada.
 *
 * Se ocultan, no se borran: igual que los otros catálogos. Quien deja de
 * trabajar con el gimnasio desaparece de la web, y si vuelve está su ficha.
 */
export default function Especialistas({ especialistas, tipo = 'especialista' }) {
    const textos = SEGUN_TIPO[tipo] ?? SEGUN_TIPO.especialista;
    // null = cerrado; una fila = editando esa; {} = creando.
    const [editando, setEditando] = useState(null);

    /** Sube o baja un puesto: ordenar sin pensar en números. */
    function mover(especialista, hacia) {
        router.post(`/panel/especialistas/${especialista.uuid}/mover`, { hacia }, { preserveScroll: true });
    }

    /** Borrar no se deshace, así que se pregunta antes. */
    function eliminar(especialista) {
        if (window.confirm(`¿Eliminar a ${especialista.nombre}? Se borra también su foto y no se puede deshacer.`)) {
            router.delete(`/panel/especialistas/${especialista.uuid}`, { preserveScroll: true });
        }
    }

    function alternar(especialista) {
        router.patch(`/panel/catalogos/especialistas/${especialista.uuid}/alternar`, {}, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title={textos.titulo} />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">{textos.titulo}</h1>
                    <p className="apoyo text-fog">{textos.bajada}</p>
                </div>

                <button
                    type="button"
                    onClick={() => setEditando({})}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    {textos.nuevo}
                </button>
            </header>

            <Tabla
                columnas={textos.columnas}
                vacia={especialistas.length === 0}
                mensajeVacio={textos.vacio}
            >
                {especialistas.map((especialista, indice) => (
                    <Fila key={especialista.uuid}>
                        <Celda className="font-medium text-chalk">
                            <span className="flex items-center gap-2">
                                <Retrato nombre={especialista.nombre} foto={especialista.foto_url} tamano="sm" ampliable />
                                {especialista.nombre}
                            </span>
                        </Celda>
                        <Celda>{especialista.especialidad}</Celda>
                        <Celda>
                            {(tipo === 'embajador'
                                ? [especialista.instagram]
                                : [especialista.whatsapp, especialista.instagram]
                            ).filter(Boolean).join(' · ') || '-'}
                        </Celda>
                        <Celda className="whitespace-nowrap">
                            {/* El puesto se lleva solo: aquí solo se dice quién va antes. */}
                            <span className="inline-flex items-center gap-1">
                                <span className="w-5 tabular-nums text-fog">{indice + 1}</span>
                                <button
                                    type="button"
                                    onClick={() => mover(especialista, 'arriba')}
                                    disabled={indice === 0}
                                    aria-label="Subir un puesto"
                                    className="rounded-control p-0.5 text-fog transition-colors hover:text-chalk disabled:opacity-25"
                                >
                                    <ChevronUpIcon className="size-4" aria-hidden="true" />
                                </button>
                                <button
                                    type="button"
                                    onClick={() => mover(especialista, 'abajo')}
                                    disabled={indice === especialistas.length - 1}
                                    aria-label="Bajar un puesto"
                                    className="rounded-control p-0.5 text-fog transition-colors hover:text-chalk disabled:opacity-25"
                                >
                                    <ChevronDownIcon className="size-4" aria-hidden="true" />
                                </button>
                            </span>
                        </Celda>
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

                                <button
                                    type="button"
                                    onClick={() => eliminar(especialista)}
                                    aria-label={`Eliminar a ${especialista.nombre}`}
                                    className="text-fog transition-colors hover:text-danger"
                                >
                                    <TrashIcon className="size-4" aria-hidden="true" />
                                </button>
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <FormularioPersona
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                tipo={tipo}
                persona={editando?.uuid ? editando : null}
                existentes={[...new Set(especialistas.map((e) => e.especialidad).filter(Boolean))]}
                otrosEnLaWeb={especialistas.filter((e) => e.activo && e.uuid !== editando?.uuid).length}
            />
        </>
    );
}
