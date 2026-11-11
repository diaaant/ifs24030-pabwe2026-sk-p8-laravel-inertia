import Spinner from './Spinner';

export default function ConfirmDialog({ title, message, confirmLabel = 'Hapus', processing = false, onConfirm, onCancel }) {
    return (
        <div className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4" role="alertdialog" aria-modal="true">
            <div className="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 className="font-serif text-lg font-semibold">{title}</h3>
                <p className="mt-2 text-sm text-slate-600">{message}</p>
                <div className="mt-6 flex justify-end gap-3">
                    <button type="button" onClick={onCancel} disabled={processing}
                        className="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="button" onClick={onConfirm} disabled={processing}
                        className="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-70">
                        {processing && <Spinner />} {confirmLabel}
                    </button>
                </div>
            </div>
        </div>
    );
}
