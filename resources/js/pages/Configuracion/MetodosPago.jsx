import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { CheckIcon, PencilIcon, PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo from '@/components/FormularioCatalogo';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Método', 'Comprobante', 'Pagos registrados', 'Estado', ''];

const CAMPOS = [
    {
        nombre: 'nombre',
        etiqueta: 'Nombre',
        requerido: true,
        ejemplo: 'Efectivo, Transferencia…',
    },
    {
        nombre: 'descripcion',
        etiqueta: 'Descripción',
        tipo: 'area',
        ayuda: 'Opcional. Para aclarar algo a quien cobra.',
    },
    {
        nombre: 'requiere_comprobante',
        etiqueta: 'Comprobante',
        tipo: 'si-no',
        textoCasilla: 'Pedir número de comprobante al cobrar',
        ayuda: 'Márcalo en transferencias y tarjeta, donde hace falta el respaldo.',
    },
    {
        nombre: 'activo',
        etiqueta: 'Disponibilidad',
        tipo: 'si-no',
        textoCasilla: 'Se puede elegir al cobrar',
    },
];

const NUEVO = {
    nombre: '',
    descripcion: '',
    requiere_comprobante: false,
    activo: true,
};

export default function MetodosPago({ metodos }) {
    // null = cerrado; una fila = editando esa; NUEVO = creando.
    const [editando, setEditando] = useState(null);

    /*
     * No hay «eliminar»: un metodo que ya se uso esta referenciado por los
     * pagos, y borrarlo dejaria esos pagos sin decir con que se cobraron —y
     * borraria esas cifras de los informes de años anteriores—. Desactivar hace
     * lo que de verdad se quiere: que no vuelva a ofrecerse.
     */
    function alternar(metodo) {
        router.patch(`/panel/catalogos/metodos-pago/${metodo.id}/alternar`, {}, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Métodos de pago" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Métodos de pago</h1>
                    <p className="apoyo text-fog">Cómo se puede pagar en el mesón</p>
                </div>

                <button
                    type="button"
                    onClick={() => setEditando(NUEVO)}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nuevo método
                </button>
            </header>

            <Tabla
                columnas={COLUMNAS}
                vacia={metodos.length === 0}
                mensajeVacio="Todavía no hay métodos de pago configurados."
            >
                {metodos.map((metodo) => (
                    <Fila key={metodo.id}>
                        <Celda className="font-medium text-chalk">
                            {metodo.nombre}
                            {metodo.descripcion ? (
                                <span className="apoyo block text-fog">{metodo.descripcion}</span>
                            ) : null}
                        </Celda>
                        <Celda>
                            {/* Glifo mas texto: el icono solo no dice cual de los dos es. */}
                            {metodo.requiere_comprobante ? (
                                <span className="inline-flex items-center gap-1 text-chalk">
                                    <CheckIcon className="size-3.5" aria-hidden="true" />
                                    Requiere
                                </span>
                            ) : (
                                <span className="text-fog">No requiere</span>
                            )}
                        </Celda>
                        <Cifra>{metodo.pagos}</Cifra>
                        <Celda>
                            <Activo valor={metodo.activo} />
                        </Celda>
                        <Celda className="text-right">
                            <div className="inline-flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setEditando(metodo)}
                                    aria-label={`Editar ${metodo.nombre}`}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    <PencilIcon className="size-4" aria-hidden="true" />
                                </button>

                                <button
                                    type="button"
                                    onClick={() => alternar(metodo)}
                                    className="apoyo text-fog transition-colors hover:text-chalk"
                                >
                                    {metodo.activo ? 'Desactivar' : 'Activar'}
                                </button>
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <FormularioCatalogo
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                titulo={editando?.id ? 'Editar método de pago' : 'Nuevo método de pago'}
                descripcion={
                    editando?.id
                        ? 'Los pagos ya registrados con este método no cambian.'
                        : 'Aparecerá como opción al cobrar.'
                }
                accion={editando?.id ? `/panel/metodos-pago/${editando.id}` : '/panel/metodos-pago'}
                metodo={editando?.id ? 'put' : 'post'}
                campos={CAMPOS}
                valores={{
                    nombre: editando?.nombre ?? '',
                    descripcion: editando?.descripcion ?? '',
                    requiere_comprobante: Boolean(editando?.requiere_comprobante),
                    activo: editando?.id ? Boolean(editando.activo) : true,
                }}
            />
        </>
    );
}
