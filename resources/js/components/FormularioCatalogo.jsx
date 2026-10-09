import { useForm } from '@inertiajs/react';
import { Fragment, useEffect, useState } from 'react';

import { Area, Campo, Texto } from '@/components/Campo';
import { Botones } from '@/components/Cobro';
import { PREFIJO, soloPrefijo } from '@/lib/socio';
import TextoQueCambia from '@/components/TextoQueCambia';
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
    { seccion: 'El plan', nombre: 'nombre', etiqueta: 'Nombre', requerido: true, ejemplo: 'Mensual, Trimestral…' },
    { seccion: 'El plan', nombre: 'descripcion', etiqueta: 'Descripción', tipo: 'area', max: 500, ayuda: 'Opcional. Qué incluye.' },

    /* DURA MESES O DÍAS, una de las dos. Eran dos casillas obligatorias con la
       regla «si pones días, mandan los días» escrita abajo: se elige cuál y se
       escribe solo esa. La otra se manda en 0. */
    {
        seccion: 'Duración',
        nombre: 'unidad',
        etiqueta: 'Se cuenta en',
        tipo: 'opciones',
        botones: true,
        columnas: 'grid-cols-2',
        opciones: [
            { valor: 'meses', etiqueta: 'Meses' },
            { valor: 'dias', etiqueta: 'Días' },
        ],
        alEnviar: (d) => (d.unidad === 'dias' ? { ...d, duracion_meses: 0 } : { ...d, duracion_dias: 0 }),
    },
    { seccion: 'Duración', nombre: 'duracion_meses', etiqueta: 'Meses', tipo: 'number', min: 1, requerido: true, mostrarSi: (d) => d.unidad !== 'dias' },
    { seccion: 'Duración', nombre: 'duracion_dias', etiqueta: 'Días', tipo: 'number', min: 1, requerido: true, mostrarSi: (d) => d.unidad === 'dias' },
    {
        seccion: 'Duración',
        nombre: 'dias_regalo',
        etiqueta: 'Días de regalo',
        tipo: 'number',
        min: 0,
        ayuda: 'Se suman al vencimiento. 0 si no hay.',
    },
    { seccion: 'Duración', nombre: 'max_pausas', etiqueta: 'Pausas permitidas', tipo: 'number', min: 0, requerido: true },

    {
        seccion: 'Precio',
        nombre: 'precio',
        etiqueta: 'Precio',
        tipo: 'dinero',
        requerido: true,
        /* Cambiarlo NO pisa el anterior: abre un tramo nuevo. */
        ayuda: 'Lo ya cobrado no cambia.',
    },
    { seccion: 'Precio', nombre: 'precio_convenio', etiqueta: 'Con convenio', tipo: 'dinero', ayuda: 'Opcional. Menor que el normal.' },

    { seccion: 'Dónde se ofrece', nombre: 'activo', etiqueta: 'Mesón', tipo: 'si-no', textoCasilla: 'Se puede vender' },
    /* Aparte de venderse: la Semana o la Quincena se venden en el mesón a un
       precio que se arregla con cada uno, y puesto en la web ese precio pasa
       a ser el de todos. */
    { seccion: 'Dónde se ofrece', nombre: 'en_la_web', etiqueta: 'Página web', tipo: 'si-no', textoCasilla: 'Sale en la página de planes' },
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
    { seccion: 'El convenio', nombre: 'nombre', etiqueta: 'Nombre', requerido: true, ejemplo: 'INACAP, Banco Santander…' },
    { seccion: 'El convenio', nombre: 'tipo', etiqueta: 'Tipo', tipo: 'opciones', botones: true, opciones: TIPOS_CONVENIO, requerido: true },
    { seccion: 'El convenio', nombre: 'descripcion', etiqueta: 'Descripción', tipo: 'area', max: 500 },

    /*
     * AVISO IMPORTANTE. La rebaja que se aplica al inscribir NO sale de aquí:
     * sale del «precio con convenio» de cada plan. Esto deja por escrito lo
     * acordado.
     */
    {
        seccion: 'Descuento acordado',
        nombre: 'descuento_porcentaje',
        etiqueta: 'Porcentaje',
        tipo: 'number',
        min: 0,
        max: 100,
        ayuda: 'Solo para dejarlo anotado: la rebaja real es el «precio con convenio» de cada plan.',
    },
    { seccion: 'Descuento acordado', nombre: 'descuento_monto', etiqueta: 'O un monto fijo', tipo: 'dinero' },

    { seccion: 'Contacto', nombre: 'contacto_nombre', etiqueta: 'Persona de contacto' },
    { seccion: 'Contacto', nombre: 'contacto_telefono', etiqueta: 'Teléfono', tipo: 'tel', ejemplo: '+56 9 1234 5678' },
    { seccion: 'Contacto', nombre: 'contacto_email', etiqueta: 'Correo', tipo: 'email' },

    {
        seccion: 'Canje',
        nombre: 'canje',
        etiqueta: 'Canje',
        tipo: 'si-no',
        textoCasilla: 'Entran sin pagar (por ejemplo, huéspedes de un hotel con tarjeta)',
        ayuda: 'Sus entradas se anotan en Mesón → Canje.',
    },

    { seccion: 'Página web', nombre: 'mostrar_en_web', etiqueta: 'Página web', tipo: 'si-no', textoCasilla: 'Sale en la sección de convenios' },
    // Lo de la web, solo si sale en la web.
    {
        seccion: 'Página web',
        nombre: 'requisito_web',
        etiqueta: 'Quién accede',
        ejemplo: 'Estudiantes con credencial vigente',
        ayuda: 'Se lee debajo del logo.',
        mostrarSi: (d) => Boolean(d.mostrar_en_web),
    },
    {
        seccion: 'Página web',
        nombre: 'logo',
        etiqueta: 'Logo',
        tipo: 'imagen',
        actual: 'logo_url',
        quitar: 'quitar_logo',
        ayuda: 'PNG, JPG o WEBP. Mejor con fondo blanco o transparente.',
        mostrarSi: (d) => Boolean(d.mostrar_en_web),
    },

    { seccion: 'Disponibilidad', nombre: 'activo', etiqueta: 'Al inscribir', tipo: 'si-no', textoCasilla: 'Se puede elegir' },
];

