(function () {
    'use strict';

    window.showDetailModal = function (element) {
        const card = element.closest('.status-item');
        if (!card) return;

        const unitName          = card.querySelector('h4')?.textContent || 'Unknown';
        const partNumber        = card.dataset.partNumber || '-';
        const itemName          = card.dataset.itemName || '-';
        const judgment          = card.dataset.judgment || '-';
        const totalQty          = card.dataset.totalQty || '-';
        const samplingQty       = card.dataset.samplingQty || '-';
        const okCount           = card.dataset.okCount || '-';
        const ngCount           = card.dataset.ngCount || '-';
        const operator          = card.dataset.operator || '-';
        const date              = card.dataset.date || '-';
        const shift             = card.dataset.shift || '-';
        const time              = card.dataset.time || '-';
        const tonnage           = card.dataset.tonnage || '-';
        const status            = card.dataset.status || 'idle';
        const manualDescription = card.dataset.manualDescription || '';
        const manualBy          = card.dataset.manualBy || '';
        const manualUpdated     = card.dataset.manualUpdated || '';

        document.getElementById('modalUnitName').textContent = unitName;

        let content = '';
        const unitLabel = unitName.includes('MEJA') ? 'MEJA' : 'MESIN';

        if (status === 'active') {
            content = `
            <div class="space-y-6 text-slate-800 dark:text-slate-200">
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-icons-round text-primary">inventory_2</span>
                        <h6 class="font-bold m-0 uppercase tracking-wider text-xs">Informasi Item</h6>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <p class="text-[0.65rem] text-slate-500 uppercase font-bold">Part Number</p>
                            <p class="text-sm font-mono font-bold">${partNumber}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[0.65rem] text-slate-500 uppercase font-bold">Nama Item</p>
                            <p class="text-sm font-bold">${itemName}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[0.65rem] text-slate-500 uppercase font-bold">Kapasitas (Tonnage)</p>
                            <p class="text-sm font-bold">${tonnage}T</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[0.65rem] text-slate-500 uppercase font-bold">Waktu Update</p>
                            <p class="text-sm font-bold">${time} WIB</p>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50/50 dark:bg-indigo-900/10 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-900/30">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-icons-round text-indigo-600">verified_user</span>
                        <h6 class="font-bold m-0 uppercase tracking-wider text-xs">Quality Control</h6>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-white dark:bg-slate-800 p-3 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                            <p class="text-[0.6rem] text-slate-500 uppercase font-bold mb-1">Sampling</p>
                            <div class="flex items-end gap-1">
                                <span class="text-xl font-bold">${samplingQty}</span>
                                <span class="text-[0.65rem] text-slate-400 mb-1">/ ${totalQty} pcs</span>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-3 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                            <p class="text-[0.6rem] text-slate-500 uppercase font-bold mb-1">Status Judgment</p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full ${judgment === 'OK' ? 'bg-green-500' : 'bg-red-500'}"></span>
                                <span class="text-sm font-extrabold ${judgment === 'OK' ? 'text-green-600' : 'text-red-600'}">${judgment}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 p-2.5 rounded-xl border border-green-100 dark:border-green-900/30">
                            <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">OK</div>
                            <div>
                                <p class="text-[0.6rem] text-green-700 dark:text-green-400 font-bold uppercase">Total OK</p>
                                <p class="text-sm font-bold">${okCount}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 p-2.5 rounded-xl border border-red-100 dark:border-red-900/30">
                            <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">NG</div>
                            <div>
                                <p class="text-[0.6rem] text-red-700 dark:text-red-400 font-bold uppercase">Total NG</p>
                                <p class="text-sm font-bold">${ngCount}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between px-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400">
                            <span class="material-icons-round text-lg">person</span>
                        </div>
                        <div>
                            <p class="text-[0.6rem] text-slate-500 uppercase font-bold leading-none mb-1">Inspector Quality Control</p>
                            <p class="text-xs font-bold leading-none">${operator}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[0.6rem] text-slate-500 uppercase font-bold leading-none mb-1">Shift / Tanggal</p>
                        <p class="text-xs font-medium leading-none">Shift ${shift} | ${date}</p>
                    </div>
                </div>
            </div>`;

        } else if (['maintenance', 'stopped', 'trouble'].includes(status)) {
            let badge = status === 'maintenance' ? 'GANTI MOLD/SETTING' : (status === 'stopped' ? 'STAND BY' : 'TROUBLE');
            let color = status === 'maintenance' ? 'yellow' : (status === 'stopped' ? 'gray' : 'red');
            let icon  = status === 'maintenance' ? 'engineering' : (status === 'stopped' ? 'pause_circle_outline' : 'warning');

            content = `
            <div class="text-center py-6 text-slate-800 dark:text-slate-200">
                <div class="w-20 h-20 bg-${color}-50 dark:bg-${color}-900/20 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-${color}-100 dark:border-${color}-900/30">
                    <span class="material-icons-round text-4xl text-${color}-600 dark:text-${color}-400">${icon}</span>
                </div>
                <h4 class="text-xl font-black mb-2 uppercase italic">${unitLabel} IN ${badge}</h4>
                <div class="max-w-xs mx-auto bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 mt-6">
                    <p class="text-[0.65rem] text-slate-500 uppercase font-bold mb-2">Keterangan / Masalah</p>
                    <p class="text-sm font-medium italic">"${manualDescription || 'Tidak ada keterangan tambahan'}"</p>
                    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center text-[0.6rem]">
                        <span class="text-slate-400 uppercase font-bold">Dibuat Oleh: ${manualBy}</span>
                        <span class="text-slate-400 font-medium">${manualUpdated}</span>
                    </div>
                </div>
            </div>`;

        } else {
            content = `
            <div class="text-center py-12 text-slate-800 dark:text-slate-200">
                <div class="relative w-24 h-24 mx-auto mb-6">
                    <div class="absolute inset-0 bg-slate-100 dark:bg-slate-800 rounded-full animate-ping opacity-25"></div>
                    <div class="relative w-full h-full bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center border-4 border-white dark:border-slate-900 shadow-inner">
                        <span class="material-icons-round text-4xl text-slate-400">hourglass_empty</span>
                    </div>
                </div>
                <h4 class="text-lg font-bold mb-2 tracking-tight">Status: ${unitLabel} IDLE</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-[240px] mx-auto">Menunggu pengecekan dari tim Quality Control.</p>
                <button class="mt-8 px-6 py-2 bg-slate-800 dark:bg-white text-white dark:text-slate-900 rounded-full text-xs font-bold uppercase tracking-widest shadow-lg shadow-slate-200 dark:shadow-none hover:scale-105 transition-transform" data-dismiss="modal">Tutup Detail</button>
            </div>`;
        }

        document.getElementById('modalBody').innerHTML = content;
        $('#detailModal').modal('show');
    };

    function updateDateTime() {
        const now = new Date();

        const hariList  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                           'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const namaHari  = hariList[now.getDay()];
        const tanggal   = now.getDate();
        const namaBulan = bulanList[now.getMonth()];
        const tahun     = now.getFullYear();

        const strTanggal = `${namaHari}, ${tanggal} ${namaBulan} ${tahun}`;

        const jam   = String(now.getHours()).padStart(2, '0');
        const menit = String(now.getMinutes()).padStart(2, '0');
        const detik = String(now.getSeconds()).padStart(2, '0');
        const strJam = `${jam}:${menit}:${detik}`;

        const elTanggal = document.getElementById('current-date');
        if (elTanggal) elTanggal.textContent = strTanggal;

        const elJam = document.getElementById('current-time');
        if (elJam) elJam.textContent = strJam;
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);

})();
