/**
 * Achica una foto en el navegador antes de subirla.
 *
 * La foto de un celular pesa 3 a 6 MB y el servidor no acepta más de 2: había
 * que achicarla en otro programa antes de poder subirla. Aquí se achica sola
 * por el lado más largo; el servidor igual la vuelve a pasar a WebP.
 *
 * Si el navegador no puede leerla, se devuelve tal cual y que responda el
 * servidor.
 */
export default async function achicarFoto(archivo, maximo = 1600) {
    if (! (archivo instanceof File) || ! archivo.type.startsWith('image/')) {
        return archivo;
    }

    try {
        // «from-image» respeta el giro que guarda el celular en la foto.
        const imagen = await createImageBitmap(archivo, { imageOrientation: 'from-image' });
        const escala = Math.min(1, maximo / Math.max(imagen.width, imagen.height));

        // Ya es chica y liviana: no se toca.
        if (escala === 1 && archivo.size <= 1.5 * 1024 * 1024) {
            imagen.close?.();

            return archivo;
        }

        const lienzo = document.createElement('canvas');
        lienzo.width = Math.round(imagen.width * escala);
        lienzo.height = Math.round(imagen.height * escala);
        lienzo.getContext('2d').drawImage(imagen, 0, 0, lienzo.width, lienzo.height);
        imagen.close?.();

        const blob = await new Promise((listo) => lienzo.toBlob(listo, 'image/jpeg', 0.88));

        if (! blob) {
            return archivo;
        }

        const nombre = archivo.name.replace(/\.[^.]+$/, '') + '.jpg';

        return new File([blob], nombre, { type: 'image/jpeg' });
    } catch {
        return archivo;
    }
}
