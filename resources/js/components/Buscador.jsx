import { router } from '@inertiajs/react';
import { SearchIcon } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

/**
 * Caja de busqueda que consulta al servidor.
 *
 * Se espera a que el usuario deje de escribir: sin la espera, cada tecla seria
 * una consulta y la lista parpadearia mientras se teclea el apellido.
 */
export default function Buscador({ ruta, valor = '', etiqueta = 'Buscar', extra = {} }) {
    const [texto, setTexto] = useState(valor);
    // El primer render no debe consultar: la pagina ya llega servida.
    const montado = useRef(false);

    useEffect(() => {
        if (!montado.current) {
            montado.current = true;

            return undefined;
        }

        const temporizador = setTimeout(() => {
            router.get(
                ruta,
                { ...extra, ...(texto ? { buscar: texto } : {}) },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(temporizador);
    }, [texto]);

    return (
        <div className="relative max-w-sm flex-1">
            <SearchIcon
                className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-fog"
                aria-hidden="true"
            />
            <input
                type="search"
                value={texto}
                onChange={(e) => setTexto(e.target.value)}
                placeholder={etiqueta}
                aria-label={etiqueta}
                className="w-full rounded-control border border-line bg-surface py-1.5 pr-3 pl-8 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
            />
        </div>
    );
}
