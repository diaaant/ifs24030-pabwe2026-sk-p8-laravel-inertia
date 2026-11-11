import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { dashboardLayout } from '../../Layouts/AppLayout';
import Icon from '../../Components/Icon';
import KebabMenu from '../../Components/KebabMenu';
import ConfirmDialog from '../../Components/ConfirmDialog';
import Pagination from '../../Components/Pagination';
import { inputClass } from '../../Components/format';

export default function UserList({ users, filters }) {
    const me = usePage().props.auth.user;
    const [search, setSearch] = useState(filters.search || '');
    const [deleting, setDeleting] = useState(null);
    const [busy, setBusy] = useState(false);

    const remove = () => {
        setBusy(true);
        router.delete(`/admin/users/${deleting.id}`, { preserveScroll: true, onFinish: () => { setBusy(false); setDeleting(null); } });
    };

    return (
        <>
            <Head title="Pengguna" />
            <h1 className="font-serif text-3xl font-bold text-indigo-950">Pengguna</h1>
            <p className="text-sm text-slate-600">Kelola akun dan pantau artikel setiap pengguna.</p>

            <form onSubmit={(e) => { e.preventDefault(); router.get('/admin/users', { search }, { preserveState: true, replace: true }); }} className="mt-6 flex gap-2">
                <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari nama atau username…" className={inputClass} aria-label="Cari pengguna" />
                <button type="submit" className="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Cari</button>
            </form>

            <ul className="mt-4 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
                {users.data.map((u) => (
                    <li key={u.id} className="flex items-center gap-4 px-4 py-3">
                        <div className="min-w-0 flex-1">
                            <p className="truncate font-medium">{u.name} <span className="font-normal text-slate-500">@{u.username}</span></p>
                            <p className="mt-0.5 text-xs text-slate-500">
                                <span className={`mr-3 rounded px-1.5 py-0.5 font-medium ${u.role === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700'}`}>{u.role}</span>
                                {u.posts_count} artikel
                            </p>
                        </div>
                        <Link href={`/admin/users/${u.id}/posts`} className="hidden items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 sm:inline-flex">
                            <Icon name="eye" className="h-3.5 w-3.5" /> Artikel
                        </Link>
                        <a href={`/admin/users/${u.id}/posts/export`} className="hidden items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 sm:inline-flex">
                            <Icon name="download" className="h-3.5 w-3.5" /> Ekspor
                        </a>
                        <KebabMenu items={[
                            { label: 'Detail', icon: 'eye', onClick: () => router.visit(`/admin/users/${u.id}/posts`) },
                            ...(u.id !== me.id ? [{ label: 'Hapus', icon: 'trash', danger: true, onClick: () => setDeleting(u) }] : []),
                        ]} />
                    </li>
                ))}
            </ul>
            <Pagination paginator={users} />

            {deleting && (
                <ConfirmDialog title="Hapus pengguna?" message={`Akun @${deleting.username} beserta seluruh artikelnya akan dihapus permanen.`}
                    processing={busy} onConfirm={remove} onCancel={() => setDeleting(null)} />
            )}
        </>
    );
}
UserList.layout = dashboardLayout;
