import { Link, router, usePage } from '@inertiajs/react';
import {
    BellIcon,
    ChartNoAxesColumnIcon,
    ClipboardListIcon,
    CreditCardIcon,
    HistoryIcon,
    LayoutDashboardIcon,
    LogOutIcon,
    MenuIcon,
    MonitorIcon,
    NotebookPenIcon,
    SchoolIcon,
    TicketIcon,
    MoonIcon,
    SettingsIcon,
    SunIcon,
    UsersIcon,
    WalletIcon,
    XIcon,
    XCircleIcon,
    AlertTriangleIcon,
    CheckCircle2Icon,
    InfoIcon,
} from 'lucide-react';
import { useEffect, useState } from 'react';

import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { TooltipProvider } from '@/components/ui/tooltip';
import { PREFIJOS_CONFIGURACION } from '@/lib/configuracion';
import { puede } from '@/lib/permisos';
import { fijarTema, usarPreferenciaDeTema } from '@/lib/tema';
import BuscadorGlobal from '@/components/BuscadorGlobal';
import { BotonPrivado, ProveedorPrivado } from '@/Privado';

/**
 * Navegacion del panel.
 *
 * Agrupada por lo que se HACE y no por el modelo que hay detras: quien atiende
 * el mesón no busca "Inscripcion", busca al socio que tiene delante. Por eso
 * Clientes va primero y Configuracion se ancla abajo, que se toca una vez al
 * mes y no debe competir con lo que se abre cuarenta veces al dia.
 */
const GRUPOS = [
    {
        // Sin rótulo: es la puerta de entrada, no un apartado más. Un título
        // encima de una sola línea solo gasta espacio.
        titulo: null,
        secciones: [
            { href: '/panel', etiqueta: 'Resumen', Icono: LayoutDashboardIcon, permiso: 'clientes.ver' },
        ],
    },
    {
        // La persona y su plan: lo que se abre con el socio delante.
        titulo: 'Socios',
        secciones: [
            { href: '/panel/clientes', etiqueta: 'Clientes', Icono: UsersIcon, permiso: 'clientes.ver' },
            { href: '/panel/inscripciones', etiqueta: 'Inscripciones', Icono: ClipboardListIcon, permiso: 'inscripciones.ver' },
            { href: '/panel/pagos', etiqueta: 'Pagos', Icono: CreditCardIcon, permiso: 'pagos.ver' },
        ],
    },
    {
        /*
         * Lo suelto del día, que no es membresía ni toca la caja: la barra de
         * proteína que alguien se lleva fiada y el huésped del hotel que entra
         * con su tarjeta. Aparte de Pagos a propósito: juntarlos invitaría a
         * confundir esa plata con la de las membresías.
         */
        titulo: 'Mostrador',
        secciones: [
            { href: '/panel/fiados', etiqueta: 'Fiado', Icono: NotebookPenIcon, permiso: 'clientes.ver' },
            { href: '/panel/canje', etiqueta: 'Canje', Icono: TicketIcon, permiso: 'clientes.ver' },
            // La sala que se le presta a un colegio y se le factura por hora:
            // no son socios ni mensualidades, pero es lo mismo de anotar.
            { href: '/panel/talleres', etiqueta: 'Talleres', Icono: SchoolIcon, permiso: 'pagos.ver' },
        ],
    },
    {
        // Cómo va el negocio. Recepción no ve ninguna de las dos.
        titulo: 'Dinero',
        secciones: [
            // `caja`: desaparece cuando se pidio cerrar Caja. La pantalla
            // entera son numeros del negocio; tapados, seria una pantalla de
            // puntitos.
            { href: '/panel/caja', etiqueta: 'Caja', Icono: WalletIcon, permiso: 'reportes.ver', caja: true },
            {
                href: '/panel/reportes',
                etiqueta: 'Reportes',
                Icono: ChartNoAxesColumnIcon,
                permiso: 'reportes.ver',
            },
        ],
    },
    {
        // Qué se le mandó a quién y qué tocó cada uno: se mira cuando algo no
        // cuadra, no todos los días.
        titulo: 'Avisos',
        secciones: [
            {
                href: '/panel/notificaciones',
                etiqueta: 'Notificaciones',
                Icono: BellIcon,
                permiso: 'notificaciones.ver',
                // Las plantillas de correo se ven dentro de Configuración: ahí
                // se marca esa, no las dos a la vez.
                excepto: ['/panel/notificaciones/plantillas'],
            },
            { href: '/panel/historial', etiqueta: 'Historial', Icono: HistoryIcon, permiso: 'historial.ver' },
        ],
    },
];

