import { Head, Link, usePage } from '@inertiajs/react';

import Barras from '@/components/Barras';
import { Cifra, Panel, pesos } from '@/components/Tablero';
import { puede } from '@/lib/permisos';
import { Reservado } from '@/Privado';

/**
 * La caja del día: lo que entró hoy, para cuadrar el cajón al cerrar el turno.
 *
 * Es la que ve quien tiene «Ver la caja del día» y no la caja completa. Al
 * servidor no le llega nada del mes ni de deudas: esta pantalla no tiene qué
 * esconder, solo lo que se cuadra hoy. Las cifras se tapan con el mismo ojito
 * que el resto del panel, porque el cajón se cuenta con gente al otro lado.
 */

/** El color de cada fuente, el mismo que en la Caja completa. */
const COLOR = {
    membresias: 'bg-volt',
    talleres: 'bg-sky-400',
    meson: 'bg-amber-400',
};

/** Una fila de la lista de cobros. */
function Cobro({ hora, titulo, detalle, medio, quien, monto, href }) {
    const nombre = href ? (
        <Link href={href} className="text-chalk hover:underline">
            {titulo}
        </Link>
    ) : (
        <span className="text-chalk">{titulo}</span>
    );

    return (
        <li className="flex items-baseline justify-between gap-3 border-b border-line py-2 last:border-0">
            <div className="min-w-0">
                <p className="truncate text-sm">
                    {hora ? <span className="mr-2 tabular-nums text-fog">{hora}</span> : null}
                    {nombre}
                </p>
                <p className="apoyo truncate text-fog">{[detalle, medio, quien ? `cobró ${quien}` : null].filter(Boolean).join(' · ')}</p>
            </div>
            <p className="shrink-0 font-medium tabular-nums text-chalk">
                <Reservado ancho="w-16">{pesos.format(monto)}</Reservado>
            </p>
        </li>
    );
}

export default function CajaDelDia({ fecha, fuentes, entradas, porMetodo, cobros }) {
    const { auth } = usePage().props;
    // La ficha del pago se abre solo si se puede: un enlace a un 403 no sirve.
    const verPagos = puede(auth, 'pagos.ver');
    const plata = (valor) => <Reservado ancho="w-14">{pesos.format(valor)}</Reservado>;

    const nada = cobros.membresias.length + cobros.meson.length + cobros.talleres.length === 0;

    return (
        <>
            <Head title="Caja de hoy" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Caja de hoy</h1>
                <p className="apoyo text-fog first-letter:uppercase">{fecha}</p>
            </header>

            <section className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Cifra etiqueta="Entró hoy" valor={<Reservado ancho="w-20">{pesos.format(entradas.total)}</Reservado>} />
                {Object.entries(fuentes).map(([clave, nombre]) => (
                    <Cifra
                        key={clave}
                        etiqueta={
                            <span className="inline-flex items-center gap-1.5">
                                <span className={`size-2 rounded-full ${COLOR[clave]}`} aria-hidden="true" />
                                {nombre}
                            </span>
                        }
                        valor={<Reservado ancho="w-16">{pesos.format(entradas[clave])}</Reservado>}
                    />
                ))}
            </section>

            <div className="grid gap-3 lg:grid-cols-[1fr_2fr]">
                <Panel titulo="Con qué pagaron" descripcion="Membresías y mesón. Es lo que se cuadra contra el cajón y la cuenta.">
                    <Barras filas={porMetodo} formato={plata} vacio="Todavía no se cobra nada hoy." />
                </Panel>

                <Panel titulo="Lo que se cobró hoy" descripcion="Uno por uno, el último arriba.">
                    {nada ? (
                        <p className="apoyo text-fog">Todavía no se cobra nada hoy.</p>
                    ) : (
                        <div className="space-y-4">
                            {cobros.membresias.length > 0 ? (
                                <div>
                                    <h3 className="rotulo mb-1">{fuentes.membresias}</h3>
                                    <ul>
                                        {cobros.membresias.map((p) => (
                                            <Cobro
                                                key={p.uuid}
                                                hora={p.hora}
                                                titulo={p.socio}
                                                detalle={p.detalle}
                                                medio={p.medios.map((m) => m.nombre).join(' + ')}
                                                quien={p.quien}
                                                monto={p.monto}
                                                href={verPagos ? `/panel/pagos/${p.uuid}` : null}
                                            />
                                        ))}
                                    </ul>
                                </div>
                            ) : null}

                            {cobros.meson.length > 0 ? (
                                <div>
                                    <h3 className="rotulo mb-1">{fuentes.meson}</h3>
                                    <ul>
                                        {cobros.meson.map((f, i) => (
                                            <Cobro
                                                key={`${f.hora}-${f.nombre}-${i}`}
                                                hora={f.hora}
                                                titulo={f.nombre}
                                                detalle={f.detalle}
                                                medio={f.medio}
                                                quien={f.quien}
                                                monto={f.monto}
                                            />
                                        ))}
                                    </ul>
                                </div>
                            ) : null}

                            {cobros.talleres.length > 0 ? (
                                <div>
                                    <h3 className="rotulo mb-1">{fuentes.talleres}</h3>
                                    <ul>
                                        {cobros.talleres.map((t, i) => (
                                            <Cobro key={`${t.nombre}-${i}`} titulo={t.nombre} detalle={t.detalle} monto={t.monto} />
                                        ))}
                                    </ul>
                                </div>
                            ) : null}
                        </div>
                    )}
                </Panel>
            </div>
        </>
    );
}
