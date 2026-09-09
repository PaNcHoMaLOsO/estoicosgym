import { fileURLToPath, URL } from 'node:url';

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            // app.jsx es el panel nuevo (Inertia + React). Las vistas Blade que
            // siguen vivas no pasan por Vite: cargan AdminLTE por su cuenta.
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    // Los componentes copiados de shadcn/ui importan como `@/…`. Sin este alias
    // habria que reescribir a mano cada uno que se agregue mas adelante.
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },

    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
