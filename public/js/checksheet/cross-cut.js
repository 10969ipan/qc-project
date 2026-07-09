/**
 * Modul Cross Cut (Plating & Painting)
 */
class CrossCutIndex {
    constructor(config) {
        this.config = config;
        this.init();
    }

    init() {
        this.initCharCounters();
        this.initLiveSearch();
        this.initImageModal();
        this.initPdfExport();
        this.initAjaxModals();
        this.initTooltips();
    }

    initCharCounters() {
        // Penghitung karakter untuk catatan penolakan dalam modal
        $(document).on('input', 'textarea[id^="rejection_remarks"]', function() {
            const id = $(this).attr('id').replace('rejection_remarks', '');
            $(`#charCount${id}`).text($(this).val().length);
        });
    }

    initLiveSearch() {
        const liveSearchInput = document.getElementById('liveSearch');
        if (!liveSearchInput) return;

        if (this.config.isPainting) {
            // Painting menggunakan pencarian sisi server
            let searchTimeout;
            $(liveSearchInput).on('keyup', function() {
                clearTimeout(searchTimeout);
                const searchTerm = $(this).val();
                searchTimeout = setTimeout(() => {
                    const url = new URL(window.location.href);
                    if (searchTerm) {
                        url.searchParams.set('search', searchTerm);
                    } else {
                        url.searchParams.delete('search');
                    }
                    window.location.href = url.toString();
                }, 700);
            });
        } else {
            // Plating menggunakan pencarian sisi klien
            const checksheetTable = document.getElementById('checksheetTable');
            if (!checksheetTable) return;
            const tableRows = checksheetTable.querySelectorAll('tbody tr');

            liveSearchInput.addEventListener('keyup', function () {
                const searchTerm = this.value.toLowerCase().trim();
                tableRows.forEach(function (row) {
                    const itemPart = row.cells[8] ? row.cells[8].textContent.toLowerCase() : '';
                    const customer = row.cells[9] ? row.cells[9].textContent.toLowerCase() : '';
                    const partNo = row.cells[10] ? row.cells[10].textContent.toLowerCase() : '';
                    const initials = row.cells[14] ? row.cells[14].textContent.toLowerCase() : '';

                    const matches = itemPart.includes(searchTerm) ||
                        customer.includes(searchTerm) ||
                        partNo.includes(searchTerm) ||
                        initials.includes(searchTerm);

                    row.style.display = (matches || searchTerm === '') ? '' : 'none';
                });
            });
        }
    }

