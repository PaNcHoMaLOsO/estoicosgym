import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    ArrowLeftIcon,
    ArrowRightLeftIcon,
    BanknoteIcon,
    PauseIcon,
    PencilIcon,
    PlayIcon,
    RefreshCwIcon,
} from 'lucide-react';

import Dialogo from '@/components/Dialogo';
import Estado from '@/components/Estado';
import { Campo, Seleccion, Texto } from '@/components/Campo';
import { Celda, Cifra, Fila, Tabla } from '@/components/Tabla';

const pesos = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0,
});

function Bloque({ titulo, children }) {
    return (
        <section className="rounded-panel border border-line bg-surface p-4">
            <h2 className="rotulo mb-3">{titulo}</h2>
            {children}
        </section>
    );
}

function Dato({ etiqueta, children }) {
    return (
        <div>
            <dt className="rotulo">{etiqueta}</dt>
            <dd className="mt-0.5 text-sm text-chalk">{children || <span className="text-fog">—</span>}</dd>
        </div>
    );
}

/** Los días que quedan, con color solo cuando exigen actuar. */
function Vigencia({ dias }) {
    if (dias === null || dias === undefined) {
        return <span className="text-fog">Sin fecha de vencimiento</span>;
    }

    if (dias < 0) {
        return <span className="font-medium text-danger">Venció hace {Math.abs(dias)} días</span>;
    }

    if (dias === 0) {
        return <span className="font-medium text-danger">Vence hoy</span>;
    }

    if (dias <= 7) {
        return <span className="font-medium text-warn">Quedan {dias} días</span>;
    }

    return <span className="text-fog">Quedan {dias} días</span>;
}

