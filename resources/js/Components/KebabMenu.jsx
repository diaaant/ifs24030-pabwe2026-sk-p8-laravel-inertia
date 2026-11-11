import { useEffect, useRef, useState } from 'react';
import Icon from './Icon';

/** Menu tindakan vertical dots (⋮). items: [{ label, icon, onClick, danger }] */
export default function KebabMenu({ items }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        const close = (e) => {
            if (ref.current && !ref.current.contains(e.target)) setOpen(false);
        };
        document.addEventListener('mousedown', close);
        return () => document.removeEventListener('mousedown', close);
    }, []);

    return (
        <div className="relative" ref={ref}>
            <button type="button" onClick={() => setOpen(!open)} aria-label="Menu tindakan" aria-expanded={open}
                className="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-indigo-600">
                <Icon name="dots" />
            </button>
            {open && (
                <div className="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg" role="menu">
                    {items.map((item) => (
                        <button key={item.label} type="button" role="menuitem"
                            onClick={() => { setOpen(false); item.onClick(); }}
                            className={`flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm hover:bg-slate-50 ${item.danger ? 'text-red-600' : 'text-indigo-700'}`}>
                            <Icon name={item.icon} className="h-4 w-4" /> {item.label}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
