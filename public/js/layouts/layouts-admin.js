document.addEventListener('DOMContentLoaded', function () {
    const config = window.__LAYOUTS_ADMIN__ || {};

    // Logout Confirmation
    $(document).on('click', '.btn-logout', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin ingin keluar?',
            text: "Anda akan keluar dari sesi ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const logoutForm = document.getElementById('logout-form');
                if (logoutForm) logoutForm.submit();
            }
        });
    });

    // Global Delete Confirmation
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Rejection Alerts for Inspectors
    if (config.unreadRejections && config.unreadRejections.length > 0) {
        config.unreadRejections.forEach(rejection => {
            Swal.fire({
                title: '<span class="text-danger font-weight-bold">LAPORAN DITOLAK!</span>',
                html: `<div class="text-left mt-2"><b>${rejection.title}</b><br><p class="mt-2">${rejection.message}</p></div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#aaa',
                confirmButtonText: '<i class="fas fa-eye"></i> Lihat Data',
                cancelButtonText: 'Tutup',
                backdrop: `rgba(220, 53, 69, 0.2)`
            }).then((result) => {
                // Mark as read regardless of choice (to ensure it only appears once)
                if (rejection.markReadUrl) {
                    $.post(rejection.markReadUrl);
                }
                
                if (result.isConfirmed && rejection.url) {
                    window.location.href = rejection.url;
                }
            });
        });
    }

    // Auto Uppercase for all text inputs and textareas
    $(document).on('input', 'input[type="text"]:not(.no-autoupper), textarea:not(.no-autoupper)', function () {
        let start = this.selectionStart;
        let end = this.selectionEnd;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(start, end);
    });

    // Global 419 Handler for jQuery AJAX
    $.ajaxSetup({
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 419) {
                window.location.reload();
            }
        }
    });

    // Global 419 Handler for Fetch API
    const originalFetch = window.fetch;
    window.fetch = function () {
        return originalFetch.apply(this, arguments)
            .then(async response => {
                if (response.status === 419) {
                    window.location.reload();
                    return new Promise(() => { });
                }
                return response;
            });
    };

    // Global Form Submission Loader
    $(document).on('submit', 'form', function (e) {
        if ($(this).attr('method') && $(this).attr('method').toUpperCase() === 'GET') {
            return;
        }

        if (this.checkValidity && !this.checkValidity()) {
            return;
        }

        if ($(this).hasClass('ajax-form') || $(this).hasClass('no-loader') || e.isDefaultPrevented()) {
            return;
        }

        $('#global-loader').css('display', 'flex');

        setTimeout(() => {
            $(this).find('button[type="submit"]').prop('disabled', true);
        }, 10);
    });

    // Page Navigation Loader
    $(document).on('click', 'a', function (e) {
        const href = $(this).attr('href');
        const dataToggle = $(this).attr('data-toggle');

        // Aggressive exclusion: don't show loader for table links, modals, or simple hash links
        if (!href ||
            href === '#' ||
            href.startsWith('javascript:') ||
            $(this).attr('target') === '_blank' ||
            href.startsWith('#') ||
            $(this).closest('table').length || // NEW: Ignore links inside tables
            $(this).hasClass('no-loader') ||
            $(this).hasClass('btn-edit-tool') ||
            $(this).hasClass('btn-logout') ||
            $(this).hasClass('dropdown-toggle') ||
            dataToggle === 'modal' ||
            dataToggle === 'dropdown' ||
            e.isDefaultPrevented()) {
            return;
        }

        $('#global-loader').css({
            'display': 'flex',
            'pointer-events': 'auto' // Re-enable pointer events when showing
        });

        // Safety: auto-hide loader if navigation doesn't complete (e.g. external link, back nav)
        if (window.__globalLoaderTimeout) clearTimeout(window.__globalLoaderTimeout);
        window.__globalLoaderTimeout = setTimeout(function () {
            $('#global-loader').fadeOut(function() {
                $(this).css('pointer-events', 'none'); // Ensure it's not blocking after fade
            });
        }, 3000);
    });

    // Hide loader if it was shown but navigation didn't complete
    $(window).on('pageshow', function (event) {
        $('#global-loader').fadeOut();
    });

    // Session Heartbeat
    if (config.sessionPingUrl) {
        setInterval(function () {
            $.get(config.sessionPingUrl).catch(function (err) {
                console.warn("Session heartbeat failed");
            });
        }, 10 * 60 * 1000); // 10 minutes
    }

    // Global AJAX Setup for CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ajaxError(function (event, xhr, settings) {
        if (xhr.status === 419) {
            Swal.fire({
                icon: 'error',
                title: 'Sesi Berakhir',
                text: 'Sesi anda telah berakhir atau token keamanan kadaluarsa. Silakan refresh halaman untuk melanjutkan.',
                confirmButtonText: 'Refresh Halaman',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        }
    });

    // Specific handler for downloads
    $(document).on('click', '.btn-download', function () {
        $('#global-loader').css('display', 'flex');
        setTimeout(function () {
            $('#global-loader').fadeOut();
        }, 5000);
    });
    // Global Native Validation Indonesian Translation
    document.addEventListener('invalid', (function () {
        return function (e) {
            e.preventDefault();
            const target = e.target;
            if (target.validity.valueMissing) {
                target.setCustomValidity('Harap isi bidang ini.');
            } else if (target.validity.typeMismatch && target.type === 'email') {
                target.setCustomValidity('Harap masukkan alamat email yang valid.');
            }
        };
    })(), true);

    document.addEventListener('input', function (e) {
        e.target.setCustomValidity('');
    });
});
