import { defineConfig } from 'vite'

export default defineConfig({
    build: {
        lib: {
            entry: 'resources/js/app.js',
            name: 'EstóicosGym',
            formats: ['umd'],
        },
        outDir: 'public/build',
        rollupOptions: {
            output: {
                entryFileNames: '[name].js',
            },
        },
    },
})
