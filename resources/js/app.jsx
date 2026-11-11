import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });

createInertiaApp({
    title: (title) => (title ? `${title} — BlogVerse Pro` : 'BlogVerse Pro'),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.jsx`];
        if (!page) throw new Error(`Halaman tidak ditemukan: ${name}`);
        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: false, // diganti LoadingIndicator kustom
});
