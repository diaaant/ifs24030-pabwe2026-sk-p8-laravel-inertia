import { Head, useForm } from '@inertiajs/react';
import { dashboardLayout } from '../../Layouts/AppLayout';
import Spinner from '../../Components/Spinner';
import { inputClass } from '../../Components/format';

const FIELDS = [
    ['name', 'Nama lengkap', 'text'],
    ['username', 'Username', 'text'],
    ['email', 'Email', 'email'],
    ['password', 'Password baru (kosongkan bila tidak diubah)', 'password'],
    ['password_confirmation', 'Ulangi password baru', 'password'],
];

export default function Edit({ profile }) {
    const form = useForm({
        name: profile.name, username: profile.username, email: profile.email || '',
        password: '', password_confirmation: '', photo: null,
    });

    const submit = (e) => {
        e.preventDefault();
        form.put('/profile', { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset('password', 'password_confirmation', 'photo') });
    };

    return (
        <>
            <Head title="Profil" />
            <h1 className="font-serif text-3xl font-bold text-indigo-950">Profil</h1>
            <p className="text-sm text-slate-600">Perbarui data akun dan foto profil Anda.</p>

            <form onSubmit={submit} className="mt-6 max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
                {profile.profile_photo_url && <img src={profile.profile_photo_url} alt="" className="h-20 w-20 rounded-full object-cover" />}
                {FIELDS.map(([key, label, type]) => (
                    <div key={key}>
                        <label htmlFor={`pf-${key}`} className="mb-1 block text-sm font-medium">{label}</label>
                        <input id={`pf-${key}`} type={type} className={inputClass} value={form.data[key]} onChange={(e) => form.setData(key, e.target.value)} />
                        {form.errors[key] && <p className="mt-1 text-sm text-red-600">{form.errors[key]}</p>}
                    </div>
                ))}
                <div>
                    <label htmlFor="pf-photo" className="mb-1 block text-sm font-medium">Foto profil</label>
                    <input id="pf-photo" type="file" accept="image/*" className={inputClass} onChange={(e) => form.setData('photo', e.target.files[0] || null)} />
                    {form.errors.photo && <p className="mt-1 text-sm text-red-600">{form.errors.photo}</p>}
                </div>
                <button type="submit" disabled={form.processing} className="inline-flex items-center gap-2 rounded-md bg-indigo-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-900 disabled:opacity-70">
                    {form.processing && <Spinner />} Simpan perubahan
                </button>
            </form>
        </>
    );
}
Edit.layout = dashboardLayout;
