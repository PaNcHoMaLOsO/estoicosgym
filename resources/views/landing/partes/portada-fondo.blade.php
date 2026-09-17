{{--
    El fondo de la portada, que va pasando solo: vídeo, foto, vídeo, foto, sin
    parar. La lista la arma el controlador.

    HAY UNA SOLA CAPA DE VÍDEO Y UNA DE FOTO, y se les va cambiando la fuente.
    Con una capa por escena el navegador se descargaría todos los vídeos de
    golpe nada más entrar, que es justo lo que no puede pasar en una portada.

    El recorte del vídeo se hace aquí, al reproducirlo: empieza en DESDE y a los
    DURA segundos se pasa a la siguiente escena. Así se prueba el trozo que
    interesa sin volver a exportar el archivo. El recorte de verdad —el que
    además baja el peso— hay que hacerlo sobre el vídeo.
--}}
@php
    $escenas = collect($fondoPortada ?? []);
    $primeraFoto = $escenas->firstWhere('tipo', 'foto');
    $cartel = $primeraFoto['src'] ?? $escenas->first()['src'] ?? null;
@endphp

@if($escenas->isEmpty())
    <div class="absolute inset-0 bg-linear-to-br from-pg-negro via-pg-carbon to-pg-grafito" aria-hidden="true"></div>
@else
    <div class="absolute inset-0" aria-hidden="true"
         data-portada-fondo
         data-escenas="{{ $escenas->toJson() }}">

        {{-- La foto se ve desde el primer momento: el vídeo tarda en llegar y
             una portada en negro mientras carga es peor que no tener vídeo. --}}
        <img class="portada-capa portada-foto h-full w-full object-cover"
             src="{{ $primeraFoto['src'] ?? $cartel }}" alt="" fetchpriority="high">

        <video class="portada-capa portada-video h-full w-full object-cover"
               style="opacity: 0"
               muted playsinline preload="none"
               @if($cartel) poster="{{ $cartel }}" @endif></video>

        {{-- La capa oscura, más cerrada que con la foto sola: sobre imagen en
             movimiento el titular se pierde si no se apaga el fondo. --}}
        <div class="absolute inset-0 bg-pg-negro/35"></div>
        <div class="absolute inset-0 bg-linear-to-t from-pg-negro via-pg-negro/80 to-pg-negro/40"></div>
    </div>

    @if($escenas->count() > 1)
        <script>
            (function () {
                const fondo = document.querySelector('[data-portada-fondo]');

                if (! fondo) {
                    return;
                }

                // Quien pidió menos movimiento se queda con la primera foto,
                // quieta: un fondo que cambia solo en bucle es justo lo que esa
                // preferencia pide no ver.
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                let escenas = [];

                try {
                    escenas = JSON.parse(fondo.dataset.escenas || '[]');
                } catch (e) {
                    return;
                }

                if (escenas.length < 2) {
                    return;
                }

                const imagen = fondo.querySelector('.portada-foto');
                const video = fondo.querySelector('.portada-video');

                // El trozo del vídeo que se enseña, y lo que dura cada foto.
                const VIDEO_DESDE = 3;
                const VIDEO_DURA = 9000;
                const FOTO_DURA = 6000;

                let actual = -1;
                let reloj = null;

                function mostrar(capa) {
                    imagen.style.opacity = capa === imagen ? '1' : '0';
                    video.style.opacity = capa === video ? '1' : '0';
                }

                function programar(espera) {
                    clearTimeout(reloj);
                    reloj = setTimeout(siguiente, espera);
                }

                function ponerVideo(escena) {
                    const arrancar = () => {
                        // Si el vídeo es más corto que el punto de entrada, se
                        // ve desde el principio en vez de no verse.
                        if (video.duration && video.duration > VIDEO_DESDE + 1) {
                            video.currentTime = VIDEO_DESDE;
                        }

                        const reproduciendo = video.play();

                        if (! reproduciendo) {
                            mostrar(video);
                            programar(VIDEO_DURA);

                            return;
                        }

                        reproduciendo.then(() => {
                            mostrar(video);
                            programar(VIDEO_DURA);
                        }).catch(() => {
                            // El navegador no deja reproducir solo: se salta el
                            // vídeo y sigue con las fotos.
                            siguiente();
                        });
                    };

                    if (video.dataset.puesto === escena.src) {
                        arrancar();

                        return;
                    }

                    video.dataset.puesto = escena.src;
                    video.src = escena.src;
                    video.addEventListener('loadeddata', arrancar, { once: true });
                    video.addEventListener('error', siguiente, { once: true });
                    video.load();
                }

                function ponerFoto(escena) {
                    // Se carga antes de enseñarla: cambiando el src a pelo se ve
                    // el hueco mientras baja.
                    const previa = new Image();

                    previa.onload = () => {
                        imagen.src = escena.src;
                        mostrar(imagen);
                        video.pause();
                        programar(FOTO_DURA);
                    };

                    previa.onerror = siguiente;
                    previa.src = escena.src;
                }

                function siguiente() {
                    actual = (actual + 1) % escenas.length;

                    const escena = escenas[actual];

                    if (escena.tipo === 'video') {
                        ponerVideo(escena);

                        return;
                    }

                    ponerFoto(escena);
                }

                // Con la pestaña de fondo no se gasta datos ni batería en un
                // vídeo que nadie está mirando.
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        clearTimeout(reloj);
                        video.pause();

                        return;
                    }

                    siguiente();
                });

                siguiente();
            })();
        </script>
    @endif
@endif
