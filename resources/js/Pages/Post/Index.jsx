import { useEffect, useRef, useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { dashboardLayout } from '../../Layouts/AppLayout';
import Icon from '../../Components/Icon';
import Spinner from '../../Components/Spinner';
import KebabMenu from '../../Components/KebabMenu';
import FullScreenModal from '../../Components/FullScreenModal';
import ConfirmDialog from '../../Components/ConfirmDialog';
import MarkdownEditor from '../../Components/MarkdownEditor';
import Pagination from '../../Components/Pagination';
import { formatDate, inputClass } from '../../Components/format';

const EMPTY = { title: '', category: 'Umum', excerpt: '', content: '', is_published: false, cover: null };

function StatCard({ label, value, tone }) {
    return (
        <div className={`rounded-xl border p-5 ${tone}`}>
            <p className="text-sm font-medium opacity-80">{label}</p>
            <p className="mt-1 font-serif text-4xl font-bold">{value}</p>
        </div>
    );
}

function Field({ label, error, children, htmlFor }) {
    return (
        <div>
            <label htmlFor={htmlFor} className="mb-1 block text-sm font-medium text-slate-700">{label}</label>
            {children}
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

export default function Index({ posts, stats, filters, categories }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState(filters.search || '');
    const [editing, setEditing] = useState(null); // null | 'new' | post
    const [deleting, setDeleting] = useState(null);
    const [deleteBusy, setDeleteBusy] = useState(false);
    const [busyId, setBusyId] = useState(null);
    const [aiOpen, setAiOpen] = useState(false);
    const [aiItems, setAiItems] = useState([]);
    const [aiSaving, setAiSaving] = useState(false);
    const [importing, setImporting] = useState(false);
    const fileInput = useRef(null);

    const form = useForm(EMPTY);
    const ai = useForm({ count: 3, topic: '' });

    // Hasil OpenAI datang lewat flash session pada redirect.
    useEffect(() => {
        if (flash?.ai_results) setAiItems(flash.ai_results.map((i) => ({ ...i, selected: true })));
    }, [flash?.ai_results]);

    const openCreate = () => { form.setData(EMPTY); form.clearErrors(); setEditing('new'); };
    const openEdit = (p) => {
        form.setData({ title: p.title, category: p.category, excerpt: p.excerpt || '', content: p.content, is_published: p.is_published, cover: null });
        form.clearErrors();
        setEditing(p);
    };
    const closeForm = () => setEditing(null);

    const submit = (e) => {
        e.preventDefault();
        const opts = { forceFormData: true, preserveScroll: true, onSuccess: closeForm };
        if (editing === 'new') form.post('/dashboard/posts', opts);
        else form.put(`/dashboard/posts/${editing.id}`, opts);
    };

    const confirmDelete = () => {
        setDeleteBusy(true);
        router.delete(`/dashboard/posts/${deleting.id}`, {
            preserveScroll: true,
            onFinish: () => { setDeleteBusy(false); setDeleting(null); },
        });
    };

    const toggle = (p) => {
        setBusyId(p.id);
        router.patch(`/dashboard/posts/${p.id}/publish`, {}, { preserveScroll: true, onFinish: () => setBusyId(null) });
    };

    const doSearch = (e) => {
        e.preventDefault();
        router.get('/dashboard/posts', { search }, { preserveState: true, replace: true });
    };

    const onImport = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setImporting(true);
        router.post('/dashboard/posts/import', { file }, {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => { setImporting(false); fileInput.current.value = ''; },
        });
    };

    const generate = (e) => {
        e.preventDefault();
        ai.post('/dashboard/posts/ai-generate', { preserveScroll: true });
    };

    const saveAi = (e) => {
        e.preventDefault();
        const items = aiItems.filter((i) => i.selected).map(({ selected, ...rest }) => rest);
        if (items.length === 0) return;
        setAiSaving(true);
        router.post('/dashboard/posts/ai-save', { items }, {
            onSuccess: () => { setAiOpen(false); setAiItems([]); },
            onFinish: () => setAiSaving(false),
        });
    };

    const closeAi = () => { setAiOpen(false); setAiItems([]); ai.clearErrors(); };

    return (
        <>
            <Head title="Artikel Saya" />

            <div className="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 className="font-serif text-3xl font-bold text-indigo-950">Artikel Saya</h1>
                    <p className="text-sm text-slate-600">Tulis, atur status, dan terbitkan artikel Anda.</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <button type="button" onClick={() => setAiOpen(true)} className="inline-flex items-center gap-2 rounded-md border border-indigo-300 bg-white px-3.5 py-2 text-sm font-medium text-indigo-800 hover:bg-indigo-50">
                        <Icon name="sparkles" className="h-4 w-4" /> Buat dengan AI
                    </button>
                    <a href="/dashboard/posts/export" className="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <Icon name="download" className="h-4 w-4" /> Ekspor Excel
                    </a>
                    <button type="button" onClick={() => fileInput.current.click()} disabled={importing} className="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-70">
                        {importing ? <Spinner /> : <Icon name="upload" className="h-4 w-4" />} Impor Excel
                    </button>
                    <input ref={fileInput} type="file" accept=".xlsx,.xls" className="hidden" onChange={onImport} />
                    <button type="button" onClick={openCreate} className="inline-flex items-center gap-2 rounded-md bg-indigo-800 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-900">
                        <Icon name="plus" className="h-4 w-4" /> Tambah Artikel
                    </button>
                </div>
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-3">
                <StatCard label="Total Artikel" value={stats.total} tone="border-indigo-200 bg-indigo-50 text-indigo-950" />
                <StatCard label="Total Selesai" value={stats.published} tone="border-emerald-200 bg-emerald-50 text-emerald-950" />
                <StatCard label="Belum Selesai" value={stats.draft} tone="border-amber-200 bg-amber-50 text-amber-950" />
            </div>

            <form onSubmit={doSearch} className="mt-6 flex gap-2">
                <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel saya…" className={inputClass} aria-label="Cari artikel" />
                <button type="submit" className="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Cari</button>
            </form>

            <ul className="mt-4 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
                {posts.data.length === 0 && (
                    <li className="px-5 py-12 text-center text-sm text-slate-500">Belum ada artikel. Pilih “Tambah Artikel” untuk menulis yang pertama.</li>
                )}
                {posts.data.map((p) => (
                    <li key={p.id} className="flex items-center gap-4 px-4 py-3">
                        {p.cover_url ? (
                            <img src={p.cover_url} alt="" className="hidden h-12 w-16 shrink-0 rounded object-cover sm:block" />
                        ) : (
                            <div className="hidden h-12 w-16 shrink-0 items-center justify-center rounded bg-slate-100 text-slate-400 sm:flex"><Icon name="doc" /></div>
                        )}
                        <div className="min-w-0 flex-1">
                            <Link href={`/dashboard/posts/${p.id}`} className="block truncate font-medium text-slate-900 hover:text-indigo-800">{p.title}</Link>
                            <p className="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                <span className="rounded bg-slate-100 px-1.5 py-0.5 font-medium text-slate-700">{p.category}</span>
                                <span className={`rounded px-1.5 py-0.5 font-medium ${p.is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                                    {p.is_published ? 'Dipublikasikan' : 'Draf'}
                                </span>
                                <span>{formatDate(p.updated_at)}</span>
                            </p>
                        </div>
                        <button type="button" onClick={() => toggle(p)} disabled={busyId === p.id}
                            className="hidden items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-70 sm:inline-flex">
                            {busyId === p.id ? <Spinner className="h-3.5 w-3.5" /> : <Icon name="swap" className="h-3.5 w-3.5" />}
                            {p.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                        </button>
                        <KebabMenu items={[
                            { label: 'Detail', icon: 'eye', onClick: () => router.visit(`/dashboard/posts/${p.id}`) },
                            { label: 'Ubah', icon: 'pencil', onClick: () => openEdit(p) },
                            { label: 'Hapus', icon: 'trash', danger: true, onClick: () => setDeleting(p) },
                        ]} />
                    </li>
                ))}
            </ul>
            <Pagination paginator={posts} />

            {editing && (
                <FullScreenModal title={editing === 'new' ? 'Tambah Artikel' : 'Ubah Artikel'} onClose={closeForm} onSubmit={submit}
                    processing={form.processing} submitLabel="Simpan">
                    <Field label="Judul" htmlFor="post-title" error={form.errors.title}>
                        <input id="post-title" className={inputClass} value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} />
                    </Field>
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field label="Kategori" htmlFor="post-category" error={form.errors.category}>
                            <select id="post-category" className={inputClass} value={form.data.category} onChange={(e) => form.setData('category', e.target.value)}>
                                {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                            </select>
                        </Field>
                        <Field label="Foto sampul (maks. 2 MB)" htmlFor="post-cover" error={form.errors.cover}>
                            <input id="post-cover" type="file" accept="image/*" className={inputClass} onChange={(e) => form.setData('cover', e.target.files[0] || null)} />
                        </Field>
                    </div>
                    <Field label="Ringkasan (1–2 kalimat)" htmlFor="post-excerpt" error={form.errors.excerpt}>
                        <textarea id="post-excerpt" rows={2} className={inputClass} value={form.data.excerpt} onChange={(e) => form.setData('excerpt', e.target.value)} />
                    </Field>
                    <Field label="Isi artikel" error={form.errors.content}>
                        <MarkdownEditor value={form.data.content} onChange={(v) => form.setData('content', v)} />
                    </Field>
                    <label className="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" checked={form.data.is_published} onChange={(e) => form.setData('is_published', e.target.checked)} />
                        Publikasikan sekarang
                    </label>
                </FullScreenModal>
            )}

            {aiOpen && (
                <FullScreenModal title="Buat Artikel dengan AI" onClose={closeAi} onSubmit={aiItems.length ? saveAi : generate}
                    processing={aiItems.length ? aiSaving : ai.processing}
                    submitLabel={aiItems.length ? 'Simpan sebagai draf' : 'Hasilkan artikel'} submitIcon="sparkles">
                    {aiItems.length === 0 ? (
                        <>
                            <Field label="Jumlah artikel (1–5)" htmlFor="ai-count" error={ai.errors.count}>
                                <input id="ai-count" type="number" min={1} max={5} step={1} className={inputClass}
                                    value={ai.data.count} onChange={(e) => ai.setData('count', e.target.value === '' ? '' : Math.min(5, Math.max(1, Number(e.target.value))))} />
                            </Field>
                            <Field label="Topik (opsional)" htmlFor="ai-topic" error={ai.errors.topic}>
                                <input id="ai-topic" className={inputClass} placeholder="Contoh: kebiasaan belajar efektif" value={ai.data.topic} onChange={(e) => ai.setData('topic', e.target.value)} />
                            </Field>
                            <p className="text-sm text-slate-500">AI akan membuat draf lengkap dengan kategori, ringkasan, dan isi Markdown. Anda bisa memilih mana yang disimpan.</p>
                        </>
                    ) : (
                        <div className="space-y-4">
                            {aiItems.map((it, idx) => (
                                <label key={idx} className="block cursor-pointer rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                                    <span className="flex items-start gap-3">
                                        <input type="checkbox" className="mt-1.5" checked={it.selected}
                                            onChange={(e) => setAiItems(aiItems.map((x, i) => (i === idx ? { ...x, selected: e.target.checked } : x)))} />
                                        <span>
                                            <span className="block font-serif text-lg font-bold">{it.title}</span>
                                            <span className="text-xs font-medium text-indigo-800">{it.category}</span>
                                            <span className="mt-1 block text-sm text-slate-600">{it.excerpt}</span>
                                        </span>
                                    </span>
                                </label>
                            ))}
                        </div>
                    )}
                </FullScreenModal>
            )}

            {deleting && (
                <ConfirmDialog title="Hapus artikel?" message={`“${deleting.title}” akan dihapus permanen beserta foto sampulnya.`}
                    processing={deleteBusy} onConfirm={confirmDelete} onCancel={() => setDeleting(null)} />
            )}
        </>
    );
}
Index.layout = dashboardLayout;
