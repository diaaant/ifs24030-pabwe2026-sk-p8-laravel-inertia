import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { dashboardLayout } from '../../Layouts/AppLayout';
import Icon from '../../Components/Icon';
import PostRows from '../../Components/PostRows';
import Pagination from '../../Components/Pagination';
import { inputClass } from '../../Components/format';

export default function UserPostList({ owner, posts, stats, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    return (
        <>
            <Head title={`Artikel ${owner.name}`} />
            <Link href="/admin/users" className="text-sm font-medium text-indigo-800 hover:underline">‹ Semua pengguna</Link>
            <div className="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="font-serif text-3xl font-bold text-indigo-950">Artikel {owner.name}</h1>
                    <p className="text-sm text-slate-600">@{owner.username} · {stats.total} artikel ({stats.published} dipublikasikan, {stats.draft} draf) · hanya baca</p>
                </div>
                <a href={`/admin/users/${owner.id}/posts/export`} className="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <Icon name="download" className="h-4 w-4" /> Ekspor Excel
                </a>
            </div>

            <form onSubmit={(e) => { e.preventDefault(); router.get(`/admin/users/${owner.id}/posts`, { search }, { preserveState: true, replace: true }); }} className="my-5 flex gap-2">
                <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel…" className={inputClass} aria-label="Cari artikel" />
                <button type="submit" className="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Cari</button>
            </form>

            <PostRows posts={posts.data} />
            <Pagination paginator={posts} />
        </>
    );
}
UserPostList.layout = dashboardLayout;
