import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ArrowLeftIcon } from 'lucide-react';

import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

const hoy = new Date().toISOString().slice(0, 10);

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

const FORMAS = [
    { valor: 'completo', etiqueta: 'Paga todo el saldo' },
    { valor: 'abono', etiqueta: 'Abona una parte' },
    { valor: 'mixto', etiqueta: 'Reparte entre dos métodos' },
];

/**
 * Cobro de una inscripcion.
 *
 * Lo primero es ELEGIR A QUIEN se le cobra, y hasta que no se elige no se
 * ensena nada mas: el saldo pendiente manda sobre todo lo que viene despues
 * —cuanto se puede abonar, cuanto tienen que sumar los dos metodos— y sin saber
 * de quien hablamos esos campos no significan nada.
 */
export default function Crear({ preseleccionada, metodosPago, formToken }) {
    // A quién se le cobra. Si se llega desde una ficha, ya viene resuelta.
    const [elegida, setElegida] = useState(preseleccionada ?? null);
    const [busqueda, setBusqueda] = useState('');
    const [resultados, setResultados] = useState(null);
    const [buscando, setBuscando] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        form_submit_token: formToken,
        id_inscripcion: preseleccionada?.id ?? '',
        tipo_pago: 'completo',
        monto_abonado: '',
        id_metodo_pago: '',
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

    const opcionesMetodo = metodosPago.map((m) => ({ valor: String(m.id), etiqueta: m.nombre }));

    // En mixto los dos montos tienen que sumar EXACTAMENTE el saldo. Se dice
    // aquí mientras se escribe, en vez de dejar que el servidor lo rechace
    // después de haber rellenado el resto del formulario.
    const sumaMixto = (Number(data.monto_metodo1) || 0) + (Number(data.monto_metodo2) || 0);
    const mixtoDescuadra = data.tipo_pago === 'mixto' && sumaMixto !== pendiente;

    function enviar(e) {
        e.preventDefault();
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

            <form onSubmit={enviar} className="max-w-3xl space-y-5">
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
                                    <dd className="inline text-chalk">{elegida.membresia ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="inline">Vence: </dt>
                                    <dd className="inline text-chalk">{elegida.vence ?? '—'}</dd>
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
                        <Grupo titulo="¿Cómo paga?">
                            <Campo etiqueta="Forma de pago" nombre="tipo_pago" error={errors.tipo_pago} requerido>
                                <Seleccion
                                    nombre="tipo_pago"
                                    valor={data.tipo_pago}
                                    alCambiar={(v) => setData('tipo_pago', v)}
                                    opciones={FORMAS}
                                    error={errors.tipo_pago}
                                    vacio="Elige…"
                                />
                            </Campo>

                            {data.tipo_pago === 'completo' ? (
                                <p className="apoyo text-fog">
                                    Se cobra {pesos.format(pendiente)} y la membresía queda al día.
                                </p>
                            ) : null}

                            {data.tipo_pago === 'abono' ? (
                                <Campo
                                    etiqueta="Monto del abono"
                                    nombre="monto_abonado"
                                    error={errors.monto_abonado}
                                    requerido
                                    ayuda={`Entre $1.000 y ${pesos.format(pendiente)}.`}
                                >
                                    <Texto
                                        nombre="monto_abonado"
                                        tipo="number"
                                        min={1000}
                                        max={pendiente}
                                        valor={data.monto_abonado}
                                        alCambiar={(v) => setData('monto_abonado', v)}
                                        error={errors.monto_abonado}
                                    />
                                </Campo>
                            ) : null}

                            {data.tipo_pago !== 'mixto' ? (
                                <Campo
                                    etiqueta="Método de pago"
                                    nombre="id_metodo_pago"
                                    error={errors.id_metodo_pago}
                                    requerido
                                >
                                    <Seleccion
                                        nombre="id_metodo_pago"
                                        valor={data.id_metodo_pago}
                                        alCambiar={(v) => setData('id_metodo_pago', v)}
                                        opciones={opcionesMetodo}
                                        error={errors.id_metodo_pago}
                                    />
                                </Campo>
                            ) : (
                                <>
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <Campo
                                            etiqueta="Primer método"
                                            nombre="id_metodo_pago1"
                                            error={errors.id_metodo_pago1}
                                            requerido
                                        >
                                            <Seleccion
                                                nombre="id_metodo_pago1"
                                                valor={data.id_metodo_pago1}
                                                alCambiar={(v) => setData('id_metodo_pago1', v)}
                                                opciones={opcionesMetodo}
                                                error={errors.id_metodo_pago1}
                                            />
                                        </Campo>
                                        <Campo
                                            etiqueta="Monto"
                                            nombre="monto_metodo1"
                                            error={errors.monto_metodo1}
                                            requerido
                                        >
                                            <Texto
                                                nombre="monto_metodo1"
                                                tipo="number"
                                                min={1}
                                                valor={data.monto_metodo1}
                                                alCambiar={(v) => setData('monto_metodo1', v)}
                                                error={errors.monto_metodo1}
                                            />
                                        </Campo>
                                    </div>

                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <Campo
                                            etiqueta="Segundo método"
                                            nombre="id_metodo_pago2"
                                            error={errors.id_metodo_pago2}
                                            requerido
                                            ayuda="Tiene que ser distinto del primero."
                                        >
                                            <Seleccion
                                                nombre="id_metodo_pago2"
                                                valor={data.id_metodo_pago2}
                                                alCambiar={(v) => setData('id_metodo_pago2', v)}
                                                opciones={opcionesMetodo}
                                                error={errors.id_metodo_pago2}
                                            />
                                        </Campo>
                                        <Campo
                                            etiqueta="Monto"
                                            nombre="monto_metodo2"
                                            error={errors.monto_metodo2}
                                            requerido
                                        >
                                            <Texto
                                                nombre="monto_metodo2"
                                                tipo="number"
                                                min={1}
                                                valor={data.monto_metodo2}
                                                alCambiar={(v) => setData('monto_metodo2', v)}
                                                error={errors.monto_metodo2}
                                            />
                                        </Campo>
                                    </div>

                                    <p
                                        className={`apoyo ${mixtoDescuadra ? 'font-medium text-warn' : 'text-fog'}`}
                                        role="status"
                                    >
                                        Suman {pesos.format(sumaMixto)} de {pesos.format(pendiente)}
                                        {mixtoDescuadra
                                            ? ` · faltan ${pesos.format(Math.abs(pendiente - sumaMixto))}`
                                            : ' · cuadra'}
                                    </p>
                                </>
                            )}
                        </Grupo>

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
