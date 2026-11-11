export const formatDate = (value) =>
    value ? new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '—';

export const inputClass =
    'block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-600 focus:outline-2 focus:outline-indigo-600/30';
