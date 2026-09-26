import '../css/app.css';
// Fotogramas del dialogo, el cajon y el menu. Va DESPUES de app.css para que
// gane a cualquier `animation` que Tailwind pudiera emitir para lo mismo.
import './ui.css';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

import MarcoConfiguracion from './components/MarcoConfiguracion';
import { escucharFallas } from './lib/avisarFallas';
import Layout from './Layout';

const paginas = import.meta.glob('./pages/**/*.jsx');

const NOMBRE = 'PRO GYM';

/**
 * Las pantallas que viven dentro de Configuración: se ven con su menú de
 * secciones a la izquierda, que no se desmonta al pasar de una a otra.
 */
const DE_CONFIGURACION = /^(Configuracion(\/.+)?|Web\/.+|Usuarios\/.+|Papelera|Notificaciones\/Plantillas)$/;

createInertiaApp({
    title: (titulo) => (titulo ? `${titulo} · ${NOMBRE}` : NOMBRE),

    resolve: async (nombre) => {
        const pagina = await resolvePageComponent(`./pages/${nombre}.jsx`, paginas);

        // El Layout se aplica aqui y no dentro de cada pagina: asi la barra
        // lateral no se vuelve a montar al navegar y conserva su scroll.
        pagina.default.layout ??= DE_CONFIGURACION.test(nombre)
            ? (hoja) => (
                  <Layout>
                      <MarcoConfiguracion>{hoja}</MarcoConfiguracion>
                  </Layout>
              )
            : (hoja) => <Layout>{hoja}</Layout>;

        return pagina;
    },

    setup({ el, App, props }) {
        // LO PRECARGADO NO SOBREVIVE A UN CAMBIO. Las pantallas se traen al
        // pasar el mouse (menú, filas de las tablas); si después se cobra, se
        // guarda o se borra algo, lo traído antes ya no vale: se descarta.
        router.on('finish', (evento) => {
            if (String(evento.detail.visit.method).toLowerCase() !== 'get') {
                router.flushAll();
            }
        });

        // El panel solo se ve con sesión: lo que se rompa aquí va al registro.
        escucharFallas();
        createRoot(el).render(<App {...props} />);
    },

    progress: {
        // El rojo de marca; es el mismo valor que --app-volt del tema claro.
        color: '#d81f26',
    },
});
