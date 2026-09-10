import { Head, Link, useForm } from '@inertiajs/react';
import { useMemo } from 'react';
import { ArrowLeftIcon } from 'lucide-react';

import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/**
 * Corregir los datos de una membresia ya vendida.
 *
 * Para el error de tecleo: la fecha de inicio del mes pasado en vez de la de
 * este, un precio mal puesto. Una fecha de inicio equivocada corre todo el
 * periodo, y hasta ahora no habia forma de arreglarlo desde el panel.
 *
 * AQUI NO SE CAMBIA el socio, el estado ni el plan: cada una de esas tres cosas
 * tiene su propia pantalla porque arrastra cuentas —los pagos, los dias
 * compensados, el credito del plan anterior— que un formulario de correccion
 * se saltaria enteras.
 */
export default function Editar({ inscripcion, motivos, formToken }) {
    const { data, setData, put, processing, errors } = useForm({
        form_submit_token: formToken,
        fecha_inicio: inscripcion.fecha_inicio ?? '',
        fecha_vencimiento: inscripcion.fecha_vencimiento ?? '',
        precio_base: inscripcion.precio_base,
        descuento_aplicado: inscripcion.descuento_aplicado || '',
        id_motivo_descuento: inscripcion.id_motivo_descuento
            ? String(inscripcion.id_motivo_descuento)
            : '',
        observaciones: inscripcion.observaciones ?? '',
    });

    const final = useMemo(() => {
        const base = Number(data.precio_base) || 0;
        const descuento = Number(data.descuento_aplicado) || 0;

        return Math.max(0, base - descuento);
    }, [data.precio_base, data.descuento_aplicado]);

    // El precio no puede bajar de lo que ya se cobro: el socio quedaria con
    // saldo a favor que nadie le va a devolver.
    const porDebajoDeLoCobrado = final < inscripcion.cobrado;

    const dias = useMemo(() => {
        if (!data.fecha_inicio || !data.fecha_vencimiento) {
            return null;
        }

        const desde = new Date(data.fecha_inicio);
        const hasta = new Date(data.fecha_vencimiento);

        return Math.round((hasta - desde) / 86400000) + 1;
    }, [data.fecha_inicio, data.fecha_vencimiento]);

    function enviar(e) {
        e.preventDefault();
        put(`/panel/inscripciones/${inscripcion.uuid}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title={`Corregir membresía · ${inscripcion.socio}`} />

            <header className="mb-5">
                <Link
                    href={`/panel/inscripciones/${inscripcion.uuid}`}
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Volver a la membresía
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Corregir membresía</h1>
                <p className="apoyo text-fog">
                    {inscripcion.socio}
                    {inscripcion.plan ? ` · ${inscripcion.plan}` : ''}
                    {inscripcion.convenio ? ` · ${inscripcion.convenio}` : ''}
                </p>
            </header>

            {/* Lo que NO se toca aqui, dicho antes de empezar: si alguien viene a
                cambiar el plan o a pausar, es mejor que lo sepa ya. */}
            <p className="apoyo mb-4 rounded-panel border border-line bg-surface-2 px-3 py-2 text-fog">
                Aquí se corrigen las fechas y el precio. Para cambiar de plan, pausar, traspasar o
                renovar, usa los botones de la membresía: cada una lleva sus propias cuentas.
            </p>

            <form onSubmit={enviar} className="max-w-2xl space-y-5">
                <Grupo titulo="Cuándo corre">
                    <Campo
                        etiqueta="Empieza"
                        nombre="fecha_inicio"
                        error={errors.fecha_inicio}
                        requerido
                    >
                        <Texto
                            nombre="fecha_inicio"
                            tipo="date"
                            valor={data.fecha_inicio}
                            alCambiar={(v) => setData('fecha_inicio', v)}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Vence"
                        nombre="fecha_vencimiento"
                        error={errors.fecha_vencimiento}
                        requerido
                        /* El ultimo dia que sirve, no el siguiente: si se pone
                           el dia de despues se regala una jornada. */
                        ayuda={
                            dias !== null
                                ? `El último día que sirve. Serían ${dias} días.`
                                : 'El último día que sirve, no el siguiente.'
                        }
                    >
                        <Texto
                            nombre="fecha_vencimiento"
                            tipo="date"
                            valor={data.fecha_vencimiento}
                            alCambiar={(v) => setData('fecha_vencimiento', v)}
                        />
                    </Campo>
                </Grupo>

                <Grupo titulo="Cuánto vale">
                    <Campo
                        etiqueta="Precio"
                        nombre="precio_base"
                        error={errors.precio_base}
                        requerido
                        ayuda={
                            inscripcion.cobrado > 0
                                ? `Ya se cobraron ${pesos.format(inscripcion.cobrado)}: el total no puede quedar por debajo.`
                                : 'Todavía no se ha cobrado nada de esta membresía.'
                        }
                    >
                        <Texto
                            nombre="precio_base"
                            tipo="number"
                            min="0"
                            valor={data.precio_base}
                            alCambiar={(v) => setData('precio_base', v)}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Descuento"
                        nombre="descuento_aplicado"
                        error={errors.descuento_aplicado}
                        ayuda="En pesos. Déjalo vacío si no hay."
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
                                opciones={motivos.map((m) => ({
                                    valor: String(m.id),
                                    etiqueta: m.nombre,
                                }))}
                                vacio="Sin especificar"
                            />
                        </Campo>
                    ) : null}

                    <div className="rounded-panel border border-line bg-surface-2 p-3 text-sm">
                        <dl className="space-y-1">
                            <div className="flex justify-between gap-3">
                                <dt className="font-medium text-chalk">Total</dt>
                                <dd className="font-semibold tabular-nums text-chalk">
                                    {pesos.format(final)}
                                </dd>
                            </div>

                            {inscripcion.cobrado > 0 ? (
                                <div className="flex justify-between gap-3">
                                    <dt className="text-fog">Ya cobrado</dt>
                                    <dd className="tabular-nums text-chalk">
                                        {pesos.format(inscripcion.cobrado)}
                                    </dd>
                                </div>
                            ) : null}

                            <div className="flex justify-between gap-3 border-t border-line pt-1">
                                <dt className="text-fog">Quedaría por cobrar</dt>
                                <dd
                                    className={`tabular-nums ${
                                        final - inscripcion.cobrado > 0 ? 'text-warn' : 'text-chalk'
                                    }`}
                                >
                                    {pesos.format(Math.max(0, final - inscripcion.cobrado))}
                                </dd>
                            </div>
                        </dl>

                        {porDebajoDeLoCobrado ? (
                            <p className="apoyo mt-1.5 text-danger">
                                Se cobró más de lo que costaría. Corrige o anula los pagos primero.
                            </p>
                        ) : null}
                    </div>
                </Grupo>

                <Grupo titulo="Observaciones">
                    <Campo
                        etiqueta="Notas"
                        nombre="observaciones"
                        error={errors.observaciones}
                        ayuda="Por qué se corrigió, si hace falta recordarlo."
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
                        disabled={processing || porDebajoDeLoCobrado}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        {processing ? 'Guardando…' : 'Guardar'}
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
