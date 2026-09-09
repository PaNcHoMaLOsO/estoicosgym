import * as TooltipPrimitive from '@radix-ui/react-tooltip';

import { cn } from '@/lib/utils';

/**
 * Pista. Copiado de shadcn/ui y adaptado al panel.
 *
 * Para que se usa aqui: disparadores que solo tienen icono (el ⋯ de una fila,
 * la hamburguesa) y celdas recortadas. OJO: una pista NO da nombre accesible.
 * Un boton de solo icono necesita ademas su `aria-label` o su `<span
 * className="sr-only">`; la pista es para el raton, el rotulo es para todos.
 *
 * Cambios sobre el original: fondo `chalk` con texto `on-ink` en vez de
 * foreground/background invertidos (que en tema oscuro dejaban la pista del
 * mismo color que la pagina) y sin `tw-animate-css`.
 */

function TooltipProvider({ delayDuration = 200, ...props }) {
    return <TooltipPrimitive.Provider data-slot="tooltip-provider" delayDuration={delayDuration} {...props} />;
}

function Tooltip({ ...props }) {
    return <TooltipPrimitive.Root data-slot="tooltip" {...props} />;
}

function TooltipTrigger({ ...props }) {
    return <TooltipPrimitive.Trigger data-slot="tooltip-trigger" {...props} />;
}

function TooltipContent({ className, sideOffset = 4, children, ...props }) {
    return (
        <TooltipPrimitive.Portal>
            <TooltipPrimitive.Content
                data-slot="tooltip-content"
                sideOffset={sideOffset}
                className={cn(
                    'z-50 w-fit origin-(--radix-tooltip-content-transform-origin) rounded-control bg-chalk px-2 py-1 text-xs text-balance text-page shadow-menu',
                    className,
                )}
                {...props}
            >
                {children}
                <TooltipPrimitive.Arrow className="z-50 size-2 translate-y-[calc(-50%_-_1px)] rotate-45 rounded-[2px] bg-chalk fill-chalk" />
            </TooltipPrimitive.Content>
        </TooltipPrimitive.Portal>
    );
}

export { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger };
