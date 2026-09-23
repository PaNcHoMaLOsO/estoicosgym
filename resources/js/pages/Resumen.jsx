import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import {
    ClipboardPlusIcon,
    CreditCardIcon,
    MailIcon,
    MessageCircleIcon,
    CakeIcon,
    NotebookPenIcon,
    PhoneOffIcon,
    SearchIcon,
    TicketIcon,
    UserPlusIcon,
    MailWarningIcon,
} from 'lucide-react';

import { ApuntarFiado, Notas } from '@/components/Libreta';
import MesonTienda from '@/components/MesonTienda';
import Retrato from '@/components/Retrato';
import { Panel, pesos } from '@/components/Tablero';
import { celularLegible, whatsapp as enlaceWhatsapp } from '@/lib/contacto';
import { puede } from '@/lib/permisos';
import ModalDePagina from '@/components/ModalDePagina';
import { Reservado } from '@/Privado';

/**
 * Portada del panel: lo que hay que HACER hoy.
 *
 * Es para quien atiende el mesón. Arriba, buscar al socio que tiene delante y
 * los atajos a lo que se hace con él. Después, lo que pide levantar el
 * teléfono: a quién le vence esta semana y quién se fue sin renovar.
 *
 * SIN PLATA: la caja, lo que se debe y lo fiado están en Caja, que solo abre
 * quien ve los informes.
 */

/** Lo que se hace con el socio delante. Cada uno sale solo a quien puede hacerlo. */
// `conCabecera`: en Fiado y Canje la cabecera de la pantalla trae botones propios
// (Anotar), así que se conserva; en las otras tres solo repetiría el título.
const ATAJOS = [
    { href: '/panel/clientes/crear', etiqueta: 'Nuevo socio', Icono: UserPlusIcon, permiso: 'clientes.crear' },
    { href: '/panel/pagos/cobrar', etiqueta: 'Cobrar', Icono: CreditCardIcon, permiso: 'pagos.crear' },
    { href: '/panel/inscripciones/crear', etiqueta: 'Nueva inscripción', Icono: ClipboardPlusIcon, permiso: 'inscripciones.crear' },
    { href: '/panel/fiados', etiqueta: 'Anotar fiado', Icono: NotebookPenIcon, permiso: 'clientes.ver', conCabecera: true },
    { href: '/panel/canje', etiqueta: 'Entrada por canje', Icono: TicketIcon, permiso: 'clientes.crear', conCabecera: true },
];

// El enlace de WhatsApp sale de lib/contacto, el mismo que usan las listas de
// Clientes: con una copia aquí, un arreglo en una no llegaba a la otra.
const whatsapp = enlaceWhatsapp;

/** Buscar al socio que se tiene delante: lo primero que se hace al atender. */
function Buscador() {
    const [texto, setTexto] = useState('');

    function buscar(evento) {
        evento.preventDefault();

        const busqueda = texto.trim();

        if (busqueda) {
            router.get('/panel/clientes', { buscar: busqueda });
        }
    }

    return (
        <form onSubmit={buscar} role="search" className="flex gap-2">
            <label className="relative flex-1">
                <span className="sr-only">Buscar socio</span>
                <SearchIcon className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-fog" aria-hidden="true" />
                <input
                    type="search"
                    value={texto}
                    onChange={(e) => setTexto(e.target.value)}
                    placeholder="Buscar socio por nombre o RUT"
                    className="w-full rounded-control border border-line bg-surface py-2.5 pl-9 pr-3 text-sm text-chalk placeholder:text-fog focus:border-line-strong focus:outline-none"
                />
            </label>
            <button
                type="submit"
                className="rounded-control bg-volt px-4 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
            >
                Buscar
            </button>
        </form>
    );
}

/**
 * Escribirle, en un clic.
 *
 * WHATSAPP ES EL BOTÓN, no un icono chico al lado del número. Avisar es lo que
 * se hace con estas listas —«te vence el viernes», «te echamos de menos»— y
 * antes había que fijarse en un icono de 14 píxeles para darse cuenta de que se
 * podía. El correo queda detrás, en pequeño: en el mesón se escribe por
 * WhatsApp, el correo es para lo formal.
 */
