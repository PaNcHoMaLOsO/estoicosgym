import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { MailIcon, PencilIcon, UserPlusIcon } from 'lucide-react';

import Dialogo from '@/components/Dialogo';
import FormularioCatalogo from '@/components/FormularioCatalogo';
import { haceCuanto } from '@/lib/tiempo';

/**
 * Las cuentas del panel: quién entra y qué puede hacer.
 *
 * Las cuentas NO se borran: se desactivan. Lo que hizo cada una —un pago, un
 * fiado— queda a su nombre, y una cuenta borrada dejaría esas fichas sin autor.
 */
function camposDe(roles, usuario) {
    const campos = [
        { nombre: 'nombre', etiqueta: 'Nombre', requerido: true, ejemplo: 'Camila Rojas' },
        {
            nombre: 'email',
            etiqueta: 'Correo',
            tipo: 'email',
            requerido: true,
            ayuda: 'Con este correo entra al panel.',
            autocompletar: 'off',
        },
        {
            nombre: 'id_rol',
            etiqueta: 'Rol',
            tipo: 'opciones',
            opciones: roles,
            requerido: true,
            ayuda: 'Lo que puede ver y hacer en el panel.',
        },
        { nombre: 'telefono', etiqueta: 'Celular', ejemplo: '9 1234 5678', ayuda: 'Opcional.' },
    ];

    if (usuario?.id) {
        campos.push({ nombre: 'activo', etiqueta: 'Acceso', tipo: 'si-no', textoCasilla: 'Puede entrar al panel' });

        /*
         * El segundo factor solo se puede APAGAR desde aquí. Encenderlo sin un
         * canal de SMS o WhatsApp configurado dejaría la cuenta afuera: el
         * código no llega y el login no la deja pasar sin él.
         */
        if (usuario.dos_factores) {
            campos.push({
                nombre: 'dos_factores',
                etiqueta: 'Segundo factor',
                tipo: 'si-no',
                textoCasilla: 'Pedir un código al celular al entrar',
                ayuda: 'Apágalo si el código no le está llegando.',
            });
        }
    }

    campos.push(
        {
            nombre: 'clave',
            etiqueta: usuario?.id ? 'Contraseña nueva' : 'Contraseña',
            tipo: 'password',
            requerido: !usuario?.id,
            autocompletar: 'new-password',
            ayuda: usuario?.id ? 'Déjala vacía para no cambiarla.' : 'Al menos 8 caracteres. Dísela a la persona; después puede cambiarla.',
        },
        {
            nombre: 'clave_confirmation',
            etiqueta: 'Repite la contraseña',
            tipo: 'password',
            requerido: !usuario?.id,
            autocompletar: 'new-password',
        },
    );

    return campos;
}

function valoresDe(usuario, roles) {
    // Una cuenta nueva empieza con el rol que menos puede: se sube si hace falta.
    const recepcion = roles.find((r) => r.etiqueta.toLowerCase().startsWith('recep'));

    return {
        nombre: usuario?.nombre ?? '',
        email: usuario?.email ?? '',
        id_rol: usuario?.id_rol ? String(usuario.id_rol) : (recepcion?.valor ?? roles[0]?.valor ?? ''),
        telefono: usuario?.telefono ?? '',
        activo: usuario?.id ? Boolean(usuario.activo) : true,
        dos_factores: Boolean(usuario?.dos_factores),
        clave: '',
        clave_confirmation: '',
    };
}

export default function Index({ usuarios, roles }) {
    const [editando, setEditando] = useState(null);
    // El aviso gris del navegador no dice de qué sistema viene ni se puede
    // leer con calma: el correo que se va a mandar tiene que verse entero.
    const [enlaceA, setEnlaceA] = useState(null);

    return (
        <>
            <Head title="Usuarios del panel" />

            <header className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Usuarios del panel</h1>
                    <p className="apoyo max-w-2xl text-fog">
                        Quién entra al panel y qué puede hacer. Las cuentas no se borran: se desactivan, y lo que hizo
                        cada una queda a su nombre.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => setEditando({})}
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <UserPlusIcon className="size-4" aria-hidden="true" />
                    Nueva cuenta
                </button>
            </header>

            <ul className="max-w-3xl divide-y divide-line overflow-hidden rounded-panel border border-line bg-surface">
                {usuarios.map((u) => (
                    <li key={u.id} className={`flex flex-wrap items-center gap-3 px-4 py-3 ${u.activo ? '' : 'opacity-60'}`}>
                        <span
                            className="grid size-8 shrink-0 place-content-center rounded-full bg-surface-2 text-sm font-semibold text-chalk uppercase"
                            aria-hidden="true"
                        >
                            {u.nombre.slice(0, 1)}
                        </span>

                        <div className="min-w-0 flex-1">
                            <p className="text-sm font-medium text-chalk">
                                {u.nombre}
                                {u.soy_yo ? <span className="apoyo ml-1.5 text-fog">(tú)</span> : null}
                            </p>
                            <p className="apoyo truncate text-fog">
                                {u.email} · {u.rol ?? 'sin rol'}
                                {u.dos_factores ? ' · con segundo factor' : ''}
                            </p>
                            <p className="apoyo text-fog">
                                {u.activo
                                    ? u.ultima_vez
                                        ? `Última actividad ${haceCuanto(u.ultima_vez)}`
                                        : null
                                    : 'Desactivada: no puede entrar.'}
                            </p>
                        </div>

                        <div className="flex shrink-0 flex-wrap gap-2">
                            {u.activo && !u.soy_yo ? (
                                <button
                                    type="button"
                                    onClick={() => setEnlaceA(u)}
                                    className="inline-flex items-center gap-1.5 rounded-control border border-line px-2.5 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                                >
                                    <MailIcon className="size-4" aria-hidden="true" />
                                    Enlace de contraseña
                                </button>
                            ) : null}

                            <button
                                type="button"
                                onClick={() => setEditando(u)}
                                className="inline-flex items-center gap-1.5 rounded-control border border-line px-2.5 py-1.5 text-sm text-chalk transition-colors hover:bg-surface-2"
                            >
                                <PencilIcon className="size-4" aria-hidden="true" />
                                Editar
                            </button>
                        </div>
                    </li>
                ))}
            </ul>

            <FormularioCatalogo
                abierto={editando !== null}
                alCerrar={() => setEditando(null)}
                titulo={editando?.id ? `Editar a ${editando.nombre}` : 'Nueva cuenta'}
                descripcion={
                    editando?.id
                        ? null
                        : 'Dale a la persona su correo y esta contraseña. Después puede cambiarla con «¿Olvidaste tu contraseña?».'
                }
                accion={editando?.id ? `/panel/usuarios/${editando.id}` : '/panel/usuarios'}
                metodo={editando?.id ? 'put' : 'post'}
                campos={camposDe(roles, editando)}
                valores={valoresDe(editando, roles)}
            />
            {enlaceA ? (
                <Dialogo
                    abierto
                    alCerrar={() => setEnlaceA(null)}
                    titulo="Mandarle el enlace para su contraseña"
                    descripcion={`Le llega a ${enlaceA.email} un enlace para que ponga su propia contraseña. Vence en unas horas; si se le pasa, se le manda otro.`}
                    accion={`/panel/usuarios/${enlaceA.id}/enlace`}
                    via="inertia"
                    metodo="post"
                    etiquetaConfirmar="Mandar el enlace"
                />
            ) : null}

        </>
    );
}
