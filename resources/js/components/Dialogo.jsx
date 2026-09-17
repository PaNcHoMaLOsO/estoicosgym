import { router } from '@inertiajs/react';
import { useState } from 'react';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

/**
 * Diálogo para una acción que se hace y ya: pausar, reanudar, traspasar.
 *
 * Las cuatro acciones de membresía viven en Admin\InscripcionController y
 * responden JSON, así que se llaman con fetch en vez de con un formulario de
 * Inertia. Al terminar bien se recarga la página para que la ficha refleje el
 * estado nuevo; el error del servidor se enseña DENTRO del diálogo, sin cerrarlo,
 * porque casi siempre se arregla cambiando un dato de ahí mismo.
 */
export default function Dialogo({
    abierto,
    alCerrar,
    titulo,
    descripcion,
    accion,
    datos = {},
    etiquetaConfirmar = 'Confirmar',
    peligrosa = false,
    puedeConfirmar = true,
    /*
     * Como se llama al servidor.
     *
     * 'json' es lo de siempre: las cuatro acciones de membresia viven en
     * Admin\InscripcionController y responden JSON.
     *
     * 'inertia' es para las rutas del panel nuevo, que responden con una
     * redireccion y su aviso. Ahi NO sirve el fetch de arriba: seguiria la
     * redireccion por su cuenta y se comeria el aviso, asi que la pantalla
     * volveria sin decir si salio bien.
     */
    via = 'json',
    metodo = 'patch',
    children,
}) {
    const [enviando, setEnviando] = useState(false);
    const [error, setError] = useState(null);

    function confirmarPorInertia() {
        setEnviando(true);

        router[metodo](accion, datos, {
            preserveScroll: true,
            onFinish: () => {
                setEnviando(false);
                alCerrar();
            },
        });
    }

    async function confirmar() {
        if (via === 'inertia') {
            confirmarPorInertia();

            return;
        }

        setEnviando(true);
        setError(null);

        try {
            const respuesta = await fetch(accion, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                },
                body: JSON.stringify(datos),
            });

            const cuerpo = await respuesta.json().catch(() => ({}));

            if (! respuesta.ok || cuerpo.success === false) {
                // El servidor manda el motivo en `message` cuando es una regla de
                // negocio, y en `errors` cuando es validación de un campo.
                const deCampos = cuerpo.errors
                    ? Object.values(cuerpo.errors).flat().join(' ')
                    : null;

                setError(cuerpo.message || deCampos || 'No se pudo completar la acción.');
                setEnviando(false);

                return;
            }

            // Recargar en vez de tocar el estado a mano: la acción cambia el
            // estado de la membresía, sus fechas y a veces crea otra inscripción.
            // Adivinar todo eso en el cliente es garantía de que se desincronice.
            router.reload({ onFinish: () => setEnviando(false) });
            alCerrar();
        } catch (e) {
            setError('No se pudo contactar con el servidor.');
            setEnviando(false);
        }
    }

    return (
        <Dialog open={abierto} onOpenChange={(v) => (! v && ! enviando ? alCerrar() : null)}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{titulo}</DialogTitle>
                    {descripcion ? <DialogDescription>{descripcion}</DialogDescription> : null}
                </DialogHeader>

                {children ? <div className="space-y-3">{children}</div> : null}

                {error ? (
                    <p
                        role="alert"
                        className="rounded-control border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger"
                    >
                        {error}
                    </p>
                ) : null}

                <DialogFooter>
                    <button
                        type="button"
                        onClick={alCerrar}
                        disabled={enviando}
                        className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        onClick={confirmar}
                        disabled={enviando || ! puedeConfirmar}
                        className={`rounded-control px-3 py-1.5 text-sm font-medium transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 ${
                            peligrosa ? 'bg-danger text-white' : 'bg-volt text-on-volt'
                        }`}
                    >
                        {enviando ? 'Un momento…' : etiquetaConfirmar}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
