const { app, BrowserWindow, WebContentsView, shell, Menu } = require('electron');
const path = require('node:path');

/**
 * El panel de PRO GYM y WhatsApp Web en una sola ventana.
 *
 * POR QUÉ ESTO EXISTE. WhatsApp Web no se puede meter dentro de una página:
 * manda «frame-ancestors https://*.whatsapp.com», y el navegador bloquea
 * cualquier intento de mostrarlo dentro del panel. No es un problema de este
 * sistema ni se arregla con más código.
 *
 * Aquí no hay página dentro de página: son DOS vistas hermanas dentro de la
 * misma ventana, cada una con su propio sitio, como si fueran dos ventanas
 * pegadas. Es lo mismo que hacen Rambox o Ferdi, y no toca ninguna protección
 * de WhatsApp: se carga tal cual, con su sesión guardada en este equipo.
 *
 * LA SESIÓN SE GUARDA: se escanea el QR una vez y queda, como en el navegador.
 * Vive en la carpeta de datos de esta aplicación, en este computador, y no
 * viaja a ninguna parte.
 */

/** Dónde vive el panel. Se puede cambiar al publicarlo con su dominio. */
const PANEL = process.env.PROGYM_URL || 'http://127.0.0.1:8000/panel';

const WHATSAPP = 'https://web.whatsapp.com/';

/** Cuánto ocupa WhatsApp, de 0 a 1. Un tercio deja las tablas legibles. */
const ANCHO_WHATSAPP = 0.34;

/** Alto de la barrita de arriba, con los botones de la ventana. */
const BARRA = 0;

function crearVentana() {
    const ventana = new BrowserWindow({
        width: 1600,
        height: 950,
        minWidth: 1100,
        minHeight: 700,
        title: 'PRO GYM',
        backgroundColor: '#0b0b0d',
    });

    const panel = new WebContentsView();
    const whatsapp = new WebContentsView({
        webPreferences: {
            // Su propia sesión, guardada entre arranques: el QR se escanea una
            // vez. Aparte de la del panel para que ni una toque a la otra.
            partition: 'persist:whatsapp',
        },
    });

    ventana.contentView.addChildView(panel);
    ventana.contentView.addChildView(whatsapp);

    panel.webContents.loadURL(PANEL);
    whatsapp.webContents.loadURL(WHATSAPP);

    /*
     * Las dos vistas, una al lado de la otra.
     *
     * Se recolocan en cada cambio de tamaño porque las vistas no saben de CSS:
     * sus medidas van en píxeles y hay que darlas a mano.
     */
    const acomodar = () => {
        const { width, height } = ventana.getContentBounds();
        const anchoWhatsapp = Math.max(380, Math.round(width * ANCHO_WHATSAPP));

        panel.setBounds({ x: 0, y: BARRA, width: width - anchoWhatsapp, height: height - BARRA });
        whatsapp.setBounds({
            x: width - anchoWhatsapp,
            y: BARRA,
            width: anchoWhatsapp,
            height: height - BARRA,
        });
    };

    ventana.on('resize', acomodar);
    acomodar();

    /*
     * La ventana se muestra y ya.
     *
     * `ready-to-show` NO sirve aquí: ese aviso lo manda el contenido propio de
     * la ventana, y esta no tiene —lo que se ve son las dos vistas de dentro—.
     * Esperándolo, la aplicación arrancaba con los procesos corriendo y sin que
     * apareciera nada en pantalla.
     */
    /*
     * LO QUE NO ES DEL SISTEMA SE ABRE EN EL NAVEGADOR.
     *
     * Un enlace a Google Maps, a la web pública o a un PDF no tiene por qué
     * reemplazar el panel dentro de la aplicación: ahí no habría cómo volver.
     */
    const fuera = (contenido, permitido) => {
        contenido.setWindowOpenHandler(({ url }) => {
            if (permitido(url)) {
                contenido.loadURL(url);
            } else {
                shell.openExternal(url);
            }

            return { action: 'deny' };
        });

        contenido.on('will-navigate', (evento, url) => {
            if (! permitido(url)) {
                evento.preventDefault();
                shell.openExternal(url);
            }
        });
    };

    // El panel navega por lo suyo; WhatsApp, por lo de WhatsApp.
    fuera(panel.webContents, (url) => url.startsWith(new URL(PANEL).origin));
    fuera(whatsapp.webContents, (url) => /^https:\/\/(web\.)?whatsapp\.com/.test(url));

    return { ventana, panel, whatsapp };
}

/**
 * El menú: lo justo para trabajar y para resolver los dos líos típicos
 * —que el panel se quede pegado y que WhatsApp pida el QR otra vez—.
 */
function menu(vistas) {
    return Menu.buildFromTemplate([
        {
            label: 'PRO GYM',
            submenu: [
                {
                    label: 'Recargar el panel',
                    accelerator: 'F5',
                    click: () => vistas.panel.webContents.reload(),
                },
                {
                    label: 'Volver al inicio del panel',
                    click: () => vistas.panel.webContents.loadURL(PANEL),
                },
                { type: 'separator' },
                {
                    label: 'Recargar WhatsApp',
                    click: () => vistas.whatsapp.webContents.reload(),
                },
                { type: 'separator' },
                { role: 'quit', label: 'Salir' },
            ],
        },
        {
            label: 'Edición',
            submenu: [
                { role: 'copy', label: 'Copiar' },
                { role: 'paste', label: 'Pegar' },
                { role: 'selectAll', label: 'Seleccionar todo' },
            ],
        },
        {
            label: 'Ver',
            submenu: [
                { role: 'zoomIn', label: 'Agrandar' },
                { role: 'zoomOut', label: 'Achicar' },
                { role: 'resetZoom', label: 'Tamaño normal' },
                { type: 'separator' },
                { role: 'togglefullscreen', label: 'Pantalla completa' },
            ],
        },
    ]);
}

app.whenReady().then(() => {
    const vistas = crearVentana();
    Menu.setApplicationMenu(menu(vistas));

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) {
            crearVentana();
        }
    });
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});