/*
 * UNA sola entrada, anclada abajo.
 *
 * Adentro está todo lo que se toca de tarde en tarde —datos del gimnasio,
 * planes, convenios, la página web, usuarios—, cada cosa con su menú a la
 * izquierda. Se enciende en CUALQUIERA de esas pantallas y no solo en
 * /panel/configuracion: antes, estando en Convenios el carril no marcaba nada
 * y no había cómo saber dónde se estaba.
 */
const CONFIGURACION = [
    {
        href: '/panel/configuracion',
        etiqueta: 'Configuración',
        Icono: SettingsIcon,
        permiso: 'configuracion.ver',
        tambien: PREFIJOS_CONFIGURACION,
    },
];

const TEMAS = [
    { valor: 'sistema', etiqueta: 'Como el sistema', Icono: MonitorIcon },
    { valor: 'claro', etiqueta: 'Claro', Icono: SunIcon },
    { valor: 'oscuro', etiqueta: 'Oscuro', Icono: MoonIcon },
];

/** Activo tambien en las fichas: /panel/clientes/{uuid} marca "Clientes". */
function esActiva(seccion, url) {
    const ruta = url.split('?')[0].replace(/\/$/, '') || '/panel';
    const coincide = (href) => ruta === href || ruta.startsWith(`${href}/`);

    /*
     * La portada del panel se marca SOLO en su propia ruta.
     *
     * Con la regla de prefijo a secas, «/panel/clientes» empieza por «/panel/»
     * y encendia tambien Resumen: las dos secciones salian marcadas a la vez y
     * el carril dejaba de decir donde estas, que es lo unico que hace.
     */
    if (seccion.href === '/panel') {
        return ruta === '/panel';
    }

    if ((seccion.excepto ?? []).some(coincide)) {
        return false;
    }

    // El resto SI usa el prefijo, para que una ficha marque su seccion:
    // /panel/clientes/{uuid} tiene que encender «Clientes». `tambien` suma
    // las otras direcciones que son la misma seccion.
    return [seccion.href, ...(seccion.tambien ?? [])].some(coincide);
}

/**
 * Firma de PRO GYM.
 *
 * Es TIPOGRAFICA y no una imagen a proposito: se repinta sola con el tema
 * —«GYM» usa text-chalk, que es casi negro en claro y casi blanco en oscuro,
 * igual que el plata del logotipo cambia segun el fondo— y no pesa ninguna
 * peticion en cada carga.
 *
 * PARA PONER EL LOGOTIPO DE VERDAD: deja el PNG en public/img/progym.png y
 * cambia el interior por <img src="/img/progym.png" alt="PRO GYM" className="h-5 w-auto" />.
 * Hacen falta las dos variantes (la clara y la oscura) o el isotipo gris se
 * pierde sobre uno de los dos fondos.
 */
function Marca({ className = '' }) {
    return (
        <span className={`flex items-baseline gap-1.5 ${className}`}>
            <span className="text-sm font-bold tracking-tight uppercase">
                <span className="text-volt">PRO</span>
                <span className="text-chalk">GYM</span>
            </span>
            {/* «panel» se queda: distingue de un vistazo el panel del sitio. */}
            <span className="apoyo text-fog">panel</span>
        </span>
    );
}

