import { Head, Link, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import Nota from '@/components/Nota';
import { ArrowLeftIcon, BookmarkIcon, DownloadIcon, XIcon } from 'lucide-react';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

const numeros = new Intl.NumberFormat('es-CL');

/** Un valor ya viene formateado del servidor; aqui solo se decide como pintarlo. */
function Celda({ valor, tipo }) {
    if (valor === null || valor === '') {
        return <span className="text-fog">-</span>;
    }

    if (tipo === 'moneda') {
        return <span className="tabular-nums">{pesos.format(valor)}</span>;
    }

    if (tipo === 'numero') {
        return <span className="tabular-nums">{numeros.format(valor)}</span>;
    }

    if (tipo === 'booleano') {
        return valor ? 'Sí' : 'No';
    }

    if (tipo === 'fecha') {
        return <span className="tabular-nums">{valor}</span>;
    }

    return String(valor);
}

/**
 * Constructor de informes.
 *
 * Se elige de que, que columnas y con que filtros. La tabla se pide por JSON y
 * se pinta debajo sin recargar: cambiar una columna y volver a mirar es lo que
 * mas se hace aqui, y recargar la pagina entera perderia lo ya armado.
 */
export default function Constructor({ catalogo, limites, tope, guardados = [] }) {
    const claves = Object.keys(catalogo);

    const [modulo, setModulo] = useState(claves[0]);
    const [elegidas, setElegidas] = useState(() => primerasCinco(catalogo[claves[0]]));
    const [filtros, setFiltros] = useState({});
    const [orden, setOrden] = useState(null);
    const [direccion, setDireccion] = useState('desc');
    const [limite, setLimite] = useState(100);

    const [informe, setInforme] = useState(null);
    const [cargando, setCargando] = useState(false);
    const [fallo, setFallo] = useState(null);

    // Guardar la receta: armar un informe cuesta veinte clics y el mismo se
    // pide todos los meses.
    const [nombre, setNombre] = useState('');
    const [guardando, setGuardando] = useState(false);

    const columnas = catalogo[modulo].columnas;

    // Por una columna derivada no se puede ordenar ni filtrar: no existe en la
    // tabla, se calcula despues de traer las filas.
    const propias = useMemo(
        () => Object.entries(columnas).filter(([, c]) => !c.derivada),
        [columnas],
    );

    function cambiarModulo(nuevo) {
        setModulo(nuevo);
        setElegidas(primerasCinco(catalogo[nuevo]));
        // Los filtros y el orden son del modulo anterior: dejarlos puestos
        // pediria columnas que en este no existen.
        setFiltros({});
        setOrden(null);
        setInforme(null);
        setFallo(null);
    }

    function alternar(clave) {
        setElegidas(elegidas.includes(clave)
            ? elegidas.filter((c) => c !== clave)
            : [...elegidas, clave]);
    }

    function ponerFiltro(clave, valor) {
        setFiltros({ ...filtros, [clave]: valor });
    }

    /** Lo que se guarda: lo elegido, no las filas. */
    function receta() {
        return { columnas: elegidas, filtros, orden, direccion, limite };
    }

    function guardar() {
        if (nombre.trim() === '') {
            return;
        }

        setGuardando(true);

        router.post(
            '/panel/reportes/constructor/guardados',
            { nombre: nombre.trim(), modulo, configuracion: receta() },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => setNombre(''),
                onFinish: () => setGuardando(false),
            },
        );
    }

    /**
     * Abrir uno guardado: deja la pantalla como estaba y pide la tabla sola.
     *
     * Sin lo último habría que acordarse de pulsar «Ver» después de elegirlo, y
     * la pantalla se quedaría con la tabla del informe anterior debajo del
     * nombre del nuevo, que es la peor forma de equivocarse.
     */
    function abrir(informeGuardado) {
        const receta = informeGuardado.configuracion ?? {};

        setModulo(informeGuardado.modulo);
        setElegidas(receta.columnas ?? []);
        setFiltros(receta.filtros ?? {});
        setOrden(receta.orden ?? null);
        setDireccion(receta.direccion ?? 'desc');
        setLimite(receta.limite ?? 100);
        setInforme(null);
        setFallo(null);
    }

    function olvidar(informeGuardado) {
        router.delete(`/panel/reportes/constructor/guardados/${informeGuardado.uuid}`, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    const consulta = useMemo(() => {
        const p = new URLSearchParams();

        elegidas.forEach((c) => p.append('columnas[]', c));

        Object.entries(filtros).forEach(([clave, valor]) => {
            if (valor === '' || valor === null || valor === undefined) {
                return;
            }

            if (typeof valor === 'object') {
                Object.entries(valor).forEach(([extremo, v]) => {
                    if (v) {
                        p.append(`filtros[${clave}][${extremo}]`, v);
                    }
                });

                return;
            }

            p.append(`filtros[${clave}]`, valor);
        });

        if (orden) {
            p.append('orden', orden);
        }

        p.append('direccion', direccion);
        p.append('limite', String(limite));

        return p.toString();
    }, [elegidas, filtros, orden, direccion, limite]);

    async function ver() {
        setCargando(true);
        setFallo(null);

        try {
            const r = await fetch(`/panel/reportes/constructor/${modulo}/ver?${consulta}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!r.ok) {
                throw new Error('El servidor rechazó la petición.');
            }

            setInforme(await r.json());
        } catch (e) {
            setFallo('No se pudo generar el informe. Inténtalo de nuevo.');
            setInforme(null);
        } finally {
            setCargando(false);
        }
    }

    return (
        <>
            <Head title="Constructor de informes" />

            <header className="mb-5">
                <Link
                    href="/panel/reportes"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Reportes
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Constructor de informes</h1>
                <p className="apoyo text-fog">
                    Arma tu propia tabla: elige de qué, qué columnas y con qué filtros.
                </p>
            </header>

            <div className="grid gap-4 lg:grid-cols-[20rem_1fr]">
                <div className="space-y-4">
                    <section className="rounded-panel border border-line bg-surface p-3">
                        <h2 className="mb-2 text-xs font-semibold uppercase tracking-wide text-fog">
                            ¿De qué?
                        </h2>

                        <div className="flex flex-wrap gap-1.5">
                            {claves.map((clave) => (
                                <button
                                    key={clave}
                                    type="button"
                                    onClick={() => cambiarModulo(clave)}
                                    aria-pressed={modulo === clave}
                                    className={`rounded-control border px-2.5 py-1 text-sm transition-colors ${
                                        modulo === clave
                                            ? 'border-volt bg-volt text-on-volt'
                                            : 'border-line text-fog hover:text-chalk'
                                    }`}
                                >
                                    {catalogo[clave].titulo}
                                </button>
                            ))}
                        </div>
                    </section>

                    {/*
                      * MIS INFORMES, arriba del todo.
                      *
                      * Es lo primero que se busca al entrar: el que ya está
                      * armado. Abajo, después de las columnas y los filtros,
                      * habría que pasar por delante de todo lo que se quiere
                      * evitar para encontrarlo.
                      */}
                    <section className="rounded-panel border border-line bg-surface p-3">
                        <h2 className="mb-2 text-xs font-semibold uppercase tracking-wide text-fog">
                            Mis informes
                        </h2>

                        {guardados.length === 0 ? (
                            <p className="apoyo text-fog">
                                Arma uno abajo y guárdalo con un nombre: no hay que volver a montarlo el mes
                                que viene.
                            </p>
                        ) : (
                            <ul className="mb-2 space-y-0.5">
                                {guardados.map((g) => (
                                    <li key={g.uuid} className="group flex items-center gap-1">
                                        <button
                                            type="button"
                                            onClick={() => abrir(g)}
                                            className="min-w-0 flex-1 truncate rounded-control px-1.5 py-1 text-left text-sm text-chalk transition-colors hover:bg-surface-2"
                                        >
                                            {g.nombre}
                                            <span className="apoyo block text-fog">
                                                {catalogo[g.modulo]?.titulo ?? g.modulo}
                                            </span>
                                        </button>

                                        {/* Quitar solo al pasar por encima: es lo
                                            único de aquí que no se deshace. */}
                                        <button
                                            type="button"
                                            onClick={() => olvidar(g)}
                                            aria-label={`Quitar ${g.nombre}`}
                                            className="rounded-control p-1 text-fog opacity-0 transition-opacity hover:text-danger focus:opacity-100 group-hover:opacity-100"
                                        >
                                            <XIcon className="size-3.5" aria-hidden="true" />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}

                        <div className="mt-2 flex gap-1.5 border-t border-line pt-2">
                            <input
                                type="text"
                                value={nombre}
                                onChange={(e) => setNombre(e.target.value)}
                                onKeyDown={(e) => (e.key === 'Enter' ? guardar() : null)}
                                maxLength={80}
                                placeholder="Guardar esto como…"
                                aria-label="Nombre del informe"
                                className="min-w-0 flex-1 rounded-control border border-line bg-surface-2 px-2 py-1 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                            />
                            <button
                                type="button"
                                onClick={guardar}
                                disabled={guardando || nombre.trim() === ''}
                                aria-label="Guardar el informe"
                                className="shrink-0 rounded-control border border-line px-2 text-fog transition-colors hover:text-chalk disabled:opacity-40"
                            >
                                <BookmarkIcon className="size-4" aria-hidden="true" />
                            </button>
                        </div>
                    </section>

                    <section className="rounded-panel border border-line bg-surface p-3">
                        <div className="mb-2 flex items-baseline justify-between gap-2">
                            <h2 className="text-xs font-semibold uppercase tracking-wide text-fog">
                                Columnas
                            </h2>

                            <div className="apoyo flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => setElegidas(Object.keys(columnas))}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    Todas
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setElegidas([])}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    Ninguna
                                </button>
                            </div>
                        </div>

                        <ul className="space-y-0.5">
                            {Object.entries(columnas).map(([clave, columna]) => (
                                <li key={clave}>
                                    <label className="flex cursor-pointer items-center gap-2 rounded-control px-1.5 py-1 text-sm text-chalk transition-colors hover:bg-surface-2">
                                        <input
                                            type="checkbox"
                                            checked={elegidas.includes(clave)}
                                            onChange={() => alternar(clave)}
                                            className="size-3.5 accent-[var(--color-volt)]"
                                        />
                                        {columna.titulo}
                                    </label>
                                </li>
                            ))}
                        </ul>

                        {/* Sin ninguna marcada el servidor enseña las primeras
                            cinco. Se avisa aqui para que no parezca que las
                            eligio solo. */}
                        {elegidas.length === 0 ? (
                            <p className="apoyo mt-2 text-warn">
                                Sin columnas marcadas se muestran las cinco primeras.
                            </p>
                        ) : null}
                    </section>

                    <section className="rounded-panel border border-line bg-surface p-3">
                        <h2 className="mb-2 text-xs font-semibold uppercase tracking-wide text-fog">
                            Filtros
                        </h2>

                        <div className="space-y-2">
                            {propias.map(([clave, columna]) => (
                                <Filtro
                                    key={clave}
                                    clave={clave}
                                    columna={columna}
                                    valor={filtros[clave]}
                                    alCambiar={(v) => ponerFiltro(clave, v)}
                                />
                            ))}
                        </div>
                    </section>

                    <section className="rounded-panel border border-line bg-surface p-3">
                        <h2 className="mb-2 text-xs font-semibold uppercase tracking-wide text-fog">
                            Orden y tamaño
                        </h2>

                        <div className="space-y-2">
                            <select
                                value={orden ?? ''}
                                onChange={(e) => setOrden(e.target.value || null)}
                                aria-label="Ordenar por"
                                className="w-full rounded-control border border-line bg-surface-2 px-2 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                            >
                                <option value="">Por defecto (más reciente)</option>
                                {propias.map(([clave, columna]) => (
                                    <option key={clave} value={clave}>
                                        {columna.titulo}
                                    </option>
                                ))}
                            </select>

                            <select
                                value={direccion}
                                onChange={(e) => setDireccion(e.target.value)}
                                aria-label="Dirección del orden"
                                className="w-full rounded-control border border-line bg-surface-2 px-2 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                            >
                                <option value="desc">De mayor a menor</option>
                                <option value="asc">De menor a mayor</option>
                            </select>

                            <select
                                value={limite}
                                onChange={(e) => setLimite(Number(e.target.value))}
                                aria-label="Cuántas filas"
                                className="w-full rounded-control border border-line bg-surface-2 px-2 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                            >
                                {limites.map((n) => (
                                    <option key={n} value={n}>
                                        {n === tope ? `Todas (máx. ${numeros.format(tope)})` : `${n} filas`}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </section>

                    <div className="flex gap-2">
                        <button
                            type="button"
                            onClick={ver}
                            disabled={cargando}
                            className="flex-1 rounded-control bg-volt px-3 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            {cargando ? 'Generando…' : 'Ver informe'}
                        </button>

                        {/* Es un enlace y no un boton: la descarga la sirve el
                            servidor, aqui no hay nada que armar. */}
                        <a
                            href={`/panel/reportes/constructor/${modulo}/csv?${consulta}`}
                            className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-2 text-sm text-fog transition-colors hover:text-chalk"
                        >
                            <DownloadIcon className="size-4" aria-hidden="true" />
                            CSV
                        </a>
                    </div>
                </div>

                <section className="min-w-0">
                    {fallo ? (
                        <Nota tono="peligro">{fallo}</Nota>
                    ) : !informe ? (
                        <div className="rounded-panel border border-dashed border-line px-4 py-10 text-center">
                            <p className="text-sm text-fog">
                                Elige qué quieres ver y pulsa «Ver informe».
                            </p>
                        </div>
                    ) : informe.filas.length === 0 ? (
                        <div className="rounded-panel border border-line bg-surface px-4 py-10 text-center">
                            <p className="text-sm text-fog">
                                Ninguna fila cumple esos filtros.
                            </p>
                        </div>
                    ) : (
                        <>
                            <div className="mb-2 flex flex-wrap items-baseline justify-between gap-2">
                                <p className="apoyo text-fog">
                                    {numeros.format(informe.cuantas)}{' '}
                                    {informe.cuantas === 1 ? 'fila' : 'filas'}
                                </p>

                                {/* Una tabla recortada TIENE que decirlo, o se
                                    lee como si fuera todo lo que hay. */}
                                {informe.recortado ? (
                                    <p className="apoyo text-warn">
                                        Hay más de las que caben: sube el límite o afina los filtros.
                                    </p>
                                ) : null}
                            </div>

                            <div className="overflow-x-auto rounded-panel border border-line">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b border-line bg-surface-2">
                                            {informe.columnas.map((c) => (
                                                <th
                                                    key={c.clave}
                                                    scope="col"
                                                    className={`whitespace-nowrap px-3 py-2 font-medium text-fog ${
                                                        c.tipo === 'moneda' || c.tipo === 'numero'
                                                            ? 'text-right'
                                                            : 'text-left'
                                                    }`}
                                                >
                                                    {c.titulo}
                                                </th>
                                            ))}
                                        </tr>
                                    </thead>

                                    <tbody className="divide-y divide-line">
                                        {informe.filas.map((fila, i) => (
                                            <tr key={i} className="transition-colors hover:bg-surface-2">
                                                {informe.columnas.map((c) => (
                                                    <td
                                                        key={c.clave}
                                                        className={`px-3 py-2 text-chalk ${
                                                            c.tipo === 'moneda' || c.tipo === 'numero'
                                                                ? 'text-right'
                                                                : 'text-left'
                                                        }`}
                                                    >
                                                        <Celda valor={fila[c.clave]} tipo={c.tipo} />
                                                    </td>
                                                ))}
                                            </tr>
                                        ))}
                                    </tbody>

                                    {/* La suma de las columnas de dinero, abajo:
                                        sin ella hay que sacar la calculadora. */}
                                    {Object.keys(informe.totales).length > 0 ? (
                                        <tfoot>
                                            <tr className="border-t border-line-strong bg-surface-2">
                                                {informe.columnas.map((c, i) => (
                                                    <td
                                                        key={c.clave}
                                                        className={`px-3 py-2 font-semibold text-chalk ${
                                                            c.tipo === 'moneda' ? 'text-right tabular-nums' : ''
                                                        }`}
                                                    >
                                                        {c.clave in informe.totales
                                                            ? pesos.format(informe.totales[c.clave])
                                                            : i === 0
                                                              ? 'Total'
                                                              : null}
                                                    </td>
                                                ))}
                                            </tr>
                                        </tfoot>
                                    ) : null}
                                </table>
                            </div>
                        </>
                    )}
                </section>
            </div>
        </>
    );
}

/** Un filtro por columna, del tipo que pida la columna. */
function Filtro({ clave, columna, valor, alCambiar }) {
    const base =
        'w-full rounded-control border border-line bg-surface-2 px-2 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none';

    if (columna.tipo === 'fecha') {
        return (
            <div>
                <span className="apoyo mb-0.5 block text-fog">{columna.titulo}</span>
                <div className="flex gap-1.5">
                    <input
                        type="date"
                        aria-label={`${columna.titulo}: desde`}
                        value={valor?.desde ?? ''}
                        onChange={(e) => alCambiar({ ...valor, desde: e.target.value })}
                        className={base}
                    />
                    <input
                        type="date"
                        aria-label={`${columna.titulo}: hasta`}
                        value={valor?.hasta ?? ''}
                        onChange={(e) => alCambiar({ ...valor, hasta: e.target.value })}
                        className={base}
                    />
                </div>
            </div>
        );
    }

    if (columna.tipo === 'booleano') {
        return (
            <label className="block">
                <span className="apoyo mb-0.5 block text-fog">{columna.titulo}</span>
                <select value={valor ?? ''} onChange={(e) => alCambiar(e.target.value)} className={base}>
                    <option value="">Da igual</option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
            </label>
        );
    }

    // Va ANTES del caso numerico: un estado tiene tipo 'estado' pero trae sus
    // opciones, y sin esto se pediria el codigo a mano.
    if (columna.opciones) {
        return (
            <label className="block">
                <span className="apoyo mb-0.5 block text-fog">{columna.titulo}</span>
                <select value={valor ?? ''} onChange={(e) => alCambiar(e.target.value)} className={base}>
                    <option value="">Todos</option>
                    {Object.entries(columna.opciones).map(([v, etiqueta]) => (
                        <option key={v} value={v}>
                            {etiqueta}
                        </option>
                    ))}
                </select>
            </label>
        );
    }

    if (columna.tipo === 'numero' || columna.tipo === 'moneda' || columna.tipo === 'estado') {
        return (
            <label className="block">
                <span className="apoyo mb-0.5 block text-fog">{columna.titulo}</span>
                <input
                    type="number"
                    value={valor ?? ''}
                    onChange={(e) => alCambiar(e.target.value)}
                    className={base}
                />
            </label>
        );
    }

    return (
        <label className="block">
            <span className="apoyo mb-0.5 block text-fog">{columna.titulo}</span>
            <input
                type="search"
                value={valor ?? ''}
                onChange={(e) => alCambiar(e.target.value)}
                placeholder="Contiene…"
                className={base}
            />
        </label>
    );
}

function primerasCinco(modulo) {
    return Object.keys(modulo.columnas).slice(0, 5);
}
