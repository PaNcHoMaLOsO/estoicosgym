import { MessageCircleIcon } from 'lucide-react';

/**
 * WhatsApp Web al lado del panel.
 *
 * NO SE PUEDE METER DENTRO DE LA PÁGINA. WhatsApp prohíbe que su web se cargue
 * dentro de otra (manda la cabecera `frame-ancestors 'none'`): un marco aquí
 * dentro saldría en blanco. No es una limitación de este sistema ni algo que se
 * arregle con más código; es una decisión de WhatsApp, y saltársela sería
 * pelear con su seguridad.
 *
 * Lo que sí se puede, y es lo que hace este botón: abrirlo en una ventana
 * propia, SIEMPRE LA MISMA. Se abre una vez, se deja al lado de la del panel
 * —en Windows, tecla de Windows + flecha— y desde ahí queda a un clic. Si ya
 * está abierta, este botón la trae al frente en vez de abrir otra, que es lo
 * que pasaba al apretar cada enlace de WhatsApp de las listas.
 *
 * `noopener` a propósito aunque se pierda el foco en algunos navegadores: sin
 * él, la pestaña de WhatsApp puede tocar la del panel a través de
 * `window.opener`.
 */
export default function PanelWhatsapp({ className = '' }) {
    function abrir() {
        const alto = Math.min(960, window.screen.availHeight - 40);
        const ancho = 520;
        const izquierda = Math.max(0, window.screen.availWidth - ancho);

        const ventana = window.open(
            'https://web.whatsapp.com/',
            // El nombre es lo que hace que sea SIEMPRE la misma ventana.
            'progym-whatsapp',
            `noopener,noreferrer,width=${ancho},height=${alto},left=${izquierda},top=0`,
        );

        ventana?.focus();
    }

    return (
        <button
            type="button"
            onClick={abrir}
            title="Abre WhatsApp Web en una ventana aparte, para dejarla al lado del panel"
            className={`inline-flex items-center gap-2 rounded-control border border-[#25D366]/40 bg-[#25D366]/10 px-2.5 py-1.5 text-sm font-medium text-[#25D366] transition-colors hover:bg-[#25D366]/20 ${className}`}
        >
            <MessageCircleIcon className="size-4" aria-hidden="true" />
            WhatsApp
        </button>
    );
}
