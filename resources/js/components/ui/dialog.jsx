import * as DialogPrimitive from '@radix-ui/react-dialog';
import { XIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

/**
 * Dialogo. Copiado de shadcn/ui (que a su vez es Radix) y adaptado al panel.
 *
 * QUE SE CAMBIO RESPECTO A LO QUE ESCUPE `shadcn add dialog`, y por que:
 *
 * 1. `bg-black/50` del velo pasa a `bg-scrim`. El negro fijo es de tema claro;
 *    el token cambia con el tema y no se lo puede saltar la marca.
 * 2. `rounded-lg` / `shadow-lg` pasan a `rounded-panel` / `shadow-overlay`: la
 *    forma y la elevacion del panel son 8 px y una sola sombra, no la escala de
 *    serie de Tailwind.
 * 3. Se quitaron las clases de `tw-animate-css` (animate-in, fade-in-0,
 *    zoom-in-95…). Ese paquete no esta instalado y su hoja tendria que
 *    importarse desde resources/css/app.css. El movimiento vive en
 *    resources/js/ui.css, colgado de estos mismos `data-slot`.
 * 4. `DialogFooter` ya no importa `Button`: el panel no instala el boton de
 *    shadcn porque ya tiene `.boton` con los tokens correctos, y tener dos
 *    sistemas de botones es justo la deriva que este bloque viene a matar.
 * 5. Los textos van en espanol.
 */

function Dialog({ ...props }) {
    return <DialogPrimitive.Root data-slot="dialog" {...props} />;
}

function DialogTrigger({ ...props }) {
    return <DialogPrimitive.Trigger data-slot="dialog-trigger" {...props} />;
}

function DialogPortal({ ...props }) {
    return <DialogPrimitive.Portal data-slot="dialog-portal" {...props} />;
}

function DialogClose({ ...props }) {
    return <DialogPrimitive.Close data-slot="dialog-close" {...props} />;
}

function DialogOverlay({ className, ...props }) {
    return (
        <DialogPrimitive.Overlay
            data-slot="dialog-overlay"
            className={cn('fixed inset-0 z-50 bg-scrim', className)}
            {...props}
        />
    );
}

function DialogContent({ className, children, showCloseButton = true, ...props }) {
    return (
        <DialogPortal data-slot="dialog-portal">
            <DialogOverlay />
            <DialogPrimitive.Content
                data-slot="dialog-content"
                className={cn(
                    'fixed top-1/2 left-1/2 z-50 grid max-h-[calc(100dvh-2rem)] w-full max-w-[calc(100%-2rem)] -translate-x-1/2 -translate-y-1/2 gap-4 overflow-y-auto rounded-panel border border-line bg-raise p-4 shadow-overlay outline-none sm:max-w-md',
                    className,
                )}
                {...props}
            >
                {children}
                {showCloseButton && (
                    <DialogPrimitive.Close
                        data-slot="dialog-close"
                        className="absolute top-3 right-3 rounded-control p-1 text-fog transition-colors hover:bg-surface-2 hover:text-chalk focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0"
                    >
                        <XIcon />
                        <span className="sr-only">Cerrar</span>
                    </DialogPrimitive.Close>
                )}
            </DialogPrimitive.Content>
        </DialogPortal>
    );
}

function DialogHeader({ className, ...props }) {
    return <div data-slot="dialog-header" className={cn('flex flex-col gap-1 pr-6', className)} {...props} />;
}

function DialogFooter({ className, ...props }) {
    return (
        <div
            data-slot="dialog-footer"
            className={cn('flex flex-col-reverse gap-2 sm:flex-row sm:justify-end', className)}
            {...props}
        />
    );
}

function DialogTitle({ className, ...props }) {
    return (
        <DialogPrimitive.Title
            data-slot="dialog-title"
            className={cn('titulo-panel text-chalk', className)}
            {...props}
        />
    );
}

function DialogDescription({ className, ...props }) {
    return (
        <DialogPrimitive.Description
            data-slot="dialog-description"
            className={cn('apoyo text-fog', className)}
            {...props}
        />
    );
}

export {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogOverlay,
    DialogPortal,
    DialogTitle,
    DialogTrigger,
};
