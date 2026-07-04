const BASE = import.meta.env.VITE_APP_BASE || '/';

export function asset(path) {
    const base = BASE.endsWith('/') ? BASE.slice(0, -1) : BASE;
    const p = path.startsWith('/') ? path : '/' + path;
    return base + p;
}

// Menu photos come from two different places: the seed catalog ships its
// photos inside public/img-bakmi (versioned with the app), while photos
// uploaded through the admin panel are written to the storage disk and
// served through the public/storage symlink.
export function menuImage(imagePath) {
    if (!imagePath) return null;
    return imagePath.startsWith('img-bakmi/') ? asset('/' + imagePath) : asset('/storage/' + imagePath);
}