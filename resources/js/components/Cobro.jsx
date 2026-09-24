import { PlusIcon, TrashIcon } from 'lucide-react';

import { Campo, Texto } from '@/components/Campo';
import { pesos } from '@/components/Tablero';

/**
 * Cómo paga el socio: la misma pieza en la inscripción, la renovación y el alta.
 *
 * ERAN TRES VERSIONES DE LO MISMO y la de inscribir era la peor: «Paga el plan
 * completo» igual pedía escribir el monto a mano, el medio venía vacío y
 * «varios métodos» empezaba con una sola línea. Con el socio esperando en el
 * mesón, cada campo de más es un error posible.
 *
 *  · «Todo» no pregunta cuánto: es el total, y lo pone el servidor.
 *  · El medio sale marcado en efectivo, que es lo que más se usa.
 *  · «Varios medios» trae dos líneas, y al escribir la primera la segunda se
 *    rellena con lo que falta: casi siempre es «esto en efectivo y el resto
 *    con tarjeta», y así no hay que restar de cabeza.
 */

/** Botones grandes para elegir de una lista corta: un toque, sin desplegar. */
export function Botones({ opciones, valor, alElegir, nombre, columnas = 'sm:grid-cols-2', compacto = false }) {
    return (
        <div role="radiogroup" aria-label={nombre} className={`grid gap-2 ${columnas}`}>
            {opciones.map((o) => {
                const elegido = String(valor) === String(o.valor);

                return (
                    <button
                        key={o.valor}
                        type="button"
                        role="radio"
                        aria-checked={elegido}
                        onClick={() => alElegir(o.valor)}
                        className={`rounded-control border text-left text-sm transition-colors ${compacto ? 'px-2.5 py-1.5' : 'px-3 py-2'} ${
                            elegido
                                ? 'border-volt bg-volt/10 text-chalk'
                                : 'border-line text-fog hover:border-line-strong hover:text-chalk'
                        }`}
                    >
                        <span className="block font-medium">{o.etiqueta}</span>
                        {o.pie ? <span className="apoyo block text-fog">{o.pie}</span> : null}
                    </button>
                );
            })}
        </div>
    );
}

/** El efectivo primero, si existe: es lo que más se usa en el mesón. */
export function metodoPorDefecto(metodosPago) {
    return metodosPago.find((m) => /efectivo/i.test(m.nombre))?.id ?? metodosPago[0]?.id ?? '';
}

/** Las dos líneas con que empieza un pago repartido: efectivo y otro medio. */
export function partesIniciales(metodosPago) {
    const primero = metodoPorDefecto(metodosPago);
    const otro = metodosPago.find((m) => String(m.id) !== String(primero))?.id ?? '';

    return [
        { id_metodo_pago: primero, monto: '' },
        { id_metodo_pago: otro, monto: '' },
    ];
}

/** El detalle que espera el servidor: solo las líneas con medio y monto. */
export function detalleDePartes(partes, metodosPago) {
    return partes
        .filter((p) => p.id_metodo_pago && Number(p.monto) > 0)
        .map((p) => ({
            id_metodo_pago: Number(p.id_metodo_pago),
            monto: Number(p.monto),
            // El nombre también: queda escrito en el pago, y si mañana se
            // renombra el medio, el recibo viejo sigue diciendo con qué se pagó.
            metodo_nombre: metodosPago.find((m) => String(m.id) === String(p.id_metodo_pago))?.nombre,
        }));
}

/**
 * @param {object} props
 * @param {number} props.total               lo que sale el plan, ya con descuentos
 * @param {string} props.forma               la forma elegida
 * @param {(v:string)=>void} props.alCambiarForma
 * @param {string} props.abono               el valor de la forma «una parte» en el servidor: «abono» o «parcial»
 */
