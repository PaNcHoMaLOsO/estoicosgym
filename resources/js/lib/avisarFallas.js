/**
 * Avisa al registro de fallas cuando una pantalla del panel se rompe en el
 * navegador.
 *
 * Sin esto, un error de JavaScript dejaba la pantalla en blanco o un botón que
 * no hacía nada, y nadie en el sistema se enteraba: solo quien estaba delante.
 * Ahora queda en Configuración → Panel → Registro de fallas.
 *
 * Cada falla se avisa una vez por visita, y nunca más de diez: un error dentro
 * de un bucle no puede llenar el registro.
 */
const avisadas = new Set();

function avisar({ mensaje, tipo, archivo, linea, traza }) {
    const clave = `${mensaje}|${archivo}|${linea}`;

    if (! mensaje || avisadas.has(clave) || avisadas.size >= 10) {
        return;
    }

    avisadas.add(clave);

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/fallas/navegador', {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(token ? { 'X-CSRF-TOKEN': token } : {}),
        },
        body: JSON.stringify({
            mensaje: String(mensaje).slice(0, 2000),
            tipo: tipo ? String(tipo).slice(0, 200) : null,
            archivo: archivo ? String(archivo).slice(0, 500) : null,
            linea: Number.isFinite(linea) ? linea : null,
            traza: traza ? String(traza).slice(0, 8000) : null,
            pantalla: window.location.href.slice(0, 500),
        }),
    }).catch(() => {
        // Si ni siquiera esto sale, no hay a quién avisar.
    });
}

export function escucharFallas() {
    window.addEventListener('error', (evento) => {
        // Una imagen que no cargó también dispara «error», sin mensaje: no es
        // una falla del panel.
        if (! evento.message) {
            return;
        }

        avisar({
            mensaje: evento.message,
            tipo: evento.error?.name,
            archivo: evento.filename,
            linea: evento.lineno,
            traza: evento.error?.stack,
        });
    });

    window.addEventListener('unhandledrejection', (evento) => {
        const razon = evento.reason;

        avisar({
            mensaje: razon?.message ?? String(razon),
            tipo: razon?.name ?? 'Promesa rechazada',
            traza: razon?.stack,
        });
    });
}
