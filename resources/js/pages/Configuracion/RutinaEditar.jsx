import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, ChevronDownIcon, ChevronUpIcon, CopyIcon, ExternalLinkIcon, PlusIcon, TrashIcon, XIcon } from 'lucide-react';

import { Area, Campo, Texto } from '@/components/Campo';
import { Botones } from '@/components/Cobro';
import { confirmar } from '@/components/Confirmar';
import useAvisoAlSalir from '@/lib/useAvisoAlSalir';

import TextoQueCambia from '@/components/TextoQueCambia';
/**
 * Una rutina de la sala, entera en una pantalla: de qué es, sus días y los
 * ejercicios de cada día con series, repeticiones, descanso y la variante
 * «si está ocupada».
 *
 * Los días de la semana son los días que tiene: agregar un día la vuelve de
 * cuatro a cinco. Todo se guarda junto con «Guardar».
 */
const DESCANSOS = [0, 30, 45, 60, 75, 90, 120, 150, 180];

const lineaNueva = () => ({ id_ejercicio: '', id_alternativa: '', series: 3, repeticiones: '10 a 12', descanso_seg: 60, nota: '' });
const diaNuevo = (n) => ({ titulo: `Día ${n}`, foco: '', ejercicios: [lineaNueva()] });

const campo =
    'w-full rounded-control border border-line bg-surface px-2 py-1.5 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none';

/** El desplegable de ejercicios, agrupados por zona. */
function ElegirEjercicio({ valor, alCambiar, ejercicios, zonas, vacio, etiqueta }) {
    return (
        <select value={valor ?? ''} onChange={(e) => alCambiar(e.target.value ? Number(e.target.value) : '')} aria-label={etiqueta} className={campo}>
            <option value="">{vacio}</option>
            {Object.entries(zonas).map(([zona, nombre]) => {
                const deLaZona = ejercicios.filter((e) => e.zona === zona);

                return deLaZona.length ? (
                    <optgroup key={zona} label={nombre}>
                        {deLaZona.map((e) => (
                            <option key={e.id} value={e.id}>
                                {e.nombre}
                            </option>
                        ))}
                    </optgroup>
                ) : null;
            })}
        </select>
    );
}

function BotonIcono({ onClick, etiqueta, children, peligro = false, disabled = false }) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            aria-label={etiqueta}
            title={etiqueta}
            className={`rounded-control p-1 text-fog transition-colors disabled:opacity-25 ${peligro ? 'hover:text-danger' : 'hover:text-chalk'}`}
        >
            {children}
        </button>
    );
}

