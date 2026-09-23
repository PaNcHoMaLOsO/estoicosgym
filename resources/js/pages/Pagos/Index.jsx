import { Head, Link, usePage } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';

import Buscador from '@/components/Buscador';
import Estado from '@/components/Estado';
import Filtros from '@/components/Filtros';
import Selector from '@/components/Selector';
import Paginacion from '@/components/Paginacion';
import { Celda, DosLineas, Fila, Tabla } from '@/components/Tabla';
import { Cifra as Tarjeta, pesos } from '@/components/Tablero';
import Convenio from '@/components/Convenio';
import { Reservado } from '@/Privado';

/*
 * Cada fila es plata que ENTRÓ: cuándo, de quién, cuánto y por dónde. La
 * última columna dice cómo quedó la membresía HOY. Antes decía lo que faltaba
 * el día de ese pago, y un socio que ya había terminado de pagar seguía
 * saliendo como que debía en sus abonos viejos.
 */
const COLUMNAS = [
    { titulo: 'Fecha', className: 'hidden sm:table-cell' },
    'Socio',
    { titulo: 'Pagó', className: 'text-right' },
    { titulo: 'Medio', className: 'hidden md:table-cell' },
    { titulo: 'La membresía hoy', className: 'hidden lg:table-cell' },
];

function Membresia({ debe, cobros }) {
    if (debe === null || debe === undefined) {
        return <span className="apoyo text-fog">·</span>;
    }

    if (debe > 0) {
        return (
            <span className="font-medium tabular-nums text-warn">
                Debe <Reservado ancho="w-16">{pesos.format(debe)}</Reservado>
            </span>
        );
    }

    /*
     * «Pagada» al lado de un cobro de 5.000 de una membresía de 25.000 parece
     * un error del sistema. No lo es: hubo más cobros. Decirlo aquí evita la
     * revisión a mano que ese aparente descuadre obliga a hacer.
     */
    return (
        <span className="text-ok">
            Pagada
            {cobros > 1 ? <span className="apoyo block text-fog">entre {cobros} cobros</span> : null}
        </span>
    );
}

/** Cómo se puede ordenar la lista. «Reciente» es lo de siempre. */
const ORDENES = [
    { valor: '', etiqueta: 'Lo más reciente' },
    { valor: 'monto_desc', etiqueta: 'Monto: de mayor a menor' },
    { valor: 'monto_asc', etiqueta: 'Monto: de menor a mayor' },
    { valor: 'antiguos', etiqueta: 'Lo más antiguo' },
];

