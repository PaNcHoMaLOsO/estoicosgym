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
        // Quién es el gimnasio y cómo trabaja el mesón: lo que se toca al
        // estrenar el sistema y casi nunca después.
        titulo: 'El gimnasio',
        icono: 'gimnasio',
        secciones: [
            { href: '/panel/configuracion/gimnasio', etiqueta: 'Datos del gimnasio', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/horario', etiqueta: 'Horario', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/meson', etiqueta: 'Mesón', permiso: 'configuracion.ver' },
        ],
    },
    {
        // Qué se vende y a qué precio. Es lo que más se vuelve a abrir.
        titulo: 'Planes y cobros',
        icono: 'cobros',
        secciones: [
            { href: '/panel/membresias', etiqueta: 'Planes y precios', permiso: 'configuracion.ver' },
            { href: '/panel/convenios', etiqueta: 'Convenios', permiso: 'configuracion.ver' },
            { href: '/panel/metodos-pago', etiqueta: 'Métodos de pago', permiso: 'configuracion.ver' },
            { href: '/panel/motivos-descuento', etiqueta: 'Motivos de descuento', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/reglas', etiqueta: 'Reglas de las membresías', permiso: 'configuracion.ver' },
        ],
    },
    {
        /*
         * Todo el correo junto, en el orden en que se configura: primero desde
         * qué cuenta se escribe, después qué dice cada mensaje, y al final
         * cuándo salen solos. Antes «Mesón y correos» mezclaba las notas del
         * mesón con esto, y «Correos y tareas» sonaba igual que «Correo de
         * salida» sin serlo.
         */
        titulo: 'Correos',
        icono: 'correos',
        secciones: [
            { href: '/panel/configuracion/correo', etiqueta: 'Cuenta de correo', permiso: 'configuracion.ver' },
            { href: '/panel/notificaciones/plantillas', etiqueta: 'Qué dice cada correo', permiso: 'notificaciones.ver' },
            { href: '/panel/configuracion/tareas', etiqueta: 'Avisos automáticos', permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'Página web',
        icono: 'web',
        secciones: [
            { href: '/panel/configuracion/portada', etiqueta: 'Portada y aviso', permiso: 'configuracion.ver' },
            { href: '/panel/web/servicio', etiqueta: 'Servicios', permiso: 'configuracion.ver' },
            { href: '/panel/web/foto', etiqueta: 'Fotos', permiso: 'configuracion.ver' },
            { href: '/panel/web/pregunta', etiqueta: 'Preguntas frecuentes', permiso: 'configuracion.ver' },
            { href: '/panel/web/testimonio', etiqueta: 'Testimonios', permiso: 'configuracion.ver' },
            { href: '/panel/especialistas', etiqueta: 'Especialistas', permiso: 'configuracion.ver' },
            { href: '/panel/embajadores', etiqueta: 'Embajadores', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/web', etiqueta: 'Google y redes', permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'Contrato y privacidad',
        icono: 'legal',
        secciones: [
            { href: '/panel/textos-legales/contrato', etiqueta: 'Contrato', permiso: 'configuracion.ver' },
            { href: '/panel/textos-legales/terminos', etiqueta: 'Términos y condiciones', permiso: 'configuracion.ver' },
            { href: '/panel/textos-legales/privacidad', etiqueta: 'Política de privacidad', permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'Sistema',
        icono: 'sistema',
        secciones: [
            // Si las cifras del negocio se ven o no. Aqui y no en «El
            // gimnasio»: no es como trabaja el gimnasio, es que decide este
            // panel enseñar.
            { href: '/panel/configuracion/privacidad', etiqueta: 'El dinero en pantalla', permiso: 'configuracion.ver' },
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
    '/panel/embajadores',
    '/panel/usuarios',
    '/panel/papelera',
    '/panel/textos-legales',
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
