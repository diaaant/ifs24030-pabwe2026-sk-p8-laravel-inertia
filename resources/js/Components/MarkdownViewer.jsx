import { useEffect, useRef } from 'react';
import Viewer from '@toast-ui/editor/dist/toastui-editor-viewer';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';

export default function MarkdownViewer({ value }) {
    const el = useRef(null);

    useEffect(() => {
        const viewer = new Viewer({ el: el.current, initialValue: value || '', usageStatistics: false });
        return () => viewer.destroy();
    }, [value]);

    return <div ref={el} className="markdown-body" />;
}
