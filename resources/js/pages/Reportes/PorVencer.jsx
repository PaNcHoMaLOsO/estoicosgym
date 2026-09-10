import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangleIcon, ArrowLeftIcon } from 'lucide-react';

import { Celda, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Socio', 'Contacto', 'Membresía', 'Vence', 'Faltan'];

const PLAZOS = [7, 15, 30, 60];

/** Cuanto mas cerca del vencimiento, mas urgente es la llamada. */
function Faltan({ dias }) {
    if (dias <= 0) {
        return <span className="font-medium text-danger">Vence hoy</span>;
    }

    if (dias <= 3) {
        return <span className="font-medium text-danger">{dias} d</span>;
    }

    if (dias <= 7) {
        return <span className="font-medium text-warn">{dias} d</span>;
    }

    return <span className="text-fog">{dias} d</span>;
}

export default function PorVencer({ dias, inscripciones, sinContacto }) {
    return (
        <>
            <Head title="Membresías por vencer" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <Link
                        href="/panel/reportes"
                        className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                    >
                        <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                        Reportes
                    </Link>
                    <h1 className="mt-1 text-lg font-semibold text-chalk">Por vencer</h1>
                    <p className="apoyo text-fog">
                        {inscripciones.length} membresías vencen en los próximos {dias} días
                    </p>
                </div>

                <label className="flex items-center gap-2 text-sm text-fog">
                    Próximos
                    <select
                        value={dias}
                        onChange={(e) =>
                            router.get('/panel/reportes/por-vencer', { dias: e.target.value }, {
                                preserveState: true,
                                preserveScroll: true,
                            })
                        }
                        className="rounded-control border border-line bg-surface px-2 py-1 text-sm text-chalk focus:border-line-strong focus:outline-none"
                    >
                        {PLAZOS.map((d) => (
                            <option key={d} value={d}>
                                {d} días
                            </option>
                        ))}
                    </select>
                </label>
            </header>

            {/* Sin correo ni celular no hay a quien avisar: esos socios hay que
                buscarlos a mano y por eso se cuentan aparte. */}
            {sinContacto > 0 ? (
                <p className="mb-3 flex items-start gap-2 rounded-panel border border-warn/40 bg-warn/5 px-3 py-2 text-sm text-warn">
                    <AlertTriangleIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    {sinContacto === 1
                        ? 'Un socio de esta lista no tiene correo ni celular: habrá que avisarle en persona.'
                        : `${sinContacto} socios de esta lista no tienen correo ni celular: habrá que avisarles en persona.`}
                </p>
            ) : null}

            <Tabla
                columnas={COLUMNAS}
                vacia={inscripciones.length === 0}
                mensajeVacio={`Ninguna membresía vence en los próximos ${dias} días.`}
            >
                {inscripciones.map((i) => (
                    <Fila key={i.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link href={`/panel/inscripciones/${i.uuid}`} className="hover:underline">
                                {i.socio}
                            </Link>
                        </Celda>
                        <Celda>
                            {i.email || i.celular ? (
                                <>
                                    {i.email ?? ''}
                                    {i.email && i.celular ? ' · ' : ''}
                                    {i.celular ?? ''}
                                </>
                            ) : (
                                <span className="text-warn">Sin contacto</span>
                            )}
                        </Celda>
                        <Celda>{i.membresia ?? '—'}</Celda>
                        <Celda className="tabular-nums">{i.vence ?? '—'}</Celda>
                        <Celda>
                            <Faltan dias={i.dias} />
                        </Celda>
                    </Fila>
                ))}
            </Tabla>
        </>
    );
}
