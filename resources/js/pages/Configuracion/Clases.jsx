import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ChevronDownIcon, ChevronUpIcon, ExternalLinkIcon, PencilIcon, PlusIcon, TrashIcon, XIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import { Area, Campo, Texto } from '@/components/Campo';
import { confirmar } from '@/components/Confirmar';
import { Celda, Fila, Tabla } from '@/components/Tabla';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import achicarFoto from '@/lib/achicarFoto';
import useAvisoAlSalir from '@/lib/useAvisoAlSalir';

/**
 * Las clases del gimnasio: judo, lucha olímpica, boxeo… Abiertas a cualquiera
 * y con mensualidad. Salen en la página «Clases» de la web, con su calendario.
 *
 * NO SON LOS TALLERES: aquellos son instituciones que arriendan horas y se
 * manejan en Talleres. Esto es lo que el gimnasio ofrece al público.
 *
 * Se trabaja como los contenidos de la web —lista, flechas para ordenar,
 * ocultar sin borrar—, pero con su propio formulario: el horario son filas
 * que se agregan y se quitan, y eso no cabe en el formulario de catálogo.
 */
const bloqueNuevo = (dia = 'lunes') => ({ dia, desde: '19:00', hasta: '20:00' });

const control =
    'w-full rounded-control border border-line bg-surface px-2 py-1.5 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none';

const miles = (valor) => (valor === '' || valor === null || valor === undefined ? '' : Number(valor).toLocaleString('es-CL'));

function valoresDe(clase) {
    return {
        nombre: clase?.nombre ?? '',
        para_quien: clase?.para_quien ?? '',
        profesor: clase?.profesor ?? '',
        descripcion: clase?.descripcion ?? '',
        precio_mensual: clase?.precio_mensual ?? '',
        color: clase?.color ?? 'rojo',
        horario: clase?.horario?.length ? clase.horario.map((b) => ({ ...b })) : [bloqueNuevo()],
        activo: clase?.uuid ? Boolean(clase.activo) : true,
        imagen: null,
    };
}

/** La foto: la que hay o la elegida, achicada en el navegador antes de subirla. */
function CampoFoto({ archivo, actual, alElegir }) {
    const [vista, setVista] = useState(null);

    // Cada createObjectURL reserva memoria: se suelta al cambiar.
    useEffect(() => {
        if (! (archivo instanceof File)) {
            setVista(null);

            return undefined;
        }

        const url = URL.createObjectURL(archivo);
        setVista(url);

        return () => URL.revokeObjectURL(url);
    }, [archivo]);

    const mostrar = vista ?? actual;

    return (
        <div className="flex items-center gap-3">
            <div className="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-control border border-line bg-surface-2">
                {mostrar ? <img src={mostrar} alt="" className="h-full w-full object-cover" /> : <span className="apoyo text-fog">Sin foto</span>}
            </div>
            <input
                id="imagen"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                onChange={async (e) => {
                    const elegido = e.target.files?.[0] ?? null;
                    alElegir(elegido ? await achicarFoto(elegido) : null);
                }}
                className="apoyo min-w-0 max-w-full text-fog file:mr-2 file:rounded-control file:border file:border-line file:bg-surface-2 file:px-2 file:py-1 file:text-chalk"
            />
        </div>
    );
}

