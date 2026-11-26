import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Fonts are loaded via direct CDN <link> tags in the layout head (matching
// the Stitch reference) rather than laravel-vite-plugin's Bunny Fonts
// self-hosting helper — that plugin fetches and processes every font
// weight on first dev-server boot, which hung indefinitely in this Docker
// Desktop + Windows bind-mount environment.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // The E2E run builds into its own folder so it never leaves stale assets for the dev site.
            buildDirectory: process.env.VITE_BUILD_DIR || 'build',
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
