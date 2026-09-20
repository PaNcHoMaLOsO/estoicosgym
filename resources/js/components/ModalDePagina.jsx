import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';

// Las mismas pantallas que resuelve app.jsx: Vite las parte una sola vez.
const paginas = import.meta.glob('../pages/**/*.jsx');

/**
 * Una pantalla del panel abierta como ventana, ENCIMA de donde se está.
 *
 * Los accesos rápidos del Resumen (nuevo socio, cobrar, inscribir, fiado, canje)
 * sacaban al usuario del Resumen para cada cosa, y al terminar había que volver.
 * Aquí la pantalla de siempre, con sus islas, se abre en una ventana: se
 * resuelve y se sigue donde se estaba.
 *
 * NO HAY UNA SEGUNDA VERSIÓN DE CADA FORMULARIO. Se le pide al servidor la misma
 * página por el protocolo de Inertia (cabecera X-Inertia: responde el nombre del
 * componente y sus datos en JSON) y se pinta ese componente aquí dentro. Así el
 * alta en ventana y el alta en página son el mismo código y no pueden divergir.
 * La dirección de cada pantalla sigue existiendo: con Ctrl+clic se abre entera.
 *
 * Si algo falla al cargarla —versión de los archivos cambiada, sesión vencida—
 * se va a la página normal, que es lo que pasaba antes.
 */
export default function ModalDePagina({ href, titulo, descripcion, conCabecera = false, alCerrar }) {
    const { version } = usePage();
    const [pagina, setPagina] = useState(null);

    useEffect(() => {
        let vigente = true;

        const cargar = async () => {
            try {
                const r = await fetch(href, {
                    headers: {
                        Accept: 'text/html, application/xhtml+xml',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Inertia': 'true',
                        'X-Inertia-Version': version ?? '',
                    },
                    credentials: 'same-origin',
                });

                if (! r.ok || ! r.headers.get('X-Inertia')) {
                    throw new Error('no es una respuesta de Inertia');
                }

                const datos = await r.json();
                const modulo = await paginas[`../pages/${datos.component}.jsx`]();

                if (vigente) {
                    setPagina({ Componente: modulo.default, props: datos.props });
                }
            } catch {
                if (vigente) {
                    router.visit(href);
                }
            }
        };

        cargar();

        /*
         * MIENTRAS LA VENTANA ESTÁ ABIERTA, un formulario rechazado no puede
         * cerrarla. El servidor responde a una validación fallida volviendo a la
         * página de atrás —el Resumen—, e Inertia por defecto la monta de nuevo:
         * la ventana desaparecería con todo lo escrito. Con «errors» el estado se
         * conserva solo cuando hay errores; si se guardó bien, se sigue normal.
         */
        const soltarAntes = router.on('before', (evento) => {
            const visita = evento.detail.visit;
            if (visita.method !== 'get') {
                visita.preserveState = 'errors';
                visita.preserveScroll = true;
            }
        });

        // Lo que se guarda desde dentro sin salir (anotar un fiado, una entrada
        // por canje) deja viejos los datos de la ventana: se vuelven a pedir.
        const soltarDespues = router.on('success', cargar);

        return () => {
            vigente = false;
            soltarAntes();
            soltarDespues();
        };
    }, [href, version]);

    // «Cancelar» dentro de la pantalla es un enlace a su listado: aquí cierra la
    // ventana, que es lo que se espera de un Cancelar dentro de una ventana.
    const alPulsar = (e) => {
        const enlace = e.target.closest('a');
        if (enlace && enlace.textContent.trim() === 'Cancelar') {
            e.preventDefault();
            e.stopPropagation();
            alCerrar();
        }
    };

    return (
        <Dialog open onOpenChange={(abierto) => (abierto ? null : alCerrar())}>
            <DialogContent
                className="gap-3 bg-page p-3 sm:max-w-5xl sm:p-5"
                // Un clic fuera no cierra: se perdería medio formulario por un
                // dedo mal puesto. Se cierra con la X, con Escape o con Cancelar.
                onInteractOutside={(e) => e.preventDefault()}
            >
                <DialogHeader>
                    <DialogTitle>{titulo}</DialogTitle>
                    {descripcion ? <DialogDescription>{descripcion}</DialogDescription> : <DialogDescription className="sr-only">{titulo}</DialogDescription>}
                </DialogHeader>

                {pagina ? (
                    <div
                        onClickCapture={alPulsar}
                        className={
                            conCabecera
                                ? 'min-w-0 [&>header_a[aria-label^=Volver]]:hidden'
                                : 'min-w-0 [&>header:first-of-type]:hidden'
                        }
                    >
                        <pagina.Componente {...pagina.props} />
                    </div>
                ) : (
                    <p className="apoyo py-10 text-center text-fog">Cargando…</p>
                )}
            </DialogContent>
        </Dialog>
    );
}
