# PRO GYM en una sola ventana

El panel del gimnasio y WhatsApp Web, lado a lado, en una aplicación de
escritorio para el computador del mesón.

## Por qué existe

WhatsApp Web **no se puede meter dentro de una página**. Su servidor manda esta
cabecera:

```
content-security-policy: frame-ancestors https://*.whatsapp.com
```

Eso le dice al navegador que solo whatsapp.com puede mostrarlo dentro de otra
página. Cualquier intento desde el panel sale en blanco, y no es algo que se
arregle escribiendo más código: es una decisión de WhatsApp.

Aquí no hay página dentro de página. Son **dos vistas hermanas** en la misma
ventana, cada una cargando su sitio como si fueran dos ventanas pegadas. Es lo
mismo que hacen Rambox o Ferdi. No se toca ninguna protección de WhatsApp.

## Cómo se usa

Con el sistema corriendo (MySQL y `php artisan serve`):

```
cd escritorio
npm install    # solo la primera vez; baja Electron, unos 250 MB
npm start
```

La primera vez hay que escanear el QR de WhatsApp una sola vez: la sesión queda
guardada en este computador, en la carpeta de datos de la aplicación, y no viaja
a ninguna parte.

Si el panel se publica con su propio dominio, se le dice a la aplicación dónde
está:

```
set PROGYM_URL=https://panel.progym.cl/panel
npm start
```

## Lo que hay dentro

- `principal.js` — la ventana, las dos vistas y el menú.
- El menú trae lo que resuelve los dos líos típicos: recargar el panel (F5) y
  recargar WhatsApp cuando pide el QR de nuevo.
- Los enlaces que no son del sistema (Google Maps, la web pública) se abren en
  el navegador, no dentro de la aplicación: ahí dentro no habría cómo volver.

## Lo que falta

Empaquetarla como `.exe` con icono, para que se abra con doble clic sin pasar
por la consola. Se hace con `electron-builder`, y conviene dejarlo para cuando
la ventana ya esté como se quiere.
