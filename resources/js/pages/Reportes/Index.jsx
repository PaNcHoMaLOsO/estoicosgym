import { Head, Link, usePage } from '@inertiajs/react';
import {
    BanknoteIcon,
    CalendarClockIcon,
    ScrollTextIcon,
    SlidersHorizontalIcon,
    WalletIcon,
} from 'lucide-react';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/**
 * Los cuatro informes que se miran a diario, mas el constructor.
 *
 * Cada tarjeta dice QUE PREGUNTA responde, no como se llama el informe:
 * «cuánto entró» se busca antes que «ingresos mensuales».
 */
const INFORMES = [
    {
        href: '/panel/reportes/ingresos',
        titulo: 'Ingresos',
        pregunta: 'Cuánto entró, por mes, método y plan.',
        Icono: BanknoteIcon,
        // No se ofrece cuando se pidió cerrar la caja: la tarjeta llevaría a
        // una pantalla que devuelve al resumen.
        caja: true,
    },
    {
        href: '/panel/reportes/por-vencer',
        titulo: 'Por vencer',
        pregunta: 'A quién hay que llamar esta semana.',
        Icono: CalendarClockIcon,
    },
    {
        href: '/panel/reportes/pendientes',
        titulo: 'Por cobrar',
        pregunta: 'Quién debe y cuánto.',
        Icono: WalletIcon,
        pendientes: true,
    },
    {
        href: '/panel/reportes/membresias',
        titulo: 'Membresías',
        pregunta: 'Cómo se reparten los planes y en qué estado están.',
        Icono: ScrollTextIcon,
    },
    {
        href: '/panel/reportes/constructor',
        titulo: 'Constructor',
        pregunta: 'Arma un informe a medida eligiendo columnas y filtros.',
        Icono: SlidersHorizontalIcon,
    },
];

function Cifra({ etiqueta, valor, destacada = false }) {
    return (
        <div
            className={`rounded-panel border p-3 ${
                destacada && valor > 0 ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface'
            }`}
        >
            <p className="rotulo">{etiqueta}</p>
            <p
                className={`mt-0.5 text-lg font-semibold tabular-nums ${
                    destacada && valor > 0 ? 'text-warn' : 'text-chalk'
                }`}
            >
                {valor}
            </p>
        </div>
    );
}

export default function Index({ cifras }) {
    const { privado } = usePage().props;
    const sinCaja = Boolean(privado?.sin_caja);
    const sinMontos = Boolean(privado?.sin_montos);
    const sinPendientes = Boolean(privado?.sin_pendientes);

    const informes = INFORMES.filter((i) => ! (i.caja && sinCaja) && ! (i.pendientes && sinPendientes));

    return (
        <>
            <Head title="Reportes" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Reportes</h1>
                <p className="apoyo text-fog">Qué está pasando en el gimnasio</p>
            </header>

            <div className="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Cifra etiqueta="Socios activos" valor={cifras.socios} />
                <Cifra etiqueta="Membresías al día" valor={cifras.activas} />
                {/* Estas dos no pasan por <Reservado>: se quitan enteras, que
                    es lo que pide una portada de informes. */}
                {sinMontos || sinCaja ? null : (
                    <Cifra etiqueta="Ingresos del mes" valor={pesos.format(cifras.ingresos_mes)} />
                )}
                {sinMontos || sinPendientes ? null : (
                    <Cifra etiqueta="Por cobrar" valor={pesos.format(cifras.por_cobrar)} destacada />
                )}
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
                {informes.map(({ href, titulo, pregunta, Icono }) => {
                    return (
                        <Link
                            key={href}
                            href={href}
                            className="flex gap-3 rounded-panel border border-line bg-surface p-4 transition-colors hover:border-line-strong hover:bg-surface-2"
                        >
                            <Icono className="mt-0.5 size-5 shrink-0 text-volt" aria-hidden="true" />
                            <div>
                                <p className="text-sm font-medium text-chalk">{titulo}</p>
                                <p className="apoyo mt-0.5 text-fog">{pregunta}</p>
                            </div>
                        </Link>
                    );
                })}
            </div>
        </>
    );
}
