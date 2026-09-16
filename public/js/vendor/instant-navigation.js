/**
 * Instant Navigation Engine for QC Apps
 * Prefetches internal page HTML on link hover/touch for 0ms transition speeds.
 */
(function () {
    'use strict';

    // Disable if low-data mode or slow connection is active
    if ('connection' in navigator) {
        if (navigator.connection.saveData || (navigator.connection.effectiveType || '').includes('2g')) {
            return;
        }
    }

    const prefetchedUrls = new Set();
    const delayMs = 65;
    let timer = null;

    function isPrefetchable(anchor) {
        if (!anchor || !anchor.href) return false;
        if (anchor.target && anchor.target !== '_self') return false;
        if (anchor.hasAttribute('download')) return false;

        const href = anchor.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        const url = new URL(anchor.href, window.location.href);
        if (url.origin !== window.location.origin) return false;
        if (url.pathname === window.location.pathname && url.search === window.location.search) return false;

        // Skip sensitive / action endpoints
        const sensitivePatterns = [
            '/logout', '/delete', '/destroy', '/export', '/download',
            '/pdf', '/excel', '/csv', '/print', '/api/'
        ];
        const pathnameLower = url.pathname.toLowerCase();
        for (let i = 0; i < sensitivePatterns.length; i++) {
            if (pathnameLower.includes(sensitivePatterns[i])) return false;
        }

        return !prefetchedUrls.has(url.href);
    }

    function prefetch(url) {
        if (prefetchedUrls.has(url)) return;
        prefetchedUrls.add(url);

        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        document.head.appendChild(link);
    }

    function onMouseOver(e) {
        const anchor = e.target.closest('a');
        if (!isPrefetchable(anchor)) return;

        timer = setTimeout(function () {
            prefetch(anchor.href);
        }, delayMs);
    }

    function onMouseOut(e) {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    }

    function onTouchStart(e) {
        const anchor = e.target.closest('a');
        if (isPrefetchable(anchor)) {
            prefetch(anchor.href);
        }
    }

    // Attach passive event listeners to document
    document.addEventListener('mouseover', onMouseOver, { passive: true });
    document.addEventListener('mouseout', onMouseOut, { passive: true });
    document.addEventListener('touchstart', onTouchStart, { passive: true });
})();
