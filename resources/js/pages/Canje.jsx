import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { XIcon } from 'lucide-react';

import { Campo, Seleccion, Texto } from '@/components/Campo';
import Dialogo from '@/components/Dialogo';
import { Celda, Fila, Tabla } from '@/components/Tabla';
import { Cifra, Panel } from '@/components/Tablero';
import { puede } from '@/lib/permisos';
import { useTokenDeEnvio } from '@/lib/tokenDeEnvio';

/**
 * Entradas por canje: el huésped del hotel que llega con su tarjetita.
 *
 * No paga y no es socio. Se anota su nombre y su tarjeta en dos segundos y se
 * le deja pasar. Arriba, cuántos van en el mes por cada convenio.
 */
export default function Canje({ convenios, entradas }) {
    const unoSolo = convenios.length === 1;
    const { auth } = usePage().props;
    // Anotar es crear y quitar es corregir: el mismo reparto que el servidor.
    const puedeAnotar = puede(auth, 'clientes.crear');
    const puedeQuitar = puede(auth, 'clientes.editar');

    const { data, setData, post, transform, processing, errors, reset } = useForm({
        // Con un solo convenio de canje no hay nada que elegir.
        id_convenio: unoSolo ? convenios[0].id : '',
        nombre: '',
        tarjeta: '',
    });
    // El doble clic en «Anotar» dejaba la misma entrada dos veces.
    const token = useTokenDeEnvio();

    function anotar(evento) {
        evento.preventDefault();
        transform((d) => ({ ...d, form_submit_token: token.actual() }));
        post('/panel/canje', {
            preserveScroll: true,
            onSuccess: () => {
                reset('nombre', 'tarjeta');
                token.renovar();
            },
        });
    }

    // El mismo cuadro del resto del panel, no el aviso gris del navegador.
    const [quitando, setQuitando] = useState(null);

    return (
        <>
            <Head title="Canje" />

            <header className="mb-4">
                <h1 className="text-lg font-semibold text-chalk">Entradas por canje</h1>
                <p className="apoyo text-fog">Huéspedes de hotel y otros convenios que entran sin pagar</p>
            </header>

            {convenios.length === 0 ? (
                <section className="rounded-panel border border-line bg-surface p-4 text-sm text-fog">
                    Todavía no hay convenios de canje. Abre el convenio del hotel en{' '}
                    {puede(auth, 'configuracion.ver') ? (
                        <Link href="/panel/convenios" className="text-chalk underline">
                            Configuración → Convenios
                        </Link>
                    ) : (
                        <span className="text-chalk">Configuración → Convenios</span>
                    )}{' '}
                    y marca «Entran sin pagar».
                </section>
            ) : (
                <>
                    {puedeAnotar ? (
                        <form onSubmit={anotar} className="mb-4 rounded-panel border border-line bg-surface p-4">
                            <div className="grid grid-cols-1 items-end gap-3 sm:grid-cols-[1fr_1fr_auto] lg:grid-cols-[1fr_2fr_1fr_auto]">
                                {unoSolo ? null : (
                                    <Campo etiqueta="Viene de" nombre="id_convenio" error={errors.id_convenio} requerido>
                                        <Seleccion
                                            nombre="id_convenio"
                                            valor={data.id_convenio}
                                            alCambiar={(v) => setData('id_convenio', v)}
                                            error={errors.id_convenio}
                                            opciones={convenios.map((c) => ({ valor: c.id, etiqueta: c.nombre }))}
                                        />
                                    </Campo>
                                )}

                                <div className={unoSolo ? 'lg:col-span-2' : ''}>
                                    <Campo etiqueta="Nombre" nombre="nombre" error={errors.nombre} requerido>
                                        <Texto
                                            nombre="nombre"
                                            valor={data.nombre}
                                            alCambiar={(v) => setData('nombre', v)}
                                            error={errors.nombre}
                                            autoFocus
                                        />
                                    </Campo>
                                </div>

                                <Campo etiqueta="N.º de tarjeta" nombre="tarjeta" error={errors.tarjeta}>
                                    <Texto
                                        nombre="tarjeta"
                                        valor={data.tarjeta}
                                        alCambiar={(v) => setData('tarjeta', v)}
                                        error={errors.tarjeta}
                                    />
                                </Campo>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-control bg-volt px-4 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                                >
                                    Anotar entrada
                                </button>
                            </div>
                            {unoSolo ? <p className="apoyo mt-2 text-fog">Convenio: {convenios[0].nombre}</p> : null}
                            {/* Con un solo convenio el campo no se pinta, y su error
                                se perdía con él: si el convenio se cerró mientras
                                tanto, «Anotar entrada» no hacía nada. */}
                            {unoSolo && errors.id_convenio ? (
                                <p className="apoyo mt-1 text-danger" role="alert">
                                    {errors.id_convenio}
                                </p>
                            ) : null}
                        </form>
                    ) : null}

                    <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        {convenios.map((c) => (
                            <Cifra
                                key={c.id}
                                etiqueta={c.nombre}
                                valor={c.mes}
                                pie={`este mes · ${c.hoy} hoy · ${c.mes_pasado} el mes pasado`}
                            />
                        ))}
                    </div>
                </>
            )}

            <Panel titulo="Últimas entradas">
                <Tabla
                    columnas={['Cuándo', 'Nombre', 'Tarjeta', 'Convenio', 'Anotó', '']}
                    vacia={entradas.length === 0}
                    mensajeVacio="Todavía no se anota ninguna entrada."
                >
                    {entradas.map((e) => (
                        <Fila key={e.uuid}>
                            <Celda className="tabular-nums">{e.cuando}</Celda>
                            <Celda className="font-medium text-chalk">{e.nombre}</Celda>
                            <Celda className="tabular-nums">{e.tarjeta ?? '·'}</Celda>
                            <Celda>{e.convenio}</Celda>
                            <Celda className="text-fog">{e.anoto ?? '·'}</Celda>
                            <Celda>
                                {e.es_de_hoy && puedeQuitar ? (
                                    <button
                                        type="button"
                                        onClick={() => setQuitando(e)}
                                        aria-label={`Quitar la entrada de ${e.nombre}`}
                                        title="Quitar (anotada por error)"
                                        className="rounded-control p-1 text-fog transition-colors hover:bg-surface-2 hover:text-danger"
                                    >
                                        <XIcon className="size-3.5" aria-hidden="true" />
                                    </button>
                                ) : null}
                            </Celda>
                        </Fila>
                    ))}
                </Tabla>
            </Panel>
            {quitando ? (
                <Dialogo
                    abierto
                    alCerrar={() => setQuitando(null)}
                    titulo="¿Quitar esta entrada?"
                    descripcion={`La entrada de ${quitando.nombre}${quitando.tarjeta ? ` (tarjeta ${quitando.tarjeta})` : ''} deja de contar en el mes. Se quita porque se anotó por error.`}
                    accion={`/panel/canje/${quitando.uuid}`}
                    via="inertia"
                    metodo="delete"
                    etiquetaConfirmar="Quitar"
                    peligrosa
                />
            ) : null}

        </>
    );
}
