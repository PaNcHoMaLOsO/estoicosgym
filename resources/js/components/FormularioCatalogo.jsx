import { useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { Area, Campo, Texto } from '@/components/Campo';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

/**
 * El formulario de un catalogo, dentro de un dialogo.
 *
 * Los cuatro catalogos —planes, convenios, metodos de pago y motivos— son la
 * misma pantalla con distintos campos: un nombre, una descripcion, un
 * interruptor de activo. Lo comun vive aqui y cada listado pasa `campos` con lo
 * suyo, en vez de cuatro dialogos casi iguales.
 *
 * Se abre encima del listado y no en su propia pagina porque son tres o cuatro
 * campos: irse a otra pantalla para escribir un nombre y volver es mas viaje
 * que trabajo.
 *
 * NO usa <Dialogo>: aquel confirma UNA accion con su propio boton y su propio
 * fetch, y esto es un formulario de Inertia con validacion por campo. Meter uno
 * dentro del otro dejaria dos botones de guardar.
 */
/**
 * Los campos de cada catalogo.
 *
 * Viven AQUI y no en cada pantalla porque un plan se edita desde dos sitios
 * —el listado y su propia ficha— y si cada uno declarara sus campos, el dia que
 * se añada uno se añadiria en uno solo y el otro lo dejaria en blanco al
 * guardar.
 */
export const CAMPOS_PLAN = [
    { nombre: 'nombre', etiqueta: 'Nombre', requerido: true, ejemplo: 'Mensual, Trimestral…' },
    {
        nombre: 'descripcion',
        etiqueta: 'Descripción',
        tipo: 'area',
        ayuda: 'Opcional. Qué incluye el plan.',
    },
    {
        nombre: 'duracion_meses',
        etiqueta: 'Dura (meses)',
        tipo: 'number',
        min: 0,
        requerido: true,
        ayuda: 'Pon los meses o los días, lo que corresponda. Si pones días, mandan los días.',
    },
    { nombre: 'duracion_dias', etiqueta: 'Dura (días)', tipo: 'number', min: 0, requerido: true },
    {
        nombre: 'dias_regalo',
        etiqueta: 'Días de regalo',
        tipo: 'number',
        min: 0,
        ayuda: 'Los que se suman al vencimiento por pagar todo junto. El anual con 5 dura un año y cinco días. Cero si no se regala nada.',
    },
    {
        nombre: 'max_pausas',
        etiqueta: 'Pausas permitidas',
        tipo: 'number',
        min: 0,
        requerido: true,
        ayuda: 'Cuántas veces puede congelar la membresía.',
    },
    {
        nombre: 'precio',
        etiqueta: 'Precio',
        tipo: 'number',
        min: 0,
        requerido: true,
        /* Un plan sin precio vigente no se puede vender: el alta de inscripcion
           lo rechaza. Cambiarlo NO pisa el anterior, abre un tramo nuevo. */
        ayuda: 'Al cambiarlo, lo que ya se cobró no se toca: queda como histórico.',
    },
    {
        nombre: 'precio_convenio',
        etiqueta: 'Precio con convenio',
        tipo: 'number',
        min: 0,
        ayuda: 'Opcional. Lo que paga quien viene por un convenio. Tiene que ser menor que el normal.',
    },
    { nombre: 'activo', etiqueta: 'Disponibilidad', tipo: 'si-no', textoCasilla: 'Se puede vender' },
    /* Aparte de venderse: la Semana o la Quincena se venden en el mesón a un
       precio que se arregla con cada uno, y puesto en la web ese precio pasa
       a ser el de todos. */
    {
        nombre: 'en_la_web',
        etiqueta: 'Página web',
        tipo: 'si-no',
        textoCasilla: 'Sale en la página de planes',
        ayuda: 'Apágalo en los precios que se arreglan con cada persona: se siguen vendiendo en el mesón, pero no se anuncian.',
    },
];

export const TIPOS_CONVENIO = [
    { valor: 'empresa', etiqueta: 'Empresa' },
    { valor: 'institucion_educativa', etiqueta: 'Institución educativa' },
    // Los clubes —fútbol, básquetbol— pagan distinto a una empresa y se
    // buscan aparte: mezclados con «organización» no se encontraban.
    { valor: 'club_deportivo', etiqueta: 'Club deportivo' },
    { valor: 'organizacion', etiqueta: 'Organización' },
    { valor: 'otro', etiqueta: 'Otro' },
];

export const CAMPOS_CONVENIO = [
    { nombre: 'nombre', etiqueta: 'Nombre', requerido: true, ejemplo: 'INACAP, Banco Santander…' },
    { nombre: 'tipo', etiqueta: 'Tipo', tipo: 'opciones', opciones: TIPOS_CONVENIO, requerido: true },
    { nombre: 'descripcion', etiqueta: 'Descripción', tipo: 'area' },
    {
        nombre: 'descuento_porcentaje',
        etiqueta: 'Descuento (%)',
        tipo: 'number',
        min: 0,
        max: 100,
        /*
         * AVISO IMPORTANTE. La rebaja que se aplica al inscribir NO sale de
         * aqui: sale del «precio con convenio» que tenga cargado cada plan. Un
         * plan sin ese precio no rebaja nada, se ponga aqui lo que se ponga.
         */
        ayuda: 'Informativo. La rebaja real sale del «precio con convenio» de cada plan.',
    },
    {
        nombre: 'descuento_monto',
        etiqueta: 'Descuento fijo',
        tipo: 'number',
        min: 0,
        ayuda: 'También informativo, para dejar por escrito lo acordado.',
    },
    { nombre: 'contacto_nombre', etiqueta: 'Persona de contacto' },
    { nombre: 'contacto_telefono', etiqueta: 'Teléfono' },
    { nombre: 'contacto_email', etiqueta: 'Correo', tipo: 'email' },
    {
        nombre: 'canje',
        etiqueta: 'Canje',
        tipo: 'si-no',
        textoCasilla: 'Entran sin pagar (por ejemplo, huéspedes de un hotel con tarjeta)',
        ayuda: 'Sus entradas se anotan en Mesón → Canje, con nombre y n.º de tarjeta.',
    },
    {
        nombre: 'mostrar_en_web',
        etiqueta: 'Página web',
        tipo: 'si-no',
        textoCasilla: 'Mostrarlo en la sección de convenios de la web',
    },
    {
        nombre: 'requisito_web',
        etiqueta: 'Quién accede',
        ejemplo: 'Estudiantes con credencial vigente',
        ayuda: 'Se lee debajo del logo en la web.',
    },
    {
        nombre: 'logo',
        etiqueta: 'Logo',
        tipo: 'imagen',
        actual: 'logo_url',
        quitar: 'quitar_logo',
        ayuda: 'PNG, JPG o WEBP, hasta 2 MB. Mejor con fondo blanco o transparente.',
    },
    {
        nombre: 'activo',
        etiqueta: 'Disponibilidad',
        tipo: 'si-no',
        textoCasilla: 'Se puede elegir al inscribir',
    },
];

/** Lo que hay que mandar para guardar un plan, a partir de su ficha o su fila. */
export function valoresDePlan(plan) {
    return {
        nombre: plan?.nombre ?? '',
        descripcion: plan?.descripcion ?? '',
        duracion_meses: plan?.duracion_meses ?? 1,
        duracion_dias: plan?.duracion_dias ?? 0,
        dias_regalo: plan?.dias_regalo ?? 0,
        max_pausas: plan?.max_pausas ?? 1,
        precio: plan?.precio ?? '',
        // El precio de convenio puede no existir, y 0 no es lo mismo que «no
        // tiene»: uno significa gratis con convenio y el otro, sin convenio.
        precio_convenio: plan?.precio_convenio ?? '',
        activo: plan?.uuid ? Boolean(plan.activo) : true,
        en_la_web: plan?.uuid ? Boolean(plan.en_la_web ?? true) : true,
    };
}

/** Lo mismo para un convenio. */
export function valoresDeConvenio(convenio) {
    return {
        nombre: convenio?.nombre ?? '',
        tipo: convenio?.tipo ?? 'empresa',
        descripcion: convenio?.descripcion ?? '',
        descuento_porcentaje: convenio?.descuento_porcentaje || '',
        descuento_monto: convenio?.descuento_monto || '',
        // El listado lo llama `contacto` y la ficha `contacto_nombre`.
        contacto_nombre: convenio?.contacto_nombre ?? convenio?.contacto ?? '',
        contacto_telefono: convenio?.contacto_telefono ?? '',
        contacto_email: convenio?.contacto_email ?? '',
        // La pagina publica. El logo solo viaja si se elige uno nuevo.
        canje: Boolean(convenio?.canje),
        mostrar_en_web: Boolean(convenio?.mostrar_en_web),
        requisito_web: convenio?.requisito_web ?? '',
        logo: null,
        quitar_logo: false,
        logo_url: convenio?.logo_url ?? null,
        activo: convenio?.uuid ? Boolean(convenio.activo) : true,
    };
}

/** Lo que hay que mandar para guardar un especialista. */
export function valoresDeEspecialista(especialista, tipo = 'especialista') {
    return {
        tipo: especialista?.tipo ?? tipo,
        nombre: especialista?.nombre ?? '',
        especialidad: especialista?.especialidad ?? '',
        descripcion: especialista?.descripcion ?? '',
        temas: especialista?.temas ?? [],
        modalidad: especialista?.modalidad ?? '',
        foto: null,
        quitar_foto: false,
        foto_url: especialista?.foto_url ?? null,
        whatsapp: especialista?.whatsapp ?? '',
        instagram: especialista?.instagram ?? '',
        activo: especialista?.uuid ? Boolean(especialista.activo) : true,
    };
}

/**
 * Una imagen del catálogo: el logo de un convenio, la foto de un especialista.
 *
 * Se ve la que hay, se puede cambiar por otra o quitar. El recuadro es blanco
 * a propósito: los logos de las instituciones están hechos para fondo blanco.
 */
function CampoImagen({ campo, data, setData }) {
    const archivo = data[campo.nombre];
    const [vista, setVista] = useState(null);

    // La vista previa se crea y se suelta: cada createObjectURL reserva memoria.
    useEffect(() => {
        if (! (archivo instanceof File)) {
            setVista(null);

            return undefined;
        }

        const url = URL.createObjectURL(archivo);
        setVista(url);

        return () => URL.revokeObjectURL(url);
    }, [archivo]);

    const actual = campo.actual ? data[campo.actual] : null;
    const quitando = campo.quitar ? Boolean(data[campo.quitar]) : false;
    const mostrar = vista ?? (quitando ? null : actual);

    return (
        <div className="flex items-center gap-3">
            <div className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-control border border-line bg-white p-1">
                {mostrar ? (
                    <img src={mostrar} alt="" className="max-h-full max-w-full object-contain" />
                ) : (
                    <span className="text-center text-[10px] leading-tight text-neutral-500">Sin imagen</span>
                )}
            </div>

            <div className="flex min-w-0 flex-col items-start gap-1">
                <input
                    id={campo.nombre}
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    onChange={(e) => {
                        setData(campo.nombre, e.target.files?.[0] ?? null);
                        if (campo.quitar) {
                            setData(campo.quitar, false);
                        }
                    }}
                    className="apoyo max-w-full text-fog file:mr-2 file:rounded-control file:border file:border-line file:bg-surface-2 file:px-2 file:py-1 file:text-chalk"
                />

                {campo.quitar && actual && ! (archivo instanceof File) ? (
                    <label className="apoyo flex items-center gap-1.5 text-fog">
                        <input
                            type="checkbox"
                            checked={quitando}
                            onChange={(e) => setData(campo.quitar, e.target.checked)}
                            className="size-3.5 accent-[var(--color-volt)]"
                        />
                        Quitar la imagen
                    </label>
                ) : null}
            </div>
        </div>
    );
}

/** Los campos que caben en media fila cuando la ventana va en dos columnas. */
const CORTOS = ['number', 'date', 'time', 'opciones', 'email', 'tel', 'password'];

export default function FormularioCatalogo({
    abierto,
    alCerrar,
    titulo,
    descripcion,
    accion,
    metodo = 'post',
    campos,
    valores,
}) {
    const { data, setData, post, put, processing, errors, clearErrors, transform } = useForm(valores);

    /*
     * Al abrirlo se rellena con lo que toque. Sin esto, editar una fila y
     * despues otra ensenaria los datos de la primera: el dialogo vive en el
     * listado y no se desmonta al cerrarse, asi que conserva su estado.
     */
    useEffect(() => {
        if (! abierto) {
            return;
        }

        clearErrors();
        Object.entries(valores).forEach(([clave, valor]) => setData(clave, valor));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [abierto, accion]);

    function enviar(e) {
        e.preventDefault();

        const opciones = { preserveScroll: true, onSuccess: () => alCerrar() };
        const conArchivo = Object.values(data).some((valor) => valor instanceof File);

        /*
         * Con un archivo, el formulario viaja como multipart, y eso PHP no lo
         * lee en un PUT. Se manda como POST diciendo que es un PUT: Laravel lo
         * entiende y la ruta es la misma.
         */
        if (metodo === 'put' && conArchivo) {
            transform((datos) => ({ ...datos, _method: 'put' }));
            post(accion, opciones);

            return;
        }

        transform((datos) => datos);
        (metodo === 'put' ? put : post)(accion, opciones);
    }

    const ancha = campos.length > 6;

    return (
        <Dialog open={abierto} onOpenChange={(v) => (! v && ! processing ? alCerrar() : null)}>
            {/* CON MUCHOS CAMPOS, MÁS ANCHA Y EN DOS COLUMNAS. El convenio tiene trece
                campos: en una ventana angosta eran una tira larguísima con «Guardar»
                al final del scroll. Lo corto (números, fechas, listas) va de a dos;
                lo que necesita ancho (nombres, textos, imágenes) ocupa la fila. */}
            <DialogContent className={ancha ? 'sm:max-w-2xl' : 'sm:max-w-md'}>
                <DialogHeader>
                    <DialogTitle>{titulo}</DialogTitle>
                    {descripcion ? <DialogDescription>{descripcion}</DialogDescription> : null}
                </DialogHeader>

                <form onSubmit={enviar} className={ancha ? 'grid gap-x-4 gap-y-3 sm:grid-cols-2' : 'space-y-3'}>
                    {campos.map((campo) => (
                        <div key={campo.nombre} className={ancha && ! CORTOS.includes(campo.tipo) ? 'sm:col-span-2' : ''}>
                        <Campo
                            etiqueta={campo.etiqueta}
                            nombre={campo.nombre}
                            error={errors[campo.nombre]}
                            requerido={campo.requerido}
                            ayuda={campo.ayuda}
                        >
                            {campo.tipo === 'area' ? (
                                <Area
                                    nombre={campo.nombre}
                                    valor={data[campo.nombre] ?? ''}
                                    alCambiar={(v) => setData(campo.nombre, v)}
                                    filas={2}
                                />
                            ) : campo.tipo === 'si-no' ? (
                                <label className="flex items-center gap-2 text-sm text-chalk">
                                    <input
                                        type="checkbox"
                                        checked={Boolean(data[campo.nombre])}
                                        onChange={(e) => setData(campo.nombre, e.target.checked)}
                                        className="size-4 accent-[var(--color-volt)]"
                                    />
                                    {campo.textoCasilla}
                                </label>
                            ) : campo.tipo === 'imagen' ? (
                                <CampoImagen campo={campo} data={data} setData={setData} />
                            ) : campo.tipo === 'opciones' ? (
                                <select
                                    id={campo.nombre}
                                    name={campo.nombre}
                                    value={data[campo.nombre] ?? ''}
                                    onChange={(e) => setData(campo.nombre, e.target.value)}
                                    className="w-full rounded-control border border-line bg-surface-2 px-2 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
                                >
                                    {campo.opciones.map((o) => (
                                        <option key={o.valor} value={o.valor}>
                                            {o.etiqueta}
                                        </option>
                                    ))}
                                </select>
                            ) : (
                                <Texto
                                    nombre={campo.nombre}
                                    tipo={campo.tipo ?? 'text'}
                                    min={campo.min}
                                    max={campo.max}
                                    valor={data[campo.nombre] ?? ''}
                                    alCambiar={(v) => setData(campo.nombre, v)}
                                    placeholder={campo.ejemplo}
                                    // «new-password» en las contraseñas de una cuenta ajena: sin
                                    // él, el navegador rellena la del administrador que tiene guardada.
                                    autoComplete={campo.autocompletar}
                                />
                            )}
                        </Campo>
                        </div>
                    ))}

                    {/* Pegados abajo: en una ventana que se desplaza, «Guardar» no se pierde. */}
                    <div className="sticky -bottom-4 -mx-4 -mb-4 flex items-center justify-end gap-3 border-t border-line bg-raise px-4 py-3 sm:col-span-2">
                        <button
                            type="button"
                            onClick={alCerrar}
                            disabled={processing}
                            className="rounded-control border border-line px-3 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2 disabled:opacity-50"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            {processing ? 'Guardando…' : 'Guardar'}
                        </button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
