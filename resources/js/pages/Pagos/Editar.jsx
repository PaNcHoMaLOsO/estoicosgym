import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeftIcon, Trash2Icon } from 'lucide-react';

import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';
import Dialogo from '@/components/Dialogo';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/**
 * Corregir o anular un pago ya registrado.
 *
 * Es para el error de tecleo: 40.000 donde iba 4.000, la tarjeta donde iba el
 * efectivo. Un monto mal escrito descuadra la caja del dia y el saldo del socio
 * a la vez, asi que esto no es un extra.
 */
export default function Editar({ pago, metodosPago, formToken }) {
    const [anulando, setAnulando] = useState(false);

    const { data, setData, put, processing, errors } = useForm({
        form_submit_token: formToken,
        monto_abonado: pago.monto_abonado,
        fecha_pago: pago.fecha_pago ?? '',
        id_metodo_pago: pago.id_metodo_pago ? String(pago.id_metodo_pago) : '',
        referencia_pago: pago.referencia_pago ?? '',
        observaciones: pago.observaciones ?? '',
    });

    function enviar(e) {
        e.preventDefault();
        put(`/panel/pagos/${pago.uuid}`, { preserveScroll: true });
    }

    const metodoElegido = metodosPago.find((m) => String(m.id) === String(data.id_metodo_pago));

    return (
        <>
            <Head title={`Corregir pago · ${pago.socio}`} />

            <header className="mb-5">
                <Link
                    href={`/panel/pagos/${pago.uuid}`}
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Volver al pago
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Corregir pago</h1>
                <p className="apoyo text-fog">
                    {pago.socio}
                    {pago.plan ? ` · ${pago.plan}` : ''}
                    {pago.total > 0 ? ` · membresía de ${pesos.format(pago.total)}` : ''}
                </p>
            </header>

            <form onSubmit={enviar} className="max-w-2xl space-y-5">
                <Grupo titulo="Qué se cobró">
                    <Campo
                        etiqueta="Monto"
                        nombre="monto_abonado"
                        error={errors.monto_abonado}
                        requerido
                        /* Entre todos los pagos de una membresia no pueden sumar
                           mas de lo que vale, o el socio saldria con saldo a
                           favor que nadie le va a devolver. */
                        ayuda={`Como mucho ${pesos.format(pago.tope)}: es lo que queda del precio descontando los otros pagos.`}
                    >
                        <Texto
                            nombre="monto_abonado"
                            tipo="number"
                            min="1"
                            max={pago.tope}
                            valor={data.monto_abonado}
                            alCambiar={(v) => setData('monto_abonado', v)}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Fecha"
                        nombre="fecha_pago"
                        error={errors.fecha_pago}
                        requerido
                        ayuda="La del día en que entró el dinero, no la de hoy."
                    >
                        <Texto
                            nombre="fecha_pago"
                            tipo="date"
                            valor={data.fecha_pago}
                            alCambiar={(v) => setData('fecha_pago', v)}
                        />
                    </Campo>

                    <Campo etiqueta="Método" nombre="id_metodo_pago" error={errors.id_metodo_pago} requerido>
                        <Seleccion
                            nombre="id_metodo_pago"
                            valor={data.id_metodo_pago}
                            alCambiar={(v) => setData('id_metodo_pago', v)}
                            opciones={metodosPago.map((m) => ({
                                valor: String(m.id),
                                etiqueta: m.nombre,
                            }))}
                            /* Sin opcion vacia cuando ya hay metodo: elegirla
                               dejaria el campo sin valor, que aqui no significa
                               nada. Un pago pendiente si llega sin metodo. */
                            vacio={pago.id_metodo_pago ? null : 'Elige cómo pagó…'}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Comprobante"
                        nombre="referencia_pago"
                        error={errors.referencia_pago}
                        /* El método dice si hace falta: en transferencia y
                           tarjeta es el respaldo de que el dinero entró. */
                        ayuda={
                            metodoElegido?.requiere_comprobante
                                ? `${metodoElegido.nombre} necesita el número de comprobante.`
                                : 'Opcional para este método.'
                        }
                    >
                        <Texto
                            nombre="referencia_pago"
                            valor={data.referencia_pago}
                            alCambiar={(v) => setData('referencia_pago', v)}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Observaciones"
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

                <div className="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        {processing ? 'Guardando…' : 'Guardar'}
                    </button>

                    <Link href={`/panel/pagos/${pago.uuid}`} className="apoyo text-fog hover:text-chalk">
                        Cancelar
                    </Link>

                    {/* Anular va a la derecha del todo y en rojo: no es lo que se
                        viene a hacer aqui, y pulsarlo por error se nota en caja. */}
                    <button
                        type="button"
                        onClick={() => setAnulando(true)}
                        className="ml-auto inline-flex items-center gap-1.5 text-sm text-fog transition-colors hover:text-danger"
                    >
                        <Trash2Icon className="size-4" aria-hidden="true" />
                        Anular este pago
                    </button>
                </div>
            </form>

            <Dialogo
                abierto={anulando}
                alCerrar={() => setAnulando(false)}
                titulo="Anular el pago"
                descripcion={`Se quitará de la caja y el saldo de ${pago.socio} volverá a subir ${pesos.format(pago.monto_abonado)}. Queda en la papelera por si hay que recuperarlo.`}
                accion={`/panel/pagos/${pago.uuid}`}
                via="inertia"
                metodo="delete"
                etiquetaConfirmar="Anular"
                peligrosa
            />
        </>
    );
}
