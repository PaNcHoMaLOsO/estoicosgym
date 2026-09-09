import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Une clases de Tailwind resolviendo los conflictos por la ultima que gana.
 *
 * Es el ayudante que esperan todos los componentes copiados de shadcn/ui: si
 * un componente trae `px-2` y quien lo usa pasa `px-4`, sin esto quedarian las
 * dos y ganaria la que Tailwind hubiera emitido antes en la hoja, que no es la
 * que escribio quien lo usa.
 */
export function cn(...entradas) {
    return twMerge(clsx(entradas));
}
