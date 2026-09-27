import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, CheckIcon } from 'lucide-react';

import { Area, Campo, Grupo, Texto } from '@/components/Campo';
import { Botones } from '@/components/Cobro';
import { PREFIJO, formatearRut, rutValido, soloPrefijo } from '@/lib/socio';
import useAvisoAlSalir from '@/lib/useAvisoAlSalir';

/**
 * Corrección de la ficha de un socio.
 *
 * SOLO SUS DATOS. La membresía y los pagos no se tocan aquí: tienen sus
 * propias pantallas —renovar, cobrar— con sus propias reglas. Esto es para
 * arreglar un teléfono mal escrito o un correo que rebota, no para cambiar lo
 * que se cobró.
 *
 * Escribe igual que el alta (lib/socio.js): el RUT se ordena solo con puntos
 * y guion y avisa si el dígito verificador no calza; el celular trae el +56 9.
 * El celular es obligatorio solo si ya tenía: a los de las planillas, que casi
 * nunca traían, no se les obliga a inventar uno para corregir el nombre.
 */

/** «+56 9 …» si está vacío, para escribir solo los ocho dígitos. */
const conPrefijo = (telefono) => (telefono && String(telefono).trim() !== '' ? telefono : PREFIJO);

/** Si el RUT se ve completo pero el verificador no calza, se dice al tiro. */
function AvisoDeRut({ rut }) {
    const limpio = String(rut ?? '').replace(/[^0-9kK]/g, '');

    if (limpio.length < 8) {
        return null;
    }

    return rutValido(rut) ? (
        <span className="apoyo inline-flex items-center gap-1 text-ok">
            <CheckIcon className="size-3.5" aria-hidden="true" /> RUT válido
        </span>
    ) : (
        <span className="apoyo text-warn">El dígito verificador no calza: revisa el carnet.</span>
    );
}

