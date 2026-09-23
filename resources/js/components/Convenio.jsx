/**
 * La etiqueta del convenio: «AIEP», «SANTO TOMÁS».
 *
 * UNA SOLA, IGUAL EN TODAS LAS LISTAS. Antes cada pantalla lo escribía a su
 * manera (una píldora gris bajo el RUT, texto suelto bajo el plan, nada en
 * Pagos) y en el mesón no se sabía dónde mirar. Va SIEMPRE junto al plan,
 * porque es lo que explica su precio: «¿por qué este paga $25.000?».
 *
 * Azul y en mayúsculas chicas: se distingue de los estados (verde, rojo,
 * amarillo) y del plan (texto normal) sin gritar más que ninguno.
 */
export default function Convenio({ nombre, className = '' }) {
    if (! nombre) {
        return null;
    }

    return (
        <span
            title={`Convenio ${nombre}`}
            className={`inline-flex max-w-32 items-center rounded-full border border-info/40 bg-info/10 px-1.5 py-px align-middle text-[0.65rem] font-medium uppercase tracking-wide leading-4 text-info ${className}`}
        >
            <span className="truncate">{nombre}</span>
        </span>
    );
}