export default function Ficha({ inscripcion, socio, pago, pausa, puede, pagos, movimientos }) {
    // Cual esta abierto: null, 'pausar', 'reanudar' o 'traspasar'.
    const [dialogo, setDialogo] = useState(null);
    const [dias, setDias] = useState('30');
    const [razon, setRazon] = useState('');
    const [destino, setDestino] = useState('');
    const [motivoTraspaso, setMotivoTraspaso] = useState('');
    const [ignorarDeuda, setIgnorarDeuda] = useState(false);
    const [candidatos, setCandidatos] = useState(null);
    const [busqueda, setBusqueda] = useState('');
    const [nombreDestino, setNombreDestino] = useState('');

    const cerrar = () => setDialogo(null);

    /*
     * A quién se le traspasa se BUSCA, no se elige de una lista.
     *
     * El servidor devuelve solo socios sin membresía vigente, que con un padrón
     * de cientos siguen siendo demasiados para un desplegable. Se consulta al
     * escribir y no al abrir el diálogo: la mayoría de las visitas a la ficha no
     * traspasan nada.
     */
    async function buscarDestinatario(texto) {
        setBusqueda(texto);
        setDestino('');

        // El servidor pide dos letras: con menos, la busqueda devolveria medio
        // padron y no serviria para elegir a nadie.
        if (texto.trim().length < 2) {
            setCandidatos(null);

            return;
        }

        try {
            const r = await fetch(
                `/panel/inscripciones/${inscripcion.uuid}/buscar-clientes-traspaso?q=${encodeURIComponent(texto)}`,
                { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
            );
            const j = await r.json();
            setCandidatos(j.clientes ?? []);
        } catch (e) {
            setCandidatos([]);
        }
    }

    // Se ofrecen SOLO las acciones que el servidor aceptaria ahora mismo: un
    // boton que lleva a un error enseña a desconfiar de lo que hay en pantalla.
    const acciones = [
        puede.cobrar && {
            href: `/panel/pagos/cobrar?inscripcion=${inscripcion.uuid}`,
            etiqueta: 'Cobrar',
            Icono: BanknoteIcon,
            primaria: true,
        },
        puede.renovar && {
            // Va a su propia pantalla y no a un dialogo como las otras tres:
            // hay que elegir plan, convenio y forma de pago, que no cabe en un
            // recuadro sin dejarlo apretado.
            href: `/panel/inscripciones/${inscripcion.uuid}/renovar`,
            etiqueta: 'Renovar',
            Icono: RefreshCwIcon,
        },
        {
            // Corregir el error de tecleo: la fecha de inicio del mes pasado en
            // vez de la de este. Se ofrece SIEMPRE porque un dato mal escrito
            // hay que poder arreglarlo este la membresia como este.
            href: `/panel/inscripciones/${inscripcion.uuid}/editar`,
            etiqueta: 'Corregir',
            Icono: PencilIcon,
        },
        puede.pausar && {
            alPulsar: () => setDialogo('pausar'),
            etiqueta: 'Pausar',
            Icono: PauseIcon,
        },
        puede.reanudar && {
            alPulsar: () => setDialogo('reanudar'),
            etiqueta: 'Reanudar',
            Icono: PlayIcon,
        },
        puede.traspasar && {
            alPulsar: () => setDialogo('traspasar'),
            etiqueta: 'Traspasar',
            Icono: ArrowRightLeftIcon,
        },
    ].filter(Boolean);

    return (
        <>
            <Head title={`${socio?.nombre ?? 'Inscripción'} · ${inscripcion.membresia ?? ''}`} />

            <header className="mb-5">
                <Link
                    href="/panel/inscripciones"
                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                >
                    <ArrowLeftIcon className="size-3.5" aria-hidden="true" />
                    Inscripciones
                </Link>

                <div className="mt-1 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="flex flex-wrap items-center gap-2 text-lg font-semibold text-chalk">
                            {inscripcion.membresia ?? 'Membresía'}
                            <Estado codigo={inscripcion.id_estado} />
                        </h1>
                        <p className="apoyo text-fog">
                            {socio ? (
                                <Link
                                    href={`/panel/clientes/${socio.uuid}`}
                                    className="text-chalk hover:underline"
                                >
                                    {socio.nombre}
                                </Link>
                            ) : (
                                'Socio eliminado'
                            )}
                            {' · '}
                            <Vigencia dias={inscripcion.dias} />
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {acciones.map(({ href, alPulsar, etiqueta, Icono, primaria }) => {
                            const estilo = `inline-flex items-center gap-1.5 rounded-control px-3 py-1.5 text-sm transition-colors ${
                                primaria
                                    ? 'bg-volt font-medium text-on-volt hover:opacity-90'
                                    : 'border border-line text-chalk hover:bg-surface-2'
                            }`;

                            // Las que se resuelven aquí mismo abren un diálogo;
                            // las que siguen en Blade son enlaces normales.
                            return alPulsar ? (
                                <button key={etiqueta} type="button" onClick={alPulsar} className={estilo}>
                                    <Icono className="size-4" aria-hidden="true" />
                                    {etiqueta}
                                </button>
                            ) : (
                                <a key={etiqueta} href={href} className={estilo}>
                                    <Icono className="size-4" aria-hidden="true" />
                                    {etiqueta}
                                </a>
                            );
                        })}
                    </div>
                </div>
            </header>

            {/* Lo primero: cuánto se debe. Es lo que se mira con el socio
                delante, antes que cualquier otro dato de la ficha. */}
            <div className="mb-4 rounded-panel border border-line bg-surface p-4">
                <div className="flex flex-wrap items-baseline justify-between gap-3">
                    <div>
                        <p className="rotulo">
                            {pago.pendiente > 0 ? 'Falta por pagar' : 'Membresía pagada'}
                        </p>
                        <p
                            className={`mt-0.5 text-2xl font-semibold tabular-nums ${
                                pago.pendiente > 0 ? 'text-warn' : 'text-ok'
                            }`}
                        >
                            {pago.pendiente > 0 ? pesos.format(pago.pendiente) : pesos.format(pago.total)}
                        </p>
                    </div>
                    <p className="apoyo text-fog">
                        {pesos.format(pago.abonado)} de {pesos.format(pago.total)} · {pago.porcentaje}%
                    </p>
                </div>

                <div className="mt-3 h-1.5 overflow-hidden rounded-pill bg-surface-2">
                    <div
                        className={`h-full rounded-pill ${pago.pendiente > 0 ? 'bg-warn' : 'bg-ok'}`}
                        style={{ width: `${pago.porcentaje}%` }}
                    />
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-3">
                <div className="space-y-3">
                    <Bloque titulo="Membresía">
                        <dl className="space-y-3">
                            <Dato etiqueta="Inicio">{inscripcion.inicio}</Dato>
                            <Dato etiqueta="Vence">{inscripcion.vence}</Dato>
                            <Dato etiqueta="Convenio">{inscripcion.convenio}</Dato>
                            {inscripcion.descuento > 0 ? (
                                <Dato etiqueta="Descuento">
                                    {pesos.format(inscripcion.descuento)}
                                    {inscripcion.motivo_descuento ? ` · ${inscripcion.motivo_descuento}` : ''}
                                </Dato>
                            ) : null}
                        </dl>
                    </Bloque>

                    <Bloque titulo="Pausas">
                        {pausa.pausada ? (
                            <div className="rounded-panel border border-warn/40 bg-warn/5 p-3 text-sm text-warn">
                                <p className="font-medium">Pausada desde el {pausa.desde}</p>
                                {pausa.hasta ? <p className="apoyo">Se reanuda el {pausa.hasta}</p> : null}
                                {pausa.razon ? <p className="apoyo mt-1">{pausa.razon}</p> : null}
                            </div>
                        ) : (
                            <p className="text-sm text-fog">No está pausada.</p>
                        )}

                        <p className="apoyo mt-3 text-fog">
                            {pausa.permitidas === 0
                                ? 'Este plan no admite pausas.'
                                : `Usadas ${pausa.usadas} de ${pausa.permitidas} · quedan ${pausa.disponibles}`}
                        </p>
                    </Bloque>

                    {socio ? (
                        <Bloque titulo="Contacto del socio">
                            <dl className="space-y-3">
                                <Dato etiqueta="RUT">{socio.rut}</Dato>
                                <Dato etiqueta="Correo">{socio.email}</Dato>
                                <Dato etiqueta="Celular">{socio.celular}</Dato>
                            </dl>
                        </Bloque>
                    ) : null}

                    {inscripcion.observaciones ? (
                        <Bloque titulo="Observaciones">
                            <p className="text-sm whitespace-pre-line text-fog">
                                {inscripcion.observaciones}
                            </p>
                        </Bloque>
                    ) : null}
                </div>

                <div className="space-y-3 lg:col-span-2">
                    <Bloque titulo="Pagos de esta membresía">
                        <Tabla
                            columnas={['Fecha', 'Método', 'Tipo', 'Estado', 'Abonado']}
                            vacia={pagos.length === 0}
                            mensajeVacio="Todavía no se ha cobrado nada de esta membresía."
                        >
                            {pagos.map((p) => (
                                <Fila key={p.uuid}>
                                    <Celda className="tabular-nums text-chalk">
                                        <a href={`/panel/pagos/${p.uuid}`} className="hover:underline">
                                            {p.fecha ?? '—'}
                                        </a>
                                    </Celda>
                                    <Celda>{p.metodo ?? '—'}</Celda>
                                    <Celda>{p.tipo}</Celda>
                                    <Celda>
                                        <Estado codigo={p.id_estado} />
                                    </Celda>
                                    <Cifra className="text-chalk">{pesos.format(p.abonado)}</Cifra>
                                </Fila>
                            ))}
                        </Tabla>
                    </Bloque>

                    <Bloque titulo="Qué le ha pasado">
                        {movimientos.length === 0 ? (
                            <p className="apoyo py-4 text-center text-fog">
                                Sin movimientos registrados.
                            </p>
                        ) : (
                            <ol className="space-y-2">
                                {movimientos.map((m) => (
                                    <li
                                        key={m.id}
                                        className="flex justify-between gap-3 border-b border-line pb-2 text-sm last:border-0 last:pb-0"
                                    >
                                        <div>
                                            <p className="text-chalk">{m.que}</p>
                                            {m.motivo ? (
                                                <p className="apoyo text-fog">{m.motivo}</p>
                                            ) : null}
                                        </div>
                                        <div className="shrink-0 text-right">
                                            <p className="apoyo tabular-nums text-fog">{m.cuando ?? '—'}</p>
                                            {m.quien ? <p className="apoyo text-fog">{m.quien}</p> : null}
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </Bloque>
                </div>
            </div>

            <Dialogo
                abierto={dialogo === 'pausar'}
                alCerrar={cerrar}
                titulo="Pausar la membresía"
                descripcion="Los días de pausa se suman al final: la membresía no pierde tiempo."
                accion={`/panel/inscripciones/${inscripcion.uuid}/pausar`}
                datos={{ dias_pausa: Number(dias), razon_pausa: razon }}
                etiquetaConfirmar="Pausar"
                puedeConfirmar={Number(dias) >= 1 && Number(dias) <= 90}
            >
                <Campo
                    etiqueta="Cuántos días"
                    nombre="dias"
                    requerido
                    ayuda={`A este plan le ${pausa.disponibles === 1 ? 'queda' : 'quedan'} ${pausa.disponibles} ${pausa.disponibles === 1 ? 'pausa' : 'pausas'}.`}
                >
                    <Seleccion
                        nombre="dias"
                        valor={dias}
                        alCambiar={setDias}
                        opciones={[
                            { valor: '7', etiqueta: '7 días' },
                            { valor: '14', etiqueta: '14 días' },
                            { valor: '30', etiqueta: '30 días' },
                            { valor: '60', etiqueta: '60 días' },
                            { valor: '90', etiqueta: '90 días' },
                        ]}
                        vacio="Elige…"
                    />
                </Campo>

                <Campo etiqueta="Motivo" nombre="razon" ayuda="Queda registrado en el historial.">
                    <Texto
                        nombre="razon"
                        valor={razon}
                        alCambiar={setRazon}
                        placeholder="Viaje, lesión…"
                    />
                </Campo>
            </Dialogo>

            <Dialogo
                abierto={dialogo === 'reanudar'}
                alCerrar={cerrar}
                titulo="Reanudar la membresía"
                descripcion={
                    pausa.hasta
                        ? `Estaba pausada hasta el ${pausa.hasta}. Al reanudar se le devuelven los días que le quedaban.`
                        : 'Al reanudar se le devuelven los días que le quedaban.'
                }
                accion={`/panel/inscripciones/${inscripcion.uuid}/reanudar`}
                etiquetaConfirmar="Reanudar"
            />

            <Dialogo
                abierto={dialogo === 'traspasar'}
                alCerrar={cerrar}
                titulo="Traspasar la membresía"
                descripcion="El socio actual la pierde y pasa entera al nuevo titular."
                accion={`/panel/inscripciones/${inscripcion.uuid}/traspasar`}
                datos={{
                    id_cliente_destino: destino,
                    motivo_traspaso: motivoTraspaso,
                    ignorar_deuda: ignorarDeuda,
                }}
                etiquetaConfirmar="Traspasar"
                peligrosa
                puedeConfirmar={destino !== '' && motivoTraspaso.trim().length > 0}
            >
                {destino ? (
                    // Ya elegido: se enseña a quién y se deja deshacer, en vez
                    // de dejar la lista abierta invitando a cambiarlo sin querer.
                    <div className="flex items-center justify-between gap-3 rounded-control border border-line bg-surface-2 px-3 py-2 text-sm">
                        <span className="text-chalk">{nombreDestino}</span>
                        <button
                            type="button"
                            onClick={() => {
                                setDestino('');
                                setNombreDestino('');
                                setBusqueda('');
                                setCandidatos(null);
                            }}
                            className="apoyo shrink-0 text-fog transition-colors hover:text-chalk"
                        >
                            Cambiar
                        </button>
                    </div>
                ) : (
                    <Campo
                        etiqueta="Nuevo titular"
                        nombre="destino"
                        requerido
                        ayuda="Solo aparecen socios SIN membresía vigente: nadie puede tener dos a la vez."
                    >
                        <Texto
                            nombre="destino"
                            tipo="search"
                            valor={busqueda}
                            alCambiar={buscarDestinatario}
                            placeholder="Nombre, RUT o correo"
                        />

                        {candidatos === null ? null : candidatos.length === 0 ? (
                            <p className="apoyo mt-2 text-fog">
                                Nadie coincide, o quien buscas ya tiene una membresía vigente.
                            </p>
                        ) : (
                            <ul className="mt-2 max-h-40 divide-y divide-line overflow-y-auto rounded-control border border-line">
                                {candidatos.map((c) => (
                                    <li key={c.id}>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setDestino(String(c.id));
                                                setNombreDestino(
                                                    c.nombre_completo ?? c.nombre ?? `Socio ${c.id}`,
                                                );
                                            }}
                                            className="w-full px-3 py-2 text-left text-sm text-chalk transition-colors hover:bg-surface-2"
                                        >
                                            {c.nombre_completo ?? c.nombre ?? `Socio ${c.id}`}
                                            {c.run_pasaporte ? (
                                                <span className="apoyo block text-fog">{c.run_pasaporte}</span>
                                            ) : null}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Campo>
                )}

                <Campo etiqueta="Motivo" nombre="motivo_traspaso" requerido>
                    <Texto
                        nombre="motivo_traspaso"
                        valor={motivoTraspaso}
                        alCambiar={setMotivoTraspaso}
                        placeholder="Por qué se traspasa"
                    />
                </Campo>

                {/* La casilla solo aparece si hay deuda: sin saldo pendiente
                    sería una pregunta sin sentido. */}
                {pago.pendiente > 0 ? (
                    <label className="flex items-start gap-2 text-sm text-chalk">
                        <input
                            type="checkbox"
                            checked={ignorarDeuda}
                            onChange={(e) => setIgnorarDeuda(e.target.checked)}
                            className="mt-0.5"
                        />
                        <span>
                            Traspasar aunque queden {pesos.format(pago.pendiente)} por cobrar.
                            <span className="apoyo block text-fog">
                                La deuda se va con la membresía al nuevo titular.
                            </span>
                        </span>
                    </label>
                ) : null}
            </Dialogo>
        </>
    );
}
