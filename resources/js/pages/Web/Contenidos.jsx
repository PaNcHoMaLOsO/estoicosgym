import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { ChevronDownIcon, ChevronUpIcon, PencilIcon, PlusIcon, TrashIcon, WandSparklesIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo from '@/components/FormularioCatalogo';
import { Celda, Fila, Tabla } from '@/components/Tabla';

/**
 * Los contenidos de un tipo: servicios, fotos, preguntas o testimonios.
 *
 * UNA pantalla para los cuatro: cambian los campos y las columnas, no la
 * forma de trabajar —una lista, un botón de nuevo, el mismo formulario—.
 * Se ocultan, no se borran, igual que los catálogos.
 */
function camposDe(tipo, iconos) {
    // El orden NO se pregunta: lo nuevo va al final y se sube o se baja con
    // las flechas de la lista. Escribir el número a mano dejaba huecos y
    // repetidos, y entonces el orden de la web no era el que se quiso.
    const activo = { nombre: 'activo', etiqueta: 'Página web', tipo: 'si-no', textoCasilla: 'Se muestra en la web' };

    switch (tipo) {
        case 'servicio':
            return [
                { nombre: 'titulo', etiqueta: 'Servicio', requerido: true, ejemplo: 'Musculación, Spinning, Funcional…' },
                {
                    nombre: 'texto',
                    etiqueta: 'Descripción',
                    tipo: 'area',
                    requerido: true,
                    ayuda: 'Una línea: qué encuentra quien viene. Hasta 200 caracteres.',
                },
                {
                    nombre: 'icono',
                    etiqueta: 'Ícono',
                    tipo: 'opciones',
                    opciones: iconos,
                    requerido: true,
                },
                activo,
            ];
        case 'foto':
            return [
                {
                    nombre: 'imagen',
                    etiqueta: 'Foto',
                    tipo: 'imagen',
                    actual: 'imagen_url',
                    ayuda: 'JPG, PNG o WEBP, hasta 8 MB. Se achica sola y se endereza si viene girada.',
                },
                {
                    nombre: 'titulo',
                    etiqueta: 'Qué muestra',
                    requerido: true,
                    ejemplo: 'Sala de musculación con las máquinas nuevas',
                    ayuda: 'Lo lee Google y quien no puede ver la imagen.',
                },
                activo,
            ];
        case 'pregunta':
            return [
                { nombre: 'titulo', etiqueta: 'Pregunta', requerido: true, ejemplo: '¿Necesito llevar candado?' },
                { nombre: 'texto', etiqueta: 'Respuesta', tipo: 'area', requerido: true },
                activo,
            ];
        default:
            return [
                { nombre: 'titulo', etiqueta: 'Nombre', requerido: true, ejemplo: 'Camila R.' },
                { nombre: 'texto', etiqueta: 'Lo que dijo', tipo: 'area', requerido: true },
                {
                    nombre: 'con_permiso',
                    etiqueta: 'Permiso',
                    tipo: 'si-no',
                    textoCasilla: 'La persona me autorizó a publicar su opinión con su nombre',
                    requerido: true,
                },
                activo,
            ];
    }
}

function valoresDe(tipo, fila) {
    return {
        titulo: fila?.titulo ?? '',
        texto: fila?.texto ?? '',
        icono: fila?.icono ?? (tipo === 'servicio' ? 'dumbbell' : ''),
        imagen: null,
        imagen_url: fila?.imagen_url ?? null,
        con_permiso: Boolean(fila?.con_permiso),
        activo: fila?.uuid ? Boolean(fila.activo) : true,
    };
}

const COLUMNAS = {
    servicio: ['Servicio', 'Descripción', 'Ícono', 'Orden', 'En la web', ''],
    foto: ['Foto', 'Qué muestra', 'Orden', 'En la web', ''],
    pregunta: ['Pregunta', 'Respuesta', 'Orden', 'En la web', ''],
    testimonio: ['Nombre', 'Lo que dijo', 'Orden', 'En la web', ''],
};

function recortar(texto, largo = 90) {
    if (! texto) {
        return '-';
    }

    return texto.length > largo ? `${texto.slice(0, largo)}…` : texto;
}

export default function Contenidos({ tipo, datos, filas, iconos }) {
    // null = cerrado; una fila = editando esa; {} = creando.
    const [editando, setEditando] = useState(null);

    const campos = useMemo(() => camposDe(tipo, iconos), [tipo, iconos]);
    const nombreIcono = useMemo(() => Object.fromEntries(iconos.map((i) => [i.valor, i.etiqueta])), [iconos]);

    /** Un orden razonable de una vez, sin ir foto por foto. */
    function ordenarSolas() {
        const aviso =
            'Se ordenan solas: las panorámicas primero y sin dos parecidas seguidas.\n\n'
            + 'Se pierde el orden que hayas puesto a mano. ¿Seguir?';

        if (window.confirm(aviso)) {
            router.post(`/panel/web/${tipo}/ordenar`, {}, { preserveScroll: true });
        }
    }

    /** Sube o baja un puesto: ordenar sin pensar en números. */
    function mover(fila, hacia) {
        router.post(`/panel/web/contenido/${fila.uuid}/mover`, { hacia }, { preserveScroll: true });
    }

    function alternar(fila) {
        router.patch(`/panel/catalogos/contenidos/${fila.uuid}/alternar`, {}, { preserveScroll: true });
    }

    /** Borrar no se deshace, así que se pregunta antes. */
    function eliminar(fila) {
        const que = fila.titulo || `esta ${datos.singular}`;

        if (window.confirm(`¿Eliminar ${que}? No se puede deshacer.`)) {
            router.delete(`/panel/web/contenido/${fila.uuid}`, { preserveScroll: true });
        }
    }

    return (
        <>
            <Head title={datos.titulo} />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">{datos.titulo}</h1>
                    <p className="apoyo text-fog">{datos.descripcion}</p>
                </div>

                {/* Ordenar diez fotos a flechazos es un trabajo que nadie
                    hace: esto deja un orden razonable de una vez y las flechas
                    siguen ahí para la que se quiera arriba. */}
                {tipo === 'foto' && filas.length > 2 ? (
                    <button
                        type="button"
                        onClick={ordenarSolas}
                        className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:border-line-strong"
                    >
                        <WandSparklesIcon className="size-4" aria-hidden="true" />
                        Ordenarlas solas
                    </button>
                ) : null}

                <button
                    type="button"
                    onClick={() => setEditando({})}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    {datos.singular === 'servicio' || datos.singular === 'testimonio' ? 'Nuevo' : 'Nueva'} {datos.singular}
                </button>
            </header>

            <Tabla columnas={COLUMNAS[tipo]} vacia={filas.length === 0} mensajeVacio="Todavía no hay nada aquí.">
                {filas.map((fila, indice) => (
                    <Fila key={fila.uuid}>
                        {tipo === 'foto' ? (
                            <Celda>
                                {fila.imagen_url ? (
                                    <img
                                        src={fila.imagen_url}
                                        alt=""
                                        className="h-12 w-20 rounded-control border border-line object-cover"
                                    />
                                ) : (
                                    '-'
                                )}
                            </Celda>
                        ) : (
                            <Celda className="font-medium text-chalk">{fila.titulo}</Celda>
                        )}

                        <Celda className="max-w-md">{tipo === 'foto' ? fila.titulo : recortar(fila.texto)}</Celda>

                        {tipo === 'servicio' ? <Celda>{nombreIcono[fila.icono] ?? fila.icono}</Celda> : null}

                        <Celda className="whitespace-nowrap">
                            {/* El puesto con sus flechas: el número se lleva
                                solo y aquí solo se dice qué va antes. */}
                            <span className="inline-flex items-center gap-1">
                                <span className="w-5 tabular-nums text-fog">{indice + 1}</span>
                                <button
                                    type="button"
                                    onClick={() => mover(fila, 'arriba')}
                                    disabled={indice === 0}
                                    aria-label="Subir un puesto"
                                    className="rounded-control p-0.5 text-fog transition-colors hover:text-chalk disabled:opacity-25"
                                >
                                    <ChevronUpIcon className="size-4" aria-hidden="true" />
                                </button>
                                <button
                                    type="button"
                                    onClick={() => mover(fila, 'abajo')}
                                    disabled={indice === filas.length - 1}
                                    aria-label="Bajar un puesto"
                                    className="rounded-control p-0.5 text-fog transition-colors hover:text-chalk disabled:opacity-25"
                                >
                                    <ChevronDownIcon className="size-4" aria-hidden="true" />
                                </button>
                            </span>
                        </Celda>
                        <Celda>
                            <Activo valor={fila.activo} />
                        </Celda>
                        <Celda className="text-right">
                            <div className="inline-flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setEditando(fila)}
                                    aria-label={`Editar ${fila.titulo}`}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    <PencilIcon className="size-4" aria-hidden="true" />
                                </button>
                                <button
                                    type="button"
                                    onClick={() => alternar(fila)}
                                    className="apoyo text-fog transition-colors hover:text-chalk"
                                >
                                    {fila.activo ? 'Ocultar' : 'Mostrar'}
                                </button>
                                {/* Borrar del todo: ocultar solo lo saca de la
                                    web, y una lista que nunca se limpia acaba
                                    siendo imposible de ordenar. */}
                                <button
                                    type="button"
                                    onClick={() => eliminar(fila)}
                                    aria-label={`Eliminar ${fila.titulo}`}
                                    className="text-fog transition-colors hover:text-danger"
                                >
                                    <TrashIcon className="size-4" aria-hidden="true" />
                                </button>
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <FormularioCatalogo
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                titulo={editando?.uuid ? `Editar ${datos.singular}` : `Nuevo: ${datos.singular}`}
                descripcion={
                    tipo === 'testimonio'
                        ? 'Solo opiniones reales y con permiso: una inventada es publicidad engañosa.'
                        : undefined
                }
                accion={editando?.uuid ? `/panel/web/contenido/${editando.uuid}` : `/panel/web/${tipo}`}
                metodo={editando?.uuid ? 'put' : 'post'}
                campos={campos}
                valores={valoresDe(tipo, editando)}
            />
        </>
    );
}
