import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import Spinner from './Spinner';

/** Top progress bar + floating pill "Memproses tindakan..." untuk setiap request Inertia. */
export default function LoadingIndicator() {
    const [active, setActive] = useState(false);

    useEffect(() => {
        const off1 = router.on('start', () => setActive(true));
        const off2 = router.on('finish', () => setActive(false));
        return () => {
            off1();
            off2();
        };
    }, []);

    if (!active) return null;

    return (
        <>
            <div className="fixed inset-x-0 top-0 z-[100] h-1 overflow-hidden bg-indigo-100" role="progressbar" aria-label="Memuat">
                <div className="h-full w-1/4 animate-[progress-slide_1.1s_ease-in-out_infinite] bg-indigo-700" />
            </div>
            <div className="fixed bottom-6 left-1/2 z-[100] flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-lg" role="status">
                <Spinner /> Memproses tindakan...
            </div>
        </>
    );
}
