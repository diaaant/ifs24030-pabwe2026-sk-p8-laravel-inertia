import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { publicLayout } from '../../Layouts/AppLayout';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import { formatDate, inputClass } from '../../Components/format';

function ArticleCard({ post }) {
    return (
        <article className="group flex flex-col border-b border-slate-200 pb-6">
            <Link href={`/articles/${post.slug}`} className="block overflow-hidden rounded-lg bg-slate-200">
                {post.cover_url ? (
                    <img src={post.cover_url} alt="" className="aspect-[16/10] w-full object-cover transition group-hover:scale-[1.02]" />
                ) : (
                    <div className="flex aspect-[16/10] items-center justify-center bg-gradient-to-br from-indigo-900 to-indigo-600 px-6 text-center font-serif text-xl font-semibold text-white/90">
                        {post.category}
                    </div>
                )}
            </Link>
            <p className="mt-3 text-sm font-medium text-indigo-800">{post.category}</p>
            <h3 className="mt-1 font-serif text-xl font-bold leading-snug text-slate-900">
                <Link href={`/articles/${post.slug}`} className="hover:text-indigo-800">{post.title}</Link>
            </h3>
            {post.excerpt && <p className="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-600">{post.excerpt}</p>}
            <p className="mt-3 text-xs text-slate-500">{post.user?.name} · {formatDate(post.published_at)}</p>
        </article>
    );
}

export default function PostIndex({ hero, posts, filters, categories }) {
    const [search, setSearch] = useState(filters.search || '');

    const go = (params) =>
        router.get('/articles', { search, category: filters.category, ...params }, { preserveState: true, replace: true });

    return (
        <>
            <Head title={hero ? 'Beranda' : 'Artikel'} />

            {hero && (
                <section className="border-b border-slate-200 bg-indigo-950 text-white">
                    <div className="mx-auto max-w-6xl px-4 py-16 sm:py-24">
                        <h1 className="max-w-3xl font-serif text-4xl font-bold leading-tight sm:text-6xl">Tulisan yang layak dibaca sampai habis.</h1>
                        <p className="mt-5 max-w-xl text-lg text-indigo-200">
                            BlogVerse Pro mempertemukan penulis dan pembaca. Baca bebas tanpa akun, atau mulai menerbitkan artikel Anda sendiri.
                        </p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            <Link href="/login" className="inline-flex items-center gap-2 rounded-md bg-white px-5 py-2.5 text-sm font-semibold text-indigo-950 hover:bg-indigo-50">
                                <Icon name="login" className="h-4 w-4" /> Masuk
                            </Link>
                            <Link href="/register" className="inline-flex items-center gap-2 rounded-md border border-white/40 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10">
                                <Icon name="userPlus" className="h-4 w-4" /> Daftar Penulis
                            </Link>
                        </div>
                    </div>
                </section>
            )}

            <div className="mx-auto max-w-6xl px-4 pt-10">
                <form onSubmit={(e) => { e.preventDefault(); go({}); }} className="flex flex-col gap-3 sm:flex-row">
                    <div className="relative flex-1">
                        <Icon name="search" className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari judul atau isi artikel…"
                            className={`${inputClass} pl-9`} aria-label="Cari artikel" />
                    </div>
                    <button type="submit" className="rounded-md bg-indigo-800 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-900">Cari</button>
                </form>

                <div className="mt-4 flex flex-wrap gap-2" role="group" aria-label="Filter kategori">
                    <button type="button" onClick={() => go({ category: '' })}
                        className={`rounded-full border px-3 py-1 text-sm ${!filters.category ? 'border-indigo-800 bg-indigo-800 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'}`}>
                        Semua
                    </button>
                    {categories.map((c) => (
                        <button key={c.value} type="button" onClick={() => go({ category: c.value })}
                            className={`rounded-full border px-3 py-1 text-sm ${filters.category === c.value ? 'border-indigo-800 bg-indigo-800 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'}`}>
                            {c.value}
                        </button>
                    ))}
                </div>

                {posts.data.length === 0 ? (
                    <p className="py-20 text-center text-slate-500">Belum ada artikel yang cocok. Coba kata kunci atau kategori lain.</p>
                ) : (
                    <div className="mt-8 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                        {posts.data.map((p) => <ArticleCard key={p.id} post={p} />)}
                    </div>
                )}

                <Pagination paginator={posts} />
            </div>
        </>
    );
}
PostIndex.layout = publicLayout;
