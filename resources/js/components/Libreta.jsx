import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { CheckIcon, PlusIcon, TrashIcon, UndoIcon } from 'lucide-react';

import { Reservado } from '@/Privado';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/**
 * El bloc de notas del meson.
 *
 * Es COMPARTIDO: en el meson se turnan varias personas y lo que deja escrito la
 * de la mañana tiene que verlo la de la tarde. Por eso se ve quien escribio cada
 * cosa —no para vigilar, sino para poder preguntarle si algo quedo a medias—.
 */
export function Notas({ notas }) {
    const { data, setData, post, processing, errors, reset } = useForm({ texto: '' });

    const pendientes = notas.filter((n) => !n.hecha);
    const hechas = notas.filter((n) => n.hecha);

    function anotar(e) {
        e.preventDefault();

        post('/panel/notas', {
            preserveScroll: true,
            onSuccess: () => reset('texto'),
        });
    }

    function tachar(nota) {
        router.patch(`/panel/notas/${nota.uuid}`, {}, { preserveScroll: true });
    }

    function quitar(nota) {
        router.delete(`/panel/notas/${nota.uuid}`, { preserveScroll: true });
    }

    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <div className="mb-3 flex items-baseline justify-between gap-3">
                <h2 className="rotulo">Notas del día</h2>
                {pendientes.length > 0 ? (
                    <span className="apoyo text-fog">
                        {pendientes.length} por hacer
                    </span>
                ) : null}
            </div>

            {/* El campo ARRIBA del todo y siempre visible: apuntar «llamar a
                Juan» no puede costar tres clics, o se acaba apuntando en un
                papel al lado del teclado. */}
            <form onSubmit={anotar} className="mb-3 flex gap-2">
                <input
                    type="text"
                    value={data.texto}
                    onChange={(e) => setData('texto', e.target.value)}
                    maxLength={280}
                    placeholder="Llamar a Juan, pedir toallas, viene el técnico el lunes…"
                    aria-label="Apuntar algo que hacer"
                    aria-invalid={errors.texto ? 'true' : undefined}
                    className={`min-w-0 flex-1 rounded-control border bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:outline-none ${
                        errors.texto ? 'border-danger' : 'border-line focus:border-line-strong'
                    }`}
                />

                <button
                    type="submit"
                    disabled={processing || data.texto.trim() === ''}
                    aria-label="Apuntar"
                    className="shrink-0 rounded-control bg-volt px-2.5 text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                </button>
            </form>

            {errors.texto ? <p className="apoyo mb-2 text-danger">{errors.texto}</p> : null}

            {notas.length === 0 ? (
                <p className="apoyo py-3 text-center text-fog">
                    Nada apuntado. Lo que escribas aquí lo ve quien entre después.
                </p>
            ) : (
                <ul className="space-y-0.5">
                    {[...pendientes, ...hechas].map((nota) => (
                        <li
                            key={nota.uuid}
                            className="group flex items-start gap-2 rounded-control px-1 py-1 hover:bg-surface-2"
                        >
                            <button
                                type="button"
                                onClick={() => tachar(nota)}
                                aria-label={nota.hecha ? 'Devolver a pendiente' : 'Dar por hecha'}
                                className={`mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-[4px] border transition-colors ${
                                    nota.hecha
                                        ? 'border-volt bg-volt text-on-volt'
                                        : 'border-line-strong hover:border-volt'
                                }`}
                            >
                                {nota.hecha ? (
                                    <CheckIcon className="size-3" aria-hidden="true" />
                                ) : null}
                            </button>

                            <div className="min-w-0 flex-1">
                                <p
                                    className={`text-sm ${
                                        nota.hecha ? 'text-fog line-through' : 'text-chalk'
                                    }`}
                                >
                                    {nota.texto}
                                </p>
                                <p className="apoyo text-fog">
                                    {nota.autor ?? 'alguien'} ·{' '}
                                    {nota.dias === 0
                                        ? nota.cuando
                                        : nota.dias === 1
                                          ? 'ayer'
                                          : `hace ${nota.dias} días`}
                                    {nota.hecha && nota.hecha_por ? ` · hecha por ${nota.hecha_por}` : ''}

                                    {/* Las notas NO se borran solas: una tarea
                                        pendiente que desaparece sola es lo peor
                                        que puede pasar. Lo que se hace es
                                        enseñarla distinta para que alguien
                                        decida —se hace, o se quita—. */}
                                    {nota.vieja ? (
                                        <span className="ml-1 text-warn">· sigue sin hacerse</span>
                                    ) : null}
                                </p>
                            </div>

                            {/* Quitar solo aparece al pasar por encima: es lo
                                unico que no se deshace y no tiene por que estar
                                pidiendo que lo pulsen. */}
                            <button
                                type="button"
                                onClick={() => quitar(nota)}
                                aria-label="Quitar del bloc"
                                className="shrink-0 rounded-control p-0.5 text-fog opacity-0 transition-opacity hover:text-danger focus:opacity-100 group-hover:opacity-100"
                            >
                                <TrashIcon className="size-3.5" aria-hidden="true" />
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}

/**
 * Lo que la gente se lleva del meson y paga despues.
 *
 * Agrupado POR PERSONA, no linea a linea: lo que se pregunta en el meson es
 * «¿cuanto debe Juan?», no «¿que se llevo el martes?». El detalle esta dentro,
 * para cuando alguien discute la cifra.
 *
 * NO es un pago de membresia y no toca la caja: es la libreta del meson.
 */
export function Fiados({ fiados }) {
    const [abierta, setAbierta] = useState(null);
    const [anotando, setAnotando] = useState(false);

    const total = fiados.reduce((t, f) => t + f.total, 0);

    function saldar(cuenta) {
        router.post('/panel/fiados/saldar', {
            id_cliente: cuenta.id_cliente,
            nombre: cuenta.nombre,
        }, { preserveScroll: true });
    }

    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <div className="mb-3 flex items-baseline justify-between gap-3">
                <div>
                    <h2 className="rotulo">Fiado en el mesón</h2>
                    <p className="apoyo mt-0.5 text-fog">
                        Lo que se llevaron y todavía no pagan ·{' '}
                        {/* Aqui va el vistazo; lo cobrado y el historial estan
                            en su pantalla, que no se mira todos los dias. */}
                        <Link
                            href="/panel/fiados"
                            className="text-fog underline transition-colors hover:text-chalk"
                        >
                            ver todo
                        </Link>
                    </p>
                </div>

                {fiados.length > 0 ? (
                    <span className="apoyo shrink-0 tabular-nums text-warn">
                        <Reservado ancho="w-14">{pesos.format(total)}</Reservado>
                    </span>
                ) : null}
            </div>

            {anotando ? (
                <ApuntarFiado alTerminar={() => setAnotando(false)} />
            ) : (
                <button
                    type="button"
                    onClick={() => setAnotando(true)}
                    className="mb-3 inline-flex items-center gap-1.5 rounded-control border border-line px-2.5 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Anotar algo fiado
                </button>
            )}

            {fiados.length === 0 ? (
                <p className="apoyo py-3 text-center text-fog">Nadie debe nada.</p>
            ) : (
                <ul className="divide-y divide-line">
                    {fiados.map((cuenta) => (
                        <li key={cuenta.clave} className="py-2">
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setAbierta(abierta === cuenta.clave ? null : cuenta.clave)
                                    }
                                    className="min-w-0 text-left"
                                >
                                    <span className="block truncate text-sm font-medium text-chalk">
                                        {cuenta.quien}
                                    </span>
                                    <span className="apoyo block text-fog">
                                        {cuenta.lineas.length}{' '}
                                        {cuenta.lineas.length === 1 ? 'cosa' : 'cosas'} · desde{' '}
                                        {cuenta.desde}
                                    </span>
                                </button>

                                <div className="flex shrink-0 items-center gap-3">
                                    <span className="font-semibold tabular-nums text-warn">
                                        <Reservado ancho="w-14">{pesos.format(cuenta.total)}</Reservado>
                                    </span>

                                    {/* Se salda la cuenta ENTERA: quien paga en
                                        el meson paga lo que debe, no la bebida
                                        del martes. */}
                                    <button
                                        type="button"
                                        onClick={() => saldar(cuenta)}
                                        className="apoyo rounded-control border border-line px-2 py-1 text-fog transition-colors hover:text-chalk"
                                    >
                                        Pagó
                                    </button>
                                </div>
                            </div>

                            {abierta === cuenta.clave ? (
                                <ul className="apoyo mt-1.5 space-y-0.5 border-l border-line pl-3 text-fog">
                                    {cuenta.lineas.map((l) => (
                                        <li key={l.uuid} className="flex justify-between gap-2">
                                            <span className="min-w-0 truncate">
                                                {l.concepto}
                                                <span className="ml-1 opacity-70">{l.cuando}</span>
                                            </span>
                                            <span className="shrink-0 tabular-nums">
                                                <Reservado ancho="w-12">{pesos.format(l.monto)}</Reservado>
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            ) : null}
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}

/**
 * El formulario de apuntar: a quien, que y cuanto.
 *
 * Se exporta porque lo usan las DOS pantallas —el resumen y la de fiados—, y
 * dos copias del mismo formulario acaban pidiendo cosas distintas.
 */
export function ApuntarFiado({ alTerminar }) {
    const [busqueda, setBusqueda] = useState('');
    const [resultados, setResultados] = useState(null);
    const [socio, setSocio] = useState(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        id_cliente: '',
        nombre: '',
        concepto: '',
        monto: '',
    });

    async function buscar(texto) {
        setBusqueda(texto);
        setData('nombre', texto);

        if (texto.trim().length < 2) {
            setResultados(null);

            return;
        }

        try {
            const r = await fetch(`/panel/fiados/buscar-socio?q=${encodeURIComponent(texto)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const j = await r.json();
            setResultados(j.clientes ?? []);
        } catch (e) {
            setResultados([]);
        }
    }

    function elegir(cliente) {
        setSocio(cliente);
        setData('id_cliente', cliente.id);
        setData('nombre', '');
        setBusqueda('');
        setResultados(null);
    }

    function enviar(e) {
        e.preventDefault();

        post('/panel/fiados', {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setSocio(null);
                setBusqueda('');
                alTerminar();
            },
        });
    }

    /*
     * El ANCHO no va aqui.
     *
     * Con `w-full` dentro, el campo del monto lo heredaba y peleaba con su
     * propio `w-24`: Tailwind resuelve ese empate por el orden en que salen las
     * dos reglas en la hoja, no por el orden en que se escriben, y ganaba
     * `w-full`. En pantalla el monto se comia la fila entera y «que se llevo»
     * quedaba en un recuadro de un centimetro.
     */
    const campo =
        'rounded-control border border-line bg-surface-2 px-2.5 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none';

    return (
        <form onSubmit={enviar} className="mb-3 space-y-2 rounded-panel border border-line bg-surface-2 p-3">
            {socio ? (
                <div className="flex items-center justify-between gap-2 text-sm">
                    <span className="min-w-0 truncate text-chalk">{socio.nombre}</span>
                    <button
                        type="button"
                        onClick={() => {
                            setSocio(null);
                            setData('id_cliente', '');
                        }}
                        className="apoyo shrink-0 text-fog hover:text-chalk"
                    >
                        Cambiar
                    </button>
                </div>
            ) : (
                <div>
                    <input
                        type="search"
                        value={busqueda}
                        onChange={(e) => buscar(e.target.value)}
                        placeholder="¿Quién? Busca al socio, o escribe un nombre"
                        aria-label="A quién se le apunta"
                        className={`${campo} w-full`}
                        autoFocus
                    />

                    {/* Se puede escribir un nombre suelto: al meson tambien se
                        acerca quien viene de visita. */}
                    {resultados && resultados.length > 0 ? (
                        <ul className="mt-1 max-h-40 divide-y divide-line overflow-y-auto rounded-control border border-line bg-surface">
                            {resultados.map((c) => (
                                <li key={c.id}>
                                    <button
                                        type="button"
                                        onClick={() => elegir(c)}
                                        className="block w-full px-2.5 py-1.5 text-left text-sm text-chalk transition-colors hover:bg-surface-2"
                                    >
                                        {c.nombre}
                                        {!c.activo ? (
                                            <span className="ml-1 text-warn">· de baja</span>
                                        ) : null}
                                        <span className="apoyo block text-fog">{c.rut ?? 'sin RUT'}</span>
                                    </button>
                                </li>
                            ))}
                        </ul>
                    ) : null}
                </div>
            )}

            {errors.nombre ? <p className="apoyo text-danger">{errors.nombre}</p> : null}

            <div className="flex gap-2">
                <input
                    type="text"
                    value={data.concepto}
                    onChange={(e) => setData('concepto', e.target.value)}
                    maxLength={120}
                    placeholder="¿Qué se llevó?"
                    aria-label="Qué se llevó"
                    className={`${campo} min-w-0 flex-1`}
                />

                <input
                    type="number"
                    min="1"
                    value={data.monto}
                    onChange={(e) => setData('monto', e.target.value)}
                    placeholder="$"
                    aria-label="Cuánto"
                    className={`${campo} w-28 shrink-0 tabular-nums`}
                />
            </div>

            {errors.concepto ? <p className="apoyo text-danger">{errors.concepto}</p> : null}
            {errors.monto ? <p className="apoyo text-danger">{errors.monto}</p> : null}

            <div className="flex items-center gap-2">
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                >
                    Anotar
                </button>

                <button
                    type="button"
                    onClick={alTerminar}
                    className="apoyo text-fog transition-colors hover:text-chalk"
                >
                    Cancelar
                </button>
            </div>
        </form>
    );
}
