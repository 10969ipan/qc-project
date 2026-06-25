(function () {
    'use strict';

    const ROUTES = window.__ITEMS__?.routes || {};

    pdfjsLib.GlobalWorkerOptions.workerSrc = ROUTES.pdfWorker;

    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let scale = 'auto';

    // Multi-file state
    let currentFileList = [];   // array of URL strings
    let currentFileIdx = 0;     // which file is currently shown

    const canvas = document.getElementById('the-canvas');
    const ctx = canvas ? canvas.getContext('2d') : null;

    /* ---------------------------------------------------------------
     *  Page rendering
     * --------------------------------------------------------------- */
    function renderPage(num) {
        if (!ctx) return;
        pageRendering = true;

        pdfDoc.getPage(num).then(function (page) {
            const unscaledViewport = page.getViewport({ scale: 1.0 });
            
            if (scale === 'auto') {
                const container = canvas.parentElement;
                const containerHeight = container.clientHeight - 40; // 40px for margin/padding
                const containerWidth = container.clientWidth - 40;
                
                const scaleHeight = containerHeight / unscaledViewport.height;
                const scaleWidth = containerWidth / unscaledViewport.width;
                
                scale = Math.min(scaleHeight, scaleWidth);
                if(scale > 2.0) scale = 2.0; // cap the scale
                if(scale < 0.2) scale = 0.2;
            }

            const viewport = page.getViewport({ scale: scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderTask = page.render({ canvasContext: ctx, viewport: viewport });
            renderTask.promise.then(function () {
                pageRendering = false;
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
            });
        });

        const pageInfoEl = document.getElementById('pageInfo');
        if (pageInfoEl) pageInfoEl.textContent = `Halaman ${num} dari ${pdfDoc.numPages}`;
    }

    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }

    function onPrevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }

    function onNextPage() {
        if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }

    const prevPageBtn = document.getElementById('prevPage');
    if (prevPageBtn) prevPageBtn.addEventListener('click', onPrevPage);

    const nextPageBtn = document.getElementById('nextPage');
    if (nextPageBtn) nextPageBtn.addEventListener('click', onNextPage);

    const zoomInBtn = document.getElementById('zoomIn');
    if (zoomInBtn) zoomInBtn.addEventListener('click', function () { scale += 0.25; queueRenderPage(pageNum); });

    const zoomOutBtn = document.getElementById('zoomOut');
    if (zoomOutBtn) zoomOutBtn.addEventListener('click', function () { if (scale > 0.25) { scale -= 0.25; queueRenderPage(pageNum); } });

    const zoomResetBtn = document.getElementById('zoomReset');
    if (zoomResetBtn) zoomResetBtn.addEventListener('click', function () { scale = 'auto'; queueRenderPage(pageNum); });

    /* ---------------------------------------------------------------
     *  File navigation bar (shown only for multi-file)
     * --------------------------------------------------------------- */
    function updateFileNav() {
        const navEl = document.getElementById('pdfFileNav');
        const fileInfoEl = document.getElementById('fileInfo');
        if (!navEl) return;

        if (currentFileList.length > 1) {
            navEl.classList.remove('d-none');
            navEl.classList.add('d-flex');
            if (fileInfoEl) fileInfoEl.textContent = `File ${currentFileIdx + 1} / ${currentFileList.length}`;
        } else {
            navEl.classList.add('d-none');
            navEl.classList.remove('d-flex');
        }
    }

    function goToFile(idx) {
        if (idx < 0 || idx >= currentFileList.length) return;
        currentFileIdx = idx;
        updateFileNav();
        loadPdfFromUrl(currentFileList[currentFileIdx], false);
    }

    const prevFileBtn = document.getElementById('prevFile');
    if (prevFileBtn) prevFileBtn.addEventListener('click', function () { goToFile(currentFileIdx - 1); });

    const nextFileBtn = document.getElementById('nextFile');
    if (nextFileBtn) nextFileBtn.addEventListener('click', function () { goToFile(currentFileIdx + 1); });

    /* ---------------------------------------------------------------
     *  Core: load & render PDF from URL (server or blob:)
     * --------------------------------------------------------------- */
    function loadPdfFromUrl(url, isBlob) {
        pdfDoc = null;
        pageNum = 1;
        scale = 'auto';
        if (ctx && canvas) ctx.clearRect(0, 0, canvas.width, canvas.height);

        const pageInfoEl = document.getElementById('pageInfo');
        if (pageInfoEl) pageInfoEl.textContent = 'Memuat...';

        const finalUrl = isBlob
            ? url
            : (url + (url.indexOf('?') !== -1 ? '&' : '?') + '_nocache=' + Date.now());

        // Download button
        const dlBtn = document.getElementById('downloadPdfBtn');
        if (dlBtn) {
            if (isBlob) {
                dlBtn.style.display = 'none';
            } else {
                dlBtn.style.display = '';
                dlBtn.setAttribute('href', url + (url.indexOf('?') !== -1 ? '&' : '?') + 'download=1');
            }
        }

        pdfjsLib.getDocument(finalUrl).promise.then(function (pdfDoc_) {
            pdfDoc = pdfDoc_;
            if (pageInfoEl) pageInfoEl.textContent = `Halaman 1 dari ${pdfDoc.numPages}`;
            renderPage(pageNum);
        }, function (reason) {
            console.error(reason);
            let errorMsg = 'Gagal memuat PDF. ';
            if (reason.name === 'MissingPDFException') {
                errorMsg += 'File PDF tidak ditemukan.';
            } else {
                errorMsg += reason.message || reason;
            }
            if (pageInfoEl) pageInfoEl.textContent = 'Error: ' + reason.name;
            alert(errorMsg);
        });
    }

    // Expose globally for blob preview (items-form-logic.js)
    window.renderPdfFromUrl = function (url) {
        currentFileList = [url];
        currentFileIdx = 0;
        updateFileNav();
        loadPdfFromUrl(url, true);
    };

    /* ---------------------------------------------------------------
     *  Click handler: read data-files (JSON array) or fall back to
     *  data-src for single-file buttons.
     * --------------------------------------------------------------- */
    $(document).on('click', '.view-pdf-btn', function () {
        let filesAttr = $(this).data('files');
        let files = [];

        try {
            files = filesAttr ? (typeof filesAttr === 'string' ? JSON.parse(filesAttr) : filesAttr) : [];
        } catch (e) {
            files = [];
        }

        if (files.length === 0) {
            // Fallback: single URL from data-src
            const src = $(this).data('src');
            if (src) files = [src];
        }

        currentFileList = files;
        currentFileIdx = 0;
        updateFileNav();

        if (currentFileList.length > 0) {
            loadPdfFromUrl(currentFileList[0], false);
        }
    });

    // Reset file state when modal closes
    $('#pdfModal').on('hidden.bs.modal', function () {
        currentFileList = [];
        currentFileIdx = 0;
        const navEl = document.getElementById('pdfFileNav');
        if (navEl) { navEl.classList.add('d-none'); navEl.classList.remove('d-flex'); }
        if (ctx && canvas) ctx.clearRect(0, 0, canvas.width, canvas.height);
        const pageInfoEl = document.getElementById('pageInfo');
        if (pageInfoEl) pageInfoEl.textContent = 'Page 1 of ?';
    });

})();
