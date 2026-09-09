import '../css/app.css';
// Fotogramas del dialogo, el cajon y el menu. Va DESPUES de app.css para que
// gane a cualquier `animation` que Tailwind pudiera emitir para lo mismo.
import './ui.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

import Layout from './Layout';

const paginas = import.meta.glob('./pages/**/*.jsx');

const NOMBRE = 'Estoicos Gym';

createInertiaApp({
    title: (titulo) => (titulo ? `${titulo} · ${NOMBRE}` : NOMBRE),

    resolve: async (nombre) => {
        const pagina = await resolvePageComponent(`./pages/${nombre}.jsx`, paginas);

        // El Layout se aplica aqui y no dentro de cada pagina: asi la barra
        // lateral no se vuelve a montar al navegar y conserva su scroll.
        pagina.default.layout ??= (hoja) => <Layout>{hoja}</Layout>;

        return pagina;
    },

    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },

    progress: {
        color: '#b08b3e',
    },
});
