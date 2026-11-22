import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

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
            outDir: 'public/build',
            publicDirectory: 'public_html',  
            buildDirectory: 'build',        
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
