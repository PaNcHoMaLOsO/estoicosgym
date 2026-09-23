import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon, InfoIcon, TrendingDownIcon, TrendingUpIcon } from 'lucide-react';

import Barras from '@/components/Barras';
import { Cifra, Panel, pesos } from '@/components/Tablero';
import { Celda, Fila, Tabla } from '@/components/Tabla';
import { Reservado } from '@/Privado';

/**
 * Cómo va el negocio, mes a mes.
 *
 * LOS OTROS INFORMES SON FOTOS. Este es la película: cuánta gente entra por
 * primera vez, cuánta renueva, cuánta deja de venir y cuántos quedan al cerrar
 * el mes. Un gimnasio que pierde diez socios al mes y gana ocho se ve idéntico
 * a uno que crece mientras solo se miren fotos.
 */

/** Las tres series juntas en una columna, para ver de dónde sale cada socio. */
function Columna({ fila, mayor }) {
    const alto = (n) => `${Math.max(0, (n / mayor) * 100)}%`;

    return (
        <div className="flex min-w-0 flex-1 flex-col items-center gap-1">
            <span className="apoyo tabular-nums text-fog">{fila.activos || ''}</span>

            <div className="flex w-full flex-1 items-end gap-px">
                {/* Altas y renovaciones, una al lado de la otra: lo que se
                    quiere ver es si el gimnasio crece por gente nueva o por la
                    de siempre. La línea de activos va detrás, en gris. */}
                <div
                    className="flex-1 rounded-t-control bg-volt"
                    style={{ height: alto(fila.altas) }}
                    title={`${fila.altas} socios nuevos`}
                />
                <div
                    className="flex-1 rounded-t-control bg-accent/70"
                    style={{ height: alto(fila.renovaciones) }}
                    title={`${fila.renovaciones} renovaciones`}
                />
                <div
                    className="flex-1 rounded-t-control bg-danger/60"
                    style={{ height: alto(fila.se_fueron) }}
                    title={`${fila.se_fueron} dejaron de venir`}
                />
            </div>

            <span className={`apoyo whitespace-nowrap ${fila.en_curso ? 'text-chalk' : 'text-fog'}`}>
                {fila.mes.replace('.', '')}
            </span>
        </div>
    );
}

