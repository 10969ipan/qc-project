/**
 * approval-guard.js
 * Handles UI protection and alerts for checksheet approvals:
 * 1. Blocked approvals when Next Process is OPEN (In-Process, FPA, Sub-Assy)
 * 2. Sampling vs Bulk confirmation and feedback
 */
$(document).ready(function () {
    // Delegated click handler for blocked approval buttons
    $(document).off('click', '.btn-blocked-next-process').on('click', '.btn-blocked-next-process', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var processName = $(this).data('next-process') || 'Sortir';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Dapat Disetujui!',
                html: '<div class="text-left py-1" style="font-size: 0.88rem; color: #334155;">' +
                      '<p>Checksheet ini memiliki <strong>Next Proses (' + $('<div>').text(processName).html() + ')</strong> yang statusnya masih <span class="badge badge-danger px-2 py-1"><i class="fas fa-clock mr-1"></i> OPEN</span>.</p>' +
                      '<div class="alert alert-warning py-2 px-3 small mb-0 mt-2" style="border-radius: 8px;">' +
                      '<i class="fas fa-exclamation-triangle mr-1"></i> Data harus diselesaikan dan berstatus <strong>CLOSE</strong> pada modul Sortir sebelum approval dapat diberikan.' +
                      '</div>' +
                      '</div>',
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Mengerti',
                confirmButtonColor: '#4e73df',
                customClass: {
                    popup: 'shadow border-0 rounded-lg'
                }
            });
        } else {
            alert('Data tidak dapat disetujui! Status Next Proses (' + processName + ') masih OPEN. Selesaikan sortir hingga status CLOSE terlebih dahulu.');
        }
    });
});
