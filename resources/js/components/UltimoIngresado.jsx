import { Link } from '@inertiajs/react';
import { ClockIcon } from 'lucide-react';

import { Reservado } from '@/Privado';

/**
 * «Último ingresado: Camila Rojas · 21.410.708-2 · hace 5 minutos.»
 *
 * Siempre arriba de la lista: después de registrar a alguien se ve que quedó,
 * y al empezar el turno se ve qué fue lo último que se hizo. Lleva a su ficha.
 */
export default function UltimoIngresado({ titulo, ultimo }) {
    if (! ultimo) {
        return null;
    }

    return (
        <p className="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-control border border-line bg-surface px-3 py-2 text-sm">
            <ClockIcon className="size-4 shrink-0 text-fog" aria-hidden="true" />
            <span className="text-fog">{titulo}:</span>
            <Link href={ultimo.href} className="font-medium text-chalk underline-offset-2 hover:underline">
                {ultimo.que}
            </Link>
            {ultimo.detalle ? (
                <span className="text-fog">
                    · {ultimo.monto !== undefined ? <Reservado ancho="w-16">{ultimo.detalle}</Reservado> : ultimo.detalle}
                </span>
            ) : null}
            {ultimo.hace ? (
                <span className="text-fog" title={ultimo.cuando}>
                    · {ultimo.hace}
                </span>
            ) : null}
        </p>
    );
}
