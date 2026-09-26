import useAvisoAlSalir from '@/lib/useAvisoAlSalir';
import { Head, Link, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { ArrowLeftIcon } from 'lucide-react';

import Cobro, { Botones, detalleDePartes, metodoPorDefecto, partesIniciales } from '@/components/Cobro';
import Nota from '@/components/Nota';
import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});


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
    const [partes, setPartes] = useState(() => partesIniciales(metodosPago));

    const { data, setData, post, processing, errors, isDirty } = useForm({
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
        // Marcado en efectivo: es lo que más se usa en el mesón.
        id_metodo_pago: metodoPorDefecto(metodosPago),
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

    // Cuanto sube o baja respecto de lo que pagó la vez pasada. Es la pregunta
    // que hace el socio en el mesón, y responderla de memoria se equivoca.
    const diferencia = cuenta ? cuenta.final - inscripcion.precio_anterior : 0;
    // Las membresías traídas de las planillas quedaron en $0: no se sabe cuánto
    // pagaron. Comparar contra ese cero diría «paga $40.000 más», y es falso.
    const sinPrecioAnterior = ! inscripcion.precio_anterior;


    // Sin guardar y con algo escrito: pregunta antes de salir.
    const tocar = useAvisoAlSalir(isDirty && ! processing);

    function enviar(e) {
        e.preventDefault();

        const detalle = detalleDePartes(partes, metodosPago);

        post(`/panel/inscripciones/${inscripcion.uuid}/renovar`, {
            preserveScroll: true,
            data: { ...data, detalle_pagos_mixto: JSON.stringify(detalle) },
        });
    }



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

            <form onSubmit={enviar} {...tocar} className="max-w-3xl space-y-5">
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
                            <dd className="inline text-chalk">
                                {sinPrecioAnterior ? 'sin dato' : pesos.format(inscripcion.precio_anterior)}
                            </dd>
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
                        {/* Un toque y no tres: son cuatro o cinco planes, y en un
                            desplegable el precio queda escondido hasta abrirlo. */}
                        <Botones
                            nombre="Plan"
                            valor={data.id_membresia}
                            alElegir={(v) => setData('id_membresia', String(v))}
                            columnas="grid-cols-2 sm:grid-cols-3"
                            opciones={membresias.map((m) => ({
                                valor: String(m.id),
                                etiqueta: m.nombre,
                                pie: `${m.duracion} · ${pesos.format(m.precio)}`,
                            }))}
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
                        {sinPrecioAnterior ? null : diferencia !== 0 ? (
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
                        <Cobro
                            total={total}
                            forma={data.tipo_pago}
                            alCambiarForma={(v) => setData('tipo_pago', v)}
                            monto={data.monto_abonado}
                            alCambiarMonto={(v) => setData('monto_abonado', v)}
                            metodo={data.id_metodo_pago}
                            alCambiarMetodo={(v) => setData('id_metodo_pago', v)}
                            metodosPago={metodosPago}
                            partes={partes}
                            setPartes={setPartes}
                            errores={errors}
                        />

                        {data.tipo_pago === 'pendiente' ? (
                            <Nota compacta>
                                Renueva debiendo {pesos.format(total)}. Aparecerá en Pagos como pendiente de cobro.
                            </Nota>
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
