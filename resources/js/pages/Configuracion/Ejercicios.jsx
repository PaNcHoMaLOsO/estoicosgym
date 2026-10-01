import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeftIcon, PencilIcon, PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import { confirmar } from '@/components/Confirmar';
import FormularioCatalogo from '@/components/FormularioCatalogo';

/**
 * El catálogo de ejercicios de la sala: cada máquina o movimiento una vez,
 * con la indicación corta que se lee frente a la máquina. Corregir una
 * indicación aquí la corrige en todas las rutinas que lo usan.
 */
export default function Ejercicios({ ejercicios, zonas, musculos = {}, equipos }) {
    // null = cerrado; {} = nuevo; un ejercicio = editándolo.
    const [editando, setEditando] = useState(null);

    const campos = [
        { nombre: 'nombre', etiqueta: 'Nombre', requerido: true, ejemplo: 'Prensa de piernas' },
        { nombre: 'zona', etiqueta: 'Qué trabaja', tipo: 'opciones', opciones: Object.entries(zonas).map(([valor, etiqueta]) => ({ valor, etiqueta })), requerido: true },
        { nombre: 'equipo', etiqueta: 'Con qué', tipo: 'opciones', opciones: Object.entries(equipos).map(([valor, etiqueta]) => ({ valor, etiqueta })), requerido: true },
        { nombre: 'indicacion', etiqueta: 'Cómo se hace', tipo: 'area', ayuda: 'Dos líneas: lo lee alguien parado frente a la máquina.' },
        {
            nombre: 'imagen',
            etiqueta: 'Foto o GIF',
            tipo: 'imagen',
            actual: 'imagen_url',
            quitar: 'quitar_imagen',
            acepta: 'image/jpeg,image/png,image/webp,image/gif',
            ayuda: 'JPG, PNG, WEBP o GIF corto, hasta 4 MB. Sin foto, se ve el mapa muscular.',
        },
        {
            nombre: 'musculo_principal',
            etiqueta: 'Músculo principal',
            tipo: 'opciones',
            opciones: [{ valor: '', etiqueta: 'Sin marcar' }, ...Object.entries(musculos).map(([valor, etiqueta]) => ({ valor, etiqueta }))],
        },
        { nombre: 'musculos_secundarios', etiqueta: 'También trabaja', tipo: 'varias', opciones: Object.entries(musculos).map(([valor, etiqueta]) => ({ valor, etiqueta })) },
        { nombre: 'activo', etiqueta: 'Rutinas', tipo: 'si-no', textoCasilla: 'Se puede elegir al armar rutinas' },
    ];

    const valores = {
        nombre: editando?.nombre ?? '',
        zona: editando?.zona ?? 'pecho',
        equipo: editando?.equipo ?? 'maquina',
        imagen: null,
        imagen_url: editando?.imagen_url ?? null,
        quitar_imagen: false,
        musculo_principal: editando?.musculo_principal ?? '',
        musculos_secundarios: editando?.musculos_secundarios ?? [],
        indicacion: editando?.indicacion ?? '',
        activo: editando?.uuid ? Boolean(editando.activo) : true,
    };

    async function alternar(e) {
        if (e.activo && e.usos > 0 && ! (await confirmar({
            titulo: `¿Apagar «${e.nombre}»?`,
            mensaje: `Deja de ofrecerse al armar rutinas. Las ${e.usos} líneas de rutina que ya lo usan siguen igual.`,
            confirmar: 'Apagar',
        }))) {
            return;
        }

        router.patch(`/panel/ejercicios/${e.uuid}/alternar`, {}, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Ejercicios" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <Link href="/panel/rutinas" className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk">
                        <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                        Rutinas de la sala
                    </Link>
                    <h1 className="mt-1 text-lg font-semibold text-chalk">Ejercicios</h1>
                    <p className="apoyo text-fog">Lo que hay en la sala, con cómo se hace. Lo usan todas las rutinas.</p>
                </div>

                <button
                    type="button"
                    onClick={() => setEditando({})}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Agregar ejercicio
                </button>
            </header>

            <div className="space-y-6">
                {Object.entries(zonas).map(([zona, nombreZona]) => {
                    const deLaZona = ejercicios.filter((e) => e.zona === zona);

                    return deLaZona.length ? (
                        <section key={zona}>
                            <h2 className="rotulo mb-2">
                                {nombreZona} ({deLaZona.length})
                            </h2>
                            <ul className="divide-y divide-line overflow-hidden rounded-panel border border-line bg-surface">
                                {deLaZona.map((e) => (
                                    <li key={e.uuid} className="flex items-start gap-4 px-4 py-3">
                                        <div className="min-w-0 flex-1">
                                            <p className={`text-sm font-medium ${e.activo ? 'text-chalk' : 'text-fog'}`}>
                                                {e.nombre}
                                                <span className="apoyo ml-2 font-normal text-fog">{equipos[e.equipo]}</span>
                                                {e.musculo_principal ? <span className="apoyo ml-2 font-normal text-fog">· {musculos[e.musculo_principal]}</span> : null}
                                                {e.imagen_url ? <span className="apoyo ml-2 font-normal text-fog">· con foto</span> : null}
                                            </p>
                                            {e.indicacion ? <p className="apoyo mt-0.5 text-fog">{e.indicacion}</p> : null}
                                            <p className="apoyo mt-0.5 text-fog">
                                                {e.usos === 0 ? 'No lo usa ninguna rutina' : `En ${e.usos} ${e.usos === 1 ? 'línea' : 'líneas'} de rutina`}
                                            </p>
                                        </div>
                                        <button type="button" onClick={() => alternar(e)} title={e.activo ? 'Clic para apagarlo' : 'Clic para prenderlo'}>
                                            <Activo valor={e.activo} />
                                        </button>
                                        <button type="button" onClick={() => setEditando(e)} aria-label={`Editar ${e.nombre}`} className="text-fog hover:text-chalk">
                                            <PencilIcon className="size-4" aria-hidden="true" />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    ) : null;
                })}
            </div>

            <FormularioCatalogo
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                titulo={editando?.uuid ? `Editar «${editando.nombre}»` : 'Agregar ejercicio'}
                accion={editando?.uuid ? `/panel/ejercicios/${editando.uuid}` : '/panel/ejercicios'}
                metodo={editando?.uuid ? 'put' : 'post'}
                campos={campos}
                valores={valores}
            />
        </>
    );
}
