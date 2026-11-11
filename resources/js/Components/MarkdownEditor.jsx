import { useEffect, useRef } from 'react';
import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';

/** TOAST UI Editor (mode Markdown) sebagai komponen terkontrol sederhana. */
export default function MarkdownEditor({ value, onChange, height = '420px' }) {
    const el = useRef(null);
    const instance = useRef(null);
    const handler = useRef(onChange);
    handler.current = onChange;

    useEffect(() => {
        instance.current = new Editor({
            el: el.current,
            height,
            initialEditType: 'markdown',
            previewStyle: 'vertical',
            initialValue: value || '',
            usageStatistics: false,
            placeholder: 'Tulis artikel Anda dalam Markdown…',
        });
        instance.current.on('change', () => handler.current(instance.current.getMarkdown()));

        return () => {
            instance.current.destroy();
            instance.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return <div ref={el} />;
}
