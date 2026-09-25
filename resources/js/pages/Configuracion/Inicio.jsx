import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { AlertTriangleIcon, CheckIcon, ChevronDownIcon, ChevronRightIcon, LightbulbIcon } from 'lucide-react';

/**
 * La portada de Configuración: lo que falta, en una lista.
 *
 * Reemplaza las tarjetas de catálogos, que contaban cuántos había de cada cosa
 * pero no decían lo que importaba. Cada punto dice qué pasa y lleva a donde se
 * arregla; dentro de cada tema va primero lo que impide trabajar, después lo
 * que conviene hacer, y al final, apagado, lo que ya está.
 */
const ESTADOS = {
    falta: { Icono: AlertTriangleIcon, tono: 'text-warn', etiqueta: 'Falta', orden: 0 },
    mejora: { Icono: LightbulbIcon, tono: 'text-info', etiqueta: 'Conviene', orden: 1 },
    ok: { Icono: CheckIcon, tono: 'text-ok', etiqueta: 'Listo', orden: 2 },
};

export default function Inicio({ puntos }) {
    const faltan = puntos.filter((p) => p.estado === 'falta').length;
    const mejoras = puntos.filter((p) => p.estado === 'mejora').length;
    const listos = puntos.filter((p) => p.estado === 'ok').length;
    const avance = puntos.length > 0 ? Math.round((listos / puntos.length) * 100) : 100;

    // Lo que ya está listo se pliega: es la mayoría, y a la vista tapaba lo poco
    // que sí hay que hacer. Se abre por grupo, para quien quiera comprobarlo.
    const [abiertos, setAbiertos] = useState({});

    const grupos = [];

    for (const punto of puntos) {
        let grupo = grupos.find((g) => g.titulo === punto.grupo);

        if (!grupo) {
            grupo = { titulo: punto.grupo, puntos: [] };
            grupos.push(grupo);
        }

        grupo.puntos.push(punto);
    }

    grupos.forEach((g) => g.puntos.sort((a, b) => ESTADOS[a.estado].orden - ESTADOS[b.estado].orden));

    return (
        <>
            <Head title="Configuración" />

            <header className="mb-6">
                <h1 className="text-xl font-semibold text-chalk">Lo que falta</h1>
                <p className="mt-1 text-sm text-fog">
                    {faltan === 0
                        ? 'Nada impide trabajar.'
                        : faltan === 1
                          ? 'Hay una cosa que conviene arreglar pronto.'
                          : `Hay ${faltan} cosas que conviene arreglar pronto.`}
                    {mejoras > 0
                        ? ` Y ${mejoras === 1 ? 'una idea' : `${mejoras} ideas`} para que la web rinda más.`
                        : ''}
                </p>
            </header>

            {/* CÓMO VA, de un vistazo: cuánto está listo y cuánto queda, antes de la lista. */}
            <section className="mb-6 rounded-panel border border-line bg-surface px-6 py-5">
                <div className="flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
                    <p className="text-sm text-chalk">
                        <span className="text-3xl font-semibold tabular-nums">{avance}%</span>
                        <span className="ml-2 text-fog">configurado</span>
                    </p>
                    <p className="flex flex-wrap gap-x-5 gap-y-1 text-sm text-fog">
                        <span className={faltan > 0 ? 'text-warn' : ''}>{faltan} por arreglar</span>
                        <span className={mejoras > 0 ? 'text-info' : ''}>{mejoras} {mejoras === 1 ? 'idea' : 'ideas'}</span>
                        <span className="text-ok">{listos} listos</span>
                    </p>
                </div>
                <div className="mt-4 h-2 overflow-hidden rounded-full bg-surface-2" role="progressbar" aria-valuenow={avance} aria-valuemin={0} aria-valuemax={100} aria-label="Configuración completada">
                    <div className="h-full rounded-full bg-ok transition-[width] duration-500" style={{ width: `${avance}%` }} />
                </div>
            </section>

            <div className="grid items-start gap-5 xl:grid-cols-2">
                {grupos.map((grupo) => {
                    const pendientes = grupo.puntos.filter((p) => p.estado !== 'ok');
                    const hechos = grupo.puntos.filter((p) => p.estado === 'ok');
                    const abierto = abiertos[grupo.titulo] ?? false;
                    const visibles = abierto ? grupo.puntos : pendientes;

                    return (
                    <section key={grupo.titulo} className="overflow-hidden rounded-panel border border-line bg-surface">
                        <h2 className="flex items-center justify-between gap-3 border-b border-line px-5 py-3">
                            <span className="rotulo">{grupo.titulo}</span>
                            {pendientes.length === 0 ? (
                                <span className="apoyo inline-flex items-center gap-1 text-ok">
                                    <CheckIcon className="size-3.5" aria-hidden="true" /> Todo listo
                                </span>
                            ) : (
                                <span className="apoyo tabular-nums text-warn">{pendientes.length} {pendientes.length === 1 ? 'pendiente' : 'pendientes'}</span>
                            )}
                        </h2>

                        <ul className="divide-y divide-line">
                            {visibles.map((punto) => {
                                const { Icono, tono, etiqueta } = ESTADOS[punto.estado];

                                return (
                                    <li key={punto.clave}>
                                        <Link
                                            href={punto.href}
                                            className={`group flex items-start gap-3.5 border-l-2 px-5 py-4 transition-colors hover:bg-surface-2 ${
                                                punto.estado === 'falta' ? 'border-l-warn' : punto.estado === 'mejora' ? 'border-l-info' : 'border-l-transparent'
                                            }`}
                                        >
                                            <Icono className={`mt-0.5 size-4 shrink-0 ${tono}`} aria-label={etiqueta} />

                                            <span className="min-w-0 flex-1">
                                                <span
                                                    className={`block text-sm font-medium ${punto.estado === 'ok' ? 'text-fog' : 'text-chalk'}`}
                                                >
                                                    {punto.titulo}
                                                </span>
                                                <span className="mt-0.5 block text-[13px] leading-relaxed text-fog">{punto.detalle}</span>
                                            </span>

                                            <ChevronRightIcon
                                                className="mt-0.5 size-4 shrink-0 text-fog transition-transform group-hover:translate-x-0.5"
                                                aria-hidden="true"
                                            />
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>

                        {hechos.length > 0 ? (
                            <button
                                type="button"
                                onClick={() => setAbiertos((a) => ({ ...a, [grupo.titulo]: !abierto }))}
                                aria-expanded={abierto}
                                className={`apoyo flex w-full items-center justify-between gap-2 px-5 py-2.5 text-fog transition-colors hover:bg-surface-2 hover:text-chalk ${visibles.length > 0 ? 'border-t border-line' : ''}`}
                            >
                                {abierto ? 'Ocultar lo que ya está listo' : `${hechos.length} ${hechos.length === 1 ? 'cosa ya está lista' : 'cosas ya están listas'}`}
                                <ChevronDownIcon className={`size-4 transition-transform ${abierto ? 'rotate-180' : ''}`} aria-hidden="true" />
                            </button>
                        ) : null}
                    </section>
                    );
                })}
            </div>
        </>
    );
}
