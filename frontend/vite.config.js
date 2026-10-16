import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            publicDirectory: '../backend/public',
            refresh: true,
        }),
    ],
    build: {
        emptyOutDir: true,
    },
    server: {
        watch: {
            ignored: ['../backend/storage/framework/views/**'],
        },
    },
});
