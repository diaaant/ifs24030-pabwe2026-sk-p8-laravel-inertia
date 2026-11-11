import { Head, Link, useForm } from '@inertiajs/react';
import { publicLayout } from '../../Layouts/AppLayout';
import Spinner from '../../Components/Spinner';
import { inputClass } from '../../Components/format';

const FIELDS = [
    ['name', 'Nama lengkap', 'text', 'name'],
    ['username', 'Username', 'text', 'username'],
    ['email', 'Email (opsional)', 'email', 'email'],
    ['password', 'Password', 'password', 'new-password'],
    ['password_confirmation', 'Ulangi password', 'password', 'new-password'],
];

export default function Register() {
    const form = useForm({ name: '', username: '', email: '', password: '', password_confirmation: '' });

    return (
        <>
            <Head title="Daftar" />
            <div className="mx-auto w-full max-w-md px-4 py-14">
                <h1 className="font-serif text-3xl font-bold text-indigo-950">Daftar sebagai penulis</h1>
                <p className="mt-2 text-sm text-slate-600">Buat akun dan terbitkan artikel pertama Anda.</p>

                <form onSubmit={(e) => { e.preventDefault(); form.post('/register'); }}
                    className="mt-8 space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    {FIELDS.map(([key, label, type, auto]) => (
                        <div key={key}>
                            <label htmlFor={`reg-${key}`} className="mb-1 block text-sm font-medium">{label}</label>
                            <input id={`reg-${key}`} type={type} autoComplete={auto} className={inputClass}
                                value={form.data[key]} onChange={(e) => form.setData(key, e.target.value)} />
                            {form.errors[key] && <p className="mt-1 text-sm text-red-600">{form.errors[key]}</p>}
                        </div>
                    ))}
                    <button id="register-submit" type="submit" disabled={form.processing}
                        className="flex w-full items-center justify-center gap-2 rounded-md bg-indigo-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-900 disabled:opacity-70">
                        {form.processing && <Spinner />} Buat akun
                    </button>
                </form>

                <p className="mt-5 text-center text-sm text-slate-600">
                    Sudah punya akun? <Link href="/login" className="font-medium text-indigo-800 underline">Masuk</Link>
                </p>
            </div>
        </>
    );
}
Register.layout = publicLayout;
