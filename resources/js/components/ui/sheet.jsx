import * as SheetPrimitive from '@radix-ui/react-dialog';
import { XIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

/**
 * Cajon lateral. Copiado de shadcn/ui y podado a lo que usa el panel.
 *
 * Solo entra por la IZQUIERDA a proposito: su unico disparador es la
 * hamburguesa de la cabecera movil, que esta en ese borde. Un cajon que sale
 * del lado contrario al dedo que lo llama se lee como otra cosa. Los cuatro
 * lados de shadcn se quitaron en vez de dejarlos sin usar, para que nadie los
 * herede sin querer.
 *
 * Los cambios sobre el original son los mismos que en dialog.jsx: velo con
 * token `scrim`, sombra `overlay`, sin `tw-animate-css` (el movimiento vive en
 * resources/js/ui.css) y textos en espanol.
 */

function Sheet({ ...props }) {
    return <SheetPrimitive.Root data-slot="sheet" {...props} />;
}

function SheetTrigger({ ...props }) {
    return <SheetPrimitive.Trigger data-slot="sheet-trigger" {...props} />;
}

function SheetClose({ ...props }) {
    return <SheetPrimitive.Close data-slot="sheet-close" {...props} />;
}

function SheetPortal({ ...props }) {
    return <SheetPrimitive.Portal data-slot="sheet-portal" {...props} />;
}

function SheetOverlay({ className, ...props }) {
    return (
        <SheetPrimitive.Overlay
            data-slot="sheet-overlay"
            className={cn('fixed inset-0 z-50 bg-scrim', className)}
            {...props}
        />
    );
}

function SheetContent({ className, children, showCloseButton = true, ...props }) {
    return (
        <SheetPortal>
            <SheetOverlay />
            <SheetPrimitive.Content
                data-slot="sheet-content"
                className={cn(
                    'fixed inset-y-0 left-0 z-50 flex h-dvh w-[17rem] max-w-[85vw] flex-col border-r border-line bg-raise shadow-overlay outline-none',
                    className,
                )}
                {...props}
            >
                {children}
                {showCloseButton && (
                    <SheetPrimitive.Close className="absolute top-2.5 right-2.5 rounded-control p-1 text-fog transition-colors hover:bg-surface-2 hover:text-chalk focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent">
                        <XIcon className="size-4" />
                        <span className="sr-only">Cerrar</span>
                    </SheetPrimitive.Close>
                )}
            </SheetPrimitive.Content>
        </SheetPortal>
    );
}

function SheetHeader({ className, ...props }) {
    return (
        <div
            data-slot="sheet-header"
            className={cn('flex flex-col gap-1 border-b border-line px-3 py-2.5 pr-10', className)}
            {...props}
        />
    );
}

function SheetFooter({ className, ...props }) {
    return (
        <div
            data-slot="sheet-footer"
            className={cn('mt-auto flex flex-col gap-2 border-t border-line px-3 py-2.5', className)}
            {...props}
        />
    );
}

function SheetTitle({ className, ...props }) {
    return (
        <SheetPrimitive.Title data-slot="sheet-title" className={cn('titulo-panel text-chalk', className)} {...props} />
    );
}

function SheetDescription({ className, ...props }) {
    return (
        <SheetPrimitive.Description
            data-slot="sheet-description"
            className={cn('apoyo text-fog', className)}
            {...props}
        />
    );
}

export { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetPortal, SheetTitle, SheetTrigger };
