import { Head, Link } from '@inertiajs/react';
import { AlertTriangleIcon, CheckIcon, ChevronRightIcon, LightbulbIcon } from 'lucide-react';

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

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">Lo que falta</h1>
                <p className="apoyo text-fog">
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

            <div className="max-w-3xl space-y-4">
                {grupos.map((grupo) => (
                    <section key={grupo.titulo} className="overflow-hidden rounded-panel border border-line bg-surface">
                        <h2 className="rotulo border-b border-line px-4 py-2">{grupo.titulo}</h2>

                        <ul className="divide-y divide-line">
                            {grupo.puntos.map((punto) => {
                                const { Icono, tono, etiqueta } = ESTADOS[punto.estado];

                                return (
                                    <li key={punto.clave}>
                                        <Link
                                            href={punto.href}
                                            className="group flex items-start gap-3 px-4 py-3 transition-colors hover:bg-surface-2"
                                        >
                                            <Icono className={`mt-0.5 size-4 shrink-0 ${tono}`} aria-label={etiqueta} />

                                            <span className="min-w-0 flex-1">
                                                <span
                                                    className={`block text-sm font-medium ${punto.estado === 'ok' ? 'text-fog' : 'text-chalk'}`}
                                                >
                                                    {punto.titulo}
                                                </span>
                                                <span className="apoyo block text-fog">{punto.detalle}</span>
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
                    </section>
                ))}
            </div>
        </>
    );
}
