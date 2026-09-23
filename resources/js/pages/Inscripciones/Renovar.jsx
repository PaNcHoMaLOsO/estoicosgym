import { Head, Link, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { ArrowLeftIcon, TrashIcon } from 'lucide-react';

import Nota from '@/components/Nota';
import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

const FORMAS = [
    { valor: 'completo', etiqueta: 'Paga el plan completo' },
    { valor: 'abono', etiqueta: 'Abona una parte' },
    { valor: 'mixto', etiqueta: 'Reparte entre varios métodos' },
    { valor: 'pendiente', etiqueta: 'No paga ahora' },
];

/**
 * Renovacion de una membresia que termina.
 *
 * Igual que el alta, salvo que el socio ya esta y que arriba se ve LO QUE
 * TENIA: al renovar la pregunta no es «que plan quiere» sino «sigue con el
 * mismo o cambia», y para responderla hay que ver el anterior y su precio al
 * lado del nuevo.
 */
/** Cómo se agrupan los convenios en el desplegable, y en qué orden. */
const GRUPOS_DE_CONVENIO = {
    institucion_educativa: 'Instituciones educativas',
    empresa: 'Empresas',
    club_deportivo: 'Clubes deportivos',
    organizacion: 'Organizaciones',
    otro: 'Otros',
};

/** Las opciones del desplegable de convenios, agrupadas por su tipo. */
function opcionesDeConvenio(convenios) {
    return convenios.map((c) => ({
        valor: String(c.id),
        etiqueta: c.nombre,
        grupo: GRUPOS_DE_CONVENIO[c.tipo] ?? 'Otros',
    }));
}

/**
 * El precio que paga ESE convenio por ESE plan.
 *
 * Un club deportivo negocia el suyo —10.000, 15.000, 20.000 la mensualidad— y
 * eso no cabe en el «precio con convenio» del plan, que es uno solo para todos.
 * Sin trato propio manda ese precio general; sin convenio, el normal. El
 * servidor aplica la misma regla: aquí solo se enseña.
 */
function precioCon(plan, idConvenio, preciosDeConvenio) {
    if (! plan) {
        return 0;
    }

    if (! idConvenio) {
        return plan.precio;
    }

    const propio = preciosDeConvenio?.[idConvenio]?.[plan.id];

    return propio ?? (plan.precio_convenio || plan.precio);
}

export default function Renovar({ inscripcion, membresias, convenios, motivos, metodosPago, formToken, volverA = '', preciosDeConvenio = {} }) {
    const [partes, setPartes] = useState([{ id_metodo_pago: '', monto: '' }]);

    const { data, setData, post, processing, errors } = useForm({
        // De dónde se vino: si fue de la ficha de un socio, se vuelve allí.
        volver: volverA,
        form_submit_token: formToken,
        // Se llega con el mismo plan y el mismo convenio ya puestos: lo normal
        // es renovar igual, y cambiar es la excepcion.
        id_membresia: String(inscripcion.id_membresia ?? ''),
        id_convenio: inscripcion.id_convenio ? String(inscripcion.id_convenio) : '',
        id_motivo_descuento: '',
        descuento_aplicado: '',
        fecha_inicio: inscripcion.empieza_sugerido,
        observaciones: '',
        tipo_pago: 'completo',
        monto_abonado: '',
        id_metodo_pago: '',
        detalle_pagos_mixto: '',
        fecha_pago: new Date().toISOString().slice(0, 10),
    });

    const plan = useMemo(
        () => membresias.find((m) => String(m.id) === String(data.id_membresia)) ?? null,
        [membresias, data.id_membresia],
    );

    const cuenta = useMemo(() => {
        if (!plan) {
            return null;
        }

        const base = plan.precio;
        const porConvenio = Math.max(0, base - precioCon(plan, data.id_convenio, preciosDeConvenio));
        const manual = Number(data.descuento_aplicado) || 0;
        const descuento = Math.min(base, porConvenio + manual);

        return { base, porConvenio, manual, descuento, final: Math.max(0, base - descuento) };
    }, [plan, data.id_convenio, data.descuento_aplicado, preciosDeConvenio]);

    const total = cuenta?.final ?? 0;
    const sumaPartes = partes.reduce((t, p) => t + (Number(p.monto) || 0), 0);

    // Cuanto sube o baja respecto de lo que pagó la vez pasada. Es la pregunta
    // que hace el socio en el mesón, y responderla de memoria se equivoca.
    const diferencia = cuenta ? cuenta.final - inscripcion.precio_anterior : 0;

    function cambiarParte(indice, campo, valor) {
        setPartes(partes.map((p, i) => (i === indice ? { ...p, [campo]: valor } : p)));
    }

    function enviar(e) {
        e.preventDefault();

        const detalle = partes
            .filter((p) => p.id_metodo_pago && Number(p.monto) > 0)
            .map((p) => ({
                id_metodo_pago: Number(p.id_metodo_pago),
                monto: Number(p.monto),
                metodo_nombre: metodosPago.find((m) => String(m.id) === String(p.id_metodo_pago))?.nombre,
            }));

        post(`/panel/inscripciones/${inscripcion.uuid}/renovar`, {
            preserveScroll: true,
            data: { ...data, detalle_pagos_mixto: JSON.stringify(detalle) },
        });
    }

    const opcionesPlan = membresias.map((m) => ({
        valor: String(m.id),
        etiqueta: `${m.nombre} · ${m.duracion} · ${pesos.format(m.precio)}`,
    }));

    const opcionesMetodo = metodosPago.map((m) => ({ valor: String(m.id), etiqueta: m.nombre }));

    return (
        <>
            <Head title={`Renovar · ${inscripcion.socio}`} />

            <header className="mb-5">
                <Link
                    href={`/panel/inscripciones/${inscripcion.uuid}`}
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Volver a la membresía
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Renovar</h1>
                <p className="apoyo text-fog">{inscripcion.socio}</p>
            </header>

            <form onSubmit={enviar} className="max-w-3xl space-y-5">
                {/* Lo que tenía, arriba del todo: es contra esto que se decide. */}
                <div className="rounded-panel border border-line bg-surface-2 p-3 text-sm">
                    <p className="rotulo mb-1">Lo que termina</p>

                    <dl className="apoyo grid grid-cols-2 gap-x-4 gap-y-0.5 text-fog sm:grid-cols-4">
                        <div>
                            <dt className="inline">Plan: </dt>
                            <dd className="inline text-chalk">{inscripcion.plan ?? '-'}</dd>
                        </div>
                        <div>
                            <dt className="inline">Pagó: </dt>
                            <dd className="inline text-chalk">{pesos.format(inscripcion.precio_anterior)}</dd>
                        </div>
                        <div>
                            <dt className="inline">Convenio: </dt>
                            <dd className="inline text-chalk">{inscripcion.convenio ?? 'sin convenio'}</dd>
                        </div>
                        <div>
                            <dt className="inline">Vence: </dt>
                            <dd className="inline text-chalk">{inscripcion.vence ?? '-'}</dd>
                        </div>
                    </dl>

                    {inscripcion.dias <= 0 ? (
                        <p className="apoyo mt-1.5 text-warn">
                            Ya venció{inscripcion.dias < 0 ? ` hace ${Math.abs(inscripcion.dias)} días` : ' hoy'}.
                        </p>
                    ) : (
                        <p className="apoyo mt-1.5 text-fog">
                            Le quedan {inscripcion.dias} días.
                        </p>
                    )}
                </div>

                <Grupo titulo="¿Con qué plan sigue?">
                    <Campo
                        etiqueta="Plan"
                        nombre="id_membresia"
                        error={errors.id_membresia}
                        requerido
                        ayuda="Viene puesto el que tenía. Cámbialo si pasa a otro."
                    >
                        <Seleccion
                            nombre="id_membresia"
                            valor={data.id_membresia}
                            alCambiar={(v) => setData('id_membresia', v)}
                            opciones={opcionesPlan}
                            vacio="Elige un plan…"
                        />
                    </Campo>

                    <Campo
                        etiqueta="Empieza el"
                        nombre="fecha_inicio"
                        error={errors.fecha_inicio}
                        requerido
                        /* Arranca al día siguiente del vencimiento, no hoy: si
                           empezara hoy se solaparían y pagaría dos veces los
                           días que le quedaban. */
                        ayuda={
                            inscripcion.dias > 0
                                ? 'El día siguiente al vencimiento, para que no se pisen.'
                                : 'Hoy, porque la anterior ya venció.'
                        }
                    >
                        <Texto
                            nombre="fecha_inicio"
                            tipo="date"
                            valor={data.fecha_inicio}
                            alCambiar={(v) => setData('fecha_inicio', v)}
                        />
                    </Campo>

                    {convenios.length > 0 ? (
                        <Campo
                            etiqueta="Convenio"
                            nombre="id_convenio"
                            error={errors.id_convenio}
                            ayuda={
                                !plan
                                    ? 'Elige primero el plan.'
                                    : plan.precio_convenio
                                      ? `En ${plan.nombre} deja el plan en ${pesos.format(plan.precio_convenio)}.`
                                      : `${plan.nombre} no tiene precio de convenio cargado: no rebaja nada.`
                            }
                        >
                            <Seleccion
                                nombre="id_convenio"
                                valor={data.id_convenio}
                                alCambiar={(v) => setData('id_convenio', v)}
                                opciones={opcionesDeConvenio(convenios)}
                                vacio="Sin convenio"
                            />
                        </Campo>
                    ) : null}

                    <Campo
                        etiqueta="Descuento adicional"
                        nombre="descuento_aplicado"
                        error={errors.descuento_aplicado}
                        ayuda="En pesos, aparte del convenio."
                    >
                        <Texto
                            nombre="descuento_aplicado"
                            tipo="number"
                            min="0"
                            valor={data.descuento_aplicado}
                            alCambiar={(v) => setData('descuento_aplicado', v)}
                            placeholder="0"
                        />
                    </Campo>

                    {Number(data.descuento_aplicado) > 0 && motivos.length > 0 ? (
                        <Campo
                            etiqueta="Motivo del descuento"
                            nombre="id_motivo_descuento"
                            error={errors.id_motivo_descuento}
                        >
                            <Seleccion
                                nombre="id_motivo_descuento"
                                valor={data.id_motivo_descuento}
                                alCambiar={(v) => setData('id_motivo_descuento', v)}
                                opciones={motivos.map((m) => ({ valor: String(m.id), etiqueta: m.nombre }))}
                                vacio="Sin especificar"
                            />
                        </Campo>
                    ) : null}
                </Grupo>

                {cuenta ? (
                    <div className="rounded-panel border border-line bg-surface-2 p-3 text-sm">
                        <dl className="space-y-1">
                            <div className="flex justify-between gap-3">
                                <dt className="text-fog">Precio del plan</dt>
                                <dd className="tabular-nums text-chalk">{pesos.format(cuenta.base)}</dd>
                            </div>

                            {cuenta.porConvenio > 0 ? (
                                <div className="flex justify-between gap-3">
                                    <dt className="text-fog">Rebaja por convenio</dt>
                                    <dd className="tabular-nums text-chalk">−{pesos.format(cuenta.porConvenio)}</dd>
                                </div>
                            ) : null}

                            {cuenta.manual > 0 ? (
                                <div className="flex justify-between gap-3">
                                    <dt className="text-fog">Descuento adicional</dt>
                                    <dd className="tabular-nums text-chalk">−{pesos.format(cuenta.manual)}</dd>
                                </div>
                            ) : null}

                            <div className="flex justify-between gap-3 border-t border-line pt-1">
                                <dt className="font-medium text-chalk">Total</dt>
                                <dd className="font-semibold tabular-nums text-chalk">
                                    {pesos.format(cuenta.final)}
                                </dd>
                            </div>
                        </dl>

                        {/* «¿Me sube?» es lo primero que preguntan. */}
                        {diferencia !== 0 ? (
                            <p className="apoyo mt-1.5 text-fog">
                                {diferencia > 0
                                    ? `Paga ${pesos.format(diferencia)} más que la vez pasada.`
                                    : `Paga ${pesos.format(Math.abs(diferencia))} menos que la vez pasada.`}
                            </p>
                        ) : (
                            <p className="apoyo mt-1.5 text-fog">Lo mismo que la vez pasada.</p>
                        )}
                    </div>
                ) : null}

                {plan ? (
                    <Grupo titulo="¿Cómo paga?">
                        <Campo etiqueta="Forma de pago" nombre="tipo_pago" error={errors.tipo_pago} requerido>
                            <Seleccion
                                nombre="tipo_pago"
                                valor={data.tipo_pago}
                                alCambiar={(v) => setData('tipo_pago', v)}
                                opciones={FORMAS}
                                vacio={null}
                            />
                        </Campo>

                        {data.tipo_pago === 'pendiente' ? (
                            <Nota compacta>
                                Renueva debiendo {pesos.format(total)}. Aparecerá en Pagos como pendiente de cobro.
                            </Nota>
                        ) : null}

                        {data.tipo_pago === 'completo' || data.tipo_pago === 'abono' ? (
                            <>
                                <Campo
                                    etiqueta="Monto"
                                    nombre="monto_abonado"
                                    error={errors.monto_abonado}
                                    requerido
                                    ayuda={
                                        data.tipo_pago === 'abono'
                                            ? `Menos de ${pesos.format(total)}. El resto queda por cobrar.`
                                            : `El total es ${pesos.format(total)}.`
                                    }
                                >
                                    <Texto
                                        nombre="monto_abonado"
                                        tipo="number"
                                        min="1"
                                        valor={data.monto_abonado}
                                        alCambiar={(v) => setData('monto_abonado', v)}
                                        placeholder={String(total)}
                                    />
                                </Campo>

                                <Campo
                                    etiqueta="Método"
                                    nombre="id_metodo_pago"
                                    error={errors.id_metodo_pago}
                                    requerido
                                >
                                    <Seleccion
                                        nombre="id_metodo_pago"
                                        valor={data.id_metodo_pago}
                                        alCambiar={(v) => setData('id_metodo_pago', v)}
                                        opciones={opcionesMetodo}
                                    />
                                </Campo>
                            </>
                        ) : null}

                        {data.tipo_pago === 'mixto' ? (
                            <Campo
                                etiqueta="Reparto"
                                nombre="detalle_pagos_mixto"
                                error={errors.detalle_pagos_mixto}
                                requerido
                                ayuda="Una línea por método. Pueden sumar menos que el total: lo que falte queda por cobrar."
                            >
                                <div className="space-y-2">
                                    {partes.map((parte, i) => (
                                        <div key={i} className="flex gap-2">
                                            <select
                                                value={parte.id_metodo_pago}
                                                onChange={(e) => cambiarParte(i, 'id_metodo_pago', e.target.value)}
                                                className="min-w-0 flex-1 rounded-control border border-line bg-surface px-2 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                                            >
                                                <option value="">Método…</option>
                                                {metodosPago.map((m) => (
                                                    <option key={m.id} value={m.id}>
                                                        {m.nombre}
                                                    </option>
                                                ))}
                                            </select>

                                            <input
                                                type="number"
                                                min="1"
                                                value={parte.monto}
                                                onChange={(e) => cambiarParte(i, 'monto', e.target.value)}
                                                placeholder="Monto"
                                                className="w-32 rounded-control border border-line bg-surface px-2 py-1.5 text-sm tabular-nums text-chalk focus:border-line-strong focus:outline-none"
                                            />

                                            {partes.length > 1 ? (
                                                <button
                                                    type="button"
                                                    onClick={() => setPartes(partes.filter((_, j) => j !== i))}
                                                    aria-label={`Quitar la parte ${i + 1}`}
                                                    className="shrink-0 rounded-control border border-line px-2 text-fog transition-colors hover:text-danger"
                                                >
                                                    <TrashIcon className="size-4" aria-hidden="true" />
                                                </button>
                                            ) : null}
                                        </div>
                                    ))}

                                    <div className="flex items-center justify-between gap-3">
                                        <button
                                            type="button"
                                            onClick={() => setPartes([...partes, { id_metodo_pago: '', monto: '' }])}
                                            className="apoyo text-fog transition-colors hover:text-chalk"
                                        >
                                            Agregar otro método
                                        </button>

                                        <p className="apoyo tabular-nums text-fog">
                                            Suma {pesos.format(sumaPartes)} de {pesos.format(total)}
                                            {sumaPartes > total ? (
                                                <span className="ml-1 text-danger">· se pasa</span>
                                            ) : null}
                                        </p>
                                    </div>
                                </div>
                            </Campo>
                        ) : null}

                        {data.tipo_pago !== 'pendiente' ? (
                            <Campo
                                etiqueta="Fecha del pago"
                                nombre="fecha_pago"
                                error={errors.fecha_pago}
                                requerido
                            >
                                <Texto
                                    nombre="fecha_pago"
                                    tipo="date"
                                    valor={data.fecha_pago}
                                    alCambiar={(v) => setData('fecha_pago', v)}
                                />
                            </Campo>
                        ) : null}
                    </Grupo>
                ) : null}

                <Grupo titulo="Observaciones">
                    <Campo
                        etiqueta="Notas"
                        nombre="observaciones"
                        error={errors.observaciones}
                        ayuda="Opcional."
                    >
                        <Area
                            nombre="observaciones"
                            valor={data.observaciones}
                            alCambiar={(v) => setData('observaciones', v)}
                        />
                    </Campo>
                </Grupo>

                <div className="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={processing || !plan}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        {processing ? 'Guardando…' : 'Renovar'}
                    </button>

                    <Link
                        href={`/panel/inscripciones/${inscripcion.uuid}`}
                        className="apoyo text-fog hover:text-chalk"
                    >
                        Cancelar
                    </Link>
                </div>
            </form>
        </>
    );
}
