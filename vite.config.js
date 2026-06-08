import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/index.js',
                'resources/js/bootstrap.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    apexcharts: ['apexcharts'],
                    alpine: ['alpinejs', '@alpinejs/persist'],
                    flatpickr: ['flatpickr'],
                    dropzone: ['dropzone'],
                },
            },
        },
    },
});