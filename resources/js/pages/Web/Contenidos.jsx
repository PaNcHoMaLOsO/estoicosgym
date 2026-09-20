import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { PencilIcon, PlusIcon } from 'lucide-react';

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
    const orden = {
        nombre: 'orden',
        etiqueta: 'Orden',
        tipo: 'number',
        min: 0,
        ayuda: 'Los de número más bajo salen primero.',
    };
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
                orden,
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
                orden,
                activo,
            ];
        case 'pregunta':
            return [
                { nombre: 'titulo', etiqueta: 'Pregunta', requerido: true, ejemplo: '¿Necesito llevar candado?' },
                { nombre: 'texto', etiqueta: 'Respuesta', tipo: 'area', requerido: true },
                orden,
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
                orden,
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
        orden: fila?.orden ?? 0,
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

    function alternar(fila) {
        router.patch(`/panel/catalogos/contenidos/${fila.uuid}/alternar`, {}, { preserveScroll: true });
    }

    return (
        <>
            <Head title={datos.titulo} />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">{datos.titulo}</h1>
                    <p className="apoyo text-fog">{datos.descripcion}</p>
                </div>

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
                {filas.map((fila) => (
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

                        <Celda className="tabular-nums">{fila.orden}</Celda>
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
