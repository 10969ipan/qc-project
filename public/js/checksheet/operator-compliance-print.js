/**
 * Script khusus halaman cetak Checksheet Kepatuhan Operator
 */
window.addEventListener('load', function () {
    setTimeout(function () {
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            window.print();
        }
    }, 600);
});

document.addEventListener('click', function (e) {
    var printBtn = e.target.closest('#btnPrintAction');
    if (printBtn) {
        e.preventDefault();
        window.print();
    }
    var backBtn = e.target.closest('#btnBackAction');
    if (backBtn) {
        e.preventDefault();
        window.history.back();
    }
});