function FormularioClase({ clase, abierto, alCerrar, dias, colores }) {
    const editando = Boolean(clase?.uuid);
    const { data, setData, post, put, processing, errors, clearErrors, transform, isDirty, reset, setDefaults } = useForm(valoresDe(clase));

    const tocar = useAvisoAlSalir(abierto && isDirty && ! processing);

    // El diálogo vive en la lista y no se desmonta: al abrirlo se rellena con
    // la clase que toque, o editar dos seguidas mostraría la primera.
    useEffect(() => {
        if (! abierto) {
            return;
        }

        const valores = valoresDe(clase);
        setDefaults(valores);
        clearErrors();
        Object.entries(valores).forEach(([clave, valor]) => setData(clave, valor));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [abierto, clase?.uuid]);

    const cambiarHorario = (fn) => setData('horario', fn(data.horario.map((b) => ({ ...b }))));

    /** Cerrar con cambios sin guardar pregunta antes, como salir de un formulario. */
    async function cerrar() {
        if (processing) {
            return;
        }

        if (isDirty && ! (await confirmar({
            titulo: '¿Cerrar sin guardar?',
            mensaje: 'Lo que llevas escrito se pierde.',
            confirmar: 'Cerrar sin guardar',
            cancelar: 'Seguir aquí',
            peligrosa: true,
        }))) {
            return;
        }

        reset();
        alCerrar();
    }

    function enviar(e) {
        e.preventDefault();

        const opciones = { preserveScroll: true, onSuccess: () => alCerrar() };

        // Con foto viaja como multipart, que PHP no lee en un PUT: se manda
        // como POST diciendo que es un PUT.
        if (editando && data.imagen instanceof File) {
            transform((d) => ({ ...d, _method: 'put' }));
            post(`/panel/clases/${clase.uuid}`, opciones);

            return;
        }

        transform((d) => d);
        (editando ? put : post)(editando ? `/panel/clases/${clase.uuid}` : '/panel/clases', opciones);
    }

    // Los errores del horario van juntos debajo de las filas: «horario.1.hasta».
    const erroresHorario = [...new Set(Object.entries(errors).filter(([k]) => k.startsWith('horario')).map(([, v]) => v))];
    const errorDeFila = (i) => Object.keys(errors).some((k) => k.startsWith(`horario.${i}.`));

    return (
        <Dialog open={abierto} onOpenChange={(v) => (! v ? cerrar() : null)}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{editando ? `Editar ${clase.nombre}` : 'Nueva clase'}</DialogTitle>
                    <DialogDescription>Sale en la página Clases, con su horario y su precio.</DialogDescription>
                </DialogHeader>

                <form onSubmit={enviar} {...tocar} className="grid gap-x-4 gap-y-3 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                        <Campo etiqueta="Nombre" nombre="nombre" error={errors.nombre} requerido>
                            <Texto nombre="nombre" valor={data.nombre} alCambiar={(v) => setData('nombre', v)} error={errors.nombre} maxLength={100} placeholder="Judo, Lucha olímpica…" />
                        </Campo>
                    </div>

                    <Campo etiqueta="Para quién" nombre="para_quien" error={errors.para_quien}>
                        <Texto nombre="para_quien" valor={data.para_quien} alCambiar={(v) => setData('para_quien', v)} error={errors.para_quien} maxLength={100} placeholder="Niños desde 8 años" />
                    </Campo>

                    <Campo etiqueta="Profesor" nombre="profesor" error={errors.profesor}>
                        <Texto nombre="profesor" valor={data.profesor} alCambiar={(v) => setData('profesor', v)} error={errors.profesor} maxLength={100} placeholder="Nombre y apellido" />
                    </Campo>

                    <div className="sm:col-span-2">
                        <Campo etiqueta="Descripción corta" nombre="descripcion" error={errors.descripcion} ayuda="Opcional. Una o dos líneas.">
                            <Area nombre="descripcion" valor={data.descripcion} alCambiar={(v) => setData('descripcion', v)} error={errors.descripcion} filas={2} maxLength={300} />
                        </Campo>
                    </div>

                    <Campo etiqueta="Precio mensual" nombre="precio_mensual" error={errors.precio_mensual} ayuda="Vacío: la web dice «Consulta el valor».">
                        <div className="flex">
                            <span className="flex items-center rounded-l-control border border-r-0 border-line bg-surface-2 px-2.5 text-sm text-fog">$</span>
                            <input
                                id="precio_mensual"
                                inputMode="numeric"
                                autoComplete="off"
                                value={miles(data.precio_mensual)}
                                onChange={(e) => {
                                    const digitos = e.target.value.replace(/\D/g, '');
                                    setData('precio_mensual', digitos === '' ? '' : Number(digitos));
                                }}
                                placeholder="25.000"
                                className="w-full min-w-0 rounded-r-control border border-line bg-surface px-2.5 py-1.5 text-sm tabular-nums text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                            />
                        </div>
                    </Campo>

                    <Campo etiqueta="Color en el calendario" nombre="color" error={errors.color} requerido>
                        <div className="flex flex-wrap gap-1.5" role="radiogroup" aria-label="Color">
                            {colores.map((c) => (
                                <button
                                    key={c.valor}
                                    type="button"
                                    role="radio"
                                    aria-checked={data.color === c.valor}
                                    aria-label={c.nombre}
                                    title={c.nombre}
                                    onClick={() => setData('color', c.valor)}
                                    style={{ background: c.hex }}
                                    className={`size-7 rounded-full border-2 transition-transform ${data.color === c.valor ? 'scale-110 border-chalk' : 'border-transparent hover:scale-105'}`}
                                />
                            ))}
                        </div>
                    </Campo>

                    {/* EL HORARIO: una fila por bloque. Dos días con la misma
                        hora son dos filas; así un día puede tener otra hora. */}
                    <div className="sm:col-span-2">
                        <Campo etiqueta="Horario" nombre="horario" requerido>
                            <ul className="space-y-2">
                                {data.horario.map((b, i) => (
                                    <li key={i} className="grid grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_auto_minmax(0,1fr)_auto] items-center gap-1.5">
                                        <select
                                            value={b.dia}
                                            onChange={(e) => cambiarHorario((h) => ((h[i].dia = e.target.value), h))}
                                            aria-label={`Día del bloque ${i + 1}`}
                                            className={control}
                                        >
                                            {dias.map((d) => (
                                                <option key={d.valor} value={d.valor}>
                                                    {d.etiqueta}
                                                </option>
                                            ))}
                                        </select>
                                        <input
                                            type="time"
                                            value={b.desde}
                                            onChange={(e) => cambiarHorario((h) => ((h[i].desde = e.target.value), h))}
                                            aria-label="Desde"
                                            className={`${control} tabular-nums ${errorDeFila(i) ? 'border-danger' : ''}`}
                                        />
                                        <span className="apoyo text-fog">a</span>
                                        <input
                                            type="time"
                                            value={b.hasta}
                                            onChange={(e) => cambiarHorario((h) => ((h[i].hasta = e.target.value), h))}
                                            aria-label="Hasta"
                                            className={`${control} tabular-nums ${errorDeFila(i) ? 'border-danger' : ''}`}
                                        />
                                        <button
                                            type="button"
                                            onClick={() => cambiarHorario((h) => h.filter((_, j) => j !== i))}
                                            disabled={data.horario.length === 1}
                                            aria-label="Quitar este bloque"
                                            title="Quitar"
                                            className="rounded-control p-1 text-fog transition-colors hover:text-danger disabled:opacity-25"
                                        >
                                            <XIcon className="size-4" aria-hidden="true" />
                                        </button>
                                    </li>
                                ))}
                            </ul>

                            {erroresHorario.length ? (
                                <ul className="apoyo space-y-0.5 text-danger" role="alert">
                                    {erroresHorario.map((e) => (
                                        <li key={e}>{e}</li>
                                    ))}
                                </ul>
                            ) : null}

                            <button
                                type="button"
                                disabled={data.horario.length >= 21}
                                // La fila nueva copia las horas de la anterior: casi
                                // siempre es la misma clase otro día.
                                onClick={() => cambiarHorario((h) => {
                                    const ultima = h[h.length - 1];

                                    return [...h, ultima ? { ...ultima } : bloqueNuevo()];
                                })}
                                className="apoyo inline-flex items-center gap-1 self-start text-fog transition-colors hover:text-chalk disabled:opacity-40"
                            >
                                <PlusIcon className="size-3.5" aria-hidden="true" /> Agregar día
                            </button>
                        </Campo>
                    </div>

                    <div className="sm:col-span-2">
                        <Campo etiqueta="Foto" nombre="imagen" error={errors.imagen} ayuda="Opcional. JPG, PNG o WEBP; se achica sola.">
                            <CampoFoto archivo={data.imagen} actual={clase?.imagen_url ?? null} alElegir={(f) => setData('imagen', f)} />
                        </Campo>
                    </div>

                    <label className="flex items-center gap-2 text-sm text-chalk sm:col-span-2">
                        <input type="checkbox" checked={data.activo} onChange={(e) => setData('activo', e.target.checked)} className="size-4 accent-[var(--color-volt)]" />
                        Sale en la web
                    </label>

                    {/* Pegados abajo: en una ventana que se desplaza, «Guardar» no se pierde. */}
                    <div className="sticky -bottom-4 -mx-4 -mb-4 flex items-center justify-end gap-3 border-t border-line bg-raise px-4 py-3 sm:col-span-2">
                        <button
                            type="button"
                            onClick={cerrar}
                            disabled={processing}
                            className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            {processing ? 'Guardando…' : 'Guardar'}
                        </button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function Clases({ clases, dias, colores, ver }) {
    // null = cerrado; {} = nueva; una clase = editando esa.
    const [editando, setEditando] = useState(null);
    const hexDe = Object.fromEntries(colores.map((c) => [c.valor, c.hex]));

    function mover(clase, hacia) {
        router.post(`/panel/clases/${clase.uuid}/mover`, { hacia }, { preserveScroll: true });
    }

    function alternar(clase) {
        router.patch(`/panel/clases/${clase.uuid}/alternar`, {}, { preserveScroll: true });
    }

    async function eliminar(clase) {
        if (await confirmar({ titulo: `¿Eliminar ${clase.nombre}?`, mensaje: 'Se borra con su foto. No se puede deshacer.', confirmar: 'Eliminar', peligrosa: true })) {
            router.delete(`/panel/clases/${clase.uuid}`, { preserveScroll: true });
        }
    }

    const precio = (valor) => (valor ? `$${Number(valor).toLocaleString('es-CL')}` : 'Sin precio');

    return (
        <>
            <Head title="Clases" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Clases</h1>
                    <p className="apoyo text-fog">Judo, lucha, boxeo… Abiertas a todos, con mensualidad. Salen en la página Clases con su calendario.</p>
                </div>

                <div className="flex items-center gap-3">
                    {clases.some((c) => c.activo) ? (
                        <a href={ver} target="_blank" rel="noopener" className="inline-flex items-center gap-1.5 text-sm text-fog transition-colors hover:text-chalk">
                            <ExternalLinkIcon className="size-4" aria-hidden="true" /> Ver en la web
                        </a>
                    ) : null}
                    <button
                        type="button"
                        onClick={() => setEditando({})}
                        className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                    >
                        <PlusIcon className="size-4" aria-hidden="true" />
                        Nueva clase
                    </button>
                </div>
            </header>

            <Tabla
                columnas={['Clase', { titulo: 'Horario', className: 'hidden md:table-cell' }, 'Precio', 'Orden', 'En la web', '']}
                vacia={clases.length === 0}
                mensajeVacio="Todavía no hay clases. Sin clases, la página no sale en la web."
            >
                {clases.map((clase, indice) => (
                    <Fila key={clase.uuid}>
                        <Celda>
                            <span className="flex items-center gap-2.5">
                                <span className="size-3 shrink-0 rounded-full" style={{ background: hexDe[clase.color] }} aria-hidden="true" />
                                <span className="min-w-0">
                                    <span className="block font-medium text-chalk">{clase.nombre}</span>
                                    {clase.para_quien ? <span className="apoyo block text-fog">{clase.para_quien}</span> : null}
                                </span>
                            </span>
                        </Celda>
                        <Celda className="hidden md:table-cell">{clase.horario_texto || '-'}</Celda>
                        <Celda className="whitespace-nowrap tabular-nums">{precio(clase.precio_mensual)}</Celda>
                        <Celda className="whitespace-nowrap">
                            <span className="inline-flex items-center gap-1">
                                <span className="w-5 tabular-nums text-fog">{indice + 1}</span>
                                <button
                                    type="button"
                                    onClick={() => mover(clase, 'arriba')}
                                    disabled={indice === 0}
                                    aria-label="Subir un puesto"
                                    className="rounded-control p-0.5 text-fog transition-colors hover:text-chalk disabled:opacity-25"
                                >
                                    <ChevronUpIcon className="size-4" aria-hidden="true" />
                                </button>
                                <button
                                    type="button"
                                    onClick={() => mover(clase, 'abajo')}
                                    disabled={indice === clases.length - 1}
                                    aria-label="Bajar un puesto"
                                    className="rounded-control p-0.5 text-fog transition-colors hover:text-chalk disabled:opacity-25"
                                >
                                    <ChevronDownIcon className="size-4" aria-hidden="true" />
                                </button>
                            </span>
                        </Celda>
                        <Celda>
                            <Activo valor={clase.activo} />
                        </Celda>
                        <Celda className="text-right">
                            <div className="inline-flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setEditando(clase)}
                                    aria-label={`Editar ${clase.nombre}`}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    <PencilIcon className="size-4" aria-hidden="true" />
                                </button>
                                <button type="button" onClick={() => alternar(clase)} className="apoyo text-fog transition-colors hover:text-chalk">
                                    {clase.activo ? 'Ocultar' : 'Mostrar'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => eliminar(clase)}
                                    aria-label={`Eliminar ${clase.nombre}`}
                                    className="text-fog transition-colors hover:text-danger"
                                >
                                    <TrashIcon className="size-4" aria-hidden="true" />
                                </button>
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <FormularioClase clase={editando} abierto={editando !== null} alCerrar={() => setEditando(null)} dias={dias} colores={colores} />
        </>
    );
}
