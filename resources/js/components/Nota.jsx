import { AlertTriangleIcon, CheckCircle2Icon, InfoIcon, XCircleIcon } from 'lucide-react';

/**
 * Un recuadro que avisa algo dentro de la pantalla: «este socio ya existe»,
 * «no se pudo enviar», «queda debiendo».
 *
 * UNO SOLO PARA TODO EL PANEL. Antes cada pantalla armaba el suyo con las mismas
 * clases copiadas, y no dos iguales: unos con icono y otros sin él, unos con
 * todo el texto teñido de amarillo (que cansa de leer) y otros no.
 *
 * Cómo se lee: una raya de color a la izquierda y el icono dicen de qué tipo es;
 * el texto va en el color normal, que es el que se lee bien. El fondo lleva
 * solo un tinte, para que no grite más que el contenido de al lado.
 */
const TONOS = {
    ok: { Icono: CheckCircle2Icon, raya: 'border-l-ok', icono: 'text-ok', fondo: 'bg-ok/5' },
    aviso: { Icono: AlertTriangleIcon, raya: 'border-l-warn', icono: 'text-warn', fondo: 'bg-warn/5' },
    peligro: { Icono: XCircleIcon, raya: 'border-l-danger', icono: 'text-danger', fondo: 'bg-danger/5' },
    dato: { Icono: InfoIcon, raya: 'border-l-info', icono: 'text-info', fondo: 'bg-info/5' },
};

export default function Nota({ tono = 'aviso', titulo, children, accion, Icono, className = '', compacta = false }) {
    const t = TONOS[tono] ?? TONOS.aviso;
    const Dibujo = Icono ?? t.Icono;

    return (
        <div
            role={tono === 'peligro' ? 'alert' : 'status'}
            className={`flex items-start gap-2.5 rounded-panel border border-line border-l-[3px] ${t.raya} ${t.fondo} ${compacta ? 'px-3 py-2' : 'px-3.5 py-3'} ${className}`}
        >
            <Dibujo className={`mt-0.5 size-4 shrink-0 ${t.icono}`} aria-hidden="true" />

            <div className="min-w-0 flex-1 text-sm text-chalk">
                {titulo ? <p className="font-medium">{titulo}</p> : null}
                <div className={titulo ? 'apoyo mt-0.5 text-fog' : ''}>{children}</div>
            </div>

            {accion ? <div className="shrink-0 self-center">{accion}</div> : null}
        </div>
    );
}
