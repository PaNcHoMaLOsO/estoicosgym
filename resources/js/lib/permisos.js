/**
 * ¿Puede el usuario hacer esto?
 *
 * Misma regla que App\Models\User::puede(): el comodin «*» lo puede todo y
 * «clientes.*» concede todo lo de clientes.
 *
 * ESTO SOLO SIRVE PARA NO PINTAR lo que no se puede usar. Quien decide de
 * verdad es el middleware del servidor, que revisa cada peticion: esconder un
 * boton no protege nada, solo evita que alguien choque contra un 403.
 */
export function puede(auth, permiso) {
    const permisos = auth?.user?.permisos;

    if (!Array.isArray(permisos)) {
        return false;
    }

    if (permisos.includes('*') || permisos.includes(permiso)) {
        return true;
    }

    const [modulo] = permiso.split('.');

    return permisos.includes(`${modulo}.*`);
}
