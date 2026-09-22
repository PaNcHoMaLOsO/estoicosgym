import { ShoppingBagIcon } from 'lucide-react';

/**
 * EL MOSTRADOR: sitio reservado para cobrar cosas sueltas.
 *
 * En el mesón no solo se cobran planes: se venden barritas, bebidas, shakers.
 * Hoy eso se anota como fiado o se cobra fuera del sistema, y el stock vive en
 * otro lado —el panel de Estoicos Suplementos, que es otra aplicación y otra
 * base de datos—.
 *
 * ESTE HUECO ESTÁ A PROPÓSITO. No hay conexión todavía y no se inventa una:
 * lo que queda apartado es el lugar donde irá, para que cuando se enchufe el
 * catálogo la pantalla no haya que rehacerla ni el que atiende tenga que
 * aprenderse otro sitio. Mientras tanto dice la verdad: aún no se puede.
 */
export default function MesonTienda() {
    return (
        <section className="rounded-panel border border-dashed border-line bg-surface p-4">
            <div className="flex items-start gap-3">
                <span className="mt-0.5 rounded-control border border-line bg-surface-2 p-1.5">
                    <ShoppingBagIcon className="size-4 text-fog" aria-hidden="true" />
                </span>
                <div className="min-w-0">
                    <h2 className="rotulo">Venta del mesón</h2>
                    <p className="apoyo mt-1 text-fog">
                        Aquí irá el cobro rápido de barritas, bebidas y suplementos, con el catálogo de Estoicos
                        Suplementos. Todavía no está conectado.
                    </p>
                    <p className="apoyo mt-2 text-fog">
                        Por ahora eso se apunta como <span className="text-chalk">fiado</span> o se cobra aparte.
                    </p>
                </div>
            </div>
        </section>
    );
}
