{{--
    Diseño base de las pantallas de acceso: entrar, recuperar la clave,
    crear una nueva y el código de verificación.

    UNA SOLA HOJA DE ESTILOS para las cuatro. Antes cada pantalla traía la
    suya —entre 500 y 1.200 líneas, con templos griegos, cipreses y una luna
    animada— y cambiar un color significaba cambiarlo cuatro veces.

    Es oscura a propósito aunque el panel tenga tema claro: el logotipo de
    PRO GYM es plateado y está hecho para fondo negro. Sobre blanco, el
    plateado se pierde.
--}}
@php
    // Si la base no respondiera, el login tiene que abrir igual.
    $gimnasio = rescue(fn () => \App\Support\Ajustes::obtener('gimnasio.nombre'), null, false) ?: 'PRO GYM';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('titulo') | {{ $gimnasio }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/progym-isotipo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        /* Los mismos valores que el panel (resources/css/tokens.css, tema oscuro). */
        :root {
            --negro: #0a0a0b;
            --carbon: #121214;
            --grafito: #1a1a1e;
            --linea: #2a2a2f;
            --linea-fuerte: #3d3d44;
            --tiza: #f2f2f4;
            --niebla: #a0a0a9;
            --apagado: #6a6a72;
            --rojo: #dd2a32;
            --rojo-oscuro: #b81e25;
            --rojo-claro: #ef4a51;
            --ok: #4ecb92;
            --peligro: #ff8a7a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: var(--negro);
            color: var(--tiza);
            font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .acceso { min-height: 100vh; display: grid; grid-template-columns: 1fr; }

        /* ---- La marca, a la izquierda (solo en pantallas anchas) ---- */
        .acceso-marca {
            display: none;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(ellipse at 25% 115%, rgba(221, 42, 50, 0.30), transparent 55%),
                linear-gradient(160deg, #161618 0%, var(--negro) 65%);
            border-right: 1px solid var(--linea);
        }
        .acceso-marca::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, transparent, var(--rojo), transparent);
        }
        .acceso-marca img {
            width: min(460px, 78%);
            height: auto;
            filter: drop-shadow(0 24px 48px rgba(0, 0, 0, 0.65));
        }
        .acceso-marca p {
            margin-top: 32px;
            font-family: 'Oswald', sans-serif;
            font-size: 13px;
            letter-spacing: 0.32em;
            text-transform: uppercase;
            color: var(--niebla);
        }

        /* ---- El formulario ---- */
        .acceso-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            background: var(--carbon);
        }
        .acceso-caja { width: 100%; max-width: 400px; }
        .acceso-logo-movil { display: block; width: 190px; margin: 0 auto 32px; }
        .acceso-logo-movil img { display: block; width: 100%; height: auto; }

        @media (min-width: 960px) {
            .acceso { grid-template-columns: 1.1fr 1fr; }
            .acceso-marca {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 48px;
            }
            .acceso-logo-movil { display: none; }
        }

        .cabecera { margin-bottom: 28px; }
        .cabecera .icono {
            width: 48px; height: 48px;
            display: grid; place-items: center;
            margin-bottom: 18px;
            border-radius: 12px;
            background: rgba(221, 42, 50, 0.12);
            color: var(--rojo-claro);
            font-size: 20px;
        }
        .cabecera h1 {
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 30px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .cabecera p { margin-top: 6px; color: var(--niebla); font-size: 14px; line-height: 1.5; }

        .alerta {
            display: flex; gap: 10px; align-items: flex-start;
            margin-bottom: 18px; padding: 12px 14px;
            border: 1px solid; border-radius: 10px;
            font-size: 14px; line-height: 1.45;
        }
        .alerta i { margin-top: 2px; }
        .alerta-error { background: rgba(255, 138, 122, 0.08); border-color: rgba(255, 138, 122, 0.35); color: var(--peligro); }
        .alerta-ok { background: rgba(78, 203, 146, 0.08); border-color: rgba(78, 203, 146, 0.35); color: var(--ok); }
        .alerta-dev { background: rgba(221, 42, 50, 0.08); border-color: rgba(221, 42, 50, 0.35); color: var(--rojo-claro); }

        .campo { margin-bottom: 18px; }
        .campo label { display: block; margin-bottom: 7px; font-size: 13px; font-weight: 500; }
        .entrada { position: relative; }
        .icono-campo {
            position: absolute; left: 15px; top: 50%;
            transform: translateY(-50%);
            color: var(--apagado); font-size: 14px;
            pointer-events: none;
        }
        .control {
            width: 100%; height: 48px;
            padding: 0 46px 0 42px;
            background: var(--grafito);
            border: 1px solid var(--linea);
            border-radius: 10px;
            color: var(--tiza);
            font: inherit; font-size: 15px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .control::placeholder { color: var(--apagado); }
        .control:focus { outline: none; border-color: var(--rojo); box-shadow: 0 0 0 3px rgba(221, 42, 50, 0.18); }
        .control.is-invalid { border-color: var(--peligro); }
        .ver-clave {
            position: absolute; right: 6px; top: 50%;
            transform: translateY(-50%);
            width: 36px; height: 36px;
            border: 0; border-radius: 8px;
            background: none; color: var(--niebla);
            cursor: pointer;
        }
        .ver-clave:hover, .ver-clave:focus-visible { color: var(--tiza); }
        .error-campo { display: flex; gap: 6px; align-items: center; margin-top: 6px; font-size: 13px; color: var(--peligro); }

        .opciones {
            display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px;
            margin: 4px 0 24px; font-size: 14px;
        }
        .recordar { display: flex; align-items: center; gap: 8px; color: var(--niebla); cursor: pointer; }
        .recordar input { width: 16px; height: 16px; accent-color: var(--rojo); }

        .enlace { color: var(--niebla); text-decoration: none; transition: color 0.15s; }
        .enlace:hover, .enlace:focus-visible { color: var(--tiza); }
        .volver { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 22px; font-size: 14px; }

        .boton {
            position: relative;
            width: 100%; height: 50px;
            border: 0; border-radius: 10px;
            background: var(--rojo); color: #fff;
            font-family: 'Oswald', sans-serif; font-weight: 600; font-size: 16px;
            letter-spacing: 0.08em; text-transform: uppercase;
            cursor: pointer;
            transition: background 0.15s, opacity 0.15s;
        }
        .boton:hover:not(:disabled) { background: var(--rojo-oscuro); }
        .boton:focus-visible { outline: 2px solid var(--rojo-claro); outline-offset: 3px; }
        .boton:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-text { display: inline-flex; align-items: center; gap: 10px; }
        .spinner {
            display: none;
            position: absolute; inset: 0; margin: auto;
            width: 22px; height: 22px;
            border: 2px solid rgba(255, 255, 255, 0.35);
            border-top-color: #fff; border-radius: 50%;
            animation: girar 0.7s linear infinite;
        }
        .boton.loading .btn-text { visibility: hidden; }
        .boton.loading .spinner { display: block; }
        @keyframes girar { to { transform: rotate(360deg); } }

        .acceso-pie { margin-top: 40px; font-size: 12px; color: var(--apagado); text-align: center; }

        /* ---- Código de verificación ---- */
        .telefono {
            display: inline-flex; align-items: center; gap: 8px;
            margin-top: 14px; padding: 8px 14px;
            background: var(--grafito); border: 1px solid var(--linea); border-radius: 999px;
            font-size: 14px;
        }
        .canal { color: var(--niebla); font-size: 12px; }
        .codigo { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 18px; }
        .code-input {
            width: 100%; max-width: 56px; height: 62px;
            background: var(--grafito);
            border: 1px solid var(--linea); border-radius: 10px;
            color: var(--tiza); text-align: center;
            font-family: 'Oswald', sans-serif; font-size: 26px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .code-input:focus { outline: none; border-color: var(--rojo); box-shadow: 0 0 0 3px rgba(221, 42, 50, 0.18); }
        .code-input.filled { border-color: var(--linea-fuerte); }
        .code-input.error { border-color: var(--peligro); animation: temblar 0.35s; }
        .code-input:disabled { opacity: 0.4; }
        @keyframes temblar { 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }
        .reloj { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 22px; font-size: 13px; color: var(--niebla); }
        #timer.expired { color: var(--peligro); }
        #resendLink.disabled { opacity: 0.4; pointer-events: none; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
            .boton.loading .spinner { border-top-color: rgba(255, 255, 255, 0.35); }
        }
    </style>
</head>
<body>
    <main class="acceso">
        <section class="acceso-marca" aria-hidden="true">
            <picture>
                <source srcset="{{ asset('images/progym-logo.webp') }}" type="image/webp">
                <img src="{{ asset('images/progym-logo.png') }}" alt="" width="1096" height="495">
            </picture>
            <p>Panel de administración</p>
        </section>

        <section class="acceso-panel">
            <div class="acceso-caja">
                <a href="{{ url('/') }}" class="acceso-logo-movil" aria-label="{{ $gimnasio }}, ir al sitio">
                    <picture>
                        <source srcset="{{ asset('images/progym-logo.webp') }}" type="image/webp">
                        <img src="{{ asset('images/progym-logo.png') }}" alt="{{ $gimnasio }}" width="1096" height="495">
                    </picture>
                </a>

                @yield('contenido')
            </div>

            <p class="acceso-pie">© {{ date('Y') }} {{ $gimnasio }} · Profesionales del deporte</p>
        </section>
    </main>

    @yield('scripts')
</body>
</html>
