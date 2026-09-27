import { SmartphoneIcon } from 'lucide-react';

/**
 * «No tiene celular: pídeselo ahora.»
 *
 * Sale al renovar o inscribir a un socio que no tiene celular —casi todos los
 * de las planillas—. Es opcional: si no lo quiere dar, se sigue igual. Si lo
 * da, se anota en su ficha con la misma membresía (App\Support\CelularDelSocio).
 */
export default function PedirCelular({ valor, alCambiar, error }) {
    return (
        <div className="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-panel border border-info/40 bg-info/5 px-4 py-3">
            <SmartphoneIcon className="size-5 shrink-0 text-info" aria-hidden="true" />

            <label htmlFor="celular_socio" className="min-w-0 flex-1 text-sm">
                <span className="block font-medium text-chalk">No tiene celular</span>
                <span className="apoyo block text-fog">Pídeselo ahora: así le llegan los avisos de vencimiento.</span>
            </label>

            <div className="w-full sm:w-56">
                <input
                    id="celular_socio"
                    name="celular_socio"
                    type="tel"
                    inputMode="tel"
                    autoComplete="off"
                    value={valor}
                    onChange={(e) => alCambiar(e.target.value)}
                    aria-invalid={error ? 'true' : undefined}
                    className={`w-full rounded-control border bg-surface px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:outline-none ${
                        error ? 'border-danger' : 'border-line focus:border-line-strong'
                    }`}
                />
                {error ? (
                    <p className="apoyo mt-1 text-danger" role="alert">
                        {error}
                    </p>
                ) : null}
            </div>
        </div>
    );
}
