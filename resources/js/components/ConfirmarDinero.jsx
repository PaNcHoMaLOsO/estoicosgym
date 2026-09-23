import { router } from '@inertiajs/react';
import { BanknoteIcon, Trash2Icon } from 'lucide-react';
import { useState } from 'react';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/**
 * Confirmar algo que mueve dinero.
 *
 * POR QUE EXISTE, y por que no vale con el dialogo generico: el error de estos
 * botones es SIEMPRE el mismo —pulsar en la fila de al lado—, y un «¿seguro?»
 * no lo evita, porque quien se equivoco de fila tambien va a decir que si. Lo
 * que lo evita es que el aviso diga EL NOMBRE Y LA CANTIDAD: ahi es donde uno
 * se da cuenta de que no era ese.
 *
 * Por eso el monto va grande y arriba, y no escondido en una frase.
 */
export default function ConfirmarDinero({
    abierto,
    alCerrar,
    titulo,
    quien,
    monto,
    detalle,
    consecuencia,
    etiquetaConfirmar = 'Confirmar',
    peligrosa = false,
    accion,
    metodo = 'post',
    datos = {},
}) {
    const [enviando, setEnviando] = useState(false);

    function confirmar() {
        setEnviando(true);

        router[metodo](accion, datos, {
            preserveScroll: true,
            onFinish: () => {
                setEnviando(false);
                alCerrar();
            },
        });
    }

    return (
        <Dialog open={abierto} onOpenChange={(v) => (! v && ! enviando ? alCerrar() : null)}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader className="flex-row items-start gap-3">
                    {/* El icono en su circulo dice de que va antes de leer: rojo es
                        borrar o quitar, azul es una accion normal. */}
                    <span
                        className={`mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full ${
                            peligrosa ? 'bg-danger/10 text-danger' : 'bg-info/10 text-info'
                        }`}
                        aria-hidden="true"
                    >
                        {peligrosa ? <Trash2Icon className="size-4" /> : <BanknoteIcon className="size-4" />}
                    </span>
                    <div className="min-w-0 flex-1">
                        <DialogTitle>{titulo}</DialogTitle>
                        {consecuencia ? <DialogDescription className="mt-1">{consecuencia}</DialogDescription> : null}
                    </div>
                </DialogHeader>

                {/* EL NOMBRE Y LA CANTIDAD, grandes. Es lo unico que hace que
                    alguien note que se equivoco de fila. */}
                <div className={`rounded-panel border p-3 ${peligrosa ? 'border-danger/30 bg-danger/5' : 'border-line bg-surface-2'}`}>
                    <p className="text-sm font-medium text-chalk">{quien}</p>
                    <p className="mt-0.5 text-2xl font-semibold tabular-nums text-chalk">
                        {pesos.format(monto)}
                    </p>

                    {detalle?.length ? (
                        <ul className="apoyo mt-2 space-y-0.5 border-t border-line pt-2 text-fog">
                            {detalle.map((d, i) => (
                                <li key={i} className="flex justify-between gap-2">
                                    <span className="min-w-0 truncate">{d.concepto}</span>
                                    <span className="shrink-0 tabular-nums">{pesos.format(d.monto)}</span>
                                </li>
                            ))}
                        </ul>
                    ) : null}
                </div>

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
                        disabled={enviando}
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
