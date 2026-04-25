(function () {
    'use strict';

    const ROUTES = window.__ITEMS__?.routes || {};

    pdfjsLib.GlobalWorkerOptions.workerSrc = ROUTES.pdfWorker;

    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let scale = 1.0;
    const canvas = document.getElementById('the-canvas');
    const ctx = canvas ? canvas.getContext('2d') : null;

    function renderPage(num) {
        if (!ctx) return;
        pageRendering = true;
        
        pdfDoc.getPage(num).then(function (page) {
            const viewport = page.getViewport({ scale: scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            const renderTask = page.render(renderContext);

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
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }

    const prevPageBtn = document.getElementById('prevPage');
    if (prevPageBtn) prevPageBtn.addEventListener('click', onPrevPage);

    const nextPageBtn = document.getElementById('nextPage');
    if (nextPageBtn) nextPageBtn.addEventListener('click', onNextPage);

    const zoomInBtn = document.getElementById('zoomIn');
    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function () {
            scale += 0.25;
            queueRenderPage(pageNum);
        });
    }

    const zoomOutBtn = document.getElementById('zoomOut');
    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function () {
            if (scale > 0.25) {
                scale -= 0.25;
                queueRenderPage(pageNum);
            }
        });
    }

    const zoomResetBtn = document.getElementById('zoomReset');
    if (zoomResetBtn) {
        zoomResetBtn.addEventListener('click', function () {
            scale = 1.0;
            queueRenderPage(pageNum);
        });
    }

    $(document).on('click', '.view-pdf-btn', function () {
        let url = $(this).data('src');
        
        // Update download button
        const downloadUrl = url + (url.indexOf('?') !== -1 ? '&' : '?') + 'download=1';
        $('#downloadPdfBtn').attr('href', downloadUrl);

        const separator = url.indexOf('?') !== -1 ? '&' : '?';
        url = url + separator + '_nocache=' + Date.now();

        pdfDoc = null;
        pageNum = 1;
        scale = 1.0;
        if (ctx && canvas) ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        const pageInfoEl = document.getElementById('pageInfo');
        if (pageInfoEl) pageInfoEl.textContent = 'Memuat...';

        pdfjsLib.getDocument(url).promise.then(function (pdfDoc_) {
            pdfDoc = pdfDoc_;
            if (pageInfoEl) pageInfoEl.textContent = `Halaman 1 dari ${pdfDoc.numPages}`;
            renderPage(pageNum);
        }, function (reason) {
            console.error(reason);
            let errorMsg = 'Gagal memuat PDF. ';
            if (reason.name === 'MissingPDFException') {
                errorMsg += 'File PDF tidak ditemukan di server.';
            } else {
                errorMsg += reason.message || reason;
            }

            if (pageInfoEl) pageInfoEl.textContent = 'Error: ' + reason.name;
            alert(errorMsg);
        });
    });

})();
