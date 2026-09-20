import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import {
    ClipboardPlusIcon,
    CreditCardIcon,
    MailIcon,
    MessageCircleIcon,
    NotebookPenIcon,
    PhoneOffIcon,
    SearchIcon,
    TicketIcon,
    UserPlusIcon,
    MailWarningIcon,
} from 'lucide-react';

import { Notas } from '@/components/Libreta';
import { Celda, Fila, Tabla } from '@/components/Tabla';
import { Cifra, Panel } from '@/components/Tablero';
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

/** El enlace de WhatsApp para un celular chileno escrito de cualquier forma. */
function whatsapp(celular) {
    const digitos = String(celular).replace(/\D/g, '');
    const numero = digitos.length === 9 && digitos.startsWith('9') ? `56${digitos}` : digitos;

    return `https://wa.me/${numero}`;
}

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

/** Cómo avisarle, con un clic: su WhatsApp y su correo. */
function Contacto({ celular, email }) {
    if (!celular && !email) {
        // Sin correo ni celular no hay a quién avisar: hay que buscarlo a mano.
        return (
            <span className="inline-flex items-center gap-1 text-warn">
                <PhoneOffIcon className="size-3.5" aria-hidden="true" />
                Sin contacto
            </span>
        );
    }

    return (
        <span className="inline-flex items-center gap-3">
            {celular ? (
                <a
                    href={whatsapp(celular)}
                    target="_blank"
                    rel="noopener"
                    className="inline-flex items-center gap-1 text-chalk hover:underline"
                >
                    <MessageCircleIcon className="size-3.5 text-[#25D366]" aria-hidden="true" />
                    {celular}
                </a>
            ) : null}
            {email ? (
                <a
                    href={`mailto:${email}`}
                    aria-label={`Escribir a ${email}`}
                    title={email}
                    className="text-fog transition-colors hover:text-chalk"
                >
                    <MailIcon className="size-3.5" aria-hidden="true" />
                </a>
            ) : null}
        </span>
    );
}

/** Cuanto más cerca del vencimiento, más urgente la llamada. */
function Faltan({ dias }) {
    if (dias <= 0) {
        return <span className="font-medium text-danger">Hoy</span>;
    }

    return <span className={`font-medium ${dias <= 3 ? 'text-danger' : 'text-warn'}`}>{dias} d</span>;
}

/** Una lista de socios a los que llamar. */
function Llamar({ filas, fecha, cuanto, vacia }) {
    return (
        <Tabla columnas={['Socio', 'Plan', fecha, cuanto, 'Contacto']} vacia={filas.length === 0} mensajeVacio={vacia}>
            {filas.map((f) => (
                <Fila key={f.uuid}>
                    <Celda className="font-medium text-chalk">
                        <Link
                            href={f.socio_uuid ? `/panel/clientes/${f.socio_uuid}` : `/panel/inscripciones/${f.uuid}`}
                            className="hover:underline"
                        >
                            {f.socio}
                        </Link>
                    </Celda>
                    <Celda>{f.membresia ?? 'Sin plan'}</Celda>
                    <Celda className="tabular-nums">{f.fecha}</Celda>
                    <Celda>{cuanto === 'Faltan' ? <Faltan dias={f.dias} /> : <span className="tabular-nums">{f.dias} d</span>}</Celda>
                    <Celda>
                        <Contacto celular={f.celular} email={f.email} />
                    </Celda>
                </Fila>
            ))}
        </Tabla>
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

export default function Resumen({ cifras, notas, porVencer, sinRenovar, porEmpezar = [], avisosFallidos = 0 }) {
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
                            className="flex items-center gap-2.5 rounded-panel border border-line bg-surface px-4 py-3 text-sm font-medium text-chalk transition-colors hover:border-line-strong"
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

            {/* CADA COSA SE NOMBRA UNA VEZ. «Vencen esta semana» y «Sin renovar» salían
                dos veces: como cifra aquí arriba y como título del panel de abajo con
                la misma gente. Ahora el número va en el título de su panel, y aquí
                quedan solo las cifras que no tienen lista propia. */}
            <div className="mb-4 grid gap-3 sm:grid-cols-3">
                <Cifra etiqueta="Socios activos" valor={<Reservado>{cifras.socios}</Reservado>} />
                <Cifra etiqueta="Membresías vigentes" valor={<Reservado>{cifras.al_dia}</Reservado>} />
                <Cifra etiqueta="Pausadas" valor={cifras.pausadas} />
            </div>

            {/* AVISOS QUE NO SALIERON. Solo aparece cuando hay alguno: en el mesón se
                da por avisado al socio, y si el correo falló nadie lo sabe. */}
            {avisosFallidos > 0 ? (
                <div role="status" className="mb-4 flex flex-wrap items-center gap-x-3 gap-y-2 rounded-panel border border-danger/40 bg-danger/5 px-4 py-3">
                    <MailWarningIcon className="size-4 shrink-0 text-danger" aria-hidden="true" />
                    <p className="min-w-0 flex-1 text-sm text-chalk">
                        {avisosFallidos === 1
                            ? 'Un aviso por correo no salió: ese socio no fue avisado.'
                            : `${avisosFallidos} avisos por correo no salieron: esos socios no fueron avisados.`}
                    </p>
                    {puede(auth, 'notificaciones.ver') ? (
                        <Link
                            href="/panel/notificaciones?estado=fallidas"
                            className="shrink-0 rounded-control border border-danger/50 px-3 py-1.5 text-sm font-medium text-danger transition-colors hover:bg-danger/10"
                        >
                            Ver y reintentar
                        </Link>
                    ) : null}
                </div>
            ) : null}

            <div className="mb-4">
                <Notas notas={notas} />
            </div>

            <div className="grid gap-3 xl:grid-cols-2">
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
                    <Llamar filas={porVencer} fecha="Vence" cuanto="Faltan" vacia="Ninguna membresía vence esta semana." />
                </Panel>

                <Panel titulo={<ConCuenta texto="Sin renovar" cuenta={cifras.sin_renovar} tono="text-danger" />} descripcion={`En los últimos ${cifras.dias_sin_renovar} días`}>
                    <Llamar filas={sinRenovar} fecha="Venció" cuanto="Hace" vacia="Nadie se fue sin renovar en estos días." />
                </Panel>

                {/* Solo cuando hay alguien: un panel vacío aquí sería ruido casi siempre. */}
                {porEmpezar.length > 0 ? (
                    <Panel titulo={<ConCuenta texto="Empiezan pronto" cuenta={porEmpezar.length} tono="text-chalk" />}>
                        <Llamar filas={porEmpezar} fecha="Empieza" cuanto="En" vacia="" />
                    </Panel>
                ) : null}
            </div>
        </>
    );
}
