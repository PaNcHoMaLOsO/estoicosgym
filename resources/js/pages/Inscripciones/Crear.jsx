import useAvisoAlSalir from '@/lib/useAvisoAlSalir';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { ArrowLeftIcon, UserPlusIcon } from 'lucide-react';

import Cobro, { Botones, detalleDePartes, metodoPorDefecto, partesIniciales } from '@/components/Cobro';
import Nota from '@/components/Nota';
import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

const hoy = new Date().toISOString().slice(0, 10);

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});


/**
 * Alta de inscripcion.
 *
 * Va de arriba abajo en el orden en que se pregunta en el meson: a quien, que
 * plan, y como paga. Cada bloque aparece cuando el anterior esta resuelto,
 * porque ninguno significa nada sin el de antes —«abona una parte» de que, si
 * todavia no hay plan elegido—.
 *
 * La pantalla vieja era un asistente de tres pasos con la lista COMPLETA de
 * socios dentro y 2.074 lineas de Blade. Aqui el socio se busca.
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

export default function Crear({ preseleccionado, membresias, convenios, motivos, metodosPago, formToken, volverA = '', preciosDeConvenio = {} }) {
    const [socio, setSocio] = useState(preseleccionado ?? null);
    const [busqueda, setBusqueda] = useState('');
    const [resultados, setResultados] = useState(null);
    const [buscando, setBuscando] = useState(false);

    // Las partes del pago mixto viven aparte del formulario: son una lista que
    // crece y el backend las espera como un solo campo JSON.
    const [partes, setPartes] = useState(() => partesIniciales(metodosPago));

    const { data, setData, post, processing, errors, isDirty } = useForm({
        // De dónde se vino: si fue de la ficha de un socio, se vuelve allí.
        volver: volverA,
        form_submit_token: formToken,
        id_cliente: preseleccionado?.id ?? '',
        id_membresia: '',
        id_convenio: '',
        id_motivo_descuento: '',
        descuento_aplicado: '',
        fecha_inicio: hoy,
        observaciones: '',
        tipo_pago: 'completo',
        monto_abonado: '',
        // Marcado en efectivo: es lo que más se usa en el mesón.
        id_metodo_pago: metodoPorDefecto(metodosPago),
        detalle_pagos_mixto: '',
        fecha_pago: hoy,
    });

    /*
     * Al socio se le BUSCA, no se le elige de una lista.
     *
     * Se espera a que deje de escribir: sin la espera, cada tecla seria una
     * consulta al servidor.
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
                    `/panel/inscripciones/buscar-socio?q=${encodeURIComponent(busqueda)}`,
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                );
                const j = await r.json();
                setResultados(j.clientes ?? []);
            } catch (e) {
                setResultados([]);
            } finally {
                setBuscando(false);
            }
        }, 300);

        return () => clearTimeout(temporizador);
    }, [busqueda]);

    const plan = useMemo(
        () => membresias.find((m) => String(m.id) === String(data.id_membresia)) ?? null,
        [membresias, data.id_membresia],
    );

    /*
     * El total, a la vista mientras se elige.
     *
     * Es una COPIA de lo que hace el servidor, para que quien atiende pueda
     * decir el precio en voz alta sin guardar primero. Quien decide cuanto se
     * cobra sigue siendo el servidor: aqui no viaja ningun precio.
     */
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


    function elegirSocio(cliente) {
        setSocio(cliente);
        setData('id_cliente', cliente.id);
        setBusqueda('');
        setResultados(null);
    }

    function cambiarSocio() {
        setSocio(null);
        setData('id_cliente', '');
    }


    // Sin guardar y con algo escrito: pregunta antes de salir.
    const tocar = useAvisoAlSalir(isDirty && ! processing);

    function enviar(e) {
        e.preventDefault();

        // El detalle mixto se arma justo antes de enviar. El nombre del metodo
        // se manda tambien: queda escrito en las observaciones del pago, y si
        // manana se renombra el metodo, el recibo viejo sigue diciendo con que
        // se pago de verdad.
        const detalle = detalleDePartes(partes, metodosPago);

        post('/panel/inscripciones', {
            preserveScroll: true,
            data: { ...data, detalle_pagos_mixto: JSON.stringify(detalle) },
        });
    }



    return (
        <>
            <Head title="Nueva inscripción" />

            <header className="mb-5">
                <Link
                    href="/panel/inscripciones"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Inscripciones
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Nueva inscripción</h1>
            </header>

            <form onSubmit={enviar} {...tocar} className="max-w-3xl space-y-5">
                <Grupo titulo="¿A quién se inscribe?">
                    {socio ? (
                        <div className="rounded-panel border border-line bg-surface-2 p-3 text-sm">
                            <div className="flex items-start justify-between gap-3">
                                <p className="font-medium text-chalk">
                                    {socio.nombre}
                                    <span className="apoyo block text-fog">
                                        {socio.rut ?? 'sin RUT'}
                                        {socio.email ? ` · ${socio.email}` : ''}
                                    </span>
                                </p>
                                <button
                                    type="button"
                                    onClick={cambiarSocio}
                                    className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                                >
                                    Cambiar
                                </button>
                            </div>

                            {/* Un menor necesita apoderado con correo: si falta,
                                la confirmacion al tutor no sale y nadie se entera. */}
                            {socio.menor ? (
                                <p className="apoyo mt-2 text-warn">
                                    Es menor de edad: se avisará también al apoderado.
                                </p>
                            ) : null}

                            {/* Se dice para que no sorprenda: venderle el plan lo
                                vuelve a dar de alta, sin pasar por su ficha. */}
                            {socio.de_baja ? (
                                <p className="apoyo mt-2 text-fog">
                                    Estaba dado de baja: al guardar la membresía queda activo otra vez.
                                </p>
                            ) : null}
                        </div>
                    ) : (
                        <Campo
                            etiqueta="Busca al socio"
                            nombre="buscar_socio"
                            error={errors.id_cliente}
                            requerido
                            ayuda="Por nombre, RUT o correo. Solo aparece quien no tiene membresía vigente."
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
                                    /*
                                     * SI NO ESTÁ, SE LE DA DE ALTA DESDE AQUÍ. Antes esto era un
                                     * callejón: «nadie coincide», y había que salir a Clientes,
                                     * crearlo y volver a empezar. El alta de socio ya trae el plan
                                     * y el pago en la misma pantalla, así que se salta allá con lo
                                     * que se escribió ya puesto. Va por sessionStorage y no en la
                                     * dirección: un RUT no tiene por qué quedar en el historial.
                                     */
                                    <div className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2 rounded-control border border-line bg-surface-2 px-3 py-2.5">
                                        <p className="apoyo min-w-0 flex-1 text-fog">
                                            Nadie coincide, o ya tiene una membresía vigente.
                                        </p>
                                        <button
                                            type="button"
                                            data-sin-aviso
                                            onClick={() => {
                                                try {
                                                    sessionStorage.setItem('alta-desde-busqueda', busqueda.trim());
                                                } catch {
                                                    // Sin almacenamiento el alta abre vacía, que es como abría siempre.
                                                }
                                                router.visit('/panel/clientes/crear');
                                            }}
                                            className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                                        >
                                            <UserPlusIcon className="size-4" aria-hidden="true" />
                                            Inscribirlo como socio nuevo
                                        </button>
                                    </div>
                                ) : resultados ? (
                                    <ul className="mt-2 max-h-64 divide-y divide-line overflow-y-auto rounded-control border border-line">
                                        {resultados.map((c) => (
                                            <li key={c.id}>
                                                <button
                                                    type="button"
                                                    onClick={() => elegirSocio(c)}
                                                    className="block w-full px-3 py-2 text-left text-sm transition-colors hover:bg-surface-2"
                                                >
                                                    <span className="block truncate text-chalk">{c.nombre}</span>
                                                    <span className="apoyo block text-fog">
                                                        {c.rut ?? 'sin RUT'}
                                                        {c.email ? ` · ${c.email}` : ''}
                                                        {c.de_baja ? ' · de baja' : ''}
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

                {/* Nada de esto aparece sin socio: el precio, el convenio y la
                    forma de pago se preguntan sobre alguien concreto. */}
                {socio ? (
                    <>
                        <Grupo titulo="¿Qué plan?">
                            <Campo
                                etiqueta="Plan"
                                nombre="id_membresia"
                                error={errors.id_membresia}
                                requerido
                                ayuda={
                                    membresias.length === 0
                                        ? 'No hay ningún plan con precio vigente. Cárgalo en Config. → Membresías.'
                                        : undefined
                                }
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
                                ayuda="Desde este día corre la membresía. El vencimiento se calcula solo."
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
                                    /*
                                     * La rebaja NO sale del convenio: sale del
                                     * precio de convenio que tenga cargado ESE
                                     * plan. Un plan sin ese precio no rebaja
                                     * nada, elijas el convenio que elijas, y
                                     * antes eso pasaba en silencio: se dejaba
                                     * el convenio puesto creyendo que aplicaba.
                                     */
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
                                ayuda="En pesos, aparte del convenio. Déjalo vacío si no hay."
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
                                    ayuda="Queda anotado para cuando alguien pregunte por qué pagó menos."
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
                        </Grupo>

                        {/* El desglose, no solo el total: quien atiende tiene que
                            poder explicar de dónde sale cada rebaja. */}
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
                                            <dd className="tabular-nums text-chalk">
                                                −{pesos.format(cuenta.porConvenio)}
                                            </dd>
                                        </div>
                                    ) : null}

                                    {cuenta.manual > 0 ? (
                                        <div className="flex justify-between gap-3">
                                            <dt className="text-fog">Descuento adicional</dt>
                                            <dd className="tabular-nums text-chalk">
                                                −{pesos.format(cuenta.manual)}
                                            </dd>
                                        </div>
                                    ) : null}

                                    <div className="flex justify-between gap-3 border-t border-line pt-1">
                                        <dt className="font-medium text-chalk">Total</dt>
                                        <dd className="font-semibold tabular-nums text-chalk">
                                            {pesos.format(cuenta.final)}
                                        </dd>
                                    </div>
                                </dl>
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
                                        Queda inscrito debiendo {pesos.format(total)}. Aparecerá en Pagos como pendiente de cobro.
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
                                ayuda="Opcional. Lo que haga falta recordar de esta inscripción."
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
                                {processing ? 'Guardando…' : 'Inscribir'}
                            </button>

                            <Link href="/panel/inscripciones" className="apoyo text-fog hover:text-chalk">
                                Cancelar
                            </Link>
                        </div>
                    </>
                ) : null}
            </form>
        </>
    );
}
