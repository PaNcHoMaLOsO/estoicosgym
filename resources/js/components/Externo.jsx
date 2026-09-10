/**
 * Enlace a una pantalla que TODAVÍA vive en Blade.
 *
 * POR QUÉ NO SE USA <Link>. El de Inertia intercepta el clic y pide la ruta como
 * si fuera una página suya; una vista de Blade le devuelve HTML completo, e
 * Inertia lo pinta encima de la página actual. En pantalla queda un recuadro
 * oscuro con medio AdminLTE dentro y sin forma de volver.
 *
 * Es un fallo que no avisa —no hay error en consola, solo una pantalla rota—,
 * asi que conviene que el enlace diga por sí mismo a dónde va: mientras queden
 * pantallas en el panel antiguo, cualquier enlace hacia /admin usa esto.
 *
 * Cuando esa pantalla se migre, basta con cambiar <Externo> por <Link>.
 */
export default function Externo({ href, className, children, ...resto }) {
    return (
        <a href={href} className={className} {...resto}>
            {children}
        </a>
    );
}
