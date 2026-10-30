// Realtime auto-refresh: authenticated pages poll a cheap revision fingerprint.
// Polling pauses while tab is hidden, modal is open, or user is mid-input.
// Reload only triggers on list/overview pages to prevent interrupting form entry.
const liveEndpoint = document.querySelector('meta[name="kulaan-live-endpoint"]')?.content;

if (liveEndpoint) {
    const intervalMs = Number(document.querySelector('meta[name="kulaan-live-interval"]')?.content || 30000);
    let baseline = null;

    const isBusy = () => {
        if (document.visibilityState !== 'visible') {
            return true;
        }
        if (document.body.style.overflow === 'hidden') {
            return true;
        }
        if (document.querySelector('dialog[open]')) {
            return true;
        }
        const active = document.activeElement;
        return !!active && (active.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName));
    };

    const isListPage = () => {
        const path = window.location.pathname.toLowerCase();
        // List/overview pages where fresh data needs auto-refresh
        const listPatterns = ['/dashboard', '/orders', '/shipments', '/proposals', '/requests', '/categories', '/products'];
        return listPatterns.some((pattern) => path.includes(pattern));
    };

    const fetchRevision = async () => {
        try {
            const res = await fetch(liveEndpoint, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin',
            });
            if (!res.ok) {
                return null;
            }
            const data = await res.json();
            return data?.revision ?? null;
        } catch (e) {
            return null;
        }
    };

    const handleRevisionChange = (revision) => {
        if (!revision || !baseline) {
            baseline = revision;
            return;
        }
        if (revision !== baseline) {
            baseline = revision;
            if (isListPage() && !isBusy()) {
                location.reload();
            }
        }
    };

    (async () => {
        baseline = await fetchRevision();
    })();

    setInterval(async () => {
        if (isBusy()) {
            return;
        }
        const revision = await fetchRevision();
        handleRevisionChange(revision);
    }, intervalMs);

    document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState === 'visible' && !isBusy()) {
            const revision = await fetchRevision();
            handleRevisionChange(revision);
        }
    });
}
