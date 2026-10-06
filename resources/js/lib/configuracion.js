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
        // Qué se vende, a qué precio y con qué reglas. Es lo que más se
        // vuelve a abrir.
        titulo: 'Ventas',
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
            { href: '/panel/notificaciones/plantillas', etiqueta: 'Plantillas de correo', permiso: 'notificaciones.ver' },
            { href: '/panel/configuracion/tareas', etiqueta: 'Avisos automáticos', permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'Página web',
        icono: 'web',
        secciones: [
            // Qué páginas se ven: se encienden cuando están listas.
            { href: '/panel/configuracion/paginas', etiqueta: 'Páginas que se ven', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/portada', etiqueta: 'Portada y aviso', permiso: 'configuracion.ver' },
            { href: '/panel/web/servicio', etiqueta: 'Servicios', permiso: 'configuracion.ver' },
            { href: '/panel/web/foto', etiqueta: 'Fotos', permiso: 'configuracion.ver' },
            { href: '/panel/web/testimonio', etiqueta: 'Testimonios', permiso: 'configuracion.ver' },
            // Judo, lucha…: abiertas a todos y con mensualidad. No son los talleres.
            { href: '/panel/clases', etiqueta: 'Clases', permiso: 'configuracion.ver' },
            { href: '/panel/web/arriendo', etiqueta: 'Arriendo: fotos', permiso: 'configuracion.ver' },
            { href: '/panel/web/institucion', etiqueta: 'Arriendo: logos', permiso: 'configuracion.ver' },
            { href: '/panel/especialistas', etiqueta: 'Especialistas', permiso: 'configuracion.ver' },
            { href: '/panel/embajadores', etiqueta: 'Embajadores', permiso: 'configuracion.ver' },
            // El QR de la sala: «Qué entrenar hoy».
            { href: '/panel/rutinas', etiqueta: 'Rutinas de la sala', permiso: 'configuracion.ver' },
            { href: '/panel/ejercicios', etiqueta: 'Ejercicios', permiso: 'configuracion.ver' },
            { href: '/panel/configuracion/web', etiqueta: 'Google, redes y tienda', permiso: 'configuracion.ver' },
        ],
    },
    {
        titulo: 'Contrato y legales',
        icono: 'legal',
        secciones: [
            { href: '/panel/textos-legales/contrato', etiqueta: 'Contrato', permiso: 'configuracion.ver' },
            { href: '/panel/textos-legales/terminos', etiqueta: 'Términos y condiciones', permiso: 'configuracion.ver' },
            { href: '/panel/textos-legales/privacidad', etiqueta: 'Política de privacidad', permiso: 'configuracion.ver' },
        ],
    },
    {
        // El panel mismo: qué se ve en la pantalla del mesón, quién entra y
        // lo que se borró.
        titulo: 'Panel',
        icono: 'sistema',
        secciones: [
            // Si las cifras del negocio se ven o no. Aqui y no en «El
            // gimnasio»: no es como trabaja el gimnasio, es que decide este
            // panel enseñar.
            { href: '/panel/configuracion/privacidad', etiqueta: 'Lo que se ve en pantalla', permiso: 'configuracion.ver' },
            // Exacta: si no, «Qué puede cada perfil», que cuelga de
            // /panel/usuarios/, encendería las dos a la vez.
            { href: '/panel/usuarios', etiqueta: 'Usuarios del panel', permiso: 'usuarios.ver', exacta: true },
            { href: '/panel/usuarios/perfiles', etiqueta: 'Qué puede cada perfil', permiso: 'usuarios.editar' },
            { href: '/panel/papelera', etiqueta: 'Papelera', permiso: 'configuracion.ver' },
            // Lo que salió mal, en el servidor o en el navegador.
            { href: '/panel/fallas', etiqueta: 'Registro de fallas', permiso: 'configuracion.ver' },
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
    '/panel/clases',
    '/panel/especialistas',
    '/panel/embajadores',
    '/panel/usuarios',
    '/panel/papelera',
    '/panel/textos-legales',
    '/panel/fallas',
    '/panel/rutinas',
    '/panel/ejercicios',
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
