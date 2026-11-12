import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    server: {
        watch: { ignored: ['**/storage/framework/views/**'] },
    },
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                // Memecah file JS besar menjadi beberapa bagian (Vendor)
                manualChunks: {
                    'vendor-react': ['react', 'react-dom', '@inertiajs/react'],
                    'vendor-editor': ['@toast-ui/editor'],
                }
            }
        }
    }
});