function Contacto({ celular, email, nombre, compacto = false }) {
    if (! celular && ! email) {
        // Sin correo ni celular no hay a quién avisar: hay que buscarlo a mano.
        return (
            <span
                className="inline-flex items-center gap-1 whitespace-nowrap text-warn"
                title={compacto ? 'Sin correo ni celular: hay que avisarle en persona' : undefined}
            >
                <PhoneOffIcon className="size-3.5" aria-hidden="true" />
                {compacto ? <span className="sr-only">Sin contacto</span> : 'Sin contacto'}
            </span>
        );
    }

    // En las listas del resumen el WhatsApp es solo el icono: con «WhatsApp»
    // escrito ocho veces seguidas, la palabra deja de decir nada.
    if (compacto) {
        return (
            <span className="inline-flex shrink-0 items-center gap-1">
                {celular ? (
                    <a
                        href={whatsapp(celular)}
                        target="progym-whatsapp"
                        rel="noopener"
                        title={`Escribirle por WhatsApp a ${celularLegible(celular)}`}
                        aria-label={`Escribirle por WhatsApp a ${nombre ?? celularLegible(celular)}`}
                        className="inline-flex size-7 items-center justify-center rounded-control text-[#25D366] transition-colors hover:bg-[#25D366]/15"
                    >
                        <MessageCircleIcon className="size-4" aria-hidden="true" />
                    </a>
                ) : null}
                {email ? (
                    <a
                        href={`mailto:${email}`}
                        aria-label={`Escribir a ${email}`}
                        title={email}
                        className="inline-flex size-7 items-center justify-center rounded-control text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                    >
                        <MailIcon className="size-4" aria-hidden="true" />
                    </a>
                ) : null}
            </span>
        );
    }

    return (
        <span className="inline-flex items-center gap-2">
            {celular ? (
                <a
                    href={whatsapp(celular)}
                    // SIEMPRE A LA MISMA VENTANA: sin el nombre, escribirle a
                    // cinco socios dejaba cinco pestañas de WhatsApp abiertas.
                    target="progym-whatsapp"
                    rel="noopener"
                    title={`Escribirle por WhatsApp a ${celularLegible(celular)}`}
                    aria-label={`Escribirle por WhatsApp a ${nombre ?? celularLegible(celular)}`}
                    className="inline-flex items-center gap-1.5 whitespace-nowrap rounded-control border border-[#25D366]/40 bg-[#25D366]/10 px-2.5 py-1 text-sm font-medium text-[#25D366] transition-colors hover:bg-[#25D366]/20"
                >
                    <MessageCircleIcon className="size-3.5" aria-hidden="true" />
                    WhatsApp
                </a>
            ) : null}
            {email ? (
                <a
                    href={`mailto:${email}`}
                    aria-label={`Escribir a ${email}`}
                    title={email}
                    className="text-fog transition-colors hover:text-chalk"
                >
                    <MailIcon className="size-4" aria-hidden="true" />
                </a>
            ) : null}
        </span>
    );
}

/**
 * Lo que alguien debe del mesón, y el formulario para apuntar otra cosa.
 *
 * SE APUNTA AQUÍ MISMO. La barra de proteína se fía en diez segundos, con la
 * persona todavía delante: mandar a otra pantalla a anotarla —y volver— es más
 * trabajo que el fiado, y lo que pasa entonces es que no se anota.
 */
