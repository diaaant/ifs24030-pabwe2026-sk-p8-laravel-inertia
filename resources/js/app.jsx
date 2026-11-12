import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

// HAPUS { eager: true } agar halaman dimuat secara lazy (hanya saat dibuka)
const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    title: (title) => (title ? `${title} — BlogVerse Pro` : 'BlogVerse Pro'),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.jsx`];
        if (!page) throw new Error(`Halaman tidak ditemukan: ${name}`);
        return page(); // Panggil fungsi ini untuk memuat halaman secara dinamis
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#4f46e5', // Tambahkan progress bar bawaan Inertia saat pindah halaman
    },
});