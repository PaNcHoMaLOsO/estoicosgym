import { useCallback, useEffect, useRef, useState } from 'react';
import { MessageCircleIcon, MoveIcon, XIcon } from 'lucide-react';

/**
 * WhatsApp Web encajado en un recuadro del resumen.
 *
 * CÓMO FUNCIONA, PORQUE NO ES LO QUE PARECE. WhatsApp no se puede meter dentro
 * de una página: manda «frame-ancestors https://*.whatsapp.com» y el navegador
 * bloquea cualquier marco. Lo que se hace aquí es otra cosa: se abre WhatsApp
 * Web en una ventana propia y se la coloca EXACTAMENTE encima de este recuadro,
 * del mismo tamaño. A la vista queda dentro del panel; por debajo son dos
 * ventanas, y WhatsApp se carga tal cual, sin tocarle ninguna protección.
 *
 * LO QUE NO SE PUEDE: que se quede encima. Windows no deja que una página web
 * mantenga otra ventana al frente, así que al hacer clic en el panel la de
 * WhatsApp se va detrás. Para eso está «Traer al frente», que además la vuelve
 * a calzar si quedó corrida.
 *
 * La posición se calcula con las medidas de la propia ventana del navegador:
 * `outerWidth - innerWidth` son los bordes, y `outerHeight - innerHeight`, la
 * barra de direcciones y las pestañas. Sale al píxel casi siempre; con zoom del
 * navegador puede bailar un poco, y por eso el botón de recolocar está a mano.
 */
export default function VentanaWhatsapp() {
    const caja = useRef(null);
    const ventana = useRef(null);
    const [abierta, setAbierta] = useState(false);

    /** Dónde está el recuadro dentro de la pantalla, en píxeles de verdad. */
    const donde = useCallback(() => {
        const r = caja.current?.getBoundingClientRect();

        if (! r) {
            return null;
        }

        const bordes = (window.outerWidth - window.innerWidth) / 2;
        const cabecera = window.outerHeight - window.innerHeight - bordes;

        return {
            left: Math.round((window.screenX ?? window.screenLeft ?? 0) + bordes + r.left),
            top: Math.round((window.screenY ?? window.screenTop ?? 0) + cabecera + r.top),
            width: Math.round(r.width),
            height: Math.round(r.height),
        };
    }, []);

    const colocar = useCallback(() => {
        const sitio = donde();

        if (! sitio || ! ventana.current || ventana.current.closed) {
            return;
        }

        ventana.current.moveTo(sitio.left, sitio.top);
        ventana.current.resizeTo(sitio.width, sitio.height);
        ventana.current.focus();
    }, [donde]);

    function abrir() {
        const sitio = donde();

        if (! sitio) {
            return;
        }

        if (ventana.current && ! ventana.current.closed) {
            colocar();

            return;
        }

        ventana.current = window.open(
            'https://web.whatsapp.com/',
            // El nombre hace que sea SIEMPRE la misma ventana: sin él, cada
            // clic abriría otra y acabarían diez encima del recuadro.
            'progym-whatsapp',
            `popup=yes,noopener,noreferrer,width=${sitio.width},height=${sitio.height},left=${sitio.left},top=${sitio.top}`,
        );

        setAbierta(Boolean(ventana.current));
    }

    function cerrar() {
        ventana.current?.close();
        ventana.current = null;
        setAbierta(false);
    }

    // Si se mueve o se cambia de tamaño la ventana del panel, la de WhatsApp
    // deja de calzar: se recoloca sola cuando eso pasa.
    useEffect(() => {
        if (! abierta) {
            return undefined;
        }

        const seguir = () => colocar();
        window.addEventListener('resize', seguir);

        // Y se entera de si la cerraron desde su propia X.
        const mirar = setInterval(() => {
            if (ventana.current?.closed) {
                ventana.current = null;
                setAbierta(false);
            }
        }, 1500);

        return () => {
            window.removeEventListener('resize', seguir);
            clearInterval(mirar);
        };
    }, [abierta, colocar]);

    // Al salir del panel, la ventana se queda: es una ventana del navegador,
    // no parte de esta pantalla, y cerrarla sola perdería la conversación.

    return (
        <section className="overflow-hidden rounded-panel border border-line bg-surface">
            <div className="flex items-center justify-between gap-2 px-3 py-2">
                <h2 className="rotulo">WhatsApp</h2>

                {abierta ? (
                    <span className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={colocar}
                            title="Vuelve a calzarla sobre el recuadro y la trae adelante"
                            className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                        >
                            <MoveIcon className="size-3.5" aria-hidden="true" />
                            Traer al frente
                        </button>
                        <button
                            type="button"
                            onClick={cerrar}
                            aria-label="Cerrar WhatsApp"
                            className="rounded-control p-1 text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                        >
                            <XIcon className="size-3.5" aria-hidden="true" />
                        </button>
                    </span>
                ) : null}
            </div>

            {/*
              * EL RECUADRO. Aquí no se pinta WhatsApp: encima de este hueco se
              * coloca su ventana. Por eso tiene alto fijo, y por eso lo que se
              * ve debajo explica qué está pasando cuando la ventana no está.
              */}
            <div
                ref={caja}
                className="relative h-[32rem] border-t border-line bg-surface-2/40"
            >
                {! abierta ? (
                    <div className="flex h-full flex-col items-center justify-center gap-3 px-6 text-center">
                        <MessageCircleIcon className="size-8 text-fog" aria-hidden="true" />
                        <p className="text-sm text-chalk">WhatsApp Web, aquí mismo</p>
                        <p className="apoyo max-w-md text-fog">
                            Se abre en su propia ventana y se coloca justo sobre este recuadro. WhatsApp no permite
                            mostrarse dentro de otra página, así que esta es la forma de tenerlo a la vista sin
                            tocar su seguridad.
                        </p>
                        <button
                            type="button"
                            onClick={abrir}
                            className="inline-flex items-center gap-2 rounded-control bg-[#25D366] px-4 py-2 text-sm font-medium text-black transition-opacity hover:opacity-90"
                        >
                            <MessageCircleIcon className="size-4" aria-hidden="true" />
                            Abrir WhatsApp aquí
                        </button>
                    </div>
                ) : (
                    <div className="flex h-full flex-col items-center justify-center gap-2 px-6 text-center">
                        <p className="apoyo text-fog">
                            WhatsApp está abierto sobre este recuadro. Si trabajas en el panel se irá detrás:
                            aprieta «Traer al frente» para volver a verlo.
                        </p>
                    </div>
                )}
            </div>
        </section>
    );
}
