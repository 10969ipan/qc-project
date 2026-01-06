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
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-responsive.css') }}" rel="stylesheet">

    <style>
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

    {{-- Tambahkan script lain yang dibutuhkan di sini --}}
    @stack('scripts')

</body>

</html>