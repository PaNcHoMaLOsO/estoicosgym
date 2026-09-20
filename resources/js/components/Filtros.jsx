import { router } from "@inertiajs/react";

/**
 * Los filtros de una lista como botones con su cantidad.
 *
 * «12 vencidos» arriba de la tabla era un texto; ahora es un botón que muestra
 * a esos 12. El cero se ve apagado para que no llame la atención.
 *
 * `opciones`: [{ valor, etiqueta, cantidad, tono }] con tono 'ok' | 'warn' |
 * 'danger' para las que piden hacer algo. El valor '' es «todos».
 */
const TONOS = {
  ok: "text-ok",
  warn: "text-warn",
  danger: "text-danger",
};

export default function Filtros({
  ruta,
  actual = "",
  opciones,
  extra = {},
  nombre = "filtro",
}) {
  function elegir(valor) {
    router.get(
      ruta,
      { ...extra, ...(valor ? { [nombre]: valor } : {}) },
      { preserveScroll: true, preserveState: true, replace: true },
    );
  }

  return (
    <div
      role="group"
      aria-label="Filtrar la lista"
      className="flex flex-wrap gap-1.5"
    >
      {opciones.map(({ valor, etiqueta, cantidad, tono, aparte }) => {
        const elegido = (actual ?? "") === valor;
        const apagado = cantidad === 0 && !elegido;

        return (
          <span key={valor || "todos"} className="contents">
            {/* Lo que va «aparte» (los pases diarios) se separa con
                        una raya: no es un estado más de los socios. */}
            {aparte ? (
              <span
                className="mx-1 w-px self-stretch bg-line"
                aria-hidden="true"
              />
            ) : null}
            <button
              type="button"
              aria-pressed={elegido}
              onClick={() => elegir(valor)}
              className={`inline-flex items-center gap-1.5 rounded-pill border px-3 py-1 text-sm transition-colors ${
                elegido
                  ? "border-line-strong bg-surface-2 font-medium text-chalk"
                  : "border-line text-fog hover:border-line-strong hover:text-chalk"
              } ${apagado ? "opacity-50" : ""}`}
            >
              {etiqueta}
              {cantidad !== undefined ? (
                <span
                  className={`tabular-nums ${cantidad > 0 && tono ? TONOS[tono] : ""}`}
                >
                  {cantidad}
                </span>
              ) : null}
            </button>
          </span>
        );
      })}
    </div>
  );
}