export default function RutinaEditar({ rutina, objetivos, niveles, zonas, ejercicios }) {
    const nueva = ! rutina;
    const { data, setData, post, put, processing, errors, isDirty } = useForm({
        nombre: rutina?.nombre ?? '',
        objetivo: rutina?.objetivo ?? 'empezar',
        nivel: rutina?.nivel ?? 'nunca',
        descripcion: rutina?.descripcion ?? '',
        activa: rutina?.activa ?? true,
        dias: rutina?.dias?.length ? rutina.dias : [diaNuevo(1)],
    });

    const tocar = useAvisoAlSalir(isDirty && ! processing);

    // Cambiar los días sin mutar: una copia, el cambio, y se guarda.
    const cambiarDias = (fn) => setData('dias', fn(data.dias.map((d) => ({ ...d, ejercicios: d.ejercicios.map((l) => ({ ...l })) }))));
    const mover = (lista, i, hacia) => {
        const j = i + hacia;
        if (j < 0 || j >= lista.length) return lista;
        [lista[i], lista[j]] = [lista[j], lista[i]];
        return lista;
    };

    function enviar(e) {
        e.preventDefault();

        if (nueva) {
            post('/panel/rutinas');
        } else {
            put(`/panel/rutinas/${rutina.uuid}`, { preserveScroll: true });
        }
    }

    async function eliminar() {
        if (await confirmar({ titulo: `¿Eliminar «${rutina.nombre}»?`, mensaje: 'Se borran sus días y ejercicios. No se puede deshacer.', confirmar: 'Eliminar', peligrosa: true })) {
            router.delete(`/panel/rutinas/${rutina.uuid}`);
        }
    }

    // Los errores de un día, juntos arriba de ese día: «dias.1.ejercicios.2.repeticiones».
    const erroresDelDia = (i) => Object.entries(errors).filter(([k]) => k.startsWith(`dias.${i}.`)).map(([, v]) => v);

    return (
        <>
            <Head title={nueva ? 'Nueva rutina' : rutina.nombre} />

            <header className="mb-5">
                <Link href="/panel/rutinas" className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk">
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Rutinas de la sala
                </Link>
                <div className="mt-1 flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-xl font-semibold text-chalk">{nueva ? 'Nueva rutina' : rutina.nombre}</h1>
                    {nueva ? null : (
                        <div className="flex items-center gap-3 text-sm">
                            <a href={rutina.ver} target="_blank" rel="noopener" className="inline-flex items-center gap-1.5 text-fog hover:text-chalk">
                                <ExternalLinkIcon className="size-4" aria-hidden="true" /> Ver en la web
                            </a>
                            <button type="button" onClick={() => router.post(`/panel/rutinas/${rutina.uuid}/duplicar`)} className="inline-flex items-center gap-1.5 text-fog hover:text-chalk">
                                <CopyIcon className="size-4" aria-hidden="true" /> Duplicar
                            </button>
                            <button type="button" onClick={eliminar} className="inline-flex items-center gap-1.5 text-fog hover:text-danger">
                                <TrashIcon className="size-4" aria-hidden="true" /> Eliminar
                            </button>
                        </div>
                    )}
                </div>
            </header>

            <form onSubmit={enviar} {...tocar} className="max-w-5xl space-y-5">
                <section className="rounded-panel border border-line bg-surface p-4">
                    <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div className="space-y-3">
                            <Campo etiqueta="Nombre" nombre="nombre" error={errors.nombre} requerido>
                                <Texto nombre="nombre" valor={data.nombre} alCambiar={(v) => setData('nombre', v)} error={errors.nombre} maxLength={80} placeholder="Torso y pierna · 4 días" />
                            </Campo>
                            <Campo etiqueta="Descripción" nombre="descripcion" error={errors.descripcion} ayuda="Una o dos frases: para quién es y cómo se hace.">
                                <Area nombre="descripcion" valor={data.descripcion} alCambiar={(v) => setData('descripcion', v)} filas={3} maxLength={300} />
                            </Campo>
                            <label className="flex items-center gap-2 text-sm text-chalk">
                                <input type="checkbox" checked={data.activa} onChange={(e) => setData('activa', e.target.checked)} className="size-4 accent-[var(--color-volt)]" />
                                Sale en la web
                            </label>
                        </div>
                        <div className="space-y-3">
                            <Campo etiqueta="Para quién busca" nombre="objetivo" error={errors.objetivo} requerido>
                                <Botones
                                    opciones={Object.entries(objetivos).map(([valor, etiqueta]) => ({ valor, etiqueta }))}
                                    valor={data.objetivo}
                                    alElegir={(v) => setData('objetivo', v)}
                                    nombre="Objetivo"
                                    columnas="grid-cols-2"
                                    compacto
                                />
                            </Campo>
                            <Campo etiqueta="Nivel" nombre="nivel" error={errors.nivel} requerido>
                                <Botones
                                    opciones={Object.entries(niveles).map(([valor, etiqueta]) => ({ valor, etiqueta }))}
                                    valor={data.nivel}
                                    alElegir={(v) => setData('nivel', v)}
                                    nombre="Nivel"
                                    columnas="grid-cols-1 sm:grid-cols-3"
                                    compacto
                                />
                            </Campo>
                            <p className="apoyo text-fog">
                                {data.dias.length} {data.dias.length === 1 ? 'día' : 'días'} por semana: los días que tiene abajo.
                            </p>
                        </div>
                    </div>
                </section>

                {errors.dias ? <p className="text-sm text-danger">{errors.dias}</p> : null}

                {data.dias.map((dia, i) => (
                    <section key={i} className="rounded-panel border border-line bg-surface">
                        <header className="flex flex-wrap items-center gap-3 border-b border-line px-4 py-3">
                            <span className="rotulo shrink-0">Día {i + 1}</span>
                            <input
                                value={dia.titulo}
                                onChange={(e) => cambiarDias((d) => ((d[i].titulo = e.target.value), d))}
                                placeholder="Tren superior"
                                maxLength={60}
                                aria-label={`Título del día ${i + 1}`}
                                className={`${campo} min-w-40 flex-1 font-medium`}
                            />
                            <input
                                value={dia.foco}
                                onChange={(e) => cambiarDias((d) => ((d[i].foco = e.target.value), d))}
                                placeholder="Pecho, espalda y hombros (opcional)"
                                maxLength={120}
                                aria-label={`De qué va el día ${i + 1}`}
                                className={`${campo} min-w-48 flex-[2]`}
                            />
                            <span className="flex items-center">
                                <BotonIcono etiqueta="Subir el día" disabled={i === 0} onClick={() => cambiarDias((d) => mover(d, i, -1))}>
                                    <ChevronUpIcon className="size-4" />
                                </BotonIcono>
                                <BotonIcono etiqueta="Bajar el día" disabled={i === data.dias.length - 1} onClick={() => cambiarDias((d) => mover(d, i, 1))}>
                                    <ChevronDownIcon className="size-4" />
                                </BotonIcono>
                                <BotonIcono
                                    etiqueta="Quitar el día"
                                    peligro
                                    disabled={data.dias.length === 1}
                                    onClick={async () => {
                                        if (await confirmar({ titulo: `¿Quitar el día ${i + 1}?`, mensaje: 'Se quitan sus ejercicios al guardar.', confirmar: 'Quitar', peligrosa: true })) {
                                            cambiarDias((d) => d.filter((_, j) => j !== i));
                                        }
                                    }}
                                >
                                    <TrashIcon className="size-4" />
                                </BotonIcono>
                            </span>
                        </header>

                        {erroresDelDia(i).length ? (
                            <ul className="apoyo space-y-0.5 border-b border-line px-4 py-2 text-danger" role="alert">
                                {[...new Set(erroresDelDia(i))].map((e) => (
                                    <li key={e}>{e}</li>
                                ))}
                            </ul>
                        ) : null}

                        {/* Encabezados solo en pantalla ancha; en el celular cada fila se apila. */}
                        <div className="hidden gap-2 px-4 pt-3 text-xs text-fog lg:grid lg:grid-cols-[minmax(0,2.2fr)_4rem_minmax(0,1.1fr)_5.5rem_minmax(0,2fr)_4.5rem]">
                            <span>Ejercicio</span>
                            <span>Series</span>
                            <span>Repeticiones</span>
                            <span>Descanso</span>
                            <span>Si está ocupada</span>
                            <span />
                        </div>

                        <ul className="divide-y divide-line">
                            {dia.ejercicios.map((l, j) => (
                                <li key={j} className="px-4 py-3">
                                    <div className="grid grid-cols-1 gap-2 lg:grid-cols-[minmax(0,2.2fr)_4rem_minmax(0,1.1fr)_5.5rem_minmax(0,2fr)_4.5rem] lg:items-center">
                                        <ElegirEjercicio
                                            valor={l.id_ejercicio}
                                            alCambiar={(v) => cambiarDias((d) => ((d[i].ejercicios[j].id_ejercicio = v), d))}
                                            ejercicios={ejercicios}
                                            zonas={zonas}
                                            vacio="Elige un ejercicio…"
                                            etiqueta={`Ejercicio ${j + 1} del día ${i + 1}`}
                                        />
                                        <input
                                            type="number"
                                            min={1}
                                            max={10}
                                            value={l.series}
                                            onChange={(e) => cambiarDias((d) => ((d[i].ejercicios[j].series = e.target.value), d))}
                                            aria-label="Series"
                                            className={`${campo} tabular-nums`}
                                        />
                                        <input
                                            value={l.repeticiones}
                                            onChange={(e) => cambiarDias((d) => ((d[i].ejercicios[j].repeticiones = e.target.value), d))}
                                            placeholder="10 a 12"
                                            maxLength={40}
                                            aria-label="Repeticiones"
                                            className={campo}
                                        />
                                        <select
                                            value={l.descanso_seg}
                                            onChange={(e) => cambiarDias((d) => ((d[i].ejercicios[j].descanso_seg = Number(e.target.value)), d))}
                                            aria-label="Descanso"
                                            className={campo}
                                        >
                                            {[...new Set([...DESCANSOS, Number(l.descanso_seg)])].sort((a, b) => a - b).map((s) => (
                                                <option key={s} value={s}>
                                                    {s === 0 ? 'Sin descanso' : `${s} s`}
                                                </option>
                                            ))}
                                        </select>
                                        <ElegirEjercicio
                                            valor={l.id_alternativa}
                                            alCambiar={(v) => cambiarDias((d) => ((d[i].ejercicios[j].id_alternativa = v), d))}
                                            ejercicios={ejercicios.filter((e) => e.id !== l.id_ejercicio)}
                                            zonas={zonas}
                                            vacio="Sin variante"
                                            etiqueta="Si está ocupada"
                                        />
                                        <span className="flex items-center justify-end">
                                            <BotonIcono etiqueta="Subir" disabled={j === 0} onClick={() => cambiarDias((d) => ((d[i].ejercicios = mover(d[i].ejercicios, j, -1)), d))}>
                                                <ChevronUpIcon className="size-4" />
                                            </BotonIcono>
                                            <BotonIcono etiqueta="Bajar" disabled={j === dia.ejercicios.length - 1} onClick={() => cambiarDias((d) => ((d[i].ejercicios = mover(d[i].ejercicios, j, 1)), d))}>
                                                <ChevronDownIcon className="size-4" />
                                            </BotonIcono>
                                            <BotonIcono etiqueta="Quitar el ejercicio" peligro disabled={dia.ejercicios.length === 1} onClick={() => cambiarDias((d) => ((d[i].ejercicios = d[i].ejercicios.filter((_, k) => k !== j)), d))}>
                                                <XIcon className="size-4" />
                                            </BotonIcono>
                                        </span>
                                    </div>
                                    <input
                                        value={l.nota}
                                        onChange={(e) => cambiarDias((d) => ((d[i].ejercicios[j].nota = e.target.value), d))}
                                        placeholder="Nota para este día (opcional; si no, sale la indicación del ejercicio)"
                                        maxLength={200}
                                        aria-label="Nota"
                                        className={`${campo} mt-2 text-xs`}
                                    />
                                </li>
                            ))}
                        </ul>

                        <div className="border-t border-line px-4 py-2.5">
                            <button
                                type="button"
                                disabled={dia.ejercicios.length >= 15}
                                onClick={() => cambiarDias((d) => (d[i].ejercicios.push(lineaNueva()), d))}
                                className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk disabled:opacity-40"
                            >
                                <PlusIcon className="size-3.5" aria-hidden="true" /> Agregar ejercicio
                            </button>
                        </div>
                    </section>
                ))}

                <button
                    type="button"
                    disabled={data.dias.length >= 7}
                    onClick={() => cambiarDias((d) => [...d, diaNuevo(d.length + 1)])}
                    className="inline-flex items-center gap-1.5 rounded-control border border-dashed border-line px-4 py-2 text-sm text-fog transition-colors hover:border-line-strong hover:text-chalk disabled:opacity-40"
                >
                    <PlusIcon className="size-4" aria-hidden="true" /> Agregar un día
                </button>

                <div className="sticky bottom-0 z-10 flex flex-wrap items-center gap-3 rounded-panel border border-line bg-surface/95 px-4 py-3 backdrop-blur">
                    <button
                        type="submit"
                        disabled={processing || (! nueva && ! isDirty)}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                    >
                        <TextoQueCambia ocupado={processing} mientras="Guardando…">{nueva ? 'Crear rutina' : 'Guardar'}</TextoQueCambia>
                    </button>
                    <Link href="/panel/rutinas" className="text-sm text-fog hover:text-chalk">
                        Cancelar
                    </Link>
                    <span className="apoyo ml-auto text-fog">{isDirty ? 'Hay cambios sin guardar.' : 'Sin cambios.'}</span>
                </div>
            </form>
        </>
    );
}