export default function Index({ pagos, filtros, resumen, cantidades }) {
    const { privado } = usePage().props;
    const sinDeudas = Boolean(privado?.sin_pendientes);

    // Lo que no se pierde al tocar otro filtro.
    const conservar = {
        ...(filtros.buscar ? { buscar: filtros.buscar } : {}),
        ...(filtros.filtro ? { filtro: filtros.filtro } : {}),
        ...(filtros.orden ? { orden: filtros.orden } : {}),
    };

    const opciones = [
        { valor: '', etiqueta: 'Todos', cantidad: cantidades.total },
        { valor: 'hoy', etiqueta: 'Hoy', cantidad: cantidades.hoy },
        { valor: 'mes', etiqueta: 'Este mes', cantidad: cantidades.mes },
        { valor: 'abonos', etiqueta: 'Abonos', cantidad: cantidades.abonos, tono: 'warn' },
        // «Sin pagar» es la lista de los que deben, con otro nombre.
        ...(sinDeudas ? [] : [{ valor: 'pendientes', etiqueta: 'Sin pagar', cantidad: cantidades.pendientes, tono: 'warn' }]),
        // Aparte: la plata de los pases sí está en las cifras de arriba.
        { valor: 'pases', etiqueta: 'Pases diarios', cantidad: cantidades.pases, aparte: true },
    ];

    return (
        <>
            <Head title="Pagos" />

            <header className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-lg font-semibold text-chalk">Pagos</h1>
                    <p className="apoyo text-fog">Lo que entró, del más reciente al más antiguo</p>
                </div>

                <Link
                    href="/panel/pagos/cobrar"
                    className="inline-flex items-center gap-1.5 rounded-control bg-volt px-3 py-1.5 text-sm font-medium text-on-volt transition-opacity hover:opacity-90"
                >
                    <PlusIcon className="size-4" aria-hidden="true" />
                    Registrar pago
                </Link>
            </header>

            <div className={`mb-4 grid gap-3 ${sinDeudas ? 'sm:grid-cols-2' : 'sm:grid-cols-3'}`}>
                <Tarjeta etiqueta="Entró hoy" valor={<Reservado>{pesos.format(resumen.recaudado_hoy)}</Reservado>} />
                <Tarjeta etiqueta="Entró este mes" valor={<Reservado>{pesos.format(resumen.recaudado_mes)}</Reservado>} />
                {/* La única cifra que pide hacer algo, y la primera que se va
                    cuando se pidió esconder quién debe. */}
                {sinDeudas ? null : (
                <Tarjeta
                    etiqueta="Por cobrar"
                    valor={<Reservado>{pesos.format(resumen.por_cobrar)}</Reservado>}
                    tono="aviso"
                    siempreTono={resumen.por_cobrar > 0}
                    pie={
                        resumen.por_cobrar > 0 ? (
                            <Link href="/panel/inscripciones?filtro=con_deuda" className="hover:text-chalk hover:underline">
                                Ver quién debe →
                            </Link>
                        ) : undefined
                    }
                />
                )}
            </div>

            <div className="mb-3 flex flex-col gap-3">
                <Buscador
                    ruta="/panel/pagos"
                    valor={filtros.buscar}
                    etiqueta="Buscar por socio o RUT"
                    extra={conservar}
                />
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <Filtros ruta="/panel/pagos" actual={filtros.filtro} opciones={opciones} extra={conservar} />

                    {/* «¿Cuál fue el cobro más grande del mes?» no se responde
                        bajando una lista de trescientas filas. */}
                    <Selector
                        etiqueta="Ordenar"
                        nombre="orden"
                        valor={filtros.orden ?? ''}
                        ruta="/panel/pagos"
                        extra={conservar}
                        opciones={ORDENES}
                    />
                </div>
            </div>

            <Tabla
                columnas={COLUMNAS}
                vacia={pagos.data.length === 0}
                mensajeVacio={
                    filtros.buscar
                        ? `Ningún pago coincide con «${filtros.buscar}».`
                        : filtros.filtro
                          ? 'No hay pagos en este grupo.'
                          : 'Todavía no hay pagos registrados.'
                }
            >
                {pagos.data.map((pago) => (
                    <Fila key={pago.uuid} href={`/panel/pagos/${pago.uuid}`}>
                        <Celda className="hidden tabular-nums whitespace-nowrap sm:table-cell">{pago.fecha ?? '?'}</Celda>
                        <Celda>
                            <DosLineas
                                arriba={
                                    <Link href={`/panel/pagos/${pago.uuid}`} className="font-medium text-chalk hover:underline">
                                        {pago.socio}
                                    </Link>
                                }
                                abajo={
                                    <>
                                        {/* En celular no hay columna de fecha: va aquí. */}
                                        <span className="tabular-nums sm:hidden">{pago.fecha} · </span>
                                        {pago.membresia ?? 'Sin plan'}
                                        <Convenio nombre={pago.convenio} className="ml-1.5" />
                                    </>
                                }
                            />
                        </Celda>
                        <Celda className="text-right">
                            <div className="flex flex-col items-end gap-0.5">
                                <span className="font-medium tabular-nums text-chalk">
                                    {pago.abonado > 0 ? (
                                        <Reservado ancho="w-16">{pesos.format(pago.abonado)}</Reservado>
                                    ) : (
                                        'Nada'
                                    )}
                                </span>
                                {/* Solo se dice el precio de la membresía cuando este
                                    cobro no la cubre entero. Y se dice que es UNA PARTE
                                    cuando hubo más cobros: «$5.000 de $25.000» a secas se
                                    lee como que faltan 20.000, aunque ya estén pagados. */}
                                {pago.abonado < pago.total ? (
                                    <span className="apoyo tabular-nums text-fog">
                                        {pago.cobros > 1 ? 'parte de ' : 'de '}
                                        <Reservado ancho="w-14">{pesos.format(pago.total)}</Reservado>
                                    </span>
                                ) : null}
                                <span className="lg:hidden">
                                    <Estado codigo={pago.id_estado} />
                                </span>
                            </div>
                        </Celda>
                        <Celda className="hidden md:table-cell">{pago.metodo ?? 'Sin medio'}</Celda>
                        <Celda className="hidden lg:table-cell">
                            {sinDeudas ? null : <Membresia debe={pago.debe_hoy} cobros={pago.cobros} />}
                        </Celda>
                    </Fila>
                ))}
            </Tabla>

            <Paginacion paginador={pagos} />
        </>
    );
}
