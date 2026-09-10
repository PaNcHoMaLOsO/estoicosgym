import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';

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
];

export const TIPOS_CONVENIO = [
    { valor: 'empresa', etiqueta: 'Empresa' },
    { valor: 'institucion_educativa', etiqueta: 'Institución educativa' },
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
        max_pausas: plan?.max_pausas ?? 1,
        precio: plan?.precio ?? '',
        // El precio de convenio puede no existir, y 0 no es lo mismo que «no
        // tiene»: uno significa gratis con convenio y el otro, sin convenio.
        precio_convenio: plan?.precio_convenio ?? '',
        activo: plan?.uuid ? Boolean(plan.activo) : true,
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
        activo: convenio?.uuid ? Boolean(convenio.activo) : true,
    };
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
    const { data, setData, post, put, processing, errors, clearErrors } = useForm(valores);

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

        const enviarlo = metodo === 'put' ? put : post;

        enviarlo(accion, {
            preserveScroll: true,
            onSuccess: () => alCerrar(),
        });
    }

    return (
        <Dialog open={abierto} onOpenChange={(v) => (! v && ! processing ? alCerrar() : null)}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{titulo}</DialogTitle>
                    {descripcion ? <DialogDescription>{descripcion}</DialogDescription> : null}
                </DialogHeader>

                <form onSubmit={enviar} className="space-y-3">
                    {campos.map((campo) => (
                        <Campo
                            key={campo.nombre}
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
                                />
                            )}
                        </Campo>
                    ))}

                    <div className="flex items-center justify-end gap-3 pt-1">
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
