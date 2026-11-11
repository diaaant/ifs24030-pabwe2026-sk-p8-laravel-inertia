import { Link } from '@inertiajs/react';

export default function Pagination({ paginator }) {
    if (!paginator || paginator.last_page <= 1) return null;

    const base = 'rounded-md border px-3 py-1.5 text-sm';
    const on = `${base} border-slate-300 bg-white text-slate-700 hover:bg-slate-50`;
    const off = `${base} border-slate-200 text-slate-400 pointer-events-none`;

    return (
        <nav className="mt-6 flex items-center justify-between" aria-label="Paginasi">
            {paginator.prev_page_url ? <Link href={paginator.prev_page_url} className={on}>‹ Sebelumnya</Link> : <span className={off}>‹ Sebelumnya</span>}
            <span className="text-sm text-slate-500">Halaman {paginator.current_page} dari {paginator.last_page}</span>
            {paginator.next_page_url ? <Link href={paginator.next_page_url} className={on}>Berikutnya ›</Link> : <span className={off}>Berikutnya ›</span>}
        </nav>
    );
}
