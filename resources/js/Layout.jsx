import { Link, router, usePage } from '@inertiajs/react';
import {
    BadgePercentIcon,
    BellIcon,
    Building2Icon,
    ChartNoAxesColumnIcon,
    ClipboardListIcon,
    CreditCardIcon,
    HistoryIcon,
    LayoutDashboardIcon,
    LogOutIcon,
    MenuIcon,
    MonitorIcon,
    MoonIcon,
    ScrollTextIcon,
    SunIcon,
    UsersIcon,
    WalletIcon,
    XIcon,
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
import { puede } from '@/lib/permisos';
import { fijarTema, usarPreferenciaDeTema } from '@/lib/tema';

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
        titulo: 'Mesón',
        secciones: [
            { href: '/panel', etiqueta: 'Resumen', Icono: LayoutDashboardIcon, permiso: 'clientes.ver' },
            // Arriba del todo: es lo que se abre con el socio delante.
            { href: '/panel/clientes', etiqueta: 'Clientes', Icono: UsersIcon, permiso: 'clientes.ver' },
            { href: '/panel/inscripciones', etiqueta: 'Inscripciones', Icono: ClipboardListIcon, permiso: 'inscripciones.ver' },
            { href: '/panel/pagos', etiqueta: 'Pagos', Icono: CreditCardIcon, permiso: 'pagos.ver' },
        ],
    },
    {
        titulo: 'Seguimiento',
        secciones: [
            { href: '/panel/historial', etiqueta: 'Historial', Icono: HistoryIcon, permiso: 'historial.ver' },
            { href: '/panel/notificaciones', etiqueta: 'Notificaciones', Icono: BellIcon, permiso: 'notificaciones.ver' },
            {
                href: '/panel/reportes',
                etiqueta: 'Reportes',
                Icono: ChartNoAxesColumnIcon,
                permiso: 'reportes.ver',
            },
        ],
    },
];

/*
 * Configuracion va ANCLADA ABAJO y con sus cuatro entradas a la vista en vez de
 * escondidas tras un desplegable: son pocas y quien entra a crear una membresia
 * nueva no deberia tener que adivinar donde vive.
 */
const CONFIGURACION = [
    { href: '/panel/membresias', etiqueta: 'Membresías', Icono: ScrollTextIcon, permiso: 'configuracion.ver' },
    { href: '/panel/convenios', etiqueta: 'Convenios', Icono: Building2Icon, permiso: 'configuracion.ver' },
    { href: '/panel/metodos-pago', etiqueta: 'Métodos de pago', Icono: WalletIcon, permiso: 'configuracion.ver' },
    { href: '/panel/motivos-descuento', etiqueta: 'Motivos de descuento', Icono: BadgePercentIcon, permiso: 'configuracion.ver' },
];

const TEMAS = [
    { valor: 'sistema', etiqueta: 'Como el sistema', Icono: MonitorIcon },
    { valor: 'claro', etiqueta: 'Claro', Icono: SunIcon },
    { valor: 'oscuro', etiqueta: 'Oscuro', Icono: MoonIcon },
];

/** Activo tambien en las fichas: /panel/clientes/{uuid} marca "Clientes". */
function esActiva(href, url) {
    const ruta = url.split('?')[0].replace(/\/$/, '') || '/panel';

    return ruta === href || ruta.startsWith(`${href}/`);
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
    const activa = esActiva(seccion.href, url);
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
function Arbol({ url, auth, onIr }) {
    const visibles = (secciones) => secciones.filter((s) => !s.permiso || puede(auth, s.permiso));

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
                <div key={grupo.titulo} className="space-y-0.5">
                    <p className="rotulo px-3 pb-1">{grupo.titulo}</p>
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

    const exito = flash?.success;
    const error = flash?.error;
    const aviso = flash?.warning;
    const dato = flash?.info;

    useEffect(() => {
        setVisible(true);

        if (!exito && !dato) {
            return undefined;
        }

        const temporizador = setTimeout(() => setVisible(false), 4000);

        return () => clearTimeout(temporizador);
    }, [exito, error, aviso, dato]);

    const mensaje = error ?? aviso ?? exito ?? dato;

    if (!visible || !mensaje) {
        return null;
    }

    const tono = error
        ? { caja: 'border-danger/40 bg-danger/5 text-danger', glifo: '✕' }
        : aviso
          ? { caja: 'border-warn/40 bg-warn/5 text-warn', glifo: '!' }
          : exito
            ? { caja: 'border-ok/40 bg-ok/5 text-ok', glifo: '✓' }
            : { caja: 'border-info/40 bg-info/5 text-info', glifo: 'i' };

    return (
        <div
            role="status"
            className={`mb-4 flex items-start justify-between gap-3 rounded-panel border px-3 py-2 text-sm ${tono.caja}`}
        >
            <span className="flex items-start gap-1.5">
                {/* Glifo + texto + color, en ese orden: el color solo no basta. */}
                <span aria-hidden="true" className="font-semibold">
                    {tono.glifo}
                </span>
                {mensaje}
            </span>
            <button
                type="button"
                onClick={() => setVisible(false)}
                aria-label="Cerrar el aviso"
                className="-m-1 shrink-0 rounded-control p-1 opacity-70 transition-opacity hover:opacity-100"
            >
                <XIcon className="size-3.5" aria-hidden="true" />
            </button>
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

                    <Arbol url={url} auth={auth} />

                    <div className="shrink-0 border-t border-line p-2">
                        <MenuDeUsuario correo={auth?.user?.email} className="w-full" />
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

                            <Arbol url={url} auth={auth} onIr={cerrarCajon} />

                            <div className="shrink-0 border-t border-line p-2">
                                <MenuDeUsuario correo={auth?.user?.email} className="w-full" />
                            </div>
                        </SheetContent>
                    </Sheet>

                    <Link href="/panel" className="mx-auto rounded-control">
                        <Marca />
                    </Link>

                    <MenuDeUsuario
                        correo={auth?.user?.email}
                        alineacion="end"
                        lado="bottom"
                        className="max-w-[9rem]"
                    />
                </header>

                <div className="lg:pl-56">
                    {/* 1600 px: una tabla de nueve columnas no se lee mejor por
                        estrecharla. */}
                    <main className="mx-auto max-w-[1600px] px-3 py-4 sm:px-4 sm:py-6">
                        <Aviso flash={flash} />
                        {children}
                    </main>
                </div>
            </div>
        </TooltipProvider>
    );
}
