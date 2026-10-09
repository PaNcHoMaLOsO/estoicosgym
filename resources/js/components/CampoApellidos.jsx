import { useEffect, useState } from 'react';

import { Campo, Texto } from '@/components/Campo';
import { juntarApellidos, separarApellidos } from '@/lib/socio';

/**
 * LOS DOS APELLIDOS EN UN SOLO CAMPO.
 *
 * Eran dos casillas, y el materno además iba escondido en «más datos»: en el
 * mesón la persona dice «Pérez Soto» de corrido y había que partirlo a mano, o
 * el materno quedaba sin escribir. Ahora se escriben seguidos y se separan
 * solos (con los compuestos: «De la Fuente», «San Martín»). Abajo se ve cómo
 * quedaron, para corregirlo con la persona delante. La base sigue guardando
 * paterno y materno por separado: nada más cambia.
 */
export default function CampoApellidos({ paterno, materno, alCambiar, errores = {}, autoComplete }) {
    const [texto, setTexto] = useState(() => juntarApellidos(paterno, materno));

    // Si los apellidos llegan de afuera (lo escrito en el buscador, un reinicio
    // del formulario), el campo los muestra.
    useEffect(() => {
        const actual = separarApellidos(texto);

        if (actual.paterno !== (paterno ?? '') || actual.materno !== (materno ?? '')) {
            setTexto(juntarApellidos(paterno, materno));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [paterno, materno]);

    const separados = separarApellidos(texto);
    const error = errores.apellido_paterno ?? errores.apellido_materno;

    return (
        <Campo
            etiqueta="Apellidos"
            nombre="apellidos"
            error={error}
            requerido
            ayuda={separados.paterno ? `Paterno: ${separados.paterno} · Materno: ${separados.materno || '—'}` : 'Los dos seguidos: Pérez Soto'}
        >
            <Texto
                nombre="apellidos"
                valor={texto}
                alCambiar={(v) => {
                    setTexto(v);
                    alCambiar(separarApellidos(v));
                }}
                error={error}
                placeholder="Pérez Soto"
                autoComplete={autoComplete}
            />
        </Campo>
    );
}
