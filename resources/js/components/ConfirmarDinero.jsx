import { router, usePage } from '@inertiajs/react';
import { BanknoteIcon, Trash2Icon } from 'lucide-react';
import { useEffect, useState } from 'react';

import { Botones, metodoPorDefecto } from '@/components/Cobro';
import TextoQueCambia from '@/components/TextoQueCambia';

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
    // Pedir con qué se pagó: el cobro del fiado. Viaja como `id_metodo_pago`.
    conMedio = false,
    // Dejar que pague menos que el total (un abono). Viaja como `monto`, y
    // solo si es menos que todo.
    conAbono = false,
}) {
    const [enviando, setEnviando] = useState(false);
    const medios = usePage().props.medios_de_pago ?? [];
    const [medio, setMedio] = useState('');
    const [errores, setErrores] = useState([]);
    const [trae, setTrae] = useState('');
    const abona = conAbono && trae !== '' && Number(trae) > 0 && Number(trae) < monto;
    const pasaDelTotal = conAbono && Number(trae) > monto;

    // Cada vez que se abre parte en efectivo, lo más común en el mesón.
    useEffect(() => {
        if (abierto) {
            setErrores([]);
            setTrae('');
        }

        if (abierto && conMedio) {
            setMedio(metodoPorDefecto(medios));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [abierto, conMedio]);

    function confirmar() {
        setEnviando(true);

        const enviar = { ...datos, ...(conMedio ? { id_metodo_pago: medio } : {}), ...(abona ? { monto: Number(trae) } : {}) };

        router[metodo](accion, enviar, {
            preserveScroll: true,
            // SE CIERRA SOLO SI SALIÓ. Cerrándose siempre, un medio de pago que
            // ya no existe o una línea que no vale se tragaban el error: el
            // diálogo desaparecía y la cuenta seguía igual sin decir por qué.
            onSuccess: () => alCerrar(),
            onError: (e) => setErrores(Object.values(e ?? {})),
            onFinish: () => setEnviando(false),
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

                {/* Si trae menos de lo que debe, se le recibe: se pagan las
                    cosas más viejas primero y lo demás sigue debiéndose. */}
                {conAbono ? (
                    <div>
                        <label htmlFor="cuanto-paga" className="mb-1.5 block text-sm font-medium text-chalk">
                            Cuánto paga
                        </label>
                        <input
                            id="cuanto-paga"
                            type="number"
                            min="1"
                            max={monto}
                            inputMode="numeric"
                            value={trae}
                            onChange={(e) => setTrae(e.target.value)}
                            placeholder={`Todo: ${pesos.format(monto)}`}
                            className="w-full rounded-control border border-line bg-surface-2 px-2.5 py-1.5 text-sm tabular-nums text-chalk focus:border-line-strong focus:outline-none"
                        />
                        <p className={`apoyo mt-1 ${pasaDelTotal ? 'text-danger' : 'text-fog'}`}>
                            {pasaDelTotal
                                ? `Debe ${pesos.format(monto)}: no puede pagar más que eso.`
                                : abona
                                    ? `Abona ${pesos.format(Number(trae))} y le quedan ${pesos.format(monto - Number(trae))}.`
                                    : 'Vacío si paga todo.'}
                        </p>
                    </div>
                ) : null}

                {conMedio ? (
                    <div>
                        <p className="mb-1.5 text-sm font-medium text-chalk">Con qué pagó</p>
                        <Botones
                            opciones={medios.map((m) => ({ valor: m.id, etiqueta: m.nombre }))}
                            valor={medio}
                            alElegir={setMedio}
                            nombre="Con qué pagó"
                            columnas="grid-cols-2"
                            compacto
                        />
                    </div>
                ) : null}

                {errores.length > 0
                    ? errores.map((mensaje, i) => (
                          <p key={i} className="apoyo text-danger" role="alert">
                              {mensaje}
                          </p>
                      ))
                    : null}

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
                        disabled={enviando || (conMedio && ! medio) || pasaDelTotal}
                        className={`rounded-control px-3 py-1.5 text-sm font-medium transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 ${
                            peligrosa ? 'bg-danger text-white' : 'bg-volt text-on-volt'
                        }`}
                    >
                        {/* Siempre el mismo texto y el mismo ancho: con la cifra
                            del abono dentro, el botón crecía con cada número que
                            se escribía. Lo que abona ya está dicho arriba. */}
                        <TextoQueCambia ocupado={enviando} mientras="Un momento…">{etiquetaConfirmar}</TextoQueCambia>
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
