import { Link } from '@inertiajs/react';
import { formatDate } from './format';

/** Daftar artikel read-only (admin). */
export default function PostRows({ posts, showAuthor = false }) {
    return (
        <ul className="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
            {posts.length === 0 && <li className="px-5 py-12 text-center text-sm text-slate-500">Tidak ada artikel.</li>}
            {posts.map((p) => (
                <li key={p.id} className="flex items-center gap-4 px-4 py-3">
                    <div className="min-w-0 flex-1">
                        <p className="truncate font-medium text-slate-900">{p.title}</p>
                        <p className="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                            <span className="rounded bg-slate-100 px-1.5 py-0.5 font-medium text-slate-700">{p.category}</span>
                            <span className={`rounded px-1.5 py-0.5 font-medium ${p.is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                                {p.is_published ? 'Dipublikasikan' : 'Draf'}
                            </span>
                            {showAuthor && p.user && <span>oleh {p.user.name} (@{p.user.username})</span>}
                            <span>{formatDate(p.updated_at)}</span>
                        </p>
                    </div>
                    {p.is_published && (
                        <Link href={`/articles/${p.slug}`} className="text-sm font-medium text-indigo-800 hover:underline">Lihat publik</Link>
                    )}
                </li>
            ))}
        </ul>
    );
}
