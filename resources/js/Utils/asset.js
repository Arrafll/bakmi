const BASE = import.meta.env.VITE_APP_BASE || '/';

export function asset(path) {
    const base = BASE.endsWith('/') ? BASE.slice(0, -1) : BASE;
    const p = path.startsWith('/') ? path : '/' + path;
    return base + p;
}

