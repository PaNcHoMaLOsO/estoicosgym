import { Head } from '@inertiajs/react';
import { InfoIcon } from 'lucide-react';

import ChatWhatsapp from '@/components/ChatWhatsapp';

/**
 * El chat con los socios, a pantalla completa.
 *
 * La misma pieza que va en la columna lateral del resumen, con sitio para ver
 * la lista y la conversación a la vez. Por ahora es una maqueta: no manda ni
 * recibe nada, y sirve para decidir por dónde se conecta WhatsApp.
 */
export default function Whatsapp({ conversaciones, plantillas, esMaqueta }) {
    return (
        <>
            <Head title="WhatsApp" />

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">WhatsApp</h1>
                <p className="apoyo text-fog">Escribirle a un socio sin salir del panel</p>
            </header>

            {esMaqueta ? (
                <div className="mb-4 flex items-start gap-2 rounded-panel border border-warn/40 bg-warn/5 px-4 py-3">
                    <InfoIcon className="mt-0.5 size-4 shrink-0 text-warn" aria-hidden="true" />
                    <p className="text-sm text-warn">
                        Esto es una maqueta para decidir: <span className="font-medium">no manda ni recibe nada</span>.
                        Las conversaciones son de mentira, con socios de verdad. Cuando elijas por dónde se conecta
                        WhatsApp, esta misma pantalla empieza a funcionar.
                    </p>
                </div>
            ) : null}

            <ChatWhatsapp conversaciones={conversaciones} plantillas={plantillas} esMaqueta={false} />
        </>
    );
}
