import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';

import { Area, Campo, Grupo, Texto } from '@/components/Campo';

/**
 * Correccion de la ficha de un socio.
 *
 * SOLO SUS DATOS. La membresia y los pagos no se tocan aqui: tienen sus
 * propias pantallas —renovar, cobrar— con sus propias reglas. Esto es para
 * arreglar un telefono mal escrito o un correo que rebota, no para cambiar lo
 * que se cobro.
 */
export default function Editar({ cliente }) {
    const { data, setData, put, processing, errors } = useForm({
        run_pasaporte: cliente.run_pasaporte ?? '',
        nombres: cliente.nombres ?? '',
        apellido_paterno: cliente.apellido_paterno ?? '',
        apellido_materno: cliente.apellido_materno ?? '',
        celular: cliente.celular ?? '',
        email: cliente.email ?? '',
        direccion: cliente.direccion ?? '',
        fecha_nacimiento: cliente.fecha_nacimiento ?? '',
        contacto_emergencia: cliente.contacto_emergencia ?? '',
        telefono_emergencia: cliente.telefono_emergencia ?? '',
        observaciones: cliente.observaciones ?? '',
        es_menor_edad: cliente.es_menor_edad,
        consentimiento_apoderado: cliente.consentimiento_apoderado,
        apoderado_nombre: cliente.apoderado_nombre ?? '',
        apoderado_rut: cliente.apoderado_rut ?? '',
        apoderado_email: cliente.apoderado_email ?? '',
        apoderado_telefono: cliente.apoderado_telefono ?? '',
        apoderado_parentesco: cliente.apoderado_parentesco ?? '',
        apoderado_observaciones: cliente.apoderado_observaciones ?? '',
    });

    function enviar(e) {
        e.preventDefault();
        put(`/panel/clientes/${cliente.uuid}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title={`Editar · ${cliente.nombre}`} />

            <header className="mb-5">
                <Link
                    href={`/panel/clientes/${cliente.uuid}`}
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Volver a la ficha
                </Link>
                <h1 className="mt-1 text-lg font-semibold text-chalk">Editar ficha</h1>
                <p className="apoyo text-fog">{cliente.nombre}</p>
            </header>

            <form onSubmit={enviar} className="max-w-3xl space-y-5">
                <Grupo titulo="Quién es">
                    <Campo etiqueta="Nombres" nombre="nombres" error={errors.nombres} requerido>
                        <Texto
                            nombre="nombres"
                            valor={data.nombres}
                            alCambiar={(v) => setData('nombres', v)}
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
                        />
                    </Campo>

                    <Campo
                        etiqueta="RUT o pasaporte"
                        nombre="run_pasaporte"
                        error={errors.run_pasaporte}
                        ayuda="Con guion y dígito verificador. Puede quedar vacío."
                    >
                        <Texto
                            nombre="run_pasaporte"
                            valor={data.run_pasaporte}
                            alCambiar={(v) => setData('run_pasaporte', v)}
                            placeholder="12.345.678-9"
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
                        />
                    </Campo>
                </Grupo>

                <Grupo titulo="Cómo se le avisa">
                    <Campo etiqueta="Celular" nombre="celular" error={errors.celular} requerido>
                        <Texto
                            nombre="celular"
                            valor={data.celular}
                            alCambiar={(v) => setData('celular', v)}
                            placeholder="+56 9 1234 5678"
                        />
                    </Campo>

                    <Campo
                        etiqueta="Correo"
                        nombre="email"
                        error={errors.email}
                        requerido
                        ayuda="Ahí llegan los avisos de vencimiento."
                    >
                        <Texto
                            nombre="email"
                            tipo="email"
                            valor={data.email}
                            alCambiar={(v) => setData('email', v)}
                        />
                    </Campo>

                    <Campo etiqueta="Dirección" nombre="direccion" error={errors.direccion}>
                        <Texto
                            nombre="direccion"
                            valor={data.direccion}
                            alCambiar={(v) => setData('direccion', v)}
                        />
                    </Campo>
                </Grupo>

                <Grupo titulo="A quién llamar si pasa algo">
                    <Campo
                        etiqueta="Nombre"
                        nombre="contacto_emergencia"
                        error={errors.contacto_emergencia}
                    >
                        <Texto
                            nombre="contacto_emergencia"
                            valor={data.contacto_emergencia}
                            alCambiar={(v) => setData('contacto_emergencia', v)}
                        />
                    </Campo>

                    <Campo
                        etiqueta="Teléfono"
                        nombre="telefono_emergencia"
                        error={errors.telefono_emergencia}
                    >
                        <Texto
                            nombre="telefono_emergencia"
                            valor={data.telefono_emergencia}
                            alCambiar={(v) => setData('telefono_emergencia', v)}
                            placeholder="+56 9 1234 5678"
                        />
                    </Campo>
                </Grupo>

                <Grupo titulo="¿Es menor de edad?">
                    <label className="flex items-center gap-2 text-sm text-chalk">
                        <input
                            type="checkbox"
                            checked={data.es_menor_edad}
                            onChange={(e) => setData('es_menor_edad', e.target.checked)}
                            className="size-4 accent-[var(--color-volt)]"
                        />
                        Sí, necesita apoderado
                    </label>

                    {/* Al desmarcarlo, el servidor BORRA los datos del
                        apoderado: dejarlos escondidos haria que, si se vuelve a
                        marcar por error, aparecieran como si siguieran valiendo. */}
                    {data.es_menor_edad ? (
                        <>
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
                                    placeholder="12.345.678-9"
                                />
                            </Campo>

                            <Campo
                                etiqueta="Correo del apoderado"
                                nombre="apoderado_email"
                                error={errors.apoderado_email}
                                requerido
                                ayuda="Ahí se manda la confirmación de la inscripción."
                            >
                                <Texto
                                    nombre="apoderado_email"
                                    tipo="email"
                                    valor={data.apoderado_email}
                                    alCambiar={(v) => setData('apoderado_email', v)}
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
                                    placeholder="Madre, padre, tutor…"
                                />
                            </Campo>

                            <Campo
                                etiqueta="Autorización"
                                nombre="consentimiento_apoderado"
                                error={errors.consentimiento_apoderado}
                                requerido
                            >
                                <label className="flex items-center gap-2 text-sm text-chalk">
                                    <input
                                        type="checkbox"
                                        checked={data.consentimiento_apoderado}
                                        onChange={(e) =>
                                            setData('consentimiento_apoderado', e.target.checked)
                                        }
                                        className="size-4 accent-[var(--color-volt)]"
                                    />
                                    El apoderado autorizó la inscripción
                                </label>
                            </Campo>
                        </>
                    ) : null}
                </Grupo>

                <Grupo titulo="Observaciones">
                    <Campo
                        etiqueta="Notas"
                        nombre="observaciones"
                        error={errors.observaciones}
                        ayuda="Lesiones, restricciones, lo que haga falta saber."
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
                        disabled={processing}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        {processing ? 'Guardando…' : 'Guardar cambios'}
                    </button>

                    <Link
                        href={`/panel/clientes/${cliente.uuid}`}
                        className="apoyo text-fog hover:text-chalk"
                    >
                        Cancelar
                    </Link>
                </div>
            </form>
        </>
    );
}
