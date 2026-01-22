<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('master item/ipp.jpg') }}" type="image/jpeg">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet" type="text/css">

    <!-- Professional Corporate Font - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-responsive.css') }}?v={{ time() }}" rel="stylesheet">

    <!-- Custom styles for DataTables -->
    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

    <!-- Chart.js for Dashboard Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <style>
        /* Professional Corporate Font - Inter */
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span:not([class*="fa"]):not([class*="icon"]),
        div:not([class*="fa"]):not([class*="icon"]),
        a:not([class*="fa"]):not([class*="icon"]),
        button:not([class*="fa"]):not([class*="icon"]),
        input,
        textarea,
        select,
        label,
        td,
        th,
        li {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif !important;
        }

        /* Improve readability */
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga' 1, 'calt' 1;
        }

        /* Headings optimization */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6 {
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Body text optimization */
        p,
        span,
        div,
        td,
        th,
        li {
            font-weight: 400;
            letter-spacing: -0.011em;
        }

        /* Bold text */
        strong,
        b,
        .font-weight-bold {
            font-weight: 600;
        }

        /* Table Header Styling - Professional Background */
        table thead th,
        table thead td,
        .table thead th,
        .table thead td {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
            padding: 0.75rem !important;
            vertical-align: middle !important;
        }

        /* Input form tables (without thead) - Target first row with th */
        table tr:first-child th,
        .table tr:first-child th,
        table>tr:first-child th,
        .table>tr:first-child th {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
            padding: 0.75rem !important;
            vertical-align: middle !important;
        }

        /* For tables with tbody but th in first row */
        table tbody tr:first-child th,
        .table tbody tr:first-child th {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
            padding: 0.75rem !important;
            vertical-align: middle !important;
        }

        /* All th elements in tables (fallback) - EXCEPT nested tables */
        table th:not(table table th):not(td table th):not(.table .table th),
        .table th:not(table table th):not(td table th):not(.table .table th) {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
        }

        /* MAXIMUM PRIORITY: Remove ALL blue backgrounds from nested tables */
        /* Layer 1: Direct nested table selectors */
        td table th,
        td table tbody th,
        td table thead th,
        td .table th,
        td .table tbody th,
        td .table thead th,
        /* Layer 2: Kimia column specific */
        .kimia-col table th,
        .kimia-col .table th,
        .kimia-col table tbody th,
        .kimia-col table thead th,
        /* Layer 3: Table within table */
        table table th,
        table table tbody th,
        table table thead th,
        .table .table th,
        .table .table tbody th,
        .table .table thead th,
        /* Layer 4: tbody td combinations */
        table tbody td table th,
        .table tbody td table th,
        tbody td table th,
        tbody td table tbody th,
        tbody td table thead th,
        /* Layer 5: Additional specificity */
        table.table-bordered tbody td table th,
        .table.table-bordered tbody td table th {
            background-color: white !important;
            background: white !important;
            color: #5a5c69 !important;
            font-weight: 500 !important;
            border-color: #dee2e6 !important;
            font-size: 0.85rem !important;
            padding: 0.25rem !important;
        }

        /* MAXIMUM PRIORITY: Nested table cells - NO BACKGROUND */
        td table td,
        td table tbody td,
        td .table td,
        td .table tbody td,
        .kimia-col table td,
        .kimia-col .table td,
        .kimia-col table tbody td,
        table table td,
        table table tbody td,
        .table .table td,
        .table .table tbody td,
        table tbody td table td,
        .table tbody td table td,
        tbody td table td,
        tbody td table tbody td,
        table.table-bordered tbody td table td,
        .table.table-bordered tbody td table td {
            background-color: white !important;
            background: white !important;
            color: #5a5c69 !important;
            border-color: #dee2e6 !important;
            padding: 0.25rem !important;
        }

        /* Nested table itself - FORCE white background */
        td table,
        td .table,
        .kimia-col table,
        .kimia-col .table,
        table tbody td table,
        .table tbody td table,
        tbody td table {
            background-color: white !important;
            background: white !important;
        }

        /* Table striped rows for better readability */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Table hover effect */
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.08);
        }

        /* Table Border Enhancement - Clear but not too thick */
        table,
        .table {
            border: 1.5px solid #dee2e6 !important;
        }

        table th,
        table td,
        .table th,
        .table td {
            border: 1.5px solid #dee2e6 !important;
        }

        /* Table bordered variant */
        .table-bordered {
            border: 1.5px solid #dee2e6 !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1.5px solid #dee2e6 !important;
        }

        /* Premium Sidebar Animations */
        /* Premium Sidebar Animations */
        .sidebar .nav-item .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-item .nav-link i {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
        }

        /* Icon Hover Effect */
        .sidebar .nav-item:hover .nav-link i {
            transform: scale(1.2) rotate(10deg);
            color: #fff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        /* Link Slide & Glow Effect */
        .sidebar .nav-item .nav-link:hover {
            padding-left: 1.5rem !important;
            background: rgba(255, 255, 255, 0.1);
            color: #fff !important;
        }

        /* Active State Pulse Animation */
        @keyframes activePulse {
            0% {
                background: rgba(255, 255, 255, 0.15);
            }

            50% {
                background: rgba(255, 255, 255, 0.25);
            }

            100% {
                background: rgba(255, 255, 255, 0.15);
            }
        }

        .sidebar .nav-item.active {
            position: relative;
        }

        .sidebar .nav-item.active .nav-link {
            animation: activePulse 2s infinite ease-in-out;
            border-left: 4px solid #fff;
            font-weight: 700;
        }

        /* Sub-menu implementation (collapse-inner) */
        .sidebar .collapse-inner .collapse-item {
            transition: all 0.2s ease;
            border-left: 0 solid transparent;
        }

        .sidebar .collapse-inner .collapse-item:hover {
            padding-left: 2rem !important;
            border-left: 4px solid #4e73df;
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            transform: translateX(5px);
        }

        /* Logout Slide-out Animation */
        .btn-logout i {
            transition: transform 0.3s ease;
        }

        .btn-logout:hover i {
            transform: translateX(10px) scale(1.2) !important;
            color: #e74a3b !important;
            text-shadow: 0 0 10px rgba(231, 74, 59, 0.4);
        }

        /* Smooth Collapse Animation */
        .sidebar.toggled .nav-item .nav-link i {
            transform: none !important;
        }

        /* Sidebar Brand Hover */
        .sidebar-brand {
            transition: all 0.3s ease;
        }

        .sidebar-brand:hover {
            transform: scale(1.05);
        }

        .sidebar-brand:hover .sidebar-brand-icon {
            animation: rotateBrand 0.5s ease-in-out;
        }

        @keyframes rotateBrand {
            0% {
                transform: rotate(-15deg);
            }

            50% {
                transform: rotate(15deg);
            }

            100% {
                transform: rotate(-15deg);
            }
        }

        /* Generic Button & Action Animations (Inspired by Login) */
        .btn,
        .btn-sm {
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .btn:hover,
        .btn-sm:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15) !important;
        }

        .btn:active,
        .btn-sm:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
        }

        /* Color-specific glow effects */
        .btn-primary:hover {
            box-shadow: 0 8px 15px rgba(78, 115, 223, 0.4) !important;
        }

        .btn-success:hover {
            box-shadow: 0 8px 15px rgba(28, 200, 138, 0.4) !important;
        }

        .btn-danger:hover {
            box-shadow: 0 8px 15px rgba(231, 74, 59, 0.4) !important;
        }

        .btn-warning:hover {
            box-shadow: 0 8px 15px rgba(246, 194, 62, 0.4) !important;
        }

        .btn-info:hover {
            box-shadow: 0 8px 15px rgba(54, 185, 204, 0.4) !important;
        }

        /* Icon within button animation */
        .btn i,
        .btn-sm i {
            transition: transform 0.3s ease;
            display: inline-block;
        }

        .btn:hover i,
        .btn-sm:hover i {
            transform: scale(1.2);
        }

        /* Sidebar Responsive Fixes */
        .sidebar {
            width: 16rem !important;
            /* Increase width from default 14rem */
        }

        .sidebar.toggled {
            width: 6.5rem !important;
        }

        .sidebar .nav-item .collapse .collapse-inner .collapse-item {
            white-space: normal !important;
            /* Allow wrapping */
            line-height: 1.2 !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            font-size: 0.75rem !important;
        }

        .sidebar .collapse-inner {
            width: 14rem !important;
            /* Match inner with sidebar */
        }

        /* Handle deep nesting padding */
        .sidebar .collapse .collapse .collapse-inner {
            padding-left: 0.5rem !important;
        }

        /* Icon adjustment */
        .sidebar .nav-item .nav-link i {
            margin-right: 0.5rem !important;
            width: 1.1rem !important;
            text-align: center;
        }

        /* --- COMPACT UI MODE (User Requested) --- */
        /* Reduce font sizes and padding for dense information display */

        /* Form Inputs */
        .form-control {
            font-size: 0.85rem !important;
            /* Smaller text */
            padding: 0.3rem 0.5rem !important;
            /* Smaller padding */
            height: auto !important;
            /* Let height adjust to content */
            min-height: calc(1.5em + 0.5rem + 2px);
        }

        select.form-control {
            padding-right: 1.5rem !important;
            /* Space for arrow */
        }

        .form-control-sm {
            font-size: 0.75rem !important;
            padding: 0.2rem 0.4rem !important;
            min-height: calc(1.25em + 0.4rem + 2px);
        }

        /* Buttons */
        .btn {
            font-size: 0.85rem !important;
            padding: 0.3rem 0.6rem !important;
        }

        .btn-sm {
            font-size: 0.75rem !important;
            padding: 0.2rem 0.4rem !important;
        }

        /* Tables */
        table th,
        table td,
        .table th,
        .table td {
            padding: 0.35rem 0.5rem !important;
            /* Tighter cells */
            font-size: 0.8rem !important;
            /* Smaller table text */
        }

        /* Labels */
        label {
            font-size: 0.8rem !important;
            margin-bottom: 0.2rem !important;
        }

        /* Input Groups */
        .input-group-text {
            padding: 0.3rem 0.5rem !important;
            font-size: 0.85rem !important;
        }

        /* Adjust layout spacing */
        .card-body {
            padding: 1rem !important;
        }

        .form-group {
            margin-bottom: 0.5rem !important;
        }
    </style>

