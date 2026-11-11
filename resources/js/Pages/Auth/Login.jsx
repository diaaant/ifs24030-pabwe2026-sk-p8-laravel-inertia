import { Head, Link, useForm } from '@inertiajs/react';
import { publicLayout } from '../../Layouts/AppLayout';
import Spinner from '../../Components/Spinner';
import { inputClass } from '../../Components/format';

export default function Login() {
    const form = useForm({ username: '', password: '', remember: false });

    const submit = (e) => {
        e.preventDefault();
        form.post('/login');
    };

    return (
        <>
            <Head title="Masuk" />
            <div className="mx-auto w-full max-w-md px-4 py-14">
                <h1 className="font-serif text-3xl font-bold text-indigo-950">Masuk ke BlogVerse</h1>
                <p className="mt-2 text-sm text-slate-600">Lanjutkan menulis dan kelola artikel Anda.</p>

                <form onSubmit={submit} className="mt-8 space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div>
                        <label htmlFor="login-username" className="mb-1 block text-sm font-medium">Username</label>
                        <input id="login-username" name="username" autoComplete="username" className={inputClass}
                            value={form.data.username} onChange={(e) => form.setData('username', e.target.value)} />
                        {form.errors.username && <p className="mt-1 text-sm text-red-600">{form.errors.username}</p>}
                    </div>
                    <div>
                        <label htmlFor="login-password" className="mb-1 block text-sm font-medium">Password</label>
                        <input id="login-password" name="password" type="password" autoComplete="current-password" className={inputClass}
                            value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} />
                        {form.errors.password && <p className="mt-1 text-sm text-red-600">{form.errors.password}</p>}
                    </div>
                    <label className="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" checked={form.data.remember} onChange={(e) => form.setData('remember', e.target.checked)} /> Ingat saya
                    </label>
                    <button id="login-submit" type="submit" disabled={form.processing}
                        className="flex w-full items-center justify-center gap-2 rounded-md bg-indigo-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-900 disabled:opacity-70">
                        {form.processing && <Spinner />} Masuk
                    </button>
                </form>

                <p className="mt-5 text-center text-sm text-slate-600">
                    Belum punya akun? <Link href="/register" className="font-medium text-indigo-800 underline">Daftar sebagai penulis</Link>
                </p>
            </div>
        </>
    );
}
Login.layout = publicLayout;
