import { Head } from '@inertiajs/react';

/**
 * Una cifra por tarjeta y nada mas.
 *
 * `destacada` no es decoracion: marca la unica que EXIGE hacer algo hoy. Si
 * todas se pintaran igual, la que importa se perderia entre las otras tres.
 */
function Cifra({ etiqueta, valor, pie, destacada = false }) {
    return (
        <div
            className={`rounded-panel border p-4 ${
                destacada && valor > 0 ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface'
            }`}
        >
            <p className="rotulo">{etiqueta}</p>
            <p
                className={`mt-1 text-2xl font-semibold tabular-nums ${
                    destacada && valor > 0 ? 'text-warn' : 'text-chalk'
                }`}
            >
                {valor}
            </p>
            {pie ? <p className="apoyo mt-0.5 text-fog">{pie}</p> : null}
        </div>
    );
}

export default function Resumen({ cifras, recaudado_hoy }) {
    const pesos = new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        maximumFractionDigits: 0,
    });

    return (
        <>
            <Head title="Resumen" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Resumen</h1>
                <p className="apoyo text-fog">Cómo está el gimnasio hoy</p>
            </header>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Cifra etiqueta="Socios" valor={cifras.socios} pie="activos en el sistema" />
                <Cifra etiqueta="Membresías al día" valor={cifras.activas} />
                <Cifra etiqueta="Pausadas" valor={cifras.pausadas} />
                <Cifra
                    etiqueta="Vencen esta semana"
                    valor={cifras.vencen_semana}
                    pie="requieren contacto"
                    destacada
                />
            </div>

            <div className="mt-3 rounded-panel border border-line bg-surface p-4">
                <p className="rotulo">Recaudado hoy</p>
                <p className="mt-1 text-2xl font-semibold tabular-nums text-chalk">
                    {pesos.format(recaudado_hoy)}
                </p>
            </div>
        </>
    );
}
