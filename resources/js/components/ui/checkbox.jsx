import * as CheckboxPrimitive from '@radix-ui/react-checkbox';
import { CheckIcon, MinusIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

/**
 * Casilla. Copiada de shadcn/ui y adaptada al panel.
 *
 * Existe por la seleccion multiple de las listas: la casilla de la cabecera
 * necesita un tercer estado (`indeterminate`) que el <input type="checkbox">
 * nativo solo admite por JavaScript y que ningun lector de pantalla anuncia si
 * no se acompana de aria-checked="mixed". Radix lo hace bien y ademas responde
 * al teclado igual que el nativo.
 *
 * Cambios sobre el original: tokens en vez de la paleta de serie, sin ramas
 * `dark:`, y el glifo del estado mixto (el original deja la casilla marcada,
 * que miente).
 *
 * `focus-visible:outline-solid` NO SOBRA, aunque lo parezca. En Tailwind 4
 * `outline-none` no solo apaga el borde: fija `--tw-outline-style: none`, y
 * `outline-2` lo que emite es `outline-style: var(--tw-outline-style)`. Sin
 * devolver el estilo a `solid`, la casilla se quedaba con ancho 2px y color
 * accent bien puestos y ESTILO NONE: es decir, sin ningun anillo dibujado.
 * Medido tabulando de verdad: :focus-visible casaba y no se veia nada, que en
 * la unica lista con seleccion multiple deja al teclado sin saber donde esta.
 */

function Checkbox({ className, ...props }) {
    return (
        <CheckboxPrimitive.Root
            data-slot="checkbox"
            className={cn(
                'peer size-4 shrink-0 rounded-[4px] border border-line-strong bg-surface outline-none transition-colors focus-visible:outline-2 focus-visible:outline-solid focus-visible:outline-offset-1 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:border-volt data-[state=checked]:bg-volt data-[state=checked]:text-on-volt data-[state=indeterminate]:border-volt data-[state=indeterminate]:bg-volt data-[state=indeterminate]:text-on-volt',
                className,
            )}
            {...props}
        >
            <CheckboxPrimitive.Indicator data-slot="checkbox-indicator" className="grid place-content-center text-current">
                {props.checked === 'indeterminate' ? <MinusIcon className="size-3" /> : <CheckIcon className="size-3.5" />}
            </CheckboxPrimitive.Indicator>
        </CheckboxPrimitive.Root>
    );
}

export { Checkbox };
