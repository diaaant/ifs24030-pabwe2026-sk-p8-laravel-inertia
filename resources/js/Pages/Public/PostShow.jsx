import { Head, Link } from '@inertiajs/react';
import { publicLayout } from '../../Layouts/AppLayout';
import Icon from '../../Components/Icon';
import MarkdownViewer from '../../Components/MarkdownViewer';
import { formatDate } from '../../Components/format';

export default function PostShow({ post, related }) {
    return (
        <>
            <Head title={post.title} />
            <article className="mx-auto max-w-3xl px-4 pt-10">
                <Link href="/articles" className="text-sm font-medium text-indigo-800 hover:underline">‹ Semua artikel</Link>
                <p className="mt-6 text-sm font-medium text-indigo-800">{post.category}</p>
                <h1 className="mt-1 font-serif text-4xl font-bold leading-tight text-slate-900 sm:text-5xl">{post.title}</h1>
                {post.excerpt && <p className="mt-4 font-serif text-xl leading-relaxed text-slate-600">{post.excerpt}</p>}
                <p className="mt-4 text-sm text-slate-500">Oleh {post.user?.name} · {formatDate(post.published_at)}</p>

                {post.cover_url && <img src={post.cover_url} alt="" className="mt-8 w-full rounded-lg object-cover" />}

                <div className="mt-8"><MarkdownViewer value={post.content} /></div>

                <aside className="mt-14 rounded-xl border border-indigo-200 bg-indigo-50 p-6">
                    <h2 className="font-serif text-xl font-bold text-indigo-950">Punya gagasan untuk dibagikan?</h2>
                    <p className="mt-1 text-sm text-indigo-900/80">Bergabunglah dengan komunitas penulis BlogVerse Pro dan terbitkan tulisan Anda.</p>
                    <div className="mt-4 flex flex-wrap gap-3">
                        <Link href="/login" className="inline-flex items-center gap-2 rounded-md bg-indigo-800 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-900">
                            <Icon name="login" className="h-4 w-4" /> Masuk
                        </Link>
                        <Link href="/register" className="inline-flex items-center gap-2 rounded-md border border-indigo-300 bg-white px-4 py-2 text-sm font-semibold text-indigo-900 hover:bg-indigo-100">
                            <Icon name="userPlus" className="h-4 w-4" /> Daftar
                        </Link>
                    </div>
                </aside>
            </article>

            {related.length > 0 && (
                <section className="mx-auto mt-14 max-w-3xl px-4">
                    <h2 className="font-serif text-2xl font-bold">Bacaan terkait</h2>
                    <ul className="mt-4 divide-y divide-slate-200 border-y border-slate-200">
                        {related.map((r) => (
                            <li key={r.id} className="py-3">
                                <Link href={`/articles/${r.slug}`} className="font-medium text-slate-900 hover:text-indigo-800">{r.title}</Link>
                                <p className="text-xs text-slate-500">{r.user?.name} · {formatDate(r.published_at)}</p>
                            </li>
                        ))}
                    </ul>
                </section>
            )}
        </>
    );
}
PostShow.layout = publicLayout;
