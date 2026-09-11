import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { PencilIcon, PlusIcon } from 'lucide-react';

import Activo from '@/components/Activo';
import FormularioCatalogo, {
    CAMPOS_CONVENIO,
    TIPOS_CONVENIO,
    valoresDeConvenio,
} from '@/components/FormularioCatalogo';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const COLUMNAS = ['Convenio', 'Tipo', 'Descuento', 'Contacto', 'Socios', 'En la web', 'Estado', ''];

/** Cómo se lee un tipo en la tabla. */
const NOMBRE_TIPO = Object.fromEntries(TIPOS_CONVENIO.map((t) => [t.valor, t.etiqueta]));

const NUEVO = {
    nombre: '',
    tipo: 'empresa',
    descripcion: '',
    descuento_porcentaje: '',
    descuento_monto: '',
    contacto_nombre: '',
    contacto_telefono: '',
    contacto_email: '',
    activo: true,
};

export default function Convenios({ convenios }) {
    // null = cerrado; una fila = editando esa; NUEVO = creando.
    const [editando, setEditando] = useState(null);

    // Desactivar, no borrar: los socios que vinieron por este convenio lo
    // apuntan en su ficha.
    function alternar(convenio) {
        router.patch(`/panel/catalogos/convenios/${convenio.uuid}/alternar`, {}, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Convenios" />

            <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Convenios</h1>
                    <p className="apoyo text-fog">Empresas e instituciones con descuento</p>
                </div>

                <button
                    type="button"
                    onClick={() => setEditando(NUEVO)}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Nuevo convenio
                </button>
            </header>

            <Tabla
                columnas={COLUMNAS}
                vacia={convenios.length === 0}
                mensajeVacio="Todavía no hay convenios registrados."
            >
                {convenios.map((convenio) => (
                    <Fila key={convenio.uuid}>
                        <Celda className="font-medium text-chalk">
                            <Link href={`/panel/convenios/${convenio.uuid}`} className="hover:underline">
                                {convenio.nombre}
                            </Link>
                        </Celda>
                        <Celda>{NOMBRE_TIPO[convenio.tipo] ?? convenio.tipo ?? '—'}</Celda>
                        <Celda className="tabular-nums">{convenio.descuento}</Celda>
                        <Celda>{convenio.contacto ?? '—'}</Celda>
                        <Cifra>{convenio.clientes}</Cifra>
                        <Celda>{convenio.mostrar_en_web ? (convenio.logo_url ? 'Sí' : 'Sí, sin logo') : '—'}</Celda>
                        <Celda>
                            <Activo valor={convenio.activo} />
                        </Celda>
                        <Celda className="text-right">
                            <div className="inline-flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setEditando(convenio)}
                                    aria-label={`Editar ${convenio.nombre}`}
                                    className="text-fog transition-colors hover:text-chalk"
                                >
                                    <PencilIcon className="size-4" aria-hidden="true" />
                                </button>

                                <button
                                    type="button"
                                    onClick={() => alternar(convenio)}
                                    className="apoyo text-fog transition-colors hover:text-chalk"
                                >
                                    {convenio.activo ? 'Desactivar' : 'Activar'}
                                </button>
                            </div>
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <FormularioCatalogo
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                titulo={editando?.uuid ? 'Editar convenio' : 'Nuevo convenio'}
                descripcion={
                    editando?.uuid
                        ? 'Los socios que ya vinieron por este convenio no cambian.'
                        : 'Aparecerá como opción al inscribir.'
                }
                accion={editando?.uuid ? `/panel/convenios/${editando.uuid}` : '/panel/convenios'}
                metodo={editando?.uuid ? 'put' : 'post'}
                campos={CAMPOS_CONVENIO}
                valores={valoresDeConvenio(editando)}
            />
        </>
    );
}
