import useAvisoAlSalir from '@/lib/useAvisoAlSalir';
import { hoyEnChile } from '@/lib/tiempo';
import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ArrowLeftIcon } from 'lucide-react';

import { Area, Campo, Grupo, Texto } from '@/components/Campo';
import Cobro, { metodoPorDefecto, partesIniciales } from '@/components/Cobro';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/**
 * Cobro de una inscripcion.
 *
 * Lo primero es ELEGIR A QUIEN se le cobra, y hasta que no se elige no se
 * ensena nada mas: el saldo pendiente manda sobre todo lo que viene despues
 * —cuanto se puede abonar, cuanto tienen que sumar los dos metodos— y sin saber
 * de quien hablamos esos campos no significan nada.
 */
export default function Crear({ preseleccionada, metodosPago, formToken, volverA = '' }) {
    // «Hoy» en Chile, calculado al abrir el formulario: la fecha UTC se
    // adelantaba un día desde las 21:00 y proponía cobros con fecha de mañana.
    const hoy = hoyEnChile();

    // A quién se le cobra. Si se llega desde una ficha, ya viene resuelta.
    const [elegida, setElegida] = useState(preseleccionada ?? null);
    const [busqueda, setBusqueda] = useState('');
    const [resultados, setResultados] = useState(null);
    const [buscando, setBuscando] = useState(false);

    // Los dos medios de un pago repartido: el mismo bloque de cobro que al
    // inscribir y renovar, con botones en vez de desplegables.
    const [partes, setPartes] = useState(() => partesIniciales(metodosPago));

    const { data, setData, post, processing, errors, isDirty, transform } = useForm({
        // De dónde se vino: si fue de la ficha de un socio, se vuelve allí.
        volver: volverA,
        form_submit_token: formToken,
        id_inscripcion: preseleccionada?.id ?? '',
        tipo_pago: 'completo',
        monto_abonado: '',
        // Marcado en efectivo: es lo que más se usa en el mesón.
        id_metodo_pago: metodoPorDefecto(metodosPago),
        id_metodo_pago1: '',
        id_metodo_pago2: '',
        monto_metodo1: '',
        monto_metodo2: '',
        fecha_pago: hoy,
        referencia_pago: '',
        observaciones: '',
    });

    const pendiente = elegida?.pendiente ?? 0;

    /*
     * Al socio se le BUSCA, no se le elige de una lista.
     *
     * Antes iban todas las inscripciones con saldo dentro de un <select>
     * —sesenta hoy, miles en un gimnasio en marcha—, y con ese volumen encontrar
     * a alguien es imposible. Se espera a que deje de escribir: sin la espera,
     * cada tecla seria una consulta.
     */
    useEffect(() => {
        if (busqueda.trim().length < 2) {
            setResultados(null);

            return undefined;
        }

        setBuscando(true);

        const temporizador = setTimeout(async () => {
            try {
                const r = await fetch(
                    `/panel/pagos/buscar?q=${encodeURIComponent(busqueda)}`,
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                );
                const j = await r.json();
                setResultados(j.inscripciones ?? []);
            } catch (e) {
                setResultados([]);
            } finally {
                setBuscando(false);
            }
        }, 300);

        return () => clearTimeout(temporizador);
    }, [busqueda]);

    function elegir(inscripcion) {
        setElegida(inscripcion);
        setData('id_inscripcion', inscripcion.id);
        setBusqueda('');
        setResultados(null);
    }

    function cambiarSocio() {
        setElegida(null);
        setData('id_inscripcion', '');
    }

    // En mixto los dos montos tienen que sumar EXACTAMENTE el saldo: un pago
    // repartido salda la cuenta. El bloque de cobro lo dice mientras se escribe.
    const sumaMixto = partes.reduce((t, p) => t + (Number(p.monto) || 0), 0);
    const mixtoDescuadra = data.tipo_pago === 'mixto'
        && (sumaMixto !== pendiente || partes.some((p) => ! p.id_metodo_pago) || partes[0]?.id_metodo_pago === partes[1]?.id_metodo_pago);

    // Sin guardar y con algo escrito: pregunta antes de salir.
    const tocar = useAvisoAlSalir(isDirty && ! processing);

    function enviar(e) {
        e.preventDefault();

        // El servidor guarda un pago repartido como dos medios con su monto.
        transform((d) => (d.tipo_pago === 'mixto'
            ? {
                ...d,
                id_metodo_pago1: partes[0]?.id_metodo_pago ?? '',
                monto_metodo1: partes[0]?.monto ?? '',
                id_metodo_pago2: partes[1]?.id_metodo_pago ?? '',
                monto_metodo2: partes[1]?.monto ?? '',
            }
            : d));

        post('/panel/pagos/registrar', { preserveScroll: true });
    }

    return (
        <>
            <Head title="Registrar pago" />

            <header className="mb-5">
                <Link
                    href="/panel/pagos"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Pagos
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Registrar pago</h1>
            </header>

            <form onSubmit={enviar} {...tocar} className="max-w-3xl space-y-5">
                <Grupo titulo="¿A quién se le cobra?">
                    {elegida ? (
                        <div className="rounded-panel border border-line bg-surface-2 p-3 text-sm">
                            <div className="flex items-start justify-between gap-3">
                                <p className="font-medium text-chalk">
                                    {elegida.socio}
                                    {elegida.rut ? (
                                        <span className="apoyo block text-fog">{elegida.rut}</span>
                                    ) : null}
                                </p>
                                <button
                                    type="button"
                                    onClick={cambiarSocio}
                                    className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                                >
                                    Cambiar
                                </button>
                            </div>

                            <dl className="apoyo mt-2 grid grid-cols-2 gap-x-4 gap-y-0.5 text-fog sm:grid-cols-4">
                                <div>
                                    <dt className="inline">Plan: </dt>
                                    <dd className="inline text-chalk">{elegida.membresia ?? '-'}</dd>
                                </div>
                                <div>
                                    <dt className="inline">Vence: </dt>
                                    <dd className="inline text-chalk">{elegida.vence ?? '-'}</dd>
                                </div>
                                <div>
                                    <dt className="inline">Pagado: </dt>
                                    <dd className="inline text-chalk">{pesos.format(elegida.abonado)}</dd>
                                </div>
                                <div>
                                    <dt className="inline">Debe: </dt>
                                    <dd className="inline font-semibold text-warn">
                                        {pesos.format(elegida.pendiente)}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    ) : (
                        <Campo
                            etiqueta="Busca al socio"
                            nombre="buscar_socio"
                            error={errors.id_inscripcion}
                            requerido
                            ayuda="Por nombre, RUT o correo. Solo aparece quien tiene saldo por pagar."
                        >
                            <Texto
                                nombre="buscar_socio"
                                tipo="search"
                                valor={busqueda}
                                alCambiar={setBusqueda}
                                placeholder="Escribe al menos dos letras"
                                autoFocus
                            />

                            {busqueda.trim().length >= 2 ? (
                                buscando ? (
                                    <p className="apoyo mt-2 text-fog">Buscando…</p>
                                ) : resultados && resultados.length === 0 ? (
                                    <p className="apoyo mt-2 text-fog">
                                        Nadie coincide, o a quien buscas no le queda nada por pagar.
                                    </p>
                                ) : resultados ? (
                                    <ul className="mt-2 max-h-64 divide-y divide-line overflow-y-auto rounded-control border border-line">
                                        {resultados.map((i) => (
                                            <li key={i.id}>
                                                <button
                                                    type="button"
                                                    onClick={() => elegir(i)}
                                                    className="flex w-full items-baseline justify-between gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-surface-2"
                                                >
                                                    <span className="min-w-0">
                                                        <span className="block truncate text-chalk">
                                                            {i.socio}
                                                        </span>
                                                        <span className="apoyo block text-fog">
                                                            {i.membresia ?? 'sin plan'}
                                                            {i.rut ? ` · ${i.rut}` : ''}
                                                        </span>
                                                    </span>
                                                    {/* Lo que debe va a la derecha: es el dato
                                                        que decide a cuál de dos homónimos cobrar. */}
                                                    <span className="shrink-0 font-medium tabular-nums text-warn">
                                                        {pesos.format(i.pendiente)}
                                                    </span>
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                ) : null
                            ) : null}
                        </Campo>
                    )}
                </Grupo>

                {/* El resto no aparece hasta que hay socio: sin saldo conocido,
                    «abona una parte» no tiene contra qué compararse. */}
                {elegida ? (
                    <>
                        <section className="rounded-panel border border-line bg-surface p-4">
                            <h2 className="mb-3 text-sm font-semibold text-chalk">¿Cómo paga?</h2>
                            <Cobro
                                total={pendiente}
                                forma={data.tipo_pago}
                                alCambiarForma={(v) => setData('tipo_pago', v)}
                                abono="abono"
                                monto={data.monto_abonado}
                                alCambiarMonto={(v) => setData('monto_abonado', v)}
                                metodo={data.id_metodo_pago}
                                alCambiarMetodo={(v) => setData('id_metodo_pago', v)}
                                metodosPago={metodosPago}
                                partes={partes}
                                setPartes={setPartes}
                                sinPendiente
                                maxPartes={2}
                                errores={{
                                    ...errors,
                                    detalle_pagos_mixto: errors.id_metodo_pago1 ?? errors.monto_metodo1 ?? errors.id_metodo_pago2 ?? errors.monto_metodo2,
                                }}
                            />
                            {data.tipo_pago === 'mixto' && partes[0]?.id_metodo_pago && partes[0]?.id_metodo_pago === partes[1]?.id_metodo_pago ? (
                                <p className="apoyo mt-2 text-warn">Los dos medios tienen que ser distintos.</p>
                            ) : null}
                        </section>

                        <Grupo titulo="Datos del cobro">
                            <div className="grid gap-3 sm:grid-cols-2">
                                <Campo etiqueta="Fecha" nombre="fecha_pago" error={errors.fecha_pago} requerido>
                                    <Texto
                                        nombre="fecha_pago"
                                        tipo="date"
                                        max={hoy}
                                        valor={data.fecha_pago}
                                        alCambiar={(v) => setData('fecha_pago', v)}
                                        error={errors.fecha_pago}
                                    />
                                </Campo>
                                <Campo
                                    etiqueta="Referencia"
                                    nombre="referencia_pago"
                                    error={errors.referencia_pago}
                                    ayuda="N.º de transferencia o comprobante."
                                >
                                    <Texto
                                        nombre="referencia_pago"
                                        valor={data.referencia_pago}
                                        alCambiar={(v) => setData('referencia_pago', v)}
                                        error={errors.referencia_pago}
                                    />
                                </Campo>
                            </div>

                            <Campo etiqueta="Observaciones" nombre="observaciones" error={errors.observaciones}>
                                <Area
                                    nombre="observaciones"
                                    valor={data.observaciones}
                                    alCambiar={(v) => setData('observaciones', v)}
                                    error={errors.observaciones}
                                />
                            </Campo>
                        </Grupo>
                    </>
                ) : null}

                <div className="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={processing || ! elegida || mixtoDescuadra}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {processing ? 'Registrando…' : 'Registrar pago'}
                    </button>
                    <Link href="/panel/pagos" className="text-sm text-fog transition-colors hover:text-chalk">
                        Cancelar
                    </Link>
                </div>
            </form>
        </>
    );
}
