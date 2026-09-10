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
