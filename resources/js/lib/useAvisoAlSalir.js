import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

import { confirmar } from '@/components/Confirmar';

/**
 * Pregunta antes de salir de un formulario a medio llenar.
 *
 * Un clic en el menú a mitad de un alta y se perdía todo lo escrito, sin
 * aviso. Ahora, si se tocó algo y quedó sin guardar, pregunta. No pregunta:
 *
 *  · si no se tocó nada (la pantalla que se completa sola al abrirse no cuenta);
 *  · al guardar (no es irse);
 *  · en las búsquedas dentro de la misma pantalla;
 *  · con los botones marcados `data-sin-aviso`, que llevan a otra parte a
 *    propósito («abrir su ficha» cuando el RUT ya existe).
 *
 * Uso:
 *
 *     const tocar = useAvisoAlSalir(isDirty && ! processing);
 *     <form onSubmit={…} {...tocar}>
 */
export default function useAvisoAlSalir(hayCambios) {
    const [tocado, setTocado] = useState(false);
    const confirmada = useRef(false);
    const activo = tocado && hayCambios;

    useEffect(() => {
        if (! activo) {
            return undefined;
        }

        const quitar = router.on('before', (evento) => {
            const visita = evento.detail.visit;

            if (confirmada.current || String(visita.method).toLowerCase() !== 'get') {
                return;
            }

            // Buscar o filtrar sin salir de la pantalla no es irse.
            if (visita.only?.length || visita.url?.pathname === window.location.pathname) {
                return;
            }

            evento.preventDefault();

            confirmar({
                titulo: '¿Salir sin guardar?',
                mensaje: 'Lo que llevas escrito en este formulario se pierde.',
                confirmar: 'Salir sin guardar',
                cancelar: 'Seguir aquí',
                peligrosa: true,
            }).then((salir) => {
                if (salir) {
                    confirmada.current = true;
                    router.visit(visita.url.href);
                }
            });
        });

        // Cerrar o recargar la pestaña: ahí solo sirve el cuadro del navegador.
        const alCerrar = (e) => {
            e.preventDefault();
            e.returnValue = '';
        };
        window.addEventListener('beforeunload', alCerrar);

        return () => {
            quitar();
            window.removeEventListener('beforeunload', alCerrar);
        };
    }, [activo]);

    const marcar = () => setTocado(true);

    return {
        onInputCapture: marcar,
        onClickCapture: (e) => {
            // «Abrir su ficha» cuando el RUT ya existe: irse es lo que se eligió.
            if (e.target.closest?.('[data-sin-aviso]')) {
                confirmada.current = true;

                return;
            }

            marcar();
        },
    };
}