    initImageModal() {
        const modalImage = document.getElementById('modalImage');
        const modalItemName = document.getElementById('modalItemName');
        const modalQcDatetime = document.getElementById('modalQcDatetime');
        const modalViewImage = document.getElementById('modalViewImage');

        $(document).on('click', '.view-image-btn', (e) => {
            const btn = $(e.currentTarget);
            const imagePath = btn.data('image');
            const checksheetId = btn.data('id');

            if (modalViewImage && imagePath) {
                modalViewImage.src = imagePath;
            } else if (checksheetId && this.config.showRoute) {
                const fetchUrl = this.config.showRoute.replace(':id', checksheetId);
                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (modalImage) modalImage.src = data.image_url;
                        if (modalItemName) modalItemName.textContent = `Item: ${data.item_name}`;
                        if (modalQcDatetime) modalQcDatetime.textContent = `QC Datetime: ${data.qc_datetime}`;
                    })
                    .catch(error => console.error('Error fetching image data:', error));
            }
        });
    }

    initPdfExport() {
        const exportPdfBtn = document.getElementById('exportPdfBtn');
        if (!exportPdfBtn) return;

        exportPdfBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape');

            const logo = document.getElementById('pdf-logo');
            
            doc.autoTable({
                startY: 10,
                head: [],
                body: [
                    [
                        { content: '', rowSpan: 4, styles: { minCellHeight: 25, valign: 'middle' } },
                        { content: this.config.pdfTitle || 'LAPORAN CHECKSHEET CROSS CUT', rowSpan: 4, styles: { halign: 'center', valign: 'middle', fontSize: 14, fontStyle: 'bold' } },
                        { content: 'No. Dokumen', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                        { content: this.config.docNo || 'QC-KRW-F-XXXX', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                    ],
                    [{ content: 'Tgl. Terbit', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }, { content: '-', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }],
                    [{ content: 'Revisi Ke', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }, { content: '-', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }],
                    [{ content: 'Tgl. Revisi', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }, { content: '-', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }]
                ],
                theme: 'grid',
                styles: { lineColor: [0, 0, 0], lineWidth: 0.1, cellPadding: 1.5 },
                columnStyles: { 0: { cellWidth: 30 } },
                didDrawCell: (data) => {
                    if (data.section === 'body' && data.column.index === 0 && logo) {
                        try { doc.addImage(logo, 'JPEG', data.cell.x + 2, data.cell.y + 2, 26, 21); } catch (err) { console.warn('Logo error:', err); }
                    }
                }
            });

            const originalTable = document.getElementById('checksheetTable');
            if (!originalTable) return;
            const tableClone = originalTable.cloneNode(true);
            $(tableClone).find('.no-export').remove();

            // Ratakan kolom Kimia jika ada
            $(tableClone).find('.kimia-col').each((_, cell) => {
                const nested = $(cell).find('table');
                if (nested.length) {
                    let text = [];
                    nested.find('tr').each((_, tr) => {
                        const th = $(tr).find('th').text().trim();
                        const td = $(tr).find('td').text().trim();
                        if (th && td) text.push(`${th}: ${td}`);
                    });
                    $(cell).text(text.join('\n')).css('white-space', 'pre-wrap');
                }
            });

            document.body.appendChild(tableClone);
            doc.autoTable({
                html: tableClone,
                startY: doc.lastAutoTable.finalY + 7,
                theme: 'grid',
                styles: { fontSize: 5, cellPadding: 1, valign: 'middle', halign: 'center', lineColor: [0, 0, 0], lineWidth: 0.1 },
                headStyles: { fillColor: [78, 115, 223], textColor: [255, 255, 255] }
            });

            document.body.removeChild(tableClone);
            doc.save(`${this.config.moduleName || 'Cross_Cut'}_${new Date().toISOString().slice(0, 10)}.pdf`);
        });
    }

    initAjaxModals() {
        // Modal Edit
        const self = this;
        $(document).on('click', '.btn-edit-modal, .edit-btn', function(e) {
            e.preventDefault();
            const url = $(this).attr('href') || (self.config.editRoute ? self.config.editRoute.replace(':id', $(this).data('id')) : null);
            if (!url) return;

            const modalId = self.config.isPainting ? '#editModal' : '#editModal';
            const bodyId = self.config.isPainting ? '#editModalBody' : '#editModalBody';

            $(modalId).modal('show');
            $(bodyId).html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

            $.ajax({
                url: url,
                success: (res) => $(bodyId).html(res),
                error: () => $(bodyId).html('<div class="alert alert-danger">Gagal memuat data.</div>')
            });
        });

        // Modal Status/Approval
        $(document).on('click', '.btn-status-modal', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#statusModal').modal('show');
            $('#statusModalBody').html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div></div>');

            $.ajax({
                url: url,
                success: (res) => $('#statusModalBody').html(res),
                error: (xhr) => {
                    let msg = 'Gagal memuat data.';
                    if (xhr.status === 404) msg = 'Data tidak ditemukan.';
                    else if (xhr.status === 403) msg = 'Akses ditolak.';
                    $('#statusModalBody').html(`<div class="alert alert-danger">${msg}</div>`);
                }
            });
        });

        // Modal Konfirmasi Approval (Painting)
        if (this.config.isPainting) {
            $('#approvalModal').on('show.bs.modal', (e) => {
                const btn = $(e.relatedTarget);
                if (!btn.length) return;
                this.setupApprovalForm(btn.data('id'), btn.data('type'), btn.data('label'), false);
            });

            window.toggleApprovalModal = (id, type, label, isReject = false) => {
                this.setupApprovalForm(id, type, label, isReject);
                $('#approvalModal').modal('show');
            };

            window.toggleRejectReason = (show) => {
                const form = $('#approvalForm');
                const action = form.attr('action');
                if (show) {
                    $('#rejectReasonGroup').slideDown();
                    $('textarea[name="rejection_remarks"]').prop('required', true);
                    form.attr('action', action.replace('/approve/', '/reject/'));
                } else {
                    $('#rejectReasonGroup').slideUp();
                    $('textarea[name="rejection_remarks"]').prop('required', false);
                    form.attr('action', action.replace('/reject/', '/approve/'));
                }
            };
        }
    }

    setupApprovalForm(id, type, label, isReject) {
        const form = $('#approvalForm');
        let url = this.config.approveRoute.replace(':id', id).replace(':type', type);
        form.attr('action', url);
        $('#approvalLabelText').text(label);

        $('#approverNameGroup').hide();
        $('#approver_name_input').prop('required', false);

        const action = isReject ? 'reject' : 'approve';
        $(`input[name="action_type"][value="${action}"]`).prop('checked', true).parent().addClass('active').siblings().removeClass('active');
        window.toggleRejectReason(isReject);
    }

    initTooltips() {
        $('[data-toggle="tooltip"]').tooltip();
    }
}