export default function Negocio({ porMes, retencion, porConvenio, meses, diasDeGracia, importadas }) {
    const cerrados = porMes.filter((m) => ! m.en_curso);
    const ultimo = cerrados[cerrados.length - 1];
    const anterior = cerrados[cerrados.length - 2];

    const mayor = Math.max(...porMes.map((m) => Math.max(m.altas, m.renovaciones, m.se_fueron)), 1);

    // Crece si en el último mes cerrado entró más gente de la que se fue.
    const saldo = ultimo ? ultimo.altas - ultimo.se_fueron : 0;

    return (
        <>
            <Head title="Cómo va el negocio" />

            <header className="mb-5">
                <Link
                    href="/panel/reportes"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Reportes
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Cómo va el negocio</h1>
                <p className="apoyo text-fog">
                    Quién entra, quién renueva y quién deja de venir, mes a mes. Sin pases diarios: quien
                    compra uno no es un socio que se gane ni que se pierda.
                </p>
            </header>

            <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Cifra
                    etiqueta={ultimo ? `Socios al cerrar ${ultimo.mes}` : 'Socios'}
                    valor={ultimo?.activos ?? 0}
                    pie={
                        anterior
                            ? `${ultimo.activos - anterior.activos >= 0 ? '+' : ''}${ultimo.activos - anterior.activos} contra el mes anterior`
                            : undefined
                    }
                />
                <Cifra
                    etiqueta="Entraron nuevos"
                    valor={ultimo?.altas ?? 0}
                    pie={ultimo ? `y ${ultimo.renovaciones} renovaron` : undefined}
                />
                <Cifra
                    etiqueta="Dejaron de venir"
                    valor={ultimo?.se_fueron ?? 0}
                    tono={saldo < 0 ? 'alerta' : 'normal'}
                    siempreTono={saldo < 0}
                    pie={`sin comprar nada en ${diasDeGracia} días`}
                />
                <Cifra
                    etiqueta="Vuelve a comprar"
                    valor={`${retencion.porcentaje}%`}
                    tono={retencion.porcentaje < 40 ? 'aviso' : 'normal'}
                    siempreTono={retencion.porcentaje < 40}
                    pie={`${retencion.volvieron} de ${retencion.nuevos} socios`}
                />
            </div>

            {ultimo ? (
                <p
                    className={`mb-4 flex items-start gap-2 rounded-panel border px-3 py-2.5 text-sm ${
                        saldo >= 0 ? 'border-ok/40 bg-ok/5 text-ok' : 'border-danger/40 bg-danger/5 text-danger'
                    }`}
                >
                    {saldo >= 0 ? (
                        <TrendingUpIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    ) : (
                        <TrendingDownIcon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    )}
                    <span>
                        En {ultimo.mes} entraron {ultimo.altas} socios nuevos y dejaron de venir {ultimo.se_fueron}:{' '}
                        <strong className="font-semibold">
                            {saldo === 0 ? 'quedó igual' : saldo > 0 ? `${saldo} más` : `${Math.abs(saldo)} menos`}
                        </strong>
                        .{' '}
                        <span className="text-fog">
                            Las renovaciones no cuentan aquí: son los socios de siempre, que ya estaban.
                        </span>
                    </span>
                </p>
            ) : null}

            {/*
             * DE DÓNDE SALEN ESTOS DATOS.
             *
             * En las planillas la renovación se escribía ENCIMA de la fila del
             * socio, así que un socio de tres años quedó como una sola
             * membresía y «vuelve a comprar» sale casi en cero. No es que la
             * gente no vuelva: es que no quedó anotado. Decirlo aquí evita que
             * alguien tome una decisión con un número que no significa eso.
             */}
            {importadas?.avisar ? (
                <p className="mb-4 flex items-start gap-2 rounded-panel border border-line bg-surface px-3 py-2.5">
                    <InfoIcon className="mt-0.5 size-4 shrink-0 text-fog" aria-hidden="true" />
                    <span className="apoyo text-fog">
                        {importadas.cuantas} de {importadas.de} membresías vienen de las planillas viejas, donde
                        la renovación se escribía encima de la fila del socio en vez de anotarse aparte. Por eso
                        «renovaciones» y «vuelve a comprar» salen tan bajos:{' '}
                        <span className="text-chalk">no es que la gente no vuelva, es que no quedó escrito</span>.
                        Estas dos cifras se vuelven verdad con lo que se registre desde ahora.
                    </span>
                </p>
            ) : null}

            <Panel
                titulo={`Los últimos ${meses} meses`}
                descripcion="Cada mes, tres columnas: nuevos, renovaciones y los que dejaron de venir. El número de arriba es cuántos socios quedaron activos al cerrar."
            >
                <div className="flex h-40 items-end gap-1 sm:gap-2">
                    {porMes.map((fila) => (
                        <Columna key={fila.clave} fila={fila} mayor={mayor} />
                    ))}
                </div>

                <div className="mt-3 flex flex-wrap gap-4 border-t border-line pt-3">
                    <span className="apoyo inline-flex items-center gap-1.5 text-fog">
                        <span className="size-2.5 rounded-[3px] bg-volt" aria-hidden="true" /> Socios nuevos
                    </span>
                    <span className="apoyo inline-flex items-center gap-1.5 text-fog">
                        <span className="size-2.5 rounded-[3px] bg-accent/70" aria-hidden="true" /> Renovaciones
                    </span>
                    <span className="apoyo inline-flex items-center gap-1.5 text-fog">
                        <span className="size-2.5 rounded-[3px] bg-danger/60" aria-hidden="true" /> Dejaron de venir
                    </span>
                </div>
            </Panel>

            <div className="mt-3 grid gap-3 lg:grid-cols-2">
                <Panel
                    titulo="Cuánto dura un socio"
                    descripcion="Lo que cuesta conseguir uno nuevo se recupera si vuelve; si no vuelve, hay que reponerlo todos los meses."
                >
                    <dl className="space-y-3">
                        <div className="flex items-baseline justify-between gap-3">
                            <dt className="text-sm text-chalk">Compra una segunda vez</dt>
                            <dd className="text-lg font-semibold tabular-nums text-chalk">
                                {retencion.porcentaje}%
                            </dd>
                        </div>
                        <div className="flex items-baseline justify-between gap-3">
                            <dt className="text-sm text-chalk">Membresías por socio</dt>
                            <dd className="text-lg font-semibold tabular-nums text-chalk">
                                {retencion.membresias_por_socio}
                            </dd>
                        </div>
                        <div className="flex items-baseline justify-between gap-3">
                            <dt className="text-sm text-chalk">
                                Meses que se queda
                                <span className="apoyo block text-fog">la mitad dura más, la mitad menos</span>
                            </dt>
                            <dd className="text-lg font-semibold tabular-nums text-chalk">
                                {retencion.meses_de_vida}
                            </dd>
                        </div>
                        <div className="flex items-baseline justify-between gap-3 border-t border-line pt-3">
                            <dt className="text-sm text-fog">Socios con historia en el sistema</dt>
                            <dd className="tabular-nums text-fog">{retencion.socios}</dd>
                        </div>
                    </dl>
                </Panel>

                <Panel
                    titulo="De dónde sale el negocio"
                    descripcion="Lo vendido por convenio en los últimos 12 meses, y cuántos socios distintos trajo cada uno."
                >
                    <Barras
                        filas={porConvenio.slice(0, 10)}
                        formato={(v) => <Reservado ancho="w-16">{pesos.format(v)}</Reservado>}
                        vacio="Todavía no hay membresías en este periodo."
                    />
                </Panel>
            </div>

            <Panel titulo="Mes a mes, en números" descripcion="Lo mismo del gráfico, para copiar o revisar una cifra concreta.">
                <Tabla
                    columnas={[
                        { titulo: 'Mes', className: 'w-full' },
                        { titulo: 'Nuevos', className: 'text-right' },
                        { titulo: 'Renovaron', className: 'text-right' },
                        { titulo: 'Se fueron', className: 'text-right' },
                        { titulo: 'Activos al cierre', className: 'text-right' },
                        { titulo: 'Vendido', className: 'text-right' },
                    ]}
                    vacia={porMes.length === 0}
                >
                    {[...porMes].reverse().map((fila) => (
                        <Fila key={fila.clave}>
                            <Celda className="text-chalk">
                                {fila.mes}
                                {fila.en_curso ? <span className="apoyo text-fog"> · en curso</span> : null}
                            </Celda>
                            <Celda className="text-right tabular-nums text-chalk">{fila.altas}</Celda>
                            <Celda className="text-right tabular-nums">{fila.renovaciones}</Celda>
                            <Celda className="text-right tabular-nums">
                                {/* Los últimos meses no se pueden dar por cerrados:
                                    esa gente todavía puede volver. */}
                                {fila.se_fueron === 0 && fila.en_curso ? '—' : fila.se_fueron}
                            </Celda>
                            <Celda className="text-right tabular-nums text-chalk">{fila.activos}</Celda>
                            <Celda className="text-right tabular-nums">
                                <Reservado ancho="w-20">{pesos.format(fila.ingresos)}</Reservado>
                            </Celda>
                        </Fila>
                    ))}
                </Tabla>
            </Panel>
        </>
    );
}
