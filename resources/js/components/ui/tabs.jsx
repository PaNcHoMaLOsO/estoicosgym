import * as TabsPrimitive from '@radix-ui/react-tabs';
import { cva } from 'class-variance-authority';

import { cn } from '@/lib/utils';

/**
 * Pestanas. Copiado de shadcn/ui y adaptado al panel.
 *
 * Lo que aporta sobre unos <button> sueltos, que es lo que habia en Ajustes:
 * role="tablist", aria-selected, y foco itinerante (una sola parada de tabulador
 * para todo el grupo, y dentro se mueve con las flechas). Eso no se improvisa.
 *
 * Cambios sobre el original: paleta por tokens en vez de la de serie, sin las
 * ramas `dark:` (aqui el tema lo resuelven las primitivas --app-*, no una
 * variante por clase) y `rounded-*` por los radios del panel.
 */

function Tabs({ className, orientation = 'horizontal', ...props }) {
    return (
        <TabsPrimitive.Root
            data-slot="tabs"
            data-orientation={orientation}
            orientation={orientation}
            className={cn('group/tabs flex gap-3 data-[orientation=horizontal]:flex-col', className)}
            {...props}
        />
    );
}

const tabsListVariants = cva(
    'group/tabs-list inline-flex w-fit items-center justify-center text-fog group-data-[orientation=vertical]/tabs:flex-col group-data-[orientation=vertical]/tabs:items-stretch',
    {
        variants: {
            variant: {
                // Contenedor relleno: para grupos cortos que caben siempre.
                default: 'gap-0.5 rounded-panel bg-surface-2 p-0.5',
                // Marcador de 2 px bajo la activa: para grupos que crecen y se
                // desplazan en horizontal, como los de Ajustes.
                line: 'gap-1 border-b border-line',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
);

function TabsList({ className, variant = 'default', ...props }) {
    return (
        <TabsPrimitive.List
            data-slot="tabs-list"
            data-variant={variant}
            className={cn(tabsListVariants({ variant }), className)}
            {...props}
        />
    );
}

function TabsTrigger({ className, ...props }) {
    return (
        <TabsPrimitive.Trigger
            data-slot="tabs-trigger"
            className={cn(
                "relative inline-flex shrink-0 items-center justify-center gap-1.5 rounded-control px-2.5 py-1 text-sm font-medium whitespace-nowrap transition-colors hover:text-chalk focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent disabled:pointer-events-none disabled:opacity-50 data-[state=active]:text-chalk [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
                'group-data-[variant=default]/tabs-list:data-[state=active]:bg-surface',
                // El marcador de la variante `line`: 2 px de volt. Es el unico
                // relleno de marca que una pantalla se puede permitir ademas del
                // de su accion principal.
                'group-data-[variant=line]/tabs-list:rounded-none group-data-[variant=line]/tabs-list:after:absolute group-data-[variant=line]/tabs-list:after:inset-x-0 group-data-[variant=line]/tabs-list:after:-bottom-px group-data-[variant=line]/tabs-list:after:h-0.5 group-data-[variant=line]/tabs-list:after:bg-volt group-data-[variant=line]/tabs-list:after:opacity-0 group-data-[variant=line]/tabs-list:data-[state=active]:after:opacity-100',
                className,
            )}
            {...props}
        />
    );
}

function TabsContent({ className, ...props }) {
    return <TabsPrimitive.Content data-slot="tabs-content" className={cn('flex-1 outline-none', className)} {...props} />;
}

export { Tabs, TabsContent, TabsList, TabsTrigger, tabsListVariants };