function Enlace({ seccion, url, onIr }) {
    const activa = esActiva(seccion, url);
    const { Icono } = seccion;

    // Las secciones que siguen en Blade se visitan con una carga normal: un
    // <Link> de Inertia pediria esa ruta como pagina suya y no lo es.
    const Componente = seccion.externa ? 'a' : Link;

    return (
        <Componente
            href={seccion.href}
            onClick={onIr}
            aria-current={activa ? 'page' : undefined}
            className={`relative flex items-center gap-2 rounded-control py-1.5 pr-2 pl-3 text-sm transition-colors ${
                activa
                    ? // El marcador de 2 px senala sin gritar: no hace falta
                      // rellenar la fila entera de color.
                      'bg-surface-2 font-medium text-chalk before:absolute before:inset-y-1 before:left-0 before:w-0.5 before:rounded-full before:bg-volt'
                    : 'text-fog hover:bg-surface-2 hover:text-chalk'
            }`}
        >
            <Icono className="size-4 shrink-0" aria-hidden="true" />
            {seccion.etiqueta}
        </Componente>
    );
}

/**
 * El arbol de navegacion, uno solo. Lo pintan el carril de escritorio y el
 * cajon de movil: si fueran dos copias, la seccion que se anadiera manana
 * aparecería en una y no en la otra.
 */
function Arbol({ url, auth, onIr, sinCaja = false }) {
    const visibles = (secciones) =>
        secciones.filter((s) => (! s.permiso || puede(auth, s.permiso)) && ! (s.caja && sinCaja));

    // Un grupo entero puede quedarse sin secciones —recepcion no ve nada de
    // Configuracion—, y en ese caso tampoco se pinta su rotulo: un titulo
    // suelto sobre el vacio parece que algo no cargo.
    const grupos = GRUPOS.map((g) => ({ ...g, secciones: visibles(g.secciones) })).filter(
        (g) => g.secciones.length > 0,
    );

    const configuracion = visibles(CONFIGURACION);

    return (
        <nav className="flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto px-2 py-3">
            {grupos.map((grupo) => (
                <div key={grupo.titulo ?? grupo.secciones[0].href} className="space-y-0.5">
                    {grupo.titulo ? <p className="rotulo px-3 pb-1">{grupo.titulo}</p> : null}
                    {grupo.secciones.map((seccion) => (
                        <Enlace key={seccion.href} seccion={seccion} url={url} onIr={onIr} />
                    ))}
                </div>
            ))}

            {configuracion.length > 0 ? (
                <div className="mt-auto space-y-0.5 border-t border-line pt-3">
                    <p className="rotulo px-3 pb-1">Configuración</p>
                    {configuracion.map((seccion) => (
                        <Enlace key={seccion.href} seccion={seccion} url={url} onIr={onIr} />
                    ))}
                </div>
            ) : null}
        </nav>
    );
}

/**
 * Menu de usuario: tema y salida.
 *
 * «Salir» deja de ser un boton siempre visible con el mismo peso que la
 * navegacion. Salir se hace una vez al dia; Clientes, cuarenta.
 */
