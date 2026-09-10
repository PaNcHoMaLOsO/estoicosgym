import { Head, Link } from '@inertiajs/react';
import { PhoneOffIcon } from 'lucide-react';

import Barras from '@/components/Barras';
import Columnas from '@/components/Columnas';
import { Celda, Fila, Tabla } from '@/components/Tabla';

/**
 * Portada del panel.
 *
 * NO HAY CIFRAS DE DINERO, a propósito: la ve todo el mundo, incluida recepción,
 * que por permisos no entra a los informes de ingresos. La plata vive en
 * Reportes. Aquí está lo que se puede HACER hoy.
 */

function Cifra({ etiqueta, valor, pie, tono = 'normal' }) {
    // El color solo aparece cuando el número pide actuar. Si todo se pintara,
    // no destacaría nada.
    const activo = valor > 0;

    const estilos = {
        normal: 'border-line bg-surface',
        aviso: activo ? 'border-warn/40 bg-warn/5' : 'border-line bg-surface',
        alerta: activo ? 'border-danger/40 bg-danger/5' : 'border-line bg-surface',
    };

    const texto = {
        normal: 'text-chalk',
        aviso: activo ? 'text-warn' : 'text-chalk',
        alerta: activo ? 'text-danger' : 'text-chalk',
    };

    return (
        <div className={`rounded-panel border p-4 ${estilos[tono]}`}>
            <p className="rotulo">{etiqueta}</p>
            <p className={`mt-1 text-2xl font-semibold tabular-nums ${texto[tono]}`}>{valor}</p>
            {pie ? <p className="apoyo mt-0.5 text-fog">{pie}</p> : null}
        </div>
    );
}

function Panel({ titulo, descripcion, children, enlace }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <div className="mb-3 flex items-start justify-between gap-3">
                <div>
                    <h2 className="rotulo">{titulo}</h2>
                    {descripcion ? <p className="apoyo mt-0.5 text-fog">{descripcion}</p> : null}
                </div>
                {enlace}
            </div>
            {children}
        </section>
    );
}

/** Cuanto más cerca del vencimiento, más urgente la llamada. */
function Faltan({ dias }) {
    if (dias <= 0) {
        return <span className="font-medium text-danger">Hoy</span>;
    }

    if (dias <= 3) {
        return <span className="font-medium text-danger">{dias} d</span>;
    }

    return <span className="font-medium text-warn">{dias} d</span>;
}

export default function Resumen({ cifras, altas, porPlan, porVencer }) {
    return (
        <>
            <Head title="Resumen" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Resumen</h1>
                <p className="apoyo text-fog">Qué hay que atender hoy</p>
            </header>

            <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Cifra etiqueta="Socios activos" valor={cifras.socios} />
                <Cifra
                    etiqueta="Membresías al día"
                    valor={cifras.al_dia}
                    pie={cifras.pausadas > 0 ? `${cifras.pausadas} pausadas` : undefined}
                />
                <Cifra
                    etiqueta="Vencen esta semana"
                    valor={cifras.vencen_semana}
                    pie="hay que llamarles"
                    tono="aviso"
                />
                <Cifra
                    etiqueta="Vencidas sin renovar"
                    valor={cifras.vencidas}
                    pie="socios que se están yendo"
                    tono="alerta"
                />
            </div>

            <div className="mb-3 grid gap-3 lg:grid-cols-2">
                <Panel
                    titulo="Socios nuevos"
                    descripcion="Altas de cada mes: si el gimnasio crece o se estanca."
                >
                    <Columnas datos={altas} etiqueta="Altas" />
                </Panel>

                <Panel
                    titulo="Qué planes se venden"
                    descripcion="Reparto de las membresías que están al día."
                >
                    <Barras
                        filas={porPlan}
                        formato={(v) => `${v}`}
                        vacio="No hay membresías al día."
                    />
                </Panel>
            </div>

            {/* La lista accionable va al final pero es lo que de verdad se usa:
                los cuadros de arriba dicen CUÁNTOS, esto dice A QUIÉN. */}
            <Panel
                titulo="A quién llamar esta semana"
                enlace={
                    <Link
                        href="/panel/reportes/por-vencer"
                        className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                    >
                        Ver todas
                    </Link>
                }
            >
                <Tabla
                    columnas={['Socio', 'Plan', 'Vence', 'Faltan', 'Contacto']}
                    vacia={porVencer.length === 0}
                    mensajeVacio="Ninguna membresía vence esta semana."
                >
                    {porVencer.map((i) => (
                        <Fila key={i.uuid}>
                            <Celda className="font-medium text-chalk">
                                <Link
                                    href={
                                        i.socio_uuid
                                            ? `/panel/clientes/${i.socio_uuid}`
                                            : `/panel/inscripciones/${i.uuid}`
                                    }
                                    className="hover:underline"
                                >
                                    {i.socio}
                                </Link>
                            </Celda>
                            <Celda>{i.membresia ?? '—'}</Celda>
                            <Celda className="tabular-nums">{i.vence ?? '—'}</Celda>
                            <Celda>
                                <Faltan dias={i.dias} />
                            </Celda>
                            <Celda>
                                {i.contacto ? (
                                    i.contacto
                                ) : (
                                    // Sin correo ni celular no hay a quien avisar.
                                    <span className="inline-flex items-center gap-1 text-warn">
                                        <PhoneOffIcon className="size-3.5" aria-hidden="true" />
                                        Sin contacto
                                    </span>
                                )}
                            </Celda>
                        </Fila>
                    ))}
                </Tabla>
            </Panel>
        </>
    );
}