</head>

<body id="page-top">

    <div id="wrapper">

        @include('layouts.sidebar')
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                @include('layouts.topbar')
                <div class="container-fluid px-3 px-md-4 py-3 py-md-4">

                    {{-- Konten Utama Halaman Diletakkan di sini --}}
                    @yield('content')

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    {{-- Hidden Logout Form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/jquery/jquery.min.js') }}"></script>
    <script
        src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script
        src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    {{-- SweetAlert2 --}}
    <script src="{{ asset('sweetalert/sweetalert2.all.min.js') }}"></script>
    <script>
        // Flash Messages
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 1500
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: @json(session('error')),
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: @json(session('warning')),
            });
        @endif

        @if(session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Info',
                text: @json(session('info')),
            });
        @endif

        // Global Delete Confirmation
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');
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
                    document.getElementById('logout-form').submit();
                }
            });
        });

        // Catatan: Auto logout saat browser ditutup ditangani oleh config session.php
        // dengan expire_on_close => true. Session cookie akan otomatis expire saat browser ditutup.
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto Uppercase for all text inputs and textareas
            $(document).on('input', 'input[type="text"], textarea', function () {
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
                            // Keep the promise pending so downstream .then() doesn't execute with broken state
                            return new Promise(() => { });
                        }
                        return response;
                    });
            };
        });
    </script>

    {{-- Sticky Horizontal Scroll --}}
    <script src="{{ asset('js/sticky-scroll.js') }}?v={{ time() }}"></script>

    {{-- Tambahkan script lain yang dibutuhkan di sini --}}
    @stack('scripts')

</body>

</html>