function MenuDeUsuario({ correo, alineacion = 'start', lado = 'top', className = '' }) {
    const preferencia = usarPreferenciaDeTema();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger
                className={`flex max-w-full items-center gap-2 rounded-control px-2 py-1.5 text-sm text-fog transition-colors hover:bg-surface-2 hover:text-chalk data-[state=open]:bg-surface-2 data-[state=open]:text-chalk ${className}`}
            >
                <span className="grid size-5 shrink-0 place-content-center rounded-full bg-surface-2 text-[0.625rem] font-semibold text-chalk uppercase">
                    {(correo ?? '?').slice(0, 1)}
                </span>
                <span className="truncate">{correo}</span>
            </DropdownMenuTrigger>

            <DropdownMenuContent align={alineacion} side={lado} className="w-56">
                <DropdownMenuLabel>Tema</DropdownMenuLabel>
                <DropdownMenuRadioGroup value={preferencia} onValueChange={fijarTema}>
                    {TEMAS.map(({ valor, etiqueta, Icono }) => (
                        <DropdownMenuRadioItem key={valor} value={valor}>
                            <Icono className="size-4" aria-hidden="true" />
                            {etiqueta}
                        </DropdownMenuRadioItem>
                    ))}
                </DropdownMenuRadioGroup>

                <DropdownMenuSeparator />

                <DropdownMenuItem onSelect={() => router.post('/logout')}>
                    <LogOutIcon aria-hidden="true" />
                    Salir
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

/**
 * Los avisos de accion completada se van solos; los que exigen leer se quedan.
 *
 * Vive en el Layout y no en cada pantalla A PROPOSITO: en las vistas Blade cada
 * una tenia que acordarse de pintar su bloque y 9 de 14 se olvidaban, asi que
 * las confirmaciones y los errores se perdian sin que nadie los viera.
 */
function Aviso({ flash }) {
    const [visible, setVisible] = useState(true);
    const [enPausa, setEnPausa] = useState(false);

    const exito = flash?.success;
    const error = flash?.error;
    const aviso = flash?.warning;
    const dato = flash?.info;

    // Lo bueno se va solo a los 5 s; un error o un aviso se quedan hasta que
    // se cierran, porque hay que leerlos. Con el mouse encima no corre el reloj.
    const seVaSolo = Boolean(exito || dato) && ! error && ! aviso;

    useEffect(() => {
        setVisible(true);
        setEnPausa(false);
    }, [exito, error, aviso, dato]);

    useEffect(() => {
        if (! seVaSolo || ! visible || enPausa) {
            return undefined;
        }

        const temporizador = setTimeout(() => setVisible(false), 5000);

        return () => clearTimeout(temporizador);
    }, [seVaSolo, visible, enPausa, exito, dato]);

    const mensaje = error ?? aviso ?? exito ?? dato;

    if (! visible || ! mensaje) {
        return null;
    }

    const tono = error
        ? { raya: 'bg-danger', icono: 'text-danger', Icono: XCircleIcon, titulo: 'No se pudo' }
        : aviso
          ? { raya: 'bg-warn', icono: 'text-warn', Icono: AlertTriangleIcon, titulo: 'Ojo' }
          : exito
            ? { raya: 'bg-ok', icono: 'text-ok', Icono: CheckCircle2Icon, titulo: 'Listo' }
            : { raya: 'bg-info', icono: 'text-info', Icono: InfoIcon, titulo: null };

    return (
        /*
         * FLOTANTE, arriba a la derecha, y no al principio de la pagina: casi todo
         * lo que se hace en una lista larga conserva el scroll, y el aviso salia
         * fuera de la vista. Fondo macizo, raya de color a la izquierda, icono y
         * el texto en el color normal: el color solo dice el tipo, y lo que se
         * lee es el mensaje.
         */
        <div
            role={error ? 'alert' : 'status'}
            onMouseEnter={() => setEnPausa(true)}
            onMouseLeave={() => setEnPausa(false)}
            className="aviso-flotante fixed right-3 top-[6.25rem] z-50 w-[calc(100%-1.5rem)] max-w-sm overflow-hidden rounded-panel border border-line bg-raise shadow-overlay sm:w-auto sm:min-w-[19rem] lg:top-4"
        >
            <div className="flex items-start gap-3 py-3 pl-3.5 pr-2.5">
                <span className={`absolute inset-y-0 left-0 w-[3px] ${tono.raya}`} aria-hidden="true" />
                <tono.Icono className={`mt-0.5 size-5 shrink-0 ${tono.icono}`} aria-hidden="true" />

                <div className="min-w-0 flex-1 text-sm text-chalk">
                    {tono.titulo ? <p className="font-medium leading-5">{tono.titulo}</p> : null}
                    <p className={tono.titulo ? 'apoyo mt-0.5 text-fog' : 'leading-5'}>{mensaje}</p>
                </div>

                <button
                    type="button"
                    onClick={() => setVisible(false)}
                    aria-label="Cerrar el aviso"
                    className="-m-1 shrink-0 rounded-control p-1 text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                >
                    <XIcon className="size-4" aria-hidden="true" />
                </button>
            </div>

            {/* La barrita que se consume dice cuanto le queda al aviso; con el mouse
                encima se detiene, igual que el reloj. */}
            {seVaSolo ? (
                <span
                    aria-hidden="true"
                    className={`aviso-cuenta block h-0.5 ${tono.raya} opacity-40`}
                    style={{ animationPlayState: enPausa ? 'paused' : 'running' }}
                />
            ) : null}
        </div>
    );
}

export default function Layout({ children }) {
    const { props, url } = usePage();
    const { auth, flash } = props;
    const [cajon, setCajon] = useState(false);

    // Al navegar dentro del cajon, Inertia no desmonta el Layout: hay que
    // cerrarlo a mano o el usuario aterriza en la pantalla nueva con el cajon
    // todavia encima.
    const cerrarCajon = () => setCajon(false);

    return (
        <TooltipProvider>
            {/* `forzado`: cuando se pidio tapar los importes, las cifras se
                quedan tapadas y el ojo de la barra desaparece. */}
            <ProveedorPrivado forzado={Boolean(props.privado?.sin_montos)}>
            <div className="min-h-dvh bg-page">
                {/* CARRIL FIJO en escritorio. Carril y no barra horizontal:
                    son once secciones y creceran; ademas deja a las tablas el
                    alto completo, que es lo que se viene a mirar. */}
                <aside className="fixed inset-y-0 left-0 z-30 hidden w-56 flex-col border-r border-line bg-surface lg:flex">
                    <div className="flex h-12 shrink-0 items-center border-b border-line px-4">
                        <Link href="/panel" className="rounded-control">
                            <Marca />
                        </Link>
                    </div>

                    {/* Buscar a un socio desde cualquier pantalla: en el mesón todo
                        empieza por un nombre, y antes solo se podía desde Resumen. */}
                    {puede(auth, 'clientes.ver') ? (
                        <div className="shrink-0 border-b border-line p-2">
                            <BuscadorGlobal />
                        </div>
                    ) : null}

                    <Arbol url={url} auth={auth} sinCaja={Boolean(props.privado?.sin_caja)} />

                    {/* El ojito, al lado del usuario: se busca abajo a la
                        izquierda, donde estan las cosas de «yo», no arriba
                        entre lo que se esta mirando. */}
                    <div className="flex shrink-0 items-center gap-1 border-t border-line p-2">
                        <MenuDeUsuario correo={auth?.user?.email} className="min-w-0 flex-1" />
                        <BotonPrivado />
                    </div>
                </aside>

                {/* CABECERA de 48 px en movil y tableta. Tres cosas y ninguna
                    puede envolver: hamburguesa, marca y usuario. */}
                <header className="sticky top-0 z-20 flex h-12 items-center gap-2 border-b border-line bg-surface/95 px-2 backdrop-blur lg:hidden">
                    <Sheet open={cajon} onOpenChange={setCajon}>
                        <SheetTrigger
                            aria-label="Abrir la navegación"
                            className="rounded-control p-1.5 text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                        >
                            <MenuIcon className="size-5" aria-hidden="true" />
                        </SheetTrigger>

                        <SheetContent aria-describedby={undefined}>
                            <SheetHeader>
                                <SheetTitle asChild>
                                    <Link href="/panel" onClick={cerrarCajon}>
                                        <Marca />
                                    </Link>
                                </SheetTitle>
                            </SheetHeader>

                            <Arbol url={url} auth={auth} sinCaja={Boolean(props.privado?.sin_caja)} onIr={cerrarCajon} />

                            <div className="shrink-0 border-t border-line p-2">
                                <MenuDeUsuario correo={auth?.user?.email} className="w-full" />
                            </div>
                        </SheetContent>
                    </Sheet>

                    <Link href="/panel" className="mx-auto rounded-control">
                        <Marca />
                    </Link>

                    <BotonPrivado />

                    <MenuDeUsuario
                        correo={auth?.user?.email}
                        alineacion="end"
                        lado="bottom"
                        className="max-w-[9rem]"
                    />
                </header>

                {puede(auth, 'clientes.ver') ? (
                    <div className="sticky top-12 z-10 border-b border-line bg-surface/95 px-2 py-1.5 backdrop-blur lg:hidden">
                        <BuscadorGlobal />
                    </div>
                ) : null}

                <div className="lg:pl-56">
                    {/* 1600 px: una tabla de nueve columnas no se lee mejor por
                        estrecharla. */}
                    <main className="mx-auto max-w-[1600px] px-3 py-4 sm:px-4 sm:py-6">
                        <Aviso flash={flash} />
                        {children}
                    </main>
                </div>
            </div>
            </ProveedorPrivado>
        </TooltipProvider>
    );
}
