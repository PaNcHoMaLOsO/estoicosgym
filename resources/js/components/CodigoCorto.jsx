import { useState } from 'react';
import { CheckIcon, CopyIcon } from 'lucide-react';

/**
 * El código corto de un pago o de una membresía (#E5E42D49), con un toque
 * para copiarlo: para nombrarlo en un WhatsApp o una boleta y encontrarlo
 * después escribiéndolo en el buscador de la lista.
 */
export default function CodigoCorto({ codigo }) {
    const [copiado, setCopiado] = useState(false);

    if (! codigo) {
        return null;
    }

    function copiar() {
        navigator.clipboard?.writeText(codigo).then(() => {
            setCopiado(true);
            setTimeout(() => setCopiado(false), 1500);
        }).catch(() => {});
    }

    return (
        <button
            type="button"
            onClick={copiar}
            title="Copiar el código"
            className="inline-flex items-center gap-1 rounded-control border border-line px-1.5 py-0.5 font-mono text-xs font-normal text-fog transition-colors hover:text-chalk"
        >
            {codigo}
            {copiado ? <CheckIcon className="size-3 text-volt" aria-hidden="true" /> : <CopyIcon className="size-3" aria-hidden="true" />}
            <span className="sr-only">{copiado ? 'Copiado' : 'Copiar'}</span>
        </button>
    );
}
