import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/reports-manager.css',
                'resources/js/app.js',
                'resources/js/reports-manager.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