export default function Cobro({
    total,
    forma,
    alCambiarForma,
    abono = 'abono',
    monto,
    alCambiarMonto,
    metodo,
    alCambiarMetodo,
    metodosPago,
    partes,
    setPartes,
    errores = {},
}) {
    const opcionesMetodo = metodosPago.map((m) => ({ valor: m.id, etiqueta: m.nombre }));
    const suma = partes.reduce((t, p) => t + (Number(p.monto) || 0), 0);
    const falta = Math.max(0, total - suma);

    function cambiarParte(indice, cambios) {
        setPartes((antes) => {
            const nuevas = antes.map((p, j) => (j === indice ? { ...p, ...cambios } : p));

            // Al escribir el monto de la primera, la segunda se rellena con lo
            // que falta, pero solo mientras esté vacía o siga siendo ese resto:
            // lo que se escribe a mano manda.
            if (indice === 0 && 'monto' in cambios && nuevas.length >= 2) {
                const restoDeAntes = Math.max(0, total - (Number(antes[0].monto) || 0));
                const segunda = antes[1];

                if (segunda.monto === '' || Number(segunda.monto) === restoDeAntes) {
                    const resto = Math.max(0, total - (Number(cambios.monto) || 0));
                    nuevas[1] = { ...nuevas[1], monto: resto > 0 ? String(resto) : '' };
                }
            }

            return nuevas;
        });
    }

    return (
        <div className="space-y-4">
            <Campo etiqueta="Paga" nombre="tipo_pago" error={errores.tipo_pago} requerido>
                <Botones
                    nombre="Cómo paga"
                    valor={forma}
                    alElegir={alCambiarForma}
                    columnas="grid-cols-2 sm:grid-cols-4"
                    opciones={[
                        { valor: 'completo', etiqueta: 'Todo', pie: pesos.format(total) },
                        { valor: abono, etiqueta: 'Una parte', pie: 'abono' },
                        { valor: 'mixto', etiqueta: 'Varios medios', pie: 'efectivo y tarjeta…' },
                        { valor: 'pendiente', etiqueta: 'Nada todavía', pie: 'queda debiendo' },
                    ]}
                />
            </Campo>

            {forma === abono ? (
                <Campo
                    etiqueta="Cuánto abona"
                    nombre="monto_abonado"
                    error={errores.monto_abonado}
                    requerido
                    ayuda={`Menos de ${pesos.format(total)}. El resto queda por cobrar.`}
                >
                    <Texto nombre="monto_abonado" tipo="number" min="1" inputMode="numeric" valor={monto} alCambiar={alCambiarMonto} />
                </Campo>
            ) : null}

            {forma === 'completo' || forma === abono ? (
                <Campo etiqueta="Con qué paga" nombre="id_metodo_pago" error={errores.id_metodo_pago} requerido>
                    <Botones
                        nombre="Medio de pago"
                        valor={metodo}
                        alElegir={alCambiarMetodo}
                        columnas="grid-cols-2 sm:grid-cols-3"
                        opciones={opcionesMetodo}
                    />
                </Campo>
            ) : null}

            {forma === 'mixto' ? (
                <Campo etiqueta="Cómo se reparte" nombre="detalle_pagos_mixto" error={errores.detalle_pagos_mixto} requerido>
                    <div className="space-y-3">
                        {partes.map((parte, i) => (
                            <div key={i} className="flex flex-wrap items-center gap-2 rounded-control border border-line p-2">
                                <div className="min-w-0 flex-1">
                                    <Botones
                                        nombre={`Medio de la parte ${i + 1}`}
                                        valor={parte.id_metodo_pago}
                                        alElegir={(v) => cambiarParte(i, { id_metodo_pago: v })}
                                        columnas="grid-cols-3"
                                        opciones={opcionesMetodo}
                                        compacto
                                    />
                                </div>
                                <input
                                    type="number"
                                    min="1"
                                    inputMode="numeric"
                                    value={parte.monto}
                                    onChange={(e) => cambiarParte(i, { monto: e.target.value })}
                                    placeholder={i === 0 ? 'Monto' : 'El resto'}
                                    aria-label={`Monto de la parte ${i + 1}`}
                                    className="w-28 rounded-control border border-line bg-surface px-2 py-1.5 text-sm tabular-nums text-chalk focus:border-line-strong focus:outline-none"
                                />
                                {partes.length > 2 ? (
                                    <button
                                        type="button"
                                        onClick={() => setPartes((ps) => ps.filter((_, j) => j !== i))}
                                        aria-label={`Quitar la parte ${i + 1}`}
                                        className="rounded-control p-1.5 text-fog transition-colors hover:text-danger"
                                    >
                                        <TrashIcon className="size-4" aria-hidden="true" />
                                    </button>
                                ) : null}
                            </div>
                        ))}

                        <div className="flex flex-wrap items-center justify-between gap-3">
                            {partes.length < metodosPago.length ? (
                                <button
                                    type="button"
                                    onClick={() => setPartes((ps) => [...ps, { id_metodo_pago: '', monto: falta > 0 ? String(falta) : '' }])}
                                    className="apoyo inline-flex items-center gap-1 text-fog transition-colors hover:text-chalk"
                                >
                                    <PlusIcon className="size-3.5" aria-hidden="true" />
                                    Otro medio más
                                </button>
                            ) : (
                                <span />
                            )}

                            <p className="apoyo tabular-nums">
                                <span className="text-fog">Suman {pesos.format(suma)} de {pesos.format(total)}</span>
                                {suma > total ? (
                                    <span className="ml-1 text-danger">· se pasa por {pesos.format(suma - total)}</span>
                                ) : falta > 0 && suma > 0 ? (
                                    <span className="ml-1 text-warn">· faltan {pesos.format(falta)}, quedan por cobrar</span>
                                ) : suma === total && total > 0 ? (
                                    <span className="ml-1 text-ok">· cuadra</span>
                                ) : null}
                            </p>
                        </div>
                    </div>
                </Campo>
            ) : null}
        </div>
    );
}
