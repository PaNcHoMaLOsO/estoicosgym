import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { ArrowLeftIcon, CameraIcon, ImageIcon } from 'lucide-react';

import { Area, Campo, Grupo, Seleccion, Texto } from '@/components/Campo';
import CamaraFoto from '@/components/CamaraFoto';
import Retrato from '@/components/Retrato';

/**
 * La foto del socio al darlo de alta.
 *
 * ES OPCIONAL, y se dice que lo es. Sirve para reconocer a quien llega al
 * mesón sin tener que preguntarle el RUT, pero nadie debería quedarse sin
 * inscribirse por no querer que le retraten: sin foto la ficha sale con sus
 * iniciales y funciona igual.
 */
function CampoFoto({ archivo, nombre, error, alElegir }) {
    const selector = useRef(null);
    const [vistaPrevia, setVistaPrevia] = useState(null);
    // La cámara del mesón: es como se saca la foto con la persona delante.
    const [conCamara, setConCamara] = useState(false);

    // La vista previa se crea y se SUELTA: cada createObjectURL reserva
    // memoria hasta que alguien la libera, y elegir cinco fotos seguidas
    // dejaría cuatro colgadas.
    useEffect(() => {
        if (! archivo) {
            setVistaPrevia(null);

            return undefined;
        }

        const url = URL.createObjectURL(archivo);
        setVistaPrevia(url);

        return () => URL.revokeObjectURL(url);
    }, [archivo]);

    return (
        <div className="flex items-center gap-3">
            <Retrato nombre={nombre} foto={vistaPrevia} tamano="lg" ampliable />

            <div>
                <input
                    ref={selector}
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    id="foto_perfil"
                    onChange={(e) => alElegir(e.target.files?.[0] ?? null)}
                    className="hidden"
                    tabIndex={-1}
                />

                <div className="flex flex-wrap items-center gap-2">
                    {/* PRIMERO LA CÁMARA y después el archivo: en el mesón la
                        persona está delante, y buscar un archivo en el disco es
                        lo raro, no lo normal. */}
                    <button
                        type="button"
                        onClick={() => setConCamara(true)}
                        className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                    >
                        <CameraIcon className="size-4" aria-hidden="true" />
                        {archivo ? 'Sacar otra' : 'Sacar foto'}
                    </button>

                    <button
                        type="button"
                        onClick={() => selector.current?.click()}
                        className="inline-flex items-center gap-1.5 rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                    >
                        <ImageIcon className="size-4" aria-hidden="true" />
                        Elegir archivo
                    </button>

                    {archivo ? (
                        <button
                            type="button"
                            onClick={() => {
                                alElegir(null);
                                // Sin vaciarlo, volver a elegir EL MISMO
                                // archivo no dispara el evento.
                                if (selector.current) {
                                    selector.current.value = '';
                                }
                            }}
                            className="apoyo text-fog transition-colors hover:text-danger"
                        >
                            Quitar
                        </button>
                    ) : null}
                </div>

                {error ? (
                    <p className="apoyo mt-1 text-danger">{error}</p>
                ) : (
                    <p className="apoyo mt-1 text-fog">
                        Opcional. Solo se ve dentro del panel. JPG, PNG o WEBP, hasta 2 MB.
                    </p>
                )}

                <CamaraFoto
                    abierta={conCamara}
                    alCerrar={() => setConCamara(false)}
                    alSacar={alElegir}
                    nombre={nombre}
                />
            </div>
        </div>
    );
}

const hoy = new Date().toISOString().slice(0, 10);

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

/** Lo que trae el celular antes de escribir nada: el prefijo de un móvil chileno. */
const PREFIJO = '+56 9 ';

/** Un teléfono que solo tiene el prefijo es un teléfono sin escribir. */
const soloPrefijo = (telefono) => telefono.replace(/\D/g, '') === '569';

/**
 * El RUT con puntos y guion mientras se escribe: 123456789 → 12.345.678-9.
 *
 * El último carácter es siempre el dígito verificador, y la K solo vale ahí.
 */
function formatearRut(texto) {
    const limpio = texto.toUpperCase().replace(/[^0-9K]/g, '').slice(0, 9);

    if (limpio.length < 2) {
        return limpio;
    }

    const cuerpo = limpio.slice(0, -1).replace(/K/g, '');

    return `${cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.')}-${limpio.slice(-1)}`;
}

/**
 * Si el RUT está bien escrito: el dígito verificador se calcula, no se cree.
 *
 * Se comprueba EN EL MESÓN y no al guardar: un dígito mal tecleado que se
 * descubre después del formulario entero obliga a revisar el carnet con la
 * persona ya de espaldas. Un pasaporte no lleva verificador y no pasa por aquí.
 */
function rutValido(texto) {
    const limpio = String(texto).toUpperCase().replace(/[^0-9K]/g, '');

    if (limpio.length < 8 || limpio.length > 9) {
        return false;
    }

    const cuerpo = limpio.slice(0, -1);
    const dv = limpio.slice(-1);
    let suma = 0;
    let factor = 2;

    for (let i = cuerpo.length - 1; i >= 0; i -= 1) {
        suma += Number(cuerpo[i]) * factor;
        factor = factor > 6 ? 2 : factor + 1;
    }

    const resto = 11 - (suma % 11);

    return dv === (resto === 11 ? '0' : resto === 10 ? 'K' : String(resto));
}

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
        valor: c.id,
        etiqueta: c.nombre,
        grupo: GRUPOS_DE_CONVENIO[c.tipo] ?? 'Otros',
    }));
}

