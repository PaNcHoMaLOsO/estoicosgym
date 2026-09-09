import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';

import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';

const hoy = new Date().toISOString().slice(0, 10);

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/**
 * Alta de socio.
 *
 * El formulario CRECE segun lo que se necesite: primero la ficha, y solo si se
 * pide se abren la membresia y el pago. Ese es el mismo `flujo_cliente` que
 * entiende el servidor (solo_cliente / con_membresia / completo). Ensenar los
 * treinta campos de golpe para dar de alta a alguien que todavia no decide plan
 * era pedir que se rellenara lo que no toca.
 */
export default function Crear({ membresias, convenios, motivos, metodosPago, formToken }) {
    const { data, setData, post, processing, errors } = useForm({
        form_submit_token: formToken,
        flujo_cliente: 'solo_cliente',

        run_pasaporte: '',
        nombres: '',
        apellido_paterno: '',
        apellido_materno: '',
        celular: '',
        email: '',
        fecha_nacimiento: '',
        direccion: '',
        contacto_emergencia: '',
        telefono_emergencia: '',
        observaciones: '',

        es_menor_edad: false,
        consentimiento_apoderado: false,
        apoderado_nombre: '',
        apoderado_rut: '',
        apoderado_email: '',
        apoderado_telefono: '',
        apoderado_parentesco: '',

        id_membresia: '',
        id_convenio: '',
        fecha_inicio: hoy,
        id_motivo_descuento: '',
        descuento_manual: '',
        observaciones_inscripcion: '',

        tipo_pago: 'completo',
        monto_abonado: '',
        id_metodo_pago: '',
        fecha_pago: hoy,
        referencia_pago: '',
    });

    const conMembresia = data.flujo_cliente !== 'solo_cliente';
    const conPago = data.flujo_cliente === 'completo';

    const membresia = membresias.find((m) => String(m.id) === String(data.id_membresia));
    // Con convenio manda el precio de convenio. Se calcula aqui solo para
    // ENSENARLO: el precio que se guarda lo vuelve a resolver el servidor.
    const precioBase = membresia
        ? data.id_convenio && membresia.precio_convenio
            ? membresia.precio_convenio
            : membresia.precio
        : 0;
    const precioFinal = Math.max(0, precioBase - (Number(data.descuento_manual) || 0));

    const enviar = (e) => {
        e.preventDefault();
        post('/panel/clientes');
    };

    return (
        <>
            <Head title="Nuevo cliente" />

            <header className="mb-5 flex flex-wrap items-center gap-3">
                <Link
                    href="/panel/clientes"
                    className="rounded-control p-1.5 text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                    aria-label="Volver a clientes"
                >
                    <ArrowLeftIcon className="size-4" aria-hidden="true" />
                </Link>
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Nuevo cliente</h1>
                    <p className="apoyo text-fog">Registra al socio y, si ya lo decidió, su plan</p>
                </div>
            </header>

            <form onSubmit={enviar} className="flex flex-col gap-4">
                <Grupo titulo="Datos del socio">
                    <Campo etiqueta="Nombres" nombre="nombres" error={errors.nombres} requerido>
                        <Texto
                            nombre="nombres"
                            valor={data.nombres}
                            alCambiar={(v) => setData('nombres', v)}
                            error={errors.nombres}
                            autoFocus
                        />
                    </Campo>

                    <Campo
                        etiqueta="Apellido paterno"
                        nombre="apellido_paterno"
                        error={errors.apellido_paterno}
                        requerido
                    >
                        <Texto
                            nombre="apellido_paterno"
                            valor={data.apellido_paterno}
                            alCambiar={(v) => setData('apellido_paterno', v)}
                            error={errors.apellido_paterno}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Apellido materno"
                        nombre="apellido_materno"
                        error={errors.apellido_materno}
                    >
                        <Texto
                            nombre="apellido_materno"
                            valor={data.apellido_materno}
                            alCambiar={(v) => setData('apellido_materno', v)}
                            error={errors.apellido_materno}
                        />
                    </Campo>

                    <Campo
                        etiqueta="RUT o pasaporte"
                        nombre="run_pasaporte"
                        error={errors.run_pasaporte}
                        ayuda="Con puntos y guion: 12.345.678-9"
                    >
                        <Texto
                            nombre="run_pasaporte"
                            valor={data.run_pasaporte}
                            alCambiar={(v) => setData('run_pasaporte', v)}
                            error={errors.run_pasaporte}
                        />
                    </Campo>

                    <Campo etiqueta="Celular" nombre="celular" error={errors.celular} requerido>
                        <Texto
                            nombre="celular"
                            valor={data.celular}
                            alCambiar={(v) => setData('celular', v)}
                            error={errors.celular}
                            placeholder="+56 9 1234 5678"
                        />
                    </Campo>

                    <Campo etiqueta="Correo" nombre="email" error={errors.email} requerido>
                        <Texto
                            nombre="email"
                            tipo="email"
                            valor={data.email}
                            alCambiar={(v) => setData('email', v)}
                            error={errors.email}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Fecha de nacimiento"
                        nombre="fecha_nacimiento"
                        error={errors.fecha_nacimiento}
                    >
                        <Texto
                            nombre="fecha_nacimiento"
                            tipo="date"
                            valor={data.fecha_nacimiento}
                            alCambiar={(v) => setData('fecha_nacimiento', v)}
                            error={errors.fecha_nacimiento}
                        />
                    </Campo>

                    <Campo etiqueta="Dirección" nombre="direccion" error={errors.direccion}>
                        <Texto
                            nombre="direccion"
                            valor={data.direccion}
                            alCambiar={(v) => setData('direccion', v)}
                            error={errors.direccion}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Contacto de emergencia"
                        nombre="contacto_emergencia"
                        error={errors.contacto_emergencia}
                    >
                        <Texto
                            nombre="contacto_emergencia"
                            valor={data.contacto_emergencia}
                            alCambiar={(v) => setData('contacto_emergencia', v)}
                            error={errors.contacto_emergencia}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Teléfono de emergencia"
                        nombre="telefono_emergencia"
                        error={errors.telefono_emergencia}
                    >
                        <Texto
                            nombre="telefono_emergencia"
                            valor={data.telefono_emergencia}
                            alCambiar={(v) => setData('telefono_emergencia', v)}
                            error={errors.telefono_emergencia}
                        />
                    </Campo>

                    <div className="sm:col-span-2">
                        <Campo etiqueta="Observaciones" nombre="observaciones" error={errors.observaciones}>
                            <Area
                                nombre="observaciones"
                                valor={data.observaciones}
                                alCambiar={(v) => setData('observaciones', v)}
                                error={errors.observaciones}
                            />
                        </Campo>
                    </div>

                    <div className="sm:col-span-2">
                        <label className="flex items-center gap-2 text-sm text-chalk">
                            <input
                                type="checkbox"
                                checked={data.es_menor_edad}
                                onChange={(e) => setData('es_menor_edad', e.target.checked)}
                                className="size-4 rounded-[4px] border-line-strong"
                            />
                            Es menor de edad (requiere apoderado)
                        </label>
                    </div>
                </Grupo>

                {data.es_menor_edad ? (
                    <Grupo
                        titulo="Apoderado"
                        descripcion="Obligatorio para socios menores de edad"
                    >
                        <Campo
                            etiqueta="Nombre del apoderado"
                            nombre="apoderado_nombre"
                            error={errors.apoderado_nombre}
                            requerido
                        >
                            <Texto
                                nombre="apoderado_nombre"
                                valor={data.apoderado_nombre}
                                alCambiar={(v) => setData('apoderado_nombre', v)}
                                error={errors.apoderado_nombre}
                            />
                        </Campo>

                        <Campo
                            etiqueta="RUT del apoderado"
                            nombre="apoderado_rut"
                            error={errors.apoderado_rut}
                            requerido
                        >
                            <Texto
                                nombre="apoderado_rut"
                                valor={data.apoderado_rut}
                                alCambiar={(v) => setData('apoderado_rut', v)}
                                error={errors.apoderado_rut}
                            />
                        </Campo>

                        <Campo
                            etiqueta="Correo del apoderado"
                            nombre="apoderado_email"
                            error={errors.apoderado_email}
                            requerido
                        >
                            <Texto
                                nombre="apoderado_email"
                                tipo="email"
                                valor={data.apoderado_email}
                                alCambiar={(v) => setData('apoderado_email', v)}
                                error={errors.apoderado_email}
                            />
                        </Campo>

                        <Campo
                            etiqueta="Teléfono del apoderado"
                            nombre="apoderado_telefono"
                            error={errors.apoderado_telefono}
                            requerido
                        >
                            <Texto
                                nombre="apoderado_telefono"
                                valor={data.apoderado_telefono}
                                alCambiar={(v) => setData('apoderado_telefono', v)}
                                error={errors.apoderado_telefono}
                            />
                        </Campo>

                        <Campo
                            etiqueta="Parentesco"
                            nombre="apoderado_parentesco"
                            error={errors.apoderado_parentesco}
                            requerido
                        >
                            <Texto
                                nombre="apoderado_parentesco"
                                valor={data.apoderado_parentesco}
                                alCambiar={(v) => setData('apoderado_parentesco', v)}
                                error={errors.apoderado_parentesco}
                            />
                        </Campo>

                        <div className="sm:col-span-2">
                            <label className="flex items-start gap-2 text-sm text-chalk">
                                <input
                                    type="checkbox"
                                    checked={data.consentimiento_apoderado}
                                    onChange={(e) =>
                                        setData('consentimiento_apoderado', e.target.checked)
                                    }
                                    className="mt-0.5 size-4 rounded-[4px] border-line-strong"
                                />
                                Confirmo que el apoderado autoriza la inscripción
                            </label>
                            {errors.consentimiento_apoderado ? (
                                <p className="apoyo mt-1 text-danger" role="alert">
                                    {errors.consentimiento_apoderado}
                                </p>
                            ) : null}
                        </div>
                    </Grupo>
                ) : null}

                <section className="rounded-panel border border-line bg-surface p-4">
                    <h2 className="text-sm font-semibold text-chalk">¿Qué se registra ahora?</h2>
                    <p className="apoyo mb-3 text-fog">
                        Se puede dar de alta al socio y dejar el plan para después
                    </p>

                    <div className="flex flex-col gap-2">
                        {[
                            ['solo_cliente', 'Solo la ficha del socio'],
                            ['con_membresia', 'Ficha y membresía, sin cobrar todavía'],
                            ['completo', 'Ficha, membresía y pago'],
                        ].map(([valor, etiqueta]) => (
                            <label key={valor} className="flex items-center gap-2 text-sm text-chalk">
                                <input
                                    type="radio"
                                    name="flujo_cliente"
                                    value={valor}
                                    checked={data.flujo_cliente === valor}
                                    onChange={(e) => setData('flujo_cliente', e.target.value)}
                                    className="size-4"
                                />
                                {etiqueta}
                            </label>
                        ))}
                    </div>
                </section>

                {conMembresia ? (
                    <Grupo titulo="Membresía">
                        <Campo
                            etiqueta="Plan"
                            nombre="id_membresia"
                            error={errors.id_membresia}
                            requerido
                        >
                            <Seleccion
                                nombre="id_membresia"
                                valor={data.id_membresia}
                                alCambiar={(v) => setData('id_membresia', v)}
                                error={errors.id_membresia}
                                opciones={membresias.map((m) => ({
                                    valor: m.id,
                                    etiqueta: `${m.nombre} — ${pesos.format(m.precio)}`,
                                }))}
                            />
                        </Campo>

                        <Campo etiqueta="Convenio" nombre="id_convenio" error={errors.id_convenio}>
                            <Seleccion
                                nombre="id_convenio"
                                valor={data.id_convenio}
                                alCambiar={(v) => setData('id_convenio', v)}
                                error={errors.id_convenio}
                                vacio="Sin convenio"
                                opciones={convenios.map((c) => ({ valor: c.id, etiqueta: c.nombre }))}
                            />
                        </Campo>

                        <Campo
                            etiqueta="Fecha de inicio"
                            nombre="fecha_inicio"
                            error={errors.fecha_inicio}
                            requerido
                        >
                            <Texto
                                nombre="fecha_inicio"
                                tipo="date"
                                valor={data.fecha_inicio}
                                alCambiar={(v) => setData('fecha_inicio', v)}
                                error={errors.fecha_inicio}
                                min={hoy}
                            />
                        </Campo>

                        <Campo
                            etiqueta="Descuento"
                            nombre="descuento_manual"
                            error={errors.descuento_manual}
                            ayuda={precioBase > 0 ? `Precio del plan: ${pesos.format(precioBase)}` : undefined}
                        >
                            <Texto
                                nombre="descuento_manual"
                                tipo="number"
                                min="0"
                                valor={data.descuento_manual}
                                alCambiar={(v) => setData('descuento_manual', v)}
                                error={errors.descuento_manual}
                            />
                        </Campo>

                        <Campo
                            etiqueta="Motivo del descuento"
                            nombre="id_motivo_descuento"
                            error={errors.id_motivo_descuento}
                        >
                            <Seleccion
                                nombre="id_motivo_descuento"
                                valor={data.id_motivo_descuento}
                                alCambiar={(v) => setData('id_motivo_descuento', v)}
                                error={errors.id_motivo_descuento}
                                opciones={motivos.map((m) => ({ valor: m.id, etiqueta: m.nombre }))}
                            />
                        </Campo>

                        <div className="sm:col-span-2">
                            <Campo
                                etiqueta="Observaciones de la inscripción"
                                nombre="observaciones_inscripcion"
                                error={errors.observaciones_inscripcion}
                            >
                                <Area
                                    nombre="observaciones_inscripcion"
                                    valor={data.observaciones_inscripcion}
                                    alCambiar={(v) => setData('observaciones_inscripcion', v)}
                                    error={errors.observaciones_inscripcion}
                                />
                            </Campo>
                        </div>

                        {membresia ? (
                            <div className="rounded-control bg-surface-2 px-3 py-2 sm:col-span-2">
                                <p className="apoyo text-fog">Total a cobrar</p>
                                <p className="text-lg font-semibold tabular-nums text-chalk">
                                    {pesos.format(precioFinal)}
                                </p>
                            </div>
                        ) : null}
                    </Grupo>
                ) : null}

                {conPago ? (
                    <Grupo titulo="Pago">
                        <Campo etiqueta="Tipo de pago" nombre="tipo_pago" error={errors.tipo_pago} requerido>
                            <Seleccion
                                nombre="tipo_pago"
                                valor={data.tipo_pago}
                                alCambiar={(v) => setData('tipo_pago', v)}
                                error={errors.tipo_pago}
                                vacio="Seleccione…"
                                opciones={[
                                    { valor: 'completo', etiqueta: 'Pago completo' },
                                    { valor: 'parcial', etiqueta: 'Abono' },
                                    { valor: 'pendiente', etiqueta: 'Queda pendiente' },
                                    { valor: 'mixto', etiqueta: 'Mixto' },
                                ]}
                            />
                        </Campo>

                        {/* «Completo» cobra el total y «pendiente» no cobra nada:
                            en esos dos el monto lo pone el servidor y pedirlo
                            aqui solo daria pie a que no cuadre. */}
                        {data.tipo_pago === 'parcial' || data.tipo_pago === 'mixto' ? (
                            <Campo
                                etiqueta="Monto abonado"
                                nombre="monto_abonado"
                                error={errors.monto_abonado}
                                requerido
                                ayuda={`De un total de ${pesos.format(precioFinal)}`}
                            >
                                <Texto
                                    nombre="monto_abonado"
                                    tipo="number"
                                    min="0"
                                    valor={data.monto_abonado}
                                    alCambiar={(v) => setData('monto_abonado', v)}
                                    error={errors.monto_abonado}
                                />
                            </Campo>
                        ) : null}

                        {data.tipo_pago !== 'pendiente' ? (
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
                                    error={errors.id_metodo_pago}
                                    opciones={metodosPago.map((m) => ({
                                        valor: m.id,
                                        etiqueta: m.nombre,
                                    }))}
                                />
                            </Campo>
                        ) : null}

                        <Campo
                            etiqueta="Fecha de pago"
                            nombre="fecha_pago"
                            error={errors.fecha_pago}
                            requerido
                        >
                            <Texto
                                nombre="fecha_pago"
                                tipo="date"
                                valor={data.fecha_pago}
                                alCambiar={(v) => setData('fecha_pago', v)}
                                error={errors.fecha_pago}
                                max={hoy}
                            />
                        </Campo>

                        <Campo
                            etiqueta="Referencia"
                            nombre="referencia_pago"
                            error={errors.referencia_pago}
                            ayuda="N.º de transferencia o comprobante"
                        >
                            <Texto
                                nombre="referencia_pago"
                                valor={data.referencia_pago}
                                alCambiar={(v) => setData('referencia_pago', v)}
                                error={errors.referencia_pago}
                            />
                        </Campo>
                    </Grupo>
                ) : null}

                <div className="flex items-center gap-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        {processing ? 'Guardando…' : 'Registrar socio'}
                    </button>

                    <Link
                        href="/panel/clientes"
                        className="rounded-control px-4 py-2 text-sm text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                    >
                        Cancelar
                    </Link>
                </div>
            </form>
        </>
    );
}