class CrossCutCreate {
    constructor(config) {
        this.config = config;
        this.timer = { running: false, seconds: 0, interval: null };
        this.pdf = { doc: null, page: 1, rendering: false, pending: null, scale: 1.0, currentIdx: 0, totalFiles: 0, itemId: null };
        this.refStandardPdfDoc = null;
        this.refStandardPageNum = 1;
        this.refStandardFileIndex = 0;
        this.refStandardFiles = [];
        this.refSimilarPdfDoc = null;
        this.refSimilarPageNum = 1;
        this.standardZoomLevel = 1.0;
        this.similarZoomLevel = 1.0;
        this.pdfCache = {};
        this.init();
    }

    init() {
        this.lockInputs(true);
        this.initPdfViewer();
        this.initItemSelection();
        this.initSapSelection();
        this.initImageZoom();
        this.initImageCapture();
        this.initTimer();
        this.initNextProses();
        this.initAutoJudgment();
        this.initFormSubmit();
    }

    lockInputs(lock) {
        const inputs = $('#checksheetForm').find('input, select, textarea');
        inputs.prop('disabled', lock);
        if (lock) $('#checksheetForm').addClass('inputs-locked');
        else $('#checksheetForm').removeClass('inputs-locked');
    }

    initPdfViewer() {
        if (typeof pdfjsLib === 'undefined') return;
        pdfjsLib.GlobalWorkerOptions.workerSrc = this.config.pdfWorkerSrc;

        const canvas = document.getElementById('the-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        const renderPage = (num) => {
            this.pdf.rendering = true;
            this.pdf.doc.getPage(num).then(page => {
                const viewport = page.getViewport({ scale: this.pdf.scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                page.render({ canvasContext: ctx, viewport }).promise.then(() => {
                    this.pdf.rendering = false;
                    if (this.pdf.pending !== null) {
                        renderPage(this.pdf.pending);
                        this.pdf.pending = null;
                    }
                });
            });
            $('#pageInfo').text(`Page ${num} of ${this.pdf.doc.numPages}`);
        };

        const queueRender = (num) => {
            if (this.pdf.rendering) this.pdf.pending = num;
            else renderPage(num);
        };

        const loadFile = (itemId, index) => {
            if (!itemId) return; // guard: no item selected
            const url = this.config.pdfUrlPattern.replace('ID_PLACEHOLDER', itemId).replace('INDEX_PLACEHOLDER', index);
            if (!url || url.trim() === '') return; // guard: empty path
            this.pdf.doc = null;
            this.pdf.page = 1;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            $('#pageInfo').text('Loading...');
            if (index === 'similar') {
                $('#pdfInfo').text("Dimensi Part PDF");
                $('#prevPdf, #nextPdf').hide();
            } else {
                $('#pdfInfo').text(`File ${index + 1} of ${this.pdf.totalFiles}`);
                $('#prevPdf, #nextPdf').show();
            }

            pdfjsLib.getDocument(url).promise.then(pdfDoc => {
                this.pdf.doc = pdfDoc;
                renderPage(1);
            }).catch(err => {
                console.warn('PDF load warning:', err);
                // Silently ignore path errors — item simply has no PDF
            });
        };

        $('#prevPage').click(() => { if (this.pdf.page > 1) queueRender(--this.pdf.page); });
        $('#nextPage').click(() => { if (this.pdf.page < this.pdf.doc.numPages) queueRender(++this.pdf.page); });
        $('#pdfZoomIn').click(() => { this.pdf.scale += 0.25; queueRender(this.pdf.page); });
        $('#pdfZoomOut').click(() => { if (this.pdf.scale > 0.25) { this.pdf.scale -= 0.25; queueRender(this.pdf.page); } });
        $('#pdfZoomReset').click(() => { this.pdf.scale = 1.0; queueRender(this.pdf.page); });

        $('#prevPdf').click(() => { if (this.pdf.currentIdx !== 'similar' && this.pdf.currentIdx > 0) loadFile(this.pdf.itemId, --this.pdf.currentIdx); });
        $('#nextPdf').click(() => { if (this.pdf.currentIdx !== 'similar' && this.pdf.currentIdx < this.pdf.totalFiles - 1) loadFile(this.pdf.itemId, ++this.pdf.currentIdx); });

        $(document).on('click', '.view-pdf-btn', (e) => {
            const btn = $(e.currentTarget);
            this.pdf.itemId = btn.data('id');
            const isSimilar = btn.data('similar') || (btn.attr('id') === 'fullSimilarBtn');
            this.pdf.totalFiles = isSimilar ? 1 : btn.data('count');
            this.pdf.currentIdx = isSimilar ? 'similar' : 0;
            $('#pdfModal').modal('show');
            loadFile(this.pdf.itemId, this.pdf.currentIdx);
        });

        // Controls for inline STANDARD PDF
        $('#prevStandardPage').click(() => {
            if (this.refStandardPageNum > 1) {
                this.refStandardPageNum--;
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
            } else if (this.refStandardFileIndex > 0) {
                this.refStandardFileIndex--;
                const itemId = $('#item_id').val();
                const prevFileUrl = this.config.pdfUrlPattern
                    .replace("ID_PLACEHOLDER", itemId)
                    .replace("INDEX_PLACEHOLDER", this.refStandardFileIndex);

                this.renderPdfToCanvas(
                    prevFileUrl,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                    1
                );
            }
        });

        $('#nextStandardPage').click(() => {
            if (
                this.refStandardPdfDoc &&
                this.refStandardPageNum < this.refStandardPdfDoc.numPages
            ) {
                this.refStandardPageNum++;
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
            } else if (this.refStandardFiles && this.refStandardFileIndex < this.refStandardFiles.length - 1) {
                this.refStandardFileIndex++;
                const itemId = $('#item_id').val();
                const nextFileUrl = this.config.pdfUrlPattern
                    .replace("ID_PLACEHOLDER", itemId)
                    .replace("INDEX_PLACEHOLDER", this.refStandardFileIndex);

                this.renderPdfToCanvas(
                    nextFileUrl,
                    "standardPdfCanvas",
                    "standardPdfPlaceholder",
                    "standardPdfLoading",
                    1
                );
            }
        });

        // Zoom logic for inline STANDARD PDF
        $("#zoomInStandard").click(() => {
            this.standardZoomLevel += 0.25;
            if (this.refStandardPdfDoc)
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
        });
        $("#zoomOutStandard").click(() => {
            if (this.standardZoomLevel > 0.5) {
                this.standardZoomLevel -= 0.25;
                if (this.refStandardPdfDoc)
                    this.renderPageOnCanvas(
                        this.refStandardPdfDoc,
                        "standardPdfCanvas",
                        this.refStandardPageNum,
                    );
            }
        });
        $("#zoomResetStandard").click(() => {
            this.standardZoomLevel = 1.0;
            if (this.refStandardPdfDoc)
                this.renderPageOnCanvas(
                    this.refStandardPdfDoc,
                    "standardPdfCanvas",
                    this.refStandardPageNum,
                );
        });

        // Full screen viewer trigger from inline card
        $("#fullStandardBtn").click(() => {
            const itemId = $('#item_id').val();
            if (itemId) {
                this.pdf.itemId = itemId;
                this.pdf.totalFiles = this.refStandardFiles.length;
                this.pdf.currentIdx = this.refStandardFileIndex;
                $('#pdfModal').modal('show');
                loadFile(this.pdf.itemId, this.pdf.currentIdx);
            }
        });

        // Controls for inline SIMILAR PDF
        $('#prevSimilarPage').click(() => {
            if (this.refSimilarPageNum > 1) {
                this.refSimilarPageNum--;
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
            }
        });

        $('#nextSimilarPage').click(() => {
            if (
                this.refSimilarPdfDoc &&
                this.refSimilarPageNum < this.refSimilarPdfDoc.numPages
            ) {
                this.refSimilarPageNum++;
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
            }
        });

        // Zoom logic for inline SIMILAR PDF
        $("#zoomInSimilar").click(() => {
            this.similarZoomLevel += 0.25;
            if (this.refSimilarPdfDoc)
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
        });
        $("#zoomOutSimilar").click(() => {
            if (this.similarZoomLevel > 0.5) {
                this.similarZoomLevel -= 0.25;
                if (this.refSimilarPdfDoc)
                    this.renderPageOnCanvas(
                        this.refSimilarPdfDoc,
                        "similarPdfCanvas",
                        this.refSimilarPageNum,
                    );
            }
        });
        $("#zoomResetSimilar").click(() => {
            this.similarZoomLevel = 1.0;
            if (this.refSimilarPdfDoc)
                this.renderPageOnCanvas(
                    this.refSimilarPdfDoc,
                    "similarPdfCanvas",
                    this.refSimilarPageNum,
                );
        });

        // Full screen viewer trigger for similar PDF
        $("#fullSimilarBtn").click(() => {
            const itemId = $('#item_id').val();
            if (itemId) {
                this.pdf.itemId = itemId;
                this.pdf.totalFiles = 1;
                this.pdf.currentIdx = 'similar';
                $('#pdfModal').modal('show');
                loadFile(this.pdf.itemId, this.pdf.currentIdx);
            }
        });
    }

    renderPdfToCanvas(url, canvasId, placeholderId, loadingId, pageNum = 1) {
        const _this = this;
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const $placeholder = $("#" + placeholderId);
        const $loading = $("#" + loadingId);
        const $canvas = $(canvas);

        $placeholder.removeClass("d-flex").addClass("d-none");
        $canvas.addClass("d-none").hide();
        $loading.removeClass("d-none").addClass("d-flex");

        if (this.pdfCache && this.pdfCache[url]) {
            this.drawPage(
                this.pdfCache[url],
                canvas,
                ctx,
                $loading,
                $canvas,
                pageNum,
                canvasId,
            );
            return;
        }

        pdfjsLib
            .getDocument(url)
            .promise.then((pdf) => {
                if (!_this.pdfCache) _this.pdfCache = {};
                _this.pdfCache[url] = pdf;
                _this.drawPage(
                    pdf,
                    canvas,
                    ctx,
                    $loading,
                    $canvas,
                    pageNum,
                    canvasId,
                );
            })
            .catch((err) => {
                $loading.removeClass("d-flex").addClass("d-none");
                $placeholder
                    .removeClass("d-none")
                    .addClass("d-flex")
                    .find("p")
                    .text("Gagal memuat PDF");
            });
    }

    drawPage(pdf, canvas, ctx, $loading, $canvas, pageNum, canvasId) {
        const _this = this;
        pdf.getPage(pageNum).then((page) => {
            const containerWidth = $canvas.parent().width() || 500;
            const availableWidth = containerWidth - 40;
            const viewport = page.getViewport({ scale: 1.0 });
            let zoom = canvasId === "standardPdfCanvas" ? (_this.standardZoomLevel || 1.0) : (_this.similarZoomLevel || 1.0);
            const scale = (availableWidth / viewport.width) * zoom;
            const scaledViewport = page.getViewport({ scale: scale });

            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            if (zoom > 1.0) $canvas.css({ width: "auto", "max-width": "none" });
            else $canvas.css({ width: "100%", "max-width": "100%" });
            $canvas.css("height", "auto");

            page.render({
                canvasContext: ctx,
                viewport: scaledViewport,
            }).promise.then(() => {
                $loading.removeClass("d-flex").addClass("d-none");
                $canvas.removeClass("d-none").show();
                if (canvasId === "standardPdfCanvas") {
                    _this.refStandardPdfDoc = pdf;
                    const fileInfo = _this.refStandardFiles.length > 1 ? ` (${_this.refStandardFileIndex + 1}/${_this.refStandardFiles.length})` : '';
                    $("#standardPageInfo").text(`P ${pageNum}/${pdf.numPages}${fileInfo}`);
                    _this.refStandardPageNum = pageNum;
                } else if (canvasId === "similarPdfCanvas") {
                    _this.refSimilarPdfDoc = pdf;
                    $("#similarPageInfo").text(`P ${pageNum}/${pdf.numPages}`);
                    _this.refSimilarPageNum = pageNum;
                }
                _this.updateRefNavControls();
            });
        });
    }

    renderPageOnCanvas(pdf, canvasId, pageNum) {
        if (!pdf) return;
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext("2d");
        const $canvas = $(canvas);
        const $loading = $(canvasId === "standardPdfCanvas" ? "#standardPdfLoading" : "#similarPdfLoading");

        $canvas.hide();
        $loading.removeClass("d-none").addClass("d-flex");
        this.drawPage(
            pdf,
            canvas,
            ctx,
            $loading,
            $canvas,
            pageNum,
            canvasId,
        );
    }

    updateRefNavControls() {
        if (this.refStandardFiles && this.refStandardFiles.length > 0)
            $(".standard-nav-controls").attr(
                "style",
                "display: flex !important;",
            );
        else $(".standard-nav-controls").hide();

        if (this.refSimilarPdfDoc)
            $(".similar-nav-controls").attr(
                "style",
                "display: flex !important;",
            );
        else $(".similar-nav-controls").hide();
    }

    initItemSelection() {
        const _this = this;
        $('#item_id').on('change', function() {
            const opt = $(this).find('option:selected');
            const files = opt.data('files');
            const id = $(this).val();
            const similarPdf = opt.data('similar');

            // Guard: only show PDF button if files array has valid non-empty entries
            const validFiles = Array.isArray(files) ? files.filter(f => f && f.trim && f.trim() !== '') : [];

            // Trigger inline PDF loading
            if (id) {
                _this.refStandardPdfDoc = null;
                _this.refStandardPageNum = 1;
                _this.refStandardFileIndex = 0;
                _this.refStandardFiles = validFiles;

                if (validFiles.length > 0) {
                    const firstPdfUrl = _this.config.pdfUrlPattern
                        .replace('ID_PLACEHOLDER', id)
                        .replace('INDEX_PLACEHOLDER', 0);
                    _this.renderPdfToCanvas(
                        firstPdfUrl,
                        "standardPdfCanvas",
                        "standardPdfPlaceholder",
                        "standardPdfLoading",
                        1
                    );
                    $("#downloadStandardBtn").attr("href", firstPdfUrl).show();
                    $("#fullStandardBtn").show();
                } else {
                    $("#standardPdfCanvas").addClass("d-none").hide();
                    $("#standardPdfPlaceholder")
                        .removeClass("d-none")
                        .addClass("d-flex")
                        .find("p")
                        .text("Standard PDF tidak tersedia");
                    $(".standard-nav-controls").hide();
                    $("#downloadStandardBtn, #fullStandardBtn").hide();
                }

                _this.refSimilarPdfDoc = null;
                _this.refSimilarPageNum = 1;

                if (similarPdf) {
                    _this.renderPdfToCanvas(
                        similarPdf,
                        "similarPdfCanvas",
                        "similarPdfPlaceholder",
                        "similarPdfLoading",
                        1
                    );
                    $("#similarStatusText").text("");
                    $("#downloadSimilarBtn").attr("href", similarPdf).show();
                    $("#fullSimilarBtn").show();
                } else {
                    $("#similarPdfCanvas").addClass("d-none").hide();
                    $("#similarPdfPlaceholder")
                        .removeClass("d-none")
                        .addClass("d-flex");
                    $("#similarStatusText").text("Referral Dimensi Part tidak tersedia untuk item ini");
                    $(".similar-nav-controls").hide();
                    $("#downloadSimilarBtn, #fullSimilarBtn").hide();
                }
            } else {
                $("#standardPdfCanvas").addClass("d-none").hide();
                $("#standardPdfPlaceholder")
                    .removeClass("d-none")
                    .addClass("d-flex")
                    .find("p")
                    .text("Pilih Item untuk menampilkan Standard PDF");
                $(".standard-nav-controls").hide();
                $("#downloadStandardBtn, #fullStandardBtn").hide();

                $("#similarPdfCanvas").addClass("d-none").hide();
                $("#similarPdfPlaceholder")
                    .removeClass("d-none")
                    .addClass("d-flex");
                $("#similarStatusText").text("");
                $(".similar-nav-controls").hide();
                $("#downloadSimilarBtn, #fullSimilarBtn").hide();
            }
        });
    }

    initSapSelection() {
        $('#sapCodeInput').on('input', function() {
            const sap = $(this).val().trim().toLowerCase();
            if (sap) {
                const opt = $('#item_id option').filter(function() {
                    return $(this).data('sap-code') && $(this).data('sap-code').toString().toLowerCase() === sap;
                });
                if (opt.length) {
                    $('#item_id').val(opt.val()).trigger('change');
                    const selectEl = document.getElementById('item_id');
                    if (selectEl) {
                        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else $(this).removeClass('is-valid').addClass('is-invalid');
            } else $(this).removeClass('is-valid is-invalid');
        });
    }

    initImageZoom() {
        let zoom = 1;
        const update = () => $('#modalImage').css({ transform: `scale(${zoom})`, 'transform-origin': zoom > 1 ? 'top center' : 'center center' });
        $('#zoomIn').click(() => { zoom += 0.25; update(); });
        $('#zoomOut').click(() => { if (zoom > 0.25) { zoom -= 0.25; update(); } });
        $('#zoomReset').click(() => { zoom = 1; update(); });

        $('#imageModal').on('show.bs.modal', function(e) {
            const btn = $(e.relatedTarget);
            $(this).find('#modalImage').attr('src', btn.data('image'));
            $(this).find('#modalTitle').text(btn.data('title'));
            $(this).find('#modalDescription').text(btn.data('description'));
            zoom = 1; update();
        });
    }

    initImageCapture() {
        $('#captureBtn').click(() => $('#image').click());
        $('#image').on('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                const isImage = file.type.match(/image.*/);
                
                $('#captureBtnText').html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                $('#captureBtn').removeClass('btn-primary btn-warning').addClass('btn-secondary');
                
                try {
                    let processedFile = file;
                    if (isImage) {
                        processedFile = await this.compressImage(file);
                        
                        // Replace the file in the input using DataTransfer
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(processedFile);
                        e.target.files = dataTransfer.files;
                    }
                    
                    const sizeKB = (processedFile.size / 1024).toFixed(1);
                    $('#fileName').text(`File: ${processedFile.name} (${sizeKB} KB)`);
                    $('#captureBtnText').html('<i class="fas fa-sync"></i> Ganti Foto');
                    $('#captureBtn').removeClass('btn-secondary btn-primary').addClass('btn-warning');
                    
                    const reader = new FileReader();
                    reader.onload = (ev) => { $('#previewImage').attr('src', ev.target.result); $('#previewBtn').show(); };
                    reader.readAsDataURL(processedFile);
                } catch (err) {
                    console.error("Gagal memproses gambar:", err);
                    Swal.fire('Error', 'Gagal memproses gambar sebelum upload.', 'error');
                    $('#captureBtnText').html('<i class="fas fa-camera"></i> Ambil Foto');
                    $('#captureBtn').removeClass('btn-secondary btn-warning').addClass('btn-primary');
                }
            }
        });
        $('#previewBtn').click(() => $('#imagePreviewModal').modal('show'));
    }

    compressImage(file, maxWidth = 1280, maxHeight = 1280, quality = 0.7) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > maxWidth) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (blob) {
                            const newFile = new File([blob], file.name, {
                                type: file.type,
                                lastModified: Date.now()
                            });
                            resolve(newFile);
                        } else {
                            reject(new Error("Canvas to Blob failed"));
                        }
                    }, file.type, quality);
                };
                img.onerror = (err) => reject(err);
            };
            reader.onerror = (err) => reject(err);
        });
    }

    initTimer() {
        const updateDisplay = () => {
            const h = Math.floor(this.timer.seconds / 3600);
            const m = Math.floor((this.timer.seconds % 3600) / 60);
            const s = this.timer.seconds % 60;
            const text = [h, m, s].map(v => v < 10 ? "0" + v : v).join(":");
            $('#timerDisplay').text(text);
            $('#cycleTimeInput').val(this.timer.seconds);
        };

        $('#startTimerBtn').click(() => {
            if (!this.timer.running) {
                this.timer.running = true;
                $('#startTimerBtn').removeClass('btn-success').addClass('btn-secondary').prop('disabled', true).html('<i class="fas fa-clock"></i> Running...');
                $('#saveBtn').prop('disabled', false);
                this.lockInputs(false);
                this.timer.interval = setInterval(() => { this.timer.seconds++; updateDisplay(); }, 1000);
            }
        });
    }

    initNextProses() {
        const toggle = () => {
            if ($('select[name="position_remark_judgment"]').val() === 'NG') $('#nextProsesContainer').slideDown();
            else { $('#nextProsesContainer').slideUp(); $('#nextProses').val(''); }
        };
        $('select[name="position_remark_judgment"]').on('change', toggle);
        toggle();
    }

    initAutoJudgment() {
        const updateJudgment = () => {
            const crossCutVal = $('#defectCrossCut').val();
            const pencilScratchVal = $('#defectPencilScratch').val();
            const tapTestVal = $('#defectTapTest').val();

            if (crossCutVal === 'NG' || pencilScratchVal === 'NG' || tapTestVal === 'NG') {
                $('select[name="position_remark_judgment"]').val('NG').trigger('change');
            } else {
                if ($('#defectCrossCut').length > 0) {
                    $('select[name="position_remark_judgment"]').val('OK').trigger('change');
                }
            }
        };

        $(document).on('change', '#defectCrossCut, #defectPencilScratch, #defectTapTest', updateJudgment);
    }

    initFormSubmit() {
        $('#checksheetForm').on('submit', (e) => {
            const judgement = $('select[name="position_remark_judgment"]').val();
            if (judgement === 'NG' && !$('#nextProses').val()) {
                Swal.fire({ icon: 'warning', title: 'Next Proses Wajib Dipilih' });
                $('#nextProses').focus();
                return e.preventDefault();
            }
            if (this.timer.running) { clearInterval(this.timer.interval); this.timer.running = false; }

            // Tangani pengiriman AJAX jika diinginkan, atau biarkan terkirim secara normal.
            // Berdasarkan kode asli, ini menggunakan AJAX.
            e.preventDefault();
            const $btn = $('#saveBtn');
            const original = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: $('#checksheetForm').attr('action'),
                method: 'POST',
                data: new FormData(e.target),
                processData: false,
                contentType: false,
                success: (res) => {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', showCancelButton: true, confirmButtonText: 'Lihat Data' })
                            .then(r => r.isConfirmed ? window.location.href = res.index_url : this.resetForm());
                    }
                },
                error: (xhr) => {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal menyimpan.' });
                    $btn.prop('disabled', false).html(original);
                }
            });
        });
    }

    resetForm() {
        if (this.timer.interval) clearInterval(this.timer.interval);
        this.timer = { running: false, seconds: 0, interval: null };
        $('#timerDisplay').text('00:00:00');
        $('#startTimerBtn').removeClass('btn-secondary').addClass('btn-success').prop('disabled', false).html('<i class="fas fa-play"></i> Start');
        this.lockInputs(true);
        $('#checksheetForm')[0].reset();
        $('#previewBtn').hide();
        
        // Kembalikan tombol Ambil Gambar ke semula
        $('#fileName').text('');
        $('#captureBtnText').html('<i class="fas fa-camera"></i> Buka Kamera / Pilih Foto');
        $('#captureBtn').removeClass('btn-warning').addClass('btn-primary');
        
        // Kembalikan tombol Simpan Data ke semula
        $('#saveBtn').prop('disabled', true).html('<i class="fas fa-save fa-sm"></i> Simpan Data');

        // Reset inline PDF viewer
        this.refStandardPdfDoc = null;
        this.refStandardPageNum = 1;
        this.refStandardFileIndex = 0;
        this.refStandardFiles = [];
        this.standardZoomLevel = 1.0;
        this.refSimilarPdfDoc = null;
        this.refSimilarPageNum = 1;
        this.similarZoomLevel = 1.0;
        $('#item_id').trigger('change');
    }
}

// Inisialisasi global
window.initCrossCutIndex = (config) => new CrossCutIndex(config);
window.initCrossCutCreate = (config) => new CrossCutCreate(config);
