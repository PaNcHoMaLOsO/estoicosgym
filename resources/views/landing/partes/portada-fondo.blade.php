{{--
    El fondo de la portada, que va pasando solo: las fotos del gimnasio y, si hay
    vídeos subidos, intercalados entre ellas. La lista la arma el controlador.

    HAY DOS CAPAS DE FOTO Y UNA DE VÍDEO. Con una sola capa de foto, pasar de una
    foto a otra era cambiarle la fuente de golpe: se veía el salto y no había
    transición ninguna (solo la había al pasar de vídeo a foto, que sí son capas
    distintas). Con dos capas, la que entra aparece ENCIMA de la que sale, así
    que en medio no se ve ni un hueco ni un parpadeo negro.

    Una capa por escena no sirve: el navegador se descargaría todos los vídeos de
    golpe nada más entrar, que es lo que no puede pasar en una portada.

    El vídeo se recorta al reproducirlo: empieza en DESDE y a los DURA segundos
    se pasa a la siguiente escena. El recorte de verdad, el que además baja el
    peso del archivo, hay que hacerlo sobre el vídeo.
--}}
@php
    $escenas = collect($fondoPortada ?? []);
    $primeraFoto = $escenas->firstWhere('tipo', 'foto');
    $cartel = $primeraFoto['src'] ?? $escenas->first()['src'] ?? null;
@endphp

@if($escenas->isEmpty())
    <div class="absolute inset-0 bg-linear-to-br from-pg-negro via-pg-carbon to-pg-grafito" aria-hidden="true"></div>
@else
    {{-- Cuál de las escenas es la que ya se está viendo, para que la primera
         transición no sea de una foto a esa misma foto. --}}
    <div class="absolute inset-0" aria-hidden="true"
         data-portada-fondo
         data-primera="{{ (int) $escenas->search(fn (array $e) => $e['src'] === $cartel) }}"
         data-escenas="{{ $escenas->toJson() }}">

        {{-- La primera foto se ve desde el primer momento: si se esperara a que
             cargue algo, la portada arrancaría en negro. --}}
        <img class="portada-capa portada-foto h-full w-full object-cover"
             src="{{ $cartel }}" alt="" fetchpriority="high">
        <img class="portada-capa portada-foto h-full w-full object-cover" style="opacity: 0" src="" alt="">

        <video class="portada-capa portada-video h-full w-full object-cover"
               style="opacity: 0"
               muted playsinline preload="none"
               @if($cartel) poster="{{ $cartel }}" @endif></video>

        {{-- La capa oscura, para que el titular se lea por encima de cualquier
             foto, clara u oscura. VA POR DELANTE DE LAS FOTOS a propósito: las
             capas que se cruzan se pisan entre ellas, y sin esto la foto que
             entraba tapaba el oscurecido y el título se volvía ilegible. --}}
        <div class="absolute inset-0 z-[3] bg-pg-negro/30"></div>
        {{-- En pantalla ancha el texto ya no va encima de la foto sino a su
             izquierda, sobre negro: la foto puede ir casi sin oscurecer. --}}
        <div class="absolute inset-0 z-[3] bg-linear-to-t from-pg-negro via-pg-negro/75 to-pg-negro/35"></div>
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

                const fotos = fondo.querySelectorAll('.portada-foto');
                const video = fondo.querySelector('.portada-video');

                // Lo que dura cada foto, y el trozo del vídeo que se enseña.
                const FOTO_DURA = 6500;
                const VIDEO_DESDE = 3;
                const VIDEO_DURA = 9000;

                let actual = Number(fondo.dataset.primera || 0);
                let encima = 0;
                let altura = 1;
                let reloj = null;

                /** La capa que entra se pone por encima y aparece; la de debajo se apaga después. */
                function cruzar(entra) {
                    const salen = [fotos[0], fotos[1], video].filter(capa => capa !== entra);

                    // Solo dos alturas, 1 y 2: subiendo de uno en uno sin parar,
                    // al rato las fotos acababan por encima del oscurecido y del
                    // propio título.
                    salen.forEach(capa => { capa.style.zIndex = '1'; });
                    entra.style.zIndex = '2';
                    entra.style.opacity = '1';

                    // Las de debajo se apagan cuando la de arriba ya tapa: si se
                    // apagaran a la vez, en medio se vería el fondo negro.
                    setTimeout(() => {
                        salen.forEach(capa => {
                            capa.style.opacity = '0';

                            if (capa === video) {
                                video.pause();
                            }
                        });
                    }, 1400);
                }

                function programar(espera) {
                    clearTimeout(reloj);
                    reloj = setTimeout(siguiente, espera);
                }

                function ponerFoto(escena) {
                    const entra = fotos[encima === 0 ? 1 : 0];
                    const previa = new Image();

                    // Se carga antes de enseñarla: cambiando la fuente a pelo se
                    // vería el hueco mientras baja.
                    previa.onload = () => {
                        entra.src = escena.src;
                        cruzar(entra);
                        encima = encima === 0 ? 1 : 0;
                        programar(FOTO_DURA);
                    };

                    previa.onerror = siguiente;
                    previa.src = escena.src;
                }

                function ponerVideo(escena) {
                    const arrancar = () => {
                        // Si el vídeo es más corto que el punto de entrada, se ve
                        // desde el principio en vez de no verse.
                        if (video.duration && video.duration > VIDEO_DESDE + 1) {
                            video.currentTime = VIDEO_DESDE;
                        }

                        const reproduciendo = video.play();

                        if (! reproduciendo) {
                            cruzar(video);
                            programar(VIDEO_DURA);

                            return;
                        }

                        reproduciendo.then(() => {
                            cruzar(video);
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

                function siguiente() {
                    actual = (actual + 1) % escenas.length;

                    const escena = escenas[actual];

                    if (escena.tipo === 'video') {
                        ponerVideo(escena);

                        return;
                    }

                    ponerFoto(escena);
                }

                // Con la pestaña de fondo no se gasta datos ni batería en algo
                // que nadie está mirando.
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        clearTimeout(reloj);
                        video.pause();

                        return;
                    }

                    siguiente();
                });

                programar(FOTO_DURA);
            })();
        </script>
    @endif
@endif