export default function Editar({ cliente }) {
    const { data, setData, put, processing, errors, isDirty, transform } = useForm({
        run_pasaporte: cliente.run_pasaporte ?? '',
        nombres: cliente.nombres ?? '',
        apellido_paterno: cliente.apellido_paterno ?? '',
        apellido_materno: cliente.apellido_materno ?? '',
        celular: conPrefijo(cliente.celular),
        email: cliente.email ?? '',
        direccion: cliente.direccion ?? '',
        fecha_nacimiento: cliente.fecha_nacimiento ?? '',
        contacto_emergencia: cliente.contacto_emergencia ?? '',
        telefono_emergencia: conPrefijo(cliente.telefono_emergencia),
        observaciones: cliente.observaciones ?? '',
        es_menor_edad: Boolean(cliente.es_menor_edad),
        consentimiento_apoderado: Boolean(cliente.consentimiento_apoderado),
        apoderado_nombre: cliente.apoderado_nombre ?? '',
        apoderado_rut: cliente.apoderado_rut ?? '',
        apoderado_email: cliente.apoderado_email ?? '',
        apoderado_telefono: conPrefijo(cliente.apoderado_telefono),
        apoderado_parentesco: cliente.apoderado_parentesco ?? '',
        apoderado_observaciones: cliente.apoderado_observaciones ?? '',
    });

    // Sin guardar y con algo escrito: pregunta antes de salir.
    const tocar = useAvisoAlSalir(isDirty && ! processing);

    // Un pasaporte trae letras: ahí no se ordena como RUT.
    const esPasaporte = /[A-JL-Za-jl-z]/.test(data.run_pasaporte);
    const celularObligatorio = Boolean(cliente.celular);

    function enviar(e) {
        e.preventDefault();

        // El prefijo solo, sin número, es un teléfono vacío.
        transform((d) => ({
            ...d,
            celular: soloPrefijo(d.celular) ? '' : d.celular,
            telefono_emergencia: soloPrefijo(d.telefono_emergencia) ? '' : d.telefono_emergencia,
            apoderado_telefono: soloPrefijo(d.apoderado_telefono) ? '' : d.apoderado_telefono,
            tipo_documento: esPasaporte ? 'pasaporte' : 'rut',
        }));

        put(`/panel/clientes/${cliente.uuid}`, { preserveScroll: true });
    }

    const texto = (clave, extra = {}) => ({
        nombre: clave,
        valor: data[clave],
        error: errors[clave],
        alCambiar: (v) => setData(clave, v),
        ...extra,
    });

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
                <h1 className="mt-1 text-xl font-semibold text-chalk">Editar ficha</h1>
                <p className="mt-0.5 text-sm text-fog">{cliente.nombre}</p>
            </header>

            <form onSubmit={enviar} {...tocar} className="max-w-4xl">
                <div className="grid items-start gap-4 lg:grid-cols-2">
                    <Grupo titulo="Quién es">
                        <Campo etiqueta="Nombres" nombre="nombres" error={errors.nombres} requerido>
                            <Texto {...texto('nombres', { autoComplete: 'off' })} />
                        </Campo>

                        <Campo etiqueta="Apellido paterno" nombre="apellido_paterno" error={errors.apellido_paterno} requerido>
                            <Texto {...texto('apellido_paterno', { autoComplete: 'off' })} />
                        </Campo>

                        <Campo etiqueta="Apellido materno" nombre="apellido_materno" error={errors.apellido_materno}>
                            <Texto {...texto('apellido_materno', { autoComplete: 'off' })} />
                        </Campo>

                        <Campo etiqueta="Fecha de nacimiento" nombre="fecha_nacimiento" error={errors.fecha_nacimiento}>
                            <Texto {...texto('fecha_nacimiento', { tipo: 'date' })} />
                        </Campo>

                        <div className="sm:col-span-2">
                            <Campo
                                etiqueta="RUT o pasaporte"
                                nombre="run_pasaporte"
                                error={errors.run_pasaporte}
                                ayuda={data.run_pasaporte ? null : 'Puede quedar vacío.'}
                            >
                                <Texto
                                    {...texto('run_pasaporte', {
                                        alCambiar: (v) => setData('run_pasaporte', /[A-JL-Za-jl-z]/.test(v) ? v.toUpperCase().replace(/[^A-Z0-9]/g, '') : formatearRut(v)),
                                        placeholder: '12.345.678-9',
                                        autoComplete: 'off',
                                    })}
                                />
                                {esPasaporte ? null : <AvisoDeRut rut={data.run_pasaporte} />}
                            </Campo>
                        </div>
                    </Grupo>

                    <Grupo titulo="Cómo se le avisa">
                        <Campo
                            etiqueta="Celular"
                            nombre="celular"
                            error={errors.celular}
                            requerido={celularObligatorio}
                            ayuda={celularObligatorio ? null : 'No tiene: pídeselo, así le llegan los avisos.'}
                        >
                            <Texto {...texto('celular', { tipo: 'tel', inputMode: 'tel' })} />
                        </Campo>

                        <Campo etiqueta="Correo" nombre="email" error={errors.email}>
                            <Texto {...texto('email', { tipo: 'email', placeholder: 'nombre@correo.cl' })} />
                        </Campo>

                        <div className="sm:col-span-2">
                            <Campo etiqueta="Dirección" nombre="direccion" error={errors.direccion}>
                                <Texto {...texto('direccion')} />
                            </Campo>
                        </div>
                    </Grupo>

                    <Grupo titulo="A quién llamar si pasa algo">
                        <Campo etiqueta="Nombre" nombre="contacto_emergencia" error={errors.contacto_emergencia}>
                            <Texto {...texto('contacto_emergencia')} />
                        </Campo>

                        <Campo etiqueta="Teléfono" nombre="telefono_emergencia" error={errors.telefono_emergencia}>
                            <Texto {...texto('telefono_emergencia', { tipo: 'tel', inputMode: 'tel' })} />
                        </Campo>
                    </Grupo>

                    <Grupo titulo="Observaciones">
                        <div className="sm:col-span-2">
                            <Campo
                                etiqueta="Notas"
                                nombre="observaciones"
                                error={errors.observaciones}
                                ayuda="Lesiones, restricciones, lo que haga falta saber."
                            >
                                <Area {...texto('observaciones')} filas={3} />
                            </Campo>
                        </div>
                    </Grupo>

                    <div className="lg:col-span-2">
                        <Grupo titulo="¿Es menor de edad?">
                            <div className="sm:col-span-2">
                                <Botones
                                    opciones={[
                                        { valor: 'no', etiqueta: 'No' },
                                        { valor: 'si', etiqueta: 'Sí, con apoderado' },
                                    ]}
                                    valor={data.es_menor_edad ? 'si' : 'no'}
                                    alElegir={(v) => setData('es_menor_edad', v === 'si')}
                                    nombre="¿Es menor de edad?"
                                    columnas="grid-cols-2 sm:max-w-sm"
                                    compacto
                                />
                            </div>

                            {/* Al marcar «No», el servidor BORRA los datos del
                                apoderado: dejarlos escondidos haría que, si se
                                vuelve a marcar por error, aparecieran como si
                                siguieran valiendo. */}
                            {data.es_menor_edad ? (
                                <>
                                    <Campo etiqueta="Nombre del apoderado" nombre="apoderado_nombre" error={errors.apoderado_nombre} requerido>
                                        <Texto {...texto('apoderado_nombre')} />
                                    </Campo>

                                    <Campo etiqueta="RUT del apoderado" nombre="apoderado_rut" error={errors.apoderado_rut} requerido>
                                        <Texto
                                            {...texto('apoderado_rut', {
                                                alCambiar: (v) => setData('apoderado_rut', formatearRut(v)),
                                                placeholder: '12.345.678-9',
                                            })}
                                        />
                                        <AvisoDeRut rut={data.apoderado_rut} />
                                    </Campo>

                                    <Campo
                                        etiqueta="Correo del apoderado"
                                        nombre="apoderado_email"
                                        error={errors.apoderado_email}
                                        requerido
                                        ayuda="Ahí se manda la confirmación de la inscripción."
                                    >
                                        <Texto {...texto('apoderado_email', { tipo: 'email' })} />
                                    </Campo>

                                    <Campo etiqueta="Teléfono del apoderado" nombre="apoderado_telefono" error={errors.apoderado_telefono} requerido>
                                        <Texto {...texto('apoderado_telefono', { tipo: 'tel', inputMode: 'tel' })} />
                                    </Campo>

                                    <Campo etiqueta="Parentesco" nombre="apoderado_parentesco" error={errors.apoderado_parentesco} requerido>
                                        <Texto {...texto('apoderado_parentesco', { placeholder: 'Madre, padre, tutor…' })} />
                                    </Campo>

                                    <Campo etiqueta="Autorización" nombre="consentimiento_apoderado" error={errors.consentimiento_apoderado} requerido>
                                        <label className="flex items-center gap-2 text-sm text-chalk">
                                            <input
                                                type="checkbox"
                                                checked={data.consentimiento_apoderado}
                                                onChange={(e) => setData('consentimiento_apoderado', e.target.checked)}
                                                className="size-4 accent-[var(--color-volt)]"
                                            />
                                            El apoderado autorizó la inscripción
                                        </label>
                                    </Campo>
                                </>
                            ) : null}
                        </Grupo>
                    </div>
                </div>

                {/* Pegada abajo: en una ficha larga, «Guardar» no se pierde al bajar. */}
                <div className="sticky bottom-0 z-10 mt-4 flex flex-wrap items-center gap-3 rounded-panel border border-line bg-surface/95 px-4 py-3 backdrop-blur">
                    <button
                        type="submit"
                        disabled={processing || ! isDirty}
                        className="rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-40"
                    >
                        {processing ? 'Guardando…' : 'Guardar cambios'}
                    </button>

                    <Link href={`/panel/clientes/${cliente.uuid}`} className="text-sm text-fog hover:text-chalk">
                        Cancelar
                    </Link>

                    <span className="apoyo ml-auto text-fog">{isDirty ? 'Hay cambios sin guardar.' : 'Sin cambios.'}</span>
                </div>
            </form>
        </>
    );
}
