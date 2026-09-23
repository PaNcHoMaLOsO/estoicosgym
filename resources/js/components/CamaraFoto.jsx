import { CameraIcon, RefreshCwIcon, VideoOffIcon } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import Nota from '@/components/Nota';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';

/**
 * Sacarle la foto al socio con la cámara del mesón.
 *
 * EN EL MESÓN NO HAY ARCHIVOS. La ficha se hace con la persona delante: pedirle
 * una foto por correo, o sacarla con el teléfono y pasarla al PC, es una tarea
 * para después, y «después» quiere decir que la mitad de las fichas se quedan
 * sin cara. Con una cámara USB apuntando al mostrador son dos clics.
 *
 * LA FOTO NO SALE DEL EQUIPO hasta que se guarda la ficha: se toma en el
 * navegador, se achica ahí mismo y recién entonces viaja al sistema. Y la
 * cámara se APAGA al cerrar —se sueltan las pistas de vídeo—, porque una luz
 * encendida en el mostrador después de terminar asusta, con razón.
 *
 * Si el equipo no tiene cámara, o el navegador no da permiso, no se rompe nada:
 * se dice qué pasó y queda el botón de elegir un archivo de siempre.
 */
export default function CamaraFoto({ abierta, alCerrar, alSacar, nombre }) {
    const video = useRef(null);
    const pista = useRef(null);
    const [camaras, setCamaras] = useState([]);
    const [elegida, setElegida] = useState('');
    const [error, setError] = useState(null);
    const [tomando, setTomando] = useState(false);

    // Encender la cámara al abrir y APAGARLA siempre al salir: sin esto la luz
    // del aparato se queda prendida hasta que se cierra el navegador entero.
    useEffect(() => {
        if (! abierta) {
            return undefined;
        }

        let vigente = true;

        const encender = async () => {
            setError(null);

            try {
                const senal = await navigator.mediaDevices.getUserMedia({
                    video: elegida
                        ? { deviceId: { exact: elegida } }
                        : { width: { ideal: 1280 }, height: { ideal: 960 } },
                    audio: false,
                });

                if (! vigente) {
                    senal.getTracks().forEach((t) => t.stop());

                    return;
                }

                pista.current = senal;

                if (video.current) {
                    video.current.srcObject = senal;
                }

                /*
                 * Las cámaras se preguntan DESPUÉS de dar permiso: antes, el
                 * navegador las devuelve sin nombre —«camera 1», «camera 2»— y
                 * no hay forma de saber cuál es la del mostrador.
                 */
                const aparatos = (await navigator.mediaDevices.enumerateDevices())
                    .filter((d) => d.kind === 'videoinput');

                if (vigente) {
                    setCamaras(aparatos);
                }
            } catch (e) {
                if (! vigente) {
                    return;
                }

                setError(
                    e?.name === 'NotAllowedError'
                        ? 'El navegador no dio permiso para usar la cámara. Búscala en la barra de direcciones y permítela.'
                        : e?.name === 'NotFoundError'
                          ? 'No se encontró ninguna cámara conectada a este equipo.'
                          : 'No se pudo encender la cámara.',
                );
            }
        };

        encender();

        return () => {
            vigente = false;
            pista.current?.getTracks().forEach((t) => t.stop());
            pista.current = null;
        };
    }, [abierta, elegida]);

    /**
     * La foto, ya achicada.
     *
     * Se guarda a 800 px de ancho y como JPG: una cámara moderna entrega
     * imágenes de varios megas, y la ficha solo necesita reconocer una cara. Se
     * achica aquí y no en el servidor para que ese peso no viaje siquiera.
     */
    function sacar() {
        const v = video.current;

        if (! v || ! v.videoWidth) {
            return;
        }

        setTomando(true);

        const ancho = Math.min(800, v.videoWidth);
        const alto = Math.round((v.videoHeight / v.videoWidth) * ancho);
        const lienzo = document.createElement('canvas');
        lienzo.width = ancho;
        lienzo.height = alto;
        lienzo.getContext('2d').drawImage(v, 0, 0, ancho, alto);

        lienzo.toBlob(
            (blob) => {
                setTomando(false);

                if (! blob) {
                    setError('No se pudo tomar la foto. Inténtalo de nuevo.');

                    return;
                }

                const limpio = String(nombre ?? 'socio').trim().toLowerCase().replace(/[^a-z0-9]+/g, '-') || 'socio';

                alSacar(new File([blob], `${limpio}.jpg`, { type: 'image/jpeg' }));
                alCerrar();
            },
            'image/jpeg',
            0.85,
        );
    }

    return (
        <Dialog open={abierta} onOpenChange={(v) => (v ? null : alCerrar())}>
            <DialogContent className="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>Sacar la foto</DialogTitle>
                    <DialogDescription>
                        La foto se toma aquí y solo se guarda al guardar la ficha.
                    </DialogDescription>
                </DialogHeader>

                {error ? (
                    <Nota Icono={VideoOffIcon}>{error}</Nota>
                ) : (
                    <div className="overflow-hidden rounded-panel border border-line bg-black">
                        {/* `muted` y `playsInline` no son adorno: sin ellos el
                            navegador se niega a reproducir sin que alguien
                            apriete play, y se ve un recuadro negro. */}
                        <video ref={video} autoPlay playsInline muted className="h-auto w-full" />
                    </div>
                )}

                {/* El selector solo cuando hay más de una: con la cámara del
                    portátil y la del mostrador conectadas, hay que poder decir
                    cuál es cuál. */}
                {camaras.length > 1 ? (
                    <label className="flex items-center gap-2 text-sm text-fog">
                        <RefreshCwIcon className="size-4" aria-hidden="true" />
                        Cámara
                        <select
                            value={elegida || camaras[0]?.deviceId || ''}
                            onChange={(e) => setElegida(e.target.value)}
                            className="min-w-0 flex-1 rounded-control border border-line bg-surface px-2 py-1.5 text-sm text-chalk focus:outline-none"
                        >
                            {camaras.map((c, i) => (
                                <option key={c.deviceId} value={c.deviceId}>
                                    {c.label || `Cámara ${i + 1}`}
                                </option>
                            ))}
                        </select>
                    </label>
                ) : null}

                <div className="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        onClick={alCerrar}
                        className="rounded-control px-4 py-2 text-sm text-fog transition-colors hover:bg-surface-2 hover:text-chalk"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        onClick={sacar}
                        disabled={Boolean(error) || tomando}
                        className="inline-flex items-center gap-1.5 rounded-control bg-volt px-4 py-2 text-sm font-medium text-on-volt transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        <CameraIcon className="size-4" aria-hidden="true" />
                        {tomando ? 'Sacando…' : 'Sacar la foto'}
                    </button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
