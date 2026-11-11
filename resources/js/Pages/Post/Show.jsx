import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { dashboardLayout } from '../../Layouts/AppLayout';
import Icon from '../../Components/Icon';
import Spinner from '../../Components/Spinner';
import MarkdownViewer from '../../Components/MarkdownViewer';
import { formatDate } from '../../Components/format';

export default function Show({ post }) {
    const { errors } = usePage().props;
    const [busy, setBusy] = useState(null);

    const run = (key, fn) => {
        setBusy(key);
        fn({ preserveScroll: true, onFinish: () => setBusy(null) });
    };

    const upload = (file) => {
        if (!file) return;
        router.post(`/dashboard/posts/${post.id}/cover`, { cover: file }, {
            forceFormData: true,
            preserveScroll: true,
            onStart: () => setBusy('upload'),
            onFinish: () => setBusy(null),
        });
    };

    return (
        <>
            <Head title={post.title} />
            <Link href="/dashboard/posts" className="text-sm font-medium text-indigo-800 hover:underline">‹ Kembali ke daftar</Link>

            <div className="mt-4 flex flex-wrap items-start justify-between gap-4">
                <div className="min-w-0">
                    <h1 className="font-serif text-3xl font-bold text-indigo-950">{post.title}</h1>
                    <p className="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                        <span className="rounded bg-slate-200 px-2 py-0.5 font-medium">{post.category}</span>
                        <span className={`rounded px-2 py-0.5 font-medium ${post.is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                            {post.is_published ? 'Dipublikasikan' : 'Draf'}
                        </span>
                        <span>Diperbarui {formatDate(post.updated_at)}</span>
                    </p>
                </div>
                <div className="flex gap-2">
                    {post.is_published && (
                        <Link href={`/articles/${post.slug}`} className="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <Icon name="eye" className="h-4 w-4" /> Lihat publik
                        </Link>
                    )}
                    <button type="button" disabled={busy === 'toggle'}
                        onClick={() => run('toggle', (o) => router.patch(`/dashboard/posts/${post.id}/publish`, {}, o))}
                        className="inline-flex items-center gap-2 rounded-md bg-indigo-800 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-900 disabled:opacity-70">
                        {busy === 'toggle' ? <Spinner /> : <Icon name="swap" className="h-4 w-4" />}
                        {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                    </button>
                </div>
            </div>

            <section className="mt-6 rounded-xl border border-slate-200 bg-white p-5">
                <h2 className="text-sm font-semibold text-slate-700">Foto sampul</h2>
                {post.cover_url && <img src={post.cover_url} alt="" className="mt-3 max-h-72 rounded-lg object-cover" />}
                <div className="mt-3 flex flex-wrap items-center gap-3">
                    <label className="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        {busy === 'upload' ? <Spinner /> : <Icon name="upload" className="h-4 w-4" />}
                        {post.cover_url ? 'Ganti foto' : 'Unggah foto'}
                        <input type="file" accept="image/*" className="hidden" onChange={(e) => upload(e.target.files[0])} />
                    </label>
                    {post.cover_url && (
                        <button type="button" disabled={busy === 'remove'}
                            onClick={() => run('remove', (o) => router.delete(`/dashboard/posts/${post.id}/cover`, o))}
                            className="inline-flex items-center gap-2 rounded-md border border-red-200 px-3.5 py-2 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-70">
                            {busy === 'remove' ? <Spinner /> : <Icon name="trash" className="h-4 w-4" />} Hapus foto
                        </button>
                    )}
                </div>
                {errors?.cover && <p className="mt-2 text-sm text-red-600">{errors.cover}</p>}
            </section>

            {post.excerpt && <p className="mt-6 font-serif text-lg italic text-slate-600">{post.excerpt}</p>}

            <article className="mt-6 rounded-xl border border-slate-200 bg-white p-6">
                <MarkdownViewer value={post.content} />
            </article>
        </>
    );
}
Show.layout = dashboardLayout;
