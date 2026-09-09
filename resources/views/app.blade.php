{{-- Raiz de Inertia para el panel del gimnasio. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- TEMA CLARO/OSCURO DEL PANEL.

         Va aqui arriba, EN LINEA y bloqueante: cualquier modulo se descarga en
         diferido y para cuando corriera ya habria un fotograma pintado con el
         tema equivocado. Quien tenga claro elegido veria un destello oscuro en
         cada carga.

         Solo escribe `data-theme` en el <html>; los colores los pone
         resources/css/tokens.css, que declara los tres estados: `:root`
         (claro), `@media (prefers-color-scheme: dark)` y
         `:root[data-theme="dark"]`.

         Deja `window.temaPanel` para el conmutador de tres estados del menu de
         usuario en Layout.jsx: la preferencia y el anti-parpadeo viven aqui y
         React solo pinta el control. --}}
    <script data-tema-panel>
        (function () {
            /* MISMA clave y MISMO vocabulario que resources/js/lib/tema.js
               ('sistema' | 'claro' | 'oscuro'). Si los dos no coinciden, este
               script pinta un tema y React cree que hay otro elegido. */
            var CLAVE = 'estoicosgym_panel_theme';
            var raiz = document.documentElement;

            try {
                var guardado = localStorage.getItem(CLAVE);

                if (guardado === 'claro' || guardado === 'oscuro') {
                    raiz.dataset.theme = guardado === 'oscuro' ? 'dark' : 'light';
                }
            } catch (e) {
                /* sin almacenamiento se sigue al sistema, que es el defecto */
            }
        })();
    </script>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
