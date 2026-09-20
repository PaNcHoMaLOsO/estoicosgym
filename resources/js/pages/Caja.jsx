import { Head, Link } from '@inertiajs/react';
import { TrendingDownIcon, TrendingUpIcon } from 'lucide-react';

import Barras from '@/components/Barras';
import Columnas from '@/components/Columnas';
import { Cifra, Panel, pesos } from '@/components/Tablero';
import { Reservado } from '@/Privado';

/**
 * La caja: lo que entró, lo que se debe y cómo va el gimnasio.
 *
 * Salió del resumen, que es lo primero que abre quien atiende el mesón. Aquí
 * solo entra quien ve los informes, y aun así las cifras salen TAPADAS: en el
 * mesón se sienta gente detrás de quien mira la pantalla. El ojito de la barra
 * o la tecla O las destapan.
 *
 * CADA CIFRA CON CON QUÉ COMPARARLA. «Entró $2.070.034 este mes» no dice si el
 * mes va bien; «un 12% más que a esta altura del mes pasado» sí. Un número solo
 * obliga a acordarse del anterior, y nadie se acuerda.
 */

/** Cuánto cambió respecto al periodo anterior, en palabras y con su flecha. */
function Comparacion({ ahora, antes, cuando }) {
    if (! antes) {
        // Sin periodo anterior con qué comparar, el porcentaje sería inventado:
        // «infinito por ciento más» no es una cifra que sirva para nada.
        return <>{ahora > 0 ? `nada que comparar ${cuando}` : `sin movimiento ${cuando}`}</>;
    }

    const cambio = Math.round(((ahora - antes) / antes) * 100);

    if (cambio === 0) {
        return <>igual que {cuando}</>;
    }

    const Icono = cambio > 0 ? TrendingUpIcon : TrendingDownIcon;

    return (
        <span className={`inline-flex items-center gap-1 ${cambio > 0 ? 'text-ok' : 'text-warn'}`}>
            <Icono className="size-3.5" aria-hidden="true" />
            {cambio > 0 ? '+' : ''}
            {cambio}% que {cuando}
        </span>
    );
}

/** Una parte de lo que se debe, con a dónde ir a cobrarla. */
function Deuda({ etiqueta, dato, explicacion, href }) {
    return (
        <li className="flex items-baseline justify-between gap-3 border-b border-line py-2 last:border-0">
            <div>
                <Link href={href} className="text-sm text-chalk hover:underline">
                    {etiqueta}
                </Link>
                <p className="apoyo text-fog">{explicacion}</p>
            </div>
            <div className="shrink-0 text-right">
                <p className="font-medium tabular-nums text-chalk">
                    <Reservado ancho="w-16">{pesos.format(dato.total)}</Reservado>
                </p>
                <p className="apoyo tabular-nums text-fog">
                    {dato.cuantas} {dato.cuantas === 1 ? 'membresía' : 'membresías'}
                </p>
            </div>
        </li>
    );
}

