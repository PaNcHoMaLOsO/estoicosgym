import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { PencilIcon, PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo, { camposDePersona, valoresDeEspecialista } from '@/components/FormularioCatalogo';
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
                {especialistas.map((especialista) => (
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
                titulo={editando?.uuid ? `Editar a ${editando.nombre}` : textos.nuevo}
                descripcion="Solo con su permiso: su foto y su teléfono quedan a la vista de cualquiera."
                accion={editando?.uuid ? `/panel/especialistas/${editando.uuid}` : '/panel/especialistas'}
                metodo={editando?.uuid ? 'put' : 'post'}
                campos={camposDePersona(tipo)}
                valores={valoresDeEspecialista(editando, tipo)}
            />
        </>
    );
}