function Fiado({ fiado }) {
    const [anotando, setAnotando] = useState(false);

    const apuntar = (
        <button
            type="button"
            onClick={() => setAnotando((a) => ! a)}
            className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
        >
            {anotando ? 'Cerrar' : '+ Anotar'}
        </button>
    );

    if (fiado.personas === 0) {
        return (
            <Panel titulo="Fiado del mesón" enlace={apuntar}>
                {anotando ? <ApuntarFiado alTerminar={() => setAnotando(false)} /> : null}
                <p className="apoyo text-fog">Nadie debe nada. Lo que se anote aparecerá aquí.</p>
            </Panel>
        );
    }

    return (
        <Panel
            titulo={<ConCuenta texto="Fiado del mesón" cuenta={fiado.personas} tono="text-warn" />}
            descripcion={<>Deben <Reservado ancho="w-14">{pesos.format(fiado.total)}</Reservado> en total</>}
            enlace={
                <span className="flex shrink-0 items-center gap-3">
                    {apuntar}
                    <Link href="/panel/fiados" className="apoyo text-fog transition-colors hover:text-chalk">
                        Cobrar
                    </Link>
                </span>
            }
        >
            {anotando ? <ApuntarFiado alTerminar={() => setAnotando(false)} /> : null}
            <ul className="space-y-2">
                {fiado.cuentas.map((c) => (
                    <li key={`${c.socio_uuid ?? c.quien}`} className="flex items-center justify-between gap-3">
                        <div className="flex min-w-0 items-center gap-2.5">
                            <Retrato nombre={c.quien} foto={c.foto} tamano="sm" />
                            <div className="min-w-0">
                                {c.socio_uuid ? (
                                    <Link href={`/panel/clientes/${c.socio_uuid}`} className="truncate text-sm text-chalk hover:underline">
                                        {c.quien}
                                    </Link>
                                ) : (
                                    <span className="truncate text-sm text-chalk">{c.quien}</span>
                                )}
                                {/* Los días que lleva: una cuenta de tres semanas
                                    no se cobra sola, y conviene que se note. */}
                                <p className={`apoyo ${c.dias >= 14 ? 'text-warn' : 'text-fog'}`}>
                                    {c.dias === 0 ? 'de hoy' : c.dias === 1 ? 'de ayer' : `hace ${c.dias} días`}
                                    {c.cuantas > 1 ? ` · ${c.cuantas} cosas` : ''}
                                </p>
                            </div>
                        </div>
                        <span className="shrink-0 text-sm font-medium tabular-nums text-chalk">
                            <Reservado ancho="w-12">{pesos.format(c.total)}</Reservado>
                        </span>
                    </li>
                ))}
            </ul>
        </Panel>
    );
}

/**
 * Quién cumple años esta semana.
 *
 * LO MÁS BARATO QUE PUEDE HACER UN GIMNASIO por alguien que paga todos los
 * meses es saludarlo al entrar. La fecha se pide al inscribirse y no se usaba
 * para nada; aquí sale con la cara, para reconocer a la persona antes de que
 * llegue al mesón. Al de hoy se le puede escribir de una.
 *
 * Solo aparece si hay alguien: un panel que casi siempre dice «nadie» acaba
 * siendo una línea más que saltarse.
 */
function Cumpleanos({ gente }) {
    if (gente.length === 0) {
        return null;
    }

    const hoy = gente.filter((g) => g.dias === 0).length;

    return (
        <Panel
            titulo={
                <span className="inline-flex items-center gap-2">
                    <CakeIcon className="size-3.5 text-fog" aria-hidden="true" />
                    {hoy > 0 ? 'Cumple hoy' : 'Cumpleaños'}
                </span>
            }
            descripcion={hoy > 0 ? null : 'Esta semana'}
        >
            <ul className="space-y-2">
                {gente.slice(0, 5).map((g) => (
                    <li key={g.uuid} className="flex items-center justify-between gap-2">
                        <Link href={`/panel/clientes/${g.uuid}`} className="flex min-w-0 items-center gap-2.5">
                            <Retrato nombre={g.socio} foto={g.foto} tamano="sm" />
                            <span className="min-w-0">
                                <span className="block truncate text-sm text-chalk">{g.socio}</span>
                                <span className={`apoyo block ${g.dias === 0 ? 'text-volt' : 'text-fog'}`}>
                                    {g.dias === 0 ? 'hoy' : g.dias === 1 ? 'mañana' : `el ${g.fecha}`} · {g.edad} años
                                </span>
                            </span>
                        </Link>
                        {/* Solo al de hoy: saludar con tres días de antelación
                            es raro, y el botón en todos llenaba la columna. */}
                        {g.dias === 0 ? <Contacto celular={g.celular} email={g.email} nombre={g.socio} /> : null}
                    </li>
                ))}
            </ul>
        </Panel>
    );
}

/** Cuanto más cerca del vencimiento, más urgente la llamada. */
function Faltan({ dias }) {
    if (dias <= 0) {
        return <span className="font-medium text-danger">Hoy</span>;
    }

    return <span className={`font-medium ${dias <= 3 ? 'text-danger' : 'text-warn'}`}>{dias} d</span>;
}

/** Alto de cada lista: unas cinco personas; el resto se ve bajando dentro de la lista. */
const ALTO_LISTA = 'max-h-[13.5rem]';