export default function Caja({ caja, deuda, fiado, porDia, porMes, porMetodo, altas, porPlan }) {
    // Tapado también dentro de los gráficos: destapar el ojito destapa todo.
    const plata = (valor) => <Reservado ancho="w-14">{pesos.format(valor)}</Reservado>;

    return (
        <>
            <Head title="Caja" />

            <header className="mb-5">
                <h1 className="text-lg font-semibold text-chalk">Caja</h1>
                <p className="apoyo text-fog">Lo que entra, lo que se debe y cómo va el gimnasio</p>
            </header>

            <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Cifra
                    etiqueta="Entró hoy"
                    valor={<Reservado ancho="w-20">{pesos.format(caja.hoy)}</Reservado>}
                    pie={<Comparacion ahora={caja.hoy} antes={caja.ayer} cuando="ayer" />}
                />
                <Cifra
                    etiqueta="Entró este mes"
                    valor={<Reservado ancho="w-20">{pesos.format(caja.mes)}</Reservado>}
                    pie={
                        <Comparacion
                            ahora={caja.mes}
                            antes={caja.mes_pasado}
                            cuando={`a esta altura del mes pasado`}
                        />
                    }
                />
                <Cifra
                    etiqueta="Por cobrar"
                    valor={<Reservado ancho="w-20">{pesos.format(caja.por_cobrar)}</Reservado>}
                    pie="de membresías"
                    tono={caja.por_cobrar > 0 ? 'aviso' : 'normal'}
                    siempreTono
                />
                <Cifra
                    etiqueta="Fiado en el mesón"
                    valor={<Reservado ancho="w-20">{pesos.format(fiado.total)}</Reservado>}
                    pie={
                        fiado.personas > 0
                            ? `${fiado.personas} ${fiado.personas === 1 ? 'persona debe' : 'personas deben'}`
                            : 'nadie debe'
                    }
                    tono={fiado.total > 0 ? 'aviso' : 'normal'}
                    siempreTono={fiado.total > 0}
                />
            </div>

            <div className="mb-4 grid gap-3 lg:grid-cols-2">
                <Panel
                    titulo="Cómo va el mes, día a día"
                    descripcion="Dónde se concentran los cobros: los días en gris todavía no llegan."
                >
                    <Columnas
                        datos={porDia}
                        etiqueta="Ingresos"
                        formato={plata}
                        pie={<>Vamos {caja.dia_del_mes} de {porDia.length} días</>}
                    />
                </Panel>

                <Panel titulo="Cómo pagan" descripcion="Con qué medio entró la plata este mes. Un pago mixto se reparte entre sus dos medios.">
                    <Barras
                        filas={porMetodo}
                        formato={plata}
                        vacio="Todavía no se cobró nada este mes."
                    />
                </Panel>
            </div>

            <div className="mb-4 grid gap-3 lg:grid-cols-2">
                <Panel titulo="Ingresos por mes" descripcion="Los últimos seis meses: si el gimnasio crece o se estanca.">
                    <Columnas datos={porMes} etiqueta="Ingresos" formato={plata} />
                </Panel>

                <Panel titulo="Quién debe" descripcion="Lo mismo que «por cobrar», partido según a quién hay que cobrarle.">
                    <ul>
                        <Deuda
                            etiqueta="Socios que siguen viniendo"
                            dato={deuda.vigente}
                            explicacion="Se les cobra en el mostrador, cualquier día."
                            href="/panel/inscripciones?filtro=con_deuda"
                        />
                        <Deuda
                            etiqueta="Membresías ya vencidas"
                            dato={deuda.vencida}
                            explicacion="Hay que salir a buscarlos: llamarlos o escribirles."
                            href="/panel/reportes/pendientes"
                        />
                    </ul>
                </Panel>
            </div>

            <div className="mb-4 grid gap-3 lg:grid-cols-2">
                <Panel titulo="Socios nuevos" descripcion="Altas de cada mes: cuánta gente entra al gimnasio.">
                    <Columnas datos={altas} etiqueta="Altas" pie={<>{altas.reduce((s, m) => s + m.total, 0)} en los últimos {altas.length} meses</>} />
                </Panel>

                <Panel titulo="Qué planes se venden" descripcion="Membresías vigentes de cada plan, y cuánto suman.">
                    <Barras
                        filas={porPlan.map((p) => ({ nombre: p.nombre, total: p.total, cantidad: undefined }))}
                        formato={(v) => `${v}`}
                        vacio="No hay membresías vigentes."
                    />
                </Panel>
            </div>

            <p className="apoyo flex flex-wrap gap-x-5 gap-y-1 text-fog">
                <Link href="/panel/reportes" className="transition-colors hover:text-chalk">
                    Informes con el detalle
                </Link>
                <Link href="/panel/fiados" className="transition-colors hover:text-chalk">
                    Lo fiado, persona por persona
                </Link>
                <Link href="/panel/pagos?filtro=mes" className="transition-colors hover:text-chalk">
                    Los cobros de este mes, uno por uno
                </Link>
            </p>
        </>
    );
}
