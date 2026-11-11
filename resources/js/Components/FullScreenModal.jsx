import Icon from './Icon';
import Spinner from './Spinner';

/**
 * Modal layar penuh. Header (indigo gelap) dan footer (abu terang) berbeda warna;
 * tombol aksi Batal & Simpan berada di sisi kanan footer.
 */
export default function FullScreenModal({ title, onClose, onSubmit, processing = false, submitLabel = 'Simpan', submitIcon, children }) {
    return (
        <div className="fixed inset-0 z-50 flex flex-col bg-white" role="dialog" aria-modal="true" aria-label={title}>
            <header className="flex items-center justify-between bg-indigo-900 px-6 py-4 text-white">
                <h2 className="font-serif text-xl font-semibold">{title}</h2>
                <button type="button" onClick={onClose} aria-label="Tutup" className="rounded-md p-1.5 hover:bg-white/10">
                    <Icon name="x" />
                </button>
            </header>

            <form onSubmit={onSubmit} className="flex min-h-0 flex-1 flex-col">
                <div className="flex-1 overflow-y-auto px-6 py-6">
                    <div className="mx-auto max-w-4xl space-y-5">{children}</div>
                </div>
                <footer className="flex justify-end gap-3 border-t border-slate-300 bg-slate-200 px-6 py-4">
                    <button type="button" onClick={onClose} disabled={processing}
                        className="rounded-md border border-slate-400 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" disabled={processing}
                        className="inline-flex items-center gap-2 rounded-md bg-indigo-700 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-800 disabled:opacity-70">
                        {processing ? <Spinner /> : submitIcon && <Icon name={submitIcon} className="h-4 w-4" />}
                        {submitLabel}
                    </button>
                </footer>
            </form>
        </div>
    );
}