/** Lo que hay que mandar para guardar un plan, a partir de su ficha o su fila. */
export function valoresDePlan(plan) {
    return {
        nombre: plan?.nombre ?? '',
        descripcion: plan?.descripcion ?? '',
        // Meses o días: el que tenga, y si trae los dos, MESES. Los planes que
        // vinieron con la instalación guardan «1 mes» y «30 días» a la vez:
        // abiertos en días, al guardar se borraban los meses y el Mensual
        // pasaba a ser un pase de días (salía de la página de planes).
        // Un plan nuevo, en meses.
        unidad: Number(plan?.duracion_meses ?? 0) > 0 || Number(plan?.duracion_dias ?? 0) <= 0 ? 'meses' : 'dias',
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
        // Los nuevos entran como recomendados: la página es sobre todo de eso.
        vinculo: especialista?.vinculo ?? (tipo === 'embajador' ? 'equipo' : 'recomendado'),
        dias: especialista?.dias ?? [],
        horario: especialista?.horario ?? '',
        lugar: especialista?.lugar ?? '',
        foto: null,
        quitar_foto: false,
        foto_url: especialista?.foto_url ?? null,
        whatsapp: especialista?.whatsapp ?? '',
        instagram: especialista?.instagram ?? '',
        tiktok: especialista?.tiktok ?? '',
        email: especialista?.email ?? '',
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
                    accept={campo.acepta ?? 'image/jpeg,image/png,image/webp'}
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
const CORTOS = ['number', 'date', 'time', 'opciones', 'email', 'tel', 'password', 'dinero', 'celular'];

const miles = (valor) => (valor === '' || valor === null || valor === undefined ? '' : Number(valor).toLocaleString('es-CL'));

/**
 * El control de un campo según su tipo.
 *
 *  · area: texto largo; con `max`, un contador.
 *  · si-no: casilla.  · imagen: CampoImagen.
 *  · opciones: desplegable; con `botones: true`, botones (para pocas opciones).
 *  · varias: casillas; se guarda la lista de las marcadas.
 *  · dinero: con $ y puntos de miles mientras se escribe; se guarda el número.
 *  · celular: con el +56 9 puesto; el prefijo solo se manda vacío.
 *  · lo demás: texto, número, correo…
 */
function Control({ campo, data, setData }) {
    const valor = data[campo.nombre];

    if (campo.tipo === 'area') {
        return (
            <>
                <Area nombre={campo.nombre} valor={valor ?? ''} alCambiar={(v) => setData(campo.nombre, v)} filas={campo.filas ?? 2} maxLength={campo.max} />
                {campo.max ? (
                    <span className="apoyo -mt-0.5 self-end tabular-nums text-fog">
                        {String(valor ?? '').length}/{campo.max}
                    </span>
                ) : null}
            </>
        );
    }

    if (campo.tipo === 'si-no') {
        return (
            <label className="flex items-center gap-2 text-sm text-chalk">
                <input type="checkbox" checked={Boolean(valor)} onChange={(e) => setData(campo.nombre, e.target.checked)} className="size-4 accent-[var(--color-volt)]" />
                {campo.textoCasilla}
            </label>
        );
    }

    if (campo.tipo === 'varias') {
        const marcadas = Array.isArray(valor) ? valor : [];

        return (
            <div className="flex flex-wrap gap-x-4 gap-y-1.5">
                {campo.opciones.map((o) => (
                    <label key={o.valor} className="flex items-center gap-2 text-sm text-chalk">
                        <input
                            type="checkbox"
                            checked={marcadas.includes(o.valor)}
                            onChange={(e) => setData(campo.nombre, e.target.checked ? [...marcadas, o.valor] : marcadas.filter((v) => v !== o.valor))}
                            className="size-4 accent-[var(--color-volt)]"
                        />
                        {o.etiqueta}
                    </label>
                ))}
            </div>
        );
    }

    if (campo.tipo === 'imagen') {
        return <CampoImagen campo={campo} data={data} setData={setData} />;
    }

    if (campo.tipo === 'opciones' && campo.botones) {
        return (
            <Botones
                opciones={campo.opciones}
                valor={valor}
                alElegir={(v) => setData(campo.nombre, v)}
                nombre={campo.etiqueta}
                columnas={campo.columnas ?? 'grid-cols-2 sm:grid-cols-3'}
                compacto
            />
        );
    }

    if (campo.tipo === 'opciones') {
        return (
            <select
                id={campo.nombre}
                name={campo.nombre}
                value={valor ?? ''}
                onChange={(e) => setData(campo.nombre, e.target.value)}
                className="w-full rounded-control border border-line bg-surface-2 px-2 py-1.5 text-sm text-chalk focus:border-line-strong focus:outline-none"
            >
                {campo.opciones.map((o) => (
                    <option key={o.valor} value={o.valor}>
                        {o.etiqueta}
                    </option>
                ))}
            </select>
        );
    }

    if (campo.tipo === 'dinero') {
        return (
            <div className="flex">
                <span className="flex items-center rounded-l-control border border-r-0 border-line bg-surface-2 px-2.5 text-sm text-fog">$</span>
                <input
                    id={campo.nombre}
                    name={campo.nombre}
                    inputMode="numeric"
                    autoComplete="off"
                    value={miles(valor)}
                    onChange={(e) => {
                        const digitos = e.target.value.replace(/\D/g, '');
                        setData(campo.nombre, digitos === '' ? '' : Number(digitos));
                    }}
                    placeholder={campo.ejemplo ?? '0'}
                    className="w-full min-w-0 rounded-r-control border border-line bg-surface px-2.5 py-1.5 text-sm tabular-nums text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                />
            </div>
        );
    }

    if (campo.tipo === 'celular') {
        return (
            <Texto
                nombre={campo.nombre}
                tipo="tel"
                inputMode="tel"
                valor={valor && String(valor).trim() !== '' ? valor : PREFIJO}
                alCambiar={(v) => setData(campo.nombre, v)}
                autoComplete="off"
            />
        );
    }

    return (
        <Texto
            nombre={campo.nombre}
            tipo={campo.tipo ?? 'text'}
            min={campo.min}
            max={campo.max}
            valor={valor ?? ''}
            alCambiar={(v) => setData(campo.nombre, v)}
            placeholder={campo.ejemplo}
            // «new-password» en las contraseñas de una cuenta ajena: sin
            // él, el navegador rellena la del administrador que tiene guardada.
            autoComplete={campo.autocompletar}
        />
    );
}

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

    // Lo que cada campo pide ajustar antes de enviar (el celular sin el
    // prefijo solo, los meses en 0 si el plan dura días…).
    const preparar = (datos) => campos.reduce((d, c) => {
        let nuevo = c.alEnviar ? c.alEnviar(d) : d;

        if (c.tipo === 'celular' && soloPrefijo(nuevo[c.nombre])) {
            nuevo = { ...nuevo, [c.nombre]: '' };
        }

        return nuevo;
    }, datos);

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
            transform((datos) => ({ ...preparar(datos), _method: 'put' }));
            post(accion, opciones);

            return;
        }

        transform((datos) => preparar(datos));
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
                    {campos
                        .filter((campo) => ! campo.mostrarSi || campo.mostrarSi(data))
                        .map((campo, i, visibles) => (
                            <Fragment key={campo.nombre}>
                                {/* Un título cuando empieza otra sección: el convenio
                                    mezclaba el acuerdo, el contacto y la web en una tira. */}
                                {campo.seccion && campo.seccion !== visibles[i - 1]?.seccion ? (
                                    <h3 className={`rotulo border-b border-line pb-1 ${i > 0 ? 'pt-2' : ''} ${ancha ? 'sm:col-span-2' : ''}`}>{campo.seccion}</h3>
                                ) : null}
                                <div className={ancha && ! CORTOS.includes(campo.tipo) ? 'sm:col-span-2' : ''}>
                                    <Campo
                                        etiqueta={campo.etiqueta}
                                        nombre={campo.nombre}
                                        error={errors[campo.nombre]}
                                        requerido={campo.requerido}
                                        ayuda={campo.ayuda}
                                    >
                                        <Control campo={campo} data={data} setData={setData} />
                                    </Campo>
                                </div>
                            </Fragment>
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
                            <TextoQueCambia ocupado={processing} mientras="Guardando…">Guardar</TextoQueCambia>
                        </button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
