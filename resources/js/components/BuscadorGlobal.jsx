import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { SearchIcon } from 'lucide-react';

const pesos = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 });

/** Cómo está el socio, en dos palabras: es lo que se quiere saber antes de abrir su ficha. */
function estado(socio) {
    if (! socio.activo) {
        return { texto: 'De baja', clase: 'text-fog' };
    }
    if (! socio.plan) {
        return { texto: 'Sin plan', clase: 'text-warn' };
    }
    if (socio.dias !== null && socio.dias <= 7) {
        return { texto: socio.dias <= 0 ? 'Vence hoy' : `Vence en ${socio.dias} d`, clase: 'text-warn' };
    }

    return { texto: `Hasta ${socio.vence}`, clase: 'text-fog' };
}

/**
 * Buscar a un socio desde CUALQUIER pantalla del panel.
 *
 * En el mesón todo empieza por un nombre o un RUT, y el único buscador estaba
 * en Resumen: desde Pagos o desde un reporte había que volver al inicio para
 * encontrar a alguien. Este vive en el marco y lleva directo a la ficha, que es
 * desde donde se renueva, se inscribe y se cobra.
 *
 * Se abre con «/» o Ctrl+K. Flechas para moverse, Enter para abrir, Escape
 * para cerrar: con el socio delante no hay tiempo para apuntar con el mouse.
 */
export default function BuscadorGlobal({ className = '', alElegir }) {
    const [texto, setTexto] = useState('');
    const [socios, setSocios] = useState(null);
    const [buscando, setBuscando] = useState(false);
    const [abierto, setAbierto] = useState(false);
    const [marcado, setMarcado] = useState(0);
    const caja = useRef(null);
    const raiz = useRef(null);

    // Se busca al dejar de escribir, no en cada tecla; y una respuesta vieja que
    // llega tarde no pisa a la nueva.
    useEffect(() => {
        const q = texto.trim();
        if (q.length < 2) {
            setSocios(null);
            setBuscando(false);

            return undefined;
        }

        setBuscando(true);
        const corte = new AbortController();
        const espera = setTimeout(async () => {
            try {
                const r = await fetch(`/panel/clientes/buscar?q=${encodeURIComponent(q)}`, {
                    headers: { Accept: 'application/json' },
                    signal: corte.signal,
                });
                const datos = await r.json();
                setSocios(datos.socios ?? []);
                setMarcado(0);
            } catch {
                // Cortada por la siguiente búsqueda o sin red: se queda lo que había.
            } finally {
                if (! corte.signal.aborted) {
                    setBuscando(false);
                }
            }
        }, 220);

        return () => {
            clearTimeout(espera);
            corte.abort();
        };
    }, [texto]);

    // «/» y Ctrl+K llevan al buscador desde cualquier parte, salvo que ya se
    // esté escribiendo en otra casilla.
    useEffect(() => {
        const alTeclear = (e) => {
            const escribiendo = /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName) || e.target.isContentEditable;
            const atajo = (e.key === '/' && ! escribiendo) || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k');

            // Solo responde el buscador que se ve: hay uno en el carril y otro en el móvil.
            if (atajo && caja.current && caja.current.offsetParent !== null) {
                e.preventDefault();
                caja.current.focus();
                caja.current.select();
            }
        };
        const alPulsarFuera = (e) => {
            if (raiz.current && ! raiz.current.contains(e.target)) {
                setAbierto(false);
            }
        };

        window.addEventListener('keydown', alTeclear);
        window.addEventListener('pointerdown', alPulsarFuera);

        return () => {
            window.removeEventListener('keydown', alTeclear);
            window.removeEventListener('pointerdown', alPulsarFuera);
        };
    }, []);

    const abrir = (socio) => {
        setAbierto(false);
        setTexto('');
        caja.current?.blur();
        alElegir?.();
        router.visit(`/panel/clientes/${socio.uuid}`);
    };

    const alTeclearEnCaja = (e) => {
        if (e.key === 'Escape') {
            setAbierto(false);
            caja.current?.blur();
        } else if (e.key === 'ArrowDown' && socios?.length) {
            e.preventDefault();
            setMarcado((m) => (m + 1) % socios.length);
        } else if (e.key === 'ArrowUp' && socios?.length) {
            e.preventDefault();
            setMarcado((m) => (m - 1 + socios.length) % socios.length);
        } else if (e.key === 'Enter' && socios?.[marcado]) {
            e.preventDefault();
            abrir(socios[marcado]);
        }
    };

    const conLista = abierto && texto.trim().length >= 2;

    return (
        <div ref={raiz} className={`relative ${className}`}>
            <label className="relative block">
                <span className="sr-only">Buscar socio</span>
                <SearchIcon className="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-fog" aria-hidden="true" />
                <input
                    ref={caja}
                    type="search"
                    value={texto}
                    onChange={(e) => {
                        setTexto(e.target.value);
                        setAbierto(true);
                    }}
                    onFocus={() => setAbierto(true)}
                    onKeyDown={alTeclearEnCaja}
                    placeholder="Buscar socio"
                    autoComplete="off"
                    role="combobox"
                    aria-expanded={conLista}
                    aria-controls="buscador-global-lista"
                    className="h-9 w-full rounded-control border border-line bg-surface-2 pl-8 pr-8 text-sm text-chalk placeholder:text-fog focus:border-volt focus:outline-none"
                />
                <kbd className="pointer-events-none absolute right-2 top-1/2 hidden -translate-y-1/2 rounded border border-line px-1 text-[10px] text-fog lg:block" aria-hidden="true">/</kbd>
            </label>

            {conLista ? (
                <div
                    id="buscador-global-lista"
                    role="listbox"
                    className="absolute left-0 top-full z-50 mt-1 w-full min-w-[19rem] overflow-hidden rounded-control border border-line bg-surface shadow-xl"
                >
                    {socios === null || (buscando && socios.length === 0) ? (
                        <p className="apoyo px-3 py-2.5 text-fog">Buscando…</p>
                    ) : socios.length === 0 ? (
                        <p className="apoyo px-3 py-2.5 text-fog">Nadie coincide.</p>
                    ) : (
                        <ul className="max-h-[22rem] divide-y divide-line overflow-y-auto">
                            {socios.map((s, i) => {
                                const e = estado(s);

                                return (
                                    <li key={s.uuid} role="option" aria-selected={i === marcado}>
                                        <button
                                            type="button"
                                            onClick={() => abrir(s)}
                                            onMouseEnter={() => setMarcado(i)}
                                            className={`flex w-full items-center justify-between gap-3 px-3 py-2 text-left transition-colors ${i === marcado ? 'bg-surface-2' : ''}`}
                                        >
                                            <span className="min-w-0">
                                                <span className="block truncate text-sm text-chalk">{s.nombre}</span>
                                                <span className="apoyo block truncate text-fog">
                                                    {s.rut ?? 'sin RUT'}
                                                    {s.plan ? ` · ${s.plan}` : ''}
                                                </span>
                                            </span>
                                            <span className="shrink-0 text-right">
                                                <span className={`apoyo block ${e.clase}`}>{e.texto}</span>
                                                {s.debe > 0 ? (
                                                    <span className="apoyo block text-danger">Debe {pesos.format(s.debe)}</span>
                                                ) : null}
                                            </span>
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>
            ) : null}
        </div>
    );
}
