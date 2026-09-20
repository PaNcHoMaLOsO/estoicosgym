/**
 * La cara de un socio, o sus iniciales si no tiene foto.
 *
 * ES PARA RECONOCER A QUIEN ESTA DELANTE. En el mesón la gente no llega
 * diciendo su RUT: llega y saluda. Con doscientas fichas, dos «Camila
 * González» y un apellido mal escrito, la foto resuelve en un vistazo lo que
 * si no son tres preguntas incómodas.
 *
 * SIN FOTO NO SE ROMPE NADA: quien no tenga sale con sus iniciales sobre un
 * color sacado de su propio nombre —el mismo color siempre, para que la ficha
 * se reconozca aunque nunca se le haya tomado una foto—. La foto es una ayuda,
 * no un requisito, y nadie debería quedarse sin poder inscribirse por no
 * querer que le retraten.
 */

import { Dialog, DialogContent, DialogDescription, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

/** Las dos primeras iniciales: nombre y primer apellido. */
function iniciales(nombre) {
    const partes = String(nombre ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (partes.length === 0) {
        return '?';
    }

    // `Array.from` y no `[0]`: con un nombre que empiece por acento, el índice
    // podría partir el carácter por la mitad y salir un rombo negro.
    const letra = (p) => Array.from(p)[0].toUpperCase();

    return partes.length === 1 ? letra(partes[0]) : letra(partes[0]) + letra(partes[1]);
}

/*
 * El color se saca del nombre, no al azar.
 *
 * Al azar cambiaría en cada recarga y dejaría de servir para reconocer. Así,
 * la misma ficha sale siempre del mismo color y la vista lo aprende antes que
 * el nombre.
 */
const COLORES = [
    'bg-[#3b4a5a]',
    'bg-[#4a3b5a]',
    'bg-[#5a4a3b]',
    'bg-[#3b5a4a]',
    'bg-[#5a3b4a]',
    'bg-[#3b3b5a]',
];

function colorDe(nombre) {
    const texto = String(nombre ?? '');
    let suma = 0;

    for (let i = 0; i < texto.length; i += 1) {
        suma = (suma + texto.charCodeAt(i)) % 4093;
    }

    return COLORES[suma % COLORES.length];
}

const TAMANOS = {
    sm: 'size-8 text-[11px]',
    md: 'size-12 text-sm',
    lg: 'size-20 text-xl',
    xl: 'size-28 text-2xl',
};

/**
 * La foto entera, en grande.
 *
 * En el círculo se ve solo el centro de la cara: sirve para reconocer, no para
 * mirar. Al hacer clic se abre completa, sin recortar, para ver bien a quién
 * se tiene delante o para comprobar que se subió la foto correcta.
 */
function FotoAmpliable({ nombre, foto, clases }) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <button
                    type="button"
                    title="Ver la foto en grande"
                    aria-label={`Ver la foto de ${nombre ?? 'socio'} en grande`}
                    className="shrink-0 cursor-zoom-in rounded-full transition-opacity hover:opacity-85 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                >
                    <img src={foto} alt="" loading="lazy" className={clases} />
                </button>
            </DialogTrigger>
            <DialogContent className="w-auto p-2 sm:max-w-[min(90vw,48rem)]">
                <DialogTitle className="px-2 pt-1 pr-10">{nombre}</DialogTitle>
                <DialogDescription className="sr-only">Foto de la ficha</DialogDescription>
                <img
                    src={foto}
                    alt={`Foto de ${nombre ?? 'socio'}`}
                    className="mx-auto max-h-[80dvh] w-auto max-w-full rounded-control object-contain"
                />
            </DialogContent>
        </Dialog>
    );
}

export default function Retrato({ nombre, foto, tamano = 'md', ampliable = false, className = '' }) {
    const medida = TAMANOS[tamano] ?? TAMANOS.md;

    if (foto) {
        // Recortada desde ARRIBA y no desde el centro: en una foto de cuerpo
        // entero o de medio cuerpo la cara está arriba, y centrada salía el pecho.
        const clases = `${medida} shrink-0 rounded-full border border-line object-cover object-top ${className}`;

        if (ampliable) {
            return <FotoAmpliable nombre={nombre} foto={foto} clases={clases} />;
        }

        return (
            <img
                src={foto}
                // El nombre NO va en el alt: si la imagen falla, el navegador
                // lo pinta como texto suelto al lado del nombre ya escrito.
                alt=""
                loading="lazy"
                className={clases}
            />
        );
    }

    return (
        <span
            aria-hidden="true"
            className={`${medida} ${colorDe(nombre)} inline-flex shrink-0 items-center justify-center rounded-full border border-line font-semibold text-chalk select-none ${className}`}
        >
            {iniciales(nombre)}
        </span>
    );
}
