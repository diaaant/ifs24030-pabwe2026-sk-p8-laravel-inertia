import { Link, router, usePage } from '@inertiajs/react';
import Icon from '../Components/Icon';
import LoadingIndicator from '../Components/LoadingIndicator';
import Flash from '../Components/Flash';

function Brand({ light = false }) {
    return (
        <Link href="/" className={`font-serif text-xl font-bold tracking-tight ${light ? 'text-white' : 'text-indigo-900'}`}>
            BlogVerse Pro
        </Link>
    );
}

function Avatar({ user }) {
    return user.profile_photo_url ? (
        <img src={user.profile_photo_url} alt="" className="h-8 w-8 rounded-full object-cover" />
    ) : (
        <span className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-800">
            {user.name.charAt(0).toUpperCase()}
        </span>
    );
}

/** Layout publik: Sticky Top Navbar, tata letak baca leluasa, footer editorial. Tanpa sidebar. */
function PublicShell({ children }) {
    const { auth } = usePage().props;
    const user = auth.user;
    const dash = user?.role === 'admin' ? { href: '/admin/users', label: 'Panel Admin' } : { href: '/dashboard/posts', label: 'Dashboard Penulis' };

    return (
        <div className="flex min-h-screen flex-col">
            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
                    <div className="flex items-center gap-8">
                        <Brand />
                        <nav className="hidden gap-6 text-sm font-medium text-slate-600 sm:flex">
                            <Link href="/" className="hover:text-indigo-800">Beranda</Link>
                            <Link href="/articles" className="hover:text-indigo-800">Artikel</Link>
                        </nav>
                    </div>
                    <div className="flex items-center gap-2">
                        {user ? (
                            <>
                                <Link href={dash.href} className="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-indigo-800 hover:bg-indigo-50">
                                    <Avatar user={user} /> <span className="hidden sm:inline">{dash.label}</span>
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100">
                                    <Icon name="login" className="h-4 w-4" /> Masuk
                                </Link>
                                <Link href="/register" className="inline-flex items-center gap-1.5 rounded-md bg-indigo-800 px-3.5 py-1.5 text-sm font-semibold text-white hover:bg-indigo-900">
                                    <Icon name="userPlus" className="h-4 w-4" /> Daftar
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </header>

            <main className="flex-1">
                <div className="mx-auto max-w-6xl px-4 pt-4"><Flash /></div>
                {children}
            </main>

            <footer className="mt-16 border-t border-slate-200 bg-slate-900 text-slate-300">
                <div className="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <Brand light />
                        <p className="mt-2 max-w-sm text-sm text-slate-400">Tempat penulis berbagi gagasan tentang teknologi, pendidikan, bisnis, dan kehidupan sehari-hari.</p>
                    </div>
                    <nav className="flex gap-6 text-sm" aria-label="Navigasi footer">
                        <Link href="/" className="hover:text-white">Beranda Publik</Link>
                        <Link href="/login" className="hover:text-white">Login</Link>
                        <Link href="/register" className="hover:text-white">Register</Link>
                    </nav>
                </div>
                {/* PERBAIKAN: text-slate-500 diubah menjadi text-slate-400 */}
                <p className="border-t border-slate-800 py-4 text-center text-xs text-slate-400">© {new Date().getFullYear()} BlogVerse Pro</p>
            </footer>
        </div>
    );
}

/** Layout dashboard (penulis & admin) dengan sidebar. */
function DashboardShell({ children }) {
    const page = usePage();
    const { auth } = page.props;
    const url = page.url;
    const user = auth.user;
    const isAdmin = user.role === 'admin';

    const links = isAdmin
        ? [
              { href: '/admin/users', label: 'Pengguna', icon: 'user' },
              { href: '/admin/posts', label: 'Semua Artikel', icon: 'doc' },
              { href: '/profile', label: 'Profil', icon: 'pencil' },
          ]
        : [
              { href: '/dashboard/posts', label: 'Artikel Saya', icon: 'doc' },
              { href: '/profile', label: 'Profil', icon: 'user' },
          ];

    const logout = () => router.post('/logout');

    return (
        <div className="min-h-screen md:flex">
            <aside className="bg-indigo-950 text-indigo-100 md:sticky md:top-0 md:flex md:h-screen md:w-64 md:shrink-0 md:flex-col">
                <div className="flex items-center justify-between px-5 py-4 md:block md:py-6">
                    <Brand light />
                    <p className="hidden pt-1 text-xs text-indigo-300 md:block">{isAdmin ? 'Panel Admin' : 'Ruang Penulis'}</p>
                </div>
                <nav className="flex gap-1 overflow-x-auto px-3 pb-3 md:flex-1 md:flex-col md:pb-0">
                    {links.map((l) => {
                        const active = url.startsWith(l.href);
                        return (
                            <Link key={l.href} href={l.href}
                                className={`flex items-center gap-2.5 whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium ${active ? 'bg-white/15 text-white' : 'hover:bg-white/10'}`}>
                                <Icon name={l.icon} className="h-4 w-4" /> {l.label}
                            </Link>
                        );
                    })}
                    <Link href="/" className="flex items-center gap-2.5 whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium hover:bg-white/10">
                        <Icon name="globe" className="h-4 w-4" /> Lihat Blog
                    </Link>
                </nav>
                <div className="hidden items-center gap-3 border-t border-white/10 p-4 md:flex">
                    <Avatar user={user} />
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-medium text-white">{user.name}</p>
                        <p className="truncate text-xs text-indigo-300">@{user.username}</p>
                    </div>
                    <button type="button" onClick={logout} aria-label="Keluar" className="rounded-md p-2 hover:bg-white/10">
                        <Icon name="logout" className="h-4 w-4" />
                    </button>
                </div>
                <button type="button" onClick={logout} className="m-3 flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-white/10 md:hidden">
                    <Icon name="logout" className="h-4 w-4" /> Keluar
                </button>
            </aside>
            <main className="min-w-0 flex-1 bg-slate-50 px-4 py-6 md:px-10 md:py-8">
                <Flash />
                {children}
            </main>
        </div>
    );
}

export default function AppLayout({ variant = 'dashboard', children }) {
    return (
        <>
            <LoadingIndicator />
            {variant === 'public' ? <PublicShell>{children}</PublicShell> : <DashboardShell>{children}</DashboardShell>}
        </>
    );
}

export const publicLayout = (page) => <AppLayout variant="public">{page}</AppLayout>;
export const dashboardLayout = (page) => <AppLayout variant="dashboard">{page}</AppLayout>;