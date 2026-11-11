import { usePage } from '@inertiajs/react';

export default function Flash() {
    const { flash } = usePage().props;
    if (!flash?.success && !flash?.error) return null;

    return (
        <div className="mb-5 space-y-2" aria-live="polite">
            {flash.success && <div className="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{flash.success}</div>}
            {flash.error && <div className="rounded-md border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-800">{flash.error}</div>}
        </div>
    );
}