/** Cuántos años tiene quien nació ese día. null si no se sabe. */
function edadDe(fecha) {
    if (! fecha) {
        return null;
    }

    const nacimiento = new Date(`${fecha}T00:00:00`);

    if (Number.isNaN(nacimiento.getTime())) {
        return null;
    }

    const hoyMismo = new Date();
    let anios = hoyMismo.getFullYear() - nacimiento.getFullYear();
    const mes = hoyMismo.getMonth() - nacimiento.getMonth();

    if (mes < 0 || (mes === 0 && hoyMismo.getDate() < nacimiento.getDate())) {
        anios -= 1;
    }

    return anios;
}

/** Interruptor chico, para lo que se enciende y se apaga en una línea. */
function Permiso({ encendido, alCambiar, etiqueta, ayuda }) {
    return (
        <button
            type="button"
            role="switch"
            aria-checked={encendido}
            onClick={() => alCambiar(! encendido)}
            className={`flex w-full items-start gap-2.5 rounded-control border px-3 py-2 text-left transition-colors ${
                encendido ? 'border-volt bg-volt/10' : 'border-line hover:border-line-strong'
            }`}
        >
            <span
                className={`mt-0.5 inline-block h-4 w-7 shrink-0 rounded-full transition-colors ${
                    encendido ? 'bg-volt' : 'bg-surface-2 ring-1 ring-line-strong'
                }`}
                aria-hidden="true"
            >
                <span
                    className={`relative top-0.5 block size-3 rounded-full bg-chalk transition-all ${
                        encendido ? 'left-[14px]' : 'left-0.5'
                    }`}
                />
            </span>
            <span className="min-w-0">
                <span className="block text-sm text-chalk">{etiqueta}</span>
                <span className="apoyo block text-fog">{ayuda}</span>
            </span>
        </button>
    );
}

/** Botones grandes para elegir de una lista corta: un toque, sin desplegar. */
function Botones({ opciones, valor, alElegir, nombre, columnas = 'sm:grid-cols-2' }) {
    return (
        <div role="radiogroup" aria-label={nombre} className={`grid gap-2 ${columnas}`}>
            {opciones.map((o) => {
                const elegido = String(valor) === String(o.valor);

                return (
                    <button
                        key={o.valor}
                        type="button"
                        role="radio"
                        aria-checked={elegido}
                        onClick={() => alElegir(o.valor)}
                        className={`rounded-control border px-3 py-2 text-left text-sm transition-colors ${
                            elegido
                                ? 'border-volt bg-volt/10 text-chalk'
                                : 'border-line text-fog hover:border-line-strong hover:text-chalk'
                        }`}
                    >
                        <span className="block font-medium">{o.etiqueta}</span>
                        {o.pie ? <span className="apoyo block text-fog">{o.pie}</span> : null}
                    </button>
                );
            })}
        </div>
    );
}

/** Campos que viven en la parte escondida: si alguno trae error, se abre sola. */
const CAMPOS_DE_MAS = [
    'apellido_materno',
    'fecha_nacimiento',
    'direccion',
    'contacto_emergencia',
    'telefono_emergencia',
    'observaciones',
    'foto_perfil',
    'contrato_firmado_en',
];

const CAMPOS_DEL_PLAN_DE_MAS = ['descuento_manual', 'id_motivo_descuento', 'observaciones_inscripcion', 'fecha_pago', 'referencia_pago'];

const FLUJOS = [
    ['completo', 'Plan y pago'],
    ['con_membresia', 'Plan sin cobrar'],
    ['solo_cliente', 'Solo la ficha'],
];

/** Una isla: un bloque con título y sus campos en dos columnas. */
function Isla({ titulo, accion, children, className = '' }) {
    return (
        <section className={`rounded-panel border border-line bg-surface p-4 ${className}`}>
            <div className="mb-3 flex items-center justify-between gap-3">
                <h2 className="text-sm font-semibold text-chalk">{titulo}</h2>
                {accion}
            </div>
            <div className="grid gap-3 sm:grid-cols-2">{children}</div>
        </section>
    );
}

/** Un interruptor para mostrar lo que no hace falta llenar siempre. */
function Interruptor({ encendido, alCambiar, children }) {
    return (
        <button
            type="button"
            role="switch"
            aria-checked={encendido}
            onClick={() => alCambiar(!encendido)}
            className="inline-flex items-center gap-2 text-sm text-fog transition-colors hover:text-chalk"
        >
            <span
                className={`relative inline-block h-5 w-9 rounded-full transition-colors ${encendido ? 'bg-volt' : 'bg-surface-2 ring-1 ring-line-strong'}`}
                aria-hidden="true"
            >
                <span
                    className={`absolute top-0.5 size-4 rounded-full bg-chalk transition-all ${encendido ? 'left-[18px]' : 'left-0.5'}`}
                />
            </span>
            {children}
        </button>
    );
}

/** Una casilla con su explicación debajo. */
function Casilla({ marcada, alCambiar, etiqueta, ayuda }) {
    return (
        <label className="flex items-start gap-2 text-sm text-chalk sm:col-span-2">
            <input
                type="checkbox"
                checked={marcada}
                onChange={(e) => alCambiar(e.target.checked)}
                className="mt-0.5 size-4 rounded-[4px] border-line-strong"
            />
            <span>
                {etiqueta}
                {ayuda ? <span className="apoyo block text-fog">{ayuda}</span> : null}
            </span>
        </label>
    );
}

/**
 * Alta de socio.
 *
 * LO MÍNIMO A LA VISTA. Para inscribir a alguien en el mesón basta su RUT, su
 * nombre, su celular, el plan y cómo pagó. Todo lo demás (dirección, contacto
 * de emergencia, contrato, foto) queda detrás de «Completar la ficha», para
 * quien quiera y tenga tiempo.
 *
 * El plan y el pago van por defecto: es lo que se hace casi siempre. Quien solo
 * quiere la ficha lo elige arriba de esa isla (el mismo `flujo_cliente` que
 * entiende el servidor: completo / con_membresia / solo_cliente).
 */
