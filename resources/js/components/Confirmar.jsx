import { AlertTriangleIcon, HelpCircleIcon } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

/**
 * «¿Seguro?» antes de lo que no se deshace fácil.
 *
 * Había ocho sitios que preguntaban con el cuadro gris del navegador —que en
 * el celular tapa la pantalla y no dice nada de la marca— y más de diez que no
 * preguntaban nada: reabrir un mes de taller borraba su cobro de un clic.
 * Ahora todos usan esta ventana, con el mismo aspecto que el resto del panel.
 *
 * Se usa sin montar nada en cada pantalla:
 *
 *     if (await confirmar({ titulo: '¿Borrar la nota?', confirmar: 'Borrar', peligrosa: true })) { … }
 *
 * La ventana vive una vez en el Layout (<VentanaDeConfirmar />).
 */
let abrir = null;

export function confirmar(opciones) {
    // Sin la ventana montada (una página suelta), el cuadro del navegador.
    if (! abrir) {
        return Promise.resolve(window.confirm([opciones.titulo, opciones.mensaje].filter(Boolean).join('\n\n')));
    }

    return new Promise((resolver) => abrir({ ...opciones, resolver }));
}

export function VentanaDeConfirmar() {
    const [pregunta, setPregunta] = useState(null);
    const botonSi = useRef(null);

    useEffect(() => {
        abrir = setPregunta;

        return () => {
            abrir = null;
        };
    }, []);

    function responder(si) {
        pregunta?.resolver(si);
        setPregunta(null);
    }

    const peligrosa = Boolean(pregunta?.peligrosa);
    const Icono = peligrosa ? AlertTriangleIcon : HelpCircleIcon;

    return (
        <Dialog open={pregunta !== null} onOpenChange={(abierta) => (abierta ? null : responder(false))}>
            <DialogContent
                className="sm:max-w-md"
                onOpenAutoFocus={(e) => {
                    // Con el foco en «Cancelar» si es peligrosa: un Enter de más no borra nada.
                    if (! peligrosa) {
                        e.preventDefault();
                        botonSi.current?.focus();
                    }
                }}
            >
                <DialogHeader className="flex-row items-start gap-3">
                    <span
                        className={`mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full ${
                            peligrosa ? 'bg-danger/10 text-danger' : 'bg-info/10 text-info'
                        }`}
                        aria-hidden="true"
                    >
                        <Icono className="size-4" />
                    </span>
                    <div className="min-w-0 flex-1">
                        <DialogTitle>{pregunta?.titulo}</DialogTitle>
                        {pregunta?.mensaje ? <DialogDescription className="mt-1">{pregunta.mensaje}</DialogDescription> : null}
                    </div>
                </DialogHeader>

                <DialogFooter>
                    <button
                        type="button"
                        onClick={() => responder(false)}
                        className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        {pregunta?.cancelar ?? 'Cancelar'}
                    </button>
                    <button
                        ref={botonSi}
                        type="button"
                        onClick={() => responder(true)}
                        className={`rounded-control px-3 py-1.5 text-sm font-medium transition-opacity hover:opacity-90 ${
                            peligrosa ? 'bg-danger text-white' : 'bg-volt text-on-volt'
                        }`}
                    >
                        {pregunta?.confirmar ?? 'Sí, seguir'}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
