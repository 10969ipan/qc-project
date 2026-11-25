/**
 * Script Cetak Otomatis Leveling Inspector (dipanggil di dalam hidden iframe Direct Print)
 */
(function () {
    var now = new Date();
    var pad = function (n) { return n < 10 ? '0' + n : n; };
    var dateStr = pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear()
        + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
    var el = document.getElementById('footerDateTime');
    if (el) {
        el.textContent = 'Dicetak: ' + dateStr;
    }

    window.addEventListener('load', function () {
        window.print();
    });
})();
