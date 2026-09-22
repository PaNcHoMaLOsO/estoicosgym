import { router } from '@inertiajs/react';
import { CheckIcon, ChevronDownIcon } from 'lucide-react';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

/**
 * Un desplegable que filtra u ordena la lista, al lado de los botones.
 *
 * LOS BOTONES NO SIRVEN PARA TODO. Los grupos —vigentes, vencidas, con deuda—
 * son cinco y se miran de un vistazo; los planes son ocho y crecerán, y las
 * formas de ordenar no son un estado de la membresía sino otra pregunta. En
 * botones, esa fila se volvía una pared de veinte pastillas.
 *
 * NO ES UN <select> DEL SISTEMA. El del navegador se pinta con los colores de
 * Windows —fondo blanco y la fila marcada en azul— dentro de un panel oscuro,
 * y no deja agrupar ni separar. Este es el mismo menú que usa el resto del
 * panel, así que se ve igual en todas partes.
 *
 * `opciones` es una lista, o `grupos` [{ titulo, opciones }] cuando hay
 * bastantes y se leen mejor repartidas.
 */
export default function Selector({ etiqueta, nombre, valor = '', opciones, grupos, ruta, extra = {} }) {
    const bloques = grupos ?? [{ titulo: null, opciones }];
    const todas = bloques.flatMap((b) => b.opciones);
    const elegida = todas.find((o) => o.valor === (valor ?? '')) ?? todas[0];

    function elegir(nuevo) {
        router.get(
            ruta,
            { ...extra, ...(nuevo ? { [nombre]: nuevo } : {}) },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    }

    return (
        <span className="inline-flex items-center gap-1.5">
            <span className="apoyo text-fog">{etiqueta}</span>

            <DropdownMenu>
                <DropdownMenuTrigger
                    className={`inline-flex items-center gap-1.5 rounded-control border px-2.5 py-1 text-sm transition-colors ${
                        valor
                            ? 'border-line-strong bg-surface-2 font-medium text-chalk'
                            : 'border-line text-fog hover:border-line-strong hover:text-chalk'
                    }`}
                >
                    {elegida?.etiqueta}
                    <ChevronDownIcon className="size-3.5 opacity-70" aria-hidden="true" />
                </DropdownMenuTrigger>

                <DropdownMenuContent align="start" className="min-w-52">
                    {bloques.map((bloque, i) => (
                        <span key={bloque.titulo ?? i} className="contents">
                            {i > 0 ? <DropdownMenuSeparator /> : null}
                            {bloque.titulo ? <DropdownMenuLabel>{bloque.titulo}</DropdownMenuLabel> : null}

                            {bloque.opciones.map((o) => {
                                const puesta = o.valor === (valor ?? '');

                                return (
                                    <DropdownMenuItem
                                        key={o.valor || 'todos'}
                                        onSelect={() => elegir(o.valor)}
                                        className={`justify-between gap-3 ${puesta ? 'text-chalk' : 'text-fog'}`}
                                    >
                                        <span className="inline-flex items-center gap-2">
                                            {/* El visto se reserva sitio siempre, para que
                                                los nombres no bailen al cambiar de opción. */}
                                            <CheckIcon
                                                className={`size-3.5 ${puesta ? 'opacity-100' : 'opacity-0'}`}
                                                aria-hidden="true"
                                            />
                                            {o.etiqueta}
                                        </span>

                                        {o.cuantas !== undefined ? (
                                            <span className="apoyo tabular-nums text-fog">{o.cuantas}</span>
                                        ) : null}
                                    </DropdownMenuItem>
                                );
                            })}
                        </span>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </span>
    );
}
