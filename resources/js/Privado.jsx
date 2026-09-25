import { Link, usePage } from '@inertiajs/react';
import { createContext, useCallback, useContext, useEffect, useState } from 'react';
import { EyeIcon, EyeOffIcon, LockIcon } from 'lucide-react';

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { puede } from '@/lib/permisos';

/**
 * Tapar las cifras cuando hay gente mirando la pantalla.
 *
 * ESTO NO ES UN PERMISO. Lo que se tapa sigue estando en la página: quien tenga
 * la consola del navegador abierta lo ve igual. Los permisos deciden qué datos
 * LLEGAN a cada pantalla —recepción no recibe los ingresos, punto— y esto es
 * otra cosa: en el mesón se sienta gente detrás de quien atiende, y una cifra
 * de caja en pantalla la lee cualquiera que pase por ahí.
 *
 * Por eso se guarda POR DISPOSITIVO y no por usuario: el mismo administrador
 * quiere las cifras tapadas en el computador del mesón y a la vista en el suyo.
 *
 * APARTE DE ESTO está la decisión de fondo: en Configuración → El dinero en
 * pantalla se puede pedir que las cifras NO se vean, y entonces esto deja de
 * ser un interruptor. Es lo que quiere el dueño que prefiere no tener el
 * dinero delante mientras atiende; el ojo desaparece, porque un botón que
 * destapa lo que se pidió esconder no esconde nada.
 */
const Contexto = createContext({ oculto: true, alternar: () => {}, forzado: false });

// La llave cambió de nombre a propósito al cambiar la regla: antes se guardaba
// «no» para ver, y ahora «si» para tapar. Con la llave vieja, un computador que
// tuviera guardado lo de antes habría seguido tapando sin que nadie entendiera
// por qué.
const CLAVE = 'progym:cifras-tapadas';

/**
 * Empieza DESTAPADO, y se recuerda lo que decida cada computador.
 *
 * Empezaba tapado «por si acaso», y el resultado era otro: quien abría el
 * panel por primera vez —o en otro navegador— veía todas las cifras en
 * puntitos, sin saber que eso se hace a propósito ni que se quita con el ojo
 * de la barra. Daba el panel por roto y no lo decía.
 *
 * Esconder el dinero de verdad es una decisión, y tiene su sitio: Configuración
 * → El dinero en pantalla. El ojo de arriba es para el momento —alguien se
 * acerca al mesón—, y ese momento se tapa en un segundo con la tecla O.
 */
function loQueHabia() {
    try {
        return window.localStorage.getItem(CLAVE) === 'si';
    } catch (e) {
        // Navegador con el almacenamiento capado: se ve, como por defecto.
        return false;
    }
}

export function ProveedorPrivado({ children, forzado = false }) {
    const [oculto, setOculto] = useState(loQueHabia);

    const alternar = useCallback(() => {
        setOculto((antes) => {
            const ahora = ! antes;

            try {
                window.localStorage.setItem(CLAVE, ahora ? 'si' : 'no');
            } catch (e) {
                // Si no se puede recordar, al menos vale para esta sesión.
            }

            return ahora;
        });
    }, []);

    /*
     * Atajo de teclado: la tecla O.
     *
     * Es lo que hace útil esto de verdad. Alguien se acerca por detrás y hay que
     * tapar la pantalla en el acto: buscar un botón con el ratón mientras la
     * persona ya está leyendo llega tarde.
     *
     * Se ignora mientras se escribe, o teclear «cobro» en un buscador taparía
     * las cifras cuatro veces.
     */
    useEffect(() => {
        function alPulsar(e) {
            if (forzado || (e.key !== 'o' && e.key !== 'O')) {
                return;
            }

            if (e.ctrlKey || e.metaKey || e.altKey) {
                return;
            }

            const donde = e.target;
            const etiqueta = donde?.tagName;

            if (
                etiqueta === 'INPUT'
                || etiqueta === 'TEXTAREA'
                || etiqueta === 'SELECT'
                || donde?.isContentEditable
            ) {
                return;
            }

            e.preventDefault();
            alternar();
        }

        window.addEventListener('keydown', alPulsar);

        return () => window.removeEventListener('keydown', alPulsar);
    }, [alternar, forzado]);

    // Forzado manda: lo que se pidió esconder se queda escondido, aunque en
    // este computador alguien hubiera dejado el ojo abierto ayer.
    return (
        <Contexto.Provider value={{ oculto: forzado || oculto, alternar, forzado }}>
            {children}
        </Contexto.Provider>
    );
}

export function usarPrivado() {
    return useContext(Contexto);
}

/**
 * Una cifra que no debería leerse desde tres metros.
 *
 * Se pintan PUNTOS y no un borroso: el borroso deja ver cuántos dígitos tiene,
 * y entre «$9.000» y «$900.000» eso ya dice bastante.
 */
export function Reservado({ children, ancho = 'w-16' }) {
    const { oculto, forzado } = usarPrivado();

    if (! oculto) {
        return children;
    }

    return (
        <span
            className={`inline-block ${ancho} select-none text-center align-middle text-fog`}
            aria-label="Cifra oculta"
            title={forzado ? 'Escondido desde Configuración' : 'Oculto. Pulsa la tecla O para verlo.'}
        >
            ••••
        </span>
    );
}

/** El interruptor, en la barra de arriba. */
export function BotonPrivado({ className = '' }) {
    const { oculto, alternar, forzado } = usarPrivado();
    // Arriba del todo: los hooks no pueden quedar dentro de un `if`, y abajo
    // hay una salida temprana.
    const { auth } = usePage().props;

    /*
     * ESCONDIDO DESDE CONFIGURACIÓN: un candado en el sitio del ojo.
     *
     * Antes aquí no salía nada, y eso dejaba el panel entero en puntitos sin
     * decir por qué ni por dónde se vuelve atrás: parecía roto. El candado lo
     * explica, y a quien puede entrar a Configuración lo lleva al interruptor.
     */
    if (forzado) {
        const icono = <LockIcon className={`size-4 ${className}`} aria-hidden="true" />;

        return (
            <Tooltip>
                <TooltipTrigger asChild>
                    {puede(auth, 'configuracion.ver') ? (
                        <Link
                            href="/panel/configuracion/privacidad"
                            aria-label="Las cifras están escondidas desde Configuración"
                            className="rounded-control p-1.5 text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                        >
                            {icono}
                        </Link>
                    ) : (
                        <span className="rounded-control p-1.5 text-fog">{icono}</span>
                    )}
                </TooltipTrigger>

                <TooltipContent side="bottom">
                    Las cifras están escondidas desde Configuración → Lo que se ve en pantalla
                </TooltipContent>
            </Tooltip>
        );
    }

    const Icono = oculto ? EyeOffIcon : EyeIcon;

    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <button
                    type="button"
                    onClick={alternar}
                    aria-pressed={oculto}
                    aria-label={oculto ? 'Mostrar las cifras' : 'Ocultar las cifras'}
                    className={`rounded-control p-1.5 transition-colors hover:bg-surface-2 ${
                        oculto ? 'text-fog hover:text-chalk' : 'text-volt'
                    } ${className}`}
                >
                    <Icono className="size-4" aria-hidden="true" />
                </button>
            </TooltipTrigger>

            <TooltipContent side="bottom">
                {oculto ? 'Mostrar las cifras' : 'Ocultar las cifras'} · tecla{' '}
                <kbd className="font-mono">O</kbd>
            </TooltipContent>
        </Tooltip>
    );
}
