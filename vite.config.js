import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Lora', {
                    weights: [400, 500, 600, 700],
                    styles: ['normal', 'italic'],
                    subsets: ['latin', 'latin-ext', 'vietnamese'],
                    preload: [{ weight: 600 }],
                }),
                bunny('Manrope', {
                    weights: [400, 500, 600, 700],
                    subsets: ['latin', 'latin-ext', 'vietnamese'],
                    preload: [{ weight: 400 }, { weight: 600 }],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