/**
 * Una lista de socios a los que llamar: UNA LÍNEA POR PERSONA.
 *
 * Era una tabla de cinco columnas con una fila alta por socio: tres listas así
 * ocupaban tres pantallas, y un resumen que hay que recorrer no resume nada.
 * Aquí cada persona es una línea (cara, nombre, cuándo, cómo escribirle); la
 * lista mide lo de cinco personas y el resto se ve bajando DENTRO de ella, sin
 * que la página crezca. El plan y la fecha exacta van
 * en letra chica bajo el nombre: se leen si hacen falta y no ocupan columna.
 */
function Llamar({ filas, cuanto, vacia }) {
    if (filas.length === 0) {
        return <p className="apoyo py-2 text-fog">{vacia}</p>;
    }

    return (
        <>
            <ul className={`-mx-1 divide-y divide-line overflow-y-auto overscroll-contain pr-1 ${ALTO_LISTA}`}>
                {filas.map((f) => (
                    <li key={f.uuid} className="flex items-center gap-2.5 px-1 py-1.5">
                        <Retrato nombre={f.socio} foto={f.foto} tamano="sm" ampliable />

                        <div className="min-w-0 flex-1 leading-tight">
                            <Link
                                href={f.socio_uuid ? `/panel/clientes/${f.socio_uuid}` : `/panel/inscripciones/${f.uuid}`}
                                className="block truncate text-sm font-medium text-chalk hover:underline"
                            >
                                {f.socio}
                            </Link>
                            <p className="apoyo truncate text-fog">
                                {f.membresia ?? 'Sin plan'} · {f.fecha}
                            </p>
                        </div>

                        <span className="shrink-0 text-sm">
                            {cuanto === 'Faltan' ? <Faltan dias={f.dias} /> : <span className="tabular-nums text-fog">{cuanto === 'Hace' ? 'hace ' : 'en '}{f.dias} d</span>}
                        </span>

                        <Contacto celular={f.celular} email={f.email} nombre={f.socio} compacto />
                    </li>
                ))}
            </ul>

        </>
    );
}

/** Un número del gimnasio dentro de la barra de abajo: rótulo arriba, cifra debajo. */
function Numero({ etiqueta, valor }) {
    return (
        <div className="px-3 py-2.5">
            <p className="rotulo">{etiqueta}</p>
            <p className="mt-0.5 text-xl font-semibold tabular-nums text-chalk">{valor}</p>
        </div>
    );
}

/** El título de un panel con su número al lado: «Vencen esta semana 5». */
function ConCuenta({ texto, cuenta, tono }) {
    return (
        <span className="inline-flex items-baseline gap-2">
            {texto}
            <span className={`text-base font-semibold tabular-nums ${cuenta > 0 ? tono : 'text-fog'}`}>{cuenta}</span>
        </span>
    );
}

