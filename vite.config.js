import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: false,
            scope: '/',
            manifestFilename: 'manifest.webmanifest',
            includeAssets: ['icons/icon-192.png', 'icons/icon-512.png'],
            manifest: {
                name: 'Fanta F1',
                short_name: 'Fanta F1',
                description: 'Fantasy Formula 1 - crea la tua lega e sfida i tuoi amici',
                theme_color: '#0b0b0c',
                background_color: '#0b0b0c',
                display: 'standalone',
                start_url: '/',
                icons: [
                    { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,png,svg,woff2}'],
                navigateFallback: null,
                runtimeCaching: [
                    {
                        urlPattern: ({ request }) => request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: { cacheName: 'pages-cache' },
                    },
                    {
                        urlPattern: ({ request }) => ['style', 'script', 'font', 'image'].includes(request.destination),
                        handler: 'CacheFirst',
                        options: { cacheName: 'assets-cache' },
                    },
                ],
            },
        }),
    ],
});