export default function Crear({ membresias, convenios, motivos, metodosPago, formToken, preciosDeConvenio = {} }) {
    /*
     * EL PRECIO QUE PAGA ESTE CONVENIO por este plan.
     *
     * Un club deportivo negocia el suyo —10.000, 15.000, 20.000 la
     * mensualidad—, y eso no cabe en el «precio con convenio» del plan, que es
     * uno solo para todos. Si el convenio no tiene trato propio, manda ese
     * precio general; y sin convenio, el normal. El servidor aplica esta misma
     * regla: aquí solo se enseña.
     */
    const precioCon = (plan, idConvenio) => {
        if (! plan) {
            return 0;
        }

        if (! idConvenio) {
            return plan.precio;
        }

        const propio = preciosDeConvenio?.[idConvenio]?.[plan.id];

        return propio ?? (plan.precio_convenio || plan.precio);
    };
    // El efectivo primero, si existe: es lo que más se usa en el mesón.
    const metodoPorDefecto =
        metodosPago.find((m) => /efectivo/i.test(m.nombre))?.id ?? metodosPago[0]?.id ?? '';

    const { data, setData, post, processing, errors, transform } = useForm({
        form_submit_token: formToken,
        flujo_cliente: 'completo',

        tipo_documento: 'rut',
        run_pasaporte: '',
        nombres: '',
        apellido_paterno: '',
        apellido_materno: '',
        celular: PREFIJO,
        email: '',
        fecha_nacimiento: '',
        direccion: '',
        contacto_emergencia: '',
        telefono_emergencia: PREFIJO,
        observaciones: '',
        // null y no '': Inertia manda el formulario como multipart solo si
        // encuentra un File dentro, y una cadena vacía no lo es.
        foto_perfil: null,

        contrato_firmado_en: '',
        consentimiento_imagen: false,
        consentimiento_difusion: false,
        // Mandarle el contrato por correo apenas se guarde el alta.
        enviar_contrato: false,

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
        id_metodo_pago: metodoPorDefecto,
        fecha_pago: hoy,
        referencia_pago: '',
    });

    const [fichaCompleta, setFichaCompleta] = useState(false);
    const [masDelPlan, setMasDelPlan] = useState(false);
    // El socio que YA existe con ese RUT, si lo hay.
    const [repetido, setRepetido] = useState(null);

    /*
     * MENOR DE EDAD NO SE PREGUNTA: se sabe por la fecha de nacimiento.
     *
     * Era una casilla más que alguien tenía que acordarse de marcar, teniendo
     * la fecha escrita tres campos más arriba. Solo se pregunta cuando no hay
     * fecha, que es el único caso en que el sistema no puede saberlo.
     */
    const edad = edadDe(data.fecha_nacimiento);

    useEffect(() => {
        if (edad === null) {
            return;
        }

        setData('es_menor_edad', edad < 18);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [edad]);

    // Las dos partes del pago mixto. Viven fuera del formulario porque el
    // servidor las espera juntas en un solo campo (`detalle_pagos_mixto`), igual
    // que en «Nueva inscripción». Antes aquí se ofrecía «mixto» con un solo
    // monto y un solo medio, y la segunda mitad del pago no quedaba en ninguna parte.
    const otroMetodo = metodosPago.find((m) => m.id !== metodoPorDefecto)?.id ?? '';
    const [partes, setPartes] = useState([
        { id_metodo_pago: metodoPorDefecto, monto: '' },
        { id_metodo_pago: otroMetodo, monto: '' },
    ]);

    // Quien llega desde «Nueva inscripción» porque el socio no apareció trae
    // puesto lo que escribió en el buscador: con números es el RUT, con letras
    // el nombre. Se lee una vez y se borra, para que no reaparezca después.
    useEffect(() => {
        let traido = '';
        try {
            traido = sessionStorage.getItem('alta-desde-busqueda') ?? '';
            sessionStorage.removeItem('alta-desde-busqueda');
        } catch {
            return;
        }
        if (! traido) {
            return;
        }
        if (/\d/.test(traido)) {
            setData('run_pasaporte', traido);
        } else {
            const [nombres, ...apellidos] = traido.split(/\s+/);
            setData((d) => ({
                ...d,
                nombres,
                apellido_paterno: apellidos[0] ?? '',
                apellido_materno: apellidos.slice(1).join(' '),
            }));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    /*
     * ¿ESTA PERSONA YA ESTÁ REGISTRADA?
     *
     * Se pregunta mientras se escribe el RUT, no al guardar. El socio que
     * vuelve después de un año es el caso más común del mesón, y sin este aviso
     * se le crea una ficha nueva: su historial, sus pagos y su contrato quedan
     * repartidos en dos, y nadie se entera hasta que algo no cuadra.
     *
     * Si la consulta falla no pasa nada: el formulario sigue igual y el
     * servidor rechaza el RUT repetido de todos modos.
     */
    useEffect(() => {
        const rut = data.run_pasaporte.trim();

        if (data.tipo_documento !== 'rut' || ! rutValido(rut)) {
            setRepetido(null);

            return undefined;
        }

        const cancelar = new AbortController();
        const espera = setTimeout(async () => {
            try {
                const r = await fetch(`/panel/clientes/buscar?q=${encodeURIComponent(rut)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    signal: cancelar.signal,
                });
                const { socios = [] } = await r.json();
                const limpio = (t) => String(t ?? '').replace(/[^0-9kK]/g, '').toUpperCase();

                setRepetido(socios.find((s) => limpio(s.rut) === limpio(rut)) ?? null);
            } catch {
                // Sin conexión o respuesta rara: se sigue sin el aviso.
            }
        }, 400);

        return () => {
            clearTimeout(espera);
            cancelar.abort();
        };
    }, [data.run_pasaporte, data.tipo_documento]);

    // Un error en algo escondido no puede quedar escondido: se abre su parte.
    useEffect(() => {
        if (CAMPOS_DE_MAS.some((c) => errors[c]) || Object.keys(errors).some((c) => c.startsWith('apoderado') || c === 'consentimiento_apoderado')) {
            setFichaCompleta(true);
        }
        if (CAMPOS_DEL_PLAN_DE_MAS.some((c) => errors[c])) {
            setMasDelPlan(true);
        }
    }, [errors]);

    const conMembresia = data.flujo_cliente !== 'solo_cliente';
    const conPago = data.flujo_cliente === 'completo';
    const esRut = data.tipo_documento === 'rut';

    const membresia = membresias.find((m) => String(m.id) === String(data.id_membresia));
    const convenio = convenios.find((c) => String(c.id) === String(data.id_convenio));

    /*
     * EL PRECIO SE ENSEÑA POR PARTES, no como un total a secas.
     *
     * Con convenio, el total salía rebajado sin decir de cuánto se rebajó: la
     * mensualidad de $40.000 aparecía como $25.000 y parecía el precio normal.
     * Quien cobra tiene que poder decirle al socio «son 40, con tu convenio te
     * quedan en 25», y quien revisa después tiene que ver por qué se cobró eso.
     *
     * Esto es solo para MOSTRARLO: el precio que se guarda lo vuelve a resolver
     * el servidor con el catálogo, no con lo que llegue del navegador.
     */
    const precioNormal = membresia?.precio ?? 0;
    const rebajaConvenio = membresia
        ? Math.max(0, precioNormal - precioCon(membresia, data.id_convenio))
        : 0;
    const descuento = Math.max(0, Number(data.descuento_manual) || 0);
    const precioBase = precioNormal - rebajaConvenio;
    const precioFinal = Math.max(0, precioBase - descuento);
    const motivo = motivos.find((m) => String(m.id) === String(data.id_motivo_descuento));

    const enviar = (e) => {
        e.preventDefault();

        // El prefijo solo, sin número, es un teléfono que no se escribió.
        transform((d) => ({
            ...d,
            detalle_pagos_mixto: d.tipo_pago === 'mixto'
                ? JSON.stringify(partes.filter((p) => Number(p.monto) > 0))
                : '',
            celular: soloPrefijo(d.celular) ? '' : d.celular,
            telefono_emergencia: soloPrefijo(d.telefono_emergencia) ? '' : d.telefono_emergencia,
        }));

        post('/panel/clientes');
    };

    const texto = (campo, extra = {}) => ({
        nombre: campo,
        valor: data[campo],
        alCambiar: (v) => setData(campo, v),
        error: errors[campo],
        ...extra,
    });

    return (
        <>
            <Head title="Nuevo socio" />

            <header className="mb-4 flex flex-wrap items-center gap-3">
                <Link
                    href="/panel/clientes"
                    className="rounded-control p-1.5 text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                    aria-label="Volver a clientes"
                >
                    <ArrowLeftIcon className="size-4" aria-hidden="true" />
                </Link>
                <h1 className="text-lg font-semibold text-chalk">Nuevo socio</h1>
            </header>

            <form onSubmit={enviar} className="grid items-start gap-4 lg:grid-cols-3">
                <div className="flex flex-col gap-4 lg:col-span-2">
                    <Isla titulo="Datos del socio">
                        <Campo etiqueta={esRut ? 'RUT' : 'Pasaporte'} nombre="run_pasaporte" error={errors.run_pasaporte}>
                            <div className="flex">
                                <select
                                    aria-label="Tipo de documento"
                                    value={data.tipo_documento}
                                    onChange={(e) => {
                                        setData((d) => ({ ...d, tipo_documento: e.target.value, run_pasaporte: '' }));
                                    }}
                                    className="rounded-l-control border border-r-0 border-line bg-surface-2 px-2 text-sm text-chalk focus:outline-none"
                                >
                                    <option value="rut">RUT</option>
                                    <option value="pasaporte">Pasaporte</option>
                                </select>
                                <input
                                    id="run_pasaporte"
                                    name="run_pasaporte"
                                    value={data.run_pasaporte}
                                    onChange={(e) =>
                                        setData(
                                            'run_pasaporte',
                                            esRut ? formatearRut(e.target.value) : e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, ''),
                                        )
                                    }
                                    placeholder={esRut ? '12.345.678-9' : 'N.º de pasaporte'}
                                    inputMode={esRut ? 'numeric' : 'text'}
                                    autoFocus
                                    aria-invalid={errors.run_pasaporte ? 'true' : undefined}
                                    className={`w-full rounded-r-control border bg-surface px-2.5 py-1.5 text-sm text-chalk placeholder:text-fog focus:outline-none ${
                                        errors.run_pasaporte ? 'border-danger' : 'border-line focus:border-line-strong'
                                    }`}
                                />
                            </div>

                            {/* EL AVISO VA AQUÍ, pegado al campo y mientras se
                                escribe: a los tres campos siguientes ya es tarde. */}
                            {repetido ? (
                                <div className="mt-1 rounded-control border border-warn/40 bg-warn/5 px-3 py-2">
                                    <p className="text-sm text-warn">
                                        {repetido.nombre} ya está registrado con ese RUT.
                                    </p>
                                    <p className="apoyo text-fog">
                                        {repetido.plan
                                            ? `${repetido.plan}, vence el ${repetido.vence}.`
                                            : 'Sin plan vigente.'}{' '}
                                        {repetido.debe > 0 ? `Debe ${pesos.format(repetido.debe)}.` : ''}
                                    </p>
                                    <Link
                                        href={`/panel/clientes/${repetido.uuid}`}
                                        className="apoyo mt-1 inline-block text-chalk underline underline-offset-4"
                                    >
                                        Abrir su ficha y renovarle ahí
                                    </Link>
                                </div>
                            ) : esRut && data.run_pasaporte.trim().length >= 9 && ! rutValido(data.run_pasaporte) ? (
                                <p className="apoyo mt-1 text-warn">
                                    Ese RUT no calza con su dígito verificador. Revísalo en el carnet.
                                </p>
                            ) : null}
                        </Campo>

                        <Campo etiqueta="Nombres" nombre="nombres" error={errors.nombres} requerido>
                            <Texto {...texto('nombres')} />
                        </Campo>

                        <Campo etiqueta="Apellido paterno" nombre="apellido_paterno" error={errors.apellido_paterno} requerido>
                            <Texto {...texto('apellido_paterno')} />
                        </Campo>

                        {fichaCompleta ? (
                            <Campo etiqueta="Apellido materno" nombre="apellido_materno" error={errors.apellido_materno}>
                                <Texto {...texto('apellido_materno')} />
                            </Campo>
                        ) : null}

                        <Campo
                            etiqueta="Celular"
                            nombre="celular"
                            error={errors.celular}
                            requerido
                            ayuda="Si es extranjero, borra el +56 9 y pon su código"
                        >
                            <Texto {...texto('celular', { tipo: 'tel', inputMode: 'tel' })} />
                        </Campo>

                        <Campo etiqueta="Correo" nombre="email" error={errors.email} ayuda="Opcional">
                            <Texto {...texto('email', { tipo: 'email' })} />
                        </Campo>

                        <div className="border-t border-line pt-3 sm:col-span-2">
                            <Interruptor encendido={fichaCompleta} alCambiar={setFichaCompleta}>
                                Completar la ficha (emergencia, contrato, foto…)
                            </Interruptor>
                        </div>

                        {fichaCompleta ? (
                            <>
                                <Campo etiqueta="Fecha de nacimiento" nombre="fecha_nacimiento" error={errors.fecha_nacimiento}>
                                    <Texto {...texto('fecha_nacimiento', { tipo: 'date' })} />
                                </Campo>

                                <Campo etiqueta="Dirección" nombre="direccion" error={errors.direccion}>
                                    <Texto {...texto('direccion')} />
                                </Campo>

                                <Campo etiqueta="En emergencia avisar a" nombre="contacto_emergencia" error={errors.contacto_emergencia}>
                                    <Texto {...texto('contacto_emergencia', { placeholder: 'Nombre' })} />
                                </Campo>

                                <Campo etiqueta="Su teléfono" nombre="telefono_emergencia" error={errors.telefono_emergencia}>
                                    <Texto {...texto('telefono_emergencia', { tipo: 'tel', inputMode: 'tel' })} />
                                </Campo>

                                <div className="sm:col-span-2">
                                    <Campo etiqueta="Observaciones" nombre="observaciones" error={errors.observaciones}>
                                        <Area {...texto('observaciones')} filas={2} />
                                    </Campo>
                                </div>

                                <div className="sm:col-span-2">
                                    <Campo etiqueta="Foto" nombre="foto_perfil" error={errors.foto_perfil}>
                                        <CampoFoto
                                            archivo={data.foto_perfil}
                                            nombre={`${data.nombres} ${data.apellido_paterno}`}
                                            error={errors.foto_perfil}
                                            alElegir={(f) => setData('foto_perfil', f)}
                                        />
                                    </Campo>
                                </div>

                                {/*
                                  * EL CONTRATO, UNA PREGUNTA CON TRES RESPUESTAS.
                                  *
                                  * Antes eran una fecha y una casilla sueltas, y había
                                  * que deducir que dejar la fecha vacía y no marcar la
                                  * casilla significaba «no firmó». Ahora se elige qué
                                  * pasó con el contrato y aparece solo lo que haga falta.
                                  */}
                                <div className="sm:col-span-2">
                                    <Campo etiqueta="Contrato" nombre="contrato_firmado_en" error={errors.contrato_firmado_en}>
                                        <Botones
                                            nombre="Qué pasa con el contrato"
                                            columnas="sm:grid-cols-3"
                                            valor={data.contrato_firmado_en ? 'papel' : data.enviar_contrato ? 'correo' : 'despues'}
                                            alElegir={(v) =>
                                                setData((d) => ({
                                                    ...d,
                                                    contrato_firmado_en: v === 'papel' ? hoy : '',
                                                    enviar_contrato: v === 'correo',
                                                }))
                                            }
                                            opciones={[
                                                { valor: 'papel', etiqueta: 'Lo firmó en papel', pie: 'se anota la fecha' },
                                                { valor: 'correo', etiqueta: 'Mandárselo por correo', pie: 'lo firma en su celular' },
                                                { valor: 'despues', etiqueta: 'Todavía no', pie: 'se manda desde su ficha' },
                                            ]}
                                        />

                                        {data.contrato_firmado_en ? (
                                            <div className="mt-2 flex items-center gap-2">
                                                <span className="apoyo shrink-0 text-fog">Lo firmó el</span>
                                                <input
                                                    id="contrato_firmado_en"
                                                    name="contrato_firmado_en"
                                                    type="date"
                                                    max={hoy}
                                                    value={data.contrato_firmado_en}
                                                    onChange={(e) => setData('contrato_firmado_en', e.target.value)}
                                                    className="rounded-control border border-line bg-surface px-2.5 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                                                />
                                            </div>
                                        ) : null}

                                        {data.enviar_contrato ? (
                                            <p className="apoyo mt-2 text-fog">
                                                {data.es_menor_edad
                                                    ? 'Le llega al correo del apoderado, que es quien firma.'
                                                    : data.email
                                                      ? `Le llega a ${data.email} apenas se guarde el alta.`
                                                      : 'Falta su correo: sin él no hay a dónde mandarlo.'}
                                            </p>
                                        ) : null}
                                    </Campo>
                                </div>

                                {/* Dos permisos y no uno: «la ve el mesón» y «sale en
                                    Instagram» son finalidades distintas, y firmar para
                                    la primera no autoriza la segunda. En una línea
                                    cada uno, no como dos casillas más de una lista. */}
                                <div className="sm:col-span-2">
                                    <Campo etiqueta="Autoriza su imagen" nombre="consentimiento_imagen">
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            <Permiso
                                                encendido={data.consentimiento_imagen}
                                                alCambiar={(v) => setData('consentimiento_imagen', v)}
                                                etiqueta="Foto en su ficha"
                                                ayuda="La ve solo el personal, dentro del panel."
                                            />
                                            <Permiso
                                                encendido={data.consentimiento_difusion}
                                                alCambiar={(v) => setData('consentimiento_difusion', v)}
                                                etiqueta="En redes sociales"
                                                ayuda="Este sistema no la usa. Queda anotado para quien publique."
                                            />
                                        </div>
                                    </Campo>
                                </div>

                                {/* La edad sale de la fecha de nacimiento. La casilla
                                    solo aparece cuando no hay fecha, que es cuando el
                                    sistema no tiene cómo saberlo. */}
                                {edad === null ? (
                                    <Casilla
                                        marcada={data.es_menor_edad}
                                        alCambiar={(v) => setData('es_menor_edad', v)}
                                        etiqueta="Es menor de edad (requiere apoderado)"
                                        ayuda="Se marca solo si escribes su fecha de nacimiento."
                                    />
                                ) : data.es_menor_edad ? (
                                    <p className="apoyo text-warn sm:col-span-2">
                                        Tiene {edad} años: la inscripción la autoriza su apoderado.
                                    </p>
                                ) : null}
                            </>
                        ) : null}
                    </Isla>

                    {fichaCompleta && data.es_menor_edad ? (
                        <Isla titulo="Apoderado">
                            <Campo etiqueta="Nombre del apoderado" nombre="apoderado_nombre" error={errors.apoderado_nombre} requerido>
                                <Texto {...texto('apoderado_nombre')} />
                            </Campo>
                            <Campo etiqueta="RUT del apoderado" nombre="apoderado_rut" error={errors.apoderado_rut} requerido>
                                <Texto
                                    {...texto('apoderado_rut', { placeholder: '12.345.678-9', inputMode: 'numeric' })}
                                    alCambiar={(v) => setData('apoderado_rut', formatearRut(v))}
                                />
                            </Campo>
                            <Campo etiqueta="Correo del apoderado" nombre="apoderado_email" error={errors.apoderado_email} requerido>
                                <Texto {...texto('apoderado_email', { tipo: 'email' })} />
                            </Campo>
                            <Campo etiqueta="Teléfono del apoderado" nombre="apoderado_telefono" error={errors.apoderado_telefono} requerido>
                                <Texto {...texto('apoderado_telefono', { tipo: 'tel', placeholder: PREFIJO.trim() })} />
                            </Campo>
                            <Campo etiqueta="Parentesco" nombre="apoderado_parentesco" error={errors.apoderado_parentesco} requerido>
                                <Texto {...texto('apoderado_parentesco')} />
                            </Campo>
                            <div className="sm:col-span-2">
                                <Casilla
                                    marcada={data.consentimiento_apoderado}
                                    alCambiar={(v) => setData('consentimiento_apoderado', v)}
                                    etiqueta="Confirmo que el apoderado autoriza la inscripción"
                                />
                                {errors.consentimiento_apoderado ? (
                                    <p className="apoyo mt-1 text-danger" role="alert">
                                        {errors.consentimiento_apoderado}
                                    </p>
                                ) : null}
                            </div>
                        </Isla>
                    ) : null}

                    <section className="rounded-panel border border-line bg-surface p-4">
                        <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                            <h2 className="text-sm font-semibold text-chalk">Plan y pago</h2>

                            <div role="radiogroup" aria-label="Qué se registra ahora" className="inline-flex rounded-control border border-line p-0.5">
                                {FLUJOS.map(([valor, etiqueta]) => (
                                    <button
                                        key={valor}
                                        type="button"
                                        role="radio"
                                        aria-checked={data.flujo_cliente === valor}
                                        onClick={() => setData('flujo_cliente', valor)}
                                        className={`rounded-[6px] px-2.5 py-1 text-xs transition-colors ${
                                            data.flujo_cliente === valor ? 'bg-surface-2 font-medium text-chalk' : 'text-fog hover:text-chalk'
                                        }`}
                                    >
                                        {etiqueta}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {conMembresia ? (
                            <div className="grid gap-3 sm:grid-cols-2">
                                {/* UN TOQUE Y NO TRES. Son cuatro o cinco planes: en
                                    un desplegable hay que abrirlo, buscar y elegir, y
                                    además el precio queda escondido hasta abrirlo. */}
                                <div className="sm:col-span-2">
                                    <Campo etiqueta="Plan" nombre="id_membresia" error={errors.id_membresia} requerido>
                                        <Botones
                                            nombre="Plan"
                                            valor={data.id_membresia}
                                            alElegir={(v) => setData('id_membresia', v)}
                                            columnas="sm:grid-cols-3"
                                            /* El precio normal SIEMPRE, y la rebaja al
                                               lado: con solo el de convenio, nadie
                                               podía ver que había una rebaja. */
                                            opciones={membresias.map((m) => {
                                                const conConvenio = precioCon(m, data.id_convenio);

                                                return {
                                                    valor: m.id,
                                                    etiqueta: m.nombre,
                                                    pie:
                                                        conConvenio < m.precio ? (
                                                            <>
                                                                <span className="line-through">{pesos.format(m.precio)}</span>{' '}
                                                                <span className="text-chalk">{pesos.format(conConvenio)}</span>
                                                            </>
                                                        ) : (
                                                            pesos.format(m.precio)
                                                        ),
                                                };
                                            })}
                                        />
                                    </Campo>
                                </div>

                                <Campo etiqueta="Convenio" nombre="id_convenio" error={errors.id_convenio}>
                                    <Seleccion
                                        {...texto('id_convenio')}
                                        vacio="Sin convenio"
                                        opciones={opcionesDeConvenio(convenios)}
                                    />
                                </Campo>

                                <Campo etiqueta="Empieza el" nombre="fecha_inicio" error={errors.fecha_inicio} requerido>
                                    <Texto {...texto('fecha_inicio', { tipo: 'date', min: hoy })} />
                                </Campo>

                                {conPago ? (
                                    <div className="sm:col-span-2">
                                        <Campo etiqueta="Paga" nombre="tipo_pago" error={errors.tipo_pago} requerido>
                                            <Botones
                                                nombre="Cómo paga"
                                                valor={data.tipo_pago}
                                                alElegir={(v) => setData('tipo_pago', v)}
                                                columnas="grid-cols-2 sm:grid-cols-4"
                                                opciones={[
                                                    { valor: 'completo', etiqueta: 'Todo' },
                                                    { valor: 'parcial', etiqueta: 'Una parte', pie: 'abono' },
                                                    { valor: 'mixto', etiqueta: 'Dos medios', pie: 'efectivo y tarjeta' },
                                                    { valor: 'pendiente', etiqueta: 'Nada todavía', pie: 'queda debiendo' },
                                                ]}
                                            />
                                        </Campo>
                                    </div>
                                ) : null}

                                {/* «Todo» cobra el total y «nada» no cobra: en esos
                                    dos el monto lo pone el servidor. */}
                                {conPago && data.tipo_pago === 'parcial' ? (
                                    <Campo
                                        etiqueta="Monto abonado"
                                        nombre="monto_abonado"
                                        error={errors.monto_abonado}
                                        requerido
                                        ayuda={`De un total de ${pesos.format(precioFinal)}`}
                                    >
                                        <Texto {...texto('monto_abonado', { tipo: 'number', min: '0', inputMode: 'numeric' })} />
                                    </Campo>
                                ) : null}

                                {/* MIXTO: dos medios, cada uno con su monto. Al escribir el
                                    primero, el segundo se rellena con lo que falta: casi
                                    siempre el reparto es «esto en efectivo y el resto con
                                    tarjeta», y así no hay que restar de cabeza. */}
                                {conPago && data.tipo_pago === 'mixto' ? (
                                    <div className="flex flex-col gap-2 sm:col-span-2">
                                        {partes.map((parte, i) => (
                                            <div key={i} className="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-3">
                                                <Seleccion
                                                    nombre={`parte_metodo_${i}`}
                                                    valor={parte.id_metodo_pago}
                                                    vacio={null}
                                                    alCambiar={(v) => setPartes((ps) => ps.map((p, j) => (j === i ? { ...p, id_metodo_pago: v } : p)))}
                                                    opciones={metodosPago.map((m) => ({ valor: m.id, etiqueta: m.nombre }))}
                                                />
                                                <Texto
                                                    nombre={`parte_monto_${i}`}
                                                    tipo="number"
                                                    min="0"
                                                    inputMode="numeric"
                                                    placeholder={i === 0 ? 'Monto' : 'El resto'}
                                                    valor={parte.monto}
                                                    alCambiar={(v) => setPartes((ps) => ps.map((p, j) => {
                                                        if (j === i) {
                                                            return { ...p, monto: v };
                                                        }
                                                        // El resto solo se propone mientras el segundo esté vacío o
                                                        // siga siendo el resto de antes: lo que se escribe a mano manda.
                                                        const restoDeAntes = Math.max(0, precioFinal - (Number(ps[0].monto) || 0));
                                                        const esElResto = p.monto === '' || Number(p.monto) === restoDeAntes;

                                                        return i === 0 && j === 1 && esElResto
                                                            ? { ...p, monto: String(Math.max(0, precioFinal - (Number(v) || 0)) || '') }
                                                            : p;
                                                    }))}
                                                />
                                            </div>
                                        ))}
                                        <p className={`apoyo ${errors.detalle_pagos_mixto ? 'text-danger' : 'text-fog'}`}>
                                            {errors.detalle_pagos_mixto
                                                ?? `Suman ${pesos.format(partes.reduce((t, p) => t + (Number(p.monto) || 0), 0))} de ${pesos.format(precioFinal)}.`}
                                        </p>
                                    </div>
                                ) : null}

                                {conPago && data.tipo_pago !== 'pendiente' && data.tipo_pago !== 'mixto' ? (
                                    <div className="sm:col-span-2">
                                        <Campo etiqueta="Con qué paga" nombre="id_metodo_pago" error={errors.id_metodo_pago} requerido>
                                            <Botones
                                                nombre="Medio de pago"
                                                valor={data.id_metodo_pago}
                                                alElegir={(v) => setData('id_metodo_pago', v)}
                                                columnas="sm:grid-cols-3"
                                                opciones={metodosPago.map((m) => ({ valor: m.id, etiqueta: m.nombre }))}
                                            />
                                        </Campo>
                                    </div>
                                ) : null}

                                <div className="border-t border-line pt-3 sm:col-span-2">
                                    <Interruptor encendido={masDelPlan} alCambiar={setMasDelPlan}>
                                        Descuento, fecha del pago y comprobante
                                    </Interruptor>
                                </div>

                                {masDelPlan ? (
                                    <>
                                        <Campo
                                            etiqueta="Descuento"
                                            nombre="descuento_manual"
                                            error={errors.descuento_manual}
                                            ayuda={precioBase > 0 ? `Precio del plan: ${pesos.format(precioBase)}` : undefined}
                                        >
                                            <Texto {...texto('descuento_manual', { tipo: 'number', min: '0', inputMode: 'numeric' })} />
                                        </Campo>

                                        <Campo etiqueta="Motivo del descuento" nombre="id_motivo_descuento" error={errors.id_motivo_descuento}>
                                            <Seleccion
                                                {...texto('id_motivo_descuento')}
                                                opciones={motivos.map((m) => ({ valor: m.id, etiqueta: m.nombre }))}
                                            />
                                        </Campo>

                                        {conPago ? (
                                            <>
                                                <Campo etiqueta="Fecha del pago" nombre="fecha_pago" error={errors.fecha_pago} requerido>
                                                    <Texto {...texto('fecha_pago', { tipo: 'date', max: hoy })} />
                                                </Campo>

                                                <Campo
                                                    etiqueta="Comprobante"
                                                    nombre="referencia_pago"
                                                    error={errors.referencia_pago}
                                                    ayuda="N.º de transferencia o boleta"
                                                >
                                                    <Texto {...texto('referencia_pago')} />
                                                </Campo>
                                            </>
                                        ) : null}

                                        <div className="sm:col-span-2">
                                            <Campo
                                                etiqueta="Nota de la inscripción"
                                                nombre="observaciones_inscripcion"
                                                error={errors.observaciones_inscripcion}
                                            >
                                                <Area {...texto('observaciones_inscripcion')} filas={2} />
                                            </Campo>
                                        </div>
                                    </>
                                ) : null}
                            </div>
                        ) : (
                            <p className="apoyo text-fog">
                                Queda la ficha sin plan. La membresía se le agrega después desde su ficha.
                            </p>
                        )}
                    </section>
                </div>

                {/* Lo que se va a guardar, siempre a la vista junto al botón:
                    en pantalla ancha no hay que bajar para registrar. */}
                <aside className="flex flex-col gap-3 rounded-panel border border-line bg-surface p-4 lg:sticky lg:top-4">
                    <div className="flex items-center gap-3">
                        <Retrato nombre={`${data.nombres} ${data.apellido_paterno}`} tamano="md" />
                        <div className="min-w-0">
                            <p className="truncate text-sm font-medium text-chalk">
                                {`${data.nombres} ${data.apellido_paterno}`.trim() || 'Socio nuevo'}
                            </p>
                            <p className="apoyo truncate text-fog">{data.run_pasaporte || 'Sin documento'}</p>
                        </div>
                    </div>

                    {conMembresia ? (
                        <dl className="flex flex-col gap-1.5 border-t border-line pt-3 text-sm">
                            <div className="flex justify-between gap-3">
                                <dt className="text-fog">Plan</dt>
                                <dd className="truncate text-right text-chalk">{membresia?.nombre ?? 'Sin elegir'}</dd>
                            </div>

                            {membresia ? (
                                <>
                                    <div className="flex justify-between gap-3">
                                        <dt className="text-fog">Precio</dt>
                                        <dd className="tabular-nums text-chalk">{pesos.format(precioNormal)}</dd>
                                    </div>

                                    {rebajaConvenio > 0 ? (
                                        <div className="flex justify-between gap-3">
                                            <dt className="truncate text-fog">Convenio {convenio?.nombre}</dt>
                                            <dd className="tabular-nums text-ok">− {pesos.format(rebajaConvenio)}</dd>
                                        </div>
                                    ) : null}

                                    {descuento > 0 ? (
                                        <div className="flex justify-between gap-3">
                                            <dt className="truncate text-fog">
                                                Descuento{motivo ? ` · ${motivo.nombre}` : ''}
                                            </dt>
                                            <dd className="tabular-nums text-ok">− {pesos.format(descuento)}</dd>
                                        </div>
                                    ) : null}

                                    <div className="mt-1 flex items-baseline justify-between gap-3 border-t border-line pt-2">
                                        <dt className="text-fog">{conPago ? 'Total a cobrar' : 'Queda debiendo'}</dt>
                                        <dd className="text-lg font-semibold tabular-nums text-chalk">{pesos.format(precioFinal)}</dd>
                                    </div>

                                    {/* Lo que se cobra HOY, que con un abono no es
                                        el total: quien cobra necesita esa cifra. */}
                                    {conPago && data.tipo_pago === 'parcial' && Number(data.monto_abonado) > 0 ? (
                                        <div className="flex justify-between gap-3">
                                            <dt className="text-fog">Abona hoy</dt>
                                            <dd className="tabular-nums text-chalk">
                                                {pesos.format(Number(data.monto_abonado))}
                                                <span className="apoyo block text-warn">
                                                    queda debiendo {pesos.format(Math.max(0, precioFinal - Number(data.monto_abonado)))}
                                                </span>
                                            </dd>
                                        </div>
                                    ) : null}

                                    {conPago && data.tipo_pago === 'pendiente' ? (
                                        <p className="apoyo text-warn">No paga nada ahora: queda debiendo todo.</p>
                                    ) : null}
                                </>
                            ) : null}
                        </dl>
                    ) : null}

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-control bg-volt px-4 py-2.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        {processing ? 'Guardando…' : 'Registrar socio'}
                    </button>

                    <Link
                        href="/panel/clientes"
                        className="text-center text-sm text-fog transition-colors hover:text-chalk"
                    >
                        Cancelar
                    </Link>
                </aside>
            </form>
        </>
    );
}