export default function Resumen({
    cifras,
    notas,
    porVencer,
    sinRenovar,
    // Null cuando se pidio esconder quien debe: entonces el panel no se pinta.
    fiado = null,
    cumpleanos = [],
    porEmpezar = [],
    avisosFallidos = 0,
}) {
    const { auth } = usePage().props;
    const atajos = ATAJOS.filter((a) => puede(auth, a.permiso));
    const [enVentana, setEnVentana] = useState(null);

    return (
        <>
            <Head title="Resumen" />

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">Resumen</h1>
                <p className="apoyo text-fog">Qué hay que atender hoy</p>
            </header>

            <div className="mb-4">
                <Buscador />
            </div>

            {atajos.length > 0 ? (
                <nav aria-label="Accesos rápidos" className="mb-5 grid grid-cols-2 gap-2 lg:grid-cols-5">
                    {/* SE ABREN EN VENTANA, encima del Resumen: se resuelve y se sigue
                        aquí, sin ir y volver. Siguen siendo enlaces de verdad, así que
                        con Ctrl+clic o el botón del medio se abre la pantalla entera. */}
                    {atajos.map((atajo) => (
                        <a
                            key={atajo.href}
                            href={atajo.href}
                            onClick={(e) => {
                                if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) {
                                    return;
                                }
                                e.preventDefault();
                                setEnVentana(atajo);
                            }}
                            className="flex items-center justify-center gap-2.5 rounded-panel border border-line bg-surface px-3 py-2.5 text-sm font-medium text-chalk transition-colors hover:border-line-strong hover:bg-surface-2"
                        >
                            <atajo.Icono className="size-4 text-fog" aria-hidden="true" />
                            {atajo.etiqueta}
                        </a>
                    ))}
                </nav>
            ) : null}

            {enVentana ? (
                <ModalDePagina
                    href={enVentana.href}
                    titulo={enVentana.etiqueta}
                    conCabecera={enVentana.conCabecera}
                    alCerrar={() => setEnVentana(null)}
                />
            ) : null}

            {/*
             * DOS COLUMNAS: a la izquierda lo que hay que HACER, a la derecha
             * lo que hay que TENER A LA VISTA.
             *
             * Todo iba en una sola columna y las notas quedaban enterradas bajo
             * las listas de llamar: en el mesón se anota en un papel al lado del
             * teclado justamente porque la pantalla no las tenía. Ahora la
             * columna de la derecha se queda fija al bajar, y las notas son lo
             * primero que hay en ella.
             */}
            {/* TODO A LA VISTA. Las tres listas van lado a lado en cuanto hay
                ancho, con cinco personas cada una: el resumen cabe en una
                pantalla y lo demás se despliega desde cada lista. */}
            <div className="grid items-start gap-4 lg:grid-cols-[1fr_19rem] xl:grid-cols-[1fr_21rem]">
                <div className="grid gap-3 2xl:grid-cols-2">
                    <Panel
                        titulo={<ConCuenta texto="Vencen esta semana" cuenta={cifras.vencen_semana} tono="text-warn" />}
                        enlace={
                            // El listado completo es un informe: solo se ofrece a
                            // quien lo puede abrir. A los demás les daría un error.
                            puede(auth, 'reportes.ver') ? (
                                <Link href="/panel/reportes/por-vencer" className="apoyo shrink-0 text-fog transition-colors hover:text-chalk">
                                    Ver todas
                                </Link>
                            ) : null
                        }
                    >
                        <Llamar filas={porVencer} cuanto="Faltan" vacia="Ninguna membresía vence esta semana." />
                    </Panel>

                    <Panel
                        titulo={<ConCuenta texto="Sin renovar" cuenta={cifras.sin_renovar} tono="text-danger" />}
                    >
                        <Llamar filas={sinRenovar} cuanto="Hace" vacia="Nadie se fue sin renovar en estos días." />
                    </Panel>

                    {/* Solo cuando hay alguien: un panel vacío aquí sería ruido casi siempre. */}
                    {porEmpezar.length > 0 ? (
                        <Panel
                            titulo={<ConCuenta texto="Empiezan pronto" cuenta={porEmpezar.length} tono="text-chalk" />}
                        >
                            <Llamar filas={porEmpezar} cuanto="En" vacia="" />
                        </Panel>
                    ) : null}
                </div>

                <div className="flex flex-col gap-3 lg:sticky lg:top-4">
                    {/* LAS NOTAS, PRIMERO. Es el papel del mesón: lo que hay que
                        acordarse de hacer hoy y lo que dejó dicho el turno anterior. */}
                    <Notas notas={notas} />

                    {fiado ? <Fiado fiado={fiado} /> : null}

                    <Cumpleanos gente={cumpleanos} />

                    {/* CÓMO ESTÁ EL GIMNASIO, en una línea. Antes eran tres cajas
                        altas, y le daban el tamaño de un panel entero a algo que
                        solo se mira de reojo: no se hace nada con estos números. */}
                    <div className="grid grid-cols-3 divide-x divide-line rounded-panel border border-line bg-surface">
                        <Numero etiqueta="Socios" valor={<Reservado ancho="w-8">{cifras.socios}</Reservado>} />
                        <Numero etiqueta="Vigentes" valor={<Reservado ancho="w-8">{cifras.al_dia}</Reservado>} />
                        <Numero etiqueta="Pausadas" valor={cifras.pausadas} />
                    </div>

                    {/* El sitio del mostrador, todavía sin catálogo: al final de
                        la columna, que es donde cabe sin estorbar a lo de hoy. */}
                    <MesonTienda />
                </div>
            </div>

        </>
    );
}
