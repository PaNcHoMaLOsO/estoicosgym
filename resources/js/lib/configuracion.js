/**
 * Las secciones de Configuración, en el orden en que se leen.
 *
 * UNA sola lista para el menú de la izquierda, el selector del celular y el
 * carril principal —que enciende «Configuración» en cualquiera de estas
 * direcciones—. Si cada uno llevara la suya, la sección que se sume mañana
 * aparecería en un sitio y en el otro no.
 */
export const SECCIONES_CONFIGURACION = [
    {
        titulo: null,
        secciones: [
            { href: '/panel/configuracion', etiqueta: 'Lo que falta', exacta: true, permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'El gimnasio',
        secciones: [
            { href: '/panel/configuracion/gimnasio', etiqueta: 'Datos del gimnasio', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/horario', etiqueta: 'Horario', permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'Membresías y cobros',
        secciones: [
            { href: '/panel/membresias', etiqueta: 'Planes y precios', permiso: 'configuracion.ver' },
            { href: '/panel/convenios', etiqueta: 'Convenios', permiso: 'configuracion.ver' },
            { href: '/panel/metodos-pago', etiqueta: 'Métodos de pago', permiso: 'configuracion.ver' },
            { href: '/panel/motivos-descuento', etiqueta: 'Motivos de descuento', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/reglas', etiqueta: 'Reglas', permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'Mesón y correos',
        secciones: [
            { href: '/panel/configuracion/meson', etiqueta: 'Mesón', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/tareas', etiqueta: 'Correos y tareas', permiso: 'configuracion.ver' },
            { href: '/panel/notificaciones/plantillas', etiqueta: 'Plantillas de correo', permiso: 'notificaciones.ver' },
        ],
    },
    {
        titulo: 'Página web',
        secciones: [
            { href: '/panel/configuracion/portada', etiqueta: 'Portada y aviso', permiso: 'configuracion.ver' },
            { href: '/panel/web/servicio', etiqueta: 'Servicios', permiso: 'configuracion.ver' },
            { href: '/panel/web/foto', etiqueta: 'Fotos', permiso: 'configuracion.ver' },
            { href: '/panel/web/pregunta', etiqueta: 'Preguntas frecuentes', permiso: 'configuracion.ver' },
            { href: '/panel/web/testimonio', etiqueta: 'Testimonios', permiso: 'configuracion.ver' },
            { href: '/panel/especialistas', etiqueta: 'Especialistas', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/web', etiqueta: 'Google y redes', permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'Sistema',
        secciones: [
            { href: '/panel/usuarios', etiqueta: 'Usuarios del panel', permiso: 'usuarios.ver' },
            { href: '/panel/papelera', etiqueta: 'Papelera', permiso: 'configuracion.ver' },
        ],
    },
];

/** Todas las direcciones que son «Configuración», para el carril principal. */
export const PREFIJOS_CONFIGURACION = [
    '/panel/configuracion',
    '/panel/membresias',
    '/panel/convenios',
    '/panel/metodos-pago',
    '/panel/motivos-descuento',
    '/panel/notificaciones/plantillas',
    '/panel/web',
    '/panel/especialistas',
    '/panel/usuarios',
    '/panel/papelera',
];

/** La ruta sin la consulta ni la barra final: «/panel/convenios». */
export function rutaDe(url) {
    return url.split('?')[0].replace(/\/$/, '') || '/panel';
}

/**
 * ¿Es la sección que se está viendo?
 *
 * Por prefijo, para que la ficha de un convenio encienda «Convenios»; salvo
 * la portada, que se marca solo en su propia dirección —si no, todas las
 * demás empiezan por /panel/configuracion/ y la encenderían también—.
 */
export function seccionActiva(seccion, url) {
    const ruta = rutaDe(url);

    if (seccion.exacta) {
        return ruta === seccion.href;
    }

    return ruta === seccion.href || ruta.startsWith(`${seccion.href}/`);
}
