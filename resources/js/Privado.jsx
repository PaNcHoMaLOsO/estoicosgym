import { createContext, useCallback, useContext, useEffect, useState } from 'react';
import { EyeIcon, EyeOffIcon } from 'lucide-react';

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

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

const CLAVE = 'progym:cifras-ocultas';

/** Empieza tapado: si alguien ya sabe que le miran, el descuido no es opción. */
function loQueHabia() {
    try {
        return window.localStorage.getItem(CLAVE) !== 'no';
    } catch (e) {
        // Navegador con el almacenamiento capado: se tapa igual.
        return true;
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

    // Sin nada que alternar no hay botón: dejarlo puesto sin efecto es peor
    // que no tenerlo, porque quien lo pulse creerá que el panel no responde.
    if (forzado) {
        return null;
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
