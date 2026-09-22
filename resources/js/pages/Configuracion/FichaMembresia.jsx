import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeftIcon, PencilIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo, { CAMPOS_PLAN, valoresDePlan } from '@/components/FormularioCatalogo';
import Estado from '@/components/Estado';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Bloque({ titulo, children }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo mb-3">{titulo}</h2>
            {children}
        </section>
    );
}

/** «Anual», «3 meses», «1 día»: lo que se lee, no dos números sueltos. */
function duracion({ duracion_meses, duracion_dias, dias_regalo = 0 }) {
    let texto = '-';

    if (duracion_meses > 0) {
        texto = duracion_meses === 12 ? 'Anual' : duracion_meses === 1 ? '1 mes' : `${duracion_meses} meses`;
    } else if (duracion_dias > 0) {
        texto = duracion_dias === 1 ? '1 día' : `${duracion_dias} días`;
    }

    // Los días de regalo, aparte: es lo que se da de más, no lo que dura.
    return dias_regalo > 0 ? `${texto} + ${dias_regalo} de regalo` : texto;
}

export default function FichaMembresia({ membresia, cifras, precios, inscripciones }) {
    const [editando, setEditando] = useState(false);

    return (
        <>
            <Head title={membresia.nombre} />

            <header className="mb-5">
                <Link
                    href="/panel/membresias"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Membresías
                </Link>

                <div className="mt-1 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="flex flex-wrap items-center gap-2 text-lg font-semibold text-chalk">
                            {membresia.nombre}
                            <Activo valor={membresia.activo} />
                        </h1>
                        <p className="apoyo text-fog">
                            {duracion(membresia)} · {pesos.format(membresia.precio)}
                            {membresia.max_pausas > 0
                                ? ` · hasta ${membresia.max_pausas} ${membresia.max_pausas === 1 ? 'pausa' : 'pausas'}`
                                : ' · sin pausas'}
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={() => setEditando(true)}
                        className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        <PencilIcon className="size-4" aria-hidden="true" />
                        Editar
                    </button>
                </div>
            </header>

            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Socios con este plan</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">{cifras.vigentes}</p>
                    <p className="apoyo text-fog">{cifras.historicas} en total, contando las cerradas</p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Ha recaudado</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">
                        {pesos.format(cifras.recaudado)}
                    </p>
                </div>
                <div className="rounded-panel border border-line bg-surface p-3">
                    <p className="rotulo">Precio con convenio</p>
                    <p className="mt-0.5 text-lg font-semibold tabular-nums text-chalk">
                        {membresia.precio_convenio > 0 ? pesos.format(membresia.precio_convenio) : '-'}
                    </p>
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-3">
                <div className="space-y-3">
                    {membresia.descripcion ? (
                        <Bloque titulo="Descripción">
                            <p className="text-sm text-fog">{membresia.descripcion}</p>
                        </Bloque>
                    ) : null}

                    {/* Si el plan subió de precio, las inscripciones viejas siguen
                        cobrando lo que costaba el día que se firmaron. Sin esta
                        tabla ese desajuste parece un error de cálculo. */}
                    <Bloque titulo="Cambios de precio">
                        {precios.length === 0 ? (
                            <p className="apoyo text-fog">El precio nunca ha cambiado.</p>
                        ) : (
                            <ul className="space-y-2">
                                {precios.map((p, i) => (
                                    <li key={i} className="text-sm">
                                        <div className="flex items-baseline justify-between gap-2">
                                            <span className="tabular-nums text-fog">
                                                {pesos.format(p.antes)} → {' '}
                                                <span className="text-chalk">{pesos.format(p.despues)}</span>
                                            </span>
                                            <span className="apoyo shrink-0 tabular-nums text-fog">
                                                {p.cuando ?? '-'}
                                            </span>
                                        </div>
                                        {p.razon ? <p className="apoyo text-fog">{p.razon}</p> : null}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Bloque>
                </div>

                <div className="lg:col-span-2">
                    <Bloque titulo="Quién lo tiene">
                        <Tabla
                            columnas={['Socio', 'Estado', 'Vence', 'Debe']}
                            vacia={inscripciones.length === 0}
                            mensajeVacio="Nadie ha contratado este plan todavía."
                        >
                            {inscripciones.map((i) => (
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
                                    <Celda>
                                        <Estado codigo={i.id_estado} />
                                    </Celda>
                                    <Celda className="tabular-nums">{i.vence ?? '-'}</Celda>
                                    <Cifra className={i.pendiente > 0 ? 'font-medium text-warn' : ''}>
                                        {i.pendiente > 0 ? pesos.format(i.pendiente) : '-'}
                                    </Cifra>
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>
                </div>
            </div>
            <FormularioCatalogo
                abierto={editando}
                alCerrar={() => setEditando(false)}
                titulo="Editar plan"
                descripcion="Las inscripciones ya vendidas conservan su precio."
                accion={`/panel/membresias/${membresia.uuid}`}
                metodo="put"
                campos={CAMPOS_PLAN}
                valores={valoresDePlan(membresia)}
            />
        </>
    );
}
