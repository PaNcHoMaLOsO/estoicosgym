import { useSyncExternalStore } from 'react';

/**
 * Tema claro/oscuro del PANEL.
 *
 * Tres estados y no dos: sistema, claro y oscuro. "Sistema" no es lo mismo que
 * "claro": bodega se opera de noche y a primera hora, y quien no ha elegido
 * nada quiere seguir a su equipo, no quedarse clavado en lo que hubiera el dia
 * que entro por primera vez.
 *
 * CLAVE PROPIA, distinta de la de la tienda (`estoicos_theme`). Compartirla
 * ataria dos decisiones que no tienen por que coincidir —se mira el escaparate
 * de dia y se factura de noche— y derivarla del nombre de la marca haria que al
 * renombrar la tienda todo el mundo perdiera su preferencia de golpe.
 */
export const CLAVE_TEMA = 'estoicosgym_panel_theme';

const CONSULTA_OSCURO = '(prefers-color-scheme: dark)';

/** Lo que el usuario ELIGIO: 'sistema' | 'claro' | 'oscuro'. */
export function preferenciaDeTema() {
    if (typeof document === 'undefined') {
        return 'sistema';
    }

    const puesto = document.documentElement.dataset.theme;

    if (puesto === 'dark') {
        return 'oscuro';
    }

    if (puesto === 'light') {
        return 'claro';
    }

    return 'sistema';
}

/** Lo que se esta VIENDO ahora mismo, haya eleccion explicita o no. */
export function temaActivo() {
    const elegido = preferenciaDeTema();

    if (elegido !== 'sistema') {
        return elegido;
    }

    return window.matchMedia(CONSULTA_OSCURO).matches ? 'oscuro' : 'claro';
}

/**
 * Fija la preferencia y la recuerda.
 *
 * Escribir (o borrar) `data-theme` es lo que avisa a todos los suscriptores:
 * nadie tiene que emitir ademas un evento propio. Y borrarlo de verdad importa,
 * porque `:root[data-theme="light"]` gana al `@media (prefers-color-scheme)`:
 * dejar el atributo puesto en "light" no es volver al sistema, es fijar claro.
 */
export function fijarTema(preferencia) {
    const raiz = document.documentElement;

    if (preferencia === 'sistema') {
        delete raiz.dataset.theme;
    } else {
        raiz.dataset.theme = preferencia === 'oscuro' ? 'dark' : 'light';
    }

    try {
        localStorage.setItem(CLAVE_TEMA, preferencia);
    } catch {
        /* modo privado o almacenamiento bloqueado: vale para esta sesion */
    }
}

/**
 * Aplica lo guardado. Se llama desde resources/js/app.jsx, en el modulo de
 * entrada y antes de montar React, que es lo mas pronto que se puede desde
 * JavaScript empaquetado.
 *
 * NO ES SUFICIENTE PARA MATAR EL DESTELLO: el modulo de Vite va diferido, asi
 * que el navegador puede pintar un fotograma con el tema del sistema antes de
 * que esto corra. Lo que lo mata del todo es un script EN LINEA y bloqueante en
 * resources/views/app.blade.php, como el que ya tiene el storefront en
 * Base.astro. Queda apuntado como pendiente: esa plantilla no es de este
 * bloque.
 */
export function aplicarTemaGuardado() {
    try {
        const guardado = localStorage.getItem(CLAVE_TEMA);

        if (guardado === 'claro' || guardado === 'oscuro') {
            document.documentElement.dataset.theme = guardado === 'oscuro' ? 'dark' : 'light';
        }
    } catch {
        /* sin almacenamiento se sigue al sistema, que es el defecto */
    }
}

function suscribir(avisar) {
    // Dos vias porque el tema cambia de dos maneras: el usuario elige (muta
    // data-theme) o cambia la preferencia del sistema sin eleccion explicita.
    const observador = new MutationObserver(avisar);
    observador.observe(document.documentElement, { attributeFilter: ['data-theme'] });

    const consulta = window.matchMedia(CONSULTA_OSCURO);
    consulta.addEventListener('change', avisar);

    return () => {
        observador.disconnect();
        consulta.removeEventListener('change', avisar);
    };
}

/** Hook: la preferencia elegida, reactiva venga el cambio de donde venga. */
export function usarPreferenciaDeTema() {
    return useSyncExternalStore(suscribir, preferenciaDeTema, () => 'sistema');
